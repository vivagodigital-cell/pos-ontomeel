<?php
// api/controllers/AuthController.php
require_once __DIR__ . '/../config/database.php';

session_start();
header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

try {
    if ($action === 'login') {
        $data = json_decode(file_get_contents('php://input'), true);
        $username = $data['username'] ?? '';
        $password = $data['password'] ?? '';

        if (empty($username) || empty($password)) {
            throw new Exception("Username and password are required.");
        }

        $stmt = $pdo->prepare("SELECT id, username, password, full_name, role, pos_access FROM admins WHERE username = ? OR email = ?");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Success
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_username'] = $user['username'];
            $_SESSION['admin_name'] = $user['full_name'];
            $_SESSION['admin_role'] = $user['role'];
            $_SESSION['admin_pos_access'] = $user['pos_access'];

            // Update last login
            $update = $pdo->prepare("UPDATE admins SET last_login = NOW() WHERE id = ?");
            $update->execute([$user['id']]);

            echo json_encode([
                'success' => true,
                'user' => [
                    'username' => $user['username'],
                    'full_name' => $user['full_name'],
                    'role' => $user['role'],
                    'pos_access' => $user['pos_access']
                ]
            ]);
        } else {
            throw new Exception("Invalid username or password.");
        }
    } 
    elseif ($action === 'logout') {
        session_destroy();
        echo json_encode(['success' => true]);
    }
    elseif ($action === 'check') {
        if (isset($_SESSION['admin_id'])) {
            // Re-fetch pos_access in case it was toggled by another admin
            $stmt = $pdo->prepare("SELECT role, pos_access FROM admins WHERE id = ?");
            $stmt->execute([$_SESSION['admin_id']]);
            $user = $stmt->fetch();
            
            $_SESSION['admin_role'] = $user['role'];
            $_SESSION['admin_pos_access'] = $user['pos_access'];

            echo json_encode([
                'authenticated' => true,
                'user' => [
                    'username' => $_SESSION['admin_username'],
                    'full_name' => $_SESSION['admin_name'],
                    'role' => $_SESSION['admin_role'],
                    'pos_access' => $_SESSION['admin_pos_access']
                ]
            ]);
        } else {
            echo json_encode(['authenticated' => false]);
        }
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
