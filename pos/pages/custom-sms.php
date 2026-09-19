<?php 
require_once '../../api/shared/auth_check.php'; 
checkAuth(true); 
renderUserUI(true); 
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Send Custom SMS | Ontomeel POS</title>
    <link rel="stylesheet" href="../assets/pos-styles.css?v=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --primary-light: #eff6ff;
            --secondary: #64748b;
            --success: #10b981;
            --success-light: #ecfdf5;
            --warning: #f59e0b;
            --warning-light: #fffbeb;
            --danger: #ef4444;
            --danger-light: #fef2f2;
            --card-bg: #ffffff;
            --border: #e2e8f0;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --radius-lg: 20px;
            --radius-md: 12px;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.05);
            --shadow-md: 0 10px 25px -5px rgba(0,0,0,0.05), 0 8px 10px -6px rgba(0,0,0,0.05);
            --shadow-lg: 0 20px 30px -10px rgba(0,0,0,0.1);
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
            color: var(--text-main);
            margin: 0;
            padding: 0;
        }

        .main-content {
            margin-left: 260px;
            padding: 2.5rem;
            min-height: 100vh;
            box-sizing: border-box;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1.5rem;
        }

        .page-title h1 {
            font-size: 2.25rem;
            font-weight: 900;
            letter-spacing: -0.025em;
            margin: 0;
            color: #0f172a;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .page-title p {
            color: var(--text-muted);
            margin-top: 0.4rem;
            font-size: 1rem;
            font-weight: 500;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.25rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: var(--card-bg);
            padding: 1.25rem;
            border-radius: var(--radius-lg);
            border: 1px solid var(--border);
            box-shadow: var(--shadow-sm);
            display: flex;
            align-items: center;
            gap: 1.1rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-md);
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            flex-shrink: 0;
        }

        .icon-blue { background: #eff6ff; color: #2563eb; }
        .icon-green { background: #ecfdf5; color: #10b981; }
        .icon-purple { background: #faf5ff; color: #9333ea; }
        .icon-amber { background: #fffbeb; color: #d97706; }
        .icon-rose { background: #fff1f2; color: #e11d48; }

        .stat-info h3 {
            font-size: 0.75rem;
            font-weight: 700;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin: 0 0 0.25rem 0;
        }

        .stat-info .stat-value {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--text-main);
            line-height: 1.2;
            margin: 0;
        }

        .balance-pill {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 0.75rem;
            color: #10b981;
            font-weight: 600;
            margin-top: 3px;
        }

        /* Two-column Layout */
        .workspace-grid {
            display: grid;
            grid-template-columns: 1fr 490px;
            gap: 2rem;
            align-items: flex-start;
        }

        @media (max-width: 1200px) {
            .workspace-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Panel Card */
        .panel-card {
            background: var(--card-bg);
            border-radius: var(--radius-lg);
            border: 1px solid var(--border);
            box-shadow: var(--shadow-sm);
            padding: 1.75rem;
            position: relative;
        }

        .panel-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--border);
        }

        .panel-header h2 {
            font-size: 1.15rem;
            font-weight: 800;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
            color: #0f172a;
        }

        /* Filter Section */
        .filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
            background: #f8fafc;
            padding: 1.25rem;
            border-radius: var(--radius-md);
            border: 1px solid var(--border);
        }

        .filter-item {
            display: flex;
            flex-direction: column;
            gap: 0.4rem;
        }

        .filter-item label {
            font-size: 0.72rem;
            font-weight: 700;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .form-control {
            background: #ffffff;
            border: 1px solid var(--border);
            padding: 0.65rem 0.85rem;
            border-radius: 10px;
            font-size: 0.88rem;
            font-weight: 500;
            color: var(--text-main);
            outline: none;
            transition: all 0.2s;
            width: 100%;
            box-sizing: border-box;
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
        }

        /* Toolbar */
        .table-toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            flex-wrap: wrap;
            gap: 0.75rem;
        }

        .toolbar-left, .toolbar-right {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 0.65rem 1.1rem;
            border-radius: 10px;
            font-size: 0.85rem;
            font-weight: 700;
            cursor: pointer;
            border: none;
            transition: all 0.2s;
            text-decoration: none;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }
        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
        }

        .btn-outline {
            background: white;
            border: 1px solid var(--border);
            color: var(--text-main);
        }
        .btn-outline:hover {
            background: #f1f5f9;
            border-color: #cbd5e1;
        }

        .btn-sm {
            padding: 0.4rem 0.75rem;
            font-size: 0.78rem;
            border-radius: 8px;
        }

        /* Recipient Table */
        .table-container {
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            overflow: hidden;
            max-height: 480px;
            overflow-y: auto;
            position: relative;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.88rem;
            text-align: left;
        }

        th {
            background: #f8fafc;
            padding: 0.85rem 1rem;
            font-weight: 700;
            color: var(--text-muted);
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-bottom: 1px solid var(--border);
            position: sticky;
            top: 0;
            z-index: 10;
        }

        td {
            padding: 0.85rem 1rem;
            border-bottom: 1px solid var(--border);
            color: var(--text-main);
            vertical-align: middle;
        }

        tr:last-child td {
            border-bottom: none;
        }

        tr:hover td {
            background: #f8fafc;
        }

        tr.selected td {
            background: #eff6ff;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 8px;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
        }

        .badge-member { background: #eff6ff; color: #2563eb; }
        .badge-guest { background: #fdf4ff; color: #c026d3; }
        .badge-general { background: #ecfdf5; color: #059669; }
        .badge-booklover { background: #eff6ff; color: #2563eb; }
        .badge-collector { background: #fdf2f8; color: #db2777; }
        .badge-none { background: #f1f5f9; color: #64748b; }
        
        .valid-pill {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 0.72rem;
            font-weight: 600;
            color: #10b981;
        }
        .invalid-pill {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 0.72rem;
            font-weight: 600;
            color: #ef4444;
        }

        /* Composer Side */
        .composer-card {
            position: sticky;
            top: 2rem;
        }

        .template-selector {
            margin-bottom: 1.25rem;
        }

        .tag-pill {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 10px;
            background: #eff6ff;
            color: #2563eb;
            border: 1px dashed #bfdbfe;
            border-radius: 8px;
            font-size: 0.78rem;
            font-weight: 700;
            cursor: pointer;
            transition: 0.2s;
            margin-right: 6px;
            margin-bottom: 6px;
        }
        .tag-pill:hover {
            background: #dbeafe;
            transform: scale(1.03);
        }

        .textarea-wrapper {
            position: relative;
            margin-bottom: 0.75rem;
        }

        .sms-textarea {
            width: 100%;
            min-height: 140px;
            padding: 1rem;
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            font-family: 'Inter', sans-serif;
            font-size: 0.92rem;
            line-height: 1.5;
            color: var(--text-main);
            outline: none;
            resize: vertical;
            box-sizing: border-box;
            transition: all 0.2s;
        }

        .sms-textarea:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.15);
        }

        .sms-counter-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #f8fafc;
            padding: 0.65rem 1rem;
            border-radius: 8px;
            border: 1px solid var(--border);
            font-size: 0.78rem;
            font-weight: 600;
            color: var(--text-muted);
            margin-bottom: 1rem;
        }

        .sms-counter-highlight {
            color: var(--primary);
            font-weight: 800;
        }

        /* Cost Calculation Box */
        .cost-calculator-box {
            background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%);
            border: 1px solid #bbf7d0;
            border-radius: 14px;
            padding: 1.25rem;
            margin-bottom: 1.25rem;
        }

        .cost-calculator-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.75rem;
        }

        .cost-calculator-header h4 {
            margin: 0;
            font-size: 0.85rem;
            font-weight: 800;
            color: #166534;
            display: flex;
            align-items: center;
            gap: 6px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .cost-rate-row {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 0.85rem;
            flex-wrap: wrap;
        }

        .cost-rate-selector {
            background: white;
            border: 1px solid #86efac;
            padding: 0.4rem 0.65rem;
            border-radius: 8px;
            font-size: 0.8rem;
            font-weight: 700;
            color: #15803d;
            outline: none;
            cursor: pointer;
        }

        .cost-breakdown-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.6rem;
            font-size: 0.82rem;
            color: #14532d;
            background: rgba(255, 255, 255, 0.7);
            padding: 0.85rem;
            border-radius: 10px;
            border: 1px solid #dcfce7;
        }

        .cost-breakdown-item {
            display: flex;
            justify-content: space-between;
            font-weight: 600;
        }

        .cost-breakdown-total {
            grid-column: span 2;
            border-top: 1px dashed #86efac;
            padding-top: 0.5rem;
            margin-top: 0.25rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-weight: 800;
            font-size: 1rem;
            color: #166534;
        }

        /* Phone Preview */
        .preview-box {
            background: #f1f5f9;
            border-radius: 16px;
            padding: 1.1rem;
            border: 1px solid #cbd5e1;
            margin-bottom: 1.25rem;
            position: relative;
        }

        .preview-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 0.72rem;
            font-weight: 700;
            color: var(--text-muted);
            text-transform: uppercase;
            margin-bottom: 0.6rem;
        }

        .preview-bubble {
            background: white;
            border-radius: 14px 14px 14px 4px;
            padding: 0.85rem;
            font-size: 0.86rem;
            line-height: 1.5;
            color: #1e293b;
            box-shadow: 0 2px 4px rgba(0,0,0,0.04);
            white-space: pre-wrap;
            word-break: break-word;
            border: 1px solid #e2e8f0;
        }

        /* Modal Styles */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(4px);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            padding: 1.5rem;
        }

        .modal-overlay.active {
            display: flex;
            animation: fadeIn 0.2s ease-out;
        }

        .modal-card {
            background: white;
            border-radius: var(--radius-lg);
            width: 100%;
            max-width: 540px;
            padding: 2rem;
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--border);
            position: relative;
            animation: scaleIn 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        @keyframes scaleIn { from { transform: scale(0.94); opacity: 0; } to { transform: scale(1); opacity: 1; } }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .modal-header h3 {
            font-size: 1.3rem;
            font-weight: 800;
            margin: 0;
            color: #0f172a;
        }

        .btn-close {
            background: none;
            border: none;
            font-size: 1.2rem;
            color: var(--text-muted);
            cursor: pointer;
            padding: 4px;
        }

        /* Progress Bar */
        .progress-container {
            width: 100%;
            background: #e2e8f0;
            border-radius: 12px;
            height: 12px;
            overflow: hidden;
            margin: 1.5rem 0 1rem 0;
        }

        .progress-bar-fill {
            height: 100%;
            background: linear-gradient(90deg, #2563eb, #3b82f6);
            width: 0%;
            transition: width 0.3s ease;
        }

        .log-box {
            background: #0f172a;
            color: #94a3b8;
            font-family: monospace;
            font-size: 0.78rem;
            border-radius: 10px;
            padding: 1rem;
            max-height: 180px;
            overflow-y: auto;
            margin-top: 1rem;
            line-height: 1.6;
        }

        /* Toast */
        .toast {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            background: #0f172a;
            color: white;
            padding: 1rem 1.5rem;
            border-radius: 12px;
            font-size: 0.9rem;
            font-weight: 600;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            z-index: 2000;
            transform: translateY(100px);
            opacity: 0;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .toast.show {
            transform: translateY(0);
            opacity: 1;
        }

        .toast-success { border-left: 4px solid #10b981; }
        .toast-error { border-left: 4px solid #ef4444; }
    </style>
</head>

<body>
    <!-- Sidebar rendered automatically by sidebar.js -->
    <script src="../assets/sidebar.js"></script>

    <div class="main-content">
        <!-- Page Header -->
        <div class="page-header">
            <div class="page-title">
                <h1><i class="fa-solid fa-comment-sms" style="color: #2563eb;"></i> Send Custom SMS</h1>
                <p>Filter, personalize, and broadcast custom SMS campaigns with live cost estimation & balance tracker.</p>
            </div>
            <div style="display: flex; gap: 0.75rem;">
                <button class="btn btn-outline" onclick="loadRecipients()">
                    <i class="fa-solid fa-rotate-right"></i> Refresh List
                </button>
                <button class="btn btn-outline" onclick="openTestModal()">
                    <i class="fa-solid fa-vial"></i> Send Test SMS
                </button>
            </div>
        </div>

        <!-- Stats Grid (5 Cards) -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon icon-blue">
                    <i class="fa-solid fa-users"></i>
                </div>
                <div class="stat-info">
                    <h3>Filtered Contacts</h3>
                    <p class="stat-value" id="statTotalContacts">0</p>
                    <span style="font-size: 0.75rem; color: #64748b;" id="statValidContacts">0 valid numbers</span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon icon-purple">
                    <i class="fa-solid fa-user-check"></i>
                </div>
                <div class="stat-info">
                    <h3>Selected to Send</h3>
                    <p class="stat-value" id="statSelectedContacts">0</p>
                    <span style="font-size: 0.75rem; color: #2563eb; font-weight: 700;" id="statSelectedPercent">0% of total</span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon icon-amber">
                    <i class="fa-solid fa-calculator"></i>
                </div>
                <div class="stat-info">
                    <h3>SMS Parts</h3>
                    <p class="stat-value" id="statEstimatedParts">1</p>
                    <span style="font-size: 0.75rem; color: #64748b;" id="statTotalDispatches">0 total SMS units</span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon icon-rose">
                    <i class="fa-solid fa-receipt"></i>
                </div>
                <div class="stat-info">
                    <h3>Estimated Cost</h3>
                    <p class="stat-value" id="statEstimatedCost" style="color: #e11d48;">৳0.00</p>
                    <span style="font-size: 0.75rem; color: #64748b;" id="statRateFormula">@ ৳0.35 / SMS</span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon icon-green">
                    <i class="fa-solid fa-wallet"></i>
                </div>
                <div class="stat-info">
                    <h3>SMS Balance</h3>
                    <p class="stat-value" id="statSMSBalance">--</p>
                    <div class="balance-pill" id="balanceStatus">
                        <i class="fa-solid fa-circle-check"></i> BulkSMS BD Connected
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Workspace -->
        <div class="workspace-grid">
            <!-- Left: Audience Filtering & Selection Table -->
            <div class="panel-card">
                <div class="panel-header">
                    <h2><i class="fa-solid fa-filter" style="color: #2563eb;"></i> 1. Filter Audience & Select Recipients</h2>
                    <span id="listBadge" class="badge badge-member">All Contacts</span>
                </div>

                <!-- Filters -->
                <div class="filter-grid">
                    <div class="filter-item">
                        <label>Audience Group</label>
                        <select id="filterAudience" class="form-control" onchange="onAudienceChange()">
                            <option value="all">All Contacts (Members + Buyers)</option>
                            <option value="members">Members & Readers Only</option>
                            <option value="guests">Guest Buyers Only</option>
                        </select>
                    </div>

                    <div class="filter-item" id="filterPlanWrapper">
                        <label>Membership Plan</label>
                        <select id="filterPlan" class="form-control" onchange="loadRecipients()">
                            <option value="all">All Plans</option>
                            <option value="General">General</option>
                            <option value="BookLover">BookLover</option>
                            <option value="Collector">Collector</option>
                            <option value="None">None (Standard)</option>
                        </select>
                    </div>

                    <div class="filter-item" id="filterMemberStatusWrapper">
                        <label>Member Status</label>
                        <select id="filterMemberStatus" class="form-control" onchange="loadRecipients()">
                            <option value="all">All Statuses</option>
                            <option value="1">Active Only</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>

                    <div class="filter-item" id="filterDateFromWrapper" style="display: none;">
                        <label>Order Date From</label>
                        <input type="date" id="filterDateFrom" class="form-control" onchange="loadRecipients()">
                    </div>

                    <div class="filter-item" id="filterDateToWrapper" style="display: none;">
                        <label>Order Date To</label>
                        <input type="date" id="filterDateTo" class="form-control" onchange="loadRecipients()">
                    </div>
                </div>

                <!-- Table Toolbar -->
                <div class="table-toolbar">
                    <div class="toolbar-left">
                        <button class="btn btn-outline btn-sm" onclick="selectAll(true)">
                            <i class="fa-solid fa-check-double"></i> Select All
                        </button>
                        <button class="btn btn-outline btn-sm" onclick="selectAll(false)">
                            <i class="fa-solid fa-xmark"></i> Deselect All
                        </button>
                        <button class="btn btn-outline btn-sm" onclick="selectOnlyValid()">
                            <i class="fa-solid fa-filter-circle-dollar"></i> Select Valid Only
                        </button>
                    </div>

                    <div class="toolbar-right">
                        <input type="text" id="tableSearch" class="form-control" style="width: 170px; padding: 0.4rem 0.75rem; font-size: 0.8rem;" placeholder="Search name/phone..." onkeyup="filterLocalTable()">
                        <button class="btn btn-outline btn-sm" onclick="copyNumbersToClipboard()" title="Copy comma separated numbers">
                            <i class="fa-solid fa-copy"></i> Copy
                        </button>
                        <button class="btn btn-outline btn-sm" onclick="exportToCSV()" title="Download CSV">
                            <i class="fa-solid fa-file-csv"></i> CSV
                        </button>
                    </div>
                </div>

                <!-- Recipient Table -->
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th style="width: 40px; text-align: center;">
                                    <input type="checkbox" id="masterCheckbox" onchange="toggleMasterCheckbox(this.checked)" checked>
                                </th>
                                <th>Name</th>
                                <th>Phone</th>
                                <th>Category / Plan</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="recipientsTableBody">
                            <tr>
                                <td colspan="5" style="text-align: center; padding: 3rem; color: #64748b;">
                                    <i class="fa-solid fa-spinner fa-spin fa-2x" style="color: #2563eb;"></i>
                                    <p style="margin-top: 0.75rem; font-weight: 600;">Loading recipients...</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Right: SMS Composer, Cost Breakdown & Broadcasting -->
            <div class="panel-card composer-card">
                <div class="panel-header">
                    <h2><i class="fa-solid fa-pen-nib" style="color: #2563eb;"></i> 2. Compose & Costing</h2>
                </div>

                <!-- Template Selector -->
                <div class="template-selector">
                    <label style="font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin-bottom: 0.4rem; display: block;">
                        Message Template Preset
                    </label>
                    <select id="templatePicker" class="form-control" onchange="applyTemplate(this.value)">
                        <option value="">-- Choose a Quick Preset --</option>
                        <option value="new_arrivals">📚 New Book Arrivals Announcement</option>
                        <option value="special_discount">🎉 Special Discount & Offers</option>
                        <option value="borrow_reminder">⏳ Library Book Return Reminder</option>
                        <option value="membership_renewal">💳 Membership Renewal Notice</option>
                        <option value="thank_you">🙏 Thank You For Shopping</option>
                    </select>
                </div>

                <!-- Personalization Tags -->
                <div style="margin-bottom: 0.6rem;">
                    <span style="font-size: 0.72rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; display: block; margin-bottom: 0.35rem;">
                        Insert Dynamic Tags:
                    </span>
                    <span class="tag-pill" onclick="insertTag('{name}')" title="Insert customer/member name">
                        <i class="fa-solid fa-user-tag"></i> {name}
                    </span>
                </div>

                <!-- SMS Textarea -->
                <div class="textarea-wrapper">
                    <textarea id="smsMessage" class="sms-textarea" placeholder="Type your custom SMS message here... Use {name} for personalized greetings." oninput="updateSMSCounters()"></textarea>
                </div>

                <!-- SMS Counter Bar -->
                <div class="sms-counter-bar">
                    <div>
                        Chars: <span class="sms-counter-highlight" id="charCount">0</span> | 
                        Parts: <span class="sms-counter-highlight" id="partCount">1</span> SMS
                    </div>
                    <div id="encodingBadge" style="font-size: 0.72rem; font-weight: 700; color: #10b981;">
                        GSM 7-bit (English)
                    </div>
                </div>

                <!-- Cost Calculation Breakdown Box -->
                <div class="cost-calculator-box">
                    <div class="cost-calculator-header">
                        <h4><i class="fa-solid fa-calculator"></i> BulkSMS BD Cost Estimation</h4>
                        <div id="balanceSufficiencyBadge" style="font-size: 0.72rem; font-weight: 800; padding: 2px 8px; border-radius: 12px; background: #dcfce7; color: #15803d;">
                            Ready
                        </div>
                    </div>

                    <div class="cost-rate-row">
                        <label style="font-size: 0.75rem; font-weight: 700; color: #166534; text-transform: uppercase;">
                            Gateway Rate:
                        </label>
                        <select id="smsRateType" class="cost-rate-selector" onchange="onRateTypeChange()">
                            <option value="0.35">Non-Masking (~৳0.35 / SMS)</option>
                            <option value="0.50">Masking (~৳0.50 / SMS)</option>
                            <option value="custom">Custom Rate...</option>
                        </select>
                        <input type="number" step="0.01" id="customSmsRate" class="form-control" style="width: 80px; padding: 0.3rem 0.5rem; font-size: 0.8rem; display: none;" value="0.35" oninput="updateSMSCounters()">
                    </div>

                    <div class="cost-breakdown-grid">
                        <div class="cost-breakdown-item">
                            <span>Selected Receivers:</span>
                            <strong id="calcReceivers">0</strong>
                        </div>
                        <div class="cost-breakdown-item">
                            <span>SMS Parts:</span>
                            <strong id="calcParts">1</strong>
                        </div>
                        <div class="cost-breakdown-item">
                            <span>Total SMS Units:</span>
                            <strong id="calcTotalUnits">0</strong>
                        </div>
                        <div class="cost-breakdown-item">
                            <span>Unit Rate:</span>
                            <strong id="calcUnitRate">৳0.35</strong>
                        </div>
                        <div class="cost-breakdown-total">
                            <span>Estimated Total Cost:</span>
                            <span id="calcTotalCost">৳0.00 BDT</span>
                        </div>
                    </div>
                </div>

                <!-- Live Preview on Phone -->
                <div class="preview-box">
                    <div class="preview-header">
                        <span><i class="fa-solid fa-mobile-screen"></i> SMS Live Preview</span>
                        <span id="previewRecipientName" style="color: #2563eb; font-weight: 700;">Recipient: John Doe</span>
                    </div>
                    <div class="preview-bubble" id="smsPreviewContent">
                        Type a message above to see how it will appear on customer's phone...
                    </div>
                </div>

                <!-- Broadcast CTA -->
                <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                    <button class="btn btn-primary" style="padding: 0.95rem; font-size: 0.95rem; width: 100%;" onclick="confirmBroadcast()">
                        <i class="fa-solid fa-paper-plane"></i> Broadcast to <span id="btnSelectedCount">0</span> Contacts (<span id="btnEstimatedCost">৳0.00</span>)
                    </button>
                    <button class="btn btn-outline" style="width: 100%;" onclick="openTestModal()">
                        <i class="fa-solid fa-vial"></i> Send Single Test SMS
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Test SMS Modal -->
    <div class="modal-overlay" id="testModal">
        <div class="modal-card">
            <div class="modal-header">
                <h3><i class="fa-solid fa-vial" style="color: #2563eb;"></i> Send Single Test SMS</h3>
                <button class="btn-close" onclick="closeTestModal()">&times;</button>
            </div>
            <div style="display: flex; flex-direction: column; gap: 1.25rem;">
                <div>
                    <label style="font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">
                        Recipient Mobile Number
                    </label>
                    <input type="text" id="testPhoneNumber" class="form-control" placeholder="017XXXXXXXX" style="margin-top: 0.35rem;" value="01896036396">
                </div>
                <div>
                    <label style="font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">
                        Test Recipient Name (For {name} tag)
                    </label>
                    <input type="text" id="testRecipientName" class="form-control" placeholder="Test User" value="Admin Test" style="margin-top: 0.35rem;">
                </div>
                <div>
                    <label style="font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">
                        Message to Send
                    </label>
                    <div id="testMessagePreview" style="background: #f8fafc; padding: 0.85rem; border-radius: 8px; border: 1px solid var(--border); font-size: 0.85rem; margin-top: 0.35rem; color: #334155; white-space: pre-wrap;">
                    </div>
                </div>
                <button class="btn btn-primary" id="btnSendTest" onclick="sendTestSMS()" style="padding: 0.85rem;">
                    <i class="fa-solid fa-paper-plane"></i> Send Test Now
                </button>
            </div>
        </div>
    </div>

    <!-- Broadcasting Execution Progress Modal -->
    <div class="modal-overlay" id="broadcastModal">
        <div class="modal-card" style="max-width: 580px;">
            <div class="modal-header">
                <h3 id="broadcastTitle"><i class="fa-solid fa-paper-plane" style="color: #2563eb;"></i> Broadcasting Custom SMS</h3>
            </div>
            <div>
                <p id="broadcastSubtitle" style="color: var(--text-muted); font-size: 0.9rem; margin: 0;">
                    Sending messages to selected contacts via BulkSMS BD...
                </p>

                <!-- Cost & Details Badge -->
                <div style="background: #f8fafc; border: 1px solid var(--border); border-radius: 10px; padding: 0.75rem 1rem; margin-top: 0.85rem; font-size: 0.82rem; display: flex; justify-content: space-between;">
                    <span>Campaign Cost: <strong id="modalCostBadge" style="color: #e11d48;">৳0.00</strong></span>
                    <span>SMS Units: <strong id="modalUnitsBadge">0</strong></span>
                    <span>Rate: <strong id="modalRateBadge">৳0.35/SMS</strong></span>
                </div>

                <div class="progress-container">
                    <div class="progress-bar-fill" id="broadcastProgressBar"></div>
                </div>

                <div style="display: flex; justify-content: space-between; font-size: 0.85rem; font-weight: 700; margin-bottom: 1rem;">
                    <span style="color: #10b981;"><i class="fa-solid fa-circle-check"></i> Sent: <span id="broadcastSuccessCount">0</span></span>
                    <span style="color: #ef4444;"><i class="fa-solid fa-circle-xmark"></i> Failed: <span id="broadcastFailedCount">0</span></span>
                    <span style="color: #64748b;"><i class="fa-solid fa-list-check"></i> Total: <span id="broadcastTotalCount">0</span></span>
                </div>

                <div class="log-box" id="broadcastLog">
                    [System] Initializing broadcast batch dispatch...
                </div>

                <div style="margin-top: 1.5rem; text-align: right;">
                    <button class="btn btn-outline" id="btnCloseBroadcast" onclick="closeBroadcastModal()" style="display: none;">
                        Done & Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast -->
    <div class="toast" id="toast">
        <span id="toastMessage">Notification</span>
    </div>

    <script>
        // State
        let allRecipients = [];
        let selectedRecipientsMap = new Set();
        let currentSmsBalance = null;

        const templates = {
            new_arrivals: "Hello {name}, exciting news! Fresh new book titles have just arrived at Ontomeel Library & Bookshop. Visit us today or browse our latest catalog!",
            special_discount: "Dear {name}, enjoy an exclusive 15% discount on all books and stationery this week at Ontomeel! Use code SPECIAL15 at checkout.",
            borrow_reminder: "Hi {name}, this is a gentle reminder regarding your borrowed library book from Ontomeel. Please ensure return or renewal on time to avoid late fines. Thank you!",
            membership_renewal: "Hello {name}, your Ontomeel Library membership is up for renewal. Renew now to enjoy uninterrupted book borrowing & exclusive perks!",
            thank_you: "Thank you {name} for your recent purchase at Ontomeel! We hope you enjoy reading your books. Have a wonderful day!"
        };

        document.addEventListener('DOMContentLoaded', () => {
            fetchSMSBalance();
            loadRecipients();
            // Default message
            document.getElementById('smsMessage').value = templates.new_arrivals;
            updateSMSCounters();
        });

        // Toast Helper
        function showToast(msg, type = 'success') {
            const toast = document.getElementById('toast');
            const toastMsg = document.getElementById('toastMessage');
            toastMsg.innerText = msg;
            toast.className = `toast show toast-${type}`;
            setTimeout(() => {
                toast.classList.remove('show');
            }, 3500);
        }

        // Fetch Balance
        async function fetchSMSBalance() {
            try {
                const res = await fetch('../../api/controllers/CustomSMSController.php?action=getBalance');
                const data = await res.json();
                if (data.success && data.balance !== undefined) {
                    currentSmsBalance = parseFloat(data.balance);
                    document.getElementById('statSMSBalance').innerText = '৳' + currentSmsBalance.toFixed(2);
                } else {
                    document.getElementById('statSMSBalance').innerText = 'Active';
                }
            } catch (e) {
                document.getElementById('statSMSBalance').innerText = 'Active';
            }
            updateSMSCounters();
        }

        function onAudienceChange() {
            const aud = document.getElementById('filterAudience').value;
            const planWrapper = document.getElementById('filterPlanWrapper');
            const memberStatusWrapper = document.getElementById('filterMemberStatusWrapper');
            const dateFromWrapper = document.getElementById('filterDateFromWrapper');
            const dateToWrapper = document.getElementById('filterDateToWrapper');
            const badge = document.getElementById('listBadge');

            if (aud === 'members') {
                planWrapper.style.display = 'flex';
                memberStatusWrapper.style.display = 'flex';
                dateFromWrapper.style.display = 'none';
                dateToWrapper.style.display = 'none';
                badge.innerText = 'Members & Readers';
                badge.className = 'badge badge-member';
            } else if (aud === 'guests') {
                planWrapper.style.display = 'none';
                memberStatusWrapper.style.display = 'none';
                dateFromWrapper.style.display = 'flex';
                dateToWrapper.style.display = 'flex';
                badge.innerText = 'Guest Buyers';
                badge.className = 'badge badge-guest';
            } else {
                planWrapper.style.display = 'flex';
                memberStatusWrapper.style.display = 'flex';
                dateFromWrapper.style.display = 'none';
                dateToWrapper.style.display = 'none';
                badge.innerText = 'All Contacts';
                badge.className = 'badge badge-member';
            }

            loadRecipients();
        }

        // Load Recipients from API
        async function loadRecipients() {
            const audience = document.getElementById('filterAudience').value;
            const plan = document.getElementById('filterPlan').value;
            const memberStatus = document.getElementById('filterMemberStatus').value;
            const dateFrom = document.getElementById('filterDateFrom').value;
            const dateTo = document.getElementById('filterDateTo').value;

            const tbody = document.getElementById('recipientsTableBody');
            tbody.innerHTML = `
                <tr>
                    <td colspan="5" style="text-align: center; padding: 3rem; color: #64748b;">
                        <i class="fa-solid fa-spinner fa-spin fa-2x" style="color: #2563eb;"></i>
                        <p style="margin-top: 0.75rem; font-weight: 600;">Loading filtered contacts...</p>
                    </td>
                </tr>
            `;

            try {
                const params = new URLSearchParams({
                    action: 'getRecipients',
                    audience,
                    plan,
                    member_status: memberStatus,
                    date_from: dateFrom,
                    date_to: dateTo
                });

                const res = await fetch(`../../api/controllers/CustomSMSController.php?${params.toString()}`);
                const data = await res.json();

                if (data.success) {
                    allRecipients = data.recipients || [];
                    selectedRecipientsMap = new Set();
                    
                    // Select all valid by default
                    allRecipients.forEach(r => {
                        if (r.is_valid) {
                            selectedRecipientsMap.add(r.id);
                        }
                    });

                    renderTable();
                    updateStats();
                } else {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 2rem; color: #ef4444;">
                                <i class="fa-solid fa-triangle-exclamation"></i> ${data.error || 'Failed to fetch contacts'}
                            </td>
                        </tr>
                    `;
                }
            } catch (err) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 2rem; color: #ef4444;">
                            <i class="fa-solid fa-triangle-exclamation"></i> Network error loading contacts.
                        </td>
                    </tr>
                `;
            }
        }

        function renderTable() {
            const tbody = document.getElementById('recipientsTableBody');
            const search = document.getElementById('tableSearch').value.toLowerCase().trim();

            const filtered = allRecipients.filter(r => {
                if (!search) return true;
                return (r.name && r.name.toLowerCase().includes(search)) ||
                       (r.phone && r.phone.includes(search)) ||
                       (r.sub_category && r.sub_category.toLowerCase().includes(search));
            });

            if (filtered.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 2.5rem; color: #64748b;">
                            <i class="fa-regular fa-folder-open fa-2x" style="margin-bottom: 0.5rem; color: #cbd5e1;"></i>
                            <p style="margin: 0; font-weight: 600;">No matching contacts found.</p>
                        </td>
                    </tr>
                `;
                return;
            }

            let html = '';
            filtered.forEach(r => {
                const isChecked = selectedRecipientsMap.has(r.id);
                let badgeClass = 'badge-none';
                if (r.category === 'Member') {
                    const planLower = (r.plan || '').toLowerCase();
                    badgeClass = planLower === 'general' ? 'badge-general' : 
                                 planLower === 'booklover' ? 'badge-booklover' : 
                                 planLower === 'collector' ? 'badge-collector' : 'badge-none';
                } else {
                    badgeClass = 'badge-guest';
                }

                html += `
                    <tr class="${isChecked ? 'selected' : ''}" onclick="toggleRowCheckbox('${r.id}', event)">
                        <td style="text-align: center;" onclick="event.stopPropagation()">
                            <input type="checkbox" class="row-checkbox" value="${r.id}" ${isChecked ? 'checked' : ''} onchange="toggleRecipient('${r.id}', this.checked)">
                        </td>
                        <td>
                            <div style="font-weight: 700; color: #0f172a;">${escapeHtml(r.name)}</div>
                        </td>
                        <td>
                            <div style="font-weight: 600; font-family: monospace;">${escapeHtml(r.phone)}</div>
                            ${r.is_valid ? '<span class="valid-pill"><i class="fa-solid fa-circle-check"></i> Valid BD Phone</span>' : '<span class="invalid-pill"><i class="fa-solid fa-circle-xmark"></i> Non-standard</span>'}
                        </td>
                        <td>
                            <span class="badge ${badgeClass}">${escapeHtml(r.sub_category || r.category)}</span>
                        </td>
                        <td>
                            <span style="font-size: 0.8rem; font-weight: 600; color: #64748b;">${escapeHtml(r.status)}</span>
                        </td>
                    </tr>
                `;
            });

            tbody.innerHTML = html;
            updateMasterCheckboxState();
        }

        function filterLocalTable() {
            renderTable();
        }

        function toggleRowCheckbox(id, event) {
            if (event.target.tagName === 'INPUT') return;
            const willCheck = !selectedRecipientsMap.has(id);
            toggleRecipient(id, willCheck);
            renderTable();
        }

        function toggleRecipient(id, isChecked) {
            if (isChecked) {
                selectedRecipientsMap.add(id);
            } else {
                selectedRecipientsMap.delete(id);
            }
            updateStats();
            updateMasterCheckboxState();
        }

        function toggleMasterCheckbox(isChecked) {
            allRecipients.forEach(r => {
                if (isChecked) {
                    selectedRecipientsMap.add(r.id);
                } else {
                    selectedRecipientsMap.delete(r.id);
                }
            });
            renderTable();
            updateStats();
        }

        function selectAll(check) {
            toggleMasterCheckbox(check);
            document.getElementById('masterCheckbox').checked = check;
        }

        function selectOnlyValid() {
            selectedRecipientsMap.clear();
            allRecipients.forEach(r => {
                if (r.is_valid) {
                    selectedRecipientsMap.add(r.id);
                }
            });
            renderTable();
            updateStats();
            showToast("Selected all valid 11-digit mobile numbers.");
        }

        function updateMasterCheckboxState() {
            const master = document.getElementById('masterCheckbox');
            if (allRecipients.length === 0) {
                master.checked = false;
                master.indeterminate = false;
            } else if (selectedRecipientsMap.size === allRecipients.length) {
                master.checked = true;
                master.indeterminate = false;
            } else if (selectedRecipientsMap.size === 0) {
                master.checked = false;
                master.indeterminate = false;
            } else {
                master.checked = false;
                master.indeterminate = true;
            }
        }

        function updateStats() {
            const total = allRecipients.length;
            const valid = allRecipients.filter(r => r.is_valid).length;
            const selected = selectedRecipientsMap.size;

            document.getElementById('statTotalContacts').innerText = total;
            document.getElementById('statValidContacts').innerText = `${valid} valid numbers`;
            document.getElementById('statSelectedContacts').innerText = selected;
            
            const percent = total > 0 ? Math.round((selected / total) * 100) : 0;
            document.getElementById('statSelectedPercent').innerText = `${percent}% of total`;
            document.getElementById('btnSelectedCount').innerText = selected;

            updateSMSCounters();
        }

        // Rate Type Change
        function onRateTypeChange() {
            const type = document.getElementById('smsRateType').value;
            const customInput = document.getElementById('customSmsRate');
            if (type === 'custom') {
                customInput.style.display = 'inline-block';
            } else {
                customInput.style.display = 'none';
            }
            updateSMSCounters();
        }

        function getUnitRate() {
            const type = document.getElementById('smsRateType').value;
            if (type === 'custom') {
                return parseFloat(document.getElementById('customSmsRate').value) || 0.35;
            }
            return parseFloat(type) || 0.35;
        }

        // Composer, Part Calculation & Costing
        function applyTemplate(key) {
            if (templates[key]) {
                document.getElementById('smsMessage').value = templates[key];
                updateSMSCounters();
            }
        }

        function insertTag(tag) {
            const textarea = document.getElementById('smsMessage');
            const start = textarea.selectionStart;
            const end = textarea.selectionEnd;
            const text = textarea.value;
            textarea.value = text.substring(0, start) + tag + text.substring(end);
            textarea.focus();
            textarea.selectionStart = textarea.selectionEnd = start + tag.length;
            updateSMSCounters();
        }

        function isUnicode(str) {
            for (let i = 0; i < str.length; i++) {
                if (str.charCodeAt(i) > 127) return true;
            }
            return false;
        }

        function updateSMSCounters() {
            const msg = document.getElementById('smsMessage').value;
            const charCount = msg.length;
            const unicode = isUnicode(msg);

            let parts = 1;
            if (unicode) {
                // Unicode (e.g. Bengali): 70 chars per 1 part, 67 chars per part for multipart
                if (charCount > 70) {
                    parts = Math.ceil(charCount / 67);
                }
                document.getElementById('encodingBadge').innerText = "Unicode (70 chars/part)";
                document.getElementById('encodingBadge').style.color = "#d97706";
            } else {
                // GSM 7-bit (English): 160 chars per 1 part, 153 chars per part for multipart
                if (charCount > 160) {
                    parts = Math.ceil(charCount / 153);
                }
                document.getElementById('encodingBadge').innerText = "GSM 7-bit (160 chars/part)";
                document.getElementById('encodingBadge').style.color = "#10b981";
            }

            document.getElementById('charCount').innerText = charCount;
            document.getElementById('partCount').innerText = parts;
            document.getElementById('statEstimatedParts').innerText = parts;

            // Cost calculations
            const selectedCount = selectedRecipientsMap.size;
            const totalDispatches = selectedCount * parts;
            const rate = getUnitRate();
            const totalCost = totalDispatches * rate;

            document.getElementById('statTotalDispatches').innerText = `${totalDispatches} total SMS units`;
            document.getElementById('statEstimatedCost').innerText = '৳' + totalCost.toFixed(2);
            document.getElementById('statRateFormula').innerText = `@ ৳${rate.toFixed(2)} / SMS`;

            // Breakdown box
            document.getElementById('calcReceivers').innerText = selectedCount;
            document.getElementById('calcParts').innerText = `${parts} (${unicode ? 'Unicode' : 'GSM'})`;
            document.getElementById('calcTotalUnits').innerText = totalDispatches;
            document.getElementById('calcUnitRate').innerText = '৳' + rate.toFixed(2);
            document.getElementById('calcTotalCost').innerText = '৳' + totalCost.toFixed(2) + ' BDT';

            document.getElementById('btnEstimatedCost').innerText = '৳' + totalCost.toFixed(2);

            // Check Balance Sufficiency
            const badge = document.getElementById('balanceSufficiencyBadge');
            if (currentSmsBalance !== null) {
                if (currentSmsBalance >= totalCost) {
                    const remaining = (currentSmsBalance - totalCost).toFixed(2);
                    badge.innerText = `Sufficient (৳${remaining} left)`;
                    badge.style.background = '#dcfce7';
                    badge.style.color = '#15803d';
                } else {
                    const deficit = (totalCost - currentSmsBalance).toFixed(2);
                    badge.innerText = `Low Balance (Short ৳${deficit})`;
                    badge.style.background = '#fee2e2';
                    badge.style.color = '#dc2626';
                }
            } else {
                badge.innerText = 'Ready';
                badge.style.background = '#dcfce7';
                badge.style.color = '#15803d';
            }

            // Update live preview
            const sampleName = allRecipients.length > 0 ? (allRecipients[0].name || 'John Doe') : 'John Doe';
            document.getElementById('previewRecipientName').innerText = `Recipient: ${sampleName}`;
            
            const previewText = msg.replace(/{name}/gi, sampleName);
            document.getElementById('smsPreviewContent').innerText = previewText || "Type a message above to see how it will appear on customer's phone...";
        }

        // Export / Copy
        function copyNumbersToClipboard() {
            const selectedList = allRecipients.filter(r => selectedRecipientsMap.has(r.id));
            if (selectedList.length === 0) {
                showToast("No recipients selected to copy.", "error");
                return;
            }
            const numbers = selectedList.map(r => r.phone).join(', ');
            navigator.clipboard.writeText(numbers).then(() => {
                showToast(`Copied ${selectedList.length} phone numbers to clipboard!`);
            });
        }

        function exportToCSV() {
            const selectedList = allRecipients.filter(r => selectedRecipientsMap.has(r.id));
            if (selectedList.length === 0) {
                showToast("No recipients selected for export.", "error");
                return;
            }

            let csvContent = "data:text/csv;charset=utf-8,Name,Phone,Category,Status\n";
            selectedList.forEach(r => {
                const name = `"${(r.name || '').replace(/"/g, '""')}"`;
                const phone = `"${r.phone}"`;
                const cat = `"${r.sub_category || r.category}"`;
                const status = `"${r.status}"`;
                csvContent += `${name},${phone},${cat},${status}\n`;
            });

            const encodedUri = encodeURI(csvContent);
            const link = document.createElement("a");
            link.setAttribute("href", encodedUri);
            link.setAttribute("download", `ontomeel_sms_contacts_${new Date().toISOString().slice(0,10)}.csv`);
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            showToast("CSV file exported successfully!");
        }

        // Test Modal
        function openTestModal() {
            const msg = document.getElementById('smsMessage').value;
            const testName = document.getElementById('testRecipientName').value || 'Test User';
            document.getElementById('testMessagePreview').innerText = msg.replace(/{name}/gi, testName);
            document.getElementById('testModal').classList.add('active');
        }

        function closeTestModal() {
            document.getElementById('testModal').classList.remove('active');
        }

        async function sendTestSMS() {
            const phone = document.getElementById('testPhoneNumber').value.trim();
            const name = document.getElementById('testRecipientName').value.trim();
            const msg = document.getElementById('smsMessage').value.trim();
            const btn = document.getElementById('btnSendTest');

            if (!phone) {
                showToast("Please enter a test phone number.", "error");
                return;
            }
            if (!msg) {
                showToast("SMS message is empty.", "error");
                return;
            }

            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Sending Test...';

            try {
                const res = await fetch('../../api/controllers/CustomSMSController.php?action=sendTest', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ phone, name, message: msg })
                });
                const data = await res.json();

                if (data.success) {
                    showToast("Test SMS sent successfully!");
                    closeTestModal();
                    fetchSMSBalance();
                } else {
                    showToast("Failed to send test: " + (data.message || data.error || "Gateway error"), "error");
                }
            } catch (e) {
                showToast("Network error sending test SMS.", "error");
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-paper-plane"></i> Send Test Now';
            }
        }

        // Broadcast Execution
        async function confirmBroadcast() {
            const selectedList = allRecipients.filter(r => selectedRecipientsMap.has(r.id));
            const msg = document.getElementById('smsMessage').value.trim();
            const rate = getUnitRate();
            const unicode = isUnicode(msg);
            const charCount = msg.length;
            let parts = 1;
            if (unicode) {
                if (charCount > 70) parts = Math.ceil(charCount / 67);
            } else {
                if (charCount > 160) parts = Math.ceil(charCount / 153);
            }

            const totalDispatches = selectedList.length * parts;
            const totalCost = totalDispatches * rate;

            if (selectedList.length === 0) {
                showToast("Please select at least one recipient.", "error");
                return;
            }
            if (!msg) {
                showToast("SMS message cannot be empty.", "error");
                return;
            }

            const confirmMsg = `Broadcasting Campaign Summary:
• Recipients: ${selectedList.length}
• SMS Parts: ${parts} (${unicode ? 'Unicode/Bengali' : 'GSM English'})
• Total SMS Dispatches: ${totalDispatches}
• Estimated Cost: ৳${totalCost.toFixed(2)} BDT (@ ৳${rate.toFixed(2)}/SMS)

Do you want to proceed with broadcasting?`;

            if (!confirm(confirmMsg)) {
                return;
            }

            // Open broadcast modal
            const modal = document.getElementById('broadcastModal');
            const pBar = document.getElementById('broadcastProgressBar');
            const logBox = document.getElementById('broadcastLog');
            const btnClose = document.getElementById('btnCloseBroadcast');

            document.getElementById('modalCostBadge').innerText = '৳' + totalCost.toFixed(2);
            document.getElementById('modalUnitsBadge').innerText = totalDispatches;
            document.getElementById('modalRateBadge').innerText = '৳' + rate.toFixed(2) + '/SMS';

            modal.classList.add('active');
            pBar.style.width = '0%';
            logBox.innerHTML = `[${new Date().toLocaleTimeString()}] Starting SMS broadcast to ${selectedList.length} contacts (Est. Cost: ৳${totalCost.toFixed(2)})...<br>`;
            document.getElementById('broadcastTotalCount').innerText = selectedList.length;
            document.getElementById('broadcastSuccessCount').innerText = '0';
            document.getElementById('broadcastFailedCount').innerText = '0';
            btnClose.style.display = 'none';

            // Send in batches of 15 contacts
            const batchSize = 15;
            let sentTotal = 0;
            let failedTotal = 0;

            for (let i = 0; i < selectedList.length; i += batchSize) {
                const chunk = selectedList.slice(i, i + batchSize);
                
                try {
                    const res = await fetch('../../api/controllers/CustomSMSController.php?action=sendBulk', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            recipients: chunk.map(c => ({ name: c.name, phone: c.phone })),
                            message: msg
                        })
                    });
                    const data = await res.json();

                    if (data.success) {
                        sentTotal += data.sent_count || 0;
                        failedTotal += data.failed_count || 0;

                        if (data.logs) {
                            data.logs.forEach(l => {
                                const statusColor = l.status === 'sent' ? '#4ade80' : '#f87171';
                                logBox.innerHTML += `<span style="color: ${statusColor};">[${l.status.toUpperCase()}]</span> ${escapeHtml(l.name)} (${l.phone}) ${l.reason ? '- ' + escapeHtml(l.reason) : ''}<br>`;
                            });
                        }
                    } else {
                        failedTotal += chunk.length;
                        logBox.innerHTML += `<span style="color: #f87171;">[BATCH FAILED]</span> ${escapeHtml(data.error || 'Gateway response error')}<br>`;
                    }
                } catch (e) {
                    failedTotal += chunk.length;
                    logBox.innerHTML += `<span style="color: #f87171;">[NETWORK ERROR]</span> Connection lost during batch dispatch.<br>`;
                }

                const currentProcessed = Math.min(i + batchSize, selectedList.length);
                const percent = Math.round((currentProcessed / selectedList.length) * 100);
                pBar.style.width = `${percent}%`;
                document.getElementById('broadcastSuccessCount').innerText = sentTotal;
                document.getElementById('broadcastFailedCount').innerText = failedTotal;
                logBox.scrollTop = logBox.scrollHeight;
            }

            logBox.innerHTML += `[${new Date().toLocaleTimeString()}] Broadcast finished! Successfully sent: ${sentTotal}, Failed: ${failedTotal}.<br>`;
            logBox.scrollTop = logBox.scrollHeight;
            btnClose.style.display = 'inline-block';
            fetchSMSBalance();
            showToast(`Broadcast completed: ${sentTotal} sent, ${failedTotal} failed.`);
        }

        function closeBroadcastModal() {
            document.getElementById('broadcastModal').classList.remove('active');
        }

        function escapeHtml(str) {
            if (!str) return '';
            return String(str)
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }
    </script>
</body>
</html>
