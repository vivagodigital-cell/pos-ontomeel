<?php
// api/controllers/DashboardController.php
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

try {
    // Get Active Borrows
    $stmt = $pdo->query("SELECT COUNT(*) FROM borrows WHERE status NOT IN ('Returned', 'Cancelled')");
    $active_borrows = $stmt->fetchColumn();

    // Get Today's Sales
    $stmt = $pdo->query("SELECT SUM(total_amount) FROM orders WHERE DATE(order_date) = CURDATE()");
    $today_sales = $stmt->fetchColumn() ?: 0;

    // Get Total Members
    $stmt = $pdo->query("SELECT COUNT(*) FROM members");
    $total_members = $stmt->fetchColumn();

    // Get Overdue Books
    $stmt = $pdo->query("SELECT COUNT(*) FROM borrows WHERE due_date < CURDATE() AND status NOT IN ('Returned', 'Cancelled')");
    $overdue_books = $stmt->fetchColumn();

    echo json_encode([
        'active_borrows' => $active_borrows,
        'today_sales' => number_format($today_sales, 2),
        'total_members' => $total_members,
        'overdue_books' => $overdue_books
    ]);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
