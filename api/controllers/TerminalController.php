<?php
// api/controllers/TerminalController.php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../shared/notification_helper.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

try {
    if ($action === 'getBooks') {
        // Fetch books and join with categories for names
        $stmt_b = $pdo->query("SELECT b.id, b.title, b.author, b.isbn, b.sell_price, b.cover_image, b.stock_qty, c.name as category_name, 'Book' as item_type, 0 as _isInventory 
                               FROM books b 
                               LEFT JOIN categories c ON b.category_id = c.id
                               WHERE b.is_active = 1");
        $books = $stmt_b->fetchAll();

        // Fetch generic inventory items, mapping to product structure and joining categories
        $stmt_i = $pdo->query("SELECT i.id, i.item_name as title, '' as author, i.barcode as isbn, i.sell_price, '' as cover_image, i.quantity as stock_qty, c.name as category_name, 'Inventory' as item_type, 1 as _isInventory 
                               FROM inventory_items i 
                               LEFT JOIN categories c ON i.item_type = c.id
                               WHERE i.is_active = 1");
        $items = $stmt_i->fetchAll();

        $all = array_merge($books, $items);
        
        // Grouping to ensure unique items in Terminal display (optional but recommended)
        $grouped = [];
        foreach ($all as $itm) {
            $key = $itm['title'] . ($itm['isbn'] ?? '');
            if (!isset($grouped[$key])) {
                $grouped[$key] = $itm;
            } else {
                $grouped[$key]['stock_qty'] += $itm['stock_qty'];
            }
        }

        echo json_encode(array_values($grouped));
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

            // Handle conditional order date (for backdating)
            $orderDateValue = !empty($data['orderDate']) ? $data['orderDate'] : date('Y-m-d H:i:s');

            // Insert into orders table
            $stmt = $pdo->prepare("INSERT INTO orders (invoice_no, member_id, subtotal, discount, total_amount, payment_status, payment_method, order_status, guest_name, guest_phone, guest_email, order_date, staff_id, staff_name, order_type) VALUES (?, ?, ?, ?, ?, 'Paid', ?, 'Delivered', ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $invoice_no,
                $data['memberId'] ?? null,
                $data['subtotal'] ?? ($data['total'] ?? 0),
                $data['discount'] ?? 0,
                $data['total'] ?? 0,
                $data['paymentMethod'] ?? 'Cash',
                $data['guestName'] ?? null,
                $data['guestPhone'] ?? null,
                $data['guestEmail'] ?? null,
                $orderDateValue,
                $data['staffId'] ?? null,
                $data['staffName'] ?? 'Admin',
                $data['orderType'] ?? 'Sale'
            ]);

            $order_id = $pdo->lastInsertId();

            // Group items to handle duplicates gracefully and respect quantities from frontend
            $groupedItems = [];
            foreach ($data['items'] as $item) {
                // Use composite key to avoid ID collisions between books and inventory
                $isInv = isset($item['_isInventory']) && $item['_isInventory'] == 1;
                $key = ($isInv ? 'inv_' : 'book_') . $item['id'];
                
                if (!isset($groupedItems[$key])) {
                    $groupedItems[$key] = $item;
                    $groupedItems[$key]['purchase_qty'] = 0;
                }
                $groupedItems[$key]['purchase_qty'] += (int)($item['qty'] ?? 1);
            }

            // Insert items and decrease stock
            foreach ($groupedItems as $item) {
                $price = $item['sell_price'] ?? $item['price'] ?? 0;
                $qty = $item['purchase_qty'];
                $total_price = $price * $qty;
                $item_type = $item['item_type'] ?? 'Book';
                $isInv = isset($item['_isInventory']) && $item['_isInventory'] == 1;

                // Insert order item
                $stmt = $pdo->prepare("INSERT INTO order_items (order_id, book_id, quantity, unit_price, total_price, item_type) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$order_id, $item['id'], $qty, $price, $total_price, $item_type]);

                // Update stock based on type
                if ($isInv) {
                    $stmt = $pdo->prepare("UPDATE inventory_items SET quantity = quantity - ? WHERE id = ?");
                } else {
                    $stmt = $pdo->prepare("UPDATE books SET stock_qty = stock_qty - ? WHERE id = ?");
                }
                $stmt->execute([$qty, $item['id']]);
            }

            // Record transaction for members OR handling Guest Email Notification
            if ($data['memberId']) {
                $stmt = $pdo->prepare("INSERT INTO transactions (member_id, amount, type, description, reference_id) VALUES (?, ?, 'Purchase', 'Book purchase at terminal', ?)");
                $stmt->execute([$data['memberId'], $data['total'], $invoice_no]);

                // Handle Member Wallet/Fund deduction if used in split payment
                $walletAmount = floatval($data['walletAmount'] ?? 0);
                if ($walletAmount > 0) {
                    // Re-verify balance using a row lock for safety
                    $stmt = $pdo->prepare("SELECT acc_balance FROM members WHERE id = ? FOR UPDATE");
                    $stmt->execute([$data['memberId']]);
                    $balance = $stmt->fetchColumn();

                    if ($balance < $walletAmount) {
                        throw new Exception("Insufficient member balance for fund allocation. Available: ৳$balance");
                    }

                    $stmt = $pdo->prepare("UPDATE members SET acc_balance = acc_balance - ? WHERE id = ?");
                    $stmt->execute([$walletAmount, $data['memberId']]);
                }

                // Send Email Notification for Members
                $mStmt = $pdo->prepare("SELECT full_name, email, phone FROM members WHERE id = ?");
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

                // Send SMS Notification for Members
                if ($member && !empty($member['phone'])) {
                    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
                    $host = $_SERVER['HTTP_HOST'] ?? 'ontomeel.com';
                    $baseUrl = getenv('BULKSMS_BASE_URL') ?: "$protocol://$host";
                    $link = $baseUrl . '/' . $invoice_no;
                    
                    // Build item summary for SMS
                    $items_summary = [];
                    foreach ($groupedItems as $item) {
                        $items_summary[] = $item['title'] . " (৳" . number_format($item['sell_price'] * $item['purchase_qty'], 2) . ")";
                    }
                    $items_text = implode(", ", $items_summary);
                    $total_amount = number_format($data['total'], 2);

                    $sms_message = "Thank you for your order. Your Order number is $invoice_no. Your Order contains: $items_text. Total Amount: ৳$total_amount. Love From Ontomeel.";
                    
                    $sms_result = send_sms_instantly($member['phone'], $sms_message);
                    if (!$sms_result['success']) {
                        error_log("Failed to send SMS to member " . $member['phone'] . ": " . json_encode($sms_result));
                    }
                }
            }
            else {
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

                // Guest SMS Notification
                if (!empty($data['guestPhone'])) {
                    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
                    $host = $_SERVER['HTTP_HOST'] ?? 'ontomeel.com';
                    $baseUrl = getenv('BULKSMS_BASE_URL') ?: "$protocol://$host";
                    $link = $baseUrl . '/' . $invoice_no;

                    // Build item summary for SMS
                    $items_summary = [];
                    foreach ($groupedItems as $item) {
                        $items_summary[] = $item['title'] . " (৳" . number_format($item['sell_price'] * $item['purchase_qty'], 2) . ")";
                    }
                    $items_text = implode(", ", $items_summary);
                    $total_amount = number_format($data['total'], 2);

                    $sms_message = "Thank you for your order. Your Order number is $invoice_no. Your Order contains: $items_text. Total Amount: ৳$total_amount. Love From Ontomeel.";

                    $sms_result = send_sms_instantly($data['guestPhone'], $sms_message);
                    if (!$sms_result['success']) {
                        error_log("Failed to send SMS to guest " . $data['guestPhone'] . ": " . json_encode($sms_result));
                    }
                }
            }

            $pdo->commit();
            echo json_encode(['success' => true, 'invoice_no' => $invoice_no]);

        }
        catch (Exception $e) {
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
                $qty = (int)($item['qty'] ?? 1);
                
                for ($i = 0; $i < $qty; $i++) {
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
                }

                $bookTitleWithQty = $qty > 1 ? "{$item['title']} (x{$qty})" : $item['title'];
                $booksList[] = $bookTitleWithQty;
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

        }
        catch (Exception $e) {
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

        // Generate Membership ID (OM-YYYY-XXXX format)
        $year = date('Y');
        $membership_id = 'OM-' . $year . '-' . strtoupper(substr(md5(uniqid()), -4));
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
        if (empty($memberId))
            throw new Exception("Member ID is required.");

        $stmt = $pdo->prepare("SELECT b.id as borrow_id, bk.id as book_id, bk.title, bk.author, b.borrow_date, b.due_date FROM borrows b JOIN books bk ON b.book_id = bk.id WHERE b.member_id = ? AND b.status = 'Active' ORDER BY b.borrow_date DESC");
        $stmt->execute([$memberId]);
        echo json_encode($stmt->fetchAll());
    }
    elseif ($action === 'returnBooks') {
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['borrowIds']))
            throw new Exception("No books selected for return.");

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
        }
        catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
    elseif ($action === 'getOrders') {
        $search = $_GET['search'] ?? '';
        $status = $_GET['status'] ?? '';
        $method = $_GET['method'] ?? '';
        $staff = $_GET['staff'] ?? '';
        $date_start = $_GET['dateStart'] ?? '';
        $date_end = $_GET['dateEnd'] ?? '';
        $sort = $_GET['sort'] ?? 'date_desc';

        $params = [];
        $query = "SELECT o.*, m.full_name as member_name, m.phone as member_phone, m.email as member_email,
                  (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as item_count,
                  (SELECT COALESCE(bk.title, inv.item_name) FROM order_items oi 
                   LEFT JOIN books bk ON oi.book_id = bk.id AND (oi.item_type = 'Book' OR oi.item_type IS NULL)
                   LEFT JOIN inventory_items inv ON oi.book_id = inv.id AND (oi.item_type != 'Book')
                   WHERE oi.order_id = o.id LIMIT 1) as first_item
                  FROM orders o LEFT JOIN members m ON o.member_id = m.id WHERE 1=1";

        if (!empty($search)) {
            $query .= " AND (o.invoice_no LIKE ? OR m.full_name LIKE ? OR o.guest_name LIKE ? OR m.phone LIKE ? OR o.guest_phone LIKE ?)";
            $sh = "%$search%";
            array_push($params, $sh, $sh, $sh, $sh, $sh);
        }

        if (!empty($status)) {
            $query .= " AND o.payment_status = ?";
            $params[] = $status;
        }

        if (!empty($method)) {
            $query .= " AND o.payment_method LIKE ?";
            $params[] = "%$method%";
        }

        if (!empty($staff)) {
            $query .= " AND (o.staff_name LIKE ? OR o.staff_id = ?)";
            $params[] = "%$staff%";
            $params[] = $staff;
        }

        if (!empty($date_start)) {
            $query .= " AND o.order_date >= ?";
            $params[] = $date_start . ' 00:00:00';
        }

        if (!empty($date_end)) {
            $query .= " AND o.order_date <= ?";
            $params[] = $date_end . ' 23:59:59';
        }

        switch($sort) {
            case 'date_asc': $query .= " ORDER BY o.order_date ASC"; break;
            case 'amount_asc': $query .= " ORDER BY o.total_amount ASC"; break;
            case 'amount_desc': $query .= " ORDER BY o.total_amount DESC"; break;
            default: $query .= " ORDER BY o.order_date DESC"; break;
        }

        $query .= " LIMIT 100";

        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        echo json_encode(['success' => true, 'orders' => $stmt->fetchAll()]);
    }
    elseif ($action === 'orderAction') {
        $data = json_decode(file_get_contents('php://input'), true);
        $orderId = $data['id'] ?? '';
        $type = $data['type'] ?? ''; // 'refund', 'cancel', 'delete'

        if (empty($orderId) || empty($type)) throw new Exception("Invalid parameters.");

        $pdo->beginTransaction();
        try {
            // Revert Stock for all destructive actions
            $stmt = $pdo->prepare("SELECT book_id, quantity, item_type FROM order_items WHERE order_id = ?");
            $stmt->execute([$orderId]);
            $items = $stmt->fetchAll();

            foreach ($items as $item) {
                if ($item['item_type'] === 'Book' || empty($item['item_type'])) {
                    $stmt = $pdo->prepare("UPDATE books SET stock_qty = stock_qty + ? WHERE id = ?");
                    $stmt->execute([$item['quantity'], $item['book_id']]);
                } else {
                    $stmt = $pdo->prepare("UPDATE inventory_items SET quantity = quantity + ? WHERE id = ?");
                    $stmt->execute([$item['quantity'], $item['book_id']]);
                }
            }

            if ($type === 'refund' || $type === 'cancel') {
                $status = ($type === 'refund') ? 'Refunded' : 'Cancelled';
                // Update Order Status
                $stmt = $pdo->prepare("UPDATE orders SET payment_status = ?, order_status = ? WHERE id = ?");
                $stmt->execute([$status, $status, $orderId]);
            } 
            elseif ($type === 'delete') {
                // Delete items first
                $stmt = $pdo->prepare("DELETE FROM order_items WHERE order_id = ?");
                $stmt->execute([$orderId]);
                // Delete order
                $stmt = $pdo->prepare("DELETE FROM orders WHERE id = ?");
                $stmt->execute([$orderId]);
            }

            $pdo->commit();
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
    elseif ($action === 'getOrderDetails') {
        $orderId = $_GET['id'] ?? '';
        if (empty($orderId))
            throw new Exception("Order ID is required.");

        // Get order
        $stmt = $pdo->prepare("SELECT o.*, m.full_name as member_name, m.membership_id FROM orders o LEFT JOIN members m ON o.member_id = m.id WHERE o.id = ?");
        $stmt->execute([$orderId]);
        $order = $stmt->fetch();

        if (!$order)
            throw new Exception("Order not found.");

        // Get items joined with both books and inventory items for the correct title
        $stmt = $pdo->prepare("SELECT oi.*, COALESCE(b.title, i.item_name) as book_title 
                              FROM order_items oi 
                              LEFT JOIN books b ON oi.book_id = b.id AND (oi.item_type = 'Book' OR oi.item_type IS NULL)
                              LEFT JOIN inventory_items i ON oi.book_id = i.id AND (oi.item_type != 'Book')
                              WHERE oi.order_id = ?");
        $stmt->execute([$orderId]);
        $items = $stmt->fetchAll();

        echo json_encode(['success' => true, 'order' => $order, 'items' => $items]);
    }
    elseif ($action === 'resendSMS') {
        $orderId = $_GET['id'] ?? '';
        if (empty($orderId)) throw new Exception("Order ID is required.");

        $stmt = $pdo->prepare("SELECT o.invoice_no, o.total_amount, m.phone as member_phone, o.guest_phone FROM orders o LEFT JOIN members m ON o.member_id = m.id WHERE o.id = ?");
        $stmt->execute([$orderId]);
        $order = $stmt->fetch();

        if (!$order) throw new Exception("Order not found.");

        $phone = $order['member_phone'] ?: $order['guest_phone'];
        if (empty($phone)) throw new Exception("No phone number found for this order.");

        // Get items for the SMS template
        $stmt = $pdo->prepare("SELECT oi.quantity, oi.total_price, COALESCE(b.title, i.item_name) as title 
                              FROM order_items oi 
                              LEFT JOIN books b ON oi.book_id = b.id AND (oi.item_type = 'Book' OR oi.item_type IS NULL)
                              LEFT JOIN inventory_items i ON oi.book_id = i.id AND (oi.item_type != 'Book')
                              WHERE oi.order_id = ?");
        $stmt->execute([$orderId]);
        $items = $stmt->fetchAll();

        $items_summary = [];
        foreach ($items as $item) {
            $items_summary[] = $item['title'] . " (৳" . number_format($item['total_price'], 2) . ")";
        }
        $items_text = implode(", ", $items_summary);
        $total_amount = number_format($order['total_amount'], 2);
        $invoice_no = $order['invoice_no'];

        $sms_message = "Thank you for your order. Your Order number is $invoice_no. Your Order contains: $items_text. Total Amount: ৳$total_amount. Love From Ontomeel.";

        $sms_result = send_sms_instantly($phone, $sms_message);
        echo json_encode($sms_result);
    }
}
catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
