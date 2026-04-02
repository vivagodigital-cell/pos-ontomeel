<?php
// api/shared/auth_check.php

// Secure session settings - MUST be before session_start()
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        ini_set('session.cookie_secure', 1);
    }

    session_start();
}

// Global Auth Check
function checkAuth($isSubPage = false) {
    $loginPath = $isSubPage ? '../login.php' : 'login.php';
    
    if (!isset($_SESSION['admin_id'])) {
        header("Location: $loginPath");
        exit;
    }

    // Optional: Check POS Access
    if (!isset($_SESSION['admin_pos_access']) || $_SESSION['admin_pos_access'] == 0) {
        $logoutPath = $isSubPage ? '../logout.php' : 'logout.php';
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Access Denied | Ontomeel POS</title>
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
            <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
            <style>
                body {
                    background: radial-gradient(circle at top right, #fef2f2 0%, #fff1f2 100%);
                    height: 100vh;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    overflow: hidden;
                    font-family: 'Inter', sans-serif;
                    margin: 0;
                }
                .denied-card {
                    background: rgba(255, 255, 255, 0.9);
                    backdrop-filter: blur(20px);
                    border: 1px solid rgba(220, 38, 38, 0.1);
                    width: 440px;
                    padding: 3.5rem 3rem;
                    border-radius: 40px;
                    text-align: center;
                    box-shadow: 0 40px 100px -20px rgba(220, 38, 38, 0.08);
                    animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1);
                }
                @keyframes slideUp {
                    from { transform: translateY(40px); opacity: 0; }
                    to { transform: translateY(0); opacity: 1; }
                }
                .icon-box {
                    width: 100px;
                    height: 100px;
                    background: #fef2f2;
                    color: #dc2626;
                    font-size: 3rem;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    border-radius: 30px;
                    margin: 0 auto 2rem;
                    box-shadow: inset 0 2px 4px rgba(220, 38, 38, 0.05);
                }
                h1 { font-weight: 800; color: #0f172a; font-size: 1.75rem; margin-bottom: 1rem; }
                p { color: #64748b; font-size: 1rem; line-height: 1.6; margin-bottom: 2.5rem; }
                .btn-logout {
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                    gap: 12px;
                    width: 100%;
                    padding: 1.1rem;
                    border-radius: 18px;
                    background: #0f172a;
                    color: white;
                    text-decoration: none;
                    font-weight: 700;
                    font-size: 1rem;
                    transition: all 0.3s;
                    box-shadow: 0 10px 30px -5px rgba(15, 23, 42, 0.3);
                }
                .btn-logout:hover {
                    background: #1e293b;
                    transform: translateY(-2px);
                    box-shadow: 0 15px 35px -5px rgba(15, 23, 42, 0.4);
                }
            </style>
        </head>
        <body>
            <div class="denied-card">
                <div class="icon-box">
                    <i class="fa-solid fa-shield-lock"></i>
                </div>
                <h1>Access Denied</h1>
                <p>Your account does not have authorization to access the POS system. Please contact your administrator if you believe this is an error.</p>
                <a href="<?php echo $logoutPath; ?>" class="btn-logout">
                    LOGOUT & SESSION END <i class="fa-solid fa-right-from-bracket"></i>
                </a>
            </div>
        </body>
        </html>
        <?php
        exit;
    }
}

// Check for login page (redirect to index if already logged in)
function checkLogin() {
    if (isset($_SESSION['admin_id'])) {
        header("Location: index.php");
        exit;
    }
}

// Global User Info (Injects JS and Handles UI updates via PHP script)
function renderUserUI($isSubPage = false) {
    if (!isset($_SESSION['admin_id'])) return;

    $name = $_SESSION['admin_name'] ?? 'Admin';
    $role = $_SESSION['admin_role'] ?? 'Editor';
    $posAccess = $_SESSION['admin_pos_access'] ?? 0;
    
    // Calculate Initials
    $parts = explode(' ', trim($name));
    $initials = '';
    foreach ($parts as $p) { if (!empty($p)) $initials .= strtoupper($p[0]); }
    $initials = substr($initials, 0, 2);
    if (empty($initials)) $initials = 'AD';

    $profilePath = $isSubPage ? 'profile.php' : 'pages/profile.php';

    ?>
    <script>
        window.posUserName = <?php echo json_encode($name); ?>;
        window.posUserRole = <?php echo json_encode($role); ?>;
        window.posUserAccess = <?php echo json_encode($posAccess); ?>;

        document.addEventListener('DOMContentLoaded', function() {
            // Update Text Displays
            document.querySelectorAll('.user-name-display').forEach(el => el.innerText = window.posUserName);
            
            const roleLower = window.posUserRole.toLowerCase().trim();
            const roleClass = 'role-' + roleLower.replace(/\s+/g, '-');
            document.querySelectorAll('.user-role-display').forEach(el => {
                el.innerText = window.posUserRole.replace('_', ' ');
                el.classList.remove('role-super-admin', 'role-manager', 'role-editor', 'role-superadmin');
                el.classList.add(roleClass);
            });

            if (roleLower === 'super admin' || roleLower === 'superadmin') {
                document.querySelectorAll('.admin-only').forEach(el => el.style.display = 'block');
            }

            // Inject Profile Icon
            document.querySelectorAll('.header-tools').forEach(el => {
                const oldProfile = el.querySelector('.user-profile');
                if (oldProfile) oldProfile.remove();
                
                if (!el.querySelector('.header-profile-link')) {
                    const profileHTML = `
                        <a href="<?php echo $profilePath; ?>" class="header-profile-link" title="View Profile" style="text-decoration: none; order: 10; display: block;">
                            <div class="user-initials-display" style="width: 44px; height: 44px; background: #eff6ff; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-weight: 700; color: #2563eb; font-size: 0.95rem; border: 1px solid #dbeafe; transition: all 0.3s ease; cursor: pointer; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
                                <?php echo $initials; ?>
                            </div>
                        </a>
                    `;
                    el.insertAdjacentHTML('beforeend', profileHTML);
                }
            });
        });

        async function logout() {
            try {
                const apiPath = <?php echo json_encode($isSubPage ? '../../api/controllers/AuthController.php' : '../api/controllers/AuthController.php'); ?>;
                const loginPath = <?php echo json_encode($isSubPage ? '../login.php' : 'login.php'); ?>;
                const response = await fetch(apiPath + '?action=logout');
                const data = await response.json();
                if (data.success) {
                    sessionStorage.clear();
                    window.location.href = loginPath;
                }
            } catch (e) {}
        }
    </script>
    <style>
        .role-super-admin, .role-superadmin { background: #fef2f2 !important; color: #ef4444 !important; border: 1px solid #fee2e2 !important; }
        .role-manager { background: #f0fdf4 !important; color: #22c55e !important; border: 1px solid #dcfce7 !important; }
        .role-editor { background: #f0f9ff !important; color: #3b82f6 !important; border: 1px solid #e0f2fe !important; }
    </style>
    <?php
}
?>
