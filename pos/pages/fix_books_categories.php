<?php
// fix_books_categories.php
require_once __DIR__ . '/../../api/config/database.php';

try {
    echo "<h1>Fixing Books Category Mapping...</h1>";

    // 1. Ensure 'Books' category exists
    $stmt = $pdo->prepare("SELECT id FROM item_categories WHERE name = 'Books'");
    $stmt->execute();
    $booksCatId = $stmt->fetchColumn();

    if (!$booksCatId) {
        $pdo->exec("INSERT INTO item_categories (name) VALUES ('Books')");
        $booksCatId = $pdo->lastInsertId();
        echo "Created 'Books' category with ID: $booksCatId <br>";
    } else {
        echo "Found 'Books' category with ID: $booksCatId <br>";
    }

    // 2. Update books table category_id where it is NULL or 0
    $stmt = $pdo->prepare("UPDATE books SET category_id = ? WHERE category_id IS NULL OR category_id = 0");
    $stmt->execute([$booksCatId]);
    $affected = $stmt->rowCount();
    echo "Updated $affected books to category 'Books' (ID: $booksCatId). <br>";

    // 3. Verification
    $count = $pdo->query("SELECT COUNT(*) FROM books WHERE category_id = $booksCatId")->fetchColumn();
    echo "Total books now in 'Books' category: $count <br>";

    echo "<h3 style='color:green;'>Books category mapping completed!</h3>";
} catch (Exception $e) {
    echo "<h3 style='color:red;'>Error: " . $e->getMessage() . "</h3>";
}
