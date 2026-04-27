<?php
// sms_test.php
require_once 'api/config/database.php';
require_once 'api/shared/notification_helper.php';

header('Content-Type: text/plain');

echo "--- Ontomeel BulkSMS BD Test ---\n";

// Check environment variables
$api_key = getenv('BULKSMS_API_KEY') ?: ($_ENV['BULKSMS_API_KEY'] ?? ($_SERVER['BULKSMS_API_KEY'] ?? ''));
$senderid = getenv('BULKSMS_SENDER_ID') ?: ($_ENV['BULKSMS_SENDER_ID'] ?? ($_SERVER['BULKSMS_SENDER_ID'] ?? ''));

$env_path = realpath(__DIR__ . '/.env');
echo "Env File Path: " . ($env_path ?: "NOT FOUND at " . __DIR__ . '/.env') . "\n";

if ($env_path) {
    echo "--- Keys found in .env ---\n";
    $lines = file($env_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line) || strpos($line, '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            echo "- " . trim($name) . " (Length: " . strlen(trim($value)) . ")\n";
        }
    }
    echo "--------------------------\n";
}

echo "API Key: " . ($api_key ? "Found (Ends with " . substr($api_key, -4) . ")" : "NOT FOUND") . "\n";
echo "Sender ID: " . ($senderid ? "Found ($senderid)" : "NOT FOUND") . "\n";

// Test Number (Replace with your number if needed)
$test_number = "8801595378750"; 
if (isset($_GET['number'])) {
    $test_number = $_GET['number'];
}

$test_link = "https://ontomeel.com/OTM-TEST-LINK";
$test_message = "Thanks for your order. From Ontomeel. Click this link to see your invoice: $test_link";

echo "Target Number: $test_number\n";
echo "Message: $test_message\n";
echo "Sending SMS...\n";

$result = send_sms_instantly($test_number, $test_message);

echo "\n--- API Response ---\n";
if (is_array($result['response'])) {
    print_r($result['response']);
} else {
    echo $result['response'];
}

echo "\n-------------------\n";

if ($result['success']) {
    echo "\nSUCCESS! The message was accepted by the provider.\n";
} else {
    echo "\nFAILED! Please check the API response above.\n";
    echo "Common issues: 1003 (Missing params), 1002 (Invalid API key), 1005 (Invalid Number).\n";
}
?>
