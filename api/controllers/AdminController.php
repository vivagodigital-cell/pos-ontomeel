<?php
// api/controllers/AdminController.php
require_once __DIR__ . '/../config/database.php';

session_start();
header('Content-Type: application/json');

// Constant to control max POS users
const MAX_POS_USERS = 3; 

// Only Super Admin can access this controller
$currentRole = strtolower($_SESSION['admin_role'] ?? '');
if (!isset($_SESSION['admin_id']) || ($currentRole !== 'super admin' && $currentRole !== 'superadmin')) {
    http_response_code(403);
    echo json_encode(['error' => 'Permission denied. Only Super Admin can manage users.']);
    exit;
}

$action = $_GET['action'] ?? '';

try {
    if ($action === 'list') {
        $stmt = $pdo->query("SELECT id, username, full_name, role, pos_access, last_login FROM admins ORDER BY role DESC");
        $users = $stmt->fetchAll();
        
        echo json_encode([
            'success' => true, 
            'users' => $users,
            'max_pos_users' => MAX_POS_USERS
        ]);
    }
    elseif ($action === 'create') {
        $data = json_decode(file_get_contents('php://input'), true);
        $username = $data['username'] ?? '';
        $password = $data['password'] ?? '';
        $full_name = $data['full_name'] ?? '';
        $role = $data['role'] ?? 'editor'; // manager, editor
        $pos_access = $data['pos_access'] ?? 0;

        if (empty($username) || empty($password)) {
            throw new Exception("Username and password are required.");
        }

        // Count current POS users if trying to add a new one with access
        if ($pos_access) {
            $countStmt = $pdo->query("SELECT COUNT(*) FROM admins WHERE pos_access = 1");
            $currentCount = $countStmt->fetchColumn();
            if ($currentCount >= MAX_POS_USERS) {
                throw new Exception("Maximum POS access limit reached (" . MAX_POS_USERS . "). Deactivate another user first.");
            }
        }

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO admins (username, password, full_name, role, pos_access, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$username, $hashedPassword, $full_name, $role, $pos_access]);

        echo json_encode(['success' => true, 'message' => 'User created successfully.']);
    }
    elseif ($action === 'toggle_access') {
        $data = json_decode(file_get_contents('php://input'), true);
        $userId = $data['user_id'] ?? 0;
        $newStatus = $data['status'] ?? 0;

        if ($newStatus == 1) {
            $countStmt = $pdo->query("SELECT COUNT(*) FROM admins WHERE pos_access = 1");
            $currentCount = $countStmt->fetchColumn();
            if ($currentCount >= MAX_POS_USERS) {
                throw new Exception("Limit reached (" . MAX_POS_USERS . "). Disable another user to grant POS access.");
            }
        }

        $stmt = $pdo->prepare("UPDATE admins SET pos_access = ? WHERE id = ?");
        $stmt->execute([$newStatus, $userId]);

        echo json_encode(['success' => true]);
    }
    elseif ($action === 'delete') {
        $userId = $_GET['id'] ?? 0;
        // Don't delete yourself
        if ($userId == $_SESSION['admin_id']) {
            throw new Exception("You cannot delete your own account.");
        }
        
        $stmt = $pdo->prepare("DELETE FROM admins WHERE id = ?");
        $stmt->execute([$userId]);
        echo json_encode(['success' => true]);
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
