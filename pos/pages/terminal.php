<?php require_once '../../api/shared/auth_check.php'; checkAuth(true); renderUserUI(true); ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS Terminal | Ontomeel Library</title>
    <link rel="stylesheet" href="../assets/pos-styles.css?v=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.0/dist/JsBarcode.all.min.js"></script>
    
    <style>
        /* Modern Glassmorphism & Refined UI */
        :root {
            --glass-bg: rgba(255, 255, 255, 0.7);
            --glass-border: rgba(255, 255, 255, 0.3);
            --glass-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.07);
        }

        .terminal-layout {
            display: grid;
            grid-template-columns: 1fr 420px;
            gap: 2rem;
            height: calc(100vh - 160px);
            margin-top: 1.5rem;
        }

        /* Left side: Books Explorer */
        .explorer-pane {
            background: var(--glass-bg);
            backdrop-filter: blur(8px);
            border-radius: 24px;
            border: 1px solid var(--border-light);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            box-shadow: var(--glass-shadow);
        }

        .pane-header {
            padding: 1.5rem 2rem;
            border-bottom: 1px solid var(--border-light);
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: rgba(255, 255, 255, 0.5);
        }

        .browse-grid {
            flex-grow: 1;
            padding: 2rem;
            overflow-y: auto;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 1.5rem;
            align-content: start;
        }

        .book-pill {
            background: white;
            border: 1px solid var(--border-light);
            border-radius: 20px;
            padding: 1rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
            display: flex;
            flex-direction: column;
            gap: 12px;
            position: relative;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }

        .book-pill:hover {
            border-color: var(--primary-blue);
            box-shadow: 0 20px 25px -5px rgba(37, 99, 235, 0.1);
            transform: translateY(-5px);
        }

        .book-thumbnail {
            width: 100%;
            aspect-ratio: 3/4;
            border-radius: 12px;
            background: #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            box-shadow: inset 0 0 10px rgba(0, 0, 0, 0.02);
            position: relative;
        }

        .book-thumbnail img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .book-pill:hover .book-thumbnail img {
            transform: scale(1.08);
        }

        .stock-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(4px);
            padding: 4px 10px;
            border-radius: 8px;
            font-size: 0.65rem;
            font-weight: 800;
            color: #1e293b;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            border: 1px solid var(--border-light);
            z-index: 2;
        }

        .book-info-compact {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .book-name {
            font-size: 0.95rem;
            font-weight: 700;
            color: var(--text-header);
            line-height: 1.3;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .book-meta {
            font-size: 0.75rem;
            color: var(--text-muted);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .book-isbn {
            font-size: 0.7rem;
            color: var(--primary-blue);
            font-weight: 600;
            opacity: 0.7;
        }

        .book-price-tag {
            font-size: 1.05rem;
            font-weight: 800;
            color: var(--primary-blue);
            margin-top: 4px;
        }

        /* Right side: Modern Cart */
        .cart-pane {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .member-card {
            background: var(--glass-bg);
            backdrop-filter: blur(8px);
            border: 1px solid var(--border-light);
            border-radius: 24px;
            padding: 1.5rem;
            box-shadow: var(--glass-shadow);
            position: relative;
            z-index: 10;
        }

        .pos-cart {
            background: white;
            border: 1px solid var(--border-light);
            border-radius: 24px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            box-shadow: var(--shadow-lg);
            overflow: hidden;
        }

        .cart-header {
            padding: 1.5rem;
            border-bottom: 1px solid var(--border-light);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .cart-list {
            flex-grow: 1;
            padding: 1.5rem;
            overflow-y: auto;
        }

        .cart-row {
            display: flex;
            gap: 16px;
            padding: 12px;
            border-radius: 16px;
            border-bottom: 1px solid #f1f5f9;
            transition: background 0.2s;
        }

        .cart-row:hover {
            background: #f8fafc;
        }

        .cart-img {
            width: 50px;
            height: 70px;
            border-radius: 8px;
            background: var(--primary-blue-soft);
            object-fit: cover;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .cart-details {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .cart-total-sect {
            background: #fcfdfe;
            padding: 1.5rem 2rem 2rem;
            border-top: 1px solid var(--border-light);
        }

        .action-btns {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-top: 1.5rem;
        }

        .btn-action {
            height: 52px;
            border-radius: 14px;
            border: none;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            font-size: 0.95rem;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .btn-sell {
            background: var(--primary-blue);
            color: white;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
        }

        .btn-issue {
            background: #0f172a;
            color: white;
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.2);
        }

        .btn-action:hover {
            transform: scale(1.02);
            opacity: 0.95;
        }

        .btn-action:active {
            transform: scale(0.98);
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* Sticky Modern Header */
        header {
            position: sticky;
            top: 0;
            background: rgba(248, 250, 252, 0.82);
            backdrop-filter: blur(14px);
            z-index: 50;
            padding: 1.25rem 0;
            margin-bottom: 2rem !important;
            border-bottom: 1px solid rgba(226, 232, 240, 0.6);
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 2rem;
        }

        .header-center-search {
            flex-grow: 1;
            display: flex;
            justify-content: center;
            max-width: 700px;
        }

        .main-search-box {
            background: white;
            border: 2px solid var(--border-light);
            border-radius: 20px;
            padding: 0 1.75rem;
            height: 60px;
            width: 100%;
            display: flex;
            align-items: center;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .main-search-box:focus-within {
            border-color: var(--primary-blue);
            box-shadow: 0 15px 30px -5px rgba(37, 99, 235, 0.12);
            transform: translateY(-2px);
        }

        .main-search-box i {
            color: var(--primary-blue);
            font-size: 1.2rem;
            margin-right: 15px;
        }

        .main-search-box input {
            background: transparent;
            border: none;
            color: var(--text-header);
            width: 100%;
            font-size: 1.1rem;
            font-weight: 600;
        }

        .main-search-box input:focus {
            outline: none;
        }

        .main-search-box input::placeholder {
            color: var(--text-muted);
            font-weight: 500;
            opacity: 0.7;
        }

        /* Member Search Dropdown */
        .search-results-dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white !important;
            border-radius: 12px;
            border: 1px solid var(--border-light);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
            margin-top: 8px;
            z-index: 1000;
            max-height: 350px;
            overflow-y: auto;
            display: none;
        }

        .result-item {
            padding: 12px 16px;
            cursor: pointer;
            transition: background 0.2s;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .result-item:last-child {
            border-bottom: none;
        }

        .result-item:hover {
            background: var(--primary-blue-soft);
        }

        .result-name {
            font-weight: 700;
            color: var(--text-header);
            font-size: 0.9rem;
        }

        .result-id {
            font-size: 0.75rem;
            color: var(--text-muted);
        }

        /* Checkout Workflow Enhancements */
        .customer-tabs {
            display: flex;
            background: #f1f5f9;
            padding: 4px;
            border-radius: 12px;
            margin-bottom: 1.5rem;
        }

        .cust-tab {
            flex: 1;
            padding: 10px;
            text-align: center;
            font-size: 0.85rem;
            font-weight: 700;
            border-radius: 9px;
            cursor: pointer;
            transition: all 0.2s;
            color: var(--text-muted);
        }

        .cust-tab.active {
            background: white;
            color: var(--primary-blue);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }

        .guest-inputs {
            display: none;
            flex-direction: column;
            gap: 12px;
        }

        .selected-member-display {
            display: none;
            margin-top: 15px;
            padding: 15px;
            background: #f0f7ff;
            border-radius: 16px;
            border: 1px solid #dbeafe;
            justify-content: space-between;
            align-items: center;
            animation: fadeIn 0.3s ease;
        }

        .member-detail-badge {
            background: #dbeafe;
            color: #1e40af;
            padding: 2px 8px;
            border-radius: 6px;
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
        }

        .btn-quick-add:hover {
            background: #dcfce7;
            transform: translateY(-1px);
        }

        /* Terminal Specific Action Buttons */
        .terminal-actions-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin-bottom: 1rem;
        }

        .terminal-btn {
            background: white;
            border: 1px solid var(--border-light);
            border-radius: 12px;
            padding: 10px 5px;
            font-size: 0.75rem;
            font-weight: 700;
            color: #64748b;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 6px;
            position: relative;
        }

        .terminal-btn i {
            font-size: 1rem;
            color: #94a3b8;
        }

        .terminal-btn:hover {
            border-color: var(--primary-blue);
            color: var(--primary-blue);
            background: #f0f7ff;
            transform: translateY(-2px);
        }

        .terminal-btn:hover i {
            color: var(--primary-blue);
        }

        .terminal-btn.danger:hover {
            border-color: #ef4444;
            color: #ef4444;
            background: #fef2f2;
        }

        .terminal-btn.danger:hover i {
            color: #ef4444;
        }

        .parked-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: var(--primary-blue);
            color: white;
            font-size: 0.65rem;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.4);
            border: 2px solid white;
        }

        .search-container-pos {
            position: relative;
            background: white;
            border: 1px solid var(--border-light);
            border-radius: 14px;
            padding: 2px;
            transition: all 0.3s;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
            z-index: 101;
        }

        .search-container-pos:focus-within {
            border-color: var(--primary-blue);
            box-shadow: 0 8px 20px -5px rgba(37, 99, 235, 0.1);
        }

        .checkout-input {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid var(--border-light);
            border-radius: 10px;
            font-size: 0.9rem;
            font-weight: 500;
            outline: none;
            transition: border-color 0.2s;
        }

        .checkout-input:focus {
            border-color: var(--primary-blue);
        }

        /* Hide Spin Buttons */
        input::-webkit-outer-spin-button,
        input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        input[type=number] {
            -moz-appearance: textfield;
        }

        /* Modal Overlay */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(15, 23, 42, 0.4);
            backdrop-filter: blur(4px);
            z-index: 1000;
            display: none;
            align-items: center;
            justify-content: center;
        }

        .checkout-modal {
            background: white;
            width: 500px;
            border-radius: 24px;
            padding: 2.5rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            animation: modalPop 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        @keyframes modalPop {
            from {
                transform: scale(0.9);
                opacity: 0;
            }

            to {
                transform: scale(1);
                opacity: 1;
            }
        }

        .pay-method-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin: 1.5rem 0;
        }

        .pay-btn {
            padding: 15px;
            border: 2px solid #f1f5f9;
            border-radius: 14px;
            cursor: pointer;
            transition: all 0.2s;
            text-align: center;
            font-weight: 700;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .pay-btn.selected {
            border-color: var(--primary-blue);
            background: var(--primary-blue-soft);
            color: var(--primary-blue);
        }

        /* Skeleton Loading Effect */
        .skeleton {
            background: linear-gradient(90deg, #f0f2f5 25%, #e6e8eb 50%, #f0f2f5 75%);
            background-size: 200% 100%;
            animation: skeleton-loading 1.5s infinite linear;
            border-radius: 12px;
        }

        @keyframes skeleton-loading {
            0% {
                background-position: 200% 0;
            }

            100% {
                background-position: -200% 0;
            }
        }

        .skeleton-thumb {
            width: 100%;
            aspect-ratio: 3/4;
            margin-bottom: 12px;
        }

        .skeleton-text {
            height: 16px;
            width: 85%;
            margin-bottom: 8px;
        }

        .skeleton-text.short {
            width: 45%;
        }

        /* Fade-in Animation */
        .fade-in {
            animation: fadeIn 0.4s cubic-bezier(0.4, 0, 0.2, 1) forwards;
            opacity: 0;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Borrow Modal Specifics */
        .borrow-modal {
            background: white;
            width: 450px;
            border-radius: 24px;
            padding: 2rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            animation: modalPop 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        /* Modern Toast System */
        #toastContainer {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 2000;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .toast {
            background: rgba(15, 23, 42, 0.9);
            backdrop-filter: blur(10px);
            color: white;
            padding: 16px 24px;
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 0.9rem;
            font-weight: 600;
            min-width: 300px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            animation: toastSlideIn 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            transition: all 0.3s ease;
        }

        .toast.success {
            border-left: 4px solid var(--accent-mint);
        }

        .toast.error {
            border-left: 4px solid #ef4444;
        }

        .toast.info {
            border-left: 4px solid var(--primary-blue);
        }

        .toast i {
            font-size: 1.2rem;
        }

        .toast.success i {
            color: var(--accent-mint);
        }

        .toast.error i {
            color: #ef4444;
        }

        .toast.info i {
            color: var(--primary-blue);
        }

        @keyframes toastSlideIn {
            from {
                transform: translateX(100%) scale(0.8);
                opacity: 0;
            }

            to {
                transform: translateX(0) scale(1);
                opacity: 1;
            }
        }

        .toast.fade-out {
            transform: scale(0.9);
            opacity: 0;
            pointer-events: none;
        }

        /* Invoice Print Styles (80mm) */
        @media print {
            body * {
                visibility: hidden;
            }

            #invoicePrintContainer,
            #invoicePrintContainer * {
                visibility: visible;
            }

            #invoicePrintContainer {
                position: absolute;
                left: 0;
                top: 0;
                width: 576px;
                background: white;
            }

            @page {
                size: 576px 1500px;
                margin: 0;
            }

            .invoice-receipt {
                width: 576px;
                margin: 0;
                padding: 30px;
            }

            #invoiceModal,
            .modal-overlay,
            header,
            .layout-main {
                display: none !important;
            }
        }
        /* Receipt Styles (optimized for 576px / 80mm thermal printing) */
        .invoice-receipt {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            width: 576px;
            padding: 20px;
            margin: auto;
            color: #000;
            background: #fff;
            font-size: 14px;
            line-height: 1.4;
            min-height: 300px;
            max-height: 1500px;
        }

        .receipt-header {
            text-align: center;
            margin-bottom: 25px;
            border-bottom: 1.5px dashed #000;
            padding-bottom: 20px;
        }

        .receipt-header img {
            max-width: 80px;
            margin-bottom: 12px;
            filter: grayscale(1);
        }

        .receipt-header h2 {
            margin: 0;
            font-size: 24px;
            font-weight: 800;
            letter-spacing: 1.5px;
            text-transform: uppercase;
        }

        .receipt-header p {
            margin: 4px 0;
            font-size: 13px;
            color: #000;
            font-weight: 500;
        }

        .receipt-info {
            padding: 12px 0;
            border-bottom: 1px dashed #eee;
            margin-bottom: 12px;
        }

        .receipt-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }

        .receipt-items {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }

        .receipt-items th {
            text-align: left;
            border-bottom: 2px solid #000;
            padding-bottom: 8px;
            font-size: 13px;
            font-weight: 800;
        }

        .receipt-items td {
            padding: 10px 0;
            font-size: 14px;
            vertical-align: top;
        }

        .receipt-totals {
            border-top: 2.5px solid #000;
            margin-top: 20px;
            padding-top: 15px;
        }

        .total-bold {
            font-weight: 800;
            font-size: 18px;
            border-top: 1px solid #000;
            margin-top: 8px;
            padding-top: 8px;
        }

        .barcode-container {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px dashed #000;
        }

        .in-words {
            font-style: italic;
            font-size: 13px;
            margin-top: 15px;
            border-top: 1px dotted #ccc;
            padding-top: 15px;
        }
    </style>
</head>

<body>

    <script src="../assets/sidebar.js"></script>

    <div class="main-wrapper">
        <header>
            <div class="page-title" style="min-width: 250px;">
                <h1>Sales <span style="color: var(--primary-blue);">Terminal</span></h1>
                <p>Quick issue & billing portal</p>
            </div>

            <div class="header-center-search">
                <div class="main-search-box">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" id="bookSearch" placeholder="Search title, author, category, or barcode..."
                        onkeyup="filterBooks()">
                </div>
            </div>

            <div class="header-tools" style="display: flex; justify-content: flex-end; align-items: center; gap: 1.5rem;">
                <!-- Profile Link as Initials Box -->
                <a href="profile.php" class="header-profile-link" style="text-decoration: none;">
                    <div style="width: 42px; height: 42px; background: #eff6ff; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-weight: 700; color: #2563eb; font-size: 0.9rem; border: 1px solid #dbeafe; transition: all 0.2s;" class="user-initials-display">
                        AD
                    </div>
                </a>
            </div>
            <style>
                .header-profile-link:hover div {
                    background: #dbeafe;
                    transform: scale(1.05);
                    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.1);
                }
            </style>
        </header>

        <div class="terminal-layout">
            <!-- Explorer -->
            <div class="explorer-pane">
                <div class="pane-header">
                    <div style="font-weight: 700; color: var(--text-header);">Catalog</div>
                    <div id="bookCount" style="font-size: 0.8rem; color: var(--text-muted);">Loading...</div>
                </div>
                <!-- Filter tabs -->
                <div style="display: flex; gap: 8px; padding: 0 1.5rem 1rem; flex-shrink: 0;">
                    <button class="filter-tab active" onclick="setFilter('all', this)"
                        style="padding: 6px 16px; border-radius: 20px; border: 2px solid transparent; background: var(--primary-blue); color: white; font-weight: 700; font-size: 0.75rem; cursor: pointer; transition: 0.2s;">All</button>
                    <button class="filter-tab" onclick="setFilter('books', this)"
                        style="padding: 6px 16px; border-radius: 20px; border: 2px solid #e2e8f0; background: white; color: var(--text-muted); font-weight: 700; font-size: 0.75rem; cursor: pointer; transition: 0.2s;"><i
                            class="fa-solid fa-book" style="margin-right:4px;"></i>Books</button>
                    <button class="filter-tab" onclick="setFilter('inventory', this)"
                        style="padding: 6px 16px; border-radius: 20px; border: 2px solid #e2e8f0; background: white; color: var(--text-muted); font-weight: 700; font-size: 0.75rem; cursor: pointer; transition: 0.2s;"><i
                            class="fa-solid fa-box-open" style="margin-right:4px;"></i>Inventory</button>
                </div>
                <div class="browse-grid" id="browseGrid">
                    <!-- Dynamic Content -->
                </div>
            </div>

            <!-- Cart -->
            <div class="cart-pane">
                <div class="member-card" style="padding: 1.2rem;">
                    <div class="customer-tabs" style="margin-bottom: 1.2rem;">
                        <div class="cust-tab active" onclick="switchCustomerTab('member')">Registered Member</div>
                        <div class="cust-tab" onclick="switchCustomerTab('guest')">Walk-in Guest</div>
                    </div>

                    <div id="memberSection">
                        <div
                            style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                            <div
                                style="font-size: 0.65rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px;">
                                Session Control</div>
                            <button class="btn-quick-add" onclick="openMemberModal()">
                                <i class="fa-solid fa-user-plus"></i> QUICK ADD
                            </button>
                        </div>

                        <div class="terminal-actions-grid">
                            <button class="terminal-btn" id="btnHoldCart" onclick="holdCart()">
                                <i class="fa-solid fa-pause"></i>
                                HOLD
                            </button>
                            <div id="parkedContainer" style="position: relative; width: 100%;">
                                <button class="terminal-btn" onclick="toggleParked()" style="width: 100%;">
                                    <i class="fa-solid fa-box-archive"></i>
                                    PARKED
                                    <span id="parkedCountBadge" class="parked-badge" style="display: none;">0</span>
                                </button>
                                <div id="parkedList"
                                    style="display:none; position:absolute; top:105%; right:0; background:white; width:280px; border-radius:16px; box-shadow:var(--shadow-xl); z-index:100; border:1px solid #e2e8f0; max-height:350px; overflow-y:auto;">
                                    <!-- Parked items -->
                                </div>
                            </div>
                            <button class="terminal-btn danger" onclick="clearCart()">
                                <i class="fa-solid fa-trash-can"></i>
                                CLEAR
                            </button>
                        </div>

                        <div class="search-container-pos">
                            <div class="search-box" style="width: 100%; border: none; box-shadow: none;">
                                <i class="fa-solid fa-magnifying-glass"
                                    style="color: var(--primary-blue); font-size: 0.9rem;"></i>
                                <input type="text" id="memberSearch" placeholder="Scan ID or search name..."
                                    style="font-size: 0.85rem;" onkeyup="searchMembers()" autocomplete="off">
                            </div>
                            <div id="memberSearchResults" class="search-results-dropdown"
                                style="border-radius: 0 0 14px 14px; margin-top: 0; border-top: none;"></div>
                        </div>

                        <div id="selectedMember" class="selected-member-display">
                            <div style="flex: 1;">
                                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
                                    <span id="selMemberName"
                                        style="font-weight: 700; color: var(--primary-blue); font-size: 0.95rem;"></span>
                                    <span id="selMemberPlan" class="member-detail-badge"></span>
                                </div>
                                <div id="selMemberId"
                                    style="font-size: 0.75rem; color: var(--text-muted); font-family: monospace;"></div>
                                <div
                                    style="margin-top: 8px; display: flex; gap: 15px; border-top: 1px dashed #bfdbfe; padding-top: 8px;">
                                    <div>
                                        <div style="font-size: 0.6rem; color: #64748b; text-transform: uppercase;">
                                            Balance</div>
                                        <div id="selMemberBal"
                                            style="font-weight: 700; color: #16a34a; font-size: 0.85rem;"></div>
                                    </div>
                                    <div>
                                        <div style="font-size: 0.6rem; color: #64748b; text-transform: uppercase;">
                                            Expiry</div>
                                        <div id="selMemberExpiry"
                                            style="font-weight: 600; color: #e11d48; font-size: 0.85rem;"></div>
                                    </div>
                                </div>
                            </div>
                            <button onclick="clearMember()"
                                style="background: none; border: none; color: #ef4444; cursor: pointer; padding: 10px;"><i
                                    class="fa-solid fa-circle-xmark fa-lg"></i></button>
                        </div>
                    </div>

                    <div id="guestSection" class="guest-inputs">
                        <input type="text" id="guestName" class="checkout-input" placeholder="Guest Name">
                        <input type="text" id="guestPhone" class="checkout-input" placeholder="Phone Number">
                        <input type="email" id="guestEmail" class="checkout-input"
                            placeholder="Email Address (Optional)">
                    </div>
                </div>

                <div class="pos-cart">
                    <div class="cart-header">
                        <span style="font-weight: 800; color: var(--text-header); font-size: 1.1rem;">Order
                            Details</span>
                        <div id="cartStatus"
                            style="font-size: 0.8rem; color: var(--accent-mint); font-weight: 700; display: none;"><i
                                class="fa-solid fa-check-circle"></i> Item Added</div>
                    </div>
                    <div class="cart-list" id="cartList">
                        <div style="text-align: center; color: var(--text-muted); margin-top: 5rem; padding: 2rem;">
                            <div style="position: relative; display: inline-block;">
                                <i class="fa-solid fa-basket-shopping"
                                    style="font-size: 3.5rem; opacity: 0.05; display: block; margin-bottom: 1.5rem;"></i>
                                <i class="fa-solid fa-plus"
                                    style="position: absolute; bottom: 15px; right: -5px; font-size: 1.2rem; opacity: 0.2; color: var(--primary-blue);"></i>
                            </div>
                            <div style="font-weight: 700; font-size: 0.9rem; opacity: 0.4;">Your cart is empty</div>
                            <div style="font-size: 0.75rem; opacity: 0.4; margin-top: 4px;">Search and add books to
                                start</div>
                        </div>
                    </div>
                    <div class="cart-total-sect">
                        <div
                            style="display: flex; justify-content: space-between; margin-bottom: 0.5rem; font-size: 0.9rem;">
                            <span style="color: var(--text-muted);">Selected Items</span>
                            <span id="cartCount" style="font-weight: 700;">0</span>
                        </div>
                        <div
                            style="display: flex; justify-content: space-between; margin-bottom: 0.5rem; font-size: 0.9rem;">
                            <span style="color: var(--text-muted);">Subtotal</span>
                            <span id="cartSubtotal" style="font-weight: 700;">৳0.00</span>
                        </div>
                        <div
                            style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem; font-size: 0.9rem;">
                            <span style="color: var(--text-muted);">Discount</span>
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <div style="position: relative; display: flex; align-items: center;">
                                    <input type="number" id="cartDiscountPercent" class="checkout-input"
                                        style="width: 55px; padding: 4px 22px 4px 8px; text-align: right; height: 30px; margin: 0; font-size: 0.85rem; border-radius: 8px;"
                                        placeholder="0" min="0" max="100" step="any" onkeyup="applyDiscount('percent')" onchange="applyDiscount('percent')">
                                    <span style="position: absolute; right: 8px; font-size: 0.75rem; color: #94a3b8;">%</span>
                                </div>
                                <div style="position: relative; display: flex; align-items: center;">
                                    <input type="number" id="cartDiscount" class="checkout-input"
                                        style="width: 80px; padding: 4px 8px 4px 20px; text-align: right; height: 30px; margin: 0; font-size: 0.85rem; border-radius: 8px;"
                                        placeholder="0" min="0" step="any" onkeyup="applyDiscount('amount')" onchange="applyDiscount('amount')">
                                    <span style="position: absolute; left: 8px; font-size: 0.75rem; color: #94a3b8;">৳</span>
                                </div>
                            </div>
                        </div>

                        <div
                            style="display: flex; justify-content: space-between; font-size: 1.2rem; font-weight: 800; color: var(--text-header); margin-top: 10px; padding-top: 10px; border-top: 1px dashed var(--border-light);">
                            <span>Total</span>
                            <span id="cartTotal">৳0.00</span>
                        </div>

                        <div class="action-btns">
                            <button class="btn-action btn-issue" onclick="finalize('issue')"><i
                                    class="fa-solid fa-hand-holding"></i> ISSUE</button>
                            <button class="btn-action btn-sell" onclick="finalize('sell')"><i
                                    class="fa-solid fa-receipt"></i> SELL</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Borrow Modal -->
    <div class="modal-overlay" id="borrowModal">
        <div class="borrow-modal">
            <h2 style="font-weight: 800; margin-bottom: 0.5rem;">Issue Books</h2>
            <p style="color: var(--text-muted); margin-bottom: 2rem;">Borrowing <span id="borrowCountText"
                    style="color: var(--primary-blue); font-weight: 800;">0</span> books for member.</p>

            <div style="margin-bottom: 1.5rem;">
                <label
                    style="font-size: 0.85rem; font-weight: 700; color: var(--text-header); display: block; margin-bottom: 8px;">Expected
                    Return Date</label>
                <input type="date" id="dueDate" class="checkout-input" value="">
            </div>

            <div
                style="background: #fffbeb; border: 1px solid #fef3c7; padding: 12px; border-radius: 12px; margin-bottom: 1.5rem; display: flex; gap: 10px; align-items: flex-start;">
                <i class="fa-solid fa-circle-info" style="color: #d97706; margin-top: 3px;"></i>
                <div style="font-size: 0.8rem; color: #92400e; line-height: 1.4;">Members must return books within the
                    due date to avoid fines. Stock will be decreased automatically.</div>
            </div>

            <button class="btn-action btn-issue" style="width: 100%; margin-top: 1rem;" onclick="processBorrow()">
                <i class="fa-solid fa-book-bookmark"></i> CONFIRM BORROWING
            </button>
            <button class="btn-action"
                style="width: 100%; margin-top: 0.75rem; background: #f1f5f9; color: var(--text-body);"
                onclick="closeBorrowModal()">
                CANCEL
            </button>
        </div>
    </div>

    <!-- Checkout Modal -->
    <div class="modal-overlay" id="checkoutModal">
        <div class="checkout-modal" style="width: 500px;">
            <h2 style="font-weight: 800; margin-bottom: 0.5rem;">Reconcile Payment</h2>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; background: #f8fafc; padding: 15px; border-radius: 16px;">
                <div>
                    <p style="color: var(--text-muted); font-size: 0.75rem; text-transform: uppercase; font-weight: 700; margin: 0;">Total Payable</p>
                    <span id="modalTotal" style="color: var(--primary-blue); font-weight: 800; font-size: 1.5rem;">৳0.00</span>
                </div>
                <div style="text-align: right;">
                    <p style="color: var(--text-muted); font-size: 0.75rem; text-transform: uppercase; font-weight: 700; margin: 0;">Remaining</p>
                    <span id="paymentRemaining" style="color: #ef4444; font-weight: 800; font-size: 1.5rem;">৳0.00</span>
                </div>
            </div>

            <label style="font-size: 0.85rem; font-weight: 700; color: var(--text-header); display: block; margin-bottom: 12px;">Enter Amounts</label>
            <div class="payment-reconciliation-list" style="display: flex; flex-direction: column; gap: 10px;">
                <div class="pay-method-row" style="display: flex; align-items: center; gap: 12px; background: #fff; border: 1px solid #f1f5f9; padding: 8px 12px; border-radius: 14px;">
                    <div style="width: 32px; height: 32px; background: #f1f5f9; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #64748b;">
                        <i class="fa-solid fa-money-bill-wave"></i>
                    </div>
                    <span style="font-weight: 700; font-size: 0.9rem; flex: 1;">Cash</span>
                    <input type="number" step="any" class="checkout-input pay-amount-input" data-method="Cash" style="width: 120px; height: 40px; text-align: right; padding-left: 20px;" placeholder="0.00" oninput="calculateSplitPayment()">
                </div>
                <div class="pay-method-row" style="display: flex; align-items: center; gap: 12px; background: #fff; border: 1px solid #f1f5f9; padding: 8px 12px; border-radius: 14px;">
                    <div style="width: 32px; height: 32px; background: #f1f5f9; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #64748b;">
                        <i class="fa-solid fa-wallet"></i>
                    </div>
                    <span style="font-weight: 700; font-size: 0.9rem; flex: 1;">bKash</span>
                    <input type="number" step="any" class="checkout-input pay-amount-input" data-method="Bkash" style="width: 120px; height: 40px; text-align: right; padding-left: 20px;" placeholder="0.00" oninput="calculateSplitPayment()">
                </div>
                <div class="pay-method-row" style="display: flex; align-items: center; gap: 12px; background: #fff; border: 1px solid #f1f5f9; padding: 8px 12px; border-radius: 14px;">
                    <div style="width: 32px; height: 32px; background: #f1f5f9; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #64748b;">
                        <i class="fa-solid fa-mobile-screen"></i>
                    </div>
                    <span style="font-weight: 700; font-size: 0.9rem; flex: 1;">Nagad</span>
                    <input type="number" step="any" class="checkout-input pay-amount-input" data-method="Nagad" style="width: 120px; height: 40px; text-align: right; padding-left: 20px;" placeholder="0.00" oninput="calculateSplitPayment()">
                </div>
                <div class="pay-method-row" style="display: flex; align-items: center; gap: 12px; background: #fff; border: 1px solid #f1f5f9; padding: 8px 12px; border-radius: 14px;">
                    <div style="width: 32px; height: 32px; background: #f1f5f9; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #64748b;">
                        <i class="fa-solid fa-credit-card"></i>
                    </div>
                    <span style="font-weight: 700; font-size: 0.9rem; flex: 1;">Card</span>
                    <input type="number" step="any" class="checkout-input pay-amount-input" data-method="Card" style="width: 120px; height: 40px; text-align: right; padding-left: 20px;" placeholder="0.00" oninput="calculateSplitPayment()">
                </div>
                <div id="walletPayRow" class="pay-method-row" style="display: flex; align-items: center; gap: 12px; background: #fff; border: 1px solid #f1f5f9; padding: 8px 12px; border-radius: 14px;">
                    <div style="width: 32px; height: 32px; background: #f1f5f9; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #64748b;">
                        <i class="fa-solid fa-user-shield"></i>
                    </div>
                    <span style="font-weight: 700; font-size: 0.9rem; flex: 1;">Member Fund</span>
                    <input type="number" step="any" class="checkout-input pay-amount-input" data-method="Wallet" style="width: 120px; height: 40px; text-align: right; padding-left: 20px;" placeholder="0.00" oninput="calculateSplitPayment()">
                </div>
            </div>

            <div style="margin-top: 1.5rem; border-top: 1px dashed #e2e8f0; padding-top: 1rem;">
                <label style="font-size: 0.8rem; font-weight: 700; color: #64748b; display: block; margin-bottom: 8px;">
                    <i class="fa-solid fa-calendar-day"></i> Transaction Date (Backdate)
                </label>
                <input type="datetime-local" id="orderDateOverride" class="checkout-input" style="font-size: 0.85rem;">
                <p style="font-size: 0.7rem; color: #94a3b8; margin-top: 4px;">Leave empty for current time.</p>
            </div>

            <button class="btn-action btn-sell" style="width: 100%; margin-top: 1.5rem;" onclick="processOrder()"
                id="btnFinalOrder">
                <i class="fa-solid fa-shield-check"></i> CONFIRM TRANSACTION
            </button>
            <button class="btn-action"
                style="width: 100%; margin-top: 0.75rem; background: #f1f5f9; color: var(--text-body);"
                onclick="closeCheckout()">
                CANCEL
            </button>
        </div>
    </div>
    <!-- Member Modal -->
    <div class="modal-overlay" id="memberModal">
        <div class="borrow-modal" style="width: 400px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h2 style="margin: 0; font-size: 1.25rem; color: var(--primary-blue); font-weight: 800;">Register Member
                </h2>
                <button onclick="closeMemberModal()"
                    style="background: none; border: none; font-size: 1.5rem; color: #94a3b8; cursor: pointer;">&times;</button>
            </div>
            <div style="display: flex; flex-direction: column; gap: 15px;">
                <div>
                    <label
                        style="display: block; font-size: 0.75rem; font-weight: 700; color: #64748b; margin-bottom: 5px;">FULL
                        NAME *</label>
                    <input type="text" id="newMemberName" class="checkout-input" placeholder="Enter Full Name">
                </div>
                <div>
                    <label
                        style="display: block; font-size: 0.75rem; font-weight: 700; color: #64748b; margin-bottom: 5px;">PHONE
                        NUMBER *</label>
                    <input type="text" id="newMemberPhone" class="checkout-input" placeholder="e.g. 01700000000">
                </div>
                <div>
                    <label
                        style="display: block; font-size: 0.75rem; font-weight: 700; color: #64748b; margin-bottom: 5px;">EMAIL
                        ADDRESS</label>
                    <input type="email" id="newMemberEmail" class="checkout-input" placeholder="Enter Email (Optional)">
                </div>
                <div
                    style="background: #fffbeb; padding: 10px; border-radius: 10px; border: 1px solid #fef3c7; margin-top: 5px;">
                    <div style="font-size: 0.7rem; color: #92400e; display: flex; align-items: center; gap: 8px;">
                        <i class="fa-solid fa-info-circle"></i>
                        Default password for the user is <strong>123456</strong>
                    </div>
                </div>
                <button onclick="saveNewMember()" class="btn-action btn-sell" id="btnSaveMember"
                    style="width: 100%; margin-top: 10px;">
                    <i class="fa-solid fa-user-plus"></i> CREATE MEMBER
                </button>
            </div>
        </div>
    </div>

    <!-- Return Modal -->
    <div class="modal-overlay" id="returnModal">
        <div class="checkout-modal" style="width: 550px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <h2 style="margin:0; font-weight: 800;">Process Returns</h2>
                <button onclick="closeReturnModal()"
                    style="border:none; background:none; cursor:pointer; font-size:1.5rem; color:#94a3b8;">&times;</button>
            </div>
            <p style="color: var(--text-muted); margin-bottom: 1.5rem;">Active borrows for <span id="returnMemberName"
                    style="color: var(--primary-blue); font-weight: 800;">Member</span></p>

            <div style="max-height: 300px; overflow-y: auto; margin-bottom: 1.5rem;" id="borrowListContainer">
                <!-- Borrowed items will be loaded here -->
            </div>

            <button class="btn-action btn-sell" style="width: 100%; margin-top: 1rem; background: #10b981;"
                onclick="processReturn()">
                <i class="fa-solid fa-rotate-left"></i> CONFIRM RETURN
            </button>
            <button class="btn-action"
                style="width: 100%; margin-top: 0.75rem; background: #f1f5f9; color: var(--text-body);"
                onclick="closeReturnModal()">
                CANCEL
            </button>
        </div>
    </div>
    <!-- Toasts -->
    <div id="toastContainer"></div>

    <!-- Print Container (Hidden) -->
    <div id="invoicePrintContainer" style="display: none;"></div>

    <!-- Invoice / Receipt Preview Modal -->
    <div class="modal-overlay" id="invoiceModal">
        <div class="checkout-modal" style="width: 450px; padding: 0; overflow: hidden; background: #fff;">
            <div style="padding: 20px; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center;">
                <h3 style="margin: 0; font-weight: 800; color: var(--text-header); font-size: 1.1rem;">Sale Preview</h3>
                <button onclick="closeInvoiceModal()" style="background: none; border: none; font-size: 1.25rem; color: #94a3b8; cursor: pointer;"><i class="fa-solid fa-times"></i></button>
            </div>
            
            <div id="receiptPreviewContent" style="padding: 20px; max-height: 70vh; overflow-y: auto; background: #f8fafc;">
                <!-- Receipt content will be cloned here for preview -->
            </div>

            <div style="padding: 20px; border-top: 1px solid #f1f5f9; display: flex; gap: 10px;">
                <button onclick="window.print()" class="btn-action btn-sell" style="flex: 1; height: 50px; font-weight: 800;">
                    <i class="fa-solid fa-print"></i> PRINT RECEIPT (80mm)
                </button>
                <button onclick="closeInvoiceModal()" class="btn-action" style="background: #f1f5f9; color: var(--text-body); padding: 0 20px;">
                    DONE
                </button>
            </div>
        </div>
    </div>

    <script>
        let allItems = [];
        let cart = [];
        let paymentMethod = 'Cash';
        let customerType = 'member';
        let lastDiscountType = 'amount';

        async function init() {
            renderSkeletons();
            try {
                const response = await fetch('../../api/controllers/TerminalController.php?action=getBooks');
                allItems = await response.json();
                applyFilter();
                updateParkedUI();

                // Check for Quick Reorder from Orders History
                const quickOrder = localStorage.getItem('pos_quick_reorder');
                if (quickOrder) {
                    const data = JSON.parse(quickOrder);
                    cart = data.items || [];
                    
                    // Cleanup reorder flag
                    localStorage.removeItem('pos_quick_reorder');
                    
                    if (data.memberId) {
                        // Fetch the full member object to ensure dropdown and other parts update
                        const mRes = await fetch(`../../api/controllers/TerminalController.php?action=searchMembers&q=${data.memberId}`);
                        const members = await mRes.json();
                        if (members && members.length > 0) {
                            selectMember(members[0]);
                        }
                    }
                    
                    updateCartUI();
                    showToast('Reorder loaded successfully!', 'info');
                }

                // Check for URL params (memberId)
                const urlParams = new URLSearchParams(window.location.search);
                const mid = urlParams.get('memberId');
                if (mid) {
                    const memberRes = await fetch(`../../api/controllers/TerminalController.php?action=getMemberById&id=${mid}`);
                    const member = await memberRes.json();
                    if (member && !member.error) {
                        selectMember(member);
                    }
                }
            } catch (e) {
                console.error('Initial load failed:', e);
                showToast("Failed to load catalog.", "error");
            }
        }

        // Sync stock periodically
        setInterval(async () => {
            try {
                const res = await fetch('../../api/controllers/TerminalController.php?action=getBooks');
                const latest = await res.json();
                
                // Update local storage/cache silently
                allItems = latest;
                
                // Update stock badges on screen without re-rendering everything
                latest.forEach(item => {
                    const badges = document.querySelectorAll(`[data-item-id="${item.id}"]`);
                    badges.forEach(badge => {
                        badge.innerText = item.stock_qty > 0 ? `${item.stock_qty} in stock` : 'Out of stock';
                        badge.style.color = item.stock_qty > 5 ? 'var(--accent-mint)' : '#ef4444';
                    });
                });
            } catch(e) {}
        }, 15000);

        function switchCustomerTab(type) {
            customerType = type;
            document.querySelectorAll('.cust-tab').forEach(t => t.classList.remove('active'));
            event.target.classList.add('active');

            if (type === 'member') {
                document.getElementById('memberSection').style.display = 'block';
                document.getElementById('guestSection').style.display = 'none';
            } else {
                document.getElementById('memberSection').style.display = 'none';
                document.getElementById('guestSection').style.display = 'flex';
            }
        }

        function setPayMethod(method, btn) {
            // Deprecated for split payment, but kept for compatibility if needed
            paymentMethod = method;
            document.querySelectorAll('.pay-btn').forEach(b => b.classList.remove('selected'));
            if (btn) btn.classList.add('selected');
        }

        function calculateSplitPayment() {
            const totalPayable = parseFloat(document.getElementById('modalTotal').innerText.replace('৳', ''));
            const inputs = document.querySelectorAll('.pay-amount-input');
            let totalPaid = 0;
            
            inputs.forEach(input => {
                totalPaid += (parseFloat(input.value) || 0);
            });

            const remaining = totalPayable - totalPaid;
            const remainingEl = document.getElementById('paymentRemaining');
            remainingEl.innerText = '৳' + remaining.toFixed(2);
            
            if (Math.abs(remaining) < 0.01) {
                remainingEl.style.color = '#16a34a'; // Green
                document.getElementById('btnFinalOrder').disabled = false;
                document.getElementById('btnFinalOrder').style.opacity = '1';
                document.getElementById('btnFinalOrder').style.cursor = 'pointer';
            } else {
                remainingEl.style.color = '#ef4444'; // Red
                document.getElementById('btnFinalOrder').disabled = true;
                document.getElementById('btnFinalOrder').style.opacity = '0.5';
                document.getElementById('btnFinalOrder').style.cursor = 'not-allowed';
            }
        }

        let activeFilter = 'all';
        function setFilter(filter, btn) {
            activeFilter = filter;
            document.querySelectorAll('.filter-tab').forEach(t => {
                t.style.background = 'white';
                t.style.color = 'var(--text-muted)';
                t.style.borderColor = '#e2e8f0';
            });
            btn.style.background = 'var(--primary-blue)';
            btn.style.color = 'white';
            btn.style.borderColor = 'transparent';
            applyFilter();
        }

        function applyFilter() {
            const query = document.getElementById('bookSearch').value.toLowerCase();
            let filtered = allItems.filter(item => {
                const matchQuery = item.title.toLowerCase().includes(query) ||
                    (item.author && item.author.toLowerCase().includes(query)) ||
                    (item.isbn && item.isbn.includes(query)) ||
                    (item.category_name && item.category_name.toLowerCase().includes(query));

                const isItemInventory = item._isInventory == 1; 
                if (activeFilter === 'books') return matchQuery && !isItemInventory;
                if (activeFilter === 'inventory') return matchQuery && isItemInventory;
                return matchQuery;
            });

            // If query is empty and we have "all", just show everything
            const displayed = query ? filtered : (
                activeFilter === 'books' ? allItems.filter(i => i._isInventory == 0) :
                activeFilter === 'inventory' ? allItems.filter(i => i._isInventory == 1) :
                allItems
            );

            renderBooks(displayed);
            document.getElementById('bookCount').innerText = `${displayed.length} Items`;
        }

        function showToast(message, type = 'info') {
            const container = document.getElementById('toastContainer');
            const toast = document.createElement('div');
            toast.className = `toast ${type}`;

            let icon = 'fa-circle-info';
            if (type === 'success') icon = 'fa-circle-check';
            if (type === 'error') icon = 'fa-circle-exclamation';

            toast.innerHTML = `<i class="fa-solid ${icon}"></i> <span>${message}</span>`;
            container.appendChild(toast);

            setTimeout(() => {
                toast.classList.add('fade-out');
                setTimeout(() => toast.remove(), 300);
            }, 4000);
        }

        function numberToWords(number) {
            const words = {
                0: 'Zero', 1: 'One', 2: 'Two', 3: 'Three', 4: 'Four', 5: 'Five', 6: 'Six', 7: 'Seven', 8: 'Eight', 9: 'Nine',
                10: 'Ten', 11: 'Eleven', 12: 'Twelve', 13: 'Thirteen', 14: 'Fourteen', 15: 'Fifteen', 16: 'Sixteen', 17: 'Seventeen', 18: 'Eighteen', 19: 'Nineteen',
                20: 'Twenty', 30: 'Thirty', 40: 'Forty', 50: 'Fifty', 60: 'Sixty', 70: 'Seventy', 80: 'Eighty', 90: 'Ninety'
            };

            if (number in words) return words[number];

            let amountInWords = '';
            if (number >= 100) {
                amountInWords += numberToWords(Math.floor(number / 100)) + ' Hundred ';
                number %= 100;
            }

            if (number > 0) {
                if (amountInWords !== '') amountInWords += 'and ';
                if (number < 20) {
                    amountInWords += words[number];
                } else {
                    amountInWords += words[Math.floor(number / 10) * 10];
                    if (number % 10 > 0) {
                        amountInWords += '-' + words[number % 10];
                    }
                }
            }

            return amountInWords.trim();
        }

        function formatAmountInWords(amount) {
            const integerPart = Math.floor(amount);
            const decimalPart = Math.round((amount - integerPart) * 100);

            let result = numberToWords(integerPart) + ' Taka';
            if (decimalPart > 0) {
                result += ' and ' + numberToWords(decimalPart) + ' Paisa';
            }
            return result + ' Only';
        }

        function renderSkeletons() {
            const grid = document.getElementById('browseGrid');
            grid.innerHTML = '';
            // Show 12 skeleton cards
            for (let i = 0; i < 12; i++) {
                const card = document.createElement('div');
                card.className = 'book-pill';
                card.innerHTML = `
                    <div class="skeleton" style="width: 60px; height: 18px; border-radius: 30px; position: absolute; top: 15px; right: 15px; opacity: 0.6;"></div>
                    <div class="skeleton skeleton-thumb"></div>
                    <div class="book-info-compact">
                        <div class="skeleton skeleton-text"></div>
                        <div class="skeleton skeleton-text short"></div>
                        <div class="skeleton" style="height: 20px; width: 40%; margin-top: 10px;"></div>
                    </div>
                `;
                grid.appendChild(card);
            }
        }
        let displayedItems = [];
        let itemsToShow = 50;
        const INCREMENT = 50;

        function renderBooks(books, append = false) {
            const grid = document.getElementById('browseGrid');
            
            if (!append) {
                grid.innerHTML = '';
                grid.scrollTop = 0;
                itemsToShow = INCREMENT;
                displayedItems = books;
            }

            const chunk = displayedItems.slice(grid.children.length, itemsToShow);
            
            // Image base path
            const isProduction = window.location.hostname.includes('ontomeel.com');
            const imageBasePath = isProduction
                ? 'https://ontomeel.com/admin/assets/book-images/'
                : '../../../bookshop/admin/assets/book-images/';

            chunk.forEach(book => {
                const pill = document.createElement('div');
                pill.className = 'book-pill fade-in';
                pill.setAttribute('data-card-id', book.id);
                pill.onclick = () => addToCart(book);

                // Priority: If it has an image, always use book layout
                const hasImage = book.cover_image && book.cover_image.trim() !== '';
                const isBook = book.item_type && book.item_type.toLowerCase() === 'book';

                if (!hasImage && !isBook) {
                    // Generic Inventory item card (No image and not a Book)
                    const typeIcons = { 'Stationary': 'pen-ruler', 'Flowers': 'seedling', 'Accessories': 'plug', 'General': 'box' };
                    const icon = typeIcons[book.item_type] || 'box';
                    pill.onclick = () => addToCart(book);
                    pill.innerHTML = `
                        <div class="stock-badge" data-item-id="${book.id}" style="color: ${book.stock_qty > 5 ? 'var(--accent-mint)' : '#ef4444'}">
                            ${book.stock_qty > 0 ? `${book.stock_qty} in stock` : 'Out of stock'}
                        </div>
                        <div class="book-thumbnail" style="background: #f0fdf4; display:flex; align-items:center; justify-content:center;">
                            <i class="fa-solid fa-${icon}" style="font-size: 2.5rem; color: #16a34a;"></i>
                        </div>
                        <div class="book-info-compact">
                            <div class="book-name" title="${book.title}">${book.title}</div>
                            <div class="book-meta" style="color: #16a34a; font-weight: 700;">${book.item_type}</div>
                            ${book.isbn ? `<div class="book-isbn">SKU: ${book.isbn}</div>` : ''}
                            <div class="book-price-tag">৳${parseFloat(book.sell_price).toFixed(2)}</div>
                        </div>
                    `;
                } else {
                    // Book card
                    book.displayImage = '';
                    if (book.cover_image) {
                        book.displayImage = book.cover_image.startsWith('http')
                            ? book.cover_image
                            : imageBasePath + book.cover_image;
                    }
                    pill.onclick = () => addToCart(book);
                    pill.innerHTML = `
                        <div class="stock-badge" data-item-id="${book.id}" style="color: ${book.stock_qty > 5 ? 'var(--accent-mint)' : '#ef4444'}">
                            ${book.stock_qty > 0 ? `${book.stock_qty} in stock` : 'Out of stock'}
                        </div>
                        <div class="book-thumbnail">
                            ${book.displayImage
                            ? `<img src="${book.displayImage}" loading="lazy" alt="${book.title}" onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name=${encodeURIComponent(book.title)}&background=eff6ff&color=2563eb&bold=true'">`
                            : '<i class="fa-solid fa-book-open" style="font-size: 2.5rem; color: #cbd5e1;"></i>'}
                        </div>
                        <div class="book-info-compact">
                            <div class="book-name" title="${book.title}">${book.title}</div>
                            <div class="book-meta">${book.author || ''}</div>
                            ${book.isbn ? `<div class="book-isbn">ISBN: ${book.isbn}</div>` : ''}
                            <div class="book-price-tag">৳${parseFloat(book.sell_price).toFixed(2)}</div>
                        </div>
                    `;
                }
                grid.appendChild(pill);
            });
        }

        // Initialize scroll listener for lazy loading
        document.addEventListener('DOMContentLoaded', () => {
            const grid = document.getElementById('browseGrid');
            if (grid) {
                grid.addEventListener('scroll', () => {
                    if (grid.scrollTop + grid.clientHeight >= grid.scrollHeight - 200) {
                        if (grid.children.length < displayedItems.length) {
                            itemsToShow += INCREMENT;
                            renderBooks(displayedItems, true);
                        }
                    }
                });
            }
        });

        function filterBooks() {
            applyFilter();
        }

        function addToCart(book) {
            if (book.stock_qty <= 0) {
                alert('This book is currently out of stock.');
                return;
            }
            cart.push({ ...book, cartId: Date.now() + Math.random() });
            updateCartUI();

            // Show feedback
            const status = document.getElementById('cartStatus');
            status.style.display = 'block';
            setTimeout(() => status.style.display = 'none', 1500);
        }

        function removeFromCart(cartId) {
            cart = cart.filter(item => item.cartId !== cartId);
            updateCartUI();
        }

        function clearCart() {
            cart = [];
            lastDiscountType = 'amount';
            updateCartUI();
            showToast("Cart cleared!", "info");
            const discountInput = document.getElementById('cartDiscount');
            const discountPercentInput = document.getElementById('cartDiscountPercent');
            if (discountInput) discountInput.value = '';
            if (discountPercentInput) discountPercentInput.value = '';
        }

        function applyDiscount(type) {
            const subtotal = calculateSubtotal();
            const amountInput = document.getElementById('cartDiscount');
            const percentInput = document.getElementById('cartDiscountPercent');

            if (subtotal === 0) {
                amountInput.value = '';
                percentInput.value = '';
                updateCartUI();
                return;
            }

            if (type === 'percent') {
                lastDiscountType = 'percent';
                const percent = parseFloat(percentInput.value) || 0;
                if (percent > 100) {
                    percentInput.value = 100;
                    amountInput.value = subtotal.toFixed(2);
                } else if (percent < 0) {
                    percentInput.value = 0;
                    amountInput.value = '';
                } else {
                    const amount = (subtotal * percent) / 100;
                    amountInput.value = amount > 0 ? amount.toFixed(2) : '';
                }
            } else {
                lastDiscountType = 'amount';
                const amount = parseFloat(amountInput.value) || 0;
                if (amount > subtotal) {
                    amountInput.value = subtotal.toFixed(2);
                    percentInput.value = 100;
                } else if (amount < 0) {
                    amountInput.value = 0;
                    percentInput.value = '';
                } else {
                    const percent = (amount / subtotal) * 100;
                    percentInput.value = percent > 0 ? percent.toFixed(2) : '';
                }
            }

            updateCartUI();
        }
        function calculateSubtotal() {
            return cart.reduce((sum, item) => sum + parseFloat(item.sell_price), 0);
        }

        function getDiscount() {
            const input = document.getElementById('cartDiscount');
            if (!input) return 0;
            let val = parseFloat(input.value) || 0;
            return val > 0 ? val : 0;
        }

        function calculateTotal() {
            let subtotal = calculateSubtotal();
            let discount = getDiscount();
            if (discount > subtotal) discount = subtotal;
            return subtotal - discount;
        }

        function updateCartUI() {
            const list = document.getElementById('cartList');
            const totalEl = document.getElementById('cartTotal');
            const subtotalEl = document.getElementById('cartSubtotal');
            const countEl = document.getElementById('cartCount');
            const discountInput = document.getElementById('cartDiscount');

            if (cart.length === 0) {
                list.innerHTML = `<div style="text-align: center; color: var(--text-muted); margin-top: 5rem; padding: 2rem;"><div style="position: relative; display: inline-block;"><i class="fa-solid fa-basket-shopping" style="font-size: 3.5rem; opacity: 0.05; display: block; margin-bottom: 1.5rem;"></i><i class="fa-solid fa-plus" style="position: absolute; bottom: 15px; right: -5px; font-size: 1.2rem; opacity: 0.2; color: var(--primary-blue);"></i></div><div style="font-weight: 700; font-size: 0.9rem; opacity: 0.4;">Your cart is empty</div><div style="font-size: 0.75rem; opacity: 0.4; margin-top: 4px;">Search and add books to start</div></div>`;
                totalEl.innerText = '৳0.00';
                if (subtotalEl) subtotalEl.innerText = '৳0.00';
                countEl.innerText = '0';
                if (discountInput) discountInput.value = '';
                const discountPercentInput = document.getElementById('cartDiscountPercent');
                if (discountPercentInput) discountPercentInput.value = '';
                return;
            }

            list.innerHTML = '';

            cart.forEach((item) => {
                const row = document.createElement('div');
                row.className = 'cart-row fade-in';
                row.innerHTML = `
                    <img src="${item.displayImage}" class="cart-img" onerror="this.src='https://ui-avatars.com/api/?name=${encodeURIComponent(item.title)}&background=eff6ff&color=2563eb&bold=true'">
                    <div class="cart-details">
                        <div style="font-size: 0.9rem; font-weight: 700; color: var(--text-header); line-height: 1.2; overflow: hidden; text-overflow: ellipsis; display: -webkit-box; -webkit-line-clamp: 2; line-clamp: 2; -webkit-box-orient: vertical;">${item.title}</div>
                        <div style="font-size: 0.85rem; color: var(--primary-blue); font-weight: 700; margin-top: 4px;">৳${parseFloat(item.sell_price).toFixed(2)}</div>
                    </div>
                    <button onclick="removeFromCart(${item.cartId})" style="background: rgba(239, 68, 68, 0.05); border: none; color: #ef4444; width: 32px; height: 32px; border-radius: 8px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s;"><i class="fa-solid fa-times"></i></button>
                `;
                list.appendChild(row);
            });

            const subtotal = calculateSubtotal();
            const percentInput = document.getElementById('cartDiscountPercent');
            
            // Re-sync discount if it was percentage based
            if (lastDiscountType === 'percent' && percentInput && percentInput.value !== '' && document.activeElement !== percentInput && document.activeElement !== discountInput) {
                const percent = parseFloat(percentInput.value) || 0;
                const amount = (subtotal * percent) / 100;
                if (discountInput) discountInput.value = amount > 0 ? amount.toFixed(2) : '';
            } else if (lastDiscountType === 'amount' && discountInput && discountInput.value !== '' && document.activeElement !== discountInput && document.activeElement !== percentInput) {
                // Re-sync percentage if it was amount based
                const amount = parseFloat(discountInput.value) || 0;
                const percent = subtotal > 0 ? (amount / subtotal) * 100 : 0;
                if (percentInput) percentInput.value = percent > 0 ? percent.toFixed(2) : '';
            }

            let discount = getDiscount();
            if (discount > subtotal) {
                discount = subtotal;
                // Only update the input directly if the user isn't currently typing inside it to avoid frustrating input jank
                if (discountInput && document.activeElement !== discountInput) discountInput.value = discount;
            }
            const total = subtotal - discount;

            if (subtotalEl) subtotalEl.innerText = '৳' + subtotal.toFixed(2);
            totalEl.innerText = '৳' + total.toFixed(2);
            countEl.innerText = cart.length;
        }

        let selectedMember = null;
        let memberSearchTimer = null;

        let searchResults = [];
        async function searchMembers() {
            const query = document.getElementById('memberSearch').value;
            const resultsBox = document.getElementById('memberSearchResults');

            if (query.length < 2) {
                resultsBox.style.display = 'none';
                return;
            }

            // Debounce
            clearTimeout(memberSearchTimer);
            memberSearchTimer = setTimeout(async () => {
                try {
                    const response = await fetch(`../../api/controllers/TerminalController.php?action=searchMembers&q=${encodeURIComponent(query)}`);
                    searchResults = await response.json();

                    if (searchResults.length > 0) {
                        resultsBox.innerHTML = searchResults.map((m, index) => `
                            <div class="result-item" onclick="selectMemberByIndex(${index})">
                                <span class="result-name">${m.full_name}</span>
                                <span class="result-id">${m.membership_id} • Balance: ৳${m.acc_balance}</span>
                            </div>
                        `).join('');
                        resultsBox.style.display = 'block';
                    } else {
                        resultsBox.innerHTML = '<div class="result-item" style="cursor: default;">No members found</div>';
                        resultsBox.style.display = 'block';
                    }
                } catch (e) {
                    console.error('Member search error:', e);
                }
            }, 300);
        }
        function selectMemberByIndex(index) {
            selectMember(searchResults[index]);
        }

        function selectMember(member) {
            selectedMember = member;
            document.getElementById('memberSearch').value = '';
            document.getElementById('memberSearchResults').style.display = 'none';

            document.getElementById('selMemberName').innerText = member.full_name;
            document.getElementById('selMemberPlan').innerText = member.membership_plan || 'None';
            document.getElementById('selMemberPlan').style.display = member.membership_plan === 'None' ? 'none' : 'inline-block';
            document.getElementById('selMemberId').innerText = `MEMBERSHIP ID: ${member.membership_id}`;
            document.getElementById('selMemberBal').innerText = `৳${member.acc_balance}`;

            const expiry = member.plan_expire_date ? new Date(member.plan_expire_date).toLocaleDateString() : 'N/A';
            document.getElementById('selMemberExpiry').innerText = expiry;
            document.getElementById('selMemberExpiry').style.color = expiry === 'N/A' ? '#94a3b8' : '#e11d48';

            document.getElementById('selectedMember').style.display = 'flex';

            // Check if we need to auto-trigger Return Modal
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('action') === 'return') {
                openReturnModal();
            }
        }

        async function openReturnModal() {
            if (!selectedMember) return;
            document.getElementById('returnMemberName').innerText = selectedMember.full_name;
            document.getElementById('returnModal').style.display = 'flex';

            const container = document.getElementById('borrowListContainer');
            container.innerHTML = '<div style="text-align:center; padding:20px;"><i class="fa-solid fa-spinner fa-spin"></i> Loading borrows...</div>';

            try {
                const res = await fetch(`../../api/controllers/TerminalController.php?action=getBorrowedBooks&memberId=${selectedMember.id}`);
                const borrows = await res.json();

                if (borrows.length === 0) {
                    container.innerHTML = '<div style="text-align:center; padding:20px; color:var(--text-muted);">No active borrows found for this member.</div>';
                } else {
                    container.innerHTML = borrows.map(b => `
                        <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; padding:12px; margin-bottom:8px; display:flex; justify-content:space-between; align-items:center;">
                            <div>
                                <div style="font-weight:700; font-size:0.9rem;">${b.title}</div>
                                <div style="font-size:0.75rem; color:var(--text-muted);">Due: ${new Date(b.due_date).toLocaleDateString()}</div>
                            </div>
                            <input type="checkbox" class="return-check" value="${b.borrow_id}" style="width:20px; height:20px; cursor:pointer;">
                        </div>
                    `).join('');
                }
            } catch (e) {
                container.innerHTML = '<div style="padding:20px; color:#ef4444;">Error loading borrows.</div>';
            }
        }

        function closeReturnModal() {
            document.getElementById('returnModal').style.display = 'none';
        }

        async function processReturn() {
            const checks = document.querySelectorAll('.return-check:checked');
            if (checks.length === 0) return alert("Please select books to return.");

            const borrowIds = Array.from(checks).map(c => c.value);
            const btn = document.querySelector('#returnModal .btn-sell');
            btn.disabled = true;
            btn.innerText = "PROCESSING...";

            try {
                const res = await fetch('../../api/controllers/TerminalController.php?action=returnBooks', {
                    method: 'POST',
                    body: JSON.stringify({ borrowIds: borrowIds })
                });
                if ((await res.json()).success) {
                    showToast("Books returned successfully!", "success");
                    closeReturnModal();
                    clearMember();
                }
            } catch (e) {
                showToast("Failed to process return.", "error");
            } finally {
                btn.disabled = false;
                btn.innerText = "CONFIRM RETURN";
            }
        }

        function openMemberModal() {
            document.getElementById('memberModal').style.display = 'flex';
        }

        function closeMemberModal() {
            document.getElementById('memberModal').style.display = 'none';
            document.getElementById('newMemberName').value = '';
            document.getElementById('newMemberPhone').value = '';
            document.getElementById('newMemberEmail').value = '';
        }

        async function saveNewMember() {
            const name = document.getElementById('newMemberName').value;
            const phone = document.getElementById('newMemberPhone').value;
            const email = document.getElementById('newMemberEmail').value;

            if (!name || !phone) {
                showToast('Name and Phone are required.', 'error');
                return;
            }

            const btn = document.getElementById('btnSaveMember');
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> SAVING...';

            try {
                const response = await fetch('../../api/controllers/TerminalController.php?action=registerMember', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ full_name: name, phone: phone, email: email })
                });

                const result = await response.json();
                if (result.success) {
                    showToast('✓ Member Registered Successfully!', 'success');
                    selectMember(result.member);
                    closeMemberModal();
                } else {
                    showToast('Error: ' + result.error, 'error');
                }
            } catch (e) {
                showToast('Failed to register member.', 'error');
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-user-plus"></i> CREATE MEMBER';
            }
        }
        function clearMember() {
            selectedMember = null;
            document.getElementById('selectedMember').style.display = 'none';
        }
        function finalize(type) {

            if (cart.length === 0) {
                showToast('Please add items to the cart first.', 'error');
                return;
            }

            if (customerType === 'member' && !selectedMember) {
                showToast('Please select a member first.', 'error');
                return;
            }

            if (customerType === 'guest') {
                const name = document.getElementById('guestName').value;
                const phone = document.getElementById('guestPhone').value;
                if (!name || !phone) {
                    showToast('Please enter guest name and phone number.', 'error');
                    return;
                }
            }

            if (type === 'sell') {
                const totalEl = document.getElementById('cartTotal');
                const totalAmount = totalEl.innerText;
                document.getElementById('modalTotal').innerText = totalAmount;
                document.getElementById('paymentRemaining').innerText = totalAmount;
                
                // Clear inputs
                document.querySelectorAll('.pay-amount-input').forEach(i => i.value = '');
                
                // Show/hide wallet row based on customer type
                const walletRow = document.getElementById('walletPayRow');
                if (customerType === 'member' && selectedMember) {
                    walletRow.style.display = 'flex';
                } else {
                    walletRow.style.display = 'none';
                }

                // Auto-fill first method (Cash) with full amount for convenience
                const inputs = document.querySelectorAll('.pay-amount-input');
                if (inputs[0]) {
                    inputs[0].value = parseFloat(totalAmount.replace('৳', '')).toFixed(2);
                }
                
                document.getElementById('checkoutModal').style.display = 'flex';
                calculateSplitPayment();
            } else {
                // Issue / Borrow — only books allowed
                const hasNonBook = cart.some(item => item._isInventory == 1);
                if (hasNonBook) {
                    showToast('Only books can be issued/borrowed. Remove inventory items from the cart.', 'error');
                    return;
                }

                if (customerType === 'guest') {
                    showToast('Borrowing is only allowed for registered members.', 'error');
                    return;
                }

                const date = new Date();
                date.setDate(date.getDate() + 15);
                document.getElementById('dueDate').value = date.toISOString().split('T')[0];
                document.getElementById('borrowCountText').innerText = cart.length;
                document.getElementById('borrowModal').style.display = 'flex';
            }
        }

        function closeBorrowModal() {
            document.getElementById('borrowModal').style.display = 'none';
        }

        function closeCheckout() {
            document.getElementById('checkoutModal').style.display = 'none';
        }

        async function processOrder() {
            const totalAmount = parseFloat(document.getElementById('modalTotal').innerText.replace('৳', ''));
            const inputs = document.querySelectorAll('.pay-amount-input');
            let paymentDetails = [];
            let walletAmount = 0;

            inputs.forEach(input => {
                const amount = parseFloat(input.value) || 0;
                if (amount > 0) {
                    const method = input.getAttribute('data-method');
                    paymentDetails.push(`${method}: ৳${amount.toFixed(2)}`);
                    if (method === 'Wallet') {
                        walletAmount = amount;
                    }
                }
            });

            const paymentSummary = paymentDetails.join(', ');

            if (walletAmount > 0) {
                if (!selectedMember) {
                    showToast('Please select a member to use wallet payment.', 'error');
                    return;
                }
                if (parseFloat(selectedMember.acc_balance) < walletAmount) {
                    showToast(`Insufficient balance! Member has ৳${selectedMember.acc_balance} but ৳${walletAmount.toFixed(2)} is required from wallet.`, 'error');
                    return;
                }
            }

            const checkoutBtn = document.querySelector('#checkoutModal .btn-sell');
            const originalContent = checkoutBtn.innerHTML;
            checkoutBtn.disabled = true;
            checkoutBtn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> PROCESSING...';

            const orderData = {
                items: cart,
                subtotal: calculateSubtotal(),
                discount: getDiscount(),
                total: calculateTotal(),
                memberId: customerType === 'member' ? selectedMember.id : null,
                paymentMethod: paymentSummary, // Consolidated methods
                walletAmount: walletAmount,    // Send separately for balance deduction
                paymentMethodMain: paymentDetails.length > 1 ? 'Split Payment' : (paymentDetails[0] ? paymentDetails[0].split(':')[0] : 'Cash'),
                guestName: customerType === 'guest' ? document.getElementById('guestName').value : null,
                guestPhone: customerType === 'guest' ? document.getElementById('guestPhone').value : null,
                guestEmail: customerType === 'guest' ? document.getElementById('guestEmail').value : null,
                orderDate: document.getElementById('orderDateOverride').value // Backdate support
            };

            try {
                const response = await fetch('../../api/controllers/TerminalController.php?action=saveOrder', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(orderData)
                });

                const result = await response.json();

                if (result.success) {
                    showToast('✓ Transaction Successful!', 'success');

                    // Trigger Invoice Modal
                    prepareInvoice(result.invoice_no, orderData);

                    cart = [];
                    updateCartUI();
                    if (customerType === 'guest') {
                        document.getElementById('guestName').value = '';
                        document.getElementById('guestPhone').value = '';
                        document.getElementById('guestEmail').value = '';
                    } else {
                        clearMember();
                    }
                    closeCheckout();
                } else {
                    showToast('Error: ' + result.error, 'error');
                }
            } catch (e) {
                console.error('Order processing error:', e);
                showToast('Failed to process order. Please try again.', 'error');
            } finally {
                checkoutBtn.disabled = false;
                checkoutBtn.innerHTML = originalContent;
            }
        }

        function prepareInvoice(invoiceNo, payload) {
            const container = document.getElementById('invoicePrintContainer');

            const grouped = {};
            payload.items.forEach(item => {
                if (grouped[item.id]) {
                    grouped[item.id].qty += 1;
                } else {
                    grouped[item.id] = { ...item, qty: 1 };
                }
            });

            const itemsHtml = Object.values(grouped).map(item => `
                <tr>
                    <td>${item.title}</td>
                    <td>${item.qty}</td>
                    <td style="text-align: right;">৳${(parseFloat(item.sell_price) * item.qty).toFixed(2)}</td>
                </tr>
            `).join('');

            const customerName = payload.memberId ? selectedMember.full_name : (payload.guestName || 'Cash Customer');
            const customerPhone = payload.memberId ? selectedMember.phone : (payload.guestPhone || '--');
            const memberIdField = payload.memberId ? `<div class="receipt-row"><span>Member ID:</span> <span>${selectedMember.membership_id || ''}</span></div>` : '';
            
            // Format split balance breakdown or single method
            let paymentSectionHtml = '';
            if (payload.paymentMethod && payload.paymentMethod.includes(':')) {
                paymentSectionHtml = payload.paymentMethod.split(', ').map(line => {
                    const parts = line.split(': ');
                    const method = parts[0] || 'Unknown';
                    const amount = parts[1] || '';
                    return `<div class="receipt-row"><span>Paid via ${method}:</span> <span>${amount}</span></div>`;
                }).join('');
            } else {
                paymentSectionHtml = `<div class="receipt-row"><span>Payment Method:</span> <span>${payload.paymentMethod || 'Cash'}</span></div>`;
            }

            container.innerHTML = `
                <div class="invoice-receipt">
                    <div class="receipt-header">
                        <img src="../assets/logo.webp" alt="Logo">
                        <h2>ONTOMEEL</h2>
                        <p style="font-weight:700;">Library & Bookstore</p>
                        <p>Shop no 06, Changing Closet Building, Motel Labonee, Motel Road, Cox's Bazar</p>
                        <p>Phone: 01330975787 | Email: info@ontomeel.com</p>
                    </div>
                    
                    <div class="receipt-info" style="border-top:1px dashed #000; border-bottom:1px dashed #000;">
                        <div class="receipt-row"><span>Date:</span> <span>${new Date().toLocaleString()}</span></div>
                        <div class="receipt-row"><span>Inv #:</span> <span>${invoiceNo}</span></div>
                        <div class="receipt-row"><span>Sold by:</span> <span style="text-transform:capitalize;">${window.posUserName || 'Admin'}</span></div>
                    </div>
                    
                    <div class="receipt-info">
                        <div class="receipt-row"><span>Customer:</span> <span>${customerName}</span></div>
                        <div class="receipt-row"><span>Phone:</span> <span>${customerPhone}</span></div>
                        ${memberIdField}
                    </div>
                    
                    <table class="receipt-items">
                        <thead>
                            <tr>
                                <th style="width: 50%;">Item</th>
                                <th style="width: 20%;">Qty</th>
                                <th style="width: 30%; text-align: right;">Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${itemsHtml}
                        </tbody>
                    </table>
                    
                    <div class="receipt-totals">
                        <div class="receipt-row"><span>Subtotal:</span> <span>৳${(payload.subtotal || payload.total).toFixed(2)}</span></div>
                        <div class="receipt-row"><span>Discount:</span> <span>৳${(payload.discount || 0).toFixed(2)}</span></div>
                        <div class="receipt-row total-bold"><span>Total:</span> <span>৳${payload.total.toFixed(2)}</span></div>
                    </div>

                    <div class="in-words">
                        <strong>Amount in Words:</strong><br>
                        ${formatAmountInWords(payload.total)}
                    </div>

                    <div style="margin-top:15px; border-top:1px dotted #000; padding-top:10px;">
                        <div style="font-weight: 800; text-transform: uppercase; font-size: 10px; margin-bottom: 5px;">Payment Details</div>
                        ${paymentSectionHtml}
                    </div>
                    
                    <div class="barcode-container">
                        <svg id="receipt-barcode"></svg>
                    </div>

                    <div style="margin-top: 15px; text-align: center; opacity: 0.8;">
                        <p style="margin: 0; font-size: 11px; font-weight: 800;">Thank You for Shopping!</p>
                        <p style="margin: 0; font-size: 9px;">Software by VivaGo Digital</p>
                    </div>
                </div>
            `;

            // Draw Barcode after DOM update
            setTimeout(() => {
                JsBarcode("#receipt-barcode", invoiceNo, {
                    format: "CODE128",
                    width: 1.2,
                    height: 35,
                    displayValue: false,
                    margin: 0
                });

                // Mirror for preview in modal
                const preview = document.getElementById('receiptPreviewContent');
                preview.innerHTML = container.innerHTML;
                
                // Need to re-fire barcode for the preview SVG as well
                const previewBarcode = preview.querySelector('#receipt-barcode');
                if (previewBarcode) {
                    previewBarcode.id = "receipt-barcode-preview";
                    JsBarcode("#receipt-barcode-preview", invoiceNo, {
                        format: "CODE128",
                        width: 1.2,
                        height: 35,
                        displayValue: false,
                        margin: 0
                    });
                }
            }, 100);

            document.getElementById('invoiceModal').style.display = 'flex';
        }
        function closeInvoiceModal() {
            document.getElementById('invoiceModal').style.display = 'none';
        }

        let parkedCarts = JSON.parse(localStorage.getItem('parkedCarts') || '[]');

        function holdCart() {
            if (cart.length === 0) return showToast("Nothing to hold!", "info");

            const parker = {
                id: Date.now(),
                cart: [...cart],
                member: selectedMember ? { ...selectedMember } : null,
                time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
            };

            parkedCarts.push(parker);
            localStorage.setItem('parkedCarts', JSON.stringify(parkedCarts));

            showToast("Cart parked", "success");
            cart = [];
            selectedMember = null;
            updateCartUI();
            clearMember();
            updateParkedUI();
        }

        function toggleParked() {
            const list = document.getElementById('parkedList');
            list.style.display = list.style.display === 'none' ? 'block' : 'none';
        }

        function updateParkedUI() {
            const count = parkedCarts.length;
            const badge = document.getElementById('parkedCountBadge');

            if (badge) {
                badge.innerText = count;
                badge.style.display = count > 0 ? 'flex' : 'none';
            }

            const list = document.getElementById('parkedList');
            if (!list) return;

            if (count === 0) {
                list.innerHTML = '<div style="padding:30px; text-align:center; color:#94a3b8; font-size:0.85rem;"><i class="fa-solid fa-inbox" style="display:block; font-size:2rem; margin-bottom:10px; opacity:0.3;"></i>No parked carts</div>';
                return;
            }

            list.innerHTML = `
                <div style="padding:15px; border-bottom:1px solid #f1f5f9; background:#f8fafc; font-weight:800; font-size:0.75rem; color:#64748b; letter-spacing:0.5px; text-transform:uppercase;">Active Sessions</div>
                ${parkedCarts.map((p, idx) => `
                    <div style="padding:15px; border-bottom:1px solid #f1f5f9; display:flex; justify-content:space-between; align-items:center; transition:0.2s; cursor:default;" onmouseover="this.style.background='#f0f7ff'" onmouseout="this.style.background='white'">
                        <div style="text-align:left;">
                            <div style="font-weight:700; font-size:0.85rem; color:var(--text-header);">${p.member ? p.member.full_name : 'Guest Customer'}</div>
                            <div style="font-size:0.75rem; color:var(--text-muted); display:flex; align-items:center; gap:5px;">
                                <i class="fa-solid fa-clock" style="font-size:0.65rem;"></i> ${p.time} • ${p.cart.length} items
                            </div>
                        </div>
                        <div style="display:flex; gap:8px;">
                            <button onclick="resumeCart(${p.id})" style="background:var(--primary-blue); color:white; border:none; width:34px; height:34px; border-radius:10px; cursor:pointer; display:flex; align-items:center; justify-content:center; box-shadow:0 4px 6px rgba(37, 99, 235, 0.2);"><i class="fa-solid fa-play" style="font-size:0.8rem;"></i></button>
                            <button onclick="deleteParked(${p.id})" style="background:#fef2f2; color:#ef4444; border:none; width:34px; height:34px; border-radius:10px; cursor:pointer; display:flex; align-items:center; justify-content:center;"><i class="fa-solid fa-trash-can" style="font-size:0.8rem;"></i></button>
                        </div>
                    </div>
                `).join('')}
            `;
        }

        function resumeCart(id) {
            if (cart.length > 0) {
                if (!confirm("Current cart will be overwritten. Proceed?")) return;
            }

            const p = parkedCarts.find(x => x.id === id);
            cart = p.cart;
            selectedMember = p.member;

            updateCartUI();
            if (selectedMember) selectMember(selectedMember);

            deleteParked(id);
            toggleParked();
            showToast("Cart resumed", "info");
        }

        function deleteParked(id) {
            parkedCarts = parkedCarts.filter(x => x.id !== id);
            localStorage.setItem('parkedCarts', JSON.stringify(parkedCarts));
            updateParkedUI();
        }

        async function processBorrow() {
            const borrowBtn = document.querySelector('#borrowModal .btn-issue');
            const originalContent = borrowBtn.innerHTML;
            borrowBtn.disabled = true;
            borrowBtn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> ISSUING...';

            const borrowData = {
                memberId: selectedMember.id,
                dueDate: document.getElementById('dueDate').value,
                items: cart.map(item => ({
                    id: item.id,
                    title: item.title
                }))
            };

            try {
                const response = await fetch('../../api/controllers/TerminalController.php?action=saveBorrow', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(borrowData)
                });

                const result = await response.json();

                if (result.success) {
                    showToast('✓ Books Issued Successfully!', 'success');
                    cart = [];
                    updateCartUI();
                    clearMember();
                    closeBorrowModal();
                } else {
                    showToast('Error: ' + result.error, 'error');
                }
            } catch (e) {
                console.error('Borrow process error:', e);
                showToast('Failed to issue books. Please try again.', 'error');
            } finally {
                borrowBtn.disabled = false;
                borrowBtn.innerHTML = originalContent;
            }
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.member-card')) {
                const results = document.getElementById('memberSearchResults');
                if (results) results.style.display = 'none';
            }
        });

        window.onload = init;
    </script>
</body>

</html>