<?php
// api/controllers/ReportController.php
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

try {
    if ($action === 'getSummary') {
        // Total Sales (All time)
        $totalSales = $pdo->query("SELECT SUM(total_amount) FROM orders WHERE payment_status = 'Paid'")->fetchColumn() ?: 0;
        
        // Total Borrows
        $totalBorrows = $pdo->query("SELECT COUNT(*) FROM borrows")->fetchColumn();
        
        // Total Members
        $totalMembers = $pdo->query("SELECT COUNT(*) FROM members")->fetchColumn();
        
        // Active Borrows
        $activeBorrows = $pdo->query("SELECT COUNT(*) FROM borrows WHERE status IN ('Processing', 'Active')")->fetchColumn();

        echo json_encode([
            'success' => true,
            'summary' => [
                'total_sales' => number_format((float)$totalSales, 2),
                'total_borrows' => $totalBorrows,
                'total_members' => $totalMembers,
                'active_borrows' => $activeBorrows
            ]
        ]);
    }
    elseif ($action === 'getSalesData') {
        // Last 7 days sales
        $stmt = $pdo->query("
            SELECT DATE(order_date) as date, SUM(total_amount) as amount 
            FROM orders 
            WHERE order_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) 
            AND payment_status = 'Paid'
            GROUP BY DATE(order_date)
            ORDER BY date ASC
        ");
        $sales = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'data' => $sales]);
    }
    elseif ($action === 'getPopularBooks') {
        // Top 5 borrowed books
        $stmt = $pdo->query("
            SELECT b.title, COUNT(br.id) as borrow_count 
            FROM books b 
            JOIN borrows br ON b.id = br.book_id 
            GROUP BY b.id 
            ORDER BY borrow_count DESC 
            LIMIT 5
        ");
        $popular = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'data' => $popular]);
    }
    elseif ($action === 'getMemberStats') {
        // Members by plan
        $stmt = $pdo->query("SELECT membership_plan, COUNT(*) as count FROM members GROUP BY membership_plan");
        $plans = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'data' => $plans]);
    }
    elseif ($action === 'getAdvancedStats') {
        // Sales split by Source (Website vs POS)
        $websiteSales = $pdo->query("SELECT SUM(total_amount) FROM orders WHERE payment_status = 'Paid' AND guest_email IS NOT NULL AND guest_email != ''")->fetchColumn() ?: 0;
        $posSales = $pdo->query("SELECT SUM(total_amount) FROM orders WHERE payment_status = 'Paid' AND (guest_email IS NULL OR guest_email = '')")->fetchColumn() ?: 0;
        
        $totalBooks = $pdo->query("SELECT COUNT(*) FROM books")->fetchColumn();
        $outOfStock = $pdo->query("SELECT COUNT(*) FROM books WHERE stock_qty <= 0")->fetchColumn();
        
        $topSellers = $pdo->query("
            SELECT b.title, SUM(oi.quantity) as total_sold
            FROM books b
            JOIN order_items oi ON b.id = oi.book_id
            JOIN orders o ON oi.order_id = o.id
            WHERE o.payment_status = 'Paid'
            GROUP BY b.id
            ORDER BY total_sold DESC
            LIMIT 5
        ")->fetchAll(PDO::FETCH_ASSOC);

        $recentActivity = $pdo->query("
            SELECT invoice_no, total_amount, guest_email, order_date 
            FROM orders 
            ORDER BY order_date DESC 
            LIMIT 5
        ")->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'adv' => [
                'website_sales' => $websiteSales,
                'pos_sales' => $posSales,
                'total_books' => $totalBooks,
                'low_stock' => $outOfStock,
                'top_sellers' => $topSellers,
                'recent_activity' => $recentActivity
            ]
        ]);
    }
    elseif ($action === 'getTodayReportData') {
        $from = $_GET['from_date'] ?? date('Y-m-d');
        $to = $_GET['to_date'] ?? date('Y-m-d');
        
        // 1. Books sold today (POS vs Website)
        $posBooks = $pdo->prepare("SELECT SUM(oi.quantity) FROM orders o JOIN order_items oi ON o.id = oi.order_id WHERE DATE(o.order_date) BETWEEN ? AND ? AND (o.guest_email IS NULL OR o.guest_email = '') AND o.payment_status = 'Paid'");
        $posBooks->execute([$from, $to]);
        $posCount = $posBooks->fetchColumn() ?: 0;

        $webBooks = $pdo->prepare("SELECT SUM(oi.quantity) FROM orders o JOIN order_items oi ON o.id = oi.order_id WHERE DATE(o.order_date) BETWEEN ? AND ? AND o.guest_email IS NOT NULL AND o.guest_email != '' AND o.payment_status = 'Paid'");
        $webBooks->execute([$from, $to]);
        $webCount = $webBooks->fetchColumn() ?: 0;

        // 2. Which customer bought which books (Aggregated by book for each customer)
        $customerPurchases = $pdo->prepare("
            SELECT 
                customer_name,
                customer_phone,
                GROUP_CONCAT(CONCAT(book_title, ' (x', total_qty, ')') SEPARATOR '<br>') as books
            FROM (
                SELECT 
                    COALESCE(m.full_name, o.guest_name, 'Unknown Customer') as customer_name,
                    COALESCE(m.phone, o.guest_phone, 'N/A') as customer_phone,
                    b.title as book_title,
                    SUM(oi.quantity) as total_qty,
                    o.member_id, o.guest_email, o.guest_name
                FROM orders o
                JOIN order_items oi ON o.id = oi.order_id
                JOIN books b ON oi.book_id = b.id
                LEFT JOIN members m ON o.member_id = m.id
                WHERE DATE(o.order_date) BETWEEN ? AND ? AND o.payment_status = 'Paid'
                GROUP BY o.member_id, o.guest_email, o.guest_name, b.id
            ) AS sub
            GROUP BY member_id, guest_email, guest_name
            ORDER BY customer_name ASC
        ");
        $customerPurchases->execute([$from, $to]);
        $purchases = $customerPurchases->fetchAll(PDO::FETCH_ASSOC);

        // 3. Which customer bought most books
        $topCustomer = $pdo->prepare("
            SELECT 
                COALESCE(m.full_name, o.guest_name, 'Guest') as customer_name,
                SUM(oi.quantity) as total_qty
            FROM orders o
            JOIN order_items oi ON o.id = oi.order_id
            LEFT JOIN members m ON o.member_id = m.id
            WHERE DATE(o.order_date) BETWEEN ? AND ? AND o.payment_status = 'Paid'
            GROUP BY o.member_id, o.guest_email, o.guest_name
            ORDER BY total_qty DESC
            LIMIT 1
        ");
        $topCustomer->execute([$from, $to]);
        $topCust = $topCustomer->fetch(PDO::FETCH_ASSOC);

        // 4. Which books sold (qty)
        $bookStats = $pdo->prepare("
            SELECT b.title, SUM(oi.quantity) as qty
            FROM order_items oi
            JOIN books b ON oi.book_id = b.id
            JOIN orders o ON oi.order_id = o.id
            WHERE DATE(o.order_date) BETWEEN ? AND ? AND o.payment_status = 'Paid'
            GROUP BY b.id
        ");
        $bookStats->execute([$from, $to]);
        $booksSold = $bookStats->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'data' => [
                'pos_sold' => $posCount,
                'web_sold' => $webCount,
                'purchases' => $purchases,
                'top_customer' => $topCust,
                'books_sold' => $booksSold
            ]
        ]);
    }
    elseif ($action === 'getFullDatabaseSummary') {
        // Collect statistics from the entire database for AI context
        
        // 1. Overall Totals
        $totals = [
            'total_sales_amount' => $pdo->query("SELECT SUM(total_amount) FROM orders WHERE payment_status = 'Paid'")->fetchColumn() ?: 0,
            'total_orders_count' => $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn(),
            'total_books' => $pdo->query("SELECT COUNT(*) FROM books")->fetchColumn(),
            'total_members' => $pdo->query("SELECT COUNT(*) FROM members")->fetchColumn(),
            'total_borrows' => $pdo->query("SELECT COUNT(*) FROM borrows")->fetchColumn(),
            'total_active_borrows' => $pdo->query("SELECT COUNT(*) FROM borrows WHERE status NOT IN ('Returned', 'Cancelled')")->fetchColumn(),
            'out_of_stock_books' => $pdo->query("SELECT COUNT(*) FROM books WHERE stock_qty <= 0")->fetchColumn()
        ];

        // 2. Monthly Sales (Last 6 Months)
        $monthlySales = $pdo->query("
            SELECT DATE_FORMAT(order_date, '%Y-%m') as month, SUM(total_amount) as total
            FROM orders 
            WHERE payment_status = 'Paid'
            GROUP BY month 
            ORDER BY month DESC 
            LIMIT 6
        ")->fetchAll(PDO::FETCH_ASSOC);

        // 3. Top 5 Books of All Time
        $topBooks = $pdo->query("
            SELECT b.title, COUNT(oi.id) as sell_count
            FROM books b
            JOIN order_items oi ON b.id = oi.book_id
            GROUP BY b.id
            ORDER BY sell_count DESC
            LIMIT 5
        ")->fetchAll(PDO::FETCH_ASSOC);

        // 4. Membership Distribution
        $memberStats = $pdo->query("SELECT membership_plan, COUNT(*) as count FROM members GROUP BY membership_plan")->fetchAll(PDO::FETCH_ASSOC);

        // 5. Recent High-Value Transactions
        $recentBigTrx = $pdo->query("SELECT invoice_no, total_amount, order_date FROM orders WHERE payment_status = 'Paid' ORDER BY total_amount DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'data' => [
                'totals' => $totals,
                'monthly_performance' => $monthlySales,
                'top_performing_books' => $topBooks,
                'membership_stats' => $memberStats,
                'notable_transactions' => $recentBigTrx,
                'today' => [
                    'date' => date('Y-m-d'),
                    'sales' => $pdo->query("SELECT SUM(total_amount) FROM orders WHERE DATE(order_date) = CURDATE() AND payment_status = 'Paid'")->fetchColumn() ?: 0
                ]
            ]
        ]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
