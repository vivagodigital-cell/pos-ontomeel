<?php
// api/controllers/SupplierController.php
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

try {
    if ($action === 'listSuppliers') {
        $stmt = $pdo->query("SELECT * FROM suppliers ORDER BY name ASC");
        echo json_encode(['success' => true, 'suppliers' => $stmt->fetchAll()]);
    }
    elseif ($action === 'saveSupplier') {
        $data = json_decode(file_get_contents('php://input'), true);
        $name = $data['name'] ?? '';
        $contact = $data['contact'] ?? '';
        $email = $data['email'] ?? '';
        $address = $data['address'] ?? '';

        if (empty($name)) throw new Exception("Supplier name is required.");

        $stmt = $pdo->prepare("INSERT INTO suppliers (name, contact, email, address) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $contact, $email, $address]);
        echo json_encode(['success' => true]);
    }
    elseif ($action === 'listExternalBorrows') {
        $stmt = $pdo->query("SELECT * FROM external_borrows ORDER BY due_date ASC");
        echo json_encode(['success' => true, 'borrows' => $stmt->fetchAll()]);
    }
    elseif ($action === 'saveExternalBorrow') {
        $data = json_decode(file_get_contents('php://input'), true);
        $lib = $data['library_name'] ?? '';
        $title = $data['book_title'] ?? '';
        $author = $data['author'] ?? '';
        $borrow = $data['borrow_date'] ?? date('Y-m-d');
        $due = $data['due_date'] ?? '';

        if (empty($lib) || empty($title) || empty($due)) throw new Exception("Required fields missing.");

        $stmt = $pdo->prepare("INSERT INTO external_borrows (library_name, book_title, author, borrow_date, due_date) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$lib, $title, $author, $borrow, $due]);
        echo json_encode(['success' => true]);
    }
    elseif ($action === 'getSupplierBooks') {
        // Books grouped by supplier name from the books table
        $stmt = $pdo->query("SELECT supplier_name, COUNT(*) as book_count, SUM(purchase_price * stock_qty) as inventory_value FROM books WHERE supplier_name IS NOT NULL AND supplier_name != '' GROUP BY supplier_name");
        echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
    }
    elseif ($action === 'getBooksBySupplier') {
        $name = $_GET['name'] ?? '';
        if (empty($name)) throw new Exception("Supplier name is required.");
        
        $stmt = $pdo->prepare("SELECT title, author, purchase_price, stock_qty, (purchase_price * stock_qty) as total_value FROM books WHERE supplier_name = ? ORDER BY title ASC");
        $stmt->execute([$name]);
        echo json_encode(['success' => true, 'books' => $stmt->fetchAll()]);
    }
    elseif ($action === 'receiveInventory') {
        $data = json_decode(file_get_contents('php://input'), true);
        $supplierId = $data['supplierId'] ?? null;
        $title = $data['title'] ?? '';
        $author = $data['author'] ?? '';
        $qty = intval($data['qty'] ?? 0);
        $cost = floatval($data['cost'] ?? 0);
        $sale = floatval($data['sale'] ?? 0);

        if (!$supplierId || empty($title) || $qty <= 0) throw new Exception("Required data missing.");

        // 1. Get Supplier Name
        $stmt = $pdo->prepare("SELECT name FROM suppliers WHERE id = ?");
        $stmt->execute([$supplierId]);
        $supplier = $stmt->fetch();
        if (!$supplier) throw new Exception("Supplier not found.");

        $pdo->beginTransaction();
        try {
            // 2. Check if book exists
            $stmt = $pdo->prepare("SELECT id, stock_qty FROM books WHERE title = ? AND author = ?");
            $stmt->execute([$title, $author]);
            $existing = $stmt->fetch();

            if ($existing) {
                // Update existing
                $newQty = $existing['stock_qty'] + $qty;
                $stmt = $pdo->prepare("UPDATE books SET stock_qty = ?, purchase_price = ?, sell_price = ?, supplier_name = ? WHERE id = ?");
                $stmt->execute([$newQty, $cost, $sale, $supplier['name'], $existing['id']]);
            } else {
                // Insert new
                $stmt = $pdo->prepare("INSERT INTO books (title, author, stock_qty, purchase_price, sell_price, supplier_name) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$title, $author, $qty, $cost, $sale, $supplier['name']]);
            }

            // 3. Update Supplier Due
            $totalCost = $cost * $qty;
            $stmt = $pdo->prepare("UPDATE suppliers SET total_due = total_due + ? WHERE id = ?");
            $stmt->execute([$totalCost, $supplierId]);

            $pdo->commit();
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
    elseif ($action === 'recordPayment') {
        $data = json_decode(file_get_contents('php://input'), true);
        $supplierId = $data['supplierId'] ?? null;
        $amount = floatval($data['amount'] ?? 0);
        $method = $data['method'] ?? 'Cash';
        $notes = $data['notes'] ?? '';

        if (!$supplierId || $amount <= 0) throw new Exception("Invalid payment data.");

        $pdo->beginTransaction();
        try {
            // 1. Log Payment
            $stmt = $pdo->prepare("INSERT INTO supplier_payments (supplier_id, amount, method, notes) VALUES (?, ?, ?, ?)");
            $stmt->execute([$supplierId, $amount, $method, $notes]);

            // 2. Reduce Due
            $stmt = $pdo->prepare("UPDATE suppliers SET total_due = total_due - ? WHERE id = ?");
            $stmt->execute([$amount, $supplierId]);

            $pdo->commit();
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
