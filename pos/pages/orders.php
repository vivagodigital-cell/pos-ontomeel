<?php require_once '../../api/shared/auth_check.php'; checkAuth(true); renderUserUI(true); ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Management | Ontomeel POS</title>
    <link rel="stylesheet" href="../assets/pos-styles.css?v=1.1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.0/dist/JsBarcode.all.min.js"></script>
    
    <style>
        :root {
            --primary: var(--primary-blue);
            --primary-light: var(--primary-blue-soft);
            --success: var(--accent-mint);
            --text-main: var(--text-header);
            --text-muted: var(--text-muted);
            --bg-body: var(--bg-main);
            --card-bg: var(--bg-card);
            --border: var(--border-light);
        }

        body {
            background-color: var(--bg-body);
            font-family: 'Inter', sans-serif;
            color: var(--text-main);
            display: flex;
            min-height: 100vh;
        }

        .main-wrapper {
            /* Handled by global css, only adding specific overrides here */
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
            max-width: 1600px;
        }

        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2.5rem;
        }

        .page-title h1 {
            font-size: 2.25rem;
            font-weight: 900;
            letter-spacing: -0.025em;
            margin: 0;
        }

        .page-title p {
            color: var(--text-muted);
            margin-top: 0.5rem;
            font-weight: 500;
        }

        /* Filter Section */
        .filter-section {
            background: var(--card-bg);
            padding: 1.5rem;
            border-radius: 20px;
            border: 1px solid var(--border);
            box-shadow: var(--shadow-sm);
            margin-bottom: 2rem;
            display: flex;
            flex-wrap: wrap;
            gap: 1.25rem;
            align-items: flex-end;
            width: 100%;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            flex: 1;
            min-width: 180px;
        }

        .filter-group label {
            font-size: 0.75rem;
            font-weight: 700;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .input-control {
            background: #f1f5f9;
            border: 1px solid transparent;
            padding: 0.75rem 1rem;
            border-radius: 12px;
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--text-main);
            transition: all 0.2s;
            width: 100%;
        }

        .input-control:focus {
            background: #fff;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px var(--primary-light);
            outline: none;
        }

        .btn-filter {
            background: var(--primary);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 12px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            height: 45px;
        }

        .btn-filter:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
        }

        /* Table Design */
        .order-table-container {
            background: var(--card-bg);
            border-radius: 24px;
            border: 1px solid var(--border);
            box-shadow: var(--shadow-md);
            overflow-y: auto;
            max-height: calc(100vh - 380px); /* Responsive height for better scroll view */
            position: relative;
        }

        .order-table-container::-webkit-scrollbar {
            width: 6px;
        }
        .order-table-container::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 0 24px 24px 0;
        }
        .order-table-container::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }
        .order-table-container::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #f8fafc;
            text-align: left;
            padding: 1.25rem;
            font-size: 0.75rem;
            font-weight: 800;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-bottom: 1px solid var(--border);
            position: sticky;
            top: 0;
            z-index: 20;
        }
        
        /* Ensure first/last th maintains card radius logic if needed */
        th:first-child { border-top-left-radius: 24px; }
        th:last-child { border-top-right-radius: 24px; }

        td {
            padding: 1.25rem;
            font-size: 0.9rem;
            border-bottom: 1px solid #f1f5f9;
            transition: background 0.2s;
        }

        tr:hover td {
            background: #f8fafc;
            cursor: pointer;
        }

        .invoice-id {
            font-weight: 800;
            color: var(--primary);
            font-family: 'JetBrains Mono', monospace;
        }

        .customer-info {
            display: flex;
            flex-direction: column;
        }

        .customer-name {
            font-weight: 700;
            color: var(--text-main);
        }

        .customer-type {
            font-size: 0.7rem;
            font-weight: 800;
            text-transform: uppercase;
            padding: 2px 6px;
            border-radius: 4px;
            width: fit-content;
            margin-top: 4px;
        }

        .type-member { background: #dcfce7; color: #16a34a; }
        .type-guest { background: #fef9c3; color: #a16207; }
        .type-walkin { background: #f1f5f9; color: #64748b; }

        .status-badge {
            padding: 6px 12px;
            border-radius: 10px;
            font-size: 0.75rem;
            font-weight: 800;
            text-transform: uppercase;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .status-paid { background: #dcfce7; color: #16a34a; }
        .status-pending { background: #fef9c3; color: #a16207; }
        .status-refunded { background: #ffedd5; color: #ea580c; }
        .status-cancelled { background: #fee2e2; color: #dc2626; }

        .order-type-badge {
            font-size: 0.7rem;
            font-weight: 700;
            padding: 4px 8px;
            border-radius: 6px;
            border: 1px solid var(--border);
        }

        /* Drawer System */
        .drawer-overlay {
            position: fixed;
            top: 0;
            right: 0;
            bottom: 0;
            left: 0;
            background: rgba(15, 23, 42, 0.4);
            backdrop-filter: blur(4px);
            z-index: 1000;
            display: none;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .drawer-content {
            position: fixed;
            top: 0;
            right: -500px;
            bottom: 0;
            width: 500px;
            background: white;
            z-index: 1001;
            box-shadow: -10px 0 30px rgba(0,0,0,0.1);
            transition: right 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .drawer-active .drawer-overlay { display: block; opacity: 1; }
        .drawer-active .drawer-content { right: 0; }

        .drawer-header {
            padding: 1.5rem;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #f8fafc;
        }

        .drawer-body {
            flex: 1;
            overflow-y: auto;
            padding: 1.5rem;
        }

        .drawer-footer {
            padding: 1.5rem;
            border-top: 1px solid var(--border);
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            background: #f8fafc;
        }

        /* Item List in Drawer */
        .items-list {
            margin-top: 1.5rem;
        }

        .order-item-card {
            display: flex;
            justify-content: space-between;
            padding: 1rem 0;
            border-bottom: 1px dashed var(--border);
        }

        .item-meta h4 { margin: 0; font-size: 0.95rem; }
        .item-meta p { margin: 4px 0 0; font-size: 0.8rem; color: var(--text-muted); }

        .summary-card {
            background: #f8fafc;
            padding: 1rem;
            border-radius: 12px;
            margin-top: 1.5rem;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
            font-weight: 600;
        }

        .total-row {
            border-top: 2px solid var(--border);
            margin-top: 0.75rem;
            padding-top: 0.75rem;
            font-size: 1.1rem;
            font-weight: 800;
            color: var(--primary);
        }

        .action-btn {
            padding: 0.75rem;
            border-radius: 10px;
            border: none;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: 0.2s;
        }

        .btn-print { background: var(--text-main); color: white; }
        .btn-refund { background: #fff; color: var(--danger); border: 1px solid var(--danger); }
        .btn-cancel { background: #fee2e2; color: var(--danger); }
        .btn-duplicate { background: var(--primary-light); color: var(--primary); }

        /* Receipt Styles (keeping old ones for printing) */
        @media print {
            body * { visibility: hidden; }
            #receiptToPrint, #receiptToPrint * { visibility: visible; }
            #receiptToPrint { 
                display: block !important;
                position: absolute; 
                left: 0; 
                top: 0; 
                width: 80mm !important; 
                margin: 0 !important;
                padding: 0 !important;
                height: auto !important;
            }

            @page {
                size: 80mm auto;
                margin: 0mm;
            }

            html, body {
                width: 80mm !important;
                margin: 0 !important;
                padding: 0 !important;
                background: white;
                height: auto !important;
            }

            * {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }
        }
        
        .receipt-hidden { display: none; }

        /* Receipt Styles (optimized for flexible / 80mm thermal printing) */
        .invoice-receipt, .invoice-receipt * {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            font-weight: 800 !important;
        }

        .invoice-receipt {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            width: 100%;
            max-width: 80mm; /* Standard 80mm printer width */
            padding: 10px;
            margin: auto;
            color: #000;
            background: #fff;
            font-size: 13px;
            line-height: 1.4;
        }

        .receipt-header {
            text-align: center;
            margin-bottom: 25px;
            border-bottom: 1.5px dashed #000;
            padding-bottom: 20px;
        }

        .receipt-header img {
            max-width: 80px;
            margin-bottom: 12px;
            filter: grayscale(1);
        }

        .receipt-header h2 {
            margin: 0;
            font-size: 24px;
            font-weight: 800;
            letter-spacing: 1.5px;
            text-transform: uppercase;
        }

        .receipt-header p {
            margin: 4px 0;
            font-size: 13px;
            color: #000;
            font-weight: 500;
        }

        .receipt-info {
            padding: 12px 0;
            border-bottom: 1px dashed #eee;
            margin-bottom: 12px;
        }

        .receipt-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }

        .receipt-items {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }

        .receipt-items th {
            text-align: left;
            border-bottom: 2px solid #000;
            padding-bottom: 8px;
            font-size: 13px;
            font-weight: 800;
        }

        .receipt-items td {
            padding: 10px 0;
            font-size: 14px;
            vertical-align: top;
        }

        .receipt-totals {
            border-top: 2.5px solid #000;
            margin-top: 20px;
            padding-top: 15px;
        }

        .total-bold {
            font-weight: 800;
            font-size: 18px;
            border-top: 1px solid #000;
            margin-top: 8px;
            padding-top: 8px;
        }

        .barcode-container {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px dashed #000;
        }

        .in-words {
            font-style: italic;
            font-size: 13px;
            margin-top: 15px;
            border-top: 1px dotted #ccc;
            padding-top: 15px;
        }

        /* Toast Notification */
        #toast {
            position: fixed;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%);
            background: #1e293b;
            color: white;
            padding: 12px 24px;
            border-radius: 12px;
            font-weight: 600;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.2);
            z-index: 2000;
            display: none;
            animation: fadeInOut 2.5s ease-in-out;
        }

        @keyframes fadeInOut {
            0% { opacity: 0; transform: translate(-50%, 20px); }
            15% { opacity: 1; transform: translate(-50%, 0); }
            85% { opacity: 1; transform: translate(-50%, 0); }
            100% { opacity: 0; transform: translate(-50%, -20px); }
        }
    </style>
</head>

<body>
    <script src="../assets/sidebar.js"></script>

    <div class="main-wrapper">
        <header>
            <div class="page-title">
                <h1>Orders <span style="color: var(--primary);">History</span></h1>
                <p>Manage, filter, and track all sales transactions</p>
            </div>
            <div class="header-tools">
                <div class="user-profile" style="background: white; padding: 10px 20px; border-radius: 15px; border: 1px solid var(--border); display: flex; align-items: center; gap: 12px; height: 50px;">
                    <div style="text-align: right;">
                        <div style="font-size: 0.9rem; font-weight: 800; line-height: 1;" class="user-name-display">Admin</div>
                        <div style="font-size: 0.7rem; color: var(--success); font-weight: 800; text-transform: uppercase; margin-top: 4px;">Logged In</div>
                    </div>
                </div>
            </div>
        </header>

        <div class="filter-section">
            <div class="filter-group">
                <label>Search Orders</label>
                <input type="text" id="searchInput" class="input-control" placeholder="Invoice, Name or Phone...">
            </div>
            <div class="filter-group">
                <label>Status</label>
                <select id="statusFilter" class="input-control">
                    <option value="">All Statuses</option>
                    <option value="Paid">Paid</option>
                    <option value="Pending">Pending</option>
                    <option value="Refunded">Refunded</option>
                    <option value="Cancelled">Cancelled</option>
                </select>
            </div>
            <div class="filter-group">
                <label>Date From</label>
                <input type="date" id="dateStart" class="input-control">
            </div>
            <div class="filter-group">
                <label>Date To</label>
                <input type="date" id="dateEnd" class="input-control">
            </div>
            <button class="btn-filter" onclick="applyFilters()">
                <i class="fa-solid fa-magnifying-glass"></i> Search
            </button>
            <button class="btn-filter" style="background: #f1f5f9; color: var(--text-muted);" onclick="resetFilters()">
                <i class="fa-solid fa-rotate"></i>
            </button>
        </div>

        <div class="order-table-container">
            <table>
                <thead>
                    <tr>
                        <th style="width: 12%">Date</th>
                        <th style="width: 12%">Invoice #</th>
                        <th style="width: 20%">Customer</th>
                        <th style="width: 20%">Items Summary</th>
                        <th style="width: 10%">Order Type</th>
                        <th style="width: 8%">Amount</th>
                        <th style="width: 9%">Status</th>
                        <th style="width: 9%">Method</th>
                    </tr>
                </thead>
                <tbody id="orderTableBody">
                    <!-- Loaded via JS -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- Order Details Drawer -->
    <div class="drawer-overlay" onclick="closeDrawer()"></div>
    <div class="drawer-content" id="orderDrawer">
        <div class="drawer-header">
            <div>
                <h3 id="drawerInvoiceNo" style="margin: 0; font-weight: 900; color: var(--primary);">#INV-0000</h3>
                <span id="drawerDate" style="font-size: 0.75rem; color: var(--text-muted); font-weight: 700;"></span>
            </div>
            <button onclick="closeDrawer()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: var(--text-muted);">&times;</button>
        </div>
        <div class="drawer-body" id="drawerBody">
            <div id="drawerStatusBadge"></div>
            
            <div style="margin-top: 1.5rem; display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="summary-card" style="margin-top: 0;">
                    <label style="font-size: 0.65rem; font-weight: 800; text-transform: uppercase;">Customer</label>
                    <div id="drawerCustomerName" style="font-weight: 700; margin-top: 4px;"></div>
                    <div id="drawerCustomerContact" style="font-size: 0.8rem; color: var(--text-muted); margin-top: 2px;"></div>
                </div>
                <div class="summary-card" style="margin-top: 0;">
                    <label style="font-size: 0.65rem; font-weight: 800; text-transform: uppercase;">Staff / Terminal</label>
                    <div id="drawerStaffName" style="font-weight: 700; margin-top: 4px;">Admin</div>
                    <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 2px;">Terminal Main</div>
                </div>
            </div>

            <div class="items-list">
                <h4 style="border-bottom: 1px solid var(--border); padding-bottom: 0.5rem; margin-bottom: 0;">Items</h4>
                <div id="drawerItems"></div>
            </div>

            <div class="summary-card">
                <div class="summary-row"><span>Subtotal</span> <span id="summarySubtotal"></span></div>
                <div class="summary-row"><span>Discount</span> <span id="summaryDiscount" style="color: var(--success);"></span></div>
                <div class="summary-row total-row"><span>Total</span> <span id="summaryTotal"></span></div>
            </div>

            <div id="paymentLogs" style="margin-top: 1.5rem;">
                <h4 style="border-bottom: 1px solid var(--border); padding-bottom: 0.5rem; font-size: 0.8rem; text-transform: uppercase;">Payment Log</h4>
                <div id="drawerPaymentMethod" style="font-size: 0.9rem; font-weight: 600; padding: 10px; background: #f0fdf4; border-radius: 8px;"></div>
            </div>
        </div>
        <div class="drawer-footer">
            <button class="action-btn btn-print" onclick="printInvoice()">
                <i class="fa-solid fa-print"></i> Print
            </button>
            <button class="action-btn" style="background: #eff6ff; color: var(--primary);" onclick="copyOrderLink(activeOrder.order.invoice_no)">
                <i class="fa-solid fa-link"></i> Copy Link
            </button>
            <button class="action-btn btn-duplicate" id="duplicateBtn" onclick="duplicateOrder()">
                <i class="fa-solid fa-copy"></i> Reorder
            </button>
            <button class="action-btn btn-refund" id="refundBtn" onclick="orderAction('refund')">
                <i class="fa-solid fa-rotate-left"></i> Refund
            </button>
            <button class="action-btn btn-cancel" id="cancelBtn" onclick="orderAction('cancel')">
                <i class="fa-solid fa-ban"></i> Cancel
            </button>
            <button class="action-btn btn-refund" id="deleteBtn" onclick="orderAction('delete')" style="border-color: #ef4444; color: #ef4444;">
                <i class="fa-solid fa-trash-can"></i> Delete
            </button>
        </div>
    </div>

    <!-- Hidden Printable Receipt -->
    <div id="receiptToPrint" class="receipt-hidden"></div>

    <!-- Toast Notification -->
    <div id="toast"></div>

    <script>
        let currentOrders = [];
        let activeOrder = null;

        async function fetchOrders() {
            const search = document.getElementById('searchInput').value;
            const status = document.getElementById('statusFilter').value;
            const dateStart = document.getElementById('dateStart').value;
            const dateEnd = document.getElementById('dateEnd').value;

            const url = `../../api/controllers/TerminalController.php?action=getOrders&search=${search}&status=${status}&dateStart=${dateStart}&dateEnd=${dateEnd}`;
            
            try {
                const res = await fetch(url);
                const data = await res.json();
                if (data.success) {
                    currentOrders = data.orders;
                    renderOrders();
                }
            } catch (e) {
                console.error("Order fetch error:", e);
            }
        }

        function renderOrders() {
            const tbody = document.getElementById('orderTableBody');
            if (currentOrders.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" style="text-align:center; padding: 4rem; color: var(--text-muted);"><i class="fa-solid fa-folder-open fa-3x" style="opacity: 0.3; margin-bottom: 1rem; display: block;"></i> No orders found matching filters.</td></tr>';
                return;
            }

            tbody.innerHTML = currentOrders.map(o => {
                const customerType = o.member_id ? 'member' : (o.guest_name ? 'guest' : 'walkin');
                const typeLabel = customerType.charAt(0).toUpperCase() + customerType.slice(1);
                const rawStatus = o.payment_status || 'Paid';
                const statusStr = rawStatus.toLowerCase();
                const statusClass = `status-${statusStr}`;
                
                const statusIcons = {
                    'paid': '<i class="fa-solid fa-circle-check"></i>',
                    'pending': '<i class="fa-solid fa-clock"></i>',
                    'refunded': '<i class="fa-solid fa-rotate-left"></i>',
                    'cancelled': '<i class="fa-solid fa-ban"></i>'
                };
                const icon = statusIcons[statusStr] || '';
                
                return `
                    <tr onclick="openOrderDrawer(${o.id})">
                        <td>
                            <div style="font-weight: 700;">${new Date(o.order_date).toLocaleDateString()}</div>
                            <div style="font-size: 0.7rem; color: var(--text-muted);">${new Date(o.order_date).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}</div>
                        </td>
                        <td>
                            <div class="invoice-id">${o.invoice_no}</div>
                            <button onclick="event.stopPropagation(); copyOrderLink('${o.invoice_no}')" style="background:none; border:none; color:var(--primary); cursor:pointer; font-size:0.75rem; padding:0; margin-top:4px; display:flex; align-items:center; gap:4px;">
                                <i class="fa-solid fa-copy"></i> Copy Link
                            </button>
                        </td>
                        <td>
                            <div class="customer-info">
                                <span class="customer-name">${o.member_name || o.guest_name || 'Walk-in Customer'}</span>
                                <span class="customer-type type-${customerType}">${typeLabel}</span>
                            </div>
                        </td>
                        <td>
                            <div style="font-weight: 700; color: var(--text-main);">${o.item_count} items</div>
                            <div style="font-size: 0.75rem; color: var(--text-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 180px;">
                                ${o.first_item || '--'}${o.item_count > 1 ? ' + others' : ''}
                            </div>
                        </td>
                        <td><span class="order-type-badge">${o.order_type || 'Sale'}</span></td>
                        <td style="font-weight: 900; color: var(--text-main);">৳${parseFloat(o.total_amount).toFixed(2)}</td>
                        <td><span class="status-badge ${statusClass}">${icon} ${rawStatus}</span></td>
                        <td style="font-weight: 600; font-size: 0.8rem; color: var(--text-muted);">${o.payment_method}</td>
                    </tr>
                `;
            }).join('');
        }

        async function openOrderDrawer(orderId) {
            try {
                const res = await fetch(`../../api/controllers/TerminalController.php?action=getOrderDetails&id=${orderId}`);
                const data = await res.json();
                
                if (data.success) {
                    activeOrder = data;
                    const order = data.order;
                    const items = data.items;

                    document.getElementById('drawerInvoiceNo').innerText = order.invoice_no;
                    document.getElementById('drawerDate').innerText = new Date(order.order_date).toLocaleString();
                    document.getElementById('drawerCustomerName').innerText = order.member_name || order.guest_name || 'Walk-in Customer';
                    document.getElementById('drawerCustomerContact').innerText = order.member_phone || order.guest_phone || 'No Contact Info';
                    document.getElementById('drawerStaffName').innerText = order.staff_name || 'Admin';
                    
                    document.getElementById('summarySubtotal').innerText = '৳' + parseFloat(order.subtotal || order.total_amount).toFixed(2);
                    document.getElementById('summaryDiscount').innerText = '-৳' + parseFloat(order.discount || 0).toFixed(2);
                    document.getElementById('summaryTotal').innerText = '৳' + parseFloat(order.total_amount).toFixed(2);
                    
                    const statusIcons = {
                        'paid': '<i class="fa-solid fa-circle-check"></i>',
                        'pending': '<i class="fa-solid fa-clock"></i>',
                        'refunded': '<i class="fa-solid fa-rotate-left"></i>',
                        'cancelled': '<i class="fa-solid fa-ban"></i>'
                    };
                    const icon = statusIcons[order.payment_status.toLowerCase()] || '';
                    document.getElementById('drawerStatusBadge').innerHTML = `<span class="status-badge status-${order.payment_status.toLowerCase()}" style="font-size: 1rem; padding: 10px 20px; border-radius: 12px; width: 100%; justify-content: center; gap: 10px;">${icon} ${order.payment_status}</span>`;
                    
                    document.getElementById('drawerPaymentMethod').innerText = order.payment_method;

                    // Items
                    document.getElementById('drawerItems').innerHTML = items.map(i => `
                        <div class="order-item-card">
                            <div class="item-meta">
                                <h4>${i.book_title}</h4>
                                <p>${i.quantity} x ৳${parseFloat(i.unit_price).toFixed(2)}</p>
                            </div>
                            <div style="font-weight: 800;">৳${parseFloat(i.total_price).toFixed(2)}</div>
                        </div>
                    `).join('');

                    // UI visibility for buttons
                    const status = order.payment_status.toLowerCase();
                    const isPaid = status === 'paid';
                    const isCancelled = status === 'cancelled';
                    const isRefunded = status === 'refunded';

                    document.getElementById('refundBtn').style.display = isPaid ? 'flex' : 'none';
                    document.getElementById('cancelBtn').style.display = isPaid ? 'flex' : 'none';
                    document.getElementById('deleteBtn').style.display = 'flex'; // Always allow delete for duplicates/errors

                    document.body.classList.add('drawer-active');
                    
                    // Prepare receipt for printing (reuse your old formatting here)
                    prepareReceipt(data);
                }
            } catch (e) {
                console.error("View order error:", e);
            }
        }

        function numberToWords(number) {
            const words = {
                0: 'Zero', 1: 'One', 2: 'Two', 3: 'Three', 4: 'Four', 5: 'Five', 6: 'Six', 7: 'Seven', 8: 'Eight', 9: 'Nine',
                10: 'Ten', 11: 'Eleven', 12: 'Twelve', 13: 'Thirteen', 14: 'Fourteen', 15: 'Fifteen', 16: 'Sixteen', 17: 'Seventeen', 18: 'Eighteen', 19: 'Nineteen',
                20: 'Twenty', 30: 'Thirty', 40: 'Forty', 50: 'Fifty', 60: 'Sixty', 70: 'Seventy', 80: 'Eighty', 90: 'Ninety'
            };

            if (number in words) return words[number];

            let amountInWords = '';
            if (number >= 100) {
                amountInWords += numberToWords(Math.floor(number / 100)) + ' Hundred ';
                number %= 100;
            }

            if (number > 0) {
                if (amountInWords !== '') amountInWords += 'and ';
                if (number < 20) {
                    amountInWords += words[number];
                } else {
                    amountInWords += words[Math.floor(number / 10) * 10];
                    if (number % 10 > 0) {
                        amountInWords += '-' + words[number % 10];
                    }
                }
            }

            return amountInWords.trim();
        }

        function formatAmountInWords(amount) {
            const integerPart = Math.floor(amount);
            const decimalPart = Math.round((amount - integerPart) * 100);

            let result = numberToWords(integerPart) + ' Taka';
            if (decimalPart > 0) {
                result += ' and ' + numberToWords(decimalPart) + ' Paisa';
            }
            return result + ' Only';
        }

        function closeDrawer() {
            document.body.classList.remove('drawer-active');
        }

        function applyFilters() {
            fetchOrders();
        }

        function setDefaultDates() {
            const today = new Date();
            const lastWeek = new Date();
            lastWeek.setDate(today.getDate() - 7);
            
            const formatDate = (date) => {
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');
                return `${year}-${month}-${day}`;
            };
            
            document.getElementById('dateEnd').value = formatDate(today);
            document.getElementById('dateStart').value = formatDate(lastWeek);
        }

        function resetFilters() {
            document.getElementById('searchInput').value = '';
            document.getElementById('statusFilter').value = '';
            setDefaultDates();
            fetchOrders();
        }

        async function orderAction(type) {
            const messages = {
                refund: "Are you sure you want to refund this order? This will revert stock.",
                cancel: "Are you sure you want to cancel this order? This will revert stock.",
                delete: "Are you sure you want to DELETE this order? This will be permanently removed from history and stock will be reverted."
            };

            if (!confirm(messages[type] || `Proceed with ${type}?`)) return;

            try {
                const res = await fetch('../../api/controllers/TerminalController.php?action=orderAction', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: activeOrder.order.id, type: type })
                });
                const data = await res.json();
                if (data.success) {
                    alert(`Order ${type}ed successfully.`);
                    closeDrawer();
                    fetchOrders();
                } else {
                    alert('Error: ' + data.error);
                }
            } catch (e) {
                console.error(e);
            }
        }

        function printInvoice() {
            window.print();
        }

        function showToast(message) {
            const toast = document.getElementById('toast');
            toast.innerText = message;
            toast.style.display = 'block';
            setTimeout(() => {
                toast.style.display = 'none';
            }, 2500);
        }

        function copyOrderLink(invoiceNo) {
            const baseUrl = window.location.origin + window.location.pathname.substring(0, window.location.pathname.indexOf('/pos/'));
            const link = baseUrl + '/' + invoiceNo;
            const text = `Thanks for your order. Your Order Number is '${invoiceNo}' to see all details follow the link '${link}'`;
            
            navigator.clipboard.writeText(text).then(() => {
                showToast("Order link copied to clipboard!");
            }).catch(err => {
                console.error('Could not copy text: ', err);
                // Fallback for older browsers or non-secure contexts
                const textArea = document.createElement("textarea");
                textArea.value = text;
                document.body.appendChild(textArea);
                textArea.select();
                try {
                    document.execCommand('copy');
                    showToast("Order link copied to clipboard!");
                } catch (err) {
                    alert("Failed to copy link. Please copy it manually.");
                }
                document.body.removeChild(textArea);
            });
        }

        function duplicateOrder() {
            if (!activeOrder || !activeOrder.items) return;
            
            // Format items for terminal cart
            // Terminal expects: { id, title, sell_price, item_type, _isInventory ... }
            // Our items have: { id, book_id, book_title, unit_price, quantity, item_type ... }
            
            const cartToLoad = [];
            activeOrder.items.forEach(item => {
                const terminalItem = {
                    id: item.book_id,
                    title: item.book_title,
                    sell_price: item.unit_price,
                    item_type: item.item_type || 'Book',
                    _isInventory: item.item_type === 'Book' ? 0 : 1
                };
                
                // Add multiple times based on quantity
                for(let i=0; i<parseInt(item.quantity); i++) {
                    cartToLoad.push({...terminalItem});
                }
            });

            localStorage.setItem('pos_quick_reorder', JSON.stringify({
                items: cartToLoad,
                memberId: activeOrder.order.member_id,
                memberName: activeOrder.order.member_name
            }));
            
            window.location.href = 'terminal.php';
        }

        function prepareReceipt(data) {
            const order = data.order;
            const items = data.items;
            const container = document.getElementById('receiptToPrint');

            const customerName = order.member_name || order.guest_name || 'Cash Customer';
            const customerPhone = order.member_phone || order.guest_phone || '--';
            const memberIdField = order.member_id ? `<div class="receipt-row"><span>Member ID:</span> <span>${order.membership_id || ''}</span></div>` : '';

            const itemsHtml = items.map(i => `
                <tr>
                    <td>${i.book_title}</td>
                    <td>${i.quantity}</td>
                    <td style="text-align: right;">৳${parseFloat(i.total_price).toFixed(2)}</td>
                </tr>
            `).join('');

            container.innerHTML = `
                <div class="invoice-receipt">
                    <div class="receipt-header">
                        <img src="../assets/logo.webp" alt="Logo">
                        <h2>ONTOMEEL</h2>
                        <p style="font-weight:700;">Library & Bookstore</p>
                        <p>Shop no 06, Changing Closet Building, Motel Labonee, Motel Road, Cox's Bazar</p>
                        <p>Phone: 01330975787 | Email: info@ontomeel.com</p>
                    </div>
                    
                    <div class="receipt-info" style="border-top:1px dashed #000; border-bottom:1px dashed #000;">
                        <div class="receipt-row"><span>Date:</span> <span>${new Date(order.order_date).toLocaleString()}</span></div>
                        <div class="receipt-row"><span>Inv #:</span> <span>${order.invoice_no}</span></div>
                        <div class="receipt-row"><span>Sold by:</span> <span style="text-transform:capitalize;">${order.staff_name || 'Admin'}</span></div>
                    </div>
                    
                    <div class="receipt-info">
                        <div class="receipt-row"><span>Customer:</span> <span>${customerName}</span></div>
                        <div class="receipt-row"><span>Phone:</span> <span>${customerPhone}</span></div>
                        ${memberIdField}
                    </div>
                    
                    <table class="receipt-items">
                        <thead>
                            <tr>
                                <th style="width: 50%;">Item</th>
                                <th style="width: 20%;">Qty</th>
                                <th style="width: 30%; text-align: right;">Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${itemsHtml}
                        </tbody>
                    </table>
                    
                    <div class="receipt-totals" style="border-top: 2px solid #000;">
                        <div class="receipt-row"><span>Subtotal:</span> <span>৳${parseFloat(order.subtotal || order.total_amount).toFixed(2)}</span></div>
                        <div class="receipt-row"><span>Discount:</span> <span>৳${parseFloat(order.discount || 0).toFixed(2)}</span></div>
                        <div class="receipt-row total-bold"><span>Total:</span> <span>৳${parseFloat(order.total_amount).toFixed(2)}</span></div>
                    </div>

                    <div class="in-words">
                        <strong>Amount in Words:</strong><br>
                        ${formatAmountInWords(parseFloat(order.total_amount))}
                    </div>

                    <div style="margin-top:15px; border-top:1px dotted #000; padding-top:10px;">
                        <div style="font-weight: 800; text-transform: uppercase; font-size: 10px; margin-bottom: 5px;">Payment Details</div>
                        <div class="receipt-row"><span>Method:</span> <span>${order.payment_method}</span></div>
                    </div>
                    
                    <div class="barcode-container">
                        <svg id="receipt-barcode-history"></svg>
                    </div>

                    <div style="margin-top: 15px; text-align: center; opacity: 0.8;">
                        <p style="margin: 0; font-size: 11px; font-weight: 800;">Thank You for Shopping!</p>
                        <p style="margin: 0; font-size: 9px;">Software by VivaGo Digital</p>
                    </div>
                </div>
            `;

            // Draw Barcode after content is injected
            setTimeout(() => {
                JsBarcode("#receipt-barcode-history", order.invoice_no, {
                    format: "CODE128",
                    width: 1.2,
                    height: 35,
                    displayValue: false,
                    margin: 0
                });
            }, 100);
        }

        window.onload = () => {
            setDefaultDates();
            fetchOrders();
        };
    </script>
</body>
</html>