<?php
// api/controllers/MemberController.php
require_once __DIR__ . '/../config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        ini_set('session.cookie_secure', 1);
    }
    session_start();
}
if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized access. Please log in.']);
    exit;
}

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

try {
    if ($action === 'list') {
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
        $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
        
        $stmt = $pdo->prepare("SELECT id, membership_id, full_name, email, phone, address, membership_plan, acc_balance, is_active, plan_expire_date, student_plan_expire_date, created_at, updated_at 
                               FROM members 
                               ORDER BY created_at DESC 
                               LIMIT ? OFFSET ?");
        $stmt->execute([$limit, $offset]);
        $members = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'members' => $members]);
    } 
    elseif ($action === 'stats') {
        // Total Members
        $total = $pdo->query("SELECT COUNT(*) FROM members")->fetchColumn();
        
        // Active & Inactive Members
        $active_count = $pdo->query("SELECT COUNT(*) FROM members WHERE is_active = 1")->fetchColumn();
        $inactive_count = $pdo->query("SELECT COUNT(*) FROM members WHERE is_active = 0")->fetchColumn();

        // Plan Distribution
        $plans = $pdo->query("SELECT membership_plan, COUNT(*) as count FROM members GROUP BY membership_plan")->fetchAll(PDO::FETCH_ASSOC);
        
        // Total Balance
        $total_balance = $pdo->query("SELECT SUM(acc_balance) FROM members")->fetchColumn() ?: 0;

        echo json_encode([
            'success' => true,
            'stats' => [
                'total' => $total,
                'active' => $active_count,
                'inactive' => $inactive_count,
                'plans' => $plans,
                'total_balance' => number_format($total_balance, 2)
            ]
        ]);
    }
    elseif ($action === 'search') {
        $q = trim($_GET['q'] ?? '');
        $stmt = $pdo->prepare("SELECT id, membership_id, full_name, email, phone, address, membership_plan, acc_balance, is_active, plan_expire_date, student_plan_expire_date, created_at, updated_at 
                               FROM members 
                               WHERE full_name LIKE ? OR membership_id LIKE ? OR phone LIKE ? OR email LIKE ?
                               ORDER BY created_at DESC 
                               LIMIT 30");
        $stmt->execute(["%$q%", "%$q%", "%$q%", "%$q%"]);
        echo json_encode(['success' => true, 'members' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    }
    elseif ($action === 'get') {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) {
            throw new Exception("Invalid member ID.");
        }

        // Fetch Member Record
        $stmt = $pdo->prepare("SELECT id, membership_id, full_name, email, phone, address, membership_plan, acc_balance, is_active, plan_expire_date, student_plan_expire_date, created_at, updated_at 
                               FROM members WHERE id = ?");
        $stmt->execute([$id]);
        $member = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$member) {
            throw new Exception("Member not found.");
        }

        // Fetch Recent Borrows & Borrow Stats
        $stmt_borrows = $pdo->prepare("
            SELECT b.id, b.borrow_date, b.due_date, b.return_date, b.status, b.fine_amount, 
                   COALESCE(bk.title, 'Unknown Book') as book_title, bk.isbn 
            FROM borrows b 
            LEFT JOIN books bk ON b.book_id = bk.id 
            WHERE b.member_id = ? 
            ORDER BY b.borrow_date DESC LIMIT 5
        ");
        $stmt_borrows->execute([$id]);
        $recent_borrows = $stmt_borrows->fetchAll(PDO::FETCH_ASSOC);

        $stmt_borrow_counts = $pdo->prepare("SELECT 
            COUNT(*) as total_borrows,
            SUM(CASE WHEN status = 'Active' THEN 1 ELSE 0 END) as active_borrows,
            SUM(CASE WHEN status = 'Overdue' THEN 1 ELSE 0 END) as overdue_borrows
            FROM borrows WHERE member_id = ?");
        $stmt_borrow_counts->execute([$id]);
        $borrow_stats = $stmt_borrow_counts->fetch(PDO::FETCH_ASSOC) ?: [
            'total_borrows' => 0,
            'active_borrows' => 0,
            'overdue_borrows' => 0
        ];

        // Fetch Recent Orders & Spend
        $stmt_orders = $pdo->prepare("
            SELECT id, invoice_no, total_amount, payment_status, payment_method, order_type, order_date as created_at 
            FROM orders 
            WHERE member_id = ? 
            ORDER BY order_date DESC LIMIT 5
        ");
        $stmt_orders->execute([$id]);
        $recent_orders = $stmt_orders->fetchAll(PDO::FETCH_ASSOC);

        $stmt_order_stats = $pdo->prepare("SELECT 
            COUNT(*) as total_orders,
            COALESCE(SUM(total_amount), 0) as total_spent 
            FROM orders WHERE member_id = ? AND payment_status = 'Paid'");
        $stmt_order_stats->execute([$id]);
        $order_stats = $stmt_order_stats->fetch(PDO::FETCH_ASSOC) ?: [
            'total_orders' => 0,
            'total_spent' => 0
        ];

        // Fetch Recent Transactions
        $stmt_tx = $pdo->prepare("
            SELECT id, amount, type, description, reference_id, created_at 
            FROM transactions 
            WHERE member_id = ? 
            ORDER BY created_at DESC LIMIT 5
        ");
        $stmt_tx->execute([$id]);
        $recent_transactions = $stmt_tx->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'member' => $member,
            'borrows' => $recent_borrows,
            'borrow_stats' => $borrow_stats,
            'orders' => $recent_orders,
            'order_stats' => $order_stats,
            'transactions' => $recent_transactions
        ]);
    }
    elseif ($action === 'update') {
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data) {
            throw new Exception("Invalid JSON request body.");
        }

        $id = isset($data['id']) ? (int)$data['id'] : 0;
        $full_name = trim($data['full_name'] ?? '');
        $phone = trim($data['phone'] ?? '');
        $email = trim($data['email'] ?? '');
        $address = trim($data['address'] ?? '');
        $membership_plan = trim($data['membership_plan'] ?? 'None');
        $plan_expire_date = !empty($data['plan_expire_date']) ? trim($data['plan_expire_date']) : null;
        $is_active = isset($data['is_active']) ? (int)$data['is_active'] : 1;
        $new_password = trim($data['password'] ?? '');

        if ($id <= 0) {
            throw new Exception("Invalid member ID.");
        }
        if (empty($full_name)) {
            throw new Exception("Full Name is required.");
        }
        if (empty($phone)) {
            throw new Exception("Phone Number is required.");
        }

        // Validate plan
        $valid_plans = ['None', 'General', 'BookLover', 'Collector', 'Student'];
        if (!in_array($membership_plan, $valid_plans)) {
            $membership_plan = 'None';
        }

        // Check duplicate phone for other members
        $stmt_check = $pdo->prepare("SELECT id FROM members WHERE phone = ? AND id != ?");
        $stmt_check->execute([$phone, $id]);
        if ($stmt_check->fetch()) {
            throw new Exception("A member with this phone number already exists.");
        }

        // Clean up date format if passed YYYY-MM-DD
        if ($plan_expire_date) {
            if (strlen($plan_expire_date) === 10) {
                $plan_expire_date .= ' 23:59:59';
            }
        } else {
            $plan_expire_date = null;
        }

        if (!empty($new_password)) {
            $hashed = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("
                UPDATE members 
                SET full_name = ?, phone = ?, email = ?, address = ?, 
                    membership_plan = ?, plan_expire_date = ?, is_active = ?, password = ? 
                WHERE id = ?
            ");
            $stmt->execute([
                $full_name, $phone, $email ?: null, $address ?: null,
                $membership_plan, $plan_expire_date, $is_active, $hashed, $id
            ]);
        } else {
            $stmt = $pdo->prepare("
                UPDATE members 
                SET full_name = ?, phone = ?, email = ?, address = ?, 
                    membership_plan = ?, plan_expire_date = ?, is_active = ? 
                WHERE id = ?
            ");
            $stmt->execute([
                $full_name, $phone, $email ?: null, $address ?: null,
                $membership_plan, $plan_expire_date, $is_active, $id
            ]);
        }

        // Fetch refreshed record
        $stmt_get = $pdo->prepare("SELECT id, membership_id, full_name, email, phone, address, membership_plan, acc_balance, is_active, plan_expire_date, student_plan_expire_date, created_at, updated_at FROM members WHERE id = ?");
        $stmt_get->execute([$id]);
        $updated_member = $stmt_get->fetch(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'message' => 'Member details updated successfully!',
            'member' => $updated_member
        ]);
    }
    elseif ($action === 'create') {
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data) {
            throw new Exception("Invalid JSON request body.");
        }

        $full_name = trim($data['full_name'] ?? '');
        $phone = trim($data['phone'] ?? '');
        $email = trim($data['email'] ?? '');
        $address = trim($data['address'] ?? '');
        $membership_plan = trim($data['membership_plan'] ?? 'None');
        $plan_expire_date = !empty($data['plan_expire_date']) ? trim($data['plan_expire_date']) : null;
        $is_active = isset($data['is_active']) ? (int)$data['is_active'] : 1;
        $password = trim($data['password'] ?? '123456');

        if (empty($full_name) || empty($phone)) {
            throw new Exception("Full Name and Phone Number are required.");
        }

        $stmt_check = $pdo->prepare("SELECT id FROM members WHERE phone = ?");
        $stmt_check->execute([$phone]);
        if ($stmt_check->fetch()) {
            throw new Exception("A member with this phone number already exists.");
        }

        $valid_plans = ['None', 'General', 'BookLover', 'Collector', 'Student'];
        if (!in_array($membership_plan, $valid_plans)) {
            $membership_plan = 'None';
        }

        if ($plan_expire_date && strlen($plan_expire_date) === 10) {
            $plan_expire_date .= ' 23:59:59';
        } else {
            $plan_expire_date = null;
        }

        $year = date('Y');
        $membership_id = 'OM-' . $year . '-' . strtoupper(substr(md5(uniqid()), -4));
        $hashed = password_hash($password ?: '123456', PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("
            INSERT INTO members (membership_id, full_name, email, phone, password, address, membership_plan, plan_expire_date, is_active, acc_balance) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 0.00)
        ");
        $stmt->execute([
            $membership_id, $full_name, $email ?: null, $phone, $hashed, $address ?: null, $membership_plan, $plan_expire_date, $is_active
        ]);

        $new_id = $pdo->lastInsertId();

        $stmt_get = $pdo->prepare("SELECT id, membership_id, full_name, email, phone, address, membership_plan, acc_balance, is_active, plan_expire_date, student_plan_expire_date, created_at, updated_at FROM members WHERE id = ?");
        $stmt_get->execute([$new_id]);
        $new_member = $stmt_get->fetch(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'message' => 'Member registered successfully!',
            'member' => $new_member
        ]);
    }
    elseif ($action === 'toggleStatus') {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) throw new Exception("Invalid member ID.");

        $stmt = $pdo->prepare("UPDATE members SET is_active = IF(is_active = 1, 0, 1) WHERE id = ?");
        $stmt->execute([$id]);

        $new_status = $pdo->query("SELECT is_active FROM members WHERE id = $id")->fetchColumn();

        echo json_encode([
            'success' => true,
            'is_active' => (int)$new_status,
            'message' => 'Member status updated.'
        ]);
    }
    elseif ($action === 'addFund') {
        $data = json_decode(file_get_contents('php://input'), true);
        $memberId = $data['memberId'] ?? 0;
        $amount = (float)($data['amount'] ?? 0);

        if ($memberId <= 0 || $amount <= 0) {
            throw new Exception("Invalid member or amount.");
        }

        $pdo->beginTransaction();
        try {
            // Update balance
            $stmt = $pdo->prepare("UPDATE members SET acc_balance = acc_balance + ? WHERE id = ?");
            $stmt->execute([$amount, $memberId]);

            // Record transaction
            $stmt = $pdo->prepare("INSERT INTO transactions (member_id, amount, type, description, reference_id) VALUES (?, ?, 'Deposit', 'Account Top-up from Admin Panel', ?)");
            $stmt->execute([$memberId, $amount, 'TOPUP-' . time()]);

            $pdo->commit();

            $new_balance = $pdo->query("SELECT acc_balance FROM members WHERE id = $memberId")->fetchColumn();
            echo json_encode([
                'success' => true,
                'new_balance' => number_format((float)$new_balance, 2)
            ]);
        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    } else {
        throw new Exception("Invalid action requested.");
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
