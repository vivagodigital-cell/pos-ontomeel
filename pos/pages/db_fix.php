<?php
require_once __DIR__ . '/../../api/config/database.php';
// Now $pdo is available because it was defined in database.php
try {
    $pdo->exec("ALTER TABLE orders MODIFY COLUMN payment_method VARCHAR(255) DEFAULT 'Cash'");
    echo "SUCCESS: Changed payment_method column to VARCHAR(255).";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
?>
