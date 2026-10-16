<?php
// api/controllers/DashboardController.php
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

try {
    // 1. Borrows: Combine Active & Overdue into a single fast query
    $stmtBorrow = $pdo->query("
        SELECT 
            COUNT(CASE WHEN status NOT IN ('Returned', 'Cancelled') THEN 1 END) as active_borrows,
            COUNT(CASE WHEN due_date < CURDATE() AND status NOT IN ('Returned', 'Cancelled') THEN 1 END) as overdue_books
        FROM borrows
    ");
    $borrowMetrics = $stmtBorrow->fetch(PDO::FETCH_ASSOC);

    // 2. Today's Sales: Index-friendly range condition (order_date >= CURDATE() AND < CURDATE() + INTERVAL 1 DAY)
    $stmtSales = $pdo->query("
        SELECT COALESCE(SUM(total_amount), 0) 
        FROM orders 
        WHERE order_date >= CURDATE() 
          AND order_date < CURDATE() + INTERVAL 1 DAY 
          AND payment_status = 'Paid'
    ");
    $today_sales = (float)$stmtSales->fetchColumn();

    // 3. Total Members
    $total_members = (int)$pdo->query("SELECT COUNT(*) FROM members")->fetchColumn();

    echo json_encode([
        'active_borrows' => (int)($borrowMetrics['active_borrows'] ?? 0),
        'today_sales' => number_format($today_sales, 2),
        'total_members' => $total_members,
        'overdue_books' => (int)($borrowMetrics['overdue_books'] ?? 0)
    ]);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
