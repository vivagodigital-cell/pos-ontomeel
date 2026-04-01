<?php
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

/**
 * Product Import Controller
 * Allows bulk import of products/books from CSV.
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

// Clean BOM and whitespace from headers
if ($header) {
    $header[0] = preg_replace('/^[\xEF\xBB\xBF\xFF\xFE]*/', '', $header[0]);
    $header = array_map('trim', $header);
}

$headerMap = array_flip($header ?: []);

if (!isset($headerMap['name'])) {
    echo json_encode(['success' => false, 'message' => "Missing required CSV header: 'name'. Found headers: " . implode(', ', $header ?: [])]);
    fclose($handle);
    exit;
}

$count = 0;
$skipped = 0;
$errors = [];
$rowIndex = 1; // Starting after header

// Increase time limit for large files
set_time_limit(600);

try {
    // Prepared statements for both tables
    $stmtBook = $pdo->prepare("INSERT INTO books (
        title, isbn, sell_price, purchase_price, stock_qty, author, min_stock_level, item_type, genre, is_active
    ) VALUES (?, ?, ?, ?, ?, ?, ?, 'Books', ?, 1)");

    $stmtItem = $pdo->prepare("INSERT INTO inventory_items (
        item_name, barcode, sell_price, unit_cost, quantity, item_type, is_active
    ) VALUES (?, ?, ?, ?, ?, ?, 1)");

    while (($row = fgetcsv($handle)) !== false) {
        $rowIndex++;
        $name = trim($row[$headerMap['name']] ?? '');
        
        if (empty($name)) {
            $errors[] = "Row $rowIndex: Missing product name";
            continue;
        }

        // Optional fields with defaults
        $category = isset($headerMap['category']) ? trim($row[$headerMap['category']] ?? '') : 'General';
        $barcode = isset($headerMap['barcode']) ? trim($row[$headerMap['barcode']] ?? '') : '';
        $sellPrice = isset($headerMap['selling_price']) ? (float)($row[$headerMap['selling_price']] ?? 0) : 0.00;
        $purchasePrice = isset($headerMap['purchase_price']) ? (float)($row[$headerMap['purchase_price']] ?? 0) : 0.00;
        $stockQty = isset($headerMap['opening_stock_qty']) ? (int)($row[$headerMap['opening_stock_qty']] ?? 0) : 0;
        
        $alertQty = isset($headerMap['alert_quantity']) ? (int)($row[$headerMap['alert_quantity']] ?? 0) : 0;
        $author = isset($headerMap['author']) ? trim($row[$headerMap['author']] ?? '') : '';
        $subcategory = isset($headerMap['subcategory']) ? trim($row[$headerMap['subcategory']] ?? '') : '';

        try {
            $isBook = (strtolower($category) === 'book' || strtolower($category) === 'books');
            
            if ($isBook) {
                $stmtBook->execute([$name, $barcode, $sellPrice, $purchasePrice, $stockQty, $author, $alertQty, $subcategory]);
            } else {
                $stmtItem->execute([$name, $barcode, $sellPrice, $purchasePrice, $stockQty, $category]);
            }
            $count++;
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $skipped++;
            } else {
                $errors[] = "Row $rowIndex ($name): " . $e->getMessage();
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
