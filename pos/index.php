<?php require_once '../api/shared/auth_check.php'; checkAuth(); renderUserUI(); ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ontomeel POS | Library Dashboard</title>
    <link rel="stylesheet" href="assets/pos-styles.css?v=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(15, 23, 42, 0.4);
            backdrop-filter: blur(8px);
            z-index: 1000;
            display: none;
            align-items: center;
            justify-content: center;
        }

        .modal-card {
            background: white;
            width: 600px;
            max-width: 95%;
            border-radius: 28px;
            padding: 2.5rem;
            box-shadow: 0 25px 70px -10px rgba(15, 23, 42, 0.2);
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

        .search-results {
            background: #f8fafc;
            border-radius: 12px;
            margin-top: 5px;
            max-height: 150px;
            overflow-y: auto;
            border: 1px solid #e2e8f0;
            display: none;
        }

        .result-item {
            padding: 10px 15px;
            cursor: pointer;
            border-bottom: 1px solid #e2e8f0;
            font-size: 0.9rem;
        }

        .result-item:hover {
            background: #eff6ff;
        }

        .selected-badge {
            background: #eff6ff;
            color: #2563eb;
            padding: 12px;
            border-radius: 14px;
            margin-top: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .quick-input {
            width: 100%;
            padding: 12px 18px;
            border: 1.5px solid #e2e8f0;
            border-radius: 14px;
            font-size: 0.95rem;
            outline: none;
            margin-bottom: 5px;
        }

        .quick-input:focus {
            border-color: #2563eb;
        }

        .book-item-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            background: #f8fafc;
            border-radius: 10px;
            margin-bottom: 5px;
        }

        #toastContainer {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 2000;
        }

        .toast {
            background: #1e293b;
            color: white;
            padding: 15px 25px;
            border-radius: 12px;
            margin-top: 10px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        .summary-card {
            background: white;
            border-radius: 24px;
            border: 1px solid var(--border-light);
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-sm);
        }

        .summary-list {
            list-style: none;
            padding: 0;
            margin-top: 1rem;
        }

        .summary-item {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #f1f5f9;
        }

        .summary-item:last-child {
            border-bottom: none;
        }

        .badge-pill {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 700;
        }

        .badge-blue { background: #eff6ff; color: #2563eb; }
        .badge-mint { background: #ecfdf5; color: #10b981; }
    </style>
</head>

<body>

    <script src="assets/sidebar.js"></script>

    <div class="main-wrapper">
        <header>
            <div class="page-title">
                <h1>Overview</h1>
                <p>Welcome back, Admin. Here's what's happening today.</p>
            </div>
            <div class="header-tools">
                <div class="search-box">
                    <i class="fa-solid fa-search" style="color: var(--text-muted);"></i>
                    <input type="text" placeholder="Search anything...">
                </div>
                <div class="user-profile">
                    <img src="https://ui-avatars.com/api/?name=Admin&background=eff6ff&color=2563eb&bold=true"
                        alt="User" style="width: 44px; border-radius: 12px; border: 1px solid var(--border-light);">
                </div>
            </div>
        </header>

        <section class="dashboard-overview">
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon icon-blue">
                        <i class="fa-solid fa-bookmark"></i>
                    </div>
                    <span class="stat-title">Active Borrows</span>
                    <div class="stat-value">0</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon icon-mint">
                        <i class="fa-solid fa-bangladeshi-taka-sign"></i>
                    </div>
                    <span class="stat-title">Today's Revenue</span>
                    <div class="stat-value">৳0</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon icon-pink">
                        <i class="fa-solid fa-user-group"></i>
                    </div>
                    <span class="stat-title">Total Members</span>
                    <div class="stat-value">0</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon icon-yellow">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                    </div>
                    <span class="stat-title">Overdue Alerts</span>
                    <div class="stat-value">0</div>
                </div>
            </div>

            <div class="summary-card">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <h2 style="font-weight: 800; color: var(--text-header); margin: 0;"><i class="fa-solid fa-chart-line" style="color: var(--primary-blue); margin-right: 10px;"></i> Activity Summary</h2>
                        <span id="summaryTime" class="badge-pill badge-blue">Today</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <input type="date" id="summaryFromDate" class="quick-input" style="width: auto; margin:0; padding: 8px 12px; font-size: 0.8rem;">
                        <span style="color: var(--text-muted); font-size: 0.8rem;">to</span>
                        <input type="date" id="summaryToDate" class="quick-input" style="width: auto; margin:0; padding: 8px 12px; font-size: 0.8rem;">
                        <button onclick="updateDashboard()" class="badge-pill badge-blue" style="border:none; cursor:pointer; padding: 10px 15px;"><i class="fa-solid fa-rotate"></i></button>
                    </div>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
                    <div>
                        <h4 style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; color: var(--text-muted); margin-bottom: 1rem;">Customer Purchases</h4>
                        <div id="customerPurchasesList" class="summary-list">
                            <!-- Items will be injected here -->
                            <p style="color: var(--text-muted); font-size: 0.9rem;">Loading today's activity...</p>
                        </div>
                    </div>
                    <div>
                        <h4 style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; color: var(--text-muted); margin-bottom: 1rem;">Item Sales Performance</h4>
                        <div id="bookSalesList" class="summary-list">
                            <!-- Items will be injected here -->
                        </div>
                        
                        <div id="mostBooksSection" style="margin-top: 2rem; padding-top: 1.5rem; border-top: 1px dashed #e2e8f0;">
                            <div style="font-size: 0.85rem; font-weight: 600; color: var(--text-header);">Top Customer of the Day:</div>
                            <div id="topCustomerDisplay" style="font-weight: 800; color: var(--primary-blue); font-size: 1.1rem; margin-top: 5px;">-</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="section-header">
                <h2 style="font-weight: 700; color: var(--text-header);">Quick Services</h2>
            </div>

            <div class="action-grid">
                <div class="action-card" onclick="openQuickAction('issue')" style="cursor: pointer;">
                    <i class="fa-solid fa-plus" style="color: var(--primary-blue);"></i>
                    <h3>Issue Book</h3>
                    <p>Lend a book to a registered member</p>
                </div>
                <div class="action-card" onclick="openQuickAction('return')" style="cursor: pointer;">
                    <i class="fa-solid fa-rotate-left" style="color: var(--accent-mint);"></i>
                    <h3>Pick Return</h3>
                    <p>Check-in books and calculate fines</p>
                </div>
                <div class="action-card" onclick="window.location.href='pages/terminal.php'" style="cursor: pointer;">
                    <i class="fa-solid fa-cart-shopping" style="color: #f59e0b;"></i>
                    <h3>Direct Sale</h3>
                    <p>Generate invoice for book purchase</p>
                </div>
                <div class="action-card" onclick="window.location.href='pages/member.php'" style="cursor: pointer;">
                    <i class="fa-solid fa-user-plus" style="color: #ec4899;"></i>
                    <h3>New Member</h3>
                    <p>Create a membership profile</p>
                </div>
                <div class="action-card admin-only" onclick="window.location.href='pages/profile.php#admin-management-section'" style="cursor: pointer; display: none;">
                    <i class="fa-solid fa-users-gear" style="color: #6366f1;"></i>
                    <h3>Team Management</h3>
                    <p>Add and manage POS operators</p>
                </div>
            </div>
        </section>
    </div>

    <!-- Quick Action Modal -->
    <div class="modal-overlay" id="quickActionModal">
        <div class="modal-card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <h2 id="modalTitle" style="margin:0; font-weight: 800;">Quick Service</h2>
                <button onclick="closeModal()"
                    style="border:none; background:none; cursor:pointer; font-size:1.5rem; color:#94a3b8;">&times;</button>
            </div>

            <!-- Member Search -->
            <div style="margin-bottom: 1.5rem;">
                <label style="font-size: 0.75rem; font-weight: 800; color: #64748b; text-transform: uppercase;">1.
                    Identify Member</label>
                <input type="text" id="memberSearchInput" class="quick-input"
                    placeholder="Search Member (ID, Name, or Phone)" onkeyup="searchMember()">
                <div id="memberResults" class="search-results"></div>
                <div id="selectedMember" class="selected-badge" style="display: none;">
                    <div>
                        <div id="smName" style="font-weight: 700;"></div>
                        <div id="smId" style="font-size: 0.75rem; opacity: 0.7;"></div>
                    </div>
                    <button onclick="clearSelectedMember()"
                        style="border:none; background:none; color:#ef4444; cursor:pointer;"><i
                            class="fa-solid fa-times-circle"></i></button>
                </div>
            </div>

            <!-- Action Section (Dynamic) -->
            <div id="actionSection" style="display: none;">
                <!-- Issue Section -->
                <div id="issueSection" style="display: none;">
                    <label style="font-size: 0.75rem; font-weight: 800; color: #64748b; text-transform: uppercase;">2.
                        Pick Books</label>
                    <input type="text" id="bookSearchInput" class="quick-input"
                        placeholder="Search Book by Title or ISBN" onkeyup="searchBook()">
                    <div id="bookResults" class="search-results"></div>
                    <div id="selectedBooks" style="margin-top: 15px; max-height: 150px; overflow-y: auto;"></div>
                    <div style="margin-top: 1rem;">
                        <input type="date" id="dueDate" class="quick-input" style="width: auto;">
                        <span style="font-size: 0.8rem; color: #64748b; margin-left: 10px;">Due Date</span>
                    </div>
                </div>

                <!-- Return Section -->
                <div id="returnSection" style="display: none;">
                    <label style="font-size: 0.75rem; font-weight: 800; color: #64748b; text-transform: uppercase;">2.
                        Select Books to Return</label>
                    <div id="borrowList" style="margin-top: 10px; max-height: 200px; overflow-y: auto;"></div>
                </div>

                <button id="finalBtn" onclick="processFinal()" class="nav-link active"
                    style="width:100%; justify-content:center; padding:15px; margin-top:2rem; font-weight: 800; border-radius: 16px;">
                    CONFIRM TRANSACTION
                </button>
            </div>
        </div>
    </div>

    <div id="toastContainer"></div>

    <script>
        async function updateDashboard() {
            try {
                // Get Date Values
                let fromDate = document.getElementById('summaryFromDate').value;
                let toDate = document.getElementById('summaryToDate').value;

                if (!fromDate) {
                    const today = new Date().toISOString().split('T')[0];
                    document.getElementById('summaryFromDate').value = today;
                    document.getElementById('summaryToDate').value = today;
                    fromDate = today;
                    toDate = today;
                }

                // Fetch Stats (Dashboard stats always for today/current)
                const response = await fetch('../api/controllers/DashboardController.php');
                const data = await response.json();
                if (data.active_borrows !== undefined) {
                    document.querySelector('.stat-card:nth-child(1) .stat-value').innerText = data.active_borrows;
                    document.querySelector('.stat-card:nth-child(2) .stat-value').innerText = '৳' + data.today_sales;
                    document.querySelector('.stat-card:nth-child(3) .stat-value').innerText = data.total_members;
                    document.querySelector('.stat-card:nth-child(4) .stat-value').innerText = data.overdue_books;
                }

                // Fetch Daily Summary detailed info with date range
                const sumRes = await fetch(`../api/controllers/ReportController.php?action=getTodayReportData&from_date=${fromDate}&to_date=${toDate}`);
                const sumData = await sumRes.json();
                
                if (sumData.success) {
                    const s = sumData.data;
                    
                    // 1. Customer Purchases
                    const cList = document.getElementById('customerPurchasesList');
                    if (s.purchases.length === 0) {
                        cList.innerHTML = '<div style="font-size: 0.9rem; color: #64748b; padding: 10px;">No sales recorded yet today.</div>';
                    } else {
                        cList.innerHTML = s.purchases.map(p => `
                            <div class="summary-item">
                                <div style="display: flex; flex-direction: column;">
                                    <span style="font-weight: 700; color: #1e293b; font-size: 0.9rem;">${p.customer_name}</span>
                                    <span style="font-size: 0.75rem; color: #64748b;">${p.books}</span>
                                </div>
                            </div>
                        `).join('');
                    }

                    // 2. Book Sales
                    const bList = document.getElementById('bookSalesList');
                    if (s.books_sold.length === 0) {
                        bList.innerHTML = '<div style="font-size: 0.9rem; color: #64748b; padding: 10px;">-</div>';
                    } else {
                        bList.innerHTML = s.books_sold.map(b => `
                            <div class="summary-item">
                                <span style="font-weight: 600; color: #1e293b; font-size: 0.9rem;">${b.title}</span>
                                <span class="badge-pill badge-mint">x${b.qty} Sold</span>
                            </div>
                        `).join('');
                    }

                    // 3. Top Customer
                    if (s.top_customer) {
                        document.getElementById('topCustomerDisplay').innerHTML = `
                            <span style="color: #0f172a;">${s.top_customer.customer_name}</span>
                            <span style="font-size: 0.8rem; font-weight: 600; color: #10b981; margin-left:10px;">(${s.top_customer.total_qty} units)</span>
                        `;
                    } else {
                        document.getElementById('topCustomerDisplay').innerText = "N/A";
                    }

                    const isToday = fromDate === new Date().toISOString().split('T')[0] && toDate === fromDate;
                    document.getElementById('summaryTime').innerText = isToday ? "Today" : `Range: ${fromDate} to ${toDate}`;
                    document.getElementById('summaryTime').className = isToday ? "badge-pill badge-blue" : "badge-pill badge-mint";
                }

            } catch (error) {
                console.error('Fetch error:', error);
            }
        }

        // Run on load
        updateDashboard();
        // Refresh every 15 seconds
        setInterval(updateDashboard, 15000);

        // --- Quick Action Logics ---
        let currentMode = ''; // 'issue' or 'return'
        let selectedMember = null;
        let selectedBooks = [];
        let searchTimeout = null;

        function openQuickAction(mode) {
            currentMode = mode;
            document.getElementById('modalTitle').innerText = mode === 'issue' ? 'Quick Issue Book' : 'Quick Pick Return';
            document.getElementById('quickActionModal').style.display = 'flex';
            resetModal();

            // Focus search input automatically
            setTimeout(() => document.getElementById('memberSearchInput').value = '', 10);
            document.getElementById('memberSearchInput').focus();

            if (mode === 'issue') {
                const d = new Date();
                d.setDate(d.getDate() + 15);
                document.getElementById('dueDate').value = d.toISOString().split('T')[0];
            }
        }

        function closeModal() {
            document.getElementById('quickActionModal').style.display = 'none';
        }

        function resetModal() {
            const input = document.getElementById('memberSearchInput');
            input.value = '';
            input.style.display = 'block';
            document.getElementById('memberResults').style.display = 'none';
            document.getElementById('selectedMember').style.display = 'none';
            document.getElementById('actionSection').style.display = 'none';
            selectedMember = null;
            selectedBooks = [];
            renderSelectedBooks();
        }

        async function searchMember() {
            const q = document.getElementById('memberSearchInput').value;
            if (q.length < 2) return;

            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(async () => {
                const res = await fetch(`../api/controllers/TerminalController.php?action=searchMembers&q=${encodeURIComponent(q)}`);
                const members = await res.json();
                const resultsBox = document.getElementById('memberResults');
                resultsBox.innerHTML = members.map(m => `
                    <div class="result-item" onclick="selectMember(${JSON.stringify(m).replace(/"/g, '&quot;')})">
                        <strong>${m.full_name}</strong> (${m.membership_id})<br>
                        <small>${m.phone}</small>
                    </div>
                `).join('');
                resultsBox.style.display = 'block';
            }, 300);
        }

        function selectMember(m) {
            selectedMember = m;
            document.getElementById('memberResults').style.display = 'none';
            document.getElementById('memberSearchInput').style.display = 'none';
            document.getElementById('selectedMember').style.display = 'flex';
            document.getElementById('smName').innerText = m.full_name;
            document.getElementById('smId').innerText = m.membership_id;

            document.getElementById('actionSection').style.display = 'block';
            document.getElementById('issueSection').style.display = currentMode === 'issue' ? 'block' : 'none';
            document.getElementById('returnSection').style.display = currentMode === 'return' ? 'block' : 'none';

            if (currentMode === 'return') loadBorrows(m.id);
        }

        function clearSelectedMember() {
            selectedMember = null;
            document.getElementById('selectedMember').style.display = 'none';
            document.getElementById('memberSearchInput').style.display = 'block';
            document.getElementById('actionSection').style.display = 'none';
        }

        async function searchBook() {
            const q = document.getElementById('bookSearchInput').value;
            if (q.length < 2) return;

            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(async () => {
                const res = await fetch(`../api/controllers/TerminalController.php?action=getBooks`);
                const allBooks = await res.json();
                const filtered = allBooks.filter(b => b.title.toLowerCase().includes(q.toLowerCase()) || (b.isbn && b.isbn.includes(q)));

                const resultsBox = document.getElementById('bookResults');
                resultsBox.innerHTML = filtered.map(b => `
                    <div class="result-item" onclick="selectBook(${JSON.stringify(b).replace(/"/g, '&quot;')})">
                        <strong>${b.title}</strong><br>
                        <small>${b.author} • In Stock: ${b.stock_qty}</small>
                    </div>
                `).join('');
                resultsBox.style.display = 'block';
            }, 300);
        }

        function selectBook(b) {
            if (selectedBooks.find(x => x.id === b.id)) return document.getElementById('bookResults').style.display = 'none';
            selectedBooks.push(b);
            document.getElementById('bookResults').style.display = 'none';
            document.getElementById('bookSearchInput').value = '';
            renderSelectedBooks();
        }

        function renderSelectedBooks() {
            document.getElementById('selectedBooks').innerHTML = selectedBooks.map((b, i) => `
                <div class="book-item-row">
                    <span>${b.title}</span>
                    <button onclick="removeBook(${i})" style="border:none; color:#ef4444; background:none; cursor:pointer;"><i class="fa-solid fa-trash"></i></button>
                </div>
            `).join('');
        }

        function removeBook(i) {
            selectedBooks.splice(i, 1);
            renderSelectedBooks();
        }

        async function loadBorrows(memberId) {
            const res = await fetch(`../api/controllers/TerminalController.php?action=getBorrowedBooks&memberId=${memberId}`);
            const borrows = await res.json();
            const box = document.getElementById('borrowList');
            if (borrows.length === 0) {
                box.innerHTML = '<div style="padding:15px; color:#64748b;">No active borrows.</div>';
                return;
            }
            box.innerHTML = borrows.map(b => `
                <div class="book-item-row">
                    <div>
                        <div style="font-weight:700;">${b.title}</div>
                        <div style="font-size:0.7rem; opacity:0.7;">Due: ${b.due_date}</div>
                    </div>
                    <input type="checkbox" class="return-check" value="${b.borrow_id}">
                </div>
            `).join('');
        }

        async function processFinal() {
            if (currentMode === 'issue') {
                if (selectedBooks.length === 0) return alert("Select books first.");
                const payload = {
                    memberId: selectedMember.id,
                    dueDate: document.getElementById('dueDate').value,
                    items: selectedBooks.map(b => ({ id: b.id, title: b.title }))
                };
                const res = await fetch(`../api/controllers/TerminalController.php?action=saveBorrow`, {
                    method: 'POST', body: JSON.stringify(payload)
                });
                if ((await res.json()).success) {
                    showToast("Books issued successfully!");
                    closeModal();
                    updateDashboard();
                }
            } else {
                const checks = document.querySelectorAll('.return-check:checked');
                if (checks.length === 0) return alert("Select books to return.");
                const borrowIds = Array.from(checks).map(c => c.value);
                const res = await fetch(`../api/controllers/TerminalController.php?action=returnBooks`, {
                    method: 'POST', body: JSON.stringify({ borrowIds })
                });
                if ((await res.json()).success) {
                    showToast("Books returned successfully!");
                    closeModal();
                    updateDashboard();
                }
            }
        }

        function showToast(m) {
            const t = document.createElement('div');
            t.className = 'toast';
            t.innerText = m;
            document.getElementById('toastContainer').appendChild(t);
            setTimeout(() => t.remove(), 3000);
        }
    </script>
</body>

</html>