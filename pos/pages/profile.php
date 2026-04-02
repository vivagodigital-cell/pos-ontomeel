<?php require_once '../../api/shared/auth_check.php'; checkAuth(true); renderUserUI(true); ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile | Ontomeel POS</title>
    <link rel="stylesheet" href="../assets/pos-styles.css?v=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        .profile-container {
            max-width: 800px;
            margin: 3rem auto;
            padding: 0 1.5rem;
        }

        .profile-header {
            background: white;
            border-radius: 24px;
            padding: 3rem;
            border: 1px solid var(--border-light);
            box-shadow: var(--shadow-lg);
            display: flex;
            align-items: center;
            gap: 2.5rem;
            margin-bottom: 2rem;
            position: relative;
            overflow: hidden;
        }

        .profile-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 6px;
            background: linear-gradient(90deg, #3b82f6, #6366f1);
        }

        .large-avatar {
            width: 120px;
            height: 120px;
            background: #f1f5f9;
            border-radius: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3.5rem;
            color: #64748b;
            border: 1px solid #e2e8f0;
        }

        .header-info {
            flex-grow: 1;
        }

        .header-info h1 {
            font-size: 2rem;
            font-weight: 800;
            color: #1e293b;
            margin-bottom: 0.5rem;
        }

        .role-badge-large {
            display: inline-block;
            padding: 6px 16px;
            border-radius: 10px;
            font-size: 0.85rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .details-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
        }

        .info-card {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            border: 1px solid var(--border-light);
            box-shadow: var(--shadow-sm);
        }

        .info-card h3 {
            font-size: 0.75rem;
            font-weight: 700;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            margin-bottom: 1.25rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .info-card .val {
            font-size: 1.1rem;
            font-weight: 600;
            color: #1e293b;
        }

        .subscription-card {
            grid-column: span 2;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .sub-group {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .crown-icon {
            width: 50px;
            height: 50px;
            background: #fffbeb;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: #f59e0b;
            border: 1px solid #fef3c7;
        }

        .sub-details .plan-name {
            font-weight: 800;
            color: #1e293b;
            font-size: 1.1rem;
        }

        .sub-details .expiry {
            font-size: 0.85rem;
            color: #64748b;
            margin-top: 2px;
        }

        .btn-renew {
            background: #0f172a;
            color: white;
            padding: 10px 20px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 700;
            font-size: 0.9rem;
            transition: all 0.2s;
        }

        .btn-renew:hover {
            background: #1e293b;
            transform: translateY(-2px);
        }

        /* Role specific badge styles - reusing from sidebar logic but larger */
        .role-super-admin,
        .role-superadmin {
            background: #fef2f2;
            color: #ef4444;
            border: 1px solid #fee2e2;
        }

        .role-manager {
            background: #f0fdf4;
            color: #22c55e;
            border: 1px solid #dcfce7;
        }

        .role-editor {
            background: #f0f9ff;
            color: #3b82f6;
            border: 1px solid #e0f2fe;
        }
        /* Pricing Cards */
        .pricing-section {
            margin-top: 4rem;
            padding-bottom: 5rem;
        }

        .pricing-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.5rem;
            margin-top: 2rem;
        }

        .price-card {
            background: white;
            border-radius: 24px;
            padding: 2.5rem 2rem;
            border: 1px solid #e2e8f0;
            display: flex;
            flex-direction: column;
            position: relative;
            transition: all 0.3s ease;
        }

        .price-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.05);
            border-color: #3b82f633;
        }

        .price-card.featured {
            border: 2px solid #3b82f6;
            background: #f8fbff;
        }

        .plan-tag {
            position: absolute;
            top: 20px;
            right: 20px;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 800;
            text-transform: uppercase;
        }

        .plan-name-large {
            font-size: 1.25rem;
            font-weight: 800;
            color: #1e293b;
            margin-bottom: 0.5rem;
        }

        .plan-price {
            margin: 1.5rem 0;
            display: flex;
            align-items: baseline;
            gap: 4px;
        }

        .price-val {
            font-size: 2.25rem;
            font-weight: 800;
            color: #1e293b;
        }

        .price-cur {
            font-size: 1rem;
            color: #64748b;
            font-weight: 600;
        }

        .feature-list {
            list-style: none;
            padding: 0;
            margin: 0;
            flex-grow: 1;
        }

        .feature-item {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.85rem;
            color: #64748b;
            margin-bottom: 12px;
        }

        .feature-item i {
            color: #10b981;
            font-size: 0.9rem;
        }

        .btn-plan {
            width: 100%;
            padding: 12px;
            border-radius: 12px;
            border: none;
            font-weight: 700;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.2s;
            margin-top: 2rem;
            text-align: center;
            text-decoration: none;
        }

        .btn-current {
            background: #f1f5f9;
            color: #94a3b8;
            cursor: default;
        }

        .btn-upgrade {
            background: #0f172a;
            color: white;
        }

        .btn-upgrade:hover {
            background: #1e293b;
        }
    </style>
</head>

<body>
    <div class="profile-container">
        <div style="margin-bottom: 2rem;">
            <a href="terminal.php"
                style="color: #64748b; text-decoration: none; font-weight: 600; font-size: 0.9rem; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-arrow-left"></i>
                Back to Terminal
            </a>
        </div>

        <div class="profile-header">
            <div class="large-avatar">
                <i class="fa-solid fa-user-ninja"></i>
            </div>
            <div class="header-info">
                <div class="role-badge-large user-role-display role-editor">Editor</div>
                <h1 class="user-name-display">Loading User...</h1>
                <p style="color: #64748b; font-weight: 500;">Authorized POS Operator</p>
            </div>
        </div>

        <div class="details-grid">
            <div class="info-card">
                <h3><i class="fa-solid fa-id-card"></i> Username</h3>
                <div class="val" id="profile-username">...</div>
            </div>
            <div class="info-card">
                <h3><i class="fa-solid fa-envelope"></i> Account Type</h3>
                <div class="val">Standard POS Access</div>
            </div>

            <div class="info-card subscription-card">
                <div class="sub-group">
                    <div class="crown-icon">
                        <i class="fa-solid fa-crown"></i>
                    </div>
                    <div class="sub-details">
                        <div class="plan-name">Special Custom Plan</div>
                        <div class="expiry">Next payment on <strong id="sub-page-date">...</strong></div>
                    </div>
                </div>
                <a href="#" class="btn-renew">Manage Plan</a>
            </div>
        </div>

        <!-- Subscription Offers -->
        <div class="pricing-section">
            <div style="text-align: center; margin-bottom: 3rem;">
                <h2 style="font-size: 2rem; font-weight: 800; color: #1e293b; margin-bottom: 0.5rem;">Upgrade Your Experience</h2>
                <p style="color: #64748b; font-weight: 500;">Scale your business with our professional POS plans</p>
            </div>

            <div class="pricing-grid">
                <!-- Custom Special Plan -->
                <div class="price-card featured">
                    <span class="plan-tag" style="background: #3b82f6; color: white;">Current Plan</span>
                    <div class="plan-name-large">Custom Offer</div>
                    <p style="font-size: 0.8rem; color: #64748b;">Special enterprise-tier access</p>
                    <div class="plan-price">
                        <span class="price-val">500</span>
                        <span class="price-cur">BDT/mo</span>
                    </div>
                    <ul class="feature-list">
                        <li class="feature-item"><i class="fa-solid fa-circle-check"></i> <span>All pages & features access</span></li>
                        <li class="feature-item"><i class="fa-solid fa-circle-check"></i> <span>Max 3 POS users</span></li>
                        <li class="feature-item"><i class="fa-solid fa-circle-check"></i> <span>Special discounted pricing</span></li>
                        <li class="feature-item"><i class="fa-solid fa-circle-check"></i> <span>Lifetime updates</span></li>
                    </ul>
                    <div class="btn-plan btn-current">Active Plan</div>
                </div>

                <!-- Basic Plan -->
                <div class="price-card">
                    <div class="plan-name-large">Basic</div>
                    <p style="font-size: 0.8rem; color: #64748b;">For growing retail shops</p>
                    <div class="plan-price">
                        <span class="price-val">800</span>
                        <span class="price-cur">BDT/mo</span>
                    </div>
                    <ul class="feature-list">
                        <li class="feature-item"><i class="fa-solid fa-circle-check"></i> <span>Max 3,000 customers</span></li>
                        <li class="feature-item"><i class="fa-solid fa-circle-check"></i> <span>100 Daily Sell actions</span></li>
                        <li class="feature-item"><i class="fa-solid fa-circle-check"></i> <span>AI Powered Reports Page</span></li>
                        <li class="feature-item"><i class="fa-solid fa-circle-check"></i> <span>Email Support</span></li>
                        <li class="feature-item"><i class="fa-solid fa-circle-check"></i> <span>Max 5 POS users</span></li>
                    </ul>
                    <a href="#" class="btn-plan btn-upgrade">Choose Basic</a>
                </div>

                <!-- Standard Plan -->
                <div class="price-card">
                    <div class="plan-name-large">Standard</div>
                    <p style="font-size: 0.8rem; color: #64748b;">The complete business suite</p>
                    <div class="plan-price">
                        <span class="price-val">1200</span>
                        <span class="price-cur">BDT/mo</span>
                    </div>
                    <ul class="feature-list">
                        <li class="feature-item"><i class="fa-solid fa-circle-check"></i> <span>Max 12,000 customers</span></li>
                        <li class="feature-item"><i class="fa-solid fa-circle-check"></i> <span>500 Max Terminal Daily Sell</span></li>
                        <li class="feature-item"><i class="fa-solid fa-circle-check"></i> <span>Access to Hidden Features</span></li>
                        <li class="feature-item"><i class="fa-solid fa-circle-check"></i> <span>24/7 Call Support</span></li>
                        <li class="feature-item"><i class="fa-solid fa-circle-check"></i> <span>Enterprise dashboard access</span></li>
                    </ul>
                    <a href="#" class="btn-plan btn-upgrade">Choose Standard</a>
                </div>
            </div>
        </div>

        <!-- Admin Management Section - Only for Super Admin -->
        <div id="admin-management-section" style="display: none; margin-top: 3rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <h2 style="font-size: 1.5rem; font-weight: 800; color: #1e293b;">Team Management</h2>
                <button onclick="toggleUserForm()" class="btn-renew"
                    style="background: #3b82f6; border: none; cursor: pointer;">
                    <i class="fa-solid fa-plus"></i> Add New User
                </button>
            </div>

            <!-- New User Form -->
            <div id="new-user-form"
                style="display: none; background: white; padding: 2rem; border-radius: 20px; border: 1px solid #e2e8f0; margin-bottom: 2rem; box-shadow: var(--shadow-sm);">
                <h3 style="margin-bottom: 1.5rem;">Create New POS User</h3>
                <form id="create-user-form" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <input type="text" id="new-full-name" placeholder="Full Name" required
                        style="padding: 10px; border: 1px solid #e2e8f0; border-radius: 8px;">
                    <input type="email" id="new-email" placeholder="Email Address" required
                        style="padding: 10px; border: 1px solid #e2e8f0; border-radius: 8px;">
                    <input type="text" id="new-username" placeholder="Username" required
                        style="padding: 10px; border: 1px solid #e2e8f0; border-radius: 8px;">
                    <input type="password" id="new-password" placeholder="Password" required
                        style="padding: 10px; border: 1px solid #e2e8f0; border-radius: 8px;">
                    <select id="new-role" style="padding: 10px; border: 1px solid #e2e8f0; border-radius: 8px;">
                        <option value="Manager">Manager</option>
                        <option value="Editor">Editor</option>
                        <option value="SuperAdmin">Super Admin (Careful!)</option>
                    </select>
                    <div style="grid-column: span 2; display: flex; align-items: center; gap: 10px;">
                        <input type="checkbox" id="new-pos-access" checked>
                        <label for="new-pos-access">Enable POS Access immediately</label>
                    </div>
                    <div style="grid-column: span 2; display: flex; gap: 10px; margin-top: 1rem;">
                        <button type="submit" class="btn-renew" style="background: #0f172a;">Save User</button>
                        <button type="button" onclick="toggleUserForm()" class="btn-renew"
                            style="background: #f1f5f9; color: #64748b;">Cancel</button>
                    </div>
                </form>
            </div>

            <!-- Users Table -->
            <div class="info-card" style="padding: 1.5rem; overflow-x: auto;">
                <div style="margin-bottom: 1rem; font-size: 0.85rem; color: #64748b;">
                    <i class="fa-solid fa-circle-info"></i> POS Access Limit: <strong id="pos-access-count">0</strong> /
                    <span id="pos-max-count">3</span> users active
                </div>
                <table style="width: 100%; border-collapse: collapse; min-width: 600px;">
                    <thead>
                        <tr style="text-align: left; border-bottom: 2px solid #f1f5f9;">
                            <th style="padding: 1rem; font-size: 0.75rem; text-transform: uppercase; color: #94a3b8;">
                                User</th>
                            <th style="padding: 1rem; font-size: 0.75rem; text-transform: uppercase; color: #94a3b8;">
                                Email</th>
                            <th style="padding: 1rem; font-size: 0.75rem; text-transform: uppercase; color: #94a3b8;">
                                Role</th>
                            <th style="padding: 1rem; font-size: 0.75rem; text-transform: uppercase; color: #94a3b8;">
                                POS Access</th>
                            <th style="padding: 1rem; font-size: 0.75rem; text-transform: uppercase; color: #94a3b8;">
                                Action</th>
                        </tr>
                    </thead>
                    <tbody id="users-table-body">
                        <!-- Loaded dynamically -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="../assets/sidebar.js"></script>
    <script>
        // Update subscription date from global config
        document.getElementById('sub-page-date').innerText = window.NEXT_PAYMENT_DISPLAY || 'May 1, 2026';

        function updateProfilePage() {
            if (window.posUserName) {
                document.getElementById('profile-username').innerText = '@' + (window.posUserName.toLowerCase().replace(/\s+/g, '_'));

                // Show management if super admin
                const userRole = (window.posUserRole || '').toLowerCase();
                if (userRole === 'super admin' || userRole === 'superadmin') {
                    document.getElementById('admin-management-section').style.display = 'block';
                    loadAdminUsers();

                    // Check for anchor
                    if (window.location.hash === '#admin-management-section') {
                        setTimeout(() => {
                            document.getElementById('admin-management-section').scrollIntoView({ behavior: 'smooth' });
                        }, 300);
                    }
                }
            } else {
                setTimeout(updateProfilePage, 100);
            }
        }

        async function loadAdminUsers() {
            try {
                const res = await fetch('../../api/controllers/AdminController.php?action=list');
                const data = await res.json();
                if (data.success) {
                    const tbody = document.getElementById('users-table-body');
                    tbody.innerHTML = '';

                    let activeCount = 0;
                    data.users.forEach(user => {
                        if (user.pos_access == 1) activeCount++;

                        const roleLower = (user.role || '').toLowerCase().replace(/\s+/g, '');
                        const displayRole = user.role === 'SuperAdmin' ? 'Super Admin' : user.role;
                        
                        const tr = document.createElement('tr');
                        tr.style.borderBottom = '1px solid #f8fafc';
                        tr.innerHTML = `
                            <td style="padding: 1rem;">
                                <div style="font-weight: 700; color: #1e293b;">${user.full_name}</div>
                                <div style="font-size: 0.75rem; color: #94a3b8;">@${user.username}</div>
                            </td>
                            <td style="padding: 1rem; font-size: 0.85rem; color: #64748b;">
                                ${user.email || '<i style="color: #cbd5e1;">Not set</i>'}
                            </td>
                            <td style="padding: 1rem;">
                                <span class="role-badge role-${roleLower}" style="font-size: 0.65rem; font-weight: 800; padding: 2px 8px; border-radius: 6px; text-transform: uppercase;">${displayRole}</span>
                            </td>
                            <td style="padding: 1rem;">
                                <label class="switch" style="position: relative; display: inline-block; width: 44px; height: 22px;">
                                    <input type="checkbox" ${user.pos_access == 1 ? 'checked' : ''} onchange="togglePosAccess(${user.id}, this.checked)" style="opacity: 0; width: 0; height: 0;">
                                    <span style="position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: ${user.pos_access == 1 ? '#10b981' : '#cbd5e1'}; transition: .4s; border-radius: 34px;">
                                        <span style="position: absolute; content: ''; height: 16px; width: 16px; left: 3px; bottom: 3px; background-color: white; transition: .4s; border-radius: 50%; transform: ${user.pos_access == 1 ? 'translateX(22px)' : 'none'};"></span>
                                    </span>
                                </label>
                            </td>
                            <td style="padding: 1rem;">
                                <button onclick="deleteUser(${user.id})" style="border: none; background: none; color: #ef4444; cursor: pointer; opacity: 0.6;"><i class="fa-solid fa-trash"></i></button>
                            </td>
                        `;
                        tbody.appendChild(tr);
                    });

                    document.getElementById('pos-access-count').innerText = activeCount;
                    document.getElementById('pos-max-count').innerText = data.max_pos_users;
                }
            } catch (e) { console.error('Failed to load users', e); }
        }

        // Role colors moved to CSS classes in sidebar.js/auth-guard.js

        async function togglePosAccess(userId, status) {
            try {
                const res = await fetch('../../api/controllers/AdminController.php?action=toggle_access', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ user_id: userId, status: status ? 1 : 0 })
                });
                const data = await res.json();
                if (!data.success) {
                    alert(data.error || 'Failed to update access');
                    loadAdminUsers(); // Refresh to undo toggle
                } else {
                    loadAdminUsers(); // Refresh counts
                }
            } catch (e) { alert('Request failed'); loadAdminUsers(); }
        }

        function toggleUserForm() {
            const form = document.getElementById('new-user-form');
            form.style.display = form.style.display === 'none' ? 'block' : 'none';
        }

        document.getElementById('create-user-form').onsubmit = async (e) => {
            e.preventDefault();
            const payload = {
                full_name: document.getElementById('new-full-name').value,
                email: document.getElementById('new-email').value,
                username: document.getElementById('new-username').value,
                password: document.getElementById('new-password').value,
                role: document.getElementById('new-role').value,
                pos_access: document.getElementById('new-pos-access').checked ? 1 : 0
            };

            try {
                const res = await fetch('../../api/controllers/AdminController.php?action=create', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                if (data.success) {
                    alert('User created successfully!');
                    toggleUserForm();
                    loadAdminUsers();
                } else {
                    alert(data.error);
                }
            } catch (e) { alert('Creation failed'); }
        };

        async function deleteUser(id) {
            if (!confirm('Are you sure you want to delete this user?')) return;
            try {
                const res = await fetch('../../api/controllers/AdminController.php?action=delete&id=' + id);
                const data = await res.json();
                if (data.success) loadAdminUsers();
                else alert(data.error);
            } catch (e) { alert('Delete failed'); }
        }

        updateProfilePage();
    </script>
</body>

</html>