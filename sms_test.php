<?php
// sms_test.php
require_once 'api/config/database.php';
require_once 'api/shared/notification_helper.php';

header('Content-Type: text/plain');

echo "--- BulkSMS BD Test ---\n";

// Check environment variables
$api_key = getenv('BULKSMS_API_KEY');
$senderid = getenv('BULKSMS_SENDER_ID');

echo "API Key: " . ($api_key ? "Found (Ends with " . substr($api_key, -4) . ")" : "NOT FOUND") . "\n";
echo "Sender ID: " . ($senderid ? "Found ($senderid)" : "NOT FOUND") . "\n";

// Test Number (Replace with your number if needed)
$test_number = "8801595378750"; // Example number
$test_message = "Ontomeel POS SMS Test Message. Date: " . date('Y-m-d H:i:s');

if (isset($_GET['number'])) {
    $test_number = $_GET['number'];
}

echo "Target Number: $test_number\n";
echo "Sending SMS...\n";

$result = send_sms_instantly($test_number, $test_message);

echo "Result:\n";
print_r($result);

if ($result['success']) {
    echo "\nSUCCESS! Please check your phone.\n";
} else {
    echo "\nFAILED! See response above.\n";
    echo "Check if CURL is enabled and you have internet access on the server.\n";
}
?>
