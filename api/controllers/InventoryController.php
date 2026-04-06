<?php
require_once __DIR__ . '/../config/database.php';
header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

if ($action === 'migrate') {
    try {
        $pdo->exec("ALTER TABLE books 
            ADD COLUMN IF NOT EXISTS title_en VARCHAR(255) DEFAULT NULL,
            ADD COLUMN IF NOT EXISTS subtitle VARCHAR(255) DEFAULT NULL,
            ADD COLUMN IF NOT EXISTS description TEXT DEFAULT NULL,
            ADD COLUMN IF NOT EXISTS category_id INT DEFAULT NULL,
            ADD COLUMN IF NOT EXISTS genre VARCHAR(100) DEFAULT NULL,
            ADD COLUMN IF NOT EXISTS language VARCHAR(50) DEFAULT 'Bengali',
            ADD COLUMN IF NOT EXISTS author_en VARCHAR(255) DEFAULT NULL,
            ADD COLUMN IF NOT EXISTS co_author VARCHAR(255) DEFAULT NULL,
            ADD COLUMN IF NOT EXISTS publisher VARCHAR(255) DEFAULT NULL,
            ADD COLUMN IF NOT EXISTS publish_year VARCHAR(20) DEFAULT NULL,
            ADD COLUMN IF NOT EXISTS edition VARCHAR(50) DEFAULT NULL,
            ADD COLUMN IF NOT EXISTS format VARCHAR(50) DEFAULT 'Paperback',
            ADD COLUMN IF NOT EXISTS page_count INT DEFAULT 0,
            ADD COLUMN IF NOT EXISTS book_condition VARCHAR(50) DEFAULT 'New',
            ADD COLUMN IF NOT EXISTS shelf_location VARCHAR(100) DEFAULT NULL,
            ADD COLUMN IF NOT EXISTS rack_number VARCHAR(100) DEFAULT NULL,
            ADD COLUMN IF NOT EXISTS min_stock_level INT DEFAULT 0,
            ADD COLUMN IF NOT EXISTS is_borrowable TINYINT(1) DEFAULT 1,
            ADD COLUMN IF NOT EXISTS is_suggested TINYINT(1) DEFAULT 0,
            ADD COLUMN IF NOT EXISTS photo_2 VARCHAR(255) DEFAULT NULL,
            ADD COLUMN IF NOT EXISTS photo_3 VARCHAR(255) DEFAULT NULL
        ");
        echo json_encode(["success" => true, "message" => "Database schema updated!"]);
        exit;
    } catch (Exception $e) {
        echo json_encode(["success" => false, "error" => $e->getMessage()]);
        exit;
    }
}

try {
    if ($action === 'getItems') {
        // Fetch books, joining with categories for names
        $stmt_b = $pdo->query("SELECT b.*, c.name as category_name, c.name as item_type, 'books' as source_table 
                               FROM books b 
                               LEFT JOIN categories c ON b.category_id = c.id
                               WHERE b.is_active = 1");
        $books = $stmt_b->fetchAll(PDO::FETCH_ASSOC);

        // Fetch inventory items mapped to same structure, joining with categories
        $stmt_i = $pdo->query("SELECT i.id, i.item_name as title, c.name as category_name, c.name as item_type, i.item_type as category_id, i.quantity as stock_qty, i.sell_price, i.unit_cost as purchase_price, i.supplier_name, i.barcode as isbn, i.is_active, '' as cover_image, '' as author, 'inventory_items' as source_table 
                               FROM inventory_items i 
                               LEFT JOIN categories c ON i.item_type = c.id
                               WHERE i.is_active = 1");
        $items = $stmt_i->fetchAll(PDO::FETCH_ASSOC);

        $allItems = array_merge($books, $items);
        usort($allItems, function($a, $b) { return $b['id'] <=> $a['id']; });

        echo json_encode(["success" => true, "items" => $allItems]);
    } elseif ($action === 'addItem' || $action === 'updateItem') {
        // Read JSON body (sent by JS as application/json)
        $data = json_decode(file_get_contents('php://input'), true) ?? [];

        $id            = (int)($data['id'] ?? 0);
        $source_table  = $data['source_table'] ?? 'books';
        $title         = trim($data['title'] ?? '');
        $title_en      = trim($data['title_en'] ?? '');
        $subtitle      = trim($data['subtitle'] ?? '');
        $description   = trim($data['description'] ?? '');
        $type          = $data['item_type'] ?? 'Books';
        $category_id   = !empty($data['category_id']) ? (int)$data['category_id'] : null;
        $genre         = trim($data['genre'] ?? '');
        $language      = trim($data['language'] ?? 'Bengali');

        $author        = trim($data['author'] ?? '');
        $author_en     = trim($data['author_en'] ?? '');
        $co_author     = trim($data['co_author'] ?? '');
        $publisher     = trim($data['publisher'] ?? '');
        $publish_year  = trim($data['publish_year'] ?? '');
        $edition       = trim($data['edition'] ?? '');
        $isbn          = trim($data['isbn'] ?? '');
        $supplier_name = trim($data['supplier_name'] ?? '');

        $format        = $data['format'] ?? '';
        $page_count    = (int)($data['page_count'] ?? 0);
        $book_condition= $data['book_condition'] ?? 'New';
        $shelf_location= trim($data['shelf_location'] ?? '');
        $rack_number   = trim($data['rack_number'] ?? '');
        $stock         = (int)($data['stock_qty'] ?? 0);
        $min_stock     = (int)($data['min_stock_level'] ?? 0);
        $is_borrowable = (int)($data['is_borrowable'] ?? 1);
        $is_suggested  = (int)($data['is_suggested'] ?? 0);

        $price         = (float)($data['sell_price'] ?? 0.00);
        $cost          = (float)($data['purchase_price'] ?? 0.00);

        if ($action === 'addItem') {
            if (strtolower($type) === 'books' || strtolower($type) === 'book' || (is_numeric($type) && $type == $category_id)) {
                $stmt = $pdo->prepare("INSERT INTO books (
                    title, title_en, subtitle, description, item_type, category_id, genre, language,
                    author, author_en, co_author, publisher, publish_year, edition, isbn,
                    format, page_count, book_condition, shelf_location, rack_number, stock_qty, min_stock_level, is_borrowable, is_suggested,
                    sell_price, purchase_price, supplier_name
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

                $stmt->execute([
                    $title, $title_en, $subtitle, $description, $type, $category_id, $genre, $language,
                    $author, $author_en, $co_author, $publisher, $publish_year, $edition, $isbn,
                    $format, $page_count, $book_condition, $shelf_location, $rack_number, $stock, $min_stock, $is_borrowable, $is_suggested,
                    $price, $cost, $supplier_name
                ]);
            } else {
                // For inventory items, ensure we have an ID for item_type
                $resolvedType = $category_id;
                if (!$resolvedType && !is_numeric($type)) {
                    $getCat = $pdo->prepare("SELECT id FROM item_categories WHERE name = ?");
                    $getCat->execute([$type]);
                    $resolvedType = $getCat->fetchColumn();
                }

                $stmt = $pdo->prepare("INSERT INTO inventory_items (item_name, item_type, quantity, unit_cost, sell_price, supplier_name, barcode) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$title, $resolvedType, $stock, $cost, $price, $supplier_name, $isbn]);
            }
            echo json_encode(["success" => true, "message" => "Item added!", "id" => $pdo->lastInsertId()]);
        } else {
            if ($source_table === 'inventory_items') {
                $resolvedType = $category_id;
                if (!$resolvedType && !is_numeric($type)) {
                    $getCat = $pdo->prepare("SELECT id FROM item_categories WHERE name = ?");
                    $getCat->execute([$type]);
                    $resolvedType = $getCat->fetchColumn();
                }
                $stmt = $pdo->prepare("UPDATE inventory_items SET item_name=?, item_type=?, quantity=?, unit_cost=?, sell_price=?, supplier_name=?, barcode=? WHERE id=?");
                $stmt->execute([$title, $resolvedType, $stock, $cost, $price, $supplier_name, $isbn, $id]);
            } else {
                $stmt = $pdo->prepare("UPDATE books SET
                    title=?, title_en=?, subtitle=?, description=?, item_type=?, category_id=?, genre=?, language=?,
                    author=?, author_en=?, co_author=?, publisher=?, publish_year=?, edition=?, isbn=?,
                    format=?, page_count=?, book_condition=?, shelf_location=?, rack_number=?, stock_qty=?, min_stock_level=?, is_borrowable=?, is_suggested=?,
                    sell_price=?, purchase_price=?, supplier_name=?
                    WHERE id=?");

                $stmt->execute([
                    $title, $title_en, $subtitle, $description, $type, $category_id, $genre, $language,
                    $author, $author_en, $co_author, $publisher, $publish_year, $edition, $isbn,
                    $format, $page_count, $book_condition, $shelf_location, $rack_number, $stock, $min_stock, $is_borrowable, $is_suggested,
                    $price, $cost, $supplier_name,
                    $id
                ]);
            }
            echo json_encode(["success" => true, "message" => "Item updated!"]);
        }
    } elseif ($action === 'deleteItem') {
        $data = json_decode(file_get_contents('php://input'), true);
        $id = (int) ($data['id'] ?? 0);
        $source = $data['source_table'] ?? 'books';

        if (!$id) {
            echo json_encode(["success" => false, "error" => "Invalid ID."]);
            exit;
        }

        if ($source === 'inventory_items') {
            $stmt = $pdo->prepare("UPDATE inventory_items SET is_active = 0 WHERE id = ?");
        } else {
            $stmt = $pdo->prepare("UPDATE books SET is_active = 0 WHERE id = ?");
        }
        $stmt->execute([$id]);
        echo json_encode(["success" => true, "message" => "Item deleted."]);
    } elseif ($action === 'restockItem') {
        $data = json_decode(file_get_contents('php://input'), true);
        $id = (int) ($data['id'] ?? 0);
        $qty = (int) ($data['qty'] ?? 0);
        $source = $data['source_table'] ?? 'books';

        if (!$id || $qty < 1) {
            echo json_encode(["success" => false, "error" => "Invalid restock data."]);
            exit;
        }

        if ($source === 'inventory_items') {
            $stmt = $pdo->prepare("UPDATE inventory_items SET quantity = quantity + ? WHERE id = ? AND is_active = 1");
        } else {
            $stmt = $pdo->prepare("UPDATE books SET stock_qty = stock_qty + ? WHERE id = ? AND is_active = 1");
        }
        $stmt->execute([$qty, $id]);

        if ($stmt->rowCount() === 0) {
            echo json_encode(["success" => false, "error" => "Item not found or already inactive."]);
            exit;
        }
        echo json_encode(["success" => true, "message" => "Stock updated!"]);
    } else {
        echo json_encode(["success" => false, "error" => "Invalid action."]);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
