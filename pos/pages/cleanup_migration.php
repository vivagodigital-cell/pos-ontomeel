<?php
// cleanup_migration.php
require_once __DIR__ . '/../../api/config/database.php';

try {
    $pdo->exec("DROP TABLE IF EXISTS item_categories");
    echo "<h1>Cleanup Successful!</h1><p>The redundant 'item_categories' table has been removed.</p>";
} catch (Exception $e) {
    echo "<h1>Cleanup Failed</h1><p>Error: " . $e->getMessage() . "</p>";
}
