<?php
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

try {
    if ($action === 'getItems') {
        // Fetch all items from books table (including non-book items via item_type)
        $stmt = $pdo->query("SELECT id, title, item_type, stock_qty, sell_price, isbn, author, cover_image, purchase_price, supplier_name FROM books WHERE is_active = 1 ORDER BY id DESC");
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
        $cost  = (float)($data['cost'] ?? 0.00);

        if (empty($title)) {
            echo json_encode(["success" => false, "error" => "Item name is required!"]);
            exit;
        }

        // Insert into books table with item_type
        $stmt = $pdo->prepare("INSERT INTO books (title, item_type, stock_qty, sell_price, isbn, purchase_price) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$title, $type, $stock, $price, $sku, $cost]);
        echo json_encode(["success" => true, "message" => "Item added!", "id" => $pdo->lastInsertId()]);
    }

    elseif ($action === 'updateItem') {
        $data  = json_decode(file_get_contents('php://input'), true);
        $id    = (int)($data['id'] ?? 0);
        $title = trim($data['title'] ?? '');
        $type  = $data['item_type'] ?? 'General';
        $stock = (int)($data['stock'] ?? 0);
        $price = (float)($data['price'] ?? 0.00);
        $sku   = $data['barcode'] ?? null;
        $cost  = (float)($data['cost'] ?? 0.00);

        if (!$id || empty($title)) {
            echo json_encode(["success" => false, "error" => "Invalid data."]);
            exit;
        }

        // Update in books table
        $stmt = $pdo->prepare("UPDATE books SET title=?, item_type=?, stock_qty=?, sell_price=?, isbn=?, purchase_price=? WHERE id=?");
        $stmt->execute([$title, $type, $stock, $price, $sku, $cost, $id]);
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
        $stmt = $pdo->prepare("UPDATE books SET is_active = 0 WHERE id = ?");
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

        // Restock in books table
        $stmt = $pdo->prepare("UPDATE books SET stock_qty = stock_qty + ? WHERE id = ? AND is_active = 1");
        $stmt->execute([$qty, $id]);
        
        // Verify update happened
        if ($stmt->rowCount() === 0) {
            echo json_encode(["success" => false, "error" => "Item not found or already inactive."]);
            exit;
        }
        echo json_encode(["success" => true, "message" => "Stock updated!"]);
    }

    else {
        echo json_encode(["success" => false, "error" => "Invalid action."]);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
