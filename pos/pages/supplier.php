<?php require_once '../../api/shared/auth_check.php'; checkAuth(true); renderUserUI(true); ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supplier & External Inventory | Ontomeel POS</title>
    <link rel="stylesheet" href="../assets/pos-styles.css?v=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        .supplier-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 2rem;
            margin-top: 2rem;
        }

        .data-card {
            background: white;
            border-radius: 24px;
            padding: 2rem;
            border: 1px solid var(--border-light);
            box-shadow: var(--shadow-md);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .table-wrapper {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.9rem;
        }

        th {
            text-align: left;
            padding: 1rem;
            color: var(--text-muted);
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.75rem;
            border-bottom: 1px solid var(--border-light);
        }

        td {
            padding: 1.25rem 1rem;
            border-bottom: 1px solid var(--border-light);
            color: var(--text-body);
        }

        tr:hover td {
            background: var(--primary-blue-soft);
        }

        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(15, 23, 42, 0.3);
            backdrop-filter: blur(8px);
            z-index: 1000;
            display: none;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background: white;
            width: 450px;
            border-radius: 28px;
            overflow: hidden;
            box-shadow: 0 25px 70px -10px rgba(15, 23, 42, 0.2);
            animation: modalPop 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        .input-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
            margin-bottom: 1rem;
        }

        .checkout-input {
            width: 100%;
            padding: 12px 16px;
            border: 1.5px solid #e2e8f0;
            border-radius: 14px;
            font-size: 0.95rem;
            outline: none;
            background: #f8fafc;
        }

        .checkout-input:focus {
            border-color: var(--primary-blue);
            background: white;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.08);
        }

        .status-badge {
            padding: 4px 10px;
            border-radius: 30px;
            font-size: 0.7rem;
            font-weight: 800;
            text-transform: uppercase;
        }

        .status-active {
            background: #ecfdf5;
            color: #10b981;
        }

        .status-overdue {
            background: #fef2f2;
            color: #ef4444;
        }

        .tabs {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            background: #f1f5f9;
            padding: 5px;
            border-radius: 16px;
            width: fit-content;
        }

        .tab-btn {
            padding: 10px 20px;
            border-radius: 12px;
            cursor: pointer;
            font-weight: 700;
            font-size: 0.85rem;
            transition: 0.3s;
            color: var(--text-muted);
            border: none;
            background: transparent;
        }

        .tab-btn.active {
            background: white;
            color: var(--primary-blue);
            box-shadow: var(--shadow-sm);
        }

        #toastContainer {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 2000;
        }

        .toast {
            background: #0f172a;
            color: white;
            padding: 16px 24px;
            border-radius: 16px;
            margin-top: 10px;
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
            }

            to {
                transform: translateX(0);
            }
        }
    </style>
    
</head>

<body>

    <script src="../assets/sidebar.js"></script>

    <div class="main-wrapper">
        <header>
            <div class="page-title">
                <h1>Supplier & <span style="color: var(--primary-blue);">Inventory Source</span></h1>
                <p>Track book procurement and external library loans</p>
            </div>
        </header>

        <div class="tabs">
            <button class="tab-btn active" onclick="showTab('suppliers')">Suppliers List</button>
            <button class="tab-btn" onclick="showTab('suppliedBooks')">Supplied Books</button>
            <button class="tab-btn" onclick="showTab('purchaseRecords')">Purchase Records</button>
            <button class="tab-btn" onclick="showTab('externalBorrows')">External Borrows</button>
        </div>

        <!-- Suppliers Tab -->
        <div id="suppliersTab" class="tab-content">
            <div class="data-card">
                <div class="card-header">
                    <div style="font-weight: 800; font-size: 1.1rem;">Registered Suppliers</div>
                    <button class="nav-link active" onclick="openSupplierModal()"
                        style="padding: 10px 20px; font-size: 0.85rem;">
                        <i class="fa-solid fa-plus"></i> ADD SUPPLIER
                    </button>
                </div>
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>Supplier Name</th>
                                <th>Contact</th>
                                <th>Total Due</th>
                                <th style="text-align:right;">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="supplierTableBody"></tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Supplied Books Tab -->
        <div id="suppliedBooksTab" class="tab-content" style="display:none;">
            <div class="data-card">
                <div class="card-header">
                    <div style="font-weight: 800; font-size: 1.1rem;">Procurement History</div>
                </div>
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>Supplier Name</th>
                                <th>Total Titles</th>
                                <th>Inventory Value</th>
                                <th style="text-align:right;">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="procurementTableBody"></tbody>
                    </table>
                </div>
            </div>

        </div>

        <!-- Purchase Records Tab -->
        <div id="purchaseRecordsTab" class="tab-content" style="display:none;">
            <div class="data-card">
                <div class="card-header">
                    <div style="font-weight: 800; font-size: 1.1rem;">Item Purchase Records</div>
                    <div style="display: flex; gap: 10px;">
                        <button class="nav-link" onclick="openCategoryModal()"
                            style="padding: 10px 20px; font-size: 0.85rem; background: #f1f5f9; color: var(--text-muted); border: none;">
                            <i class="fa-solid fa-tags"></i> CATEGORIES
                        </button>
                        <button class="nav-link active" onclick="openPurchaseModal()"
                            style="padding: 10px 20px; font-size: 0.85rem;">
                            <i class="fa-solid fa-plus"></i> ADD PURCHASE
                        </button>
                    </div>
                </div>
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Category</th>
                                <th>Total Cost</th>
                                <th>Paid</th>
                                <th>Status</th>
                                <th>Supplier</th>
                                <th style="text-align:right;">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="purchaseRecordsTableBody"></tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- External Borrows Tab -->
        <div id="externalBorrowsTab" class="tab-content" style="display:none;">
            <div class="data-card">
                <div class="card-header">
                    <div style="font-weight: 800; font-size: 1.1rem;">Books Borrowed from Other Libraries</div>
                    <button class="nav-link active" onclick="openModal('borrowModal')"
                        style="padding: 10px 20px; font-size: 0.85rem;">
                        <i class="fa-solid fa-plus"></i> LOG BORROW
                    </button>
                </div>
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>Library Name</th>
                                <th>Book Title</th>
                                <th>Borrow Date</th>
                                <th>Due Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="borrowTableBody"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modals -->
    <div class="modal-overlay" id="supplierModal">
        <div class="modal-content">
            <div
                style="padding: 1.5rem; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between;">
                <h2 id="supModalTitle" style="margin:0; font-size:1.2rem;">Add New Supplier</h2>
                <button onclick="closeModal('supplierModal')"
                    style="border:none; background:none; cursor:pointer; font-size:1.2rem;">&times;</button>
            </div>
            <div style="padding: 2rem;">
                <input type="hidden" id="supId">
                <div class="input-group">
                    <label style="font-size: 0.7rem; font-weight: 800; color: var(--text-muted);">SUPPLIER NAME</label>
                    <input type="text" id="supName" class="checkout-input">
                </div>
                <div class="input-group">
                    <label style="font-size: 0.7rem; font-weight: 800; color: var(--text-muted);">CONTACT PHONE</label>
                    <input type="text" id="supContact" class="checkout-input">
                </div>
                <div class="input-group">
                    <label style="font-size: 0.7rem; font-weight: 800; color: var(--text-muted);">EMAIL</label>
                    <input type="email" id="supEmail" class="checkout-input">
                </div>
                <div class="input-group">
                    <label style="font-size: 0.7rem; font-weight: 800; color: var(--text-muted);">ADDRESS</label>
                    <textarea id="supAddress" class="checkout-input" style="height: 80px;"></textarea>
                </div>
                <button onclick="saveSupplier()" class="nav-link active"
                    style="width:100%; justify-content:center; padding:12px; margin-top:1rem;">SAVE SUPPLIER</button>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="borrowModal">
        <div class="modal-content">
            <div
                style="padding: 1.5rem; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between;">
                <h2 style="margin:0; font-size:1.2rem;">Log External Borrow</h2>
                <button onclick="closeModal('borrowModal')"
                    style="border:none; background:none; cursor:pointer; font-size:1.2rem;">&times;</button>
            </div>
            <div style="padding: 2rem;">
                <div class="input-group">
                    <label style="font-size: 0.7rem; font-weight: 800; color: var(--text-muted);">LIBRARY NAME</label>
                    <input type="text" id="borLib" class="checkout-input">
                </div>
                <div class="input-group">
                    <label style="font-size: 0.7rem; font-weight: 800; color: var(--text-muted);">BOOK TITLE</label>
                    <input type="text" id="borTitle" class="checkout-input">
                </div>
                <div class="input-group">
                    <label style="font-size: 0.7rem; font-weight: 800; color: var(--text-muted);">AUTHOR</label>
                    <input type="text" id="borAuthor" class="checkout-input">
                </div>
                <div class="input-group">
                    <label style="font-size: 0.7rem; font-weight: 800; color: var(--text-muted);">DUE DATE</label>
                    <input type="date" id="borDue" class="checkout-input">
                </div>
                <button onclick="saveBorrow()" class="nav-link active"
                    style="width:100%; justify-content:center; padding:12px; margin-top:1rem;">LOG ENTRY</button>
            </div>
        </div>
    </div>

    <!-- Collect Books Modal -->
    <div class="modal-overlay" id="collectModal">
        <div class="modal-content" style="width: 500px;">
            <div
                style="padding: 1.5rem; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between;">
                <h2 style="margin:0; font-size:1.2rem;">Record Book Collection</h2>
                <button onclick="closeModal('collectModal')"
                    style="border:none; background:none; cursor:pointer; font-size:1.2rem;">&times;</button>
            </div>
            <div style="padding: 2rem; display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div class="input-group" style="grid-column: span 2;">
                    <label style="font-size: 0.7rem; font-weight: 800; color: var(--text-header);">BOOK TITLE *</label>
                    <input type="text" id="collTitle" class="checkout-input">
                </div>
                <div class="input-group" style="grid-column: span 2;">
                    <label style="font-size: 0.7rem; font-weight: 800; color: var(--text-muted);">AUTHOR</label>
                    <input type="text" id="collAuthor" class="checkout-input">
                </div>
                <div class="input-group" style="grid-column: span 2;">
                    <label style="font-size: 0.7rem; font-weight: 800; color: var(--text-muted);">ISBN / BARCODE</label>
                    <input type="text" id="collIsbn" class="checkout-input" placeholder="Leave empty to auto-generate">
                </div>
                <div class="input-group">
                    <label style="font-size: 0.7rem; font-weight: 800; color: var(--text-muted);">QUANTITY *</label>
                    <input type="number" id="collQty" class="checkout-input">
                </div>
                <div class="input-group">
                    <label style="font-size: 0.7rem; font-weight: 800; color: var(--text-muted);">PURCHASE PRICE
                        *</label>
                    <input type="number" id="collCost" class="checkout-input">
                </div>
                <div class="input-group" style="grid-column: span 2;">
                    <label style="font-size: 0.7rem; font-weight: 800; color: var(--text-muted);">SELLING PRICE</label>
                    <input type="number" id="collSale" class="checkout-input">
                </div>
                <button onclick="processCollection()" class="nav-link active"
                    style="grid-column: span 2; justify-content:center; padding:12px; margin-top:0.5rem;">
                    <i class="fa-solid fa-cloud-arrow-up"></i> UPDATE INVENTORY
                </button>
            </div>
        </div>
    </div>

    <!-- Purchase Record Modal -->
    <div class="modal-overlay" id="purchaseRecordModal">
        <div class="modal-content" style="width: 850px; max-width: 95%;">
            <div
                style="padding: 1.5rem; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center;">
                <h2 id="purModalTitle" style="margin:0; font-size:1.35rem; font-weight: 800; color: var(--text-header);"><i class="fa-solid fa-cart-shopping"></i> Purchase Record</h2>
                <button onclick="closeModal('purchaseRecordModal')"
                    style="border:none; background: #f1f5f9; cursor:pointer; font-size:1.2rem; padding: 8px 12px; border-radius: 12px; color: #94a3b8;">&times;</button>
            </div>
            
            <div style="padding: 2rem;">
                <input type="hidden" id="purId">
                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 25px;">
                    <div class="input-group">
                        <label style="font-size: 0.75rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase;">Supplier Name *</label>
                        <select id="purSupplier" class="checkout-input">
                            <option value="">Select Supplier</option>
                        </select>
                    </div>
                    <div class="input-group">
                        <label style="font-size: 0.75rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase;">Purchase Category</label>
                        <select id="purCategory" class="checkout-input">
                            <option value="">Loading...</option>
                        </select>
                    </div>
                    <div class="input-group">
                        <label style="font-size: 0.75rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase;">Purchase Date</label>
                        <input type="date" id="purDate" class="checkout-input">
                    </div>
                </div>

                <div style="background: #f8fafc; border-radius: 20px; border: 1px solid var(--border-light); padding: 1.5rem; margin-bottom: 25px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                        <h3 style="font-size: 0.95rem; font-weight: 800; color: var(--text-header); margin: 0;">Items List</h3>
                        <button type="button" onclick="addPurchaseItemRow()" class="nav-link" style="padding: 6px 12px; font-size: 0.75rem; border: 1px solid var(--primary-blue); color: var(--primary-blue); background: var(--primary-blue-soft);">
                            <i class="fa-solid fa-plus-circle"></i> Add More Item
                        </button>
                    </div>
                    <div style="max-height: 250px; overflow-y: auto;">
                        <table style="width: 100%;" id="purchaseItemsTable">
                            <thead>
                                <tr style="text-align: left; font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase;">
                                    <th style="padding: 10px; width: 35%;">Item Name / Title</th>
                                    <th style="padding: 10px; width: 25%;">ISBN/SKU/Barcode</th>
                                    <th style="padding: 10px; width: 15%;">Unit Cost</th>
                                    <th style="padding: 10px; width: 15%;">Qty</th>
                                    <th style="padding: 10px; width: 10%;"></th>
                                </tr>
                            </thead>
                            <tbody id="purchaseItemsBody">
                                <!-- Dynamic Rows -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 40px; align-items: end;">
                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px;">
                        <div class="input-group">
                            <label style="font-size: 0.75rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase;">Payment Method</label>
                            <select id="purPayMethod" class="checkout-input">
                                <option value="Cash">Cash</option>
                                <option value="Bank">Bank Transfer</option>
                                <option value="Mobile">Mobile Banking (Bkash/Nagad)</option>
                                <option value="Due">Due / Credits</option>
                            </select>
                        </div>
                        <div class="input-group">
                            <label style="font-size: 0.75rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase;">Paid Amount</label>
                            <input type="number" id="purPaid" class="checkout-input" value="0">
                        </div>
                        <div class="input-group" style="grid-column: span 2;">
                            <label style="font-size: 0.75rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase;">Observation / Note</label>
                            <input type="text" id="purNote" class="checkout-input" placeholder="Any order details or reference numbers...">
                        </div>
                    </div>

                    <div style="background: var(--text-header); color: white; padding: 1.5rem; border-radius: 20px; text-align: right;">
                        <div style="font-size: 0.7rem; font-weight: 800; opacity: 0.7; margin-bottom: 5px; text-transform: uppercase;">Total Purchase Amount</div>
                        <div id="purTotalDisplay" style="font-size: 1.75rem; font-weight: 800; font-family: 'Inter', sans-serif;">৳0.00</div>
                    </div>
                </div>

                <div style="margin-top: 2rem; display: flex; gap: 15px;">
                    <button type="button" onclick="closeModal('purchaseRecordModal')" class="nav-link" style="flex: 1; justify-content: center; background: #f1f5f9; color: var(--text-muted); border: none;">Discard</button>
                    <button type="button" onclick="savePurchaseRecord()" id="btnSavePurchase" class="nav-link active" style="flex: 2; justify-content: center; padding: 12px; font-weight: 800;">
                        <i class="fa-solid fa-save"></i> Save Purchase Record
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Pay Due Modal -->
    <div class="modal-overlay" id="payModal">
        <div class="modal-content">
            <div
                style="padding: 1.5rem; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between;">
                <h2 style="margin:0; font-size:1.2rem;">Settle Supplier Due</h2>
                <button onclick="closeModal('payModal')"
                    style="border:none; background:none; cursor:pointer; font-size:1.2rem;">&times;</button>
            </div>
            <div style="padding: 2rem;">
                <div style="background: #fef2f2; padding: 15px; border-radius: 12px; margin-bottom: 20px;">
                    <span style="font-size: 0.75rem; color: #ef4444; font-weight: 800;">CURRENT OUTSTANDING</span>
                    <div id="payDueDisplay" style="font-size: 1.5rem; font-weight: 800; color: #b91c1c;">৳0.00</div>
                </div>
                <div class="input-group">
                    <label style="font-size: 0.7rem; font-weight: 800; color: var(--text-muted);">PAYMENT AMOUNT</label>
                    <input type="number" id="payAmount" class="checkout-input">
                </div>
                <div class="input-group">
                    <label style="font-size: 0.7rem; font-weight: 800; color: var(--text-muted);">METHOD</label>
                    <select id="payMethod" class="checkout-input">
                        <option value="Cash">Cash</option>
                        <option value="Bank">Bank Transfer</option>
                        <option value="Mobile">Mobile Banking</option>
                    </select>
                </div>
                <button onclick="processPayment()" class="nav-link active"
                    style="width:100%; justify-content:center; padding:12px; margin-top:1rem; background:#10b981;">
                    RECORD PAYMENT
                </button>
            </div>
        </div>
    </div>

    <div id="toastContainer"></div>

    <!-- View Books Modal -->
    <div class="modal-overlay" id="viewBooksModal">
        <div class="modal-content" style="width: 80%; max-width: 900px;">
            <div
                style="padding: 1.5rem; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items:center;">
                <h2 style="margin:0; font-size:1.2rem;"><i class="fa-solid fa-book"></i> Books from <span
                        id="currentDetailSupplier" style="color:var(--primary-blue);"></span></h2>
                <button onclick="closeModal('viewBooksModal')"
                    style="border:none; background:#f1f5f9; cursor:pointer; font-size:1rem; padding: 10px 15px; border-radius: 50%;"><i
                        class="fa-solid fa-times"></i></button>
            </div>
            <div style="padding: 1.5rem; max-height: 70vh; overflow-y: auto;">
                <div class="table-wrapper">
                    <table style="background:#f8fafc; border-radius:12px;">
                        <thead>
                            <tr>
                                <th>Book Title</th>
                                <th>Author</th>
                                <th>Purchase Price</th>
                                <th>Stock Qty</th>
                                <th>Total Value</th>
                            </tr>
                        </thead>
                        <tbody id="supplierDetailTableBody"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="categoryModal">
        <div class="modal-content" style="width: 400px;">
            <div style="padding: 1.5rem; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items:center;">
                <h2 style="margin:0; font-size:1.1rem; font-weight:800;">Manage Categories</h2>
                <button onclick="closeModal('categoryModal')" style="background:none; border:none; font-size:1.5rem; color:#94a3b8; cursor:pointer;">&times;</button>
            </div>
            <div style="padding: 1.5rem;">
                <div class="input-group" style="margin-bottom: 1.5rem;">
                    <label style="font-size: 0.7rem; font-weight: 800; color: var(--text-muted);">CATEGORY NAME</label>
                    <div style="display: flex; gap: 10px;">
                        <input type="text" id="catName" class="checkout-input" placeholder="e.g. Stationary">
                        <button id="btnSaveCat" onclick="saveCategory()" class="nav-link active" style="white-space:nowrap; padding: 0 15px; font-size: 0.75rem;">CREATE</button>
                    </div>
                </div>

                <div class="table-wrapper" style="max-height: 300px; overflow-y: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th style="padding: 8px;">Name</th>
                                <th style="padding: 8px; text-align:right;">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="categoryListBody">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        let activeTab = 'suppliers';
        function showTab(tab) {
            activeTab = tab;
            document.querySelectorAll('.tab-content').forEach(c => c.style.display = 'none');
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            document.getElementById(tab + 'Tab').style.display = 'block';
            
            // Handle active class
            if (event && event.target && event.target.classList.contains('tab-btn')) {
                event.target.classList.add('active');
            } else {
                document.querySelectorAll('.tab-btn').forEach(btn => {
                    if (btn.getAttribute('onclick')?.includes(tab)) btn.classList.add('active');
                });
            }

            syncActiveTab();
        }

        function syncActiveTab() {
            if (activeTab === 'suppliers') fetchSuppliers();
            if (activeTab === 'suppliedBooks') fetchSuppliedBooks();
            if (activeTab === 'purchaseRecords') {
                fetchPurchaseRecords();
                loadCategories();
            }
            if (activeTab === 'externalBorrows') fetchExternalBorrows();
        }

        let categories = [];
        async function loadCategories() {
            try {
                // Ensure table exists (internal check in controller)
                await fetch('../../api/controllers/SupplierController.php?action=setupPurchaseTable');
                
                const res = await fetch('../../api/controllers/SupplierController.php?action=listCategories');
                const data = await res.json();
                if (data.success) {
                    categories = data.categories;
                    const select = document.getElementById('purCategory');
                    if (select) {
                        select.innerHTML = categories.map(c => `<option value="${c.name}">${c.name}</option>`).join('');
                    }
                    renderCategoryList();
                }
            } catch (e) {
                console.error("Failed to load categories", e);
            }
        }

        function openCategoryModal() {
            openModal('categoryModal');
            renderCategoryList();
        }

        function renderCategoryList() {
            const list = document.getElementById('categoryListBody');
            if (!list) return;
            
            if (categories.length === 0) {
                list.innerHTML = '<tr><td colspan="2" style="text-align:center; padding:20px;">No categories found.</td></tr>';
                return;
            }

            list.innerHTML = categories.map(c => `
                <tr>
                    <td style="font-weight:700;">${c.name}</td>
                    <td style="text-align:right;">
                        <button onclick="editCategory(${c.id}, '${c.name.replace(/'/g, "\\'")}')" style="background:none; border:none; color:var(--primary-blue); cursor:pointer; margin-right:10px;"><i class="fa-solid fa-edit"></i></button>
                        <button onclick="deleteCategory(${c.id})" style="background:none; border:none; color:#ef4444; cursor:pointer;"><i class="fa-solid fa-trash-can"></i></button>
                    </td>
                </tr>
            `).join('');
        }

        let editingCategoryId = null;
        function editCategory(id, name) {
            editingCategoryId = id;
            document.getElementById('catName').value = name;
            document.getElementById('btnSaveCat').innerText = "UPDATE CATEGORY";
        }

        async function saveCategory() {
            const name = document.getElementById('catName').value.trim();
            if (!name) return showToast("Category name required", "error");

            const payload = { id: editingCategoryId, name: name };
            const res = await fetch('../../api/controllers/SupplierController.php?action=saveCategory', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const data = await res.json();
            if (data.success) {
                showToast(editingCategoryId ? "Category updated!" : "Category created!");
                document.getElementById('catName').value = '';
                editingCategoryId = null;
                document.getElementById('btnSaveCat').innerText = "CREATE CATEGORY";
                loadCategories();
            }
        }

        async function deleteCategory(id) {
            if (!confirm("Are you sure? This will not delete existing records but the category won't be available for new records.")) return;
            
            const res = await fetch(`../../api/controllers/SupplierController.php?action=deleteCategory&id=${id}`);
            const data = await res.json();
            if (data.success) {
                showToast("Category deleted!");
                loadCategories();
            }
        }

        function openModal(id) { document.getElementById(id).style.display = 'flex'; }
        function closeModal(id) { document.getElementById(id).style.display = 'none'; }

        let activeSupplierId = null;
        function openSupplierModal() {
            document.getElementById('supId').value = '';
            document.getElementById('supName').value = '';
            document.getElementById('supContact').value = '';
            document.getElementById('supEmail').value = '';
            document.getElementById('supAddress').value = '';
            document.getElementById('supModalTitle').innerText = "Add New Supplier";
            openModal('supplierModal');
        }

        async function fetchSuppliers() {
            const res = await fetch('../../api/controllers/SupplierController.php?action=listSuppliers');
            const data = await res.json();
            if (data.success) {
                document.getElementById('supplierTableBody').innerHTML = data.suppliers.map(s => `
                    <tr>
                        <td style="font-weight:700;">${s.name}</td>
                        <td>
                            <div style="font-size:0.85rem;">${s.contact || 'N/A'}</div>
                            <div style="font-size:0.75rem; color:var(--text-muted);">${s.address || ''}</div>
                        </td>
                        <td style="color:#ef4444; font-weight:700;">৳${s.total_due}</td>
                        <td style="text-align:right; white-space:nowrap;">
                            <button onclick='editSupplier(${JSON.stringify(s).replace(/'/g, "&apos;")})' 
                                style="background:none; border:none; color:var(--primary-blue); cursor:pointer; font-size:1.1rem; margin-right:12px;" title="Edit Supplier">
                                <i class="fa-solid fa-edit"></i>
                            </button>
                            <button onclick="deleteSupplier(${s.id})" 
                                style="background:none; border:none; color:#ef4444; cursor:pointer; font-size:1.1rem; margin-right:20px;" title="Delete Supplier">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                            <button onclick="openCollectModal('${s.id}', '${s.name.replace(/'/g, "\\'")}')" 
                                style="padding: 8px 14px; font-size: 0.7rem; border:none; border-radius:10px; background:var(--primary-blue); color:white; font-weight:800; cursor:pointer; margin-right:5px; transition:0.3s;">
                                <i class="fa-solid fa-box-archive"></i> COLLECT
                            </button>
                            <button onclick="openPayModal('${s.id}', '${s.name.replace(/'/g, "\\'")}', ${s.total_due})" 
                                style="padding: 8px 14px; font-size: 0.7rem; border:none; border-radius:10px; background:#10b981; color:white; font-weight:800; cursor:pointer; transition:0.3s;">
                                <i class="fa-solid fa-receipt"></i> PAY
                            </button>
                        </td>
                    </tr>
                `).join('');
            }
        }

        function editSupplier(s) {
            document.getElementById('supId').value = s.id;
            document.getElementById('supName').value = s.name;
            document.getElementById('supContact').value = s.contact || '';
            document.getElementById('supEmail').value = s.email || '';
            document.getElementById('supAddress').value = s.address || '';
            document.getElementById('supModalTitle').innerText = "Edit Supplier: " + s.name;
            openModal('supplierModal');
        }

        async function deleteSupplier(id) {
            if (!confirm("Are you sure you want to delete this supplier? This action cannot be undone.")) return;
            const res = await fetch(`../../api/controllers/SupplierController.php?action=deleteSupplier&id=${id}`);
            const data = await res.json();
            if (data.success) {
                showToast("Supplier deleted successfully");
                fetchSuppliers();
            }
        }

        function openCollectModal(id, name) {
            activeSupplierId = id;
            openModal('collectModal');
            document.querySelector('#collectModal h2').innerText = "Record Collection: " + name;
        }

        function openPayModal(id, name, due) {
            activeSupplierId = id;
            document.getElementById('payDueDisplay').innerText = "৳" + due;
            document.getElementById('payAmount').value = due;
            openModal('payModal');
            document.querySelector('#payModal h2').innerText = "Payment to: " + name;
        }

        async function processPayment() {
            const payload = {
                supplierId: activeSupplierId,
                amount: document.getElementById('payAmount').value,
                method: document.getElementById('payMethod').value
            };
            if (!payload.amount || payload.amount <= 0) return alert("Valid amount required.");

            const res = await fetch('../../api/controllers/SupplierController.php?action=recordPayment', {
                method: 'POST', body: JSON.stringify(payload)
            });
            if ((await res.json()).success) {
                showToast("Payment recorded successfully!");
                closeModal('payModal');
                // Refresh all tabs
                fetchSuppliers();
                fetchSuppliedBooks();
                fetchPurchaseRecords();
            }
        }

        async function processCollection() {
            const payload = {
                supplierId: activeSupplierId,
                title: document.getElementById('collTitle').value,
                author: document.getElementById('collAuthor').value,
                isbn: document.getElementById('collIsbn').value,
                qty: document.getElementById('collQty').value,
                cost: document.getElementById('collCost').value,
                sale: document.getElementById('collSale').value
            };
            if (!payload.title || !payload.qty) return alert("Title and Quantity are required.");

            const res = await fetch('../../api/controllers/SupplierController.php?action=receiveInventory', {
                method: 'POST', body: JSON.stringify(payload)
            });
            const data = await res.json();
            if (data.success) {
                showToast("Inventory updated successfully!");
                closeModal('collectModal');
                // Refresh all tabs
                fetchSuppliers();
                fetchSuppliedBooks();
                fetchPurchaseRecords();

                // Clear inputs
                document.getElementById('collTitle').value = '';
                document.getElementById('collAuthor').value = '';
                document.getElementById('collQty').value = '';
                document.getElementById('collCost').value = '';
                document.getElementById('collSale').value = '';
            }
        }

        async function fetchSuppliedBooks() {
            const res = await fetch('../../api/controllers/SupplierController.php?action=getSupplierBooks');
            const data = await res.json();
            if (data.success) {
                if (data.data.length === 0) {
                    document.getElementById('procurementTableBody').innerHTML = '<tr><td colspan="4" style="text-align:center; padding: 40px; color: var(--text-muted);">No procurement history found.</td></tr>';
                    return;
                }
                document.getElementById('procurementTableBody').innerHTML = data.data.map(d => `
                    <tr>
                        <td style="font-weight:700;">${d.supplier_name}</td>
                        <td>${d.book_count}</td>
                        <td style="color:var(--primary-blue); font-weight:700;">৳${(parseFloat(d.inventory_value) || 0).toLocaleString()}</td>
                        <td style="text-align:right;">
                            <button class="nav-link active" onclick="viewSupplierBooks('${d.supplier_name.replace(/'/g, "\\'")}')" style="padding: 6px 12px; font-size: 0.75rem; display:inline-flex;">
                                <i class="fa-solid fa-eye"></i> VIEW BOOKS
                            </button>
                        </td>
                    </tr>
                `).join('');
            }
        }

        async function viewSupplierBooks(name) {
            document.getElementById('currentDetailSupplier').innerText = name;
            openModal('viewBooksModal');

            // Show loading state
            document.getElementById('supplierDetailTableBody').innerHTML = '<tr><td colspan="5" style="text-align:center; padding: 40px;"><i class="fa-solid fa-spinner fa-spin"></i> Loading books...</td></tr>';

            const res = await fetch(`../../api/controllers/SupplierController.php?action=getBooksBySupplier&name=${encodeURIComponent(name)}`);
            const data = await res.json();

            if (data.success) {
                if (data.books.length > 0) {
                    document.getElementById('supplierDetailTableBody').innerHTML = data.books.map(b => `
                        <tr>
                            <td style="font-weight:700;">${b.title}</td>
                            <td>${b.author || '<span style="color:#cbd5e1">Unknown</span>'}</td>
                            <td>৳${parseFloat(b.purchase_price || 0).toLocaleString()}</td>
                            <td>${b.stock_qty || 0}</td>
                            <td style="font-weight:700; color:var(--primary-blue);">৳${(parseFloat(b.total_value) || 0).toLocaleString()}</td>
                        </tr>
                    `).join('');
                } else {
                    document.getElementById('supplierDetailTableBody').innerHTML = '<tr><td colspan="5" style="text-align:center; padding: 40px; color: var(--text-muted);">No books found for this supplier.</td></tr>';
                }
            }
        }

        async function fetchExternalBorrows() {
            const res = await fetch('../../api/controllers/SupplierController.php?action=listExternalBorrows');
            const data = await res.json();
            if (data.success) {
                document.getElementById('borrowTableBody').innerHTML = data.borrows.map(b => `
                    <tr>
                        <td style="font-weight:700;">${b.library_name}</td>
                        <td>${b.book_title}</td>
                        <td>${b.borrow_date}</td>
                        <td>${b.due_date}</td>
                        <td><span class="status-badge status-${b.status.toLowerCase()}">${b.status}</span></td>
                    </tr>
                `).join('');
            }
        }

        async function fetchPurchaseRecords() {
            // Setup table first (temporary for UI check)
            // await fetch('../../api/controllers/SupplierController.php?action=setupPurchaseTable');

            const res = await fetch('../../api/controllers/SupplierController.php?action=listPurchaseRecords');
            const data = await res.json();
            if (data.success) {
                const records = data.records || [];
                if (records.length > 0) {
                    document.getElementById('purchaseRecordsTableBody').innerHTML = records.map(r => {
                        let statusClass = r.payment_status === 'Paid' ? 'status-active' : (r.payment_status === 'Partial' ? 'status-overdue' : 'status-overdue');
                        return `
                            <tr>
                                <td>${r.purchase_date}</td>
                                <td style="font-weight:700;">${r.category || 'General'}</td>
                                <td style="font-weight:700;">৳${parseFloat(r.total_amount).toLocaleString()}</td>
                                <td style="color:#10b981;">৳${parseFloat(r.paid_amount).toLocaleString()}</td>
                                <td><span class="status-badge ${statusClass}">${r.payment_status}</span></td>
                                <td>${r.supplier_name || 'N/A'}</td>
                                <td style="text-align:right; white-space:nowrap;">
                                    <button class="btn-fund" style="background:#f1f5f9; color:var(--text-header); padding:6px 10px; font-size:0.75rem; border:none; border-radius:8px; cursor:pointer;" onclick="editPurchase(${r.id})">
                                        <i class="fa-solid fa-edit"></i>
                                    </button>
                                    <button class="btn-fund" style="background:#fff1f2; color:#ef4444; padding:6px 10px; font-size:0.75rem; border:none; border-radius:8px; cursor:pointer; margin-left:5px;" onclick="deletePurchase(${r.id})">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </td>
                            </tr>
                        `;
                    }).join('');
                } else {
                    document.getElementById('purchaseRecordsTableBody').innerHTML = '<tr><td colspan="8" style="text-align:center; padding: 40px; color: var(--text-muted);">No records found. Click "Add Purchase" to create one.</td></tr>';
                }
            }
        }

        async function loadSuppliersForPurchase() {
            const res = await fetch('../../api/controllers/SupplierController.php?action=listSuppliers');
            const data = await res.json();
            if (data.success) {
                const select = document.getElementById('purSupplier');
                // Keep the first option
                select.innerHTML = '<option value="">Select Supplier</option>';
                data.suppliers.forEach(s => {
                    const opt = document.createElement('option');
                    opt.value = s.id;
                    opt.textContent = s.name;
                    select.appendChild(opt);
                });
            }
        }

        let purchaseItemRowCount = 0;
        function addPurchaseItemRow() {
            const body = document.getElementById('purchaseItemsBody');
            const rowId = `item-row-${purchaseItemRowCount++}`;
            const row = `
                <tr id="${rowId}">
                    <td style="padding: 5px;"><input type="text" class="checkout-input pur-item-name" placeholder="Item/Book Name" style="padding: 8px 12px; font-size: 0.85rem;"></td>
                    <td style="padding: 5px;"><input type="text" class="checkout-input pur-item-isbn" placeholder="123456" style="padding: 8px 12px; font-size: 0.85rem;"></td>
                    <td style="padding: 5px;"><input type="number" class="checkout-input pur-item-cost" onkeyup="calculatePurchaseTotal()" placeholder="0.00" style="padding: 8px 12px; font-size: 0.85rem;"></td>
                    <td style="padding: 5px;"><input type="number" class="checkout-input pur-item-qty" onkeyup="calculatePurchaseTotal()" value="1" min="1" style="padding: 8px 12px; font-size: 0.85rem;"></td>
                    <td style="padding: 5px; text-align: center;"><button type="button" onclick="removePurchaseItemRow('${rowId}')" style="background:none; border:none; color:#ef4444; cursor:pointer;"><i class="fa-solid fa-trash"></i></button></td>
                </tr>
            `;
            body.insertAdjacentHTML('beforeend', row);
        }

        function removePurchaseItemRow(id) {
            document.getElementById(id).remove();
            calculatePurchaseTotal();
        }

        function calculatePurchaseTotal() {
            let total = 0;
            const rows = document.querySelectorAll('#purchaseItemsBody tr');
            rows.forEach(row => {
                const cost = parseFloat(row.querySelector('.pur-item-cost').value) || 0;
                const qty = parseInt(row.querySelector('.pur-item-qty').value) || 0;
                total += cost * qty;
            });
            document.getElementById('purTotalDisplay').innerText = "৳" + total.toFixed(2);
            // Default: Paid amount equals total unless manually edited
            // document.getElementById('purPaid').value = total.toFixed(2);
        }

        async function savePurchaseRecord() {
            const rows = document.querySelectorAll('#purchaseItemsBody tr');
            const items = [];
            rows.forEach(row => {
                const name = row.querySelector('.pur-item-name').value;
                if (name) {
                    items.push({
                        name: name,
                        isbn: row.querySelector('.pur-item-isbn').value,
                        unit_cost: parseFloat(row.querySelector('.pur-item-cost').value) || 0,
                        quantity: parseInt(row.querySelector('.pur-item-qty').value) || 0
                    });
                }
            });

            if (items.length === 0) {
                alert('Please add at least one item.');
                return;
            }

            const payload = {
                id: document.getElementById('purId').value || null,
                supplier_id: document.getElementById('purSupplier').value,
                category: document.getElementById('purCategory').value,
                purchase_date: document.getElementById('purDate').value,
                payment_method: document.getElementById('purPayMethod').value,
                paid_amount: parseFloat(document.getElementById('purPaid').value) || 0,
                note: document.getElementById('purNote').value,
                items: items
            };

            const res = await fetch('../../api/controllers/SupplierController.php?action=savePurchaseRecord', {
                method: 'POST',
                body: JSON.stringify(payload)
            });
            const data = await res.json();
            if (data.success) {
                showToast(payload.id ? "Purchase record updated!" : "Purchase record saved!");
                closeModal('purchaseRecordModal');
                fetchPurchaseRecords();
                fetchSuppliedBooks();
                fetchSuppliers(); // Refresh balance
            } else {
                alert(data.error || "Failed to save record.");
            }
        }

        function openPurchaseModal() {
            document.getElementById('purId').value = '';
            document.getElementById('purSupplier').value = '';
            document.getElementById('purCategory').value = 'General';
            document.getElementById('purDate').valueAsDate = new Date();
            document.getElementById('purPayMethod').value = 'Cash';
            document.getElementById('purPaid').value = '0';
            document.getElementById('purNote').value = '';
            document.getElementById('purchaseItemsBody').innerHTML = '';
            document.getElementById('purModalTitle').innerHTML = '<i class="fa-solid fa-cart-shopping"></i> Purchase Record';
            document.getElementById('btnSavePurchase').innerHTML = '<i class="fa-solid fa-save"></i> Save Purchase Record';
            addPurchaseItemRow();
            calculatePurchaseTotal();
            openModal('purchaseRecordModal');
        }

        async function editPurchase(id) {
            const res = await fetch(`../../api/controllers/SupplierController.php?action=getPurchaseDetails&id=${id}`);
            const data = await res.json();
            if (data.success) {
                const p = data.purchase;
                const items = data.items;

                document.getElementById('purId').value = p.id;
                document.getElementById('purSupplier').value = p.supplier_id || '';
                document.getElementById('purCategory').value = p.category;
                document.getElementById('purDate').value = p.purchase_date;
                document.getElementById('purPayMethod').value = p.payment_method;
                document.getElementById('purPaid').value = p.paid_amount;
                document.getElementById('purNote').value = p.note || '';

                document.getElementById('purchaseItemsBody').innerHTML = '';
                items.forEach(item => {
                    addPurchaseItemRow();
                    const lastRow = document.querySelector('#purchaseItemsBody tr:last-child');
                    lastRow.querySelector('.pur-item-name').value = item.item_name;
                    lastRow.querySelector('.pur-item-isbn').value = item.isbn || '';
                    lastRow.querySelector('.pur-item-cost').value = item.unit_cost;
                    lastRow.querySelector('.pur-item-qty').value = item.quantity;
                });

                document.getElementById('purModalTitle').innerHTML = '<i class="fa-solid fa-edit"></i> Edit Purchase Record';
                document.getElementById('btnSavePurchase').innerHTML = '<i class="fa-solid fa-save"></i> Update Purchase Record';
                
                calculatePurchaseTotal();
                openModal('purchaseRecordModal');
            }
        }

        async function deletePurchase(id) {
            if (!confirm("Are you sure? This will reverse the stock quantity added by this purchase and adjust the supplier balance.")) return;
            const res = await fetch(`../../api/controllers/SupplierController.php?action=deletePurchase&id=${id}`);
            const data = await res.json();
            if (data.success) {
                showToast("Purchase record deleted!");
                fetchPurchaseRecords();
                fetchSuppliers(); // Balance might have changed
            }
        }

        async function saveSupplier() {
            const payload = {
                id: document.getElementById('supId').value || null,
                name: document.getElementById('supName').value,
                contact: document.getElementById('supContact').value,
                email: document.getElementById('supEmail').value,
                address: document.getElementById('supAddress').value
            };
            const res = await fetch('../../api/controllers/SupplierController.php?action=saveSupplier', {
                method: 'POST', body: JSON.stringify(payload)
            });
            if ((await res.json()).success) {
                showToast(payload.id ? "Supplier updated!" : "Supplier saved!");
                closeModal('supplierModal');
                // Reset form
                document.getElementById('supId').value = '';
                document.getElementById('supName').value = '';
                document.getElementById('supContact').value = '';
                document.getElementById('supEmail').value = '';
                document.getElementById('supAddress').value = '';
                document.getElementById('supModalTitle').innerText = "Add New Supplier";
                
                // Refresh all tabs
                fetchSuppliers();
                fetchSuppliedBooks();
                fetchPurchaseRecords();
            }
        }

        async function saveBorrow() {
            const payload = {
                library_name: document.getElementById('borLib').value,
                book_title: document.getElementById('borTitle').value,
                author: document.getElementById('borAuthor').value,
                due_date: document.getElementById('borDue').value
            };
            const res = await fetch('../../api/controllers/SupplierController.php?action=saveExternalBorrow', {
                method: 'POST', body: JSON.stringify(payload)
            });
            if ((await res.json()).success) {
                showToast("Borrow logged successfully!");
                closeModal('borrowModal');
                // Refresh all tabs
                fetchExternalBorrows();
            }
        }

        function showToast(msg) {
            const toast = document.createElement('div');
            toast.className = 'toast';
            toast.innerText = msg;
            document.getElementById('toastContainer').appendChild(toast);
            setTimeout(() => toast.remove(), 3000);
        }

        window.onload = function () {
            fetchSuppliers();
            loadSuppliersForPurchase();
            
            // Set default date for purchase record modal
            const purDate = document.getElementById('purDate');
            if (purDate) purDate.valueAsDate = new Date();
            
            // Add initial item row
            const purBody = document.getElementById('purchaseItemsBody');
            if (purBody) {
                purBody.innerHTML = '';
                addPurchaseItemRow();
            }

            // Background sync - refresh active tab every 15 seconds
            setInterval(syncActiveTab, 15000);
        };
    </script>
</body>

</html>