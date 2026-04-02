// pos/assets/auth-guard.js
(function() {
    // 0. Immediate Security Lock: Hide body before any parsing happens to prevent flashes
    const lockStyle = document.createElement('style');
    lockStyle.id = 'auth-lock-style';
    lockStyle.innerHTML = 'body { display: none !important; }';
    document.head.appendChild(lockStyle);

    window.NEXT_PAYMENT_DATE = '2026-05-01'; // YYYY-MM-DD - FOR SYSTEM LOGIC
    window.NEXT_PAYMENT_DISPLAY = 'May 1, 2026'; // DISPLAY FORMAT - FOR SIDEBAR

    async function checkAuth() {
        try {
            // 1. Subscription Check (Immediate)
            const today = new Date();
            const paymentDate = new Date(NEXT_PAYMENT_DATE);
            
            // If today is on or after the payment date, restrict access
            if (today.setHours(0,0,0,0) >= paymentDate.setHours(0,0,0,0)) {
                document.body.innerHTML = `
                    <div style="height: 100vh; display: flex; align-items: center; justify-content: center; background: #f8fafc; font-family: 'Inter', system-ui, sans-serif;">
                        <div style="max-width: 450px; text-align: center; padding: 3rem; background: white; border-radius: 20px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1), 0 8px 10px -6px rgba(0,0,0,0.1); border: 1px solid #f1f5f9;">
                            <div style="display: inline-flex; align-items: center; justify-content: center; width: 80px; height: 80px; background: #fee2e2; border-radius: 50%; margin-bottom: 2rem;">
                                <i class="fa-solid fa-credit-card" style="font-size: 2.5rem; color: #ef4444;"></i>
                            </div>
                            <h1 style="font-size: 1.5rem; color: #1e293b; margin-bottom: 1rem; font-weight: 800;">Subscription Expired</h1>
                            <p style="color: #64748b; font-size: 0.95rem; line-height: 1.6; margin-bottom: 2rem;">
                                Your last subscription period ended on <strong style="color: #ef4444;">${window.NEXT_PAYMENT_DISPLAY || 'May 1, 2026'}</strong>. Please renew your plan to regain full access to all terminal features.
                            </p>
                            <div style="padding: 1.25rem; background: #f8fafc; border-radius: 12px; margin-bottom: 2rem; border: 1px dashed #cbd5e1;">
                                <div style="font-size: 0.75rem; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 600; margin-bottom: 4px;">Next Payment Due</div>
                                <div style="font-size: 1.1rem; color: #1e293b; font-weight: 700;">Renew Before Access</div>
                            </div>
                            <div style="display: flex; gap: 12px;">
                                <button onclick="logout()" style="flex: 1; padding: 0.875rem; background: #ef4444; color: white; border: none; border-radius: 10px; font-weight: 600; cursor: pointer; transition: background 0.2s;">
                                    Logout & Exit
                                </button>
                                <button onclick="window.location.reload()" style="flex: 1; padding: 0.875rem; background: #f1f5f9; color: #475569; border: none; border-radius: 10px; font-weight: 600; cursor: pointer; transition: background 0.2s;">
                                    Try Again
                                </button>
                            </div>
                        </div>
                    </div>
                `;
                return;
            }

            // 2. Auth Check
            const isSubPage = window.location.pathname.includes('/pages/');
            const apiPath = isSubPage ? '../../api/controllers/AuthController.php' : '../api/controllers/AuthController.php';
            const loginPath = isSubPage ? '../login.html' : 'login.html';

            const response = await fetch(`${apiPath}?action=check`);
            const data = await response.json();

            if (!data.authenticated) {
                window.location.href = loginPath;
            } else {
                window.posUserName = data.user.full_name || 'Admin';
                window.posUserRole = data.user.role || 'editor'; // super admin, manager, editor
                window.posUserAccess = data.user.pos_access; // 1 or 0

                // 3. POS Access Check
                if (window.posUserAccess == 0) {
                    document.body.innerHTML = `
                        <div style="height: 100vh; display: flex; align-items: center; justify-content: center; background: #f8fafc; font-family: 'Inter', system-ui, sans-serif;">
                            <div style="max-width: 450px; text-align: center; padding: 3rem; background: white; border-radius: 20px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1), 0 8px 10px -6px rgba(0,0,0,0.1); border: 1px solid #f1f5f9;">
                                <div style="display: inline-flex; align-items: center; justify-content: center; width: 80px; height: 80px; background: #fff7ed; border-radius: 50%; margin-bottom: 2rem;">
                                    <i class="fa-solid fa-user-lock" style="font-size: 2.5rem; color: #f97316;"></i>
                                </div>
                                <h1 style="font-size: 1.5rem; color: #1e293b; margin-bottom: 1rem; font-weight: 800;">Access Restricted</h1>
                                <p style="color: #64748b; font-size: 0.95rem; line-height: 1.6; margin-bottom: 2rem;">
                                    Hello <strong style="color: #1e293b;">${window.posUserName}</strong>, your account does not have active POS terminal access. 
                                    <br><br>
                                    Please contact your <strong>Super Admin</strong> to enable POS permissions for your account.
                                </p>
                                <div style="display: flex; gap: 12px;">
                                    <button onclick="logout()" style="flex: 1; padding: 0.875rem; background: #0f172a; color: white; border: none; border-radius: 10px; font-weight: 600; cursor: pointer; transition: background 0.2s;">
                                        Logout
                                    </button>
                                </div>
                            </div>
                        </div>
                    `;
                    return;
                }

                // Update UI elements across all pages
                const nameElements = document.querySelectorAll('.user-name-display');
                nameElements.forEach(el => el.innerText = window.posUserName);

                const roleElements = document.querySelectorAll('.user-role-display');
                const roleLower = window.posUserRole.toLowerCase().trim();
                const roleClass = `role-${roleLower.replace(/\s+/g, '-')}`;
                roleElements.forEach(el => {
                    el.innerText = window.posUserRole.replace('_', ' ');
                    // Preserve existing classes (like role-badge-large) but update role specific ones
                    el.classList.remove('role-super-admin', 'role-manager', 'role-editor', 'role-superadmin');
                    el.classList.add(roleClass);
                });

                // Show Admin Only sections
                if (roleLower === 'super admin' || roleLower === 'superadmin') {
                    document.querySelectorAll('.admin-only').forEach(el => el.style.display = 'block');
                }

                // Update Initials in Header
                const initials = window.posUserName.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2);
                const profilePath = isSubPage ? 'profile.html' : 'pages/profile.html';

                // Inject if not already present, or update existing
                const headerToolsElements = document.querySelectorAll('.header-tools');
                headerToolsElements.forEach(el => {
                    const existingLink = el.querySelector('.header-profile-link');
                    if (existingLink) {
                        existingLink.href = profilePath;
                        const display = existingLink.querySelector('.user-initials-display');
                        if (display) display.innerText = initials;
                    } else {
                        // Remove old style profiles if they exist
                        const oldProfile = el.querySelector('.user-profile');
                        if (oldProfile) oldProfile.remove();

                        const profileHTML = `
                            <a href="${profilePath}" class="header-profile-link" style="text-decoration: none; order: 10;">
                                <div style="width: 42px; height: 42px; background: #eff6ff; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-weight: 700; color: #2563eb; font-size: 0.9rem; border: 1px solid #dbeafe; transition: all 0.2s;" class="user-initials-display">
                                    ${initials}
                                </div>
                            </a>
                            <style>
                                .header-profile-link:hover div {
                                    background: #dbeafe;
                                    transform: scale(1.05);
                                    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.1);
                                }
                            </style>
                        `;
                        el.insertAdjacentHTML('beforeend', profileHTML);
                    }
                });

                // Unlock UI for authorized user
                const lock = document.getElementById('auth-lock-style');
                if (lock) lock.remove();
            }
        } catch (error) {
            console.error('Auth check failed:', error);
            // On hard error, redirect to login for safety
            window.location.href = isSubPage ? '../login.html' : 'login.html';
        }
    }

    // Run check immediately
    checkAuth();
})();

async function logout() {
    try {
        const isSubPage = window.location.pathname.includes('/pages/');
        const apiPath = isSubPage ? '../../api/controllers/AuthController.php' : '../api/controllers/AuthController.php';
        const loginPath = isSubPage ? '../login.html' : 'login.html';

        const response = await fetch(`${apiPath}?action=logout`);
        const data = await response.json();
        if (data.success) {
            window.location.href = loginPath;
        }
    } catch (error) {
        console.error('Logout failed:', error);
    }
}
