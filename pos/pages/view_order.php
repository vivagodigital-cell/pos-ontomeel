<?php
// pos/pages/view_order.php
require_once '../../api/config/database.php';

$invoice = $_GET['invoice'] ?? '';

if (empty($invoice)) {
    die("Invalid Order Reference.");
}

// Get order details
$stmt = $pdo->prepare("SELECT o.*, m.full_name as member_name, m.phone as member_phone, m.email as member_email 
                      FROM orders o 
                      LEFT JOIN members m ON o.member_id = m.id 
                      WHERE o.invoice_no = ?");
$stmt->execute([$invoice]);
$order = $stmt->fetch();

if (!$order) {
    die("Order not found.");
}

// Get items
$stmt = $pdo->prepare("SELECT oi.*, COALESCE(b.title, i.item_name) as item_title 
                      FROM order_items oi 
                      LEFT JOIN books b ON oi.book_id = b.id AND (oi.item_type = 'Book' OR oi.item_type IS NULL)
                      LEFT JOIN inventory_items i ON oi.book_id = i.id AND (oi.item_type != 'Book')
                      WHERE oi.order_id = ?");
$stmt->execute([$order['id']]);
$items = $stmt->fetchAll();

$customerName = $order['member_name'] ?: ($order['guest_name'] ?: 'Walk-in Customer');
$customerContact = $order['member_phone'] ?: ($order['guest_phone'] ?: 'No contact');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details - <?php echo htmlspecialchars($invoice); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #2563eb;
            --success: #10b981;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --bg-body: #f8fafc;
            --card-bg: #ffffff;
            --border: #e2e8f0;
        }

        body {
            background-color: var(--bg-body);
            font-family: 'Inter', sans-serif;
            color: var(--text-main);
            margin: 0;
            padding: 2rem 1rem;
            display: flex;
            justify-content: center;
        }

        .view-container {
            width: 100%;
            max-width: 600px;
        }

        .order-card {
            background: var(--card-bg);
            border-radius: 24px;
            border: 1px solid var(--border);
            box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05);
            overflow: hidden;
        }

        .header {
            padding: 2rem;
            background: #f8fafc;
            border-bottom: 1px solid var(--border);
            text-align: center;
        }

        .header h1 {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 900;
            color: var(--primary);
        }

        .header p {
            margin: 0.5rem 0 0;
            color: var(--text-muted);
            font-size: 0.9rem;
            font-weight: 600;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 800;
            text-transform: uppercase;
            margin-top: 1rem;
        }

        .status-paid { background: #dcfce7; color: #16a34a; }
        .status-pending { background: #fef9c3; color: #a16207; }
        .status-refunded { background: #ffedd5; color: #ea580c; }
        .status-cancelled { background: #fee2e2; color: #dc2626; }

        .body {
            padding: 2rem;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .info-item label {
            display: block;
            font-size: 0.7rem;
            font-weight: 800;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 4px;
        }

        .info-item div {
            font-weight: 700;
            font-size: 0.95rem;
        }

        .items-list {
            border-top: 1px solid var(--border);
            padding-top: 1.5rem;
        }

        .items-list h3 {
            font-size: 0.9rem;
            font-weight: 800;
            margin: 0 0 1rem;
            text-transform: uppercase;
            color: var(--text-muted);
        }

        .item-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
            border-bottom: 1px dashed var(--border);
        }

        .item-row:last-child {
            border-bottom: none;
        }

        .item-info h4 {
            margin: 0;
            font-size: 0.95rem;
            font-weight: 700;
        }

        .item-info p {
            margin: 4px 0 0;
            font-size: 0.8rem;
            color: var(--text-muted);
        }

        .item-price {
            font-weight: 800;
            font-size: 0.95rem;
        }

        .summary-card {
            background: #f8fafc;
            padding: 1.5rem;
            border-radius: 16px;
            margin-top: 1.5rem;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .total-row {
            border-top: 2px solid var(--border);
            margin-top: 0.75rem;
            padding-top: 0.75rem;
            font-size: 1.25rem;
            font-weight: 900;
            color: var(--primary);
        }

        .footer {
            padding: 1.5rem;
            text-align: center;
            font-size: 0.8rem;
            color: var(--text-muted);
            font-weight: 500;
        }

        @media (max-width: 480px) {
            .info-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="view-container">
        <div class="order-card">
            <div class="header">
                <h1>Order Receipt</h1>
                <p>#<?php echo htmlspecialchars($invoice); ?></p>
                <div class="status-badge status-<?php echo strtolower($order['payment_status']); ?>">
                    <?php 
                    $status = strtolower($order['payment_status']);
                    if ($status == 'paid') echo '<i class="fa-solid fa-circle-check"></i>';
                    elseif ($status == 'pending') echo '<i class="fa-solid fa-clock"></i>';
                    elseif ($status == 'refunded') echo '<i class="fa-solid fa-rotate-left"></i>';
                    else echo '<i class="fa-solid fa-ban"></i>';
                    ?>
                    <?php echo htmlspecialchars($order['payment_status']); ?>
                </div>
            </div>
            <div class="body">
                <div class="info-grid">
                    <div class="info-item">
                        <label>Customer</label>
                        <div><?php echo htmlspecialchars($customerName); ?></div>
                        <div style="font-size: 0.8rem; color: var(--text-muted);"><?php echo htmlspecialchars($customerContact); ?></div>
                    </div>
                    <div class="info-item">
                        <label>Date & Time</label>
                        <div><?php echo date('d M Y, h:i A', strtotime($order['order_date'])); ?></div>
                    </div>
                </div>

                <div class="items-list">
                    <h3>Items Ordered</h3>
                    <?php foreach ($items as $item): ?>
                    <div class="item-row">
                        <div class="item-info">
                            <h4><?php echo htmlspecialchars($item['item_title']); ?></h4>
                            <p><?php echo (int)$item['quantity']; ?> x ৳<?php echo number_format($item['unit_price'], 2); ?></p>
                        </div>
                        <div class="item-price">
                            ৳<?php echo number_format($item['total_price'], 2); ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="summary-card">
                    <div class="summary-row">
                        <span>Subtotal</span>
                        <span>৳<?php echo number_format($order['subtotal'], 2); ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Discount</span>
                        <span style="color: var(--success);">-৳<?php echo number_format($order['discount'], 2); ?></span>
                    </div>
                    <div class="summary-row total-row">
                        <span>Total Paid</span>
                        <span>৳<?php echo number_format($order['total_amount'], 2); ?></span>
                    </div>
                </div>
                
                <div style="margin-top: 1.5rem; font-size: 0.85rem; font-weight: 600; padding: 12px; background: #f0fdf4; border-radius: 10px; color: #166534; display: flex; align-items: center; gap: 10px;">
                    <i class="fa-solid fa-wallet"></i>
                    Payment Method: <?php echo htmlspecialchars($order['payment_method']); ?>
                </div>
            </div>
            <div class="footer">
                <p>Thank you for shopping with Ontomeel POS</p>
                <p>© <?php echo date('Y'); ?> Ontomeel. All rights reserved.</p>
            </div>
        </div>
    </div>
</body>
</html>
