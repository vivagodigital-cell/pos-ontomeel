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
        $audience = $_GET['audience'] ?? 'all'; // all | members | guests
        $plan = $_GET['plan'] ?? 'all';
        $memberStatus = $_GET['member_status'] ?? 'all';
        $dateFrom = $_GET['date_from'] ?? '';
        $dateTo = $_GET['date_to'] ?? '';
        $search = trim($_GET['search'] ?? '');

        $contacts = [];
        $uniqueMap = [];

        // 1. Fetch Members if requested
        if ($audience === 'all' || $audience === 'members') {
            $memberSql = "SELECT id, membership_id, full_name, phone, email, membership_plan, is_active, created_at FROM members WHERE phone IS NOT NULL AND TRIM(phone) != ''";
            $params = [];

            if ($plan !== 'all') {
                $memberSql .= " AND membership_plan = ?";
                $params[] = $plan;
            }

            if ($memberStatus !== 'all') {
                $memberSql .= " AND is_active = ?";
                $params[] = (int)$memberStatus;
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
                        'category' => 'Member',
                        'sub_category' => $m['membership_plan'] . ' Plan',
                        'plan' => $m['membership_plan'],
                        'status' => $m['is_active'] ? 'Active' : 'Inactive',
                        'is_valid' => is_valid_bd_phone($normPhone),
                        'source' => 'members'
                    ];
                }
            }
        }

        // 2. Fetch Guest Buyers if requested
        if ($audience === 'all' || $audience === 'guests') {
            $guestSql = "SELECT 
                            guest_name, 
                            guest_phone, 
                            COUNT(id) as total_orders, 
                            SUM(total_amount) as total_spent, 
                            MAX(order_date) as last_order_date
                         FROM orders 
                         WHERE guest_phone IS NOT NULL AND TRIM(guest_phone) != ''";
            $params = [];

            if (!empty($dateFrom)) {
                $guestSql .= " AND DATE(order_date) >= ?";
                $params[] = $dateFrom;
            }

            if (!empty($dateTo)) {
                $guestSql .= " AND DATE(order_date) <= ?";
                $params[] = $dateTo;
            }

            if (!empty($search)) {
                $guestSql .= " AND (guest_name LIKE ? OR guest_phone LIKE ?)";
                $params[] = "%$search%";
                $params[] = "%$search%";
            }

            $guestSql .= " GROUP BY guest_phone, guest_name ORDER BY last_order_date DESC";
            $stmt = $pdo->prepare($guestSql);
            $stmt->execute($params);
            $guests = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($guests as $g) {
                $normPhone = normalize_phone($g['guest_phone']);
                if (empty($normPhone)) continue;

                // If already exists as member, keep member identity or append guest buyer info
                if (isset($uniqueMap[$normPhone])) {
                    if ($uniqueMap[$normPhone]['source'] === 'guests') {
                        // Already in unique map
                        continue;
                    } else {
                        // It's a member who also ordered as guest
                        $uniqueMap[$normPhone]['sub_category'] .= ' & Buyer';
                    }
                } else {
                    $uniqueMap[$normPhone] = [
                        'id' => 'guest_' . md5($normPhone),
                        'db_id' => null,
                        'name' => $g['guest_name'] ?: 'Guest Buyer',
                        'phone' => $normPhone,
                        'raw_phone' => $g['guest_phone'],
                        'category' => 'Guest Buyer',
                        'sub_category' => $g['total_orders'] . ' Order(s)',
                        'plan' => 'None',
                        'status' => 'Buyer',
                        'is_valid' => is_valid_bd_phone($normPhone),
                        'source' => 'guests',
                        'total_spent' => (float)$g['total_spent']
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

        // Replace {name} placeholder with 'Valued Customer' or Test Name
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

        // Send to each recipient with personalized {name}
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

            $displayName = !empty($name) && $name !== 'Guest Buyer' ? $name : 'Customer';
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

            // Micro delay to prevent gateway burst throttling
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
