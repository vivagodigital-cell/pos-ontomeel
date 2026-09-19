<?php
// api/controllers/CustomSMSController.php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../shared/notification_helper.php';

// Session Security & Check
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        ini_set('session.cookie_secure', 1);
    }
    session_start();
}

if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized access. Please log in.']);
    exit;
}

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

/**
 * Normalize and validate Bangladesh Phone Number
 */
if (!function_exists('normalize_phone')) {
    function normalize_phone($raw) {
        if (empty($raw)) return '';
        // Strip non-digits except leading +
        $clean = preg_replace('/[^\d+]/', '', trim($raw));
        
        // Convert +8801... or 8801... to 01...
        if (strpos($clean, '+880') === 0) {
            $clean = substr($clean, 3);
        } elseif (strpos($clean, '880') === 0) {
            $clean = substr($clean, 2);
        }
        
        // Bangladesh mobile number pattern (11 digits starting with 013-019)
        if (preg_match('/^01[3-9]\d{8}$/', $clean)) {
            return $clean;
        }
        
        return $clean; // return cleaned even if non-standard
    }
}

if (!function_exists('is_valid_bd_phone')) {
    function is_valid_bd_phone($phone) {
        return preg_match('/^01[3-9]\d{8}$/', $phone) === 1;
    }
}

try {
    if ($action === 'getRecipients') {
        $audience = $_GET['audience'] ?? 'pos_buyers'; // pos_buyers | web_buyers | all_buyers | book_buyers | members
        $buyerType = $_GET['buyer_type'] ?? 'all'; // all | member | guest
        $bookId = $_GET['book_id'] ?? '';
        $orderCountMin = (int)($_GET['order_count_min'] ?? 0);
        $paymentStatus = $_GET['payment_status'] ?? 'Paid'; // Paid | all
        $dateFrom = $_GET['date_from'] ?? '';
        $dateTo = $_GET['date_to'] ?? '';
        $search = trim($_GET['search'] ?? '');
        $plan = $_GET['plan'] ?? 'all';

        $contacts = [];
        $uniqueMap = [];

        // 1. ORDER TRANSACTION BUYERS (POS, Website, All Buyers, or Specific Book Buyers)
        if ($audience === 'pos_buyers' || $audience === 'web_buyers' || $audience === 'all_buyers' || $audience === 'book_buyers') {
            $sql = "SELECT 
                        COALESCE(m.phone, o.guest_phone) as phone,
                        COALESCE(NULLIF(TRIM(m.full_name), ''), NULLIF(TRIM(o.guest_name), ''), 'Customer') as buyer_name,
                        CASE 
                            WHEN o.member_id IS NOT NULL THEN 'Member Buyer'
                            ELSE 'Guest Walk-in'
                        END as buyer_type,
                        m.membership_plan,
                        COUNT(DISTINCT o.id) as total_orders,
                        SUM(o.total_amount) as total_spent,
                        MAX(o.order_date) as last_order_date,
                        GROUP_CONCAT(DISTINCT o.invoice_no ORDER BY o.order_date DESC SEPARATOR ', ') as recent_invoices,
                        MAX(CASE 
                            WHEN o.invoice_no LIKE 'OTM-%' OR o.terminal_id = 'Main' OR (o.staff_name IS NOT NULL AND o.staff_name != '') THEN 'POS'
                            ELSE 'Website'
                        END) as primary_channel,
                        GROUP_CONCAT(DISTINCT CASE 
                            WHEN o.invoice_no LIKE 'OTM-%' OR o.terminal_id = 'Main' OR (o.staff_name IS NOT NULL AND o.staff_name != '') THEN 'POS'
                            ELSE 'Website'
                        END) as all_channels
                    FROM orders o
                    LEFT JOIN members m ON o.member_id = m.id
                    LEFT JOIN order_items oi ON o.id = oi.order_id
                    LEFT JOIN books b ON oi.book_id = b.id
                    LEFT JOIN pre_orders po ON oi.preorder_id = po.id
                    WHERE ((m.phone IS NOT NULL AND TRIM(m.phone) != '') OR (o.guest_phone IS NOT NULL AND TRIM(o.guest_phone) != ''))";

            $params = [];

            // Channel Filter
            if ($audience === 'pos_buyers') {
                $sql .= " AND (o.invoice_no LIKE 'OTM-%' OR o.terminal_id = 'Main' OR (o.staff_name IS NOT NULL AND o.staff_name != ''))";
            } elseif ($audience === 'web_buyers') {
                $sql .= " AND (o.invoice_no LIKE 'PRE-%' OR o.notes LIKE '%Pre-order%' OR o.terminal_id = 'Web' OR (o.guest_email IS NOT NULL AND o.guest_email != ''))";
            }

            // Buyer Type Filter (Member vs Guest)
            if ($buyerType === 'member') {
                $sql .= " AND o.member_id IS NOT NULL";
            } elseif ($buyerType === 'guest') {
                $sql .= " AND o.member_id IS NULL";
            }

            // Specific Book Filter
            if (!empty($bookId)) {
                if (strpos($bookId, 'pre_') === 0) {
                    $preId = (int)str_replace('pre_', '', $bookId);
                    $sql .= " AND oi.preorder_id = ?";
                    $params[] = $preId;
                } else {
                    $sql .= " AND oi.book_id = ?";
                    $params[] = (int)$bookId;
                }
            }

            // Payment Status Filter
            if ($paymentStatus === 'Paid') {
                $sql .= " AND o.payment_status = 'Paid'";
            }

            // Date Range
            if (!empty($dateFrom)) {
                $sql .= " AND DATE(o.order_date) >= ?";
                $params[] = $dateFrom;
            }
            if (!empty($dateTo)) {
                $sql .= " AND DATE(o.order_date) <= ?";
                $params[] = $dateTo;
            }

            // Search (Buyer name, phone, invoice, book title)
            if (!empty($search)) {
                $sql .= " AND (m.full_name LIKE ? OR o.guest_name LIKE ? OR m.phone LIKE ? OR o.guest_phone LIKE ? OR o.invoice_no LIKE ? OR b.title LIKE ? OR po.title LIKE ?)";
                $params[] = "%$search%";
                $params[] = "%$search%";
                $params[] = "%$search%";
                $params[] = "%$search%";
                $params[] = "%$search%";
                $params[] = "%$search%";
                $params[] = "%$search%";
            }

            $sql .= " GROUP BY phone, buyer_name";

            // Minimum orders filter (e.g. repeat buyers)
            if ($orderCountMin > 1) {
                $sql .= " HAVING total_orders >= ?";
                $params[] = $orderCountMin;
            }

            $sql .= " ORDER BY last_order_date DESC";

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $buyers = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($buyers as $b) {
                $normPhone = normalize_phone($b['phone']);
                if (empty($normPhone)) continue;

                if (!isset($uniqueMap[$normPhone])) {
                    $channelLabel = strpos($b['all_channels'], 'POS') !== false && strpos($b['all_channels'], 'Website') !== false ? 'POS & Web' : $b['primary_channel'];
                    $uniqueMap[$normPhone] = [
                        'id' => 'buyer_' . md5($normPhone),
                        'db_id' => null,
                        'name' => $b['buyer_name'],
                        'phone' => $normPhone,
                        'raw_phone' => $b['phone'],
                        'category' => $channelLabel . ' Buyer',
                        'sub_category' => $b['total_orders'] . ' Order(s) · ৳' . number_format($b['total_spent'], 0),
                        'plan' => $b['membership_plan'] ?: 'None',
                        'buyer_type' => $b['buyer_type'],
                        'status' => $b['buyer_type'],
                        'total_orders' => (int)$b['total_orders'],
                        'total_spent' => (float)$b['total_spent'],
                        'last_order_date' => $b['last_order_date'],
                        'recent_invoices' => $b['recent_invoices'],
                        'is_valid' => is_valid_bd_phone($normPhone),
                        'source' => 'orders'
                    ];
                }
            }
        }
        // 2. LEGACY/DIRECTORY OPTION: ALL REGISTERED MEMBERS
        elseif ($audience === 'members') {
            $memberSql = "SELECT id, membership_id, full_name, phone, email, membership_plan, is_active, created_at FROM members WHERE phone IS NOT NULL AND TRIM(phone) != ''";
            $params = [];

            if ($plan !== 'all') {
                $memberSql .= " AND membership_plan = ?";
                $params[] = $plan;
            }

            if (!empty($search)) {
                $memberSql .= " AND (full_name LIKE ? OR phone LIKE ? OR membership_id LIKE ?)";
                $params[] = "%$search%";
                $params[] = "%$search%";
                $params[] = "%$search%";
            }

            $memberSql .= " ORDER BY id DESC";
            $stmt = $pdo->prepare($memberSql);
            $stmt->execute($params);
            $members = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($members as $m) {
                $normPhone = normalize_phone($m['phone']);
                if (empty($normPhone)) continue;

                if (!isset($uniqueMap[$normPhone])) {
                    $uniqueMap[$normPhone] = [
                        'id' => 'mem_' . $m['id'],
                        'db_id' => $m['id'],
                        'name' => $m['full_name'] ?: 'Member #' . $m['membership_id'],
                        'phone' => $normPhone,
                        'raw_phone' => $m['phone'],
                        'category' => 'Member Directory',
                        'sub_category' => ($m['membership_plan'] ?: 'None') . ' Plan',
                        'plan' => $m['membership_plan'] ?: 'None',
                        'buyer_type' => 'Registered Member',
                        'status' => $m['is_active'] ? 'Active' : 'Inactive',
                        'total_orders' => 0,
                        'total_spent' => 0,
                        'last_order_date' => $m['created_at'],
                        'recent_invoices' => '',
                        'is_valid' => is_valid_bd_phone($normPhone),
                        'source' => 'members'
                    ];
                }
            }
        }

        $recipients = array_values($uniqueMap);
        $validCount = 0;
        foreach ($recipients as $r) {
            if ($r['is_valid']) $validCount++;
        }

        echo json_encode([
            'success' => true,
            'total' => count($recipients),
            'valid_count' => $validCount,
            'recipients' => $recipients
        ]);
    }
    elseif ($action === 'getBooksList') {
        // Fetch books that have transaction history in orders
        $stmt = $pdo->query("SELECT b.id, b.title, b.author, COUNT(DISTINCT oi.order_id) as buyers_count
                             FROM books b
                             JOIN order_items oi ON b.id = oi.book_id
                             GROUP BY b.id, b.title, b.author
                             ORDER BY buyers_count DESC, b.title ASC");
        $books = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Also fetch pre-orders with orders
        $stmtPo = $pdo->query("SELECT po.id, po.title, po.author, COUNT(DISTINCT oi.order_id) as buyers_count
                               FROM pre_orders po
                               JOIN order_items oi ON po.id = oi.preorder_id
                               GROUP BY po.id, po.title, po.author
                               ORDER BY buyers_count DESC");
        $preorders = $stmtPo->fetchAll(PDO::FETCH_ASSOC);

        $list = [];
        foreach ($books as $bk) {
            $list[] = [
                'id' => $bk['id'],
                'title' => $bk['title'] . ($bk['author'] ? ' - ' . $bk['author'] : '') . ' (' . $bk['buyers_count'] . ' buyers)',
                'type' => 'book'
            ];
        }
        foreach ($preorders as $po) {
            $list[] = [
                'id' => 'pre_' . $po['id'],
                'title' => '[Pre-Order] ' . $po['title'] . ' (' . $po['buyers_count'] . ' buyers)',
                'type' => 'preorder'
            ];
        }

        echo json_encode(['success' => true, 'books' => $list]);
    }
    elseif ($action === 'getBalance') {
        $api_key = getenv('BULKSMS_API_KEY') ?: ($_ENV['BULKSMS_API_KEY'] ?? '');
        if (empty($api_key)) {
            echo json_encode(['success' => false, 'error' => 'BulkSMS API Key not configured in .env']);
            exit;
        }

        $url = "http://bulksmsbd.net/api/getBalanceApi?api_key=" . urlencode($api_key);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $res = json_decode($response, true);
        if ($res && isset($res['balance'])) {
            echo json_encode([
                'success' => true,
                'balance' => $res['balance'],
                'raw' => $res
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'error' => 'Failed to fetch balance from BulkSMS BD',
                'raw_response' => $response
            ]);
        }
    }
    elseif ($action === 'sendTest') {
        $data = json_decode(file_get_contents('php://input'), true);
        $phone = normalize_phone($data['phone'] ?? '');
        $message = trim($data['message'] ?? '');

        if (empty($phone) || !is_valid_bd_phone($phone)) {
            echo json_encode(['success' => false, 'error' => 'Please provide a valid 11-digit phone number.']);
            exit;
        }

        if (empty($message)) {
            echo json_encode(['success' => false, 'error' => 'Message content cannot be empty.']);
            exit;
        }

        $testName = $data['name'] ?? 'Tester';
        $finalMessage = str_ireplace('{name}', $testName, $message);

        $result = send_sms_instantly($phone, $finalMessage);
        echo json_encode($result);
    }
    elseif ($action === 'sendBulk') {
        $data = json_decode(file_get_contents('php://input'), true);
        $recipients = $data['recipients'] ?? [];
        $messageTemplate = trim($data['message'] ?? '');

        if (empty($recipients) || !is_array($recipients)) {
            echo json_encode(['success' => false, 'error' => 'No recipients selected.']);
            exit;
        }

        if (empty($messageTemplate)) {
            echo json_encode(['success' => false, 'error' => 'SMS message template is empty.']);
            exit;
        }

        $sentCount = 0;
        $failedCount = 0;
        $logs = [];

        foreach ($recipients as $recipient) {
            $phone = normalize_phone($recipient['phone'] ?? '');
            $name = trim($recipient['name'] ?? '');
            
            if (empty($phone) || !is_valid_bd_phone($phone)) {
                $failedCount++;
                $logs[] = [
                    'phone' => $phone ?: ($recipient['phone'] ?? 'Unknown'),
                    'name' => $name,
                    'status' => 'failed',
                    'reason' => 'Invalid phone number'
                ];
                continue;
            }

            $displayName = !empty($name) && $name !== 'Customer' && $name !== 'Guest Buyer' ? $name : 'Valued Customer';
            $personalizedMessage = str_ireplace('{name}', $displayName, $messageTemplate);

            $res = send_sms_instantly($phone, $personalizedMessage);
            if ($res && !empty($res['success'])) {
                $sentCount++;
                $logs[] = [
                    'phone' => $phone,
                    'name' => $name,
                    'status' => 'sent'
                ];
            } else {
                $failedCount++;
                $logs[] = [
                    'phone' => $phone,
                    'name' => $name,
                    'status' => 'failed',
                    'reason' => $res['message'] ?? ($res['response'] ?? 'Gateway rejected')
                ];
            }

            // Micro delay to prevent gateway throttling
            usleep(50000); // 50ms
        }

        echo json_encode([
            'success' => true,
            'total' => count($recipients),
            'sent_count' => $sentCount,
            'failed_count' => $failedCount,
            'logs' => $logs
        ]);
    }
    else {
        echo json_encode(['success' => false, 'error' => 'Invalid action.']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
