<?php
require_once __DIR__ . '/../../api/config/database.php';

try {
    $pdo->exec("ALTER TABLE orders 
                ADD COLUMN IF NOT EXISTS order_type VARCHAR(20) DEFAULT 'Sale',
                ADD COLUMN IF NOT EXISTS staff_id INT NULL,
                ADD COLUMN IF NOT EXISTS staff_name VARCHAR(100) NULL,
                ADD COLUMN IF NOT EXISTS terminal_id VARCHAR(50) DEFAULT 'Main'");

    // Convert ENUM to VARCHAR to allow Refunded/Cancelled statuses
    $pdo->exec("ALTER TABLE orders MODIFY COLUMN payment_status VARCHAR(50) DEFAULT 'Pending'");
    $pdo->exec("ALTER TABLE orders MODIFY COLUMN order_status VARCHAR(50) DEFAULT 'Processing'");
    
    // Populate missing data for existing records
    $pdo->exec("UPDATE orders SET payment_status = 'Paid' WHERE payment_status IS NULL OR payment_status = '' OR payment_status = '0'");
    $pdo->exec("UPDATE orders SET order_status = 'Delivered' WHERE order_status IS NULL OR order_status = '' OR order_status = '0'");
    $pdo->exec("UPDATE orders SET order_type = 'Sale' WHERE order_type IS NULL OR order_type = ''");

    $pdo->exec("ALTER TABLE order_items 
                ADD COLUMN IF NOT EXISTS item_type VARCHAR(20) DEFAULT 'Book'");
    
    echo "Database schema updated successfully (Orders and Order Items tables). Missing data populated.";
} catch (PDOException $e) {
    echo "Error updating database: " . $e->getMessage();
}
?>
