<?php
require_once __DIR__ . '/../../api/config/database.php';
$stmt = $pdo->query("SELECT id, title, cover_image FROM books WHERE cover_image IS NOT NULL AND cover_image != '' LIMIT 10");
$books = $stmt->fetchAll();
echo "<h1>Book Cover Paths</h1><table border=1><tr><th>ID</th><th>Title</th><th>Cover Image (DB)</th></tr>";
foreach ($books as $b) {
    echo "<tr><td>{$b['id']}</td><td>{$b['title']}</td><td>{$b['cover_image']}</td></tr>";
}
echo "</table>";
?>
