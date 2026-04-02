<?php require_once '../../api/shared/auth_check.php'; checkAuth(true); renderUserUI(true); ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ontomeel POS | Import Products</title>
    <link rel="stylesheet" href="../assets/pos-styles.css?v=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        .import-card {
            background: white;
            border-radius: 20px;
            padding: 2.5rem;
            max-width: 600px;
            width: 100%;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            margin: 2rem auto;
        }

        .upload-area {
            border: 2px dashed #e2e8f0;
            border-radius: 12px;
            padding: 3rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
        }

        .upload-area:hover {
            border-color: var(--primary-blue);
            background: #f8fafc;
        }

        .upload-area i {
            font-size: 3rem;
            color: var(--primary-blue);
            margin-bottom: 1rem;
        }

        #csvFile {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            opacity: 0;
            cursor: pointer;
        }

        .btn-import {
            width: 100%;
            padding: 1rem;
            background: var(--primary-blue);
            color: white;
            border: none;
            border-radius: 12px;
            font-weight: 700;
            margin-top: 1.5rem;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-import:hover {
            background: var(--primary-blue-dark);
            transform: translateY(-1px);
        }

        .results-box {
            margin-top: 1.5rem;
            padding: 1rem;
            border-radius: 12px;
            display: none;
            max-height: 400px;
            overflow-y: auto;
        }

        .success-box {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            color: #166534;
        }

        .error-box {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
            max-height: 300px;
            overflow-y: auto;
        }

        .mapping-info {
            margin-top: 2rem;
            background: #f8fafc;
            padding: 1.5rem;
            border-radius: 12px;
        }

        .mapping-info h4 {
            margin-bottom: 1rem;
            font-size: 0.95rem;
            color: #1e293b;
        }

        .mapping-list {
            list-style: none;
            padding: 0;
            font-size: 0.85rem;
        }

        .mapping-list li {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #e2e8f0;
        }

        .mapping-list li:last-child {
            border-bottom: none;
        }

        .header-tag {
            font-family: monospace;
            background: #e2e8f0;
            padding: 2px 6px;
            border-radius: 4px;
            font-weight: 700;
        }
    </style>
</head>

<body>

    <script src="../assets/sidebar.js"></script>

    <div class="main-wrapper">
        <header>
            <div class="page-title">
                <h1>Import <span style="color: var(--primary-blue);">Products</span></h1>
                <p>Bulk upload books and inventory items from a CSV file.</p>
            </div>
            <div class="header-tools">
                <a href="inventory.php" class="btn-secondary" style="border: 1px solid var(--border-light); padding: 0.8rem 1.2rem; border-radius: 10px; font-weight: 600; text-decoration: none; color: inherit;">
                    Back to Inventory
                </a>
            </div>
        </header>

        <main style="padding: 2rem;">
            <div class="import-card">
                <form id="importForm">
                    <div class="upload-area" id="dropZone">
                        <i class="fa-solid fa-box-open"></i>
                        <h3 style="margin-bottom: 0.5rem;">Select Product CSV</h3>
                        <p style="color: var(--text-muted); font-size: 0.9rem;">Drag & drop your CSV file here, or click to browse.</p>
                        <input type="file" name="csv_file" id="csvFile" accept=".csv" required>
                    </div>
                    
                    <div id="fileInfo" style="margin-top: 1rem; display: none; font-size: 0.9rem; font-weight: 600; color: var(--primary-blue);">
                        Selected: <span id="fileName"></span>
                    </div>

                    <div class="mapping-info">
                        <h4>Required CSV Headers:</h4>
                        <ul class="mapping-list">
                            <li><span>Product Name</span> <span class="header-tag">name</span></li>
                            <li><span>Category (Book/Other)</span> <span class="header-tag">category</span></li>
                            <li><span>Barcode / ISBN</span> <span class="header-tag">barcode</span></li>
                            <li><span>Selling Price</span> <span class="header-tag">selling_price</span></li>
                            <li><span>Purchase Price</span> <span class="header-tag">purchase_price</span></li>
                            <li><span>Stock Quantity</span> <span class="header-tag">opening_stock_qty</span></li>
                            <li><span>Author (for Books)</span> <span class="header-tag">author</span></li>
                            <li><span>Subcategory / Genre</span> <span class="header-tag">subcategory</span></li>
                        </ul>
                        <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 10px;">
                            * If category is "Book", the item will be added to the Books table.
                        </p>
                    </div>

                    <button type="submit" class="btn-import" id="submitBtn">
                        <i class="fa-solid fa-cloud-upload" style="margin-right: 8px;"></i> Start Import
                    </button>
                </form>

                <div id="results" class="results-box"></div>
            </div>
        </main>
    </div>

    <script>
        const csvFile = document.getElementById('csvFile');
        const fileInfo = document.getElementById('fileInfo');
        const fileName = document.getElementById('fileName');
        const importForm = document.getElementById('importForm');
        const results = document.getElementById('results');
        const submitBtn = document.getElementById('submitBtn');

        csvFile.addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                fileName.textContent = e.target.files[0].name;
                fileInfo.style.display = 'block';
            }
        });

        importForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const formData = new FormData(importForm);
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Processing...';
            results.style.display = 'none';

            try {
                const response = await fetch('../../api/controllers/ProductImportController.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    results.className = 'results-box success-box';
                    results.innerHTML = `
                        <div style="font-weight: 800; margin-bottom: 5px;">Import Successful!</div>
                        <div>Successfully imported <strong>${data.count}</strong> items.</div>
                        ${data.skipped > 0 ? `<div style="margin-top: 5px;">Skipped <strong>${data.skipped}</strong> existing items (duplicates).</div>` : ''}
                        ${data.errors.length > 0 ? `
                            <div style="margin-top: 10px; font-size: 0.8rem; color: #991b1b;">
                                <strong>Errors (${data.errors.length}):</strong>
                                <ul style="margin-top: 5px; padding-left: 15px;">
                                    ${data.errors.map(err => `<li>${err}</li>`).join('')}
                                </ul>
                            </div>
                        ` : ''}
                    `;
                } else {
                    results.className = 'results-box error-box';
                    results.innerHTML = `
                        <div style="font-weight: 800; margin-bottom: 5px;">Import Failed</div>
                        <div>${data.message}</div>
                    `;
                }
            } catch (error) {
                results.className = 'results-box error-box';
                results.innerHTML = 'An unexpected error occurred during import.';
            } finally {
                results.style.display = 'block';
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fa-solid fa-cloud-upload" style="margin-right: 8px;"></i> Start Import';
            }
        });
    </script>
</body>

</html>
