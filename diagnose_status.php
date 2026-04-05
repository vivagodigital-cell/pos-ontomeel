<?php
require_once 'api/config/database.php';
$stmt = $pdo->query("DESCRIBE orders");
echo "TABLE STRUCTURE:\n";
echo json_encode($stmt->fetchAll(), JSON_PRETTY_PRINT) . "\n\n";

$stmt = $pdo->query("SELECT id, invoice_no, payment_status, order_status FROM orders LIMIT 2");
echo "FIRST TWO ORDERS:\n";
echo json_encode($stmt->fetchAll(), JSON_PRETTY_PRINT);
?>