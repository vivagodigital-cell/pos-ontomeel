<?php
// verify_migration.php
require_once __DIR__ . '/../../api/config/database.php';

try {
    echo "<h1>Migration Verification Report</h1>";

    // 1. Check item_categories
    $stmt = $pdo->query("SHOW TABLES LIKE 'item_categories'");
    if ($stmt->fetch()) {
        $count = $pdo->query("SELECT COUNT(*) FROM item_categories")->fetchColumn();
        echo "<p style='color:green;'>✓ item_categories table exists with $count categories.</p>";
        
        $cats = $pdo->query("SELECT * FROM item_categories")->fetchAll();
        echo "<ul>";
        foreach ($cats as $c) {
            echo "<li>ID: {$c['id']} - Name: {$c['name']}</li>";
        }
        echo "</ul>";
    } else {
        echo "<p style='color:red;'>✗ item_categories table does not exist.</p>";
    }

    // 2. Check inventory_items column type
    $stmt = $pdo->query("SHOW COLUMNS FROM inventory_items LIKE 'item_type'");
    $col = $stmt->fetch();
    if ($col) {
        $type = $col['Type'];
        if (strpos($type, 'int') !== false) {
             echo "<p style='color:green;'>✓ inventory_items.item_type is now $type (Correct - INT).</p>";
        } else {
             echo "<p style='color:orange;'>! inventory_items.item_type is still $type (Should be INT).</p>";
        }
    } else {
        echo "<p style='color:red;'>✗ inventory_items.item_type column not found.</p>";
    }

    // 3. Check data mapping
    $total = $pdo->query("SELECT COUNT(*) FROM inventory_items")->fetchColumn();
    $nulls = $pdo->query("SELECT COUNT(*) FROM inventory_items WHERE item_type IS NULL OR item_type = 0")->fetchColumn();
    
    if ($total > 0 && $nulls == 0) {
        echo "<p style='color:green;'>✓ All $total items have been correctly mapped to category IDs.</p>";
    } elseif ($total > 0 && $nulls > 0) {
        echo "<p style='color:orange;'>! Only " . ($total - $nulls) . " out of $total items were mapped. $nulls items have missing or 0 IDs.</p>";
    } else {
        echo "<p>No items found in inventory_items to verify data mapping.</p>";
    }

} catch (Exception $e) {
    echo "<p style='color:red;'>Verification failed: " . $e->getMessage() . "</p>";
}
