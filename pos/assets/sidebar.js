// pos/assets/sidebar.js
(function() {
    function initSidebar() {
        const isSubPage = window.location.pathname.includes('/pages/');
        const basePath = isSubPage ? '../' : '';
        const pagesPath = isSubPage ? '' : 'pages/';
        const dashboardPath = isSubPage ? '../index.html' : 'index.html';
        
        // Detailed current page detection
        const pathSegments = window.location.pathname.split('/');
        const currentFile = pathSegments[pathSegments.length - 1] || 'index.html';
        const currentPage = currentFile.replace('.html', '');

        const sidebarHTML = `
    <nav class="sidebar">
        <div class="logo-wrapper">
            <div class="logo-icon">
                <img src="${basePath}assets/logo.webp" alt="Ontomeel Logo" style="width: 30px; height: 30px; object-fit: contain;">
            </div>
            <span class="logo-text">ONTOMEEL</span>
        </div>

        <ul class="nav-links">
            <li class="nav-item">
                <a href="${dashboardPath}" class="nav-link ${currentPage === 'index' || currentPage === '' ? 'active' : ''}">
                    <i class="fa-solid fa-house"></i>
                    <span class="nav-text">Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="${pagesPath}terminal.html" class="nav-link ${currentPage === 'terminal' ? 'active' : ''}">
                    <i class="fa-solid fa-cash-register"></i>
                    <span class="nav-text">Terminal</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="${pagesPath}inventory.html" class="nav-link ${currentPage === 'inventory' ? 'active' : ''}">
                    <i class="fa-solid fa-box-open"></i>
                    <span class="nav-text">Inventory</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="${pagesPath}member.html" class="nav-link ${currentPage === 'member' ? 'active' : ''}">
                    <i class="fa-solid fa-users"></i>
                    <span class="nav-text">Members</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="${pagesPath}supplier.html" class="nav-link ${currentPage === 'supplier' ? 'active' : ''}">
                    <i class="fa-solid fa-truck-ramp-box"></i>
                    <span class="nav-text">Suppliers</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="${pagesPath}report.html" class="nav-link ${currentPage === 'report' ? 'active' : ''}">
                    <i class="fa-solid fa-chart-line"></i>
                    <span class="nav-text">Reports</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="${pagesPath}orders.html" class="nav-link ${currentPage === 'orders' ? 'active' : ''}">
                    <i class="fa-solid fa-history"></i>
                    <span class="nav-text">Orders</span>
                </a>
            </li>
        </ul>
        <div style="margin-top: auto; padding: 1rem;">
            <a href="javascript:void(0)" onclick="logout()" class="nav-link logout-btn" style="color: #ef4444; display: flex; align-items: center; gap: 12px; padding: 0.75rem; border-radius: 8px; transition: all 0.2s;">
                <i class="fa-solid fa-right-from-bracket"></i>
                <span class="nav-text" style="font-weight: 600;">Logout</span>
            </a>
        </div>
    </nav>
    <style>
        /* Role Colors */
        .role-super-admin, .role-superadmin { background: #fef2f2; color: #ef4444; border: 1px solid #fee2e2; }
        .role-manager { background: #f0fdf4; color: #22c55e; border: 1px solid #dcfce7; }
        .role-editor { background: #f0f9ff; color: #3b82f6; border: 1px solid #e0f2fe; }
        .logout-btn:hover {
            background: #fef2f2;
            transform: translateX(4px);
        }
    </style>
    `;

        // Traditional fallback: Insert it before the main content
        document.body.insertAdjacentHTML('afterbegin', sidebarHTML);
    }

    // Run when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initSidebar);
    } else {
        initSidebar();
    }
})();
