<?php require_once '../../api/shared/auth_check.php'; checkAuth(true); renderUserUI(true); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Intelligence | Ontomeel POS</title>
    <link rel="stylesheet" href="../assets/pos-styles.css?v=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .report-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
            margin-top: 2rem;
        }

        .chart-container {
            background: white;
            border-radius: 24px;
            padding: 2rem;
            border: 1px solid var(--border-light);
            box-shadow: var(--shadow-md);
            margin-bottom: 2rem;
        }

        .ai-insight-card {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            border-radius: 24px;
            padding: 2.5rem;
            color: white;
            position: relative;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(15, 23, 42, 0.3);
        }

        .ai-insight-card::before {
            content: '';
            position: absolute;
            top: -50px; right: -50px;
            width: 200px; height: 200px;
            background: rgba(37, 99, 235, 0.2);
            filter: blur(60px);
            border-radius: 50%;
        }

        .btn-ai {
            background: var(--primary-blue);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 12px;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: 0.3s;
        }

        .btn-ai:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(37, 99, 235, 0.4);
        }

        .stat-small {
            font-size: 0.75rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: rgba(255,255,255,0.5);
        }

        /* Chat UI Styles */
        .ai-chat-container {
            display: flex;
            flex-direction: column;
            height: 500px;
            background: rgba(255, 255, 255, 0.03);
            border-radius: 16px;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .message {
            max-width: 85%;
            padding: 12px 16px;
            border-radius: 16px;
            font-size: 0.9rem;
            line-height: 1.5;
        }

        .message.ai {
            background: rgba(37, 99, 235, 0.1);
            color: #e2e8f0;
            align-self: flex-start;
            border-bottom-left-radius: 4px;
            border: 1px solid rgba(37, 99, 235, 0.2);
        }

        .message.user {
            background: var(--primary-blue);
            color: white;
            align-self: flex-end;
            border-bottom-right-radius: 4px;
        }

        .chat-input-area {
            padding: 1.5rem;
            background: rgba(0, 0, 0, 0.2);
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .question-selector {
            width: 100%;
            padding: 12px;
            background: #1e293b;
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: white;
            border-radius: 8px;
            outline: none;
            cursor: pointer;
        }

        .chat-messages::-webkit-scrollbar {
            width: 5px;
        }

        .chat-messages::-webkit-scrollbar-track {
            background: transparent;
        }

        .chat-messages::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
        }
    </style>
</head>
<body>

    <script src="../assets/sidebar.js"></script>

    <div class="main-wrapper">
        <header>
            <div class="page-title">
                <h1>Business <span style="color: var(--primary-blue);">Intelligence</span></h1>
                <p>Data-driven insights for smarter library management</p>
            </div>
            <div class="header-tools" style="display: flex; align-items: center; gap: 1rem;">
                <div class="user-profile" style="display: flex; align-items: center; gap: 12px; margin-right: 1.5rem;">
                    <div style="text-align: right;">
                        <div style="font-size: 0.85rem; font-weight: 700; color: var(--text-header);" class="user-name-display">Admin</div>
                        <div style="font-size: 0.7rem; color: var(--accent-mint); font-weight: 800; text-transform: uppercase;">Logged In</div>
                    </div>
                </div>
                <button class="nav-link active" onclick="window.print()" style="padding: 10px 20px; font-size: 0.85rem;">
                    <i class="fa-solid fa-file-export"></i> EXPORT PDF
                </button>
            </div>
        </header>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon icon-blue"><i class="fa-solid fa-coins"></i></div>
                <span class="stat-title">Total Revenue</span>
                <div class="stat-value" id="valTotalSales">৳0</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon icon-mint"><i class="fa-solid fa-book-reader"></i></div>
                <span class="stat-title">Total Borrows</span>
                <div class="stat-value" id="valTotalBorrows">0</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon icon-pink"><i class="fa-solid fa-user-check"></i></div>
                <span class="stat-title">New Members</span>
                <div class="stat-value" id="valTotalMembers">0</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon icon-yellow"><i class="fa-solid fa-clock"></i></div>
                <span class="stat-title">Active Borrows</span>
                <div class="stat-value" id="valActiveBorrows">0</div>
            </div>
        </div>

        <div class="report-grid">
            <div class="left-col">
                <div class="chart-container">
                    <div style="font-weight: 800; margin-bottom: 1.5rem; color: var(--text-header);">Sales Performance (Last 7 Days)</div>
                    <canvas id="salesChart" height="120"></canvas>
                </div>

                <div class="chart-container">
                    <div style="font-weight: 800; margin-bottom: 1.5rem; color: var(--text-header);">Most Borrowed Books</div>
                    <canvas id="popularChart" height="120"></canvas>
                </div>
            </div>

            <div class="right-col">
                <div class="ai-insight-card" style="padding: 1.5rem;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.2rem;">
                        <div style="font-weight: 800; font-size: 1.1rem;"><i class="fa-solid fa-brain" style="color: var(--primary-blue);"></i> AI ASSISTANT</div>
                        <div class="stat-small">Gemini Mode</div>
                    </div>
                    
                    <div class="ai-chat-container">
                        <div id="chatMessages" class="chat-messages">
                            <div class="message ai">
                                Hello! I'm your AI Business Assistant. Select a report type below to generate insights.
                            </div>
                        </div>
                        
                        <div class="chat-input-area">
                            <textarea id="aiUserInput" class="question-selector" placeholder="Type your question here (e.g. 'How many books sold today?')..." rows="2" style="resize: none;"></textarea>
                            
                            <button id="btnAiAnalyze" class="btn-ai" onclick="handleAiQuestion()" style="width: 100%; justify-content: center;">
                                <i class="fa-solid fa-paper-plane"></i> ASK AI
                            </button>
                        </div>
                    </div>
                </div>

                <div class="chart-container" style="margin-top: 2rem;">
                    <div style="font-weight: 800; margin-bottom: 1.5rem; color: var(--text-header);">Plan Distribution</div>
                    <canvas id="planChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <script>
        let reportData = {};
        let chatHistory = [];

        async function initDashboard() {
            try {
                // Fetch Summary
                const sumRes = await fetch('../../api/controllers/ReportController.php?action=getSummary');
                const sumData = await sumRes.json();
                if (sumData.success) {
                    reportData.summary = sumData.summary;
                    document.getElementById('valTotalSales').innerText = '৳' + sumData.summary.total_sales;
                    document.getElementById('valTotalBorrows').innerText = sumData.summary.total_borrows;
                    document.getElementById('valTotalMembers').innerText = sumData.summary.total_members;
                    document.getElementById('valActiveBorrows').innerText = sumData.summary.active_borrows;
                }

                // Sales Chart
                const salesRes = await fetch('../../api/controllers/ReportController.php?action=getSalesData');
                const salesData = await salesRes.json();
                if (salesData.success) {
                    reportData.sales = salesData.data;
                    renderSalesChart(salesData.data);
                }

                // Popular Books
                const popRes = await fetch('../../api/controllers/ReportController.php?action=getPopularBooks');
                const popData = await popRes.json();
                if (popData.success) {
                    reportData.popular = popData.data;
                    renderPopularChart(popData.data);
                }

                // Plan Distribution
                const planRes = await fetch('../../api/controllers/ReportController.php?action=getMemberStats');
                const planData = await planRes.json();
                if (planData.success) {
                    reportData.plans = planData.data;
                    renderPlanChart(planData.data);
                }

            } catch (e) { console.error(e); }
        }

        function renderSalesChart(data) {
            const ctx = document.getElementById('salesChart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.map(d => d.date),
                    datasets: [{
                        label: 'Daily Sales (৳)',
                        data: data.map(d => d.amount),
                        borderColor: '#2563eb',
                        backgroundColor: 'rgba(37, 99, 235, 0.1)',
                        fill: true,
                        tension: 0.4,
                        borderWidth: 3,
                        pointRadius: 4,
                        pointBackgroundColor: '#2563eb'
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, grid: { display: false } },
                        x: { grid: { display: false } }
                    }
                }
            });
        }

        function renderPopularChart(data) {
            const ctx = document.getElementById('popularChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: data.map(d => d.title),
                    datasets: [{
                        label: 'Borrows',
                        data: data.map(d => d.borrow_count),
                        backgroundColor: '#10b981',
                        borderRadius: 8
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { beginAtZero: true, grid: { display: false } },
                        y: { grid: { display: false } }
                    }
                }
            });
        }

        function renderPlanChart(data) {
            const ctx = document.getElementById('planChart').getContext('2d');
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: data.map(d => d.membership_plan),
                    datasets: [{
                        data: data.map(d => d.count),
                        backgroundColor: ['#64748b', '#10b981', '#2563eb', '#db2777'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    cutout: '70%',
                    plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, padding: 20 } } }
                }
            });
        }

        function addMessage(text, role) {
            const container = document.getElementById('chatMessages');
            const msgDiv = document.createElement('div');
            msgDiv.className = `message ${role}`;
            msgDiv.innerHTML = text.replace(/\n balance/g, '<br>'); // Basic formatting
            container.appendChild(msgDiv);
            container.scrollTop = container.scrollHeight;
        }

        async function handleAiQuestion() {
            const userInput = document.getElementById('aiUserInput');
            const questionText = userInput.value.trim();
            
            if (!questionText) return;

            addMessage(questionText, 'user');
            chatHistory.push({ role: 'user', content: questionText });
            userInput.value = ''; // Clear input
            
            const btn = document.getElementById('btnAiAnalyze');
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Thinking...';

            try {
                // Fetch basic context (Today's metrics and Full DB metrics)
                const [todayRes, fullRes] = await Promise.all([
                    fetch('../../api/controllers/ReportController.php?action=getTodayReportData'),
                    fetch('../../api/controllers/ReportController.php?action=getFullDatabaseSummary')
                ]);
                
                const todayData = await todayRes.json();
                const fullData = await fullRes.json();
                
                let context = "GENERAL LIBRARY & BUSINESS CONTEXT:\n";
                
                if (fullData.success) {
                    const fd = fullData.data;
                    context += `- All-time Totals: Sales ৳${fd.totals.total_sales_amount}, Orders ${fd.totals.total_orders_count}, Members ${fd.totals.total_members}, Books ${fd.totals.total_books}, Active Borrows ${fd.totals.total_active_borrows}\n`;
                    context += `- Monthly Sales Trend: ${JSON.stringify(fd.monthly_performance)}\n`;
                    context += `- Top Performing Books: ${JSON.stringify(fd.top_performing_books)}\n`;
                    context += `- Membership Plans Distribution: ${JSON.stringify(fd.membership_stats)}\n`;
                    context += `- High-Value Transactions: ${JSON.stringify(fd.notable_transactions)}\n`;
                }

                if (todayData.success) {
                    const stats = todayData.data;
                    context += `\nTODAY'S (${new Date().toLocaleDateString()}) REAL-TIME ACTIVITY:\n`;
                    context += `- POS Sold Units: ${stats.pos_sold}\n`;
                    context += `- Web Sold Units: ${stats.web_sold}\n`;
                    context += `- Detailed Customer Purchases: ${JSON.stringify(stats.purchases)}\n`;
                    context += `- Top Customer Today: ${stats.top_customer ? stats.top_customer.customer_name + ' (Bought ' + stats.top_customer.total_qty + ' units)' : 'None yet'}\n`;
                    context += `- Sold Books Summary: ${JSON.stringify(stats.books_sold)}\n`;
                }

                const prompt = `
                    You are an intelligent Assistant for Ontomeel Library. 
                    Your mission: Answer the user's latest question naturally and helpfully.
                    
                    DATA CONTEXT (Use ONLY IF relevant to the question):
                    ${context}

                    CONVERSATION HISTORY:
                    ${chatHistory.slice(-5).map(m => `${m.role.toUpperCase()}: ${m.content}`).join('\n')}

                    LATEST USER QUESTION: ${questionText}

                    Rules:
                    1. Prioritize the user's actual question. If they ask for a report, give data. If they say "Hi" or ask something general, respond like a normal chat.
                    2. Maintain a professional yet friendly tone like Gemini/ChatGPT.
                    3. Format using HTML like <br> for new lines.
                `;

                const aiRes = await fetch('../../api/controllers/AIController.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ prompt: prompt })
                });
                
                const aiData = await aiRes.json();
                if (aiData.success) {
                    addMessage(aiData.answer.replace(/\n/g, '<br>'), 'ai');
                    chatHistory.push({ role: 'assistant', content: aiData.answer });
                } else {
                    addMessage("Error: " + (aiData.error || "AI connection failed."), 'ai');
                }
            } catch (e) {
                console.error(e);
                addMessage("Sorry, I encountered an error while processing your request.", "ai");
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-paper-plane"></i> ASK AI';
                // Keep the last 10 messages in memory for context
                if (chatHistory.length > 20) chatHistory = chatHistory.slice(-10);
            }
        }

        async function generateTodayReport() {
            // No longer needed as handleAiQuestion is now generic
        }

        async function generateAIInsights() {
            // Keep original function if needed, but we've moved to handleAiQuestion
            // or we can just leave it as a reference.
        }

        window.onload = initDashboard;
    </script>
</body>
</html>
