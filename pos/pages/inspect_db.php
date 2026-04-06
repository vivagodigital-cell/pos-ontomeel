<?php
// inspect_db.php
require_once __DIR__ . '/../../api/config/database.php';

try {
    echo "<h1>Database Inspection</h1>";
    
    // List all tables
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "<h2>Tables:</h2><ul>";
    foreach ($tables as $t) {
        echo "<li>$t</li>";
    }
    echo "</ul>";

    // Inspect 'books' structure
    echo "<h2>'books' table:</h2>";
    $stmt = $pdo->query("SHOW COLUMNS FROM books");
    echo "<table border='1'><tr><th>Field</th><th>Type</th></tr>";
    foreach ($stmt->fetchAll() as $c) {
        echo "<tr><td>{$c['Field']}</td><td>{$c['Type']}</td></tr>";
    }
    echo "</table>";

    // Inspect 'inventory_items' structure
    echo "<h2>'inventory_items' table:</h2>";
    $stmt = $pdo->query("SHOW COLUMNS FROM inventory_items");
    echo "<table border='1'><tr><th>Field</th><th>Type</th></tr>";
    foreach ($stmt->fetchAll() as $c) {
        echo "<tr><td>{$c['Field']}</td><td>{$c['Type']}</td></tr>";
    }
    echo "</table>";

    // Check if 'categories' exists
    $hasCategories = in_array('categories', $tables);
    if ($hasCategories) {
        echo "<h2>'categories' table detected!</h2>";
        $stmt = $pdo->query("SHOW COLUMNS FROM categories");
        echo "<table border='1'><tr><th>Field</th><th>Type</th></tr>";
        foreach ($stmt->fetchAll() as $c) {
            echo "<tr><td>{$c['Field']}</td><td>{$c['Type']}</td></tr>";
        }
        echo "</table>";
    } else {
        echo "<h2 style='color:red;'>'categories' table NOT found in SHOW TABLES list.</h2>";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
