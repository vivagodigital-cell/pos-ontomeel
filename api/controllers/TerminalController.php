<?php
// api/controllers/TerminalController.php
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

try {
    if ($action === 'getBooks') {
        $stmt = $pdo->query("SELECT id, title, author, sell_price, cover_image, stock_qty FROM books WHERE is_active = 1 ORDER BY title ASC");
        $books = $stmt->fetchAll();
        echo json_encode($books);
    } 
    elseif ($action === 'searchMembers') {
        $query = $_GET['q'] ?? '';
        $stmt = $pdo->prepare("SELECT id, membership_id, full_name, acc_balance FROM members WHERE full_name LIKE ? OR membership_id LIKE ? LIMIT 10");
        $stmt->execute(["%$query%", "%$query%"]);
        $members = $stmt->fetchAll();
        echo json_encode($members);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
