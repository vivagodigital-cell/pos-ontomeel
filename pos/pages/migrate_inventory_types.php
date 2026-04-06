<?php
// migrate_inventory_types.php
require_once __DIR__ . '/../../api/config/database.php';

echo "<h2>Starting Inventory Migration...</h2>";

try {
    $pdo->beginTransaction();

    // 1. Ensure item_categories table exists
    $pdo->exec("CREATE TABLE IF NOT EXISTS `item_categories` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `name` VARCHAR(100) NOT NULL UNIQUE,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // 2. Identify unique item_types in inventory_items
    // Check if item_type column is still a string (VARCHAR) before trying to read it as such
    $checkCol = $pdo->query("SHOW COLUMNS FROM inventory_items LIKE 'item_type'")->fetch();
    if ($checkCol && strpos($checkCol['Type'], 'varchar') !== false) {
        $stmt = $pdo->query("SELECT DISTINCT item_type FROM inventory_items WHERE item_type IS NOT NULL AND item_type != ''");
        $typesFound = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // 3. Populate item_categories with these names if missing
        foreach ($typesFound as $type) {
            $stmt = $pdo->prepare("SELECT id FROM item_categories WHERE name = ?");
            $stmt->execute([$type]);
            if (!$stmt->fetch()) {
                $stmt = $pdo->prepare("INSERT INTO item_categories (name) VALUES (?)");
                $stmt->execute([$type]);
                echo "Added category: $type <br>";
            }
        }

        // 4. Add temporary category_id column
        $pdo->exec("ALTER TABLE inventory_items ADD COLUMN IF NOT EXISTS category_id INT DEFAULT NULL");

        // 5. Update category_id values based on name
        $stmt = $pdo->query("SELECT id, name FROM item_categories");
        $categories = $stmt->fetchAll();
        foreach ($categories as $cat) {
            $stmt = $pdo->prepare("UPDATE inventory_items SET category_id = ? WHERE item_type = ?");
            $stmt->execute([$cat['id'], $cat['name']]);
        }

        // 6. Drop item_type and rename category_id to item_type
        $pdo->exec("ALTER TABLE inventory_items DROP COLUMN item_type");
        $pdo->exec("ALTER TABLE inventory_items CHANGE category_id item_type INT DEFAULT NULL");
        
        echo "Migration from String to ID completed for inventory_items.item_type column.<br>";
    } else {
        echo "item_type column is already INT or migration was already done. Skipping conversion.<br>";
    }

    $pdo->commit();
    echo "<h3 style='color:green;'>Migration completed successfully!</h3>";
    echo "<p>You can now delete this file.</p>";
} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo "<h3 style='color:red;'>Migration failed: " . $e->getMessage() . "</h3>";
}
