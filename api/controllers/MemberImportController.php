<?php
// api/controllers/MemberImportController.php
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

/**
 * Member Import Controller
 * Allows bulk import of members from CSV.
 * Columns: full_name, phone
 * Generates OM-YYYY-XXXX membership IDs.
 */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Only POST allowed']);
    exit;
}

if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'No file uploaded or upload error']);
    exit;
}

$file = $_FILES['csv_file']['tmp_name'];
$handle = fopen($file, 'r');

if (!$handle) {
    echo json_encode(['success' => false, 'message' => 'Failed to open file']);
    exit;
}

// Read header
$header = fgetcsv($handle);

$count = 0;
$skipped = 0;
$errors = [];
$passwordHash = password_hash('123456', PASSWORD_DEFAULT);
$year = date('Y');

// Increase time limit for large files
set_time_limit(600);

try {
    $stmt = $pdo->prepare("INSERT INTO members (membership_id, full_name, email, phone, password, membership_plan, acc_balance, is_active) VALUES (?, ?, ?, ?, ?, 'None', 0.00, 1)");

    while (($row = fgetcsv($handle)) !== false) {
        // Skip empty or malformed rows
        if (!isset($row[1]) || empty(trim($row[0])) || empty(trim($row[1]))) {
            continue;
        }

        $fullName = trim($row[0]);
        $phone = trim($row[1]);

        // Basic phone cleaning (keep digits)
        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
        if (empty($cleanPhone))
            continue;

        // Generate Membership ID: OM-2026-F3C2
        $randomHex = strtoupper(substr(md5(uniqid($cleanPhone, true)), 0, 4));
        $membershipId = "OM-{$year}-{$randomHex}";

        // Keep email blank if data has no email
        // If your database email field is NOT NULL, you may need to 
        // alter it to be NULL to allow multiple empty emails.
        $email = isset($row[2]) && !empty(trim($row[2])) ? trim($row[2]) : null;

        try {
            $stmt->execute([
                $membershipId,
                $fullName,
                $email,
                $cleanPhone,
                $passwordHash
            ]);
            $count++;
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) { // Duplicate entry
                $skipped++;
            } else {
                $errors[] = "Row for $fullName: " . $e->getMessage();
            }
        }
    }

    echo json_encode([
        'success' => true,
        'count' => $count,
        'skipped' => $skipped,
        'errors' => $errors
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} finally {
    fclose($handle);
}
