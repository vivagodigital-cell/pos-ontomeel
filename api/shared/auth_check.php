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
        echo "<div style='text-align:center; padding:50px; font-family:sans-serif;'><h1>Access Denied</h1><p>Your account does not have POS access. <a href='logout.php' style='color:red;'>Logout</a></p></div>";
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
