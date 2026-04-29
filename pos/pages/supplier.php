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
            flex-wrap: wrap;
            gap: 1rem;
        }


        .page-title h1 {
            font-size: 1.5rem;
            margin-bottom: 4px;
            white-space: nowrap;
        }


        .page-title p {
            font-size: 0.9rem;
            color: var(--text-muted);
        }

        .table-wrapper {
            overflow-x: auto;
        }


        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.95rem;
        }

        th {
            text-align: left;
            padding: 1.2rem 1rem;
            color: var(--text-muted);
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.3px;
            white-space: nowrap;
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
            overflow-y: auto;
            padding: 2rem 1rem;
        }


        .modal-content {
            background: white;
            width: 450px;
            margin: 0 auto;
            border-radius: 28px;
            overflow: hidden;
            box-shadow: 0 25px 70px -10px rgba(15, 23, 42, 0.2);
            animation: modalPop 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            position: relative;
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
            padding: 5px 12px;
            border-radius: 30px;
            font-size: 0.75rem;
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
            padding: 10px 18px;
            border-radius: 12px;
            cursor: pointer;
            font-weight: 700;
            font-size: 0.85rem;
            transition: 0.3s;
            color: var(--text-muted);
            border: none;
            background: transparent;
            white-space: nowrap;
        }


        .tab-btn.active {
            background: white;
            color: var(--primary-blue);
            box-shadow: var(--shadow-sm);
        }

        /* Mobile Responsiveness */
        @media (max-width: 768px) {
            .tabs {
                width: 100%;
                overflow-x: auto;
                justify-content: flex-start;
            }
            .data-card {
                padding: 1rem;
                border-radius: 16px;
            }
            .card-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
            .card-header div:first-child {
                white-space: normal !important;
            }

            .modal-content {
                width: 95% !important;
                margin: 10px;
            }
            .page-title h1 {
                font-size: 1.3rem;
            }
            td, th {
                padding: 0.75rem 0.5rem;
                font-size: 0.8rem;
            }
            .modal-grid-3 {
                grid-template-columns: 1fr !important;
            }
            .modal-grid-2-1 {
                grid-template-columns: 1fr !important;
                gap: 20px !important;
            }
            .modal-grid-2 {
                grid-template-columns: 1fr !important;
            }
        }


        #invoiceModal { z-index: 2000; }
        #statementModal .modal-content { max-height: 95vh; }


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
                <p>Track product procurement and external library loans</p>
            </div>
        </header>

        <div class="tabs">
            <button class="tab-btn active" onclick="showTab('suppliers')">Suppliers List</button>
            <button class="tab-btn" onclick="showTab('purchaseRecords')">Purchase Records</button>
            <button class="tab-btn" onclick="showTab('externalBorrows')">External Borrows</button>
        </div>

        <!-- Suppliers Tab -->
        <div id="suppliersTab" class="tab-content">
            <div class="data-card">
                <div class="card-header">
                    <div style="font-weight: 800; font-size: 1.1rem; white-space: nowrap;">Registered Suppliers</div>
                    <button class="nav-link active" onclick="openSupplierModal()"
                        style="padding: 10px 22px; font-size: 0.85rem;">
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



        <!-- Purchase Records Tab -->
        <div id="purchaseRecordsTab" class="tab-content" style="display:none;">
            <div class="data-card">
                <div class="card-header" style="flex-wrap: nowrap; gap: 5px;">
                    <div style="font-weight: 800; font-size: 1.05rem; white-space: nowrap; flex-shrink: 1; overflow: hidden; text-overflow: ellipsis;">Item Purchase Records</div>
                    <div style="display: flex; gap: 6px; flex-wrap: nowrap; flex-shrink: 0;">
                        <button class="nav-link" onclick="openCategoryModal()"
                            style="padding: 6px 12px; font-size: 0.75rem; background: #f1f5f9; color: var(--text-muted); border: none; white-space: nowrap;">
                            <i class="fa-solid fa-tags"></i> CATS
                        </button>
                        <button class="nav-link active" onclick="openPurchaseModal()"
                            style="padding: 6px 12px; font-size: 0.75rem; white-space: nowrap;">
                            <i class="fa-solid fa-plus"></i> PURCHASE
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
                <div id="purchasePagination" style="margin-top: 1.5rem; display: flex; justify-content: center;">
                    <button onclick="loadAllPurchases()" id="btnLoadAllPurchases" class="nav-link" style="background: #f1f5f9; color: var(--text-header); border: none; padding: 10px 25px; font-size: 0.85rem; font-weight: 700; display: none; align-items: center; gap: 8px; border-radius: 12px; cursor: pointer;">
                        <i class="fa-solid fa-arrows-rotate"></i> LOAD ALL RECORDS
                    </button>
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
                <div class="modal-grid-3" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 25px;">
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
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; flex-wrap: wrap; gap: 10px;">
                        <h3 style="font-size: 0.95rem; font-weight: 800; color: var(--text-header); margin: 0;">Items List</h3>
                        <button type="button" onclick="addPurchaseItemRow()" class="nav-link" style="padding: 6px 12px; font-size: 0.75rem; border: 1px solid var(--primary-blue); color: var(--primary-blue); background: var(--primary-blue-soft);">
                            <i class="fa-solid fa-plus-circle"></i> Add More Item
                        </button>
                    </div>
                    <div style="max-height: 250px; overflow-y: auto; overflow-x: auto;">
                        <table style="width: 100%; min-width: 600px;" id="purchaseItemsTable">
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


                <div class="modal-grid-2-1" style="display: grid; grid-template-columns: 2fr 1fr; gap: 40px; align-items: end;">
                    <div class="modal-grid-2" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px;">

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
                        <div class="input-group" style="grid-column: span 1;">
                            <label style="font-size: 0.75rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase;">Observation / Note</label>
                            <input type="text" id="purNote" class="checkout-input" placeholder="Any order details or reference numbers...">
                        </div>
                        <div class="input-group" style="grid-column: span 1; flex-direction: row; align-items: center; gap: 10px; padding-top: 25px;">
                            <input type="checkbox" id="purUpdateStock" checked style="width: 20px; height: 20px; cursor: pointer;">
                            <label for="purUpdateStock" style="font-size: 0.85rem; font-weight: 700; color: var(--text-header); cursor: pointer;">Update Inventory Stock</label>
                        </div>
                    </div>

                    <div style="background: var(--text-header); color: white; padding: 1.5rem; border-radius: 20px; text-align: right;">
                        <div style="font-size: 0.7rem; font-weight: 800; opacity: 0.7; margin-bottom: 5px; text-transform: uppercase;">Total Purchase Amount</div>
                        <div id="purTotalDisplay" style="font-size: 1.75rem; font-weight: 800; font-family: 'Inter', sans-serif;">৳0.00</div>
                    </div>
                </div>

                <div style="margin-top: 2rem; display: flex; gap: 15px; flex-wrap: wrap;">
                    <button type="button" onclick="closeModal('purchaseRecordModal')" class="nav-link" style="flex: 1; min-width: 120px; justify-content: center; background: #f1f5f9; color: var(--text-muted); border: none;">Discard</button>
                    <button type="button" onclick="savePurchaseRecord()" id="btnSavePurchase" class="nav-link active" style="flex: 2; min-width: 200px; justify-content: center; padding: 12px; font-weight: 800;">
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

    <!-- View Invoice Modal -->
    <div class="modal-overlay" id="invoiceModal">
        <div class="modal-content" style="width: 80%; max-width: 800px; padding: 0; border-radius: 20px; overflow: hidden;">
            <div style="background: #1e293b; color: white; padding: 2rem; display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h2 style="margin:0; font-size: 1.5rem; font-weight: 800;">Purchase Invoice</h2>
                    <p id="invNumber" style="margin: 5px 0 0; color: #94a3b8; font-family: monospace; font-size: 0.85rem; font-weight: 600;"></p>
                </div>
                <button onclick="closeModal('invoiceModal')" style="background: rgba(255,255,255,0.1); border:none; color:white; width:40px; height:40px; border-radius:12px; cursor:pointer; font-size:1.2rem;">&times;</button>
            </div>
            
            <div style="padding: 2rem; background: #fff;">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2rem;">
                    <div>
                        <div style="font-size: 0.7rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; margin-bottom: 8px;">Supplier Information</div>
                        <div id="invSupplierName" style="font-size: 1.1rem; font-weight: 800; color: var(--text-header);"></div>
                        <div id="invSupplierContact" style="font-size: 0.85rem; color: var(--text-muted); margin-top: 4px;"></div>
                    </div>
                    <div style="text-align: right;">
                        <div style="font-size: 0.7rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; margin-bottom: 8px;">Transaction Details</div>
                        <div id="invDate" style="font-size: 1rem; font-weight: 700; color: var(--text-header);"></div>
                        <div id="invStatus" style="display: inline-block; margin-top: 8px; font-size: 0.7rem; font-weight: 800; padding: 4px 12px; border-radius: 20px; text-transform: uppercase;"></div>
                    </div>
                </div>

                <div class="table-wrapper" style="border: 1px solid #f1f5f9; border-radius: 12px; margin-bottom: 2rem;">
                    <table style="background: transparent;">
                        <thead style="background: #f8fafc;">
                            <tr>
                                <th style="padding: 12px 15px;">Item Description</th>
                                <th style="padding: 12px 15px;">ISBN/SKU</th>
                                <th style="padding: 12px 15px; text-align: center;">Qty</th>
                                <th style="padding: 12px 15px; text-align: right;">Unit Cost</th>
                                <th style="padding: 12px 15px; text-align: right;">Total</th>
                            </tr>
                        </thead>
                        <tbody id="invoiceItemsBody"></tbody>
                    </table>
                </div>

                <div style="display: flex; justify-content: flex-end;">
                    <div style="width: 250px; background: #f8fafc; padding: 1.5rem; border-radius: 16px;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 0.9rem;">
                            <span style="color: var(--text-muted);">Subtotal</span>
                            <span id="invSubtotal" style="font-weight: 700;"></span>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 0.9rem; color: #16a34a;">
                            <span>Paid Amount</span>
                            <span id="invPaid" style="font-weight: 700;"></span>
                        </div>
                        <div style="border-top: 2px dashed #e2e8f0; margin: 10px 0; padding-top: 10px; display: flex; justify-content: space-between; font-size: 1.1rem;">
                            <span style="font-weight: 800; color: var(--text-header);">Balance Due</span>
                            <span id="invDue" style="font-weight: 900; color: #e11d48;"></span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div style="padding: 1.5rem 2rem; background: #f8fafc; border-top: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center;">
                <div style="font-size: 0.8rem; color: var(--text-muted); font-style: italic;">
                    Note: <span id="invNote"></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Supplier Statement Modal -->
    <div class="modal-overlay" id="statementModal">
        <div class="modal-content" style="width: 950px; max-width: 95%; max-height: 90vh; overflow: hidden; display: flex; flex-direction: column; padding: 0;">
            <div style="padding: 1.5rem 2rem; background: #1e293b; color: white; display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h2 style="margin:0; font-size: 1.3rem; font-weight: 800;"><i class="fa-solid fa-file-invoice-dollar"></i> Supplier Statement</h2>
                    <p id="statementSupplierName" style="margin: 5px 0 0; color: #94a3b8; font-size: 0.9rem; font-weight: 600;"></p>
                </div>
                <button onclick="closeModal('statementModal')" style="background: rgba(255,255,255,0.1); border:none; color:white; width:40px; height:40px; border-radius:12px; cursor:pointer; font-size:1.2rem;">&times;</button>
            </div>

            <div style="padding: 2rem; overflow-y: auto; flex-grow: 1; background: #f8fafc;">
                <!-- Purchase History Section -->
                <div style="margin-bottom: 2.5rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                        <h3 style="margin:0; font-size: 1rem; font-weight: 800; color: #1e293b; display: flex; align-items: center; gap: 10px;">
                            <span style="width: 8px; height: 20px; background: #6366f1; border-radius: 4px;"></span>
                            Purchase History
                        </h3>
                    </div>
                    <div class="table-wrapper" style="border: 1px solid #e2e8f0; border-radius: 12px; background: white;">
                        <table>
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Category</th>
                                    <th>Total Cost</th>
                                    <th>Paid</th>
                                    <th>Status</th>
                                    <th style="text-align:right;">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="statementPurchasesBody"></tbody>
                        </table>
                    </div>
                </div>

                <!-- Payment History Section -->
                <div>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                        <h3 style="margin:0; font-size: 1rem; font-weight: 800; color: #1e293b; display: flex; align-items: center; gap: 10px;">
                            <span style="width: 8px; height: 20px; background: #10b981; border-radius: 4px;"></span>
                            Payment History
                        </h3>
                    </div>
                    <div class="table-wrapper" style="border: 1px solid #e2e8f0; border-radius: 12px; background: white;">
                        <table>
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Amount</th>
                                    <th>Method</th>
                                    <th>Notes</th>
                                    <th style="text-align:right;">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="statementPaymentsBody"></tbody>
                        </table>
                    </div>
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
                            <button onclick="viewSupplierStatement(${s.id}, '${s.name.replace(/'/g, "\\'")}')" 
                                style="padding: 8px 14px; font-size: 0.7rem; border:none; border-radius:10px; background:#6366f1; color:white; font-weight:800; cursor:pointer; margin-right:5px; transition:0.3s;" title="View Statement">
                                <i class="fa-solid fa-eye"></i> STATEMENT
                            </button>
                            <button onclick="openPayModal('${s.id}', '${s.name.replace(/'/g, "\\'")}', ${s.total_due})" 
                                style="padding: 8px 14px; font-size: 0.7rem; border:none; border-radius:10px; background:#10b981; color:white; font-weight:800; cursor:pointer; transition:0.3s;" title="Make Payment">
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
                fetchPurchaseRecords();
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

        function openPayModal(id, name, due) {
            activeSupplierId = id;
            document.getElementById('payDueDisplay').innerText = "৳" + due;
            document.getElementById('payAmount').value = due;
            openModal('payModal');
            document.querySelector('#payModal h2').innerText = "Payment to: " + name;
        }

        async function viewSupplierStatement(id, name) {
            document.getElementById('statementSupplierName').innerText = name;
            openModal('statementModal');
            
            // Fetch Purchases
            const resP = await fetch(`../../api/controllers/SupplierController.php?action=listPurchaseRecords&supplier_id=${id}`);
            const dataP = await resP.json();
            if (dataP.success) {
                const records = dataP.records || [];
                document.getElementById('statementPurchasesBody').innerHTML = records.map(r => {
                    let statusClass = r.payment_status === 'Paid' ? 'status-active' : 'status-overdue';
                    return `
                        <tr>
                            <td>${r.purchase_date}</td>
                            <td>${r.category}</td>
                            <td style="font-weight:700;">৳${parseFloat(r.total_amount).toLocaleString()}</td>
                            <td style="color:#10b981;">৳${parseFloat(r.paid_amount).toLocaleString()}</td>
                            <td><span class="status-badge ${statusClass}">${r.payment_status}</span></td>
                            <td style="text-align:right;">
                                <button class="btn-fund" style="background:#f1f5f9; color:var(--text-header); padding:6px 10px; border:none; border-radius:8px; cursor:pointer;" onclick="viewInvoice(${r.id})">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                    `;
                }).join('') || '<tr><td colspan="6" style="text-align:center; padding:20px; color:var(--text-muted);">No purchases found.</td></tr>';
            }

            // Fetch Payments
            const resPay = await fetch(`../../api/controllers/SupplierController.php?action=listSupplierPayments&supplier_id=${id}`);
            const dataPay = await resPay.json();
            if (dataPay.success) {
                const payments = dataPay.payments || [];
                document.getElementById('statementPaymentsBody').innerHTML = payments.map(p => `
                    <tr>
                        <td>${new Date(p.payment_date).toLocaleDateString()}</td>
                        <td style="font-weight:700; color:#10b981;">৳${parseFloat(p.amount).toLocaleString()}</td>
                        <td>${p.method}</td>
                        <td style="font-size:0.8rem; color:var(--text-muted);">${p.notes || '-'}</td>
                        <td style="text-align:right;">
                            <button class="btn-fund" style="background:#fff1f2; color:#ef4444; padding:6px 10px; border:none; border-radius:8px; cursor:pointer;" onclick="deletePayment(${p.id}, ${id}, '${name.replace(/'/g, "\\'")}')">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        </td>
                    </tr>
                `).join('') || '<tr><td colspan="5" style="text-align:center; padding:20px; color:var(--text-muted);">No payments found.</td></tr>';
            }
        }

        async function deletePayment(id, supplierId, name) {
            if (!confirm("Are you sure you want to delete this payment record? This will increase the supplier due.")) return;
            const res = await fetch(`../../api/controllers/SupplierController.php?action=deletePayment&id=${id}`);
            if ((await res.json()).success) {
                showToast("Payment record deleted.");
                viewSupplierStatement(supplierId, name);
                fetchSuppliers();
            }
        }

        let allPurchaseRecords = [];
        let purchaseDisplayLimit = 25;

        async function fetchPurchaseRecords() {
            const res = await fetch('../../api/controllers/SupplierController.php?action=listPurchaseRecords');
            const data = await res.json();
            if (data.success) {
                allPurchaseRecords = data.records || [];
                renderPurchaseRecords();
            }
        }

        function renderPurchaseRecords() {
            const records = allPurchaseRecords.slice(0, purchaseDisplayLimit);
            const container = document.getElementById('purchaseRecordsTableBody');
            
            if (records.length > 0) {
                container.innerHTML = records.map(r => {
                    let statusClass = r.payment_status === 'Paid' ? 'status-active' : 'status-overdue';
                    return `
                        <tr>
                            <td style="white-space: nowrap;">${r.purchase_date}</td>
                            <td style="font-weight:700;">${r.category || 'General'}</td>
                            <td style="font-weight:700;">৳${parseFloat(r.total_amount).toLocaleString()}</td>
                            <td style="color:#10b981;">৳${parseFloat(r.paid_amount).toLocaleString()}</td>
                            <td><span class="status-badge ${statusClass}">${r.payment_status}</span></td>
                            <td>${r.supplier_name || 'N/A'}</td>
                            <td style="text-align:right; white-space:nowrap; display:flex; gap:5px; justify-content:flex-end;">
                                <button class="btn-fund" style="background:#6366f1; color:white; padding:6px 10px; font-size:0.75rem; border:none; border-radius:8px; cursor:pointer;" onclick="viewInvoice(${r.id})">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                                <button class="btn-fund" style="background:#f1f5f9; color:var(--text-header); padding:6px 10px; font-size:0.75rem; border:none; border-radius:8px; cursor:pointer;" onclick="editPurchase(${r.id})">
                                    <i class="fa-solid fa-edit"></i>
                                </button>
                                <button class="btn-fund" style="background:#fff1f2; color:#ef4444; padding:6px 10px; font-size:0.75rem; border:none; border-radius:8px; cursor:pointer;" onclick="deletePurchase(${r.id})">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </td>
                        </tr>
                    `;
                }).join('');

                const btnLoadAll = document.getElementById('btnLoadAllPurchases');
                if (allPurchaseRecords.length > purchaseDisplayLimit) {
                    btnLoadAll.style.display = 'flex';
                } else {
                    btnLoadAll.style.display = 'none';
                }
            } else {
                container.innerHTML = '<tr><td colspan="8" style="text-align:center; padding: 40px; color: var(--text-muted);">No records found. Click "Add Purchase" to create one.</td></tr>';
                document.getElementById('btnLoadAllPurchases').style.display = 'none';
            }
        }

        function loadAllPurchases() {
            purchaseDisplayLimit = allPurchaseRecords.length;
            renderPurchaseRecords();
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
                    <td style="padding: 5px;"><input type="text" class="checkout-input pur-item-isbn" oninput="findItemBySku(this)" placeholder="123456" style="padding: 8px 12px; font-size: 0.85rem;"></td>
                    <td style="padding: 5px;"><input type="number" class="checkout-input pur-item-cost" onkeyup="calculatePurchaseTotal()" placeholder="0.00" style="padding: 8px 12px; font-size: 0.85rem;"></td>
                    <td style="padding: 5px;"><input type="number" class="checkout-input pur-item-qty" onkeyup="calculatePurchaseTotal()" value="1" min="1" style="padding: 8px 12px; font-size: 0.85rem;"></td>
                    <td style="padding: 5px; text-align: center;"><button type="button" onclick="removePurchaseItemRow('${rowId}')" style="background:none; border:none; color:#ef4444; cursor:pointer;"><i class="fa-solid fa-trash"></i></button></td>
                </tr>
            `;
            body.insertAdjacentHTML('beforeend', row);
        }

        async function findItemBySku(input) {
            const sku = input.value.trim();
            const row = input.closest('tr');
            const nameField = row.querySelector('.pur-item-name');
            const costField = row.querySelector('.pur-item-cost');

            if (sku.length < 3) {
                nameField.disabled = false;
                return;
            }

            try {
                const res = await fetch(`../../api/controllers/SupplierController.php?action=findItemBySku&sku=${encodeURIComponent(sku)}`);
                const data = await res.json();
                if (data.success && data.item) {
                    nameField.value = data.item.name;
                    nameField.disabled = true;
                    if (!costField.value || costField.value == 0) costField.value = data.item.cost;
                    calculatePurchaseTotal();
                } else {
                    nameField.disabled = false;
                }
            } catch (e) {
                nameField.disabled = false;
            }
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
                update_stock: document.getElementById('purUpdateStock').checked,
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
            document.getElementById('purUpdateStock').checked = true;
            document.getElementById('purchaseItemsBody').innerHTML = '';
            document.getElementById('purModalTitle').innerHTML = '<i class="fa-solid fa-cart-shopping"></i> Purchase Record';
            document.getElementById('btnSavePurchase').innerHTML = '<i class="fa-solid fa-save"></i> Save Purchase Record';
            addPurchaseItemRow();
            calculatePurchaseTotal();
            openModal('purchaseRecordModal');
        }

        async function viewInvoice(id) {
            const res = await fetch(`../../api/controllers/SupplierController.php?action=getPurchaseDetails&id=${id}`);
            const data = await res.json();
            if (data.success) {
                const p = data.purchase;
                const items = data.items;

                document.getElementById('invNumber').innerText = `INV #${p.id.toString().padStart(5, '0')}`;
                document.getElementById('invSupplierName').innerText = p.supplier_name || 'N/A';
                document.getElementById('invSupplierContact').innerText = p.supplier_contact || '';
                document.getElementById('invDate').innerText = new Date(p.purchase_date).toLocaleDateString('en-GB', { day: 'numeric', month: 'long', year: 'numeric' });
                
                const statusEl = document.getElementById('invStatus');
                statusEl.innerText = p.payment_status;
                statusEl.style.background = p.payment_status === 'Paid' ? '#f0fdf4' : '#fff1f2';
                statusEl.style.color = p.payment_status === 'Paid' ? '#16a34a' : '#e11d48';

                document.getElementById('invoiceItemsBody').innerHTML = items.map(item => `
                    <tr>
                        <td style="padding: 12px 15px; font-weight: 700; color: var(--text-header);">${item.item_name}</td>
                        <td style="padding: 12px 15px; color: var(--text-muted); font-family: monospace;">${item.isbn || '-'}</td>
                        <td style="padding: 12px 15px; text-align: center; font-weight: 700;">${item.quantity}</td>
                        <td style="padding: 12px 15px; text-align: right;">৳${parseFloat(item.unit_cost).toLocaleString()}</td>
                        <td style="padding: 12px 15px; text-align: right; font-weight: 700; color: var(--primary-blue);">৳${parseFloat(item.total_item_cost).toLocaleString()}</td>
                    </tr>
                `).join('');

                const total = parseFloat(p.total_amount);
                const paid = parseFloat(p.paid_amount);
                const due = total - paid;

                document.getElementById('invSubtotal').innerText = `৳${total.toLocaleString()}`;
                document.getElementById('invPaid').innerText = `৳${paid.toLocaleString()}`;
                document.getElementById('invDue').innerText = `৳${due.toLocaleString()}`;
                document.getElementById('invNote').innerText = p.note || 'No additional notes provided.';

                openModal('invoiceModal');
            }
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
                    const nameInp = lastRow.querySelector('.pur-item-name');
                    const isbnInp = lastRow.querySelector('.pur-item-isbn');
                    
                    nameInp.value = item.item_name;
                    isbnInp.value = item.isbn || '';
                    lastRow.querySelector('.pur-item-cost').value = item.unit_cost;
                    lastRow.querySelector('.pur-item-qty').value = item.quantity;

                    if (item.isbn) {
                        nameInp.disabled = true;
                    }
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