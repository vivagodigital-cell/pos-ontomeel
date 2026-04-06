<?php
// unify_categories.php
require_once __DIR__ . '/../../api/config/database.php';

echo "<h2>Unifying Categories Schema...</h2>";

try {
    $pdo->beginTransaction();

    // 1. Check if 'categories' table has 'name' column (it does based on inspection)
    // 2. Transfer unique names from 'item_categories' to 'categories'
    $stmt = $pdo->query("SELECT name FROM item_categories");
    $itemCats = $stmt->fetchAll(PDO::FETCH_COLUMN);

    foreach ($itemCats as $name) {
        $check = $pdo->prepare("SELECT id FROM categories WHERE name = ?");
        $check->execute([$name]);
        if (!$check->fetch()) {
            $ins = $pdo->prepare("INSERT INTO categories (name, slug) VALUES (?, ?)");
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
            $ins->execute([$name, $slug]);
            echo "Migrated category: $name to 'categories' table. <br>";
        }
    }

    // 3. Update inventory_items.item_type to point to 'categories' IDs instead of 'item_categories' IDs
    // We need to map by name
    $stmt = $pdo->query("SELECT id, name FROM categories");
    $allCats = $stmt->fetchAll();
    
    foreach ($allCats as $cat) {
        // Find if there was an item_category with this name
        $oldStmt = $pdo->prepare("SELECT id FROM item_categories WHERE name = ?");
        $oldStmt->execute([$cat['name']]);
        $oldId = $oldStmt->fetchColumn();

        if ($oldId) {
            $upd = $pdo->prepare("UPDATE inventory_items SET item_type = ? WHERE item_type = ?");
            $upd->execute([$cat['id'], $oldId]);
            echo "Updated inventory items for '{$cat['name']}' to use new category ID: {$cat['id']}. <br>";
        }
    }

    // 4. Specifically fix the 'Books' mapping for the 'books' table
    $stmt = $pdo->prepare("SELECT id FROM categories WHERE name = 'Books'");
    $stmt->execute();
    $booksCatId = $stmt->fetchColumn();
    if ($booksCatId) {
        $pdo->exec("UPDATE books SET category_id = $booksCatId WHERE category_id IS NULL OR category_id = 0");
        echo "Ensured books are mapped to 'Books' category (ID: $booksCatId). <br>";
    }

    $pdo->commit();
    echo "<h3 style='color:green;'>Unification completed! You should now delete 'item_categories' table.</h3>";
} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo "<h3 style='color:red;'>Unification failed: " . $e->getMessage() . "</h3>";
}
