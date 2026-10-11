<?php require_once '../../api/shared/auth_check.php'; checkAuth(true); renderUserUI(true); ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Member Directory | Ontomeel POS</title>
    <link rel="stylesheet" href="../assets/pos-styles.css?v=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        .member-container {
            background: white;
            border-radius: 24px;
            border: 1px solid var(--border-light);
            padding: 2rem;
            box-shadow: var(--shadow-md);
        }

        .member-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .member-table-wrapper {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.9rem;
        }

        th {
            text-align: left;
            padding: 1rem;
            color: var(--text-muted);
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.75rem;
            border-bottom: 1px solid var(--border-light);
            white-space: nowrap;
        }

        td {
            padding: 1.15rem 1rem;
            border-bottom: 1px solid var(--border-light);
            color: var(--text-body);
            vertical-align: middle;
        }

        tr:hover td {
            background: var(--primary-blue-soft);
        }

        .member-clickable {
            cursor: pointer;
            transition: color 0.2s;
        }

        .member-clickable:hover {
            color: var(--primary-blue);
        }

        .plan-badge {
            padding: 4px 10px;
            border-radius: 30px;
            font-size: 0.7rem;
            font-weight: 800;
            text-transform: uppercase;
            display: inline-block;
            letter-spacing: 0.3px;
        }

        .plan-none {
            background: #f1f5f9;
            color: #64748b;
        }

        .plan-general {
            background: #ecfdf5;
            color: #10b981;
        }

        .plan-booklover {
            background: #eff6ff;
            color: #2563eb;
        }

        .plan-collector {
            background: #fdf2f8;
            color: #db2777;
        }

        .plan-student {
            background: #fef3c7;
            color: #d97706;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 10px;
            border-radius: 30px;
            font-size: 0.72rem;
            font-weight: 800;
            text-transform: uppercase;
        }

        .status-active {
            background: #ecfdf5;
            color: #059669;
        }

        .status-inactive {
            background: #fef2f2;
            color: #dc2626;
        }

        .member-search-bar {
            background: #f1f5f9;
            border: none;
            padding: 10px 16px;
            border-radius: 12px;
            width: 320px;
            font-size: 0.9rem;
            outline: none;
            border: 1px solid transparent;
            transition: 0.2s;
        }

        .member-search-bar:focus {
            background: white;
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .balance-text {
            font-weight: 700;
            color: #16a34a;
        }

        .expiry-text {
            font-size: 0.75rem;
            color: #ef4444;
            font-weight: 600;
            margin-top: 2px;
        }

        .member-stats-row {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1.25rem;
            margin-bottom: 2rem;
        }

        @media (max-width: 900px) {
            .member-stats-row {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        /* Action Buttons */
        .actions-cell {
            display: flex;
            align-items: center;
            gap: 8px;
            justify-content: flex-end;
        }

        .btn-edit {
            background: #eff6ff;
            color: #2563eb;
            border: 1px solid #bfdbfe;
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 0.75rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            white-space: nowrap;
        }

        .btn-edit:hover {
            background: #dbeafe;
            transform: translateY(-1px);
            box-shadow: var(--shadow-sm);
        }

        .btn-fund {
            background: #f0fdf4;
            color: #16a34a;
            border: 1px solid #bbf7d0;
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 0.75rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            white-space: nowrap;
        }

        .btn-fund:hover {
            background: #dcfce7;
            transform: translateY(-1px);
            box-shadow: var(--shadow-sm);
        }

        /* Modal Styling */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(15, 23, 42, 0.45);
            backdrop-filter: blur(8px);
            z-index: 1000;
            display: none;
            align-items: center;
            justify-content: center;
            animation: fadeIn 0.25s ease;
            padding: 1rem;
        }

        .modal-content {
            background: white;
            width: 440px;
            max-width: 95vw;
            border-radius: 28px;
            overflow: hidden;
            box-shadow: 0 25px 70px -10px rgba(15, 23, 42, 0.25);
            animation: modalPop 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
            border: 1px solid rgba(255, 255, 255, 0.8);
            display: flex;
            flex-direction: column;
            max-height: 90vh;
        }

        .modal-content.modal-lg {
            width: 820px;
        }

        .modal-header {
            padding: 1.5rem 2rem;
            background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%);
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-shrink: 0;
        }

        .modal-body {
            padding: 1.75rem 2rem;
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
            overflow-y: auto;
        }

        .modal-footer {
            padding: 1.25rem 2rem;
            background: #f8fafc;
            border-top: 1px solid #f1f5f9;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            flex-shrink: 0;
        }

        @keyframes modalPop {
            from {
                transform: scale(0.94);
                opacity: 0;
            }

            to {
                transform: scale(1);
                opacity: 1;
            }
        }

        .input-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .input-label {
            font-size: 0.72rem;
            font-weight: 800;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-left: 2px;
        }

        .checkout-input {
            width: 100%;
            padding: 11px 15px;
            border: 1.5px solid #e2e8f0;
            border-radius: 14px;
            font-size: 0.92rem;
            font-weight: 500;
            transition: all 0.2s;
            outline: none;
            background: #f8fafc;
            box-sizing: border-box;
            color: var(--text-header);
        }

        .checkout-input:focus {
            border-color: var(--primary-blue);
            background: white;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.08);
        }

        .form-grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        @media (max-width: 650px) {
            .form-grid-2 {
                grid-template-columns: 1fr;
            }
        }

        /* Detail Modal Components */
        .member-profile-banner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: linear-gradient(135deg, #eff6ff 0%, #f8fafc 100%);
            border: 1px solid #dbeafe;
            padding: 1.25rem 1.5rem;
            border-radius: 18px;
            margin-bottom: 0.5rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .member-banner-left {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .detail-avatar-circle {
            width: 54px;
            height: 54px;
            border-radius: 16px;
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            font-weight: 800;
            box-shadow: 0 8px 16px -4px rgba(37, 99, 235, 0.3);
            flex-shrink: 0;
        }

        .detail-tabs-nav {
            display: flex;
            gap: 8px;
            border-bottom: 1.5px solid #e2e8f0;
            margin-bottom: 1rem;
        }

        .detail-tab-btn {
            background: none;
            border: none;
            padding: 10px 16px;
            font-size: 0.85rem;
            font-weight: 700;
            color: var(--text-muted);
            cursor: pointer;
            border-bottom: 2.5px solid transparent;
            margin-bottom: -1.5px;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .detail-tab-btn.active {
            color: var(--primary-blue);
            border-bottom-color: var(--primary-blue);
        }

        .detail-tab-btn:hover:not(.active) {
            color: var(--text-header);
        }

        .detail-mini-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 0.85rem;
            margin-bottom: 1rem;
        }

        .mini-stat-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 0.85rem 1rem;
            display: flex;
            flex-direction: column;
            gap: 3px;
        }

        .mini-stat-label {
            font-size: 0.68rem;
            font-weight: 800;
            text-transform: uppercase;
            color: var(--text-muted);
            letter-spacing: 0.3px;
        }

        .mini-stat-val {
            font-size: 1.15rem;
            font-weight: 800;
            color: var(--text-header);
        }

        /* History Table in Modal */
        .history-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.82rem;
            margin-top: 0.5rem;
        }

        .history-table th {
            padding: 8px 10px;
            font-size: 0.7rem;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
        }

        .history-table td {
            padding: 10px 10px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 0.82rem;
        }

        .history-header {
            font-size: 0.85rem;
            font-weight: 800;
            color: var(--text-header);
            margin: 1.25rem 0 0.5rem 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .history-header:first-of-type {
            margin-top: 0;
        }

        /* Toast Notifications */
        #toastContainer {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 2000;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .toast {
            background: rgba(15, 23, 42, 0.95);
            backdrop-filter: blur(10px);
            color: white;
            padding: 14px 20px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 0.88rem;
            font-weight: 600;
            min-width: 300px;
            border-left: 4px solid var(--primary-blue);
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            animation: toastSlide 0.4s ease;
        }

        .toast.success { border-left-color: #10b981; }
        .toast.error { border-left-color: #ef4444; }

        @keyframes toastSlide {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        /* Skeleton Loading */
        .skeleton {
            background: linear-gradient(90deg, #f1f5f9 25%, #e2e8f0 50%, #f1f5f9 75%);
            background-size: 200% 100%;
            animation: skeletonLoading 1.5s infinite;
            border-radius: 4px;
            display: inline-block;
        }

        @keyframes skeletonLoading {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }

        .skeleton-text { height: 1rem; width: 100%; }
        .skeleton-badge { height: 1.5rem; width: 80px; border-radius: 20px; }
        .skeleton-id { height: 1.2rem; width: 100px; }

        .load-more-container {
            display: flex;
            justify-content: center;
            padding: 2rem 0;
        }

        .btn-load-more {
            background: white;
            border: 1px solid var(--border-light);
            padding: 0.75rem 2rem;
            border-radius: 12px;
            font-weight: 700;
            color: var(--text-header);
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .btn-load-more:hover {
            background: #f8fafc;
            border-color: var(--primary-blue);
            color: var(--primary-blue);
        }

        .btn-load-more:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
    </style>
</head>

<body>

    <script src="../assets/sidebar.js"></script>

    <div class="main-wrapper">
        <header>
            <div class="page-title">
                <h1>Member <span style="color: var(--primary-blue);">Directory</span></h1>
                <p>Manage community members, contact details, subscriptions, and profiles</p>
            </div>
            <div class="header-tools" style="display: flex; align-items: center; gap: 1rem;">
                <div class="user-profile" style="display: flex; align-items: center; gap: 12px; margin-right: 1.5rem;">
                    <div style="text-align: right;">
                        <div style="font-size: 0.85rem; font-weight: 700; color: var(--text-header);"
                            class="user-name-display">Admin</div>
                        <div
                            style="font-size: 0.7rem; color: var(--accent-mint); font-weight: 800; text-transform: uppercase;">
                            Staff</div>
                    </div>
                </div>
                <input type="text" id="memberFilter" class="member-search-bar" placeholder="Search by name, ID, phone..."
                    onkeyup="filterTable()">
                <div class="user-profile">
                    <img src="https://ui-avatars.com/api/?name=Admin&background=eff6ff&color=2563eb&bold=true"
                        alt="Admin"
                        style="width: 44px; height: 44px; border-radius: 12px; border: 1px solid var(--border-light);">
                </div>
            </div>
        </header>

        <div class="member-stats-row">
            <div class="stat-card" style="padding: 1.25rem;">
                <div class="stat-title">Total Members</div>
                <div class="stat-value" id="statTotalMembers" style="font-size: 1.5rem;">0</div>
            </div>
            <div class="stat-card" style="padding: 1.25rem;">
                <div class="stat-title">Active Members</div>
                <div class="stat-value" id="statActiveMembers" style="font-size: 1.5rem; color: #10b981;">0</div>
            </div>
            <div class="stat-card" style="padding: 1.25rem;">
                <div class="stat-title">Total Funds</div>
                <div class="stat-value" id="statTotalFunds" style="font-size: 1.5rem;">৳0</div>
            </div>
            <div class="stat-card" style="padding: 1.25rem;">
                <div class="stat-title">Active Plans</div>
                <div class="stat-value" id="statActivePlans" style="font-size: 1.5rem;">0</div>
            </div>
        </div>

        <div class="member-container">
            <div class="member-header">
                <div>
                    <div style="font-weight: 800; font-size: 1.15rem; color: var(--text-header);">Member List</div>
                    <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 2px;">
                        Click on any member or click <span style="font-weight: 700; color: var(--primary-blue);">Details</span> to view and edit details.
                    </div>
                </div>
                <div style="display: flex; gap: 10px;">
                    <button class="nav-link" onclick="openFundModal()"
                        style="padding: 8px 16px; font-size: 0.8rem; border: 1px solid var(--border-light); cursor: pointer;">
                        <i class="fa-solid fa-hand-holding-dollar"></i> ADD FUND
                    </button>
                    <button class="nav-link active" onclick="openRegisterModal()"
                        style="padding: 8px 16px; font-size: 0.8rem; cursor: pointer;">
                        <i class="fa-solid fa-user-plus"></i> REGISTER NEW
                    </button>
                </div>
            </div>

            <div class="member-table-wrapper">
                <table id="memberTable">
                    <thead>
                        <tr>
                            <th>Member ID</th>
                            <th>Full Name</th>
                            <th>Contact Info</th>
                            <th>Address</th>
                            <th>Current Plan</th>
                            <th>Balance</th>
                            <th>Status</th>
                            <th style="text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="memberTableBody">
                        <!-- Dynamic Content -->
                    </tbody>
                </table>
            </div>

            <div class="load-more-container" id="loadMoreContainer">
                <button class="btn-load-more" id="btnLoadMore" onclick="loadMoreMembers()">
                    <span>Load More Members</span>
                    <i class="fa-solid fa-chevron-down"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Member Details & Edit Modal -->
    <div class="modal-overlay" id="memberDetailsModal">
        <div class="modal-content modal-lg">
            <div class="modal-header">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div style="width: 38px; height: 38px; border-radius: 10px; background: #eff6ff; color: var(--primary-blue); display: flex; align-items: center; justify-content: center; font-size: 1.1rem;">
                        <i class="fa-solid fa-id-card"></i>
                    </div>
                    <div>
                        <h2 style="margin: 0; font-size: 1.25rem; font-weight: 800; color: var(--text-header);">Member Details & Settings</h2>
                        <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 1px;">View and edit personal info, address, subscription, and status</div>
                    </div>
                </div>
                <button onclick="closeModals()"
                    style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #94a3b8; transition: 0.2s; padding: 10px; margin: -10px;">&times;</button>
            </div>

            <div class="modal-body" id="detailModalBody">
                <!-- Profile Banner -->
                <div class="member-profile-banner">
                    <div class="member-banner-left">
                        <div class="detail-avatar-circle" id="detailAvatarLetter">M</div>
                        <div>
                            <div style="font-size: 1.15rem; font-weight: 800; color: var(--text-header);" id="detailHeaderName">Member Name</div>
                            <div style="display: flex; align-items: center; gap: 10px; margin-top: 4px; flex-wrap: wrap;">
                                <code style="font-weight: 800; color: var(--primary-blue); font-size: 0.85rem;" id="detailHeaderId">OM-XXXX-XXXX</code>
                                <span class="status-badge" id="detailHeaderStatus">Active</span>
                                <span class="plan-badge" id="detailHeaderPlan">None</span>
                            </div>
                        </div>
                    </div>
                    <div style="text-align: right;">
                        <div style="font-size: 0.72rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase;">Current Wallet Balance</div>
                        <div style="font-size: 1.35rem; font-weight: 800; color: #16a34a; margin-top: 2px;" id="detailHeaderBalance">৳0.00</div>
                        <button type="button" class="btn-fund" onclick="openFundModalForCurrentDetail()" style="margin-top: 6px; padding: 4px 10px; font-size: 0.7rem;">
                            <i class="fa-solid fa-plus"></i> Top Up Wallet
                        </button>
                    </div>
                </div>

                <!-- Tabs Navigation -->
                <div class="detail-tabs-nav">
                    <button type="button" class="detail-tab-btn active" id="tabBtnProfile" onclick="switchDetailTab('profile')">
                        <i class="fa-solid fa-user-pen"></i> Edit Profile Details
                    </button>
                    <button type="button" class="detail-tab-btn" id="tabBtnHistory" onclick="switchDetailTab('history')">
                        <i class="fa-solid fa-clock-rotate-left"></i> Activity & History
                    </button>
                </div>

                <!-- Tab 1: Profile & Edit Form -->
                <div id="tabContentProfile">
                    <form id="editMemberForm" onsubmit="event.preventDefault(); saveMemberDetails();">
                        <input type="hidden" id="editMemberId">

                        <div class="form-grid-2">
                            <div class="input-group">
                                <label class="input-label">Full Name <span style="color: #ef4444;">*</span></label>
                                <input type="text" id="editFullName" class="checkout-input" required placeholder="e.g. John Doe">
                            </div>
                            <div class="input-group">
                                <label class="input-label">Phone Number <span style="color: #ef4444;">*</span></label>
                                <input type="text" id="editPhone" class="checkout-input" required placeholder="e.g. 01700000000">
                            </div>
                        </div>

                        <div class="form-grid-2" style="margin-top: 1rem;">
                            <div class="input-group">
                                <label class="input-label">Email Address</label>
                                <input type="email" id="editEmail" class="checkout-input" placeholder="e.g. member@example.com">
                            </div>
                            <div class="input-group">
                                <label class="input-label">Membership Plan</label>
                                <select id="editPlan" class="checkout-input">
                                    <option value="None">None</option>
                                    <option value="General">General</option>
                                    <option value="BookLover">BookLover</option>
                                    <option value="Collector">Collector</option>
                                    <option value="Student">Student</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-grid-2" style="margin-top: 1rem;">
                            <div class="input-group">
                                <label class="input-label">Plan Expiration Date</label>
                                <input type="date" id="editPlanExpire" class="checkout-input">
                            </div>
                            <div class="input-group">
                                <label class="input-label">Account Status</label>
                                <select id="editIsActive" class="checkout-input">
                                    <option value="1">Active (Normal Access)</option>
                                    <option value="0">Inactive / Suspended</option>
                                </select>
                            </div>
                        </div>

                        <div class="input-group" style="margin-top: 1rem;">
                            <label class="input-label">Full Address / Location</label>
                            <textarea id="editAddress" class="checkout-input" rows="2" placeholder="e.g. House #, Road #, Area, City..."></textarea>
                        </div>

                        <div class="input-group" style="margin-top: 1rem;">
                            <label class="input-label">Reset Password (Optional)</label>
                            <input type="password" id="editPassword" class="checkout-input" placeholder="Leave blank to keep unchanged">
                            <span style="font-size: 0.72rem; color: var(--text-muted); margin-left: 4px;">Only enter a value if you need to reset the member's login password.</span>
                        </div>
                    </form>
                </div>

                <!-- Tab 2: Activity & History -->
                <div id="tabContentHistory" style="display: none;">
                    <!-- Mini Stats -->
                    <div class="detail-mini-stats">
                        <div class="mini-stat-card">
                            <div class="mini-stat-label">Active Borrows</div>
                            <div class="mini-stat-val" id="miniStatActiveBorrows" style="color: var(--primary-blue);">0</div>
                        </div>
                        <div class="mini-stat-card">
                            <div class="mini-stat-label">Total Borrows</div>
                            <div class="mini-stat-val" id="miniStatTotalBorrows">0</div>
                        </div>
                        <div class="mini-stat-card">
                            <div class="mini-stat-label">Total Spend (Orders)</div>
                            <div class="mini-stat-val" id="miniStatTotalSpend" style="color: #16a34a;">৳0.00</div>
                        </div>
                    </div>

                    <!-- Borrows Section -->
                    <div class="history-header"><i class="fa-solid fa-book-bookmark" style="color: var(--primary-blue);"></i> Recent Borrowings</div>
                    <div style="overflow-x: auto; max-height: 180px; border: 1px solid #e2e8f0; border-radius: 12px;">
                        <table class="history-table">
                            <thead>
                                <tr>
                                    <th>Book</th>
                                    <th>Borrow Date</th>
                                    <th>Due Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="detailBorrowsBody">
                                <tr><td colspan="4" style="text-align: center; color: var(--text-muted);">No borrow records.</td></tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Orders Section -->
                    <div class="history-header"><i class="fa-solid fa-bag-shopping" style="color: #10b981;"></i> Recent POS Orders</div>
                    <div style="overflow-x: auto; max-height: 180px; border: 1px solid #e2e8f0; border-radius: 12px;">
                        <table class="history-table">
                            <thead>
                                <tr>
                                    <th>Invoice #</th>
                                    <th>Date</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="detailOrdersBody">
                                <tr><td colspan="4" style="text-align: center; color: var(--text-muted);">No purchase records.</td></tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Transactions Section -->
                    <div class="history-header"><i class="fa-solid fa-receipt" style="color: #f59e0b;"></i> Recent Wallet Transactions</div>
                    <div style="overflow-x: auto; max-height: 180px; border: 1px solid #e2e8f0; border-radius: 12px;">
                        <table class="history-table">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Amount</th>
                                    <th>Description</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody id="detailTxBody">
                                <tr><td colspan="4" style="text-align: center; color: var(--text-muted);">No wallet transactions.</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="modal-footer" id="detailModalFooter">
                <button type="button" class="btn-load-more" onclick="closeModals()" style="padding: 10px 20px; font-size: 0.85rem;">
                    Close
                </button>
                <button type="button" class="nav-link active" id="btnSaveMemberEdit" onclick="saveMemberDetails()"
                    style="padding: 10px 24px; font-size: 0.85rem; font-weight: 700; border-radius: 12px; cursor: pointer;">
                    <i class="fa-solid fa-floppy-disk"></i> Save Changes
                </button>
            </div>
        </div>
    </div>

    <!-- Register Modal -->
    <div class="modal-overlay" id="registerModal">
        <div class="modal-content" style="width: 520px;">
            <div class="modal-header">
                <div>
                    <h2 style="margin:0; font-size:1.3rem; font-weight:800; color:var(--text-header);">Register Member</h2>
                    <div style="font-size:0.75rem; color:var(--text-muted); margin-top:2px;">Add a new community or library member</div>
                </div>
                <button onclick="closeModals()"
                    style="background:none; border:none; font-size:1.5rem; cursor:pointer; color:#94a3b8; transition:0.2s; padding:10px; margin:-10px;">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-grid-2">
                    <div class="input-group">
                        <label class="input-label">Full Name <span style="color:#ef4444;">*</span></label>
                        <input type="text" id="regName" class="checkout-input" placeholder="e.g. John Doe">
                    </div>
                    <div class="input-group">
                        <label class="input-label">Phone Number <span style="color:#ef4444;">*</span></label>
                        <input type="text" id="regPhone" class="checkout-input" placeholder="e.g. 01700000000">
                    </div>
                </div>
                <div class="form-grid-2">
                    <div class="input-group">
                        <label class="input-label">Email Address (Optional)</label>
                        <input type="email" id="regEmail" class="checkout-input" placeholder="e.g. john@example.com">
                    </div>
                    <div class="input-group">
                        <label class="input-label">Membership Plan</label>
                        <select id="regPlan" class="checkout-input">
                            <option value="None">None</option>
                            <option value="General">General</option>
                            <option value="BookLover">BookLover</option>
                            <option value="Collector">Collector</option>
                            <option value="Student">Student</option>
                        </select>
                    </div>
                </div>
                <div class="input-group">
                    <label class="input-label">Address (Optional)</label>
                    <textarea id="regAddress" class="checkout-input" rows="2" placeholder="Full address or area..."></textarea>
                </div>
                <button onclick="saveMember()" id="btnSaveReg" class="nav-link active"
                    style="width:100%; justify-content:center; padding:14px; border-radius:16px; margin-top:0.5rem; font-weight:700; cursor:pointer;">
                    <i class="fa-solid fa-user-plus"></i> Create Account
                </button>
            </div>
        </div>
    </div>

    <!-- Add Fund Modal -->
    <div class="modal-overlay" id="fundModal">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h2 style="margin:0; font-size:1.3rem; font-weight:800; color:var(--text-header);">Add Funds</h2>
                    <div style="font-size:0.75rem; color:var(--text-muted); margin-top:2px;">Top up member's wallet balance</div>
                </div>
                <button onclick="closeModals()"
                    style="background:none; border:none; font-size:1.5rem; cursor:pointer; color:#94a3b8; transition:0.2s; padding:10px; margin:-10px;">&times;</button>
            </div>

            <div class="modal-body">
                <div id="fundMemberSelector" class="input-group">
                    <label class="input-label">Member Search</label>
                    <div style="position:relative;">
                        <input type="text" id="fundSearch" class="checkout-input" placeholder="Search Member ID, Name or Phone..."
                            onkeyup="searchForFund()">
                        <div id="fundSearchResults"
                            style="position:absolute; top:calc(100% + 8px); left:0; right:0; background:white; border:1px solid #e2e8f0; border-radius:18px; z-index:10; display:none; max-height:220px; overflow-y:auto; box-shadow:0 15px 30px -5px rgba(0,0,0,0.15);">
                        </div>
                    </div>
                </div>

                <div id="selectedFundMember"
                    style="display:none; background:#f0f7ff; padding:1.25rem; border-radius:18px; margin-bottom:5px; border:1px solid #dbeafe; animation:fadeIn 0.3s ease;">
                    <div style="display:flex; justify-content:space-between; align-items:center;">
                        <div>
                            <div id="fundName" style="font-weight:800; color:var(--primary-blue); font-size:1rem;">
                            </div>
                            <div id="fundId"
                                style="font-size:0.75rem; color:var(--text-muted); font-weight:600; font-family:monospace; margin-top:2px;">
                            </div>
                        </div>
                        <button onclick="clearFundMember()"
                            style="background:none; border:none; color:#ef4444; cursor:pointer; padding:8px;"><i
                                class="fa-solid fa-times-circle fa-lg"></i></button>
                    </div>
                </div>

                <div class="input-group">
                    <label class="input-label">Amount (৳)</label>
                    <input type="number" id="fundAmount" class="checkout-input" placeholder="0.00" step="any" min="1">
                </div>

                <button onclick="processAddFund()" id="btnSaveFund" class="nav-link active"
                    style="width:100%; justify-content:center; background:#10b981; padding:14px; border-radius:16px; font-weight:700; border:none; margin-top:0.5rem; cursor:pointer;">
                    <i class="fa-solid fa-money-bill-trend-up"></i> Add To Balance
                </button>
            </div>
        </div>
    </div>

    <div id="toastContainer"></div>

    <script>
        let allMembers = [];
        let currentOffset = 0;
        const limit = 50;
        let isSearchActive = false;
        let currentDetailMember = null;
        let activeDetailTab = 'profile';

        // --- Helper: Escape HTML ---
        function escapeHtml(str) {
            if (!str) return '';
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        // --- Fetch & Render Members ---
        async function fetchMembers(isLoadMore = false) {
            if (!isLoadMore) {
                currentOffset = 0;
                renderSkeletons();
                isSearchActive = false;
            }

            try {
                const response = await fetch(`../../api/controllers/MemberController.php?action=list&limit=${limit}&offset=${currentOffset}`);
                const data = await response.json();
                
                if (data.success) {
                    if (isLoadMore) {
                        allMembers = [...allMembers, ...data.members];
                        appendMembers(data.members);
                    } else {
                        allMembers = data.members;
                        renderMembers(allMembers);
                    }
                    
                    currentOffset += data.members.length;
                    
                    document.getElementById('loadMoreContainer').style.display = data.members.length < limit ? 'none' : 'flex';
                    updateStats();
                }
            } catch (e) {
                console.error("Error fetching members:", e);
                showToast("Failed to load members.", "error");
            }
        }

        function loadMoreMembers() {
            const btn = document.getElementById('btnLoadMore');
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> LOADING...';
            
            fetchMembers(true).finally(() => {
                btn.disabled = false;
                btn.innerHTML = '<span>Load More Members</span><i class="fa-solid fa-chevron-down"></i>';
            });
        }

        function renderSkeletons() {
            const body = document.getElementById('memberTableBody');
            let skeletons = '';
            for (let i = 0; i < 8; i++) {
                skeletons += `
                    <tr>
                        <td><div class="skeleton skeleton-id"></div></td>
                        <td>
                            <div class="skeleton skeleton-text" style="width: 70%; margin-bottom: 5px;"></div>
                            <div class="skeleton skeleton-text" style="width: 40%;"></div>
                        </td>
                        <td>
                            <div class="skeleton skeleton-text" style="width: 60%; margin-bottom: 5px;"></div>
                            <div class="skeleton skeleton-text" style="width: 50%;"></div>
                        </td>
                        <td><div class="skeleton skeleton-text" style="width: 80%;"></div></td>
                        <td><div class="skeleton skeleton-badge"></div></td>
                        <td><div class="skeleton skeleton-text" style="width: 50px;"></div></td>
                        <td><div class="skeleton skeleton-badge" style="width: 60px;"></div></td>
                        <td><div class="skeleton skeleton-text" style="width: 120px;"></div></td>
                    </tr>
                `;
            }
            body.innerHTML = skeletons;
            document.getElementById('loadMoreContainer').style.display = 'none';
        }

        function renderMembers(members) {
            const body = document.getElementById('memberTableBody');
            if (members.length === 0) {
                body.innerHTML = `
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 3rem; color: var(--text-muted);">
                            <i class="fa-solid fa-user-slash" style="font-size: 2.5rem; margin-bottom: 1rem; display: block; opacity: 0.4;"></i>
                            No members found.
                        </td>
                    </tr>
                `;
                return;
            }
            body.innerHTML = members.map(m => createMemberRow(m)).join('');
        }

        function appendMembers(newMembers) {
            const body = document.getElementById('memberTableBody');
            newMembers.forEach(m => {
                body.insertAdjacentHTML('beforeend', createMemberRow(m));
            });
        }

        function createMemberRow(m) {
            const plan = m.membership_plan || 'None';
            const planClass = 'plan-' + plan.toLowerCase();
            const isActive = parseInt(m.is_active) === 1;
            const address = m.address ? escapeHtml(m.address) : '<span style="color:var(--text-muted); font-size:0.75rem; font-style:italic;">No address</span>';
            const addressSnippet = m.address ? 
                (m.address.length > 30 ? escapeHtml(m.address.substring(0, 30)) + '...' : escapeHtml(m.address)) :
                '<span style="color:var(--text-muted); font-size:0.78rem; font-style:italic;">No address</span>';

            const joinedDate = m.created_at ? new Date(m.created_at).toLocaleDateString() : 'N/A';
            const balance = parseFloat(m.acc_balance || 0).toFixed(2);

            return `
                <tr id="member-row-${m.id}">
                    <td>
                        <code class="member-clickable" onclick="openMemberDetailsModal(${m.id})" style="font-weight:800; color:var(--primary-blue);" title="View details">
                            ${escapeHtml(m.membership_id)}
                        </code>
                    </td>
                    <td>
                        <div class="member-clickable" onclick="openMemberDetailsModal(${m.id})" style="font-weight:700;" title="Click to view/edit details">
                            ${escapeHtml(m.full_name)}
                        </div>
                        <div style="font-size:0.75rem; color:var(--text-muted);">Joined: ${joinedDate}</div>
                    </td>
                    <td>
                        <div style="font-size:0.85rem;"><i class="fa-solid fa-phone" style="width:15px; font-size:0.7rem; color:var(--text-muted);"></i> ${escapeHtml(m.phone)}</div>
                        <div style="font-size:0.82rem; color:var(--text-muted);"><i class="fa-solid fa-envelope" style="width:15px; font-size:0.7rem;"></i> ${m.email ? escapeHtml(m.email) : 'N/A'}</div>
                    </td>
                    <td style="max-width: 180px;">
                        <div style="font-size:0.82rem; color:var(--text-body); white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" title="${m.address ? escapeHtml(m.address) : ''}">
                            <i class="fa-solid fa-location-dot" style="font-size:0.7rem; color:#ef4444; margin-right:4px;"></i>
                            ${addressSnippet}
                        </div>
                    </td>
                    <td>
                        <span class="plan-badge ${planClass}">${escapeHtml(plan)}</span>
                    </td>
                    <td><span class="balance-text">৳${balance}</span></td>
                    <td>
                        <span class="status-badge ${isActive ? 'status-active' : 'status-inactive'}">
                            <i class="fa-solid ${isActive ? 'fa-circle-check' : 'fa-circle-xmark'}"></i> ${isActive ? 'Active' : 'Inactive'}
                        </span>
                        ${m.plan_expire_date ? `<div class="expiry-text">Exp: ${new Date(m.plan_expire_date).toLocaleDateString()}</div>` : ''}
                    </td>
                    <td>
                        <div class="actions-cell">
                            <button class="btn-edit" onclick="openMemberDetailsModal(${m.id})" title="View and edit member details">
                                <i class="fa-solid fa-user-pen"></i> Details
                            </button>
                            <button class="btn-fund" onclick="openFundModalById(${m.id})" title="Top up funds">
                                <i class="fa-solid fa-plus"></i> Fund
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        }

        // --- Member Details & Edit Modal Logic ---
        async function openMemberDetailsModal(memberId) {
            const modal = document.getElementById('memberDetailsModal');
            modal.style.display = 'flex';
            switchDetailTab('profile');

            // Quick display from cache if available while fetching fresh data
            let member = allMembers.find(m => parseInt(m.id) === parseInt(memberId));
            if (member) {
                populateMemberDetailsUI(member);
            }

            try {
                const res = await fetch(`../../api/controllers/MemberController.php?action=get&id=${memberId}`);
                const data = await res.json();
                if (data.success) {
                    currentDetailMember = data.member;
                    populateMemberDetailsUI(data.member);
                    populateMemberHistoryUI(data);

                    // Update in allMembers array if exists
                    const idx = allMembers.findIndex(m => parseInt(m.id) === parseInt(memberId));
                    if (idx !== -1) {
                        allMembers[idx] = data.member;
                    }
                } else {
                    showToast(data.error || "Failed to load member details.", "error");
                }
            } catch (e) {
                console.error("Error loading member details:", e);
                showToast("Failed to fetch fresh member details.", "error");
            }
        }

        function populateMemberDetailsUI(m) {
            currentDetailMember = m;
            document.getElementById('editMemberId').value = m.id;
            document.getElementById('editFullName').value = m.full_name || '';
            document.getElementById('editPhone').value = m.phone || '';
            document.getElementById('editEmail').value = m.email || '';
            document.getElementById('editAddress').value = m.address || '';
            document.getElementById('editPlan').value = m.membership_plan || 'None';
            document.getElementById('editPlanExpire').value = m.plan_expire_date ? m.plan_expire_date.substring(0, 10) : '';
            document.getElementById('editIsActive').value = m.is_active !== undefined ? String(m.is_active) : '1';
            document.getElementById('editPassword').value = '';

            // Header Banner
            document.getElementById('detailHeaderName').innerText = m.full_name || 'Member Details';
            document.getElementById('detailHeaderId').innerText = m.membership_id || '';
            document.getElementById('detailAvatarLetter').innerText = (m.full_name ? m.full_name.charAt(0) : 'M').toUpperCase();
            document.getElementById('detailHeaderBalance').innerText = '৳' + parseFloat(m.acc_balance || 0).toFixed(2);

            const isActive = parseInt(m.is_active) === 1;
            const statusBadge = document.getElementById('detailHeaderStatus');
            statusBadge.className = `status-badge ${isActive ? 'status-active' : 'status-inactive'}`;
            statusBadge.innerHTML = `<i class="fa-solid ${isActive ? 'fa-circle-check' : 'fa-circle-xmark'}"></i> ${isActive ? 'Active' : 'Inactive'}`;

            const planBadge = document.getElementById('detailHeaderPlan');
            const plan = m.membership_plan || 'None';
            planBadge.className = `plan-badge plan-${plan.toLowerCase()}`;
            planBadge.innerText = plan;
        }

        function populateMemberHistoryUI(data) {
            // Stats
            if (data.borrow_stats) {
                document.getElementById('miniStatActiveBorrows').innerText = data.borrow_stats.active_borrows || 0;
                document.getElementById('miniStatTotalBorrows').innerText = data.borrow_stats.total_borrows || 0;
            }
            if (data.order_stats) {
                document.getElementById('miniStatTotalSpend').innerText = '৳' + parseFloat(data.order_stats.total_spent || 0).toFixed(2);
            }

            // Borrows Table
            const borrowsBody = document.getElementById('detailBorrowsBody');
            if (data.borrows && data.borrows.length > 0) {
                borrowsBody.innerHTML = data.borrows.map(b => `
                    <tr>
                        <td>
                            <div style="font-weight:700;">${escapeHtml(b.book_title)}</div>
                            <div style="font-size:0.72rem; color:var(--text-muted); font-family:monospace;">${escapeHtml(b.isbn || '')}</div>
                        </td>
                        <td>${new Date(b.borrow_date).toLocaleDateString()}</td>
                        <td>${b.due_date ? new Date(b.due_date).toLocaleDateString() : 'N/A'}</td>
                        <td>
                            <span class="plan-badge ${b.status === 'Active' ? 'plan-general' : (b.status === 'Overdue' ? 'status-inactive' : 'plan-none')}">
                                ${escapeHtml(b.status)}
                            </span>
                        </td>
                    </tr>
                `).join('');
            } else {
                borrowsBody.innerHTML = `<tr><td colspan="4" style="text-align:center; padding:1.25rem; color:var(--text-muted);">No book borrowing records.</td></tr>`;
            }

            // Orders Table
            const ordersBody = document.getElementById('detailOrdersBody');
            if (data.orders && data.orders.length > 0) {
                ordersBody.innerHTML = data.orders.map(o => `
                    <tr>
                        <td><code style="font-weight:700; color:var(--primary-blue);">${escapeHtml(o.invoice_no)}</code></td>
                        <td>${new Date(o.created_at).toLocaleDateString()}</td>
                        <td style="font-weight:700;">৳${parseFloat(o.total_amount).toFixed(2)}</td>
                        <td>
                            <span class="status-badge ${o.payment_status === 'Paid' ? 'status-active' : 'status-inactive'}">
                                ${escapeHtml(o.payment_status)}
                            </span>
                        </td>
                    </tr>
                `).join('');
            } else {
                ordersBody.innerHTML = `<tr><td colspan="4" style="text-align:center; padding:1.25rem; color:var(--text-muted);">No order history.</td></tr>`;
            }

            // Transactions Table
            const txBody = document.getElementById('detailTxBody');
            if (data.transactions && data.transactions.length > 0) {
                txBody.innerHTML = data.transactions.map(t => {
                    const isCredit = t.type === 'Deposit' || t.type === 'Refund';
                    return `
                        <tr>
                            <td><span class="plan-badge ${isCredit ? 'plan-general' : 'plan-none'}">${escapeHtml(t.type)}</span></td>
                            <td style="font-weight:800; color:${isCredit ? '#16a34a' : '#ef4444'};">
                                ${isCredit ? '+' : '-'}৳${parseFloat(t.amount).toFixed(2)}
                            </td>
                            <td>${escapeHtml(t.description || 'N/A')}</td>
                            <td style="font-size:0.75rem; color:var(--text-muted);">${new Date(t.created_at).toLocaleDateString()}</td>
                        </tr>
                    `;
                }).join('');
            } else {
                txBody.innerHTML = `<tr><td colspan="4" style="text-align:center; padding:1.25rem; color:var(--text-muted);">No wallet transactions.</td></tr>`;
            }
        }

        function switchDetailTab(tab) {
            activeDetailTab = tab;
            const btnProfile = document.getElementById('tabBtnProfile');
            const btnHistory = document.getElementById('tabBtnHistory');
            const contentProfile = document.getElementById('tabContentProfile');
            const contentHistory = document.getElementById('tabContentHistory');
            const btnSave = document.getElementById('btnSaveMemberEdit');

            if (tab === 'profile') {
                btnProfile.classList.add('active');
                btnHistory.classList.remove('active');
                contentProfile.style.display = 'block';
                contentHistory.style.display = 'none';
                btnSave.style.display = 'inline-flex';
            } else {
                btnProfile.classList.remove('active');
                btnHistory.classList.add('active');
                contentProfile.style.display = 'none';
                contentHistory.style.display = 'block';
                btnSave.style.display = 'none';
            }
        }

        async function saveMemberDetails() {
            const id = document.getElementById('editMemberId').value;
            const full_name = document.getElementById('editFullName').value.trim();
            const phone = document.getElementById('editPhone').value.trim();
            const email = document.getElementById('editEmail').value.trim();
            const address = document.getElementById('editAddress').value.trim();
            const membership_plan = document.getElementById('editPlan').value;
            const plan_expire_date = document.getElementById('editPlanExpire').value;
            const is_active = parseInt(document.getElementById('editIsActive').value);
            const password = document.getElementById('editPassword').value.trim();

            if (!full_name) return showToast('Full Name is required.', 'error');
            if (!phone) return showToast('Phone Number is required.', 'error');

            const btn = document.getElementById('btnSaveMemberEdit');
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> SAVING...';

            try {
                const res = await fetch('../../api/controllers/MemberController.php?action=update', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        id,
                        full_name,
                        phone,
                        email,
                        address,
                        membership_plan,
                        plan_expire_date: plan_expire_date || null,
                        is_active,
                        password: password || undefined
                    })
                });

                const data = await res.json();
                if (data.success) {
                    showToast('Member details updated successfully!', 'success');
                    populateMemberDetailsUI(data.member);

                    // Update row in allMembers array and table DOM
                    const idx = allMembers.findIndex(m => parseInt(m.id) === parseInt(id));
                    if (idx !== -1) {
                        allMembers[idx] = data.member;
                    }
                    const row = document.getElementById(`member-row-${id}`);
                    if (row) {
                        row.outerHTML = createMemberRow(data.member);
                    }
                    updateStats();
                } else {
                    showToast(data.error || 'Failed to update member.', 'error');
                }
            } catch (e) {
                console.error("Save error:", e);
                showToast('Failed to update member.', 'error');
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-floppy-disk"></i> Save Changes';
            }
        }

        function openFundModalForCurrentDetail() {
            if (currentDetailMember) {
                openFundModal(currentDetailMember);
            }
        }

        function openFundModalById(id) {
            const member = allMembers.find(m => parseInt(m.id) === parseInt(id));
            if (member) {
                openFundModal(member);
            } else {
                openFundModal();
            }
        }

        // --- Modals General Logic ---
        function openRegisterModal() {
            document.getElementById('registerModal').style.display = 'flex';
            document.getElementById('regName').value = '';
            document.getElementById('regPhone').value = '';
            document.getElementById('regEmail').value = '';
            document.getElementById('regAddress').value = '';
            document.getElementById('regPlan').value = 'None';
        }

        function closeModals() {
            document.querySelectorAll('.modal-overlay').forEach(m => m.style.display = 'none');
            clearFundMember();
        }

        // Close on escape key
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                closeModals();
            }
        });

        // Close on clicking outside modal
        document.querySelectorAll('.modal-overlay').forEach(overlay => {
            overlay.addEventListener('click', function (e) {
                if (e.target === overlay) {
                    closeModals();
                }
            });
        });

        // --- Fund Modal Logic ---
        let selectedFundMember = null;
        function openFundModal(member = null) {
            document.getElementById('fundModal').style.display = 'flex';
            if (member && member.id) selectFundMember(member);
        }

        async function searchForFund() {
            const q = document.getElementById('fundSearch').value;
            if (q.length < 2) {
                document.getElementById('fundSearchResults').style.display = 'none';
                return;
            }
            const res = await fetch(`../../api/controllers/MemberController.php?action=search&q=${encodeURIComponent(q)}`);
            const data = await res.json();
            const results = document.getElementById('fundSearchResults');
            if (data.members && data.members.length > 0) {
                results.innerHTML = data.members.map(m => `
                    <div style="padding:10px 14px; cursor:pointer; border-bottom:1px solid #f1f5f9; display:flex; justify-content:space-between; align-items:center;" onclick='selectFundMemberById(${m.id})'>
                        <div>
                            <div style="font-weight:700; font-size:0.85rem;">${escapeHtml(m.full_name)}</div>
                            <div style="font-size:0.75rem; color:var(--text-muted);">${escapeHtml(m.membership_id)} • ${escapeHtml(m.phone)}</div>
                        </div>
                        <div style="font-weight:700; color:#16a34a; font-size:0.85rem;">৳${parseFloat(m.acc_balance).toFixed(2)}</div>
                    </div>
                `).join('');
                results.style.display = 'block';
            } else {
                results.innerHTML = `<div style="padding:10px; color:var(--text-muted); font-size:0.85rem; text-align:center;">No members found</div>`;
                results.style.display = 'block';
            }
        }

        function selectFundMemberById(id) {
            const member = allMembers.find(m => parseInt(m.id) === parseInt(id));
            if (member) {
                selectFundMember(member);
            } else {
                fetch(`../../api/controllers/MemberController.php?action=get&id=${id}`)
                    .then(r => r.json())
                    .then(d => { if (d.success) selectFundMember(d.member); });
            }
        }

        function selectFundMember(member) {
            selectedFundMember = member;
            document.getElementById('fundMemberSelector').style.display = 'none';
            document.getElementById('selectedFundMember').style.display = 'block';
            document.getElementById('fundName').innerText = member.full_name;
            document.getElementById('fundId').innerText = `${member.membership_id} • Balance: ৳${parseFloat(member.acc_balance).toFixed(2)}`;
            document.getElementById('fundSearchResults').style.display = 'none';
            document.getElementById('fundAmount').focus();
        }

        function clearFundMember() {
            selectedFundMember = null;
            document.getElementById('fundMemberSelector').style.display = 'block';
            document.getElementById('selectedFundMember').style.display = 'none';
            document.getElementById('fundSearch').value = '';
            document.getElementById('fundAmount').value = '';
            document.getElementById('fundSearchResults').style.display = 'none';
        }

        // --- Register Logic ---
        async function saveMember() {
            const name = document.getElementById('regName').value.trim();
            const phone = document.getElementById('regPhone').value.trim();
            const email = document.getElementById('regEmail').value.trim();
            const address = document.getElementById('regAddress').value.trim();
            const plan = document.getElementById('regPlan').value;

            if (!name || !phone) return showToast('Name and Phone Number are required!', 'error');

            const btn = document.getElementById('btnSaveReg');
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> CREATING...';

            try {
                const res = await fetch('../../api/controllers/MemberController.php?action=create', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        full_name: name,
                        phone: phone,
                        email: email,
                        address: address,
                        membership_plan: plan
                    })
                });
                const data = await res.json();
                if (data.success) {
                    showToast('Member Registered Successfully!', 'success');
                    closeModals();
                    fetchMembers();
                } else {
                    showToast(data.error || 'Registration failed!', 'error');
                }
            } catch (e) {
                showToast('Registration failed!', 'error');
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-user-plus"></i> Create Account';
            }
        }

        // --- Add Fund Action ---
        async function processAddFund() {
            if (!selectedFundMember) return showToast('Please select a member.', 'error');
            const amount = parseFloat(document.getElementById('fundAmount').value);
            if (!amount || amount <= 0) return showToast('Enter a valid amount.', 'error');

            const btn = document.getElementById('btnSaveFund');
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> PROCESSING...';

            try {
                const res = await fetch('../../api/controllers/MemberController.php?action=addFund', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ memberId: selectedFundMember.id, amount: amount })
                });
                const data = await res.json();
                if (data.success) {
                    showToast(`৳${amount.toFixed(2)} added to ${selectedFundMember.full_name}'s account!`, 'success');
                    closeModals();

                    // Update current detail modal if open
                    if (currentDetailMember && parseInt(currentDetailMember.id) === parseInt(selectedFundMember.id)) {
                        currentDetailMember.acc_balance = (parseFloat(currentDetailMember.acc_balance) + amount).toFixed(2);
                        document.getElementById('detailHeaderBalance').innerText = '৳' + currentDetailMember.acc_balance;
                    }

                    fetchMembers();
                } else {
                    showToast(data.error || 'Fund update failed!', 'error');
                }
            } catch (e) {
                showToast('Fund update failed!', 'error');
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-money-bill-trend-up"></i> Add To Balance';
            }
        }

        // --- Filter / Search Logic ---
        function filterTable() {
            clearTimeout(window.searchTimer);
            window.searchTimer = setTimeout(async () => {
                const query = document.getElementById('memberFilter').value.trim();
                if (query.length === 0) {
                    isSearchActive = false;
                    fetchMembers();
                    return;
                }
                
                isSearchActive = true;
                document.getElementById('loadMoreContainer').style.display = 'none';
                
                try {
                    const res = await fetch(`../../api/controllers/MemberController.php?action=search&q=${encodeURIComponent(query)}`);
                    const data = await res.json();
                    if (data.success) {
                        allMembers = data.members;
                        renderMembers(data.members);
                    }
                } catch (e) {
                    console.error("Search error:", e);
                }
            }, 300);
        }

        // --- Stats Aggregator ---
        async function updateStats() {
            try {
                const response = await fetch('../../api/controllers/MemberController.php?action=stats');
                const data = await response.json();
                if (data.success) {
                    document.getElementById('statTotalMembers').innerText = data.stats.total;
                    document.getElementById('statActiveMembers').innerText = data.stats.active || data.stats.total;
                    document.getElementById('statTotalFunds').innerText = '৳' + data.stats.total_balance;

                    const activePlans = data.stats.plans.reduce((acc, p) => p.membership_plan !== 'None' ? acc + parseInt(p.count) : acc, 0);
                    document.getElementById('statActivePlans').innerText = activePlans;
                }
            } catch (e) {
                console.error("Stats error:", e);
            }
        }

        // --- Toast Notifications ---
        function showToast(msg, type = 'info') {
            const container = document.getElementById('toastContainer');
            if (!container) return;
            const toast = document.createElement('div');
            toast.className = `toast ${type}`;
            toast.innerHTML = `<i class="fa-solid fa-${type === 'success' ? 'check-circle' : 'circle-exclamation'}"></i> <span>${msg}</span>`;
            container.appendChild(toast);
            setTimeout(() => {
                toast.style.opacity = '0';
                setTimeout(() => toast.remove(), 400);
            }, 3200);
        }

        window.onload = fetchMembers;
    </script>
</body>

</html>