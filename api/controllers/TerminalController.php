<?php
// api/controllers/TerminalController.php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../shared/notification_helper.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

try {
    if ($action === 'getBooks') {
        $stmt = $pdo->query("SELECT id, title, author, isbn, sell_price, cover_image, stock_qty FROM books WHERE is_active = 1 ORDER BY title ASC");
        $books = $stmt->fetchAll();
        echo json_encode($books);
    } 
    elseif ($action === 'searchMembers') {
        $query = $_GET['q'] ?? '';
        $stmt = $pdo->prepare("SELECT id, membership_id, full_name, acc_balance, email, phone, membership_plan, plan_expire_date FROM members WHERE full_name LIKE ? OR membership_id LIKE ? LIMIT 10");
        $stmt->execute(["%$query%", "%$query%"]);
        $members = $stmt->fetchAll();
        echo json_encode($members);
    }
    elseif ($action === 'saveOrder') {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$data || empty($data['items'])) {
            throw new Exception("Invalid order data.");
        }

        $pdo->beginTransaction();

        try {
            $invoice_no = 'OTM-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));
            
            // Insert into orders table
            $stmt = $pdo->prepare("INSERT INTO orders (invoice_no, member_id, subtotal, discount, total_amount, payment_status, payment_method, order_status, guest_name, guest_phone, guest_email) VALUES (?, ?, ?, ?, ?, 'Paid', ?, 'Delivered', ?, ?, ?)");
            $stmt->execute([
                $invoice_no,
                $data['memberId'] ?? null,
                $data['subtotal'] ?? ($data['total'] ?? 0),
                $data['discount'] ?? 0,
                $data['total'] ?? 0,
                $data['paymentMethod'] ?? 'Cash',
                $data['guestName'] ?? null,
                $data['guestPhone'] ?? null,
                $data['guestEmail'] ?? null
            ]);
            
            $order_id = $pdo->lastInsertId();

            // Group items to handle duplicates gracefully
            $groupedItems = [];
            foreach ($data['items'] as $item) {
                $bId = $item['id'];
                if (!isset($groupedItems[$bId])) {
                    $groupedItems[$bId] = $item;
                    $groupedItems[$bId]['purchase_qty'] = 0;
                }
                $groupedItems[$bId]['purchase_qty']++;
            }

            // Insert items and decrease stock
            foreach ($groupedItems as $item) {
                $price = $item['sell_price'] ?? $item['price'] ?? 0;
                $qty = $item['purchase_qty'];
                $total_price = $price * $qty;
                
                // Insert order item
                $stmt = $pdo->prepare("INSERT INTO order_items (order_id, book_id, quantity, unit_price, total_price) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$order_id, $item['id'], $qty, $price, $total_price]);

                // Update stock
                $stmt = $pdo->prepare("UPDATE books SET stock_qty = stock_qty - ? WHERE id = ?");
                $stmt->execute([$qty, $item['id']]);
            }

            // Record transaction for members OR handling Guest Email Notification
            if ($data['memberId']) {
                $stmt = $pdo->prepare("INSERT INTO transactions (member_id, amount, type, description, reference_id) VALUES (?, ?, 'Purchase', 'Book purchase at terminal', ?)");
                $stmt->execute([$data['memberId'], $data['total'], $invoice_no]);

                // If Wallet, deduct from member balance
                if ($data['paymentMethod'] === 'Wallet') {
                    // Re-verify balance using a row lock for safety
                    $stmt = $pdo->prepare("SELECT acc_balance FROM members WHERE id = ? FOR UPDATE");
                    $stmt->execute([$data['memberId']]);
                    $balance = $stmt->fetchColumn();
                    
                    if ($balance < $data['total']) {
                        throw new Exception("Insufficient member balance. Available: ৳$balance");
                    }

                    $stmt = $pdo->prepare("UPDATE members SET acc_balance = acc_balance - ? WHERE id = ?");
                    $stmt->execute([$data['total'], $data['memberId']]);
                }

                // Send Email Notification for Members
                $mStmt = $pdo->prepare("SELECT full_name, email FROM members WHERE id = ?");
                $mStmt->execute([$data['memberId']]);
                $member = $mStmt->fetch();

                if ($member && !empty($member['email'])) {
                    $notif_payload = [
                        'name' => $member['full_name'],
                        'invoice_no' => $invoice_no,
                        'amount' => $data['total'],
                        'address' => 'In-person purchase at POS Terminal'
                    ];
                    queueNotification($pdo, $member['email'], 'order_placed', $notif_payload);
                }
            } else {
                // Guest Email Notification
                if (!empty($data['guestEmail'])) {
                    $notif_payload = [
                        'name' => $data['guestName'] ?? 'Guest Customer',
                        'invoice_no' => $invoice_no,
                        'amount' => $data['total'] ?? 0,
                        'address' => 'In-person purchase at POS Terminal'
                    ];
                    queueNotification($pdo, $data['guestEmail'], 'order_placed', $notif_payload);
                }
            }

            $pdo->commit();
            echo json_encode(['success' => true, 'invoice_no' => $invoice_no]);

        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
    elseif ($action === 'saveBorrow') {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$data || empty($data['items']) || !$data['memberId']) {
            throw new Exception("Invalid borrow data.");
        }

        $pdo->beginTransaction();

        try {
            $booksList = [];
            foreach ($data['items'] as $item) {
                // Verify stock again
                $stmt = $pdo->prepare("SELECT stock_qty FROM books WHERE id = ? FOR UPDATE");
                $stmt->execute([$item['id']]);
                $stock = $stmt->fetchColumn();

                if ($stock <= 0) {
                    throw new Exception("Book '{$item['title']}' is out of stock.");
                }

                // Insert into borrows table
                $stmt = $pdo->prepare("INSERT INTO borrows (member_id, book_id, borrow_date, due_date, status) VALUES (?, ?, CURRENT_TIMESTAMP, ?, 'Active')");
                $stmt->execute([
                    $data['memberId'],
                    $item['id'],
                    $data['dueDate']
                ]);

                // Update stock
                $stmt = $pdo->prepare("UPDATE books SET stock_qty = stock_qty - 1 WHERE id = ?");
                $stmt->execute([$item['id']]);
                
                $booksList[] = $item['title'];
            }

            // Send Email Notification for Borrowing
            $mStmt = $pdo->prepare("SELECT full_name, email FROM members WHERE id = ?");
            $mStmt->execute([$data['memberId']]);
            $member = $mStmt->fetch();

            if ($member && !empty($member['email'])) {
                $notif_payload = [
                    'name' => $member['full_name'],
                    'invoice_no' => 'BORROW-' . date('Ymd'),
                    'book_title' => implode(", ", $booksList),
                    'due_date' => $data['dueDate']
                ];
                queueNotification($pdo, $member['email'], 'borrow_active', $notif_payload);
            }

            $pdo->commit();
            echo json_encode(['success' => true]);

        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
    elseif ($action === 'registerMember') {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (empty($data['full_name']) || empty($data['phone'])) {
            throw new Exception("Name and Phone are required.");
        }

        // Check if phone already exists
        $stmt = $pdo->prepare("SELECT id FROM members WHERE phone = ?");
        $stmt->execute([$data['phone']]);
        if ($stmt->fetch()) {
            throw new Exception("A member with this phone number already exists.");
        }

        // Generate Membership ID (OM-RANDOM)
        $membership_id = 'OM-' . strtoupper(substr(md5(uniqid()), 0, 8));
        $temp_password = password_hash('123456', PASSWORD_DEFAULT); // Default temp password

        $stmt = $pdo->prepare("INSERT INTO members (membership_id, full_name, email, phone, password, membership_plan, acc_balance) VALUES (?, ?, ?, ?, ?, 'None', 0)");
        $stmt->execute([
            $membership_id,
            $data['full_name'],
            $data['email'] ?? '',
            $data['phone'],
            $temp_password
        ]);
        
        $memberId = $pdo->lastInsertId();

        // Send Welcome Email if email is provided
        if (!empty($data['email'])) {
            $notif_payload = [
                'name' => $data['full_name'],
                'invoice_no' => $membership_id, // Using this for ID display in email
                'amount' => 0,
                'address' => 'Temporary Password: 123456. Please change it after login.'
            ];
            // Reusing order_placed for now or we could add a new template, but order_placed is generic enough with name.
            // Better to use generic update
            queueNotification($pdo, $data['email'], 'account_created', $notif_payload);
        }

        echo json_encode([
            'success' => true, 
            'member' => [
                'id' => $memberId,
                'membership_id' => $membership_id,
                'full_name' => $data['full_name'],
                'acc_balance' => 0,
                'email' => $data['email'] ?? '',
                'phone' => $data['phone'],
                'membership_plan' => 'None',
                'plan_expire_date' => null
            ]
        ]);
    }
    elseif ($action === 'getBorrowedBooks') {
        $memberId = $_GET['memberId'] ?? '';
        if (empty($memberId)) throw new Exception("Member ID is required.");
        
        $stmt = $pdo->prepare("SELECT b.id as borrow_id, bk.id as book_id, bk.title, bk.author, b.borrow_date, b.due_date FROM borrows b JOIN books bk ON b.book_id = bk.id WHERE b.member_id = ? AND b.status = 'Active' ORDER BY b.borrow_date DESC");
        $stmt->execute([$memberId]);
        echo json_encode($stmt->fetchAll());
    }
    elseif ($action === 'returnBooks') {
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['borrowIds'])) throw new Exception("No books selected for return.");

        $pdo->beginTransaction();
        try {
            foreach ($data['borrowIds'] as $borrowId) {
                // Get book_id
                $stmt = $pdo->prepare("SELECT book_id FROM borrows WHERE id = ?");
                $stmt->execute([$borrowId]);
                $bookId = $stmt->fetchColumn();

                // Update borrow record
                $stmt = $pdo->prepare("UPDATE borrows SET status = 'Returned', return_date = CURRENT_TIMESTAMP WHERE id = ?");
                $stmt->execute([$borrowId]);

                // Increase stock
                $stmt = $pdo->prepare("UPDATE books SET stock_qty = stock_qty + 1 WHERE id = ?");
                $stmt->execute([$bookId]);
            }
            $pdo->commit();
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
    elseif ($action === 'getOrders') {
        $stmt = $pdo->query("SELECT o.*, m.full_name as member_name FROM orders o LEFT JOIN members m ON o.member_id = m.id ORDER BY o.order_date DESC LIMIT 50");
        echo json_encode(['success' => true, 'orders' => $stmt->fetchAll()]);
    }
    elseif ($action === 'getOrderDetails') {
        $orderId = $_GET['id'] ?? '';
        if (empty($orderId)) throw new Exception("Order ID is required.");
        
        // Get order
        $stmt = $pdo->prepare("SELECT o.*, m.full_name as member_name, m.membership_id FROM orders o LEFT JOIN members m ON o.member_id = m.id WHERE o.id = ?");
        $stmt->execute([$orderId]);
        $order = $stmt->fetch();
        
        if (!$order) throw new Exception("Order not found.");
        
        // Get items
        $stmt = $pdo->prepare("SELECT oi.*, b.title as book_title FROM order_items oi JOIN books b ON oi.book_id = b.id WHERE oi.order_id = ?");
        $stmt->execute([$orderId]);
        $items = $stmt->fetchAll();
        
        echo json_encode(['success' => true, 'order' => $order, 'items' => $items]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
