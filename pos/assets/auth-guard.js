// pos/assets/auth-guard.js
(function() {
    async function checkAuth() {
        try {
            // Determine API path based on current location
            const isSubPage = window.location.pathname.includes('/pages/');
            const apiPath = isSubPage ? '../../api/controllers/AuthController.php' : '../api/controllers/AuthController.php';
            const loginPath = isSubPage ? '../login.html' : 'login.html';

            const response = await fetch(`${apiPath}?action=check`);
            const data = await response.json();

            if (!data.authenticated) {
                window.location.href = loginPath;
            } else {
                // Store globally for POS receipts
                window.posUserName = data.user.full_name || 'Admin';

                // Optional: Update UI with user info if elements exist
                const userElements = document.querySelectorAll('.user-name-display');
                userElements.forEach(el => el.innerText = data.user.full_name);
            }
        } catch (error) {
            console.error('Auth check failed:', error);
            window.posUserName = 'System User'; // default fallback
            // On network error, we might want to allow staying on page or force login
            // For security, usually force login if we can't verify
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
