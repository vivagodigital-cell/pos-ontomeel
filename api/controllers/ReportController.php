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
        // Website orders have an invoice_no prefix like 'WEB-' or are identified by trx_id presence
        // Assuming guest_name presence or specific invoice prefix might help, but let's use a common logic:
        // If guest_email is present, likely Website. If it's a member_id without guest info, likely POS.
        
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
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
