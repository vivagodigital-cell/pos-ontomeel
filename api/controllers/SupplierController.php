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

        if (empty($name))
            throw new Exception("Supplier name is required.");

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

        if (empty($lib) || empty($title) || empty($due))
            throw new Exception("Required fields missing.");

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
        if (empty($name))
            throw new Exception("Supplier name is required.");

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

        if (!$supplierId || empty($title) || $qty <= 0)
            throw new Exception("Required data missing.");

        // 1. Get Supplier Name
        $stmt = $pdo->prepare("SELECT name FROM suppliers WHERE id = ?");
        $stmt->execute([$supplierId]);
        $supplier = $stmt->fetch();
        if (!$supplier)
            throw new Exception("Supplier not found.");

        $pdo->beginTransaction();
        try {
            // 2. Check if book exists (by title and author)
            $stmt = $pdo->prepare("SELECT id, stock_qty FROM books WHERE title = ? AND author = ?");
            $stmt->execute([$title, $author]);
            $existing = $stmt->fetch();

            if ($existing) {
                // Update existing
                $newQty = $existing['stock_qty'] + $qty;
                $stmt = $pdo->prepare("UPDATE books SET stock_qty = ?, purchase_price = ?, sell_price = ?, supplier_name = ? WHERE id = ?");
                $stmt->execute([$newQty, $cost, $sale, $supplier['name'], $existing['id']]);
            }
            else {
                // Insert new book into books table (item_type will default to 'Book' or can be set)
                $stmt = $pdo->prepare("INSERT INTO books (title, author, stock_qty, purchase_price, sell_price, supplier_name, item_type) VALUES (?, ?, ?, ?, ?, ?, 'Book')");
                $stmt->execute([$title, $author, $qty, $cost, $sale, $supplier['name']]);
            }

            // 3. Update Supplier Due
            $totalCost = $cost * $qty;
            $stmt = $pdo->prepare("UPDATE suppliers SET total_due = total_due + ? WHERE id = ?");
            $stmt->execute([$totalCost, $supplierId]);

            $pdo->commit();
            echo json_encode(['success' => true]);
        }
        catch (Exception $e) {
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

        if (!$supplierId || $amount <= 0)
            throw new Exception("Invalid payment data.");

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
        }
        catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
    elseif ($action === 'setupPurchaseTable') {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `purchase_records` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `supplier_id` INT DEFAULT NULL,
            `supplier_name` VARCHAR(200) DEFAULT NULL,
            `item_name` VARCHAR(255) NOT NULL,
            `item_type` VARCHAR(50) DEFAULT 'General',
            `quantity` INT DEFAULT 1,
            `unit_cost` DECIMAL(10,2) DEFAULT 0.00,
            `total_cost` DECIMAL(10,2) DEFAULT 0.00,
            `paid_amount` DECIMAL(10,2) DEFAULT 0.00,
            `payment_status` ENUM('Unpaid','Partial','Paid') DEFAULT 'Unpaid',
            `payment_method` VARCHAR(50) DEFAULT 'Cash',
            `note` TEXT DEFAULT NULL,
            `purchase_date` DATE NOT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        // Create separate inventory_items table for non-book items
        $pdo->exec("CREATE TABLE IF NOT EXISTS `inventory_items` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `item_name` VARCHAR(255) NOT NULL,
            `item_type` VARCHAR(50) DEFAULT 'General',
            `quantity` INT DEFAULT 0,
            `unit_cost` DECIMAL(10,2) DEFAULT 0.00,
            `sell_price` DECIMAL(10,2) DEFAULT 0.00,
            `supplier_id` INT DEFAULT NULL,
            `supplier_name` VARCHAR(200) DEFAULT NULL,
            `barcode` VARCHAR(100) DEFAULT NULL,
            `is_active` TINYINT(1) DEFAULT 1,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        echo json_encode(['success' => true, 'message' => 'Tables ready']);
    }
    elseif ($action === 'savePurchaseRecord') {
        $data = json_decode(file_get_contents('php://input'), true);

        $supplierIdRaw = $data['supplier_id'] ?? null;
        $supplierId = ($supplierIdRaw && $supplierIdRaw !== '') ? intval($supplierIdRaw) : null;
        $supplierName = $data['supplier_name'] ?? null;
        $itemName = trim($data['item_name'] ?? '');
        $itemType = $data['item_type'] ?? 'General';
        $qty = max(1, intval($data['quantity'] ?? 1));
        $unitCost = floatval($data['unit_cost'] ?? 0);
        $paidAmount = floatval($data['paid_amount'] ?? 0);
        $payMethod = $data['payment_method'] ?? 'Cash';
        $note = $data['note'] ?? '';
        $purchaseDate = $data['purchase_date'] ?? date('Y-m-d');

        if (empty($itemName))
            throw new Exception("Item name is required.");

        $totalCost = $unitCost * $qty;

        // Determine payment status
        $payStatus = 'Unpaid';
        if ($paidAmount >= $totalCost)
            $payStatus = 'Paid';
        elseif ($paidAmount > 0)
            $payStatus = 'Partial';

        // If supplier linked by id, get name
        if ($supplierId && !$supplierName) {
            $s = $pdo->prepare("SELECT name FROM suppliers WHERE id = ?");
            $s->execute([$supplierId]);
            $row = $s->fetch();
            $supplierName = $row ? $row['name'] : null;
        }

        $stmt = $pdo->prepare("INSERT INTO purchase_records 
            (supplier_id, supplier_name, item_name, item_type, quantity, unit_cost, total_cost, paid_amount, payment_status, payment_method, note, purchase_date)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->execute([$supplierId, $supplierName, $itemName, $itemType, $qty, $unitCost, $totalCost, $paidAmount, $payStatus, $payMethod, $note, $purchaseDate]);

        // If there is an outstanding balance, add to supplier due
        if ($supplierId && $totalCost > $paidAmount) {
            $due = $totalCost - $paidAmount;
            $pdo->prepare("UPDATE suppliers SET total_due = total_due + ? WHERE id = ?")->execute([$due, $supplierId]);
        }

        // Add/update in books table for unified inventory
        // Check if item exists in books
        $check = $pdo->prepare("SELECT id, stock_qty FROM books WHERE title = ? AND (item_type = ? OR item_type = 'General')");
        $check->execute([$itemName, $itemType]);
        $existing = $check->fetch();

        if ($existing) {
            // Update existing inventory in books table
            $newQty = $existing['stock_qty'] + $qty;
            $upd = $pdo->prepare("UPDATE books SET stock_qty = ?, purchase_price = ?, sell_price = ?, supplier_name = ?, item_type = ? WHERE id = ?");
            $upd->execute([$newQty, $unitCost, $unitCost, $supplierName, $itemType, $existing['id']]);
        }
        else {
            // Insert new item into books table (only using supplier_name, not supplier_id)
            $ins = $pdo->prepare("INSERT INTO books (title, item_type, stock_qty, purchase_price, sell_price, supplier_name) VALUES (?, ?, ?, ?, ?, ?)");
            $ins->execute([$itemName, $itemType, $qty, $unitCost, $unitCost, $supplierName]);
        }

        echo json_encode(['success' => true, 'message' => 'Purchase recorded!']);
    }
    elseif ($action === 'listPurchaseRecords') {
        $supplierId = $_GET['supplier_id'] ?? null;
        if ($supplierId) {
            $stmt = $pdo->prepare("SELECT * FROM purchase_records WHERE supplier_id = ? ORDER BY purchase_date DESC, id DESC");
            $stmt->execute([$supplierId]);
        }
        else {
            $stmt = $pdo->query("SELECT * FROM purchase_records ORDER BY purchase_date DESC, id DESC");
        }
        echo json_encode(['success' => true, 'records' => $stmt->fetchAll()]);
    }
}
catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
