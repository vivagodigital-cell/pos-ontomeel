<?php
require_once __DIR__ . '/api/config/database.php';
try {
    $stmt = $pdo->query("SELECT id, username, full_name, email, role, pos_access FROM admins");
    $admins = $stmt->fetchAll();
    echo "Existing admins in the database:\n";
    foreach ($admins as $admin) {
        printf("ID: %d | Username: %s | Full Name: %s | Email: %s | Role: %s | POS Access: %d\n", 
            $admin['id'], $admin['username'], $admin['full_name'], $admin['email'], $admin['role'] ?? 'NULL', $admin['pos_access']);
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
