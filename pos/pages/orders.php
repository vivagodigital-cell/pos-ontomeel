<?php require_once '../../api/shared/auth_check.php'; checkAuth(true); renderUserUI(true); ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order History | Ontomeel POS</title>
    <link rel="stylesheet" href="../assets/pos-styles.css?v=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.0/dist/JsBarcode.all.min.js"></script>
    
    <style>
        .order-table-container {
            background: white;
            border-radius: 24px;
            padding: 2rem;
            border: 1px solid var(--border-light);
            box-shadow: var(--shadow-md);
            margin-top: 2rem;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            text-align: left;
            padding: 1.25rem 1rem;
            font-size: 0.75rem;
            font-weight: 800;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #f1f5f9;
        }

        td {
            padding: 1.25rem 1rem;
            font-size: 0.9rem;
            color: var(--text-header);
            border-bottom: 1px solid #f1f5f9;
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
        }

        .paid {
            background: #dcfce7;
            color: #16a34a;
        }

        .pending {
            background: #fef9c3;
            color: #a16207;
        }

        .btn-view {
            background: #eff6ff;
            color: var(--primary-blue);
            border: none;
            padding: 8px 16px;
            border-radius: 10px;
            font-size: 0.8rem;
            font-weight: 700;
            cursor: pointer;
            transition: 0.2s;
        }

        .btn-view:hover {
            background: #dbeafe;
            transform: translateY(-1px);
        }

        /* Receipt Styles (optimized for 576px / 80mm thermal printing) */
        .invoice-receipt {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            width: 576px;
            padding: 20px;
            margin: auto;
            color: #000;
            background: #fff;
            font-size: 14px;
            line-height: 1.4;
            min-height: 300px;
            max-height: 1500px;
            text-align: left;
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

        @media print {
            body * {
                visibility: hidden;
            }

            #invoicePrintContainer,
            #invoicePrintContainer * {
                visibility: visible;
            }

            #invoicePrintContainer {
                position: absolute;
                left: 0;
                top: 0;
                width: 576px;
                background: white;
            }

            @page {
                size: 576px 1500px;
                margin: 0;
            }

            .invoice-receipt {
                width: 576px;
                margin: 0;
                padding: 30px;
            }

            #invoiceModal,
            .modal-overlay,
            header,
            .main-wrapper {
                display: none !important;
            }
        }

        .print-btn {
            background: #1e293b;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 12px;
            font-weight: 700;
            width: 100%;
            margin-top: 1.5rem;
            cursor: pointer;
        }
    </style>
</head>

<body>

    <script src="../assets/sidebar.js"></script>

    <div class="main-wrapper">
        <header>
            <div class="page-title">
                <h1>Order <span style="color: var(--primary-blue);">History</span></h1>
                <p>Track and manage all terminal sales transactions</p>
            </div>
            <div class="header-tools" style="display: flex; align-items: center; gap: 1rem;">
                <div class="user-profile" style="display: flex; align-items: center; gap: 12px;">
                    <div style="text-align: right;">
                        <div style="font-size: 0.85rem; font-weight: 700; color: var(--text-header);"
                            class="user-name-display">Admin</div>
                        <div
                            style="font-size: 0.7rem; color: var(--accent-mint); font-weight: 800; text-transform: uppercase;">
                            Staff</div>
                    </div>
                </div>
            </div>
        </header>

        <div class="order-table-container">
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Invoice #</th>
                        <th>Member/Guest</th>
                        <th>Contact Info</th>
                        <th>Amount</th>
                        <th>Method</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="orderTableBody">
                    <tr>
                        <td colspan="8" style="text-align:center; padding: 3rem; color: var(--text-muted);"><i
                                class="fa-solid fa-spinner fa-spin"></i> Fetching orders...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Invoice View Modal -->
    <div class="modal-overlay" id="invoiceModal"
        style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(15, 23, 42, 0.6); z-index: 1000; align-items: center; justify-content: center;">
        <div style="background: white; padding: 0; border-radius: 24px; max-width: 600px; width: 95%; overflow: hidden;">
            <div style="padding: 20px; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center;">
                <h3 style="margin: 0; font-weight: 800;">Sale Receipt</h3>
                <button onclick="closeInvoiceModal()"
                    style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #94a3b8;">&times;</button>
            </div>

            <div style="padding: 20px; max-height: 70vh; overflow-y: auto; background: #f8fafc;">
                <div id="invoicePrintContainer">
                    <!-- Receipt content populated by JS -->
                </div>
            </div>

            <div style="padding: 20px; border-top: 1px solid #f1f5f9;">
                <button class="print-btn" onclick="printInvoice()">
                    <i class="fa-solid fa-print"></i> PRINT RECEIPT (80mm)
                </button>
            </div>
        </div>
    </div>

    <script>
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

        async function fetchOrders() {
            try {
                const res = await fetch('../../api/controllers/TerminalController.php?action=getOrders');
                const data = await res.json();

                if (data.success) {
                    const tbody = document.getElementById('orderTableBody');
                    if (data.orders.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="7" style="text-align:center; padding: 3rem;">No orders found.</td></tr>';
                        return;
                    }

                    tbody.innerHTML = data.orders.map(o => {
                        const contactPhone = o.member_phone || o.guest_phone || '--';
                        const contactEmail = o.member_email || o.guest_email || '';
                        const contactHtml = `<div style="font-size:0.8rem; font-weight:700;">${contactPhone}</div>` + (contactEmail ? `<div style="font-size:0.7rem; color:var(--text-muted);">${contactEmail}</div>` : '');
                        
                        return `
                            <tr>
                                <td>${new Date(o.order_date).toLocaleString()}</td>
                                <td style="font-weight: 700;">${o.invoice_no}</td>
                                <td>${o.member_name || o.guest_name || 'Anonymous'}</td>
                                <td>${contactHtml}</td>
                                <td style="font-weight: 800;">৳${parseFloat(o.total_amount).toFixed(2)}</td>
                                <td>${o.payment_method}</td>
                                <td><span class="status-badge ${o.payment_status.toLowerCase()}">${o.payment_status}</span></td>
                                <td><button class="btn-view" onclick="viewInvoice(${o.id})">View/Print</button></td>
                            </tr>
                        `;
                    }).join('');
                }
            } catch (e) { console.error(e); }
        }

        async function viewInvoice(orderId) {
            try {
                const res = await fetch(`../../api/controllers/TerminalController.php?action=getOrderDetails&id=${orderId}`);
                const data = await res.json();

                if (data.success) {
                    const order = data.order;
                    const items = data.items;

                    // Format split balance breakdown or single method
                    let paymentDetailsHtml = '';
                    if (order.payment_method && order.payment_method.includes(':')) {
                        paymentDetailsHtml = order.payment_method.split(', ').map(line => {
                            const parts = line.split(': ');
                            const method = parts[0] || 'Unknown';
                            const amount = parts[1] || '';
                            return `<div class="receipt-row"><span>Paid via ${method}:</span> <span>${amount}</span></div>`;
                        }).join('');
                    } else {
                        paymentDetailsHtml = `<div class="receipt-row"><span>Payment Method:</span> <span>${order.payment_method || 'Cash'}</span></div>`;
                    }

                    document.getElementById('invoicePrintContainer').innerHTML = `
                        <div class="invoice-receipt" id="receiptToPrint">
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
                                <div class="receipt-row"><span>Customer:</span> <span>${order.member_name || order.guest_name || 'Cash Customer'}</span></div>
                                <div class="receipt-row"><span>Phone:</span> <span>${order.phone || order.guest_phone || '--'}</span></div>
                                ${order.membership_id ? `<div class="receipt-row"><span>Member ID:</span> <span>${order.membership_id}</span></div>` : ''}
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
                                    ${(() => {
                                        const grouped = {};
                                        items.forEach(item => {
                                            const bId = item.book_id || item.id;
                                            if (grouped[bId]) {
                                                grouped[bId].quantity += parseInt(item.quantity) || 1;
                                                grouped[bId].total_price += parseFloat(item.total_price || item.sell_price);
                                            } else {
                                                grouped[bId] = { ...item, quantity: parseInt(item.quantity) || 1, total_price: parseFloat(item.total_price || item.sell_price) };
                                            }
                                        });
                                        return Object.values(grouped).map(item => `
                                            <tr>
                                                <td>${item.book_title || item.title}</td>
                                                <td>${item.quantity}</td>
                                                <td style="text-align: right;">৳${parseFloat(item.total_price).toFixed(2)}</td>
                                            </tr>
                                        `).join('');
                                    })()}
                                </tbody>
                            </table>
                            
                            <div class="receipt-totals">
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
                                ${paymentDetailsHtml}
                            </div>
                            
                            <div class="barcode-container">
                                <svg id="history-barcode"></svg>
                            </div>

                            <div style="margin-top: 15px; text-align: center; opacity: 0.8;">
                                <p style="margin: 0; font-size: 11px; font-weight: 800;">Thank You for Shopping!</p>
                                <p style="margin: 0; font-size: 9px;">Software by VivaGo Digital</p>
                            </div>
                        </div>
                    `;

                    // Generate Barcode
                    setTimeout(() => {
                        JsBarcode("#history-barcode", order.invoice_no, {
                            format: "CODE128",
                            width: 1.2,
                            height: 35,
                            displayValue: false,
                            margin: 0
                        });
                    }, 50);

                    document.getElementById('invoiceModal').style.display = 'flex';
                }
            } catch (e) { console.error(e); }
        }

        function closeInvoiceModal() {
            document.getElementById('invoiceModal').style.display = 'none';
        }

        function printInvoice() {
            window.print();
        }

        window.onload = function() {
            fetchOrders();
            // Background sync - refresh orders every 15 seconds
            setInterval(fetchOrders, 15000);
        };
    </script>
</body>

</html>