<?php require_once '../api/shared/auth_check.php'; checkLogin(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ontomeel POS | Temporary Signup</title>
    <link rel="stylesheet" href="assets/pos-styles.css?v=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            background: radial-gradient(circle at top right, #f8fafc 0%, #eff6ff 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            font-family: 'Inter', sans-serif;
            margin: 0;
        }

        .signup-card {
            background: rgba(255, 255, 255, 0.82);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            width: 440px;
            padding: 3rem;
            border-radius: 40px;
            box-shadow: 0 40px 100px -20px rgba(37, 99, 235, 0.1);
            animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes slideUp {
            from { transform: translateY(40px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .logo-section {
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .logo-circle {
            width: 80px;
            height: 80px;
            background: white;
            border-radius: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            box-shadow: 0 10px 25px rgba(37, 99, 235, 0.1);
            overflow: hidden;
        }

        .logo-section h1 {
            font-weight: 800;
            color: #0f172a;
            font-size: 1.75rem;
            margin-bottom: 0.5rem;
        }

        .logo-section p {
            color: #64748b;
            font-size: 0.95rem;
        }

        .form-group {
            margin-bottom: 1.25rem;
            position: relative;
        }

        .form-group i {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            transition: color 0.3s;
        }

        .form-control {
            width: 100%;
            padding: 1rem 1rem 1rem 3.2rem;
            border-radius: 16px;
            border: 2px solid #e2e8f0;
            background: white;
            font-family: inherit;
            font-size: 1rem;
            font-weight: 500;
            transition: all 0.3s;
            box-sizing: border-box;
        }

        .form-control:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.05);
        }

        .btn-signup {
            width: 100%;
            padding: 1.1rem;
            border-radius: 18px;
            border: none;
            background: #0f172a;
            color: white;
            font-weight: 700;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 1rem;
            box-shadow: 0 10px 30px -5px rgba(15, 23, 42, 0.3);
        }

        .btn-signup:hover {
            background: #1e293b;
            transform: translateY(-2px);
            box-shadow: 0 15px 35px -5px rgba(15, 23, 42, 0.4);
        }

        .message {
            padding: 12px;
            border-radius: 12px;
            font-size: 0.85rem;
            font-weight: 600;
            text-align: center;
            margin-bottom: 1.5rem;
            display: none;
            border: 1px solid transparent;
        }

        .error-message {
            background: #fef2f2;
            color: #dc2626;
            border-color: #fee2e2;
        }

        .success-message {
            background: #f0fdf4;
            color: #16a34a;
            border-color: #dcfce7;
        }

        .circle {
            position: absolute;
            border-radius: 50%;
            z-index: -1;
            filter: blur(80px);
        }

        .circle-1 {
            width: 400px;
            height: 400px;
            background: rgba(37, 99, 235, 0.08);
            top: -100px;
            right: -100px;
        }

        .circle-2 {
            width: 300px;
            height: 300px;
            background: rgba(16, 185, 129, 0.08);
            bottom: -50px;
            left: -50px;
        }

        .login-link {
            color: #2563eb;
            text-decoration: none;
            font-weight: 600;
            transition: opacity 0.2s;
        }
        
        .login-link:hover {
            opacity: 0.8;
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <div class="circle circle-1"></div>
    <div class="circle circle-2"></div>

    <div class="signup-card">
        <div class="logo-section">
            <div class="logo-circle">
                <img src="assets/logo.webp" alt="Ontomeel Logo" style="width: 100%; height: 100%; object-fit: contain; padding: 10px;">
            </div>
            <h1>Create Account</h1>
            <p>Temporary Registration Access</p>
        </div>

        <div id="messageBox" class="message"></div>

        <form id="signupForm">
            <div class="form-group">
                <input type="text" id="full_name" class="form-control" placeholder="Full Name" required>
                <i class="fa-solid fa-id-card"></i>
            </div>
            <div class="form-group">
                <input type="text" id="username" class="form-control" placeholder="Username" required>
                <i class="fa-solid fa-at"></i>
            </div>
            <div class="form-group">
                <input type="email" id="email" class="form-control" placeholder="Email Address" required>
                <i class="fa-solid fa-envelope"></i>
            </div>
            <div class="form-group">
                <input type="password" id="password" class="form-control" placeholder="New Password" required minlength="6">
                <i class="fa-solid fa-key"></i>
            </div>
            
            <button type="submit" id="submitBtn" class="btn-signup">
                REGISTER TEAM <i class="fa-solid fa-user-plus" style="margin-left:8px; font-size:0.8rem; vertical-align:middle;"></i>
            </button>
        </form>

        <div style="margin-top: 2rem; text-align: center;">
            <p style="font-size: 0.9rem; color: #64748b; font-weight: 500;">
                Already have an account? <a href="login.php" class="login-link">Login Here</a>
            </p>
        </div>
    </div>

    <script>
        const signupForm = document.getElementById('signupForm');
        const messageBox = document.getElementById('messageBox');
        const submitBtn = document.getElementById('submitBtn');

        signupForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            messageBox.style.display = 'none';
            messageBox.className = 'message';
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> CREATING...';

            const payload = {
                full_name: document.getElementById('full_name').value,
                username: document.getElementById('username').value,
                email: document.getElementById('email').value,
                password: document.getElementById('password').value
            };

            try {
                const response = await fetch('../api/controllers/AuthController.php?action=signup', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });

                const data = await response.json();

                if (data.success) {
                    messageBox.innerText = 'Registration successful! You can now login.';
                    messageBox.classList.add('success-message');
                    messageBox.style.display = 'block';
                    signupForm.reset();
                    
                    setTimeout(() => {
                        window.location.href = 'login.php';
                    }, 2000);
                } else {
                    throw new Error(data.error || 'Signup failed.');
                }
            } catch (error) {
                messageBox.innerText = error.message;
                messageBox.classList.add('error-message');
                messageBox.style.display = 'block';
                submitBtn.disabled = false;
                submitBtn.innerHTML = 'REGISTER TEAM <i class="fa-solid fa-user-plus" style="margin-left:8px; font-size:0.8rem; vertical-align:middle;"></i>';
            }
        });
    </script>
</body>
</html>
