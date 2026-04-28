<?php require_once '../../api/shared/auth_check.php'; checkAuth(true); renderUserUI(true); ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>General Inventory | Ontomeel POS System</title>
    <link rel="stylesheet" href="../assets/pos-styles.css?v=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap"
        rel="stylesheet">
    
    <style>
        /* ---- Stats Bar ---- */
        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            border-radius: 16px;
            padding: 1.25rem 1.5rem;
            border: 1px solid var(--border-light);
            box-shadow: var(--shadow-sm);
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .stat-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            flex-shrink: 0;
        }

        .stat-value {
            font-size: 1.5rem;
            font-weight: 900;
            color: var(--text-header);
            line-height: 1;
        }

        .stat-label {
            font-size: 0.72rem;
            font-weight: 700;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 3px;
        }

        /* ---- Toolbar ---- */
        .toolbar {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
        }

        .search-input-wrap {
            flex: 1;
            min-width: 200px;
            position: relative;
        }

        .search-input-wrap i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
        }

        .search-input-wrap input {
            width: 100%;
            padding: 11px 14px 11px 40px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-family: inherit;
            font-size: 0.9rem;
            background: white;
            transition: 0.2s;
        }

        .search-input-wrap input:focus {
            outline: none;
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 4px #eff6ff;
        }

        .filter-pills {
            display: flex;
            gap: 8px;
        }

        .fpill {
            padding: 8px 16px;
            border-radius: 20px;
            border: 2px solid #e2e8f0;
            background: white;
            color: var(--text-muted);
            font-size: 0.78rem;
            font-weight: 700;
            cursor: pointer;
            transition: 0.2s;
            white-space: nowrap;
        }

        .fpill.active,
        .fpill:hover {
            background: var(--primary-blue);
            color: white;
            border-color: var(--primary-blue);
        }

        /* ---- Grid ---- */
        .inv-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 1.25rem;
        }

        /* ---- Cards ---- */
        .inv-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            border: 1px solid var(--border-light);
            box-shadow: var(--shadow-sm);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            flex-direction: column;
        }

        .inv-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-md);
            border-color: #cbd5e1;
        }

        .card-header {
            padding: 1.25rem 1.25rem 0;
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
        }

        .type-pill {
            font-size: 0.65rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            padding: 4px 10px;
            border-radius: 20px;
        }

        .type-Stationary {
            background: #eff6ff;
            color: #2563eb;
        }

        .type-Flowers {
            background: #fdf4ff;
            color: #9333ea;
        }

        .type-Accessories {
            background: #fffbeb;
            color: #d97706;
        }

        .type-General {
            background: #f0fdf4;
            color: #16a34a;
        }

        .type-icon-wrap {
            width: 46px;
            height: 46px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
        }

        .card-body {
            padding: 1rem 1.25rem 1.25rem;
            flex: 1;
        }

        .card-title {
            font-size: 1rem;
            font-weight: 800;
            color: var(--text-header);
            margin: 0 0 4px;
            line-height: 1.3;
        }

        .card-sku {
            font-size: 0.72rem;
            color: var(--text-muted);
            font-family: monospace;
            margin-bottom: 1rem;
        }

        .card-footer {
            border-top: 1px solid #f1f5f9;
            padding: 1rem 1.25rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .stock-chip {
            font-size: 0.78rem;
            font-weight: 800;
            padding: 4px 10px;
            border-radius: 8px;
        }

        .stock-ok {
            background: #f0fdf4;
            color: #16a34a;
        }

        .stock-low {
            background: #fff1f2;
            color: #e11d48;
        }

        .price-tag {
            font-size: 1.15rem;
            font-weight: 900;
            color: var(--primary-blue);
        }

        .card-actions {
            display: flex;
            gap: 6px;
            padding: 0 1.25rem 1rem;
        }

        .card-btn {
            flex: 1;
            padding: 8px;
            border-radius: 10px;
            border: none;
            font-size: 0.75rem;
            font-weight: 700;
            cursor: pointer;
            transition: 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
        }

        .card-btn.edit {
            background: #eff6ff;
            color: var(--primary-blue);
        }

        .card-btn.edit:hover {
            background: #dbeafe;
        }

        .card-btn.del {
            background: #fff1f2;
            color: #e11d48;
        }

        .card-btn.del:hover {
            background: #ffe4e6;
        }

        .card-btn.stock-btn {
            background: #f0fdf4;
            color: #16a34a;
        }

        .card-btn.stock-btn:hover {
            background: #dcfce7;
        }

        /* ---- Skeleton ---- */
        .skel {
            background: linear-gradient(90deg, #f1f5f9 25%, #e2e8f0 50%, #f1f5f9 75%);
            background-size: 200% 100%;
            animation: skel-anim 1.4s infinite;
            border-radius: 8px;
        }

        @keyframes skel-anim {
            0% {
                background-position: 200% 0
            }

            100% {
                background-position: -200% 0
            }
        }

        /* ---- Add/Edit Modal ---- */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.55);
            backdrop-filter: blur(6px);
            z-index: 1000;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }

        .modal-box {
            background: white;
            padding: 2.5rem;
            border-radius: 24px;
            width: 100%;
            max-width: 520px;
            box-shadow: 0 30px 60px -12px rgba(0, 0, 0, 0.3);
            animation: modalIn 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        @keyframes modalIn {
            from {
                transform: scale(0.9);
                opacity: 0
            }

            to {
                transform: scale(1);
                opacity: 1
            }
        }

        .modal-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .modal-head h3 {
            margin: 0;
            font-size: 1.4rem;
            font-weight: 800;
            color: var(--text-header);
        }

        .modal-close {
            background: #f1f5f9;
            border: none;
            width: 36px;
            height: 36px;
            border-radius: 10px;
            font-size: 1.1rem;
            color: var(--text-muted);
            cursor: pointer;
            transition: 0.2s;
        }

        .modal-close:hover {
            background: #e2e8f0;
            color: var(--text-header);
        }

        .form-row {
            display: grid;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .form-row.two {
            grid-template-columns: 1fr 1fr;
        }

        .form-field {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .form-field label {
            font-size: 0.75rem;
            font-weight: 700;
            color: var(--text-header);
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        .form-field input,
        .form-field select {
            padding: 11px 14px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-family: inherit;
            font-size: 0.9rem;
            transition: 0.2s;
            background: white;
        }

        .form-field input:focus,
        .form-field select:focus {
            outline: none;
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 4px #eff6ff;
        }

        .btn-save {
            background: var(--primary-blue);
            color: white;
            border: none;
            padding: 14px;
            border-radius: 12px;
            font-weight: 800;
            font-size: 0.95rem;
            cursor: pointer;
            width: 100%;
            margin-top: 1.25rem;
            transition: 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-save:hover {
            background: #1d4ed8;
        }

        .btn-save:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        /* ---- Toast ---- */
        #invToast {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 2000;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .toast-msg {
            background: rgba(15, 23, 42, 0.92);
            backdrop-filter: blur(10px);
            color: white;
            padding: 14px 20px;
            border-radius: 14px;
            font-size: 0.88rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            animation: toastIn 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            min-width: 260px;
        }

        .toast-msg.success {
            border-left: 4px solid #10b981;
        }

        .toast-msg.error {
            border-left: 4px solid #ef4444;
        }

        .toast-msg.info {
            border-left: 4px solid var(--primary-blue);
        }

        @keyframes toastIn {
            from {
                transform: translateX(100%);
                opacity: 0
            }

            to {
                transform: translateX(0);
                opacity: 1
            }
        }

        /* ---- Add btn ---- */
        .btn-add {
            background: var(--primary-blue);
            color: white;
            border: none;
            padding: 12px 22px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 0.88rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: 0.2s;
            white-space: nowrap;
        }

        .btn-add:hover {
            background: #1d4ed8;
            transform: translateY(-1px);
        }

        /* ---- Book Form Components ---- */
        .bk-section-label {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.68rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: #b45309;
            margin-bottom: 4px;
        }
        .bk-section-line {
            display: inline-block;
            width: 28px;
            height: 2px;
            background: #fde68a;
            border-radius: 2px;
            flex-shrink: 0;
        }
        .bk-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
        .bk-grid-3 { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; }
        .bk-grid-4 { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; }
        .bk-span-2 { grid-column: span 2; }
        .bk-group { display: flex; flex-direction: column; gap: 5px; }
        .bk-label {
            font-size: 0.68rem;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.6px;
        }
        .bk-req { color: #ef4444; }
        .bk-field {
            width: 100%;
            box-sizing: border-box;
            padding: 11px 14px;
            background: #f8fafc;
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            font-family: inherit;
            font-size: 0.88rem;
            color: #1e293b;
            transition: border-color 0.18s, box-shadow 0.18s;
            outline: none;
        }
        .bk-field:focus {
            border-color: #3b82f6;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(59,130,246,0.12);
        }
        .bk-field-price {
            color: #2563eb;
            font-weight: 800;
            background: #eff6ff;
            border-color: #bfdbfe;
        }
        .bk-field-price:focus { border-color: #2563eb; }
        /* Toggle card */
        .bk-toggle-card {
            flex: 1;
            background: #f8fafc;
            border: 1.5px solid #e2e8f0;
            border-radius: 14px;
            padding: 16px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            cursor: pointer;
            transition: border-color 0.18s;
            user-select: none;
        }
        .bk-toggle-card:hover { border-color: #cbd5e1; }
        .bk-toggle-title { font-size: 0.85rem; font-weight: 700; color: #1e293b; margin: 0 0 3px; }
        .bk-toggle-sub { font-size: 0.72rem; color: #94a3b8; margin: 0; }
        .bk-switch { position: relative; display: inline-block; width: 44px; height: 24px; flex-shrink: 0; }
        .bk-switch input { opacity: 0; width: 0; height: 0; }
        .bk-slider {
            position: absolute;
            inset: 0;
            background: #cbd5e1;
            border-radius: 24px;
            transition: 0.25s;
            cursor: pointer;
        }
        .bk-slider:before {
            content: '';
            position: absolute;
            width: 18px;
            height: 18px;
            left: 3px;
            top: 3px;
            background: white;
            border-radius: 50%;
            transition: 0.25s;
            box-shadow: 0 1px 3px rgba(0,0,0,0.2);
        }
        .bk-switch input:checked + .bk-slider { background: #1e293b; }
        .bk-switch input:checked + .bk-slider:before { transform: translateX(20px); }
        .book-only.hidden { display: none !important; }
        
        
        
        @media print {
            body > *:not(#barcodePrintSection) { 
                display: none !important; 
            }
            
            #barcodePrintSection {
                display: flex !important;
                visibility: visible !important;
                position: fixed !important;
                left: 0 !important;
                top: 0 !important;
                width: 35mm !important;
                height: 25mm !important;
                margin: 0 !important;
                padding: 0 !important;
                background: white !important;
                justify-content: center !important;
                align-items: center !important;
            }
            
            #barcodePrintSection * { 
                visibility: visible !important; 
            }

            @page {
                size: 35mm 25mm;
                margin: 0;
            }
            
            html, body {
                margin: 0 !important;
                padding: 0 !important;
                width: 35mm !important;
                height: 25mm !important;
                overflow: hidden !important;
            }
        }
        
        #barcodePrintSection { display: none; }

        .barcode-label {
            width: 35mm;
            height: 25mm;
            padding: 1mm;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: space-between;
            box-sizing: border-box;
            background: white;
            color: black;
            font-weight: 800;
            font-family: 'Inter', sans-serif;
            text-align: center;
        }
        .barcode-brand { font-size: 10px; font-weight: 900; text-transform: uppercase; margin-bottom: 1px; }
        .barcode-name { font-size: 10px; font-weight: 700; line-height: 1; max-height: 16px; overflow: hidden; margin-bottom: 1px; }
        .barcode-svg { width: 100% !important; height: auto !important; max-height: 10mm; }
        .barcode-price { font-size: 11px; font-weight: 900; margin-top: 1px; }

        @media (max-width: 640px) {
            .bk-grid-2, .bk-grid-3, .bk-grid-4 { grid-template-columns: 1fr; }
            .bk-span-2 { grid-column: span 1; }
        }
    </style>
</head>

<body>

    <script src="../assets/sidebar.js"></script>

    <div class="main-wrapper">
        <header>
            <div class="page-title">
                <h1>General <span style="color: var(--primary-blue);">Inventory</span></h1>
                <p>Products, stationary, flowers &amp; more — sold directly from the terminal</p>
            </div>
            <div class="header-tools" style="display: flex; align-items: center; gap: 1rem;">
                <div class="user-profile" style="display: flex; align-items: center; gap: 10px;">
                    <div style="text-align: right;">
                        <div style="font-size: 0.85rem; font-weight: 700; color: var(--text-header);"
                            class="user-name-display">Admin</div>
                        <div
                            style="font-size: 0.7rem; color: var(--accent-mint); font-weight: 800; text-transform: uppercase;">
                            Staff</div>
                    </div>
                </div>
                <a href="product-import.php" class="btn-add" style="background: #6366f1; text-decoration: none;">
                    <i class="fa-solid fa-file-import"></i> IMPORT
                </a>
                <button class="btn-add" onclick="openModal()">
                    <i class="fa-solid fa-plus"></i> ADD ITEM
                </button>
            </div>
        </header>

        <!-- Stats Bar -->
        <div class="stats-row" id="statsRow">
            <div class="stat-card">
                <div class="stat-icon" style="background:#eff6ff; color:#2563eb;"><i
                        class="fa-solid fa-boxes-stacked"></i></div>
                <div>
                    <div class="stat-value" id="statTotal">—</div>
                    <div class="stat-label">Total Items</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:#f0fdf4; color:#16a34a;"><i
                        class="fa-solid fa-circle-check"></i></div>
                <div>
                    <div class="stat-value" id="statInStock">—</div>
                    <div class="stat-label">In Stock</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:#fff1f2; color:#e11d48;"><i
                        class="fa-solid fa-triangle-exclamation"></i></div>
                <div>
                    <div class="stat-value" id="statLow">—</div>
                    <div class="stat-label">Low / Out</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:#fdf4ff; color:#9333ea;"><i class="fa-solid fa-tag"></i></div>
                <div>
                    <div class="stat-value" id="statTypes">—</div>
                    <div class="stat-label">Categories</div>
                </div>
            </div>
        </div>

        <!-- Toolbar -->
        <div class="toolbar">
            <div class="search-input-wrap">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" id="searchInv" placeholder="Search items by name or SKU…" oninput="filterItems()">
            </div>
            <div class="filter-pills-wrap" style="flex: 1; overflow: hidden; position: relative;">
                <div class="filter-pills" id="filterPills" style="display: flex; gap: 8px; overflow-x: auto; padding-bottom: 4px; scroll-behavior: smooth;">
                    <button class="fpill active" onclick="setTypeFilter('all', this)">All</button>
                    <!-- Dynamic pills -->
                </div>
            </div>
        </div>

        <div class="inv-grid" id="inventoryGrid">
            <!-- skeleton placeholders -->
        </div>
    </div>

    <!-- Add/Edit Modal -->
    <div class="modal-overlay" id="addModal" style="display:none; overflow-y:auto; padding:24px 16px; align-items:flex-start;">
        <div id="modalBox" style="width:100%; max-width:900px; margin:0 auto; background:#fff; border-radius:20px; box-shadow:0 30px 80px -12px rgba(0,0,0,0.25); overflow:hidden;">

            <!-- Header -->
            <div style="background:#1e293b; padding:24px 32px; display:flex; align-items:center; justify-content:space-between;">
                <div>
                    <h2 id="modalTitle" style="margin:0; font-size:1.4rem; font-weight:800; color:#fff;">Add New Book</h2>
                    <p style="margin:5px 0 0; font-size:0.65rem; color:#fbbf24; font-weight:700; text-transform:uppercase; letter-spacing:1.5px;">Comprehensive Inventory Management</p>
                </div>
                <button onclick="closeModal()" style="background:rgba(255,255,255,0.12); border:none; color:#fff; width:38px; height:38px; border-radius:10px; cursor:pointer; font-size:1rem; transition:0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.22)'" onmouseout="this.style.background='rgba(255,255,255,0.12)'">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <!-- Form -->
            <form id="addItemForm" onsubmit="handleSave(event)" style="padding:28px 32px; display:flex; flex-direction:column; gap:24px;">
                <input type="hidden" id="editId" name="id" value="">
                <input type="hidden" id="editSourceTable" name="source_table" value="">

                <!-- Section 1: Basic Info -->
                <div>
                    <div class="bk-section-label"><span class="bk-section-line"></span><span>Basic Information</span></div>
                    <div class="bk-grid-2" style="margin-top:14px;">
                        <div class="bk-group">
                            <label class="bk-label">Title (Main)</label>
                            <input type="text" name="title" id="itemTitle" placeholder="Enter item name" class="bk-field">
                        </div>
                        <div class="bk-group book-only">
                            <label class="bk-label">Title (English)</label>
                            <input type="text" name="title_en" id="itemTitleEn" placeholder="Enter English title" class="bk-field">
                        </div>
                        <div class="bk-group book-only">
                            <label class="bk-label">Subtitle / Series</label>
                            <input type="text" name="subtitle" id="itemSubtitle" placeholder="e.g. Part 1, Special Edition" class="bk-field">
                        </div>
                        <div class="bk-group">
                            <label class="bk-label">Item Category</label>
                            <select name="item_type" id="itemType" class="bk-field">
                                <!-- Dynamic Options -->
                            </select>
                        </div>
                        <div class="bk-group book-only">
                            <label class="bk-label">Genre</label>
                            <input type="text" name="genre" id="itemGenre" placeholder="e.g. Thriller, Biography" class="bk-field">
                        </div>
                        <div class="bk-group book-only">
                            <label class="bk-label">Language</label>
                            <input type="text" name="language" id="itemLanguage" placeholder="e.g. Bangla, English" class="bk-field">
                        </div>
                        <div class="bk-group bk-span-2">
                            <label class="bk-label">Description</label>
                            <textarea name="description" id="itemDescription" rows="3" placeholder="Write a summary or notes..." class="bk-field" style="resize:none;"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Authors & Publication -->
                <div>
                    <div class="bk-section-label book-only" id="secPubLabel"><span class="bk-section-line"></span><span>Authors &amp; Publication</span></div>
                    <div class="bk-grid-3" style="margin-top:14px;">
                        <div class="bk-group book-only">
                            <label class="bk-label">Author (Bangla)</label>
                            <input type="text" name="author" id="itemAuthor" placeholder="লেখকের নাম" class="bk-field">
                        </div>
                        <div class="bk-group book-only">
                            <label class="bk-label">Author (English)</label>
                            <input type="text" name="author_en" id="itemAuthorEn" placeholder="Author name" class="bk-field">
                        </div>
                        <div class="bk-group book-only">
                            <label class="bk-label">Co-Author</label>
                            <input type="text" name="co_author" id="itemCoAuthor" placeholder="Secondary author" class="bk-field">
                        </div>
                        <div class="bk-group book-only">
                            <label class="bk-label">Publisher</label>
                            <input type="text" name="publisher" id="itemPublisher" placeholder="e.g. Oprokash" class="bk-field">
                        </div>
                        <div class="bk-group book-only">
                            <label class="bk-label">Publish Year</label>
                            <input type="text" name="publish_year" id="itemYear" placeholder="e.g. 2024" class="bk-field">
                        </div>
                        <div class="bk-group book-only">
                            <label class="bk-label">Edition</label>
                            <input type="text" name="edition" id="itemEdition" placeholder="1st / Revised" class="bk-field">
                        </div>
                        <div class="bk-group">
                            <label class="bk-label">ISBN / Barcode</label>
                            <input type="text" name="isbn" id="itemBarcode" placeholder="e.g. 978-..." class="bk-field">
                        </div>
                        <div class="bk-group">
                            <label class="bk-label">Supplier Name</label>
                            <input type="text" name="supplier_name" id="itemSupplier" placeholder="e.g. Book Palace" class="bk-field">
                        </div>
                        <div class="bk-group book-only">
                            <label class="bk-label">Format</label>
                            <select name="format" id="itemFormat" class="bk-field">
                                <option value="">— Select Format —</option>
                                <option value="Paperback">Paperback</option>
                                <option value="Hardcover">Hardcover</option>
                                <option value="E-book">E-book</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Section 3: Stock, Physical & Pricing -->
                <div>
                    <div class="bk-section-label"><span class="bk-section-line"></span><span>Stock, Physical &amp; Pricing</span></div>
                    <div class="bk-grid-4" style="margin-top:14px;">
                        <div class="bk-group book-only">
                            <label class="bk-label">Condition</label>
                            <select name="book_condition" id="itemCondition" class="bk-field">
                                <option value="New">✨ New</option>
                                <option value="Used">📖 Used</option>
                                <option value="Damaged">⚠️ Damaged</option>
                            </select>
                        </div>
                        <div class="bk-group book-only">
                            <label class="bk-label">Page Count</label>
                            <input type="number" name="page_count" id="itemPageCount" placeholder="e.g. 320" min="1" class="bk-field">
                        </div>
                        <div class="bk-group book-only">
                            <label class="bk-label">Shelf Location</label>
                            <input type="text" name="shelf_location" id="itemShelf" placeholder="e.g. A1" class="bk-field">
                        </div>
                        <div class="bk-group book-only">
                            <label class="bk-label">Rack Number</label>
                            <input type="text" name="rack_number" id="itemRack" placeholder="e.g. R-12" class="bk-field">
                        </div>
                        <div class="bk-group">
                            <label class="bk-label">Stock Qty</label>
                            <input type="number" name="stock_qty" id="itemStock" value="0" min="0" class="bk-field">
                        </div>
                        <div class="bk-group">
                            <label class="bk-label">Min Stock Level</label>
                            <input type="number" name="min_stock_level" id="itemMinStock" value="5" min="0" class="bk-field">
                        </div>
                        <div class="bk-group">
                            <label class="bk-label">Sell Price (৳)</label>
                            <input type="number" name="sell_price" id="itemPrice" value="0" min="0" step="0.01" class="bk-field bk-field-price">
                        </div>
                        <div class="bk-group">
                            <label class="bk-label">Purchase Price (৳)</label>
                            <input type="number" name="purchase_price" id="itemCost" value="0" min="0" step="0.01" class="bk-field">
                        </div>
                    </div>
                </div>

                <!-- Section 4: Toggles -->
                <div style="display:flex; gap:14px; flex-wrap:wrap;">
                    <label class="bk-toggle-card book-only">
                        <div>
                            <p class="bk-toggle-title">Enable Borrowing</p>
                            <p class="bk-toggle-sub">Allow students to borrow this book</p>
                        </div>
                        <div class="bk-switch">
                            <input type="checkbox" name="is_borrowable" id="itemIsBorrowable" value="1" checked>
                            <span class="bk-slider"></span>
                        </div>
                    </label>
                    <label class="bk-toggle-card book-only">
                        <div>
                            <p class="bk-toggle-title">Suggest on Website</p>
                            <p class="bk-toggle-sub">Feature in website recommendations</p>
                        </div>
                        <div class="bk-switch">
                            <input type="checkbox" name="is_suggested" id="itemIsSuggested" value="1">
                            <span class="bk-slider"></span>
                        </div>
                    </label>
                </div>

                <!-- Footer -->
                <div style="border-top:1px solid #f1f5f9; padding-top:20px; display:flex; gap:10px;">
                    <button type="button" onclick="closeModal()" style="padding:13px 26px; border-radius:10px; border:none; background:#f1f5f9; color:#64748b; font-weight:700; font-size:0.88rem; cursor:pointer; transition:0.18s;" onmouseover="this.style.background='#e2e8f0'" onmouseout="this.style.background='#f1f5f9'">Discard</button>
                    <button type="submit" id="btnSaveItem" style="flex:1; padding:13px; border-radius:10px; border:none; background:#1e293b; color:#fff; font-weight:800; font-size:0.95rem; cursor:pointer; transition:0.18s; box-shadow:0 6px 18px -4px rgba(30,41,59,0.3);" onmouseover="this.style.background='#2563eb'" onmouseout="this.style.background='#1e293b'">
                        <i class="fa-solid fa-floppy-disk" style="margin-right:8px;"></i>SAVE INFORMATION
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Restock modal -->
    <div class="modal-overlay" id="restockModal">
        <div class="modal-box" style="max-width: 380px;">
            <div class="modal-head">
                <h3>Restock Item</h3>
                <button class="modal-close" onclick="closeRestock()">&times;</button>
            </div>
            <div class="form-field" style="margin-bottom: 1.5rem;">
                <label>Add Stock Units</label>
                <input type="number" id="restockQty" min="1" value="1"
                    style="padding:11px 14px; border:2px solid #e2e8f0; border-radius:12px; font-family:inherit; font-size:0.9rem; transition:0.2s; width:100%;">
            </div>
            <input type="hidden" id="restockId">
            <input type="hidden" id="restockSourceTable">
            <button onclick="confirmRestock()" class="btn-save"><i class="fa-solid fa-plus"></i> ADD STOCK</button>
        </div>
    </div>

    <div id="invToast"></div>

    <div id="barcodePrintSection">
        <div class="barcode-label">
            <div class="barcode-brand">Ontomeel</div>
            <div class="barcode-name" id="printName">Item Name</div>
            <svg id="barcodeCanvas" class="barcode-svg"></svg>
            <div class="barcode-price" id="printPrice">৳0</div>
        </div>
    </div>

    <script>
        let allItems = [];
        let activeTypeFilter = 'all';

        const icons = { Stationary: 'pen-ruler', Flowers: 'seedling', Accessories: 'plug', General: 'box', Books: 'book' };
        const bgColors = { Stationary: '#eff6ff', Flowers: '#fdf4ff', Accessories: '#fffbeb', General: '#f0fdf4', Books: '#f1f5f9' };
        const iconColors = { Stationary: '#2563eb', Flowers: '#9333ea', Accessories: '#d97706', General: '#16a34a', Books: '#64748b' };

        function toast(msg, type = 'info') {
            const c = document.getElementById('invToast');
            const el = document.createElement('div');
            el.className = `toast-msg ${type}`;
            const iconsMap = { success: 'fa-circle-check', error: 'fa-circle-exclamation', info: 'fa-circle-info' };
            el.innerHTML = `<i class="fa-solid ${iconsMap[type]}"></i> <span>${msg}</span>`;
            c.appendChild(el);
            setTimeout(() => { el.style.opacity = '0'; el.style.transition = '0.3s'; setTimeout(() => el.remove(), 300); }, 3500);
        }

        function renderSkeletons() {
            const g = document.getElementById('inventoryGrid');
            g.innerHTML = Array.from({ length: 8 }, () => `
                <div class="inv-card" style="padding: 1.25rem;">
                    <div style="display:flex; justify-content:space-between; margin-bottom:1rem;">
                        <div class="skel" style="width:46px; height:46px; border-radius:14px;"></div>
                        <div class="skel" style="width:80px; height:22px; border-radius:20px;"></div>
                    </div>
                    <div class="skel" style="width:70%; height:18px; margin-bottom:8px;"></div>
                    <div class="skel" style="width:45%; height:14px; margin-bottom:1.5rem;"></div>
                    <div style="display:flex; justify-content:space-between;">
                        <div class="skel" style="width:70px; height:28px; border-radius:8px;"></div>
                        <div class="skel" style="width:60px; height:24px; border-radius:8px;"></div>
                    </div>
                </div>
            `).join('');
        }

        function updateStats(items) {
            document.getElementById('statTotal').innerText = items.length;
            document.getElementById('statInStock').innerText = items.filter(i => i.stock_qty > 0).length;
            document.getElementById('statLow').innerText = items.filter(i => i.stock_qty <= 5).length;
            document.getElementById('statTypes').innerText = [...new Set(items.map(i => i.item_type))].length;
        }

        let displayedItems = [];
        let itemsToShow = 40;
        const INCREMENT = 40;

        function renderItems(items, append = false) {
            const g = document.getElementById('inventoryGrid');
            
            if (!append) {
                g.innerHTML = '';
                itemsToShow = INCREMENT;
                displayedItems = items;
            }

            if (displayedItems.length === 0) {
                g.innerHTML = `<div style="grid-column:1/-1; text-align:center; padding:5rem 2rem; color:var(--text-muted);">
                    <i class="fa-solid fa-box-open" style="font-size:3rem; opacity:0.1; display:block; margin-bottom:1rem;"></i>
                    No items found. Add some to get started!
                </div>`;
                return;
            }

            const startIndex = append ? g.childElementCount : 0;
            const chunk = displayedItems.slice(startIndex, itemsToShow);

            const html = chunk.map(item => {
                const ico = icons[item.item_type] || 'box';
                const bg = bgColors[item.item_type] || '#f1f5f9';
                const col = iconColors[item.item_type] || '#64748b';
                const isProduction = window.location.hostname.includes('ontomeel.com');
                const imageBasePath = isProduction
                    ? 'https://ontomeel.com/admin/assets/book-images/'
                    : '../../../bookshop/admin/assets/book-images/';

                let imgSrc = '';
                if (item.cover_image) {
                    imgSrc = item.cover_image.startsWith('http') ? item.cover_image : imageBasePath + item.cover_image;
                }
                const isLow = parseInt(item.stock_qty) <= 5;
                return `
                <div class="inv-card">
                    <div class="card-header">
                        <div class="type-icon-wrap" style="background:${bg}; overflow:hidden; border:1px solid rgba(0,0,0,0.05);">
                            ${imgSrc ? `<img src="${imgSrc}" style="width:100%; height:100%; object-fit:cover;">` : `<i class="fa-solid fa-${ico}" style="color:${col};"></i>`}
                        </div>
                        <span class="type-pill" style="background:${bg}; color:${col};">${item.item_type}</span>
                    </div>
                    <div class="card-body">
                        <div class="card-title text-ellipsis" title="${item.title}">${item.title}</div>
                        ${item.author ? `<div style="font-size:0.75rem; color:#64748b; font-weight:600; margin-bottom:4px;">by ${item.author}</div>` : ''}
                        <div class="card-sku">${item.isbn ? ((item.source_table === 'books') ? 'ISBN: ' : 'SKU: ') + item.isbn : '<span style="opacity:0.4;">' + ((item.source_table === 'books') ? 'No ISBN' : 'No SKU') + '</span>'}</div>
                        <div style="display:flex; align-items:center; gap:8px;">
                            <span class="stock-chip ${isLow ? 'stock-low' : 'stock-ok'}">
                                <i class="fa-solid fa-${isLow ? 'triangle-exclamation' : 'circle-check'}" style="margin-right:3px;"></i>
                                ${item.stock_qty} in stock
                            </span>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="price-tag">৳${parseFloat(item.sell_price).toFixed(0)}</div>
                        <div style="display:flex; gap:5px;">
                            <button class="card-btn stock-btn" onclick="openRestock(${item.id}, '${item.source_table}')" title="Restock"><i class="fa-solid fa-plus"></i></button>
                            <button class="card-btn" style="background:#f1f5f9; color:#475569;" onclick='printBarcode(${JSON.stringify(item).replace(/'/g, "&apos;")})' title="Print Barcode">
                                <i class="fa-solid fa-barcode"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-actions">
                        <button class="card-btn edit" onclick='editItem(${JSON.stringify(item).replace(/'/g, "&apos;")})'>
                            <i class="fa-solid fa-pen-to-square"></i> EDIT
                        </button>
                        <button class="card-btn del" onclick="deleteItem(${item.id}, '${item.source_table}')">
                            <i class="fa-solid fa-trash-can"></i>
                        </button>
                    </div>
                </div>`;
            }).join('');

            if (append) {
                g.insertAdjacentHTML('beforeend', html);
            } else {
                g.innerHTML = html;
            }
        }

        // Infinite Scroll listener for inventory grid
        window.addEventListener('scroll', () => {
            if ((window.innerHeight + window.scrollY) >= document.body.offsetHeight - 600) {
                if (displayedItems.length > itemsToShow) {
                    itemsToShow += INCREMENT;
                    renderItems(displayedItems, true);
                }
            }
        });

        async function fetchInventory(isSilent = false) {
            // Initial render from local cache for speed
            const cachedItems = localStorage.getItem('pos_inventory_cache');
            if (cachedItems && !isSilent && allItems.length === 0) {
                allItems = JSON.parse(cachedItems);
                updateStats(allItems);
                filterItems();
            }

            if (!isSilent && allItems.length === 0) renderSkeletons();
            
            try {
                const res = await fetch('../../api/controllers/InventoryController.php?action=getItems');
                const data = await res.json();
                if (data.success) {
                    allItems = data.items;
                    localStorage.setItem('pos_inventory_cache', JSON.stringify(allItems));
                    updateStats(allItems);
                    filterItems();
                }
            } catch (e) { if (!isSilent) toast('Failed to load inventory', 'error'); }
        }

        function filterItems() {
            const q = document.getElementById('searchInv').value.toLowerCase().trim();
            const filter = activeTypeFilter.toLowerCase();
            let list = allItems;
            if (filter !== 'all') {
                list = list.filter(i => (i.item_type || '').toLowerCase() === filter);
            }
            if (q) {
                list = list.filter(i => 
                    (i.title || '').toLowerCase().includes(q) || 
                    (i.author || '').toLowerCase().includes(q) || 
                    (i.isbn || '').toLowerCase().includes(q)
                );
            }
            renderItems(list);
        }

        function setTypeFilter(type, btn) {
            activeTypeFilter = type;
            document.querySelectorAll('.fpill').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            filterItems();
        }

        function openModal(editMode = false) {
            document.getElementById('modalTitle').innerText = editMode ? 'Update Item Details' : 'Add New Item';
            document.getElementById('addModal').style.display = 'flex';
            document.getElementById('addModal').scrollTop = 0;
            document.body.style.overflow = 'hidden';
            if(!editMode) {
                document.getElementById('itemType').value = 'Books';
                toggleBookFields();
            }
        }

        function closeModal() {
            document.getElementById('addModal').style.display = 'none';
            document.getElementById('addItemForm').reset();
            document.getElementById('editId').value = '';
            document.getElementById('editSourceTable').value = '';
            document.body.style.overflow = '';
        }


        function editItem(item) {
            openModal(true);
            document.getElementById('editId').value = item.id;
            document.getElementById('editSourceTable').value = item.source_table || 'books';
            // Section 1 — Core
            document.getElementById('itemTitle').value = item.title || '';
            document.getElementById('itemTitleEn').value = item.title_en || '';
            document.getElementById('itemSubtitle').value = item.subtitle || '';
            document.getElementById('itemType').value = item.item_type || 'General';
            document.getElementById('itemGenre').value = item.genre || '';
            document.getElementById('itemLanguage').value = item.language || '';
            document.getElementById('itemDescription').value = item.description || '';
            // Section 2 — Authors & Publication
            document.getElementById('itemAuthor').value = item.author || '';
            document.getElementById('itemAuthorEn').value = item.author_en || '';
            document.getElementById('itemCoAuthor').value = item.co_author || '';
            document.getElementById('itemPublisher').value = item.publisher || '';
            document.getElementById('itemYear').value = item.publish_year || '';
            document.getElementById('itemEdition').value = item.edition || '';
            document.getElementById('itemBarcode').value = item.isbn || '';
            document.getElementById('itemSupplier').value = item.supplier_name || '';
            document.getElementById('itemFormat').value = item.format || '';
            // Section 3 — Physical & Stock
            document.getElementById('itemCondition').value = item.book_condition || 'New';
            document.getElementById('itemPageCount').value = item.page_count || '';
            document.getElementById('itemShelf').value = item.shelf_location || '';
            document.getElementById('itemRack').value = item.rack_number || '';
            document.getElementById('itemStock').value = item.stock_qty || 0;
            document.getElementById('itemMinStock').value = item.min_stock_level || 5;
            document.getElementById('itemPrice').value = item.sell_price || 0;
            document.getElementById('itemCost').value = item.purchase_price || 0;
            // Toggles
            document.getElementById('itemIsBorrowable').checked = parseInt(item.is_borrowable) !== 0;
            document.getElementById('itemIsSuggested').checked = parseInt(item.is_suggested) === 1;

            toggleBookFields();
        }

        async function handleSave(e) {
            e.preventDefault();
            const editId = document.getElementById('editId').value;
            const action = editId ? 'updateItem' : 'addItem';
            const btn = document.getElementById('btnSaveItem');
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin" style="margin-right:8px;"></i>Saving...';
            try {
                const form = e.target;
                const formData = new FormData(form);
                // Collect all text/number/select fields into a plain object
                const payload = {};
                for (const [k, v] of formData.entries()) {
                    payload[k] = v;
                }
                // Handle checkboxes explicitly (unchecked ones are not in FormData)
                payload.is_borrowable = document.getElementById('itemIsBorrowable').checked ? 1 : 0;
                payload.is_suggested  = document.getElementById('itemIsSuggested').checked ? 1 : 0;

                const res = await fetch(`../../api/controllers/InventoryController.php?action=${action}`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                if (data.success) {
                    toast(editId ? 'Book Updated ✓' : 'Book Added ✓', 'success');
                    closeModal();
                    fetchInventory();
                } else {
                    toast('Error: ' + (data.error || data.message || 'Unknown'), 'error');
                }
            } catch (err) {
                console.error(err);
                toast('Network Error', 'error');
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-floppy-disk" style="margin-right:8px;"></i>SAVE INFORMATION';
            }
        }

        async function deleteItem(id, source_table) {
            if (!confirm('Permanently delete this item?')) return;
            try {
                const res = await fetch(`../../api/controllers/InventoryController.php?action=deleteItem`, { method: 'POST', body: JSON.stringify({ id, source_table }) });
                const data = await res.json();
                if (data.success) { toast('Item Removed', 'info'); fetchInventory(); }
                else toast('Error: ' + data.error, 'error');
            } catch { toast('System Error', 'error'); }
        }

        function openRestock(id, source_table) {
            document.getElementById('restockId').value = id;
            document.getElementById('restockSourceTable').value = source_table;
            document.getElementById('restockQty').value = 1;
            document.getElementById('restockModal').style.display = 'flex';
        }
        function closeRestock() { document.getElementById('restockModal').style.display = 'none'; }

        async function confirmRestock() {
            const id = document.getElementById('restockId').value;
            const source_table = document.getElementById('restockSourceTable').value;
            const qty = parseInt(document.getElementById('restockQty').value) || 1;
            try {
                const res = await fetch('../../api/controllers/InventoryController.php?action=restockItem', { method: 'POST', body: JSON.stringify({ id, qty, source_table }) });
                const data = await res.json();
                if (data.success) { toast(`+${qty} Stock Added`, 'success'); closeRestock(); fetchInventory(); }
                else toast('Error: ' + data.error, 'error');
            } catch { toast('Network Error', 'error'); }
        }

                function printBarcode(item) {
            const barcode = item.isbn || item.barcode;
            if (!barcode) {
                toast('No Barcode/ISBN available for this item', 'error');
                return;
            }
            document.getElementById('printName').innerText = item.title;
            document.getElementById('printPrice').innerText = '৳' + parseFloat(item.sell_price).toFixed(0);
            JsBarcode("#barcodeCanvas", barcode, {
                format: "CODE128", width: 2, height: 40, displayValue: true, fontSize: 14, margin: 0
            });
            window.print();
        }

        function toggleBookFields() {
            const type = document.getElementById('itemType').value;
            const isBook = (type === 'Books');
            document.querySelectorAll('.book-only').forEach(el => {
                if(isBook) el.classList.remove('hidden');
                else el.classList.add('hidden');
            });
        }
        document.getElementById('itemType').addEventListener('change', toggleBookFields);

        async function loadInventoryCategories() {
            try {
                const res = await fetch('../../api/controllers/SupplierController.php?action=listCategories');
                const data = await res.json();
                if (data.success) {
                    const categories = data.categories;
                    
                    // Populate select
                    const select = document.getElementById('itemType');
                    select.innerHTML = categories.map(c => `<option value="${c.name}" data-id="${c.id}">${c.name}</option>`).join('');

                    // Populate filters
                    const pillContainer = document.getElementById('filterPills');
                    const allBtn = pillContainer.querySelector('.active');
                    pillContainer.innerHTML = '';
                    pillContainer.appendChild(allBtn);
                    
                    categories.forEach(c => {
                        const btn = document.createElement('button');
                        btn.className = 'fpill';
                        const ico = icons[c.name] || 'box';
                        btn.innerHTML = `<i class="fa-solid fa-${ico}" style="margin-right:4px;"></i>${c.name}`;
                        btn.onclick = () => setTypeFilter(c.name, btn);
                        pillContainer.appendChild(btn);
                    });
                }
            } catch (e) {
                console.error("Categories loading error", e);
            }
        }

        window.onload = function () {
            loadInventoryCategories();
            fetchInventory();
            setInterval(() => fetchInventory(true), 15000);
            toggleBookFields();
        };
    </script>
</body>

</html>