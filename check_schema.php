<?php
require_once __DIR__ . '/api/config/database.php';
try {
    $stmt = $pdo->query("DESCRIBE admins");
    $schema = $stmt->fetchAll();
    echo "Schema for 'admins' table:\n";
    foreach ($schema as $row) {
        printf("%-15s %-15s %-15s %-15s %-15s %-15s\n", $row['Field'], $row['Type'], $row['Null'], $row['Key'], $row['Default'], $row['Extra']);
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
