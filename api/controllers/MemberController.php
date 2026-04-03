<?php
// api/controllers/MemberController.php
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

try {
    if ($action === 'list') {
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
        $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
        
        $stmt = $pdo->prepare("SELECT id, membership_id, full_name, email, phone, membership_plan, acc_balance, plan_expire_date, created_at FROM members ORDER BY created_at DESC LIMIT ? OFFSET ?");
        $stmt->execute([$limit, $offset]);
        $members = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'members' => $members]);
    } 
    elseif ($action === 'stats') {
        // Total Members
        $total = $pdo->query("SELECT COUNT(*) FROM members")->fetchColumn();
        
        // Plan Distribution
        $plans = $pdo->query("SELECT membership_plan, COUNT(*) as count FROM members GROUP BY membership_plan")->fetchAll(PDO::FETCH_ASSOC);
        
        // Total Balance
        $total_balance = $pdo->query("SELECT SUM(acc_balance) FROM members")->fetchColumn() ?: 0;

        echo json_encode([
            'success' => true,
            'stats' => [
                'total' => $total,
                'plans' => $plans,
                'total_balance' => number_format($total_balance, 2)
            ]
        ]);
    }
    elseif ($action === 'search') {
        $q = $_GET['q'] ?? '';
        $stmt = $pdo->prepare("SELECT id, membership_id, full_name, email, phone, membership_plan, acc_balance, plan_expire_date, created_at FROM members WHERE full_name LIKE ? OR membership_id LIKE ? LIMIT 20");
        $stmt->execute(["%$q%", "%$q%"]);
        echo json_encode(['success' => true, 'members' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    }
    elseif ($action === 'addFund') {
        $data = json_decode(file_get_contents('php://input'), true);
        $memberId = $data['memberId'] ?? 0;
        $amount = $data['amount'] ?? 0;

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
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
