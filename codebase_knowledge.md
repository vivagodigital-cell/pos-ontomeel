# Ontomeel Library & POS Codebase Knowledge Base

Welcome to the technical codebase knowledge base for the **Ontomeel Library & POS Management System**. This document provides developers with a deep architectural overview of the codebase, database schema, design patterns, core workflows, and development guidelines.

---

## 1. System Overview & Technology Stack

The Ontomeel platform acts as a unified hub for both **Traditional Library Workflows** and **Point-of-Sale (POS) Operations**. It is designed with clean, decoupled separations of concerns using:
*   **Backend**: Vanilla PHP 8.x with PDO database connectivity.
*   **Database**: MySQL / MariaDB (using UTF-8 `utf8mb4` character set).
*   **Frontend**: Vanilla HTML5, CSS3 (incorporating custom themes, glassmorphism, responsive CSS grid, and flexbox layouts), and modern ES6 Javascript.
*   **External Libraries & Fonts**: Google Fonts (Inter), FontAwesome v6.4.0 (for icons), and `JsBarcode` (for barcode printing in POS).

---

## 2. Directory Structure

```text
pos-ontomeel/
├── .env                       # Environment configuration secrets
├── .htaccess                  # Apache rewrites and folder security
├── index.html                 # Root redirect file (routes browser traffic to /pos/)
├── ontomibh_ontomeel_library.sql # Complete database SQL schema dump
├── api/                       # RESTful API endpoints and backend logic
│   ├── config/
│   │   └── database.php       # Database connection initialization using PDO
│   ├── controllers/
│   │   ├── AIController.php            # Groq Llama-3.1-8b consultancy endpoint
│   │   ├── AdminController.php         # Operators and admin profiles actions
│   │   ├── AuthController.php          # Session validation, login, and registration
│   │   ├── DashboardController.php     # Statistics and dashboard count aggregators
│   │   ├── InventoryController.php     # Catalog management (Add/Edit/Delete/Restock)
│   │   ├── MemberController.php        # Member directories, profiles, wallet top-ups
│   │   ├── MemberImportController.php  # CSV import handling for bulk members
│   │   ├── ProductImportController.php # CSV import handling for bulk inventory/books
│   │   ├── ReportController.php        # Sales reports, valuations, active borrow logs
│   │   ├── SupplierController.php      # Supply chain, purchase invoices, payment registry
│   │   └── TerminalController.php      # Core POS engine (Sells, Issues, Returns)
│   └── shared/
│       ├── auth_check.php     # Middleware enforcing session rules, roles, & UI injections
│       ├── email_worker.php   # Asynchronous background worker processing the mail queue
│       ├── notification_helper.php # Templates for transactional Email/SMS
│       └── smtp_client.php    # Low-level SMTP client socket connection helper
├── pos/                       # Frontend Admin Panel pages
│   ├── index.php              # Dashboard main landing page
│   ├── login.php              # Access gate login interface
│   ├── logout.php             # Session destroyer
│   ├── signup.php             # New manager/editor account registration
│   ├── assets/
│   │   ├── logo.webp          # System branding asset
│   │   ├── pos-styles.css     # Unified stylesheet (custom properties, variables, components)
│   │   └── sidebar.js         # JavaScript sidebar drawer builder (prefetches pages on hover)
│   └── pages/
│       ├── inventory.php      # Inventory catalog management interface
│       ├── member-import.php  # Import members portal
│       ├── member.php         # Customer Relationship Management (CRM) page
│       ├── orders.php         # Historical orders dashboard
│       ├── product-import.php # Import catalog CSV portal
│       ├── profile.php        # Staff and profile credentials management
│       ├── report.php         # Interactive business analytics reports
│       ├── supplier.php       # Supplier directories, ledger dues, and purchase recorders
│       ├── terminal.php       # POS Terminal Interface (Smart Cart and Checkout)
│       └── view_order.php     # Thermal invoice visualizer and payment status editor
└── site/
    └── index.html             # Customer-facing presentation website placeholder
```

---

## 3. Database Schema

The database consists of **21 tables** mapped to standard relationships:

### Core Tables & Fields

1.  **`admins`**: Management accounts for POS operators.
    *   `id` (INT, PK, Auto-Increment)
    *   `username` (VARCHAR(50)), `email` (VARCHAR(100)), `password` (VARCHAR(255) - hashed)
    *   `full_name` (VARCHAR(100))
    *   `role` (ENUM('SuperAdmin', 'Manager', 'Editor'))
    *   `last_login` (DATETIME)
    *   `pos_access` (TINYINT(1), default 1)
2.  **`books`**: Unified directory of books (both for library lending and retail sale).
    *   `id` (INT, PK, Auto-Increment)
    *   `title` / `title_en` (VARCHAR(255)), `subtitle` (VARCHAR(255)), `description` (TEXT)
    *   `category_id` (INT, FK to `categories`)
    *   `isbn` (VARCHAR(50) - matches barcodes)
    *   `author` / `author_en` (VARCHAR(150))
    *   `publisher` (VARCHAR(150)), `publish_year` (VARCHAR(4)), `edition` (VARCHAR(50))
    *   `stock_qty` (INT), `min_stock_level` (INT)
    *   `sell_price`, `purchase_price`, `discount_price` (DECIMAL(10,2))
    *   `is_borrowable` (TINYINT(1), default 1), `is_active` (TINYINT(1), default 1)
    *   `item_type` (VARCHAR(50), default 'Book')
3.  **`inventory_items`**: Non-book items (stationery, lifestyle accessories, etc.).
    *   `id` (INT, PK, Auto-Increment)
    *   `item_name` (VARCHAR(255))
    *   `item_type` (INT, FK to `categories`)
    *   `quantity` (INT)
    *   `unit_cost` / `sell_price` (DECIMAL(10,2))
    *   `supplier_id` (INT, FK to `suppliers`), `supplier_name` (VARCHAR(255))
    *   `barcode` (VARCHAR(100))
    *   `is_active` (TINYINT(1), default 1)
4.  **`members`**: Library borrowers and store customers.
    *   `id` (INT, PK, Auto-Increment)
    *   `membership_id` (VARCHAR(50) - standard OM-YYYY-XXXX format)
    *   `full_name` (VARCHAR(150)), `email` (VARCHAR(255)), `phone` (VARCHAR(20))
    *   `acc_balance` (DECIMAL(10,2) - wallet balance)
    *   `membership_plan` (ENUM('None', 'General', 'BookLover', 'Collector'))
    *   `plan_expire_date` (DATETIME)
    *   `is_active` (TINYINT(1), default 1)
5.  **`borrows`**: Lending transaction register.
    *   `id` (INT, PK, Auto-Increment)
    *   `member_id` (INT, FK to `members`), `book_id` (INT, FK to `books`)
    *   `borrow_date` (TIMESTAMP), `due_date` (DATE), `return_date` (DATE)
    *   `status` (ENUM('Processing', 'Active', 'Returned', 'Overdue', 'Cancelled'))
    *   `fine_amount` (DECIMAL(10,2))
6.  **`orders`**: Sale checkouts processed via the POS.
    *   `id` (INT, PK, Auto-Increment)
    *   `invoice_no` (VARCHAR(50) - OTM-YYYYMMDD-XXXX format)
    *   `member_id` (INT, FK to `members` - NULL for guest transactions)
    *   `subtotal`, `discount`, `total_amount` (DECIMAL(10,2))
    *   `payment_status` (VARCHAR(50) - 'Paid', 'Pending', 'Refunded', 'Cancelled')
    *   `payment_method` (VARCHAR(255) - 'Cash', 'Bkash', etc.)
    *   `order_type` (VARCHAR(20), default 'Sale')
    *   `guest_name` / `guest_phone` / `guest_email` (for guest checkouts)
    *   `staff_name` (VARCHAR(100) - operator performing transaction)
7.  **`order_items`**: Line items inside each order.
    *   `id` (INT, PK)
    *   `order_id` (INT, FK to `orders`), `book_id` (INT - maps to either book ID or item ID)
    *   `quantity` (INT), `unit_price`, `total_price` (DECIMAL(10,2))
    *   `item_type` (VARCHAR(20) - 'Book' or 'Inventory' categories)
8.  **`suppliers`**: Supplier address book and credit tracking.
    *   `id` (INT, PK)
    *   `name` (VARCHAR(150)), `contact` (VARCHAR(255)), `address` (TEXT)
    *   `total_due` (DECIMAL(10,2) - aggregate unpaid purchases amount)
9.  **`purchases`**: Catalog procurement invoices from suppliers.
    *   `id` (INT, PK)
    *   `supplier_id` (INT, FK to `suppliers`), `supplier_name` (VARCHAR(255))
    *   `category` (VARCHAR(100) - e.g., 'Books', 'Stationery')
    *   `total_amount`, `paid_amount` (DECIMAL(10,2))
    *   `payment_status` (ENUM('Paid', 'Partial', 'Unpaid'))
    *   `payment_method` (VARCHAR(100))
    *   `purchase_date` (DATE)
10. **`purchase_items`**: Line items linked to supplier purchases.
    *   `id` (INT, PK)
    *   `purchase_id` (INT, FK to `purchases`)
    *   `item_name` (VARCHAR(255)), `isbn` (VARCHAR(100))
    *   `unit_cost`, `quantity`, `total_item_cost` (DECIMAL(10,2))

Other supportive tables: `categories` (holds inventory groupings), `item_categories` (legacy categories), `supplier_payments` (repayment records reducing supplier dues), `transactions` (wallet history logs for members), `email_queue` (outgoing transactional mail queue), `external_borrows` (inter-library lendings), `settings` (global configurations).

---

## 4. Key Workflows & Execution Flows

### A. Point-of-Sale (Terminal) Checkout
The terminal divides operations into **Direct Sells** (immediate purchases) and **Issues** (library lendings):

1.  **Direct Sells Flow**:
    *   Operator scans barcode or searches products -> JS adds item to the cart (splits Books vs General Items based on internal structures).
    *   Operator selects Customer Type:
        *   *Guest*: Captures raw name, email, and phone.
        *   *Member*: Selects a registered user from the database.
    *   Operator clicks "Sell" -> POSTs payload to [TerminalController.php](file:///f:/xampp/htdocs/pos-ontomeel/api/controllers/TerminalController.php#L48).
    *   The controller starts a database transaction:
        *   Generates a unique invoice number (e.g., `OTM-20260621-ABCD`).
        *   Creates an entry in `orders` and bulk inserts items into `order_items`.
        *   Decreases stock in `books.stock_qty` or `inventory_items.quantity` depending on type.
        *   If the member uses wallet balance, verifies the balance using database locks (`SELECT FOR UPDATE`), and deducts from `members.acc_balance` while logging a transaction in `transactions`.
        *   Queues order confirmation emails and sends instant SMS messages using `BulkSMS BD` API.
        *   Commits transaction.

2.  **Issue Books Flow**:
    *   Only books (not stationery/general items) can be issued.
    *   Must link to a registered **Member** with a defined Return Due Date.
    *   Operator clicks "Issue" -> POSTs to [TerminalController.php](file:///f:/xampp/htdocs/pos-ontomeel/api/controllers/TerminalController.php#L206).
    *   Controller checks stock, logs borrowing details in `borrows` table with status 'Active', decrements book stock, and sends confirmation notifications.

3.  **Return Picking Flow**:
    *   Member returns items -> Operator selects Member -> Finds active borrows -> Selects books to check in.
    *   POSTs to `TerminalController.php?action=returnBooks` -> Updates `borrows` to 'Returned', registers `return_date`, and increments `books.stock_qty`.

---

### B. Supply Chain & Purchasing
Maintains stock sync with supplier accounting:
1.  Operator records invoice details (Supplier, Category, Date, Items list, Paid amount).
2.  POSTs to `SupplierController.php?action=savePurchaseRecord`.
3.  The controller evaluates the purchase:
    *   Calculates total purchase amount.
    *   Writes record to `purchases` and links items to `purchase_items`.
    *   Applies changes to supplier balance: `suppliers.total_due = total_due + (grand_total - paid_amount)`.
    *   Updates inventory levels: If category contains "book", updates/creates record in `books` table; otherwise creates/updates record in `inventory_items`.

---

### C. Asynchronous Mail System (Worker Pattern)
To ensure immediate response times during checkout, emails are processed asynchronously using a custom queue:
1.  **Queue**: `queueNotification()` writes mail data, templates, and recipient address to the `email_queue` table with status `pending`.
2.  **Trigger**: Right after insertion, it initiates an asynchronous socket connection using `fsockopen()` to hit `api/shared/email_worker.php`. This doesn't block the HTTP request execution because it doesn't wait for a response.
3.  **Process**: The worker selects the 5 oldest `pending` emails, changes status to `processing`, transmits them over SMTP via socket layers in `smtp_client.php`, and flags them as `sent` or `failed` (allowing up to 3 retry attempts).

---

## 5. Security & Middlewares

Authorization is centrally enforced by [auth_check.php](file:///f:/xampp/htdocs/pos-ontomeel/api/shared/auth_check.php).
*   **Secure Sessions**: Configures strict session parameters (`session.cookie_httponly = 1`, `session.use_only_cookies = 1`, and secure SSL cookies conditionally).
*   **Access Verification**: Calling `checkAuth()` checks for `$_SESSION['admin_id']` and stops users if `pos_access` is disabled, rendering a glassmorphism "Access Denied" page.
*   **Role Rendering**: `renderUserUI()` hooks into DOM compilation, auto-injecting credentials, sidebar templates, and showing/hiding `.admin-only` action buttons dynamically based on roles:
    *   `SuperAdmin` / `Super Admin`: Absolute access to configurations and team management.
    *   `Manager` / `Editor`: Restricted read/write access to checkout and cataloging.

---

## 6. Developer Setup & Installation

Follow these instructions to spin up the project locally:

1.  **Workspace Directory**: Clone the codebase into your local directory (e.g., `f:\xampp\htdocs\pos-ontomeel\`).
2.  **Web Server**: Run Apache and MySQL/MariaDB (e.g., using XAMPP, MAMP, or Laragon).
3.  **Database Migration**:
    *   Open phpMyAdmin or your database interface.
    *   Create a database named `ontomibh_ontomeel_library`.
    *   Import [ontomibh_ontomeel_library.sql](file:///f:/xampp/htdocs/pos-ontomeel/ontomibh_ontomeel_library.sql) to populate schemas and default data.
4.  **Local Settings Configuration**:
    *   Copy/Edit the [.env](file:///f:/xampp/htdocs/pos-ontomeel/.env) file in the root directory.
    *   Fill in database configurations:
        ```ini
        DB_HOST=localhost
        DB_NAME=ontomibh_ontomeel_library
        DB_USER=root
        DB_PASS=your_password
        ```
    *   Configure the SMTP variables if you need to test outgoing transactional emails:
        ```ini
        SMTP_HOST=your_smtp_host
        SMTP_PORT=465
        SMTP_USER=info@yourdomain.com
        SMTP_PASS=your_smtp_password
        ```
    *   Set up Groq AI configurations to use recommendations:
        ```ini
        GROQ_API_KEY=gsk_your_groq_api_key_here
        ```
5.  **Browser Access**: Navigate to `http://localhost/pos-ontomeel/` inside your browser. The system will automatically direct you to `pos/login.php` to input your credentials.
