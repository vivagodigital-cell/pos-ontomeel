<?php
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

try {
    if ($action === 'getItems') {
        // Fetch from new inventory_items table (not books table)
        $stmt = $pdo->query("SELECT id, item_name as title, item_type, quantity as stock_qty, sell_price, barcode as isbn FROM inventory_items WHERE is_active = 1 ORDER BY id DESC");
        $items = $stmt->fetchAll();
        echo json_encode(["success" => true, "items" => $items]);
    }

    elseif ($action === 'addItem') {
        $data  = json_decode(file_get_contents('php://input'), true);
        $title = trim($data['title'] ?? '');
        $type  = $data['item_type'] ?? 'General';
        $stock = (int)($data['stock'] ?? 0);
        $price = (float)($data['price'] ?? 0.00);
        $sku   = $data['barcode'] ?? null;

        if (empty($title)) {
            echo json_encode(["success" => false, "error" => "Item name is required!"]);
            exit;
        }

        // Insert into new inventory_items table
        $stmt = $pdo->prepare("INSERT INTO inventory_items (item_name, item_type, quantity, sell_price, barcode) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$title, $type, $stock, $price, $sku]);
        echo json_encode(["success" => true, "message" => "Item added!"]);
    }

    elseif ($action === 'updateItem') {
        $data  = json_decode(file_get_contents('php://input'), true);
        $id    = (int)($data['id'] ?? 0);
        $title = trim($data['title'] ?? '');
        $type  = $data['item_type'] ?? 'General';
        $stock = (int)($data['stock'] ?? 0);
        $price = (float)($data['price'] ?? 0.00);
        $sku   = $data['barcode'] ?? null;

        if (!$id || empty($title)) {
            echo json_encode(["success" => false, "error" => "Invalid data."]);
            exit;
        }

        // Update in new inventory_items table
        $stmt = $pdo->prepare("UPDATE inventory_items SET item_name=?, item_type=?, quantity=?, sell_price=?, barcode=? WHERE id=?");
        $stmt->execute([$title, $type, $stock, $price, $sku, $id]);
        echo json_encode(["success" => true, "message" => "Item updated!"]);
    }

    elseif ($action === 'deleteItem') {
        $data = json_decode(file_get_contents('php://input'), true);
        $id   = (int)($data['id'] ?? 0);

        if (!$id) {
            echo json_encode(["success" => false, "error" => "Invalid ID."]);
            exit;
        }

        // Soft-delete: set is_active = 0 
        $stmt = $pdo->prepare("UPDATE inventory_items SET is_active = 0 WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(["success" => true, "message" => "Item deleted."]);
    }

    elseif ($action === 'restockItem') {
        $data = json_decode(file_get_contents('php://input'), true);
        $id   = (int)($data['id']  ?? 0);
        $qty  = (int)($data['qty'] ?? 0);

        if (!$id || $qty < 1) {
            echo json_encode(["success" => false, "error" => "Invalid restock data."]);
            exit;
        }

        // Restock in new inventory_items table
        $stmt = $pdo->prepare("UPDATE inventory_items SET quantity = quantity + ? WHERE id = ?");
        $stmt->execute([$qty, $id]);
        echo json_encode(["success" => true, "message" => "Stock updated!"]);
    }

    else {
        echo json_encode(["success" => false, "error" => "Invalid action."]);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
