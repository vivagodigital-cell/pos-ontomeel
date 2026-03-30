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
        $category = $data['category'] ?? 'General';
        $purchaseDate = $data['purchase_date'] ?? date('Y-m-d');
        $payMethod = $data['payment_method'] ?? 'Cash';
        $paidAmount = floatval($data['paid_amount'] ?? 0);
        $note = $data['note'] ?? '';
        $items = $data['items'] ?? [];

        if (empty($items))
            throw new Exception("Please add at least one item.");

        $pdo->beginTransaction();
        try {
            // 1. Calculate Grand Total
            $grandTotal = 0;
            foreach ($items as $item) {
                $grandTotal += (floatval($item['unit_cost']) * intval($item['quantity']));
            }

            // 2. Determine Payment Status
            $payStatus = 'Unpaid';
            if ($paidAmount >= $grandTotal) $payStatus = 'Paid';
            elseif ($paidAmount > 0) $payStatus = 'Partial';

            // 3. Resolve Supplier Name
            $supplierName = null;
            if ($supplierId) {
                $s = $pdo->prepare("SELECT name FROM suppliers WHERE id = ?");
                $s->execute([$supplierId]);
                $row = $s->fetch();
                $supplierName = $row ? $row['name'] : null;
            }

            // 4. Insert Master Purchase Record
            $stmt = $pdo->prepare("INSERT INTO purchases 
                (supplier_id, supplier_name, category, total_amount, paid_amount, payment_method, payment_status, purchase_date, note)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$supplierId, $supplierName, $category, $grandTotal, $paidAmount, $payMethod, $payStatus, $purchaseDate, $note]);
            $purchaseId = $pdo->lastInsertId();

            // 5. Insert Items into purchase_items and update books table
            foreach ($items as $item) {
                $p_name = trim($item['name'] ?? '');
                $p_isbn = trim($item['isbn'] ?? '');
                $p_cost = floatval($item['unit_cost'] ?? 0);
                $p_qty = intval($item['quantity'] ?? 1);
                $p_total = $p_cost * $p_qty;

                if (empty($p_name)) continue;

                // A. Insert into purchase_items
                $stmt = $pdo->prepare("INSERT INTO purchase_items (purchase_id, item_name, isbn, unit_cost, quantity, total_item_cost) VALUES (?,?,?,?,?,?)");
                $stmt->execute([$purchaseId, $p_name, $p_isbn, $p_cost, $p_qty, $p_total]);

                // B. Sync with books table for inventory management (Preventing Duplicates)
                $existing = null;
                if (!empty($p_isbn)) {
                    $check = $pdo->prepare("SELECT id, stock_qty FROM books WHERE isbn = ?");
                    $check->execute([$p_isbn]);
                    $existing = $check->fetch();
                }

                if (!$existing && !empty($p_name)) {
                    $check = $pdo->prepare("SELECT id, stock_qty FROM books WHERE title = ? AND (isbn IS NULL OR isbn = '')");
                    $check->execute([$p_name]);
                    $existing = $check->fetch();
                }

                if ($existing) {
                    // Update existing inventory
                    $newQty = $existing['stock_qty'] + $p_qty;
                    $upd = $pdo->prepare("UPDATE books SET stock_qty = ?, purchase_price = ?, supplier_name = ?, item_type = ? WHERE id = ?");
                    $upd->execute([$newQty, $p_cost, $supplierName, $category, $existing['id']]);
                } else {
                    // Insert new item into books table
                    // Default sell_price = unit_cost for now (adjust in Inventory page later)
                    $ins = $pdo->prepare("INSERT INTO books (title, isbn, item_type, stock_qty, purchase_price, sell_price, supplier_name) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $ins->execute([$p_name, $p_isbn, $category, $p_qty, $p_cost, $p_cost, $supplierName]);
                }
            }

            // 6. Update Supplier Due if balance exists
            if ($supplierId && $grandTotal > $paidAmount) {
                $due = $grandTotal - $paidAmount;
                $pdo->prepare("UPDATE suppliers SET total_due = total_due + ? WHERE id = ?")->execute([$due, $supplierId]);
            }

            $pdo->commit();
            echo json_encode(['success' => true, 'message' => 'Multi-item purchase recorded!']);
        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
    elseif ($action === 'listPurchaseRecords') {
        $supplierId = $_GET['supplier_id'] ?? null;
        if ($supplierId) {
            $stmt = $pdo->prepare("SELECT * FROM purchases WHERE supplier_id = ? ORDER BY purchase_date DESC, id DESC");
            $stmt->execute([$supplierId]);
        }
        else {
            $stmt = $pdo->query("SELECT * FROM purchases ORDER BY purchase_date DESC, id DESC");
        }
        echo json_encode(['success' => true, 'records' => $stmt->fetchAll()]);
    }
}
catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
