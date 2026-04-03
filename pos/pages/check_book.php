<?php
require_once __DIR__ . '/../../api/config/database.php';
$stmt = $pdo->prepare("SELECT id, title, is_active, cover_image, item_type FROM books WHERE id = 3646");
$stmt->execute();
print_r($stmt->fetch());
?>
