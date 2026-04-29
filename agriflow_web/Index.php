<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agriflow | IoT Crop-Driven Hydration</title>
    <style>
        :root {
            --bg: #f8faf8;
            --text: #1a1c1a;
            --secondary: #5c635c;
            --accent: #2e7d32;
            --white: #ffffff;
            --border: #e0e6e0;
        }

        * { box-sizing: border-box; transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1); }

        body {
            margin: 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: var(--bg);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Navigation */
        nav {
            padding: 1.5rem 5%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(12px);
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            border-bottom: 1px solid var(--border);
        }

        .logo {
            font-weight: 800;
            font-size: 1.3rem;
            letter-spacing: -1px;
            color: var(--accent);
            text-decoration: none;
        }

        /* Hero Section */
        main {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            padding: 120px 24px 60px;
        }

        .status-pill {
            background: #e8f5e9;
            color: var(--accent);
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .dot { height: 8px; width: 8px; background-color: var(--accent); border-radius: 50%; display: inline-block; animation: pulse 2s infinite; }

        @keyframes pulse { 0% { opacity: 1; } 50% { opacity: 0.3; } 100% { opacity: 1; } }

        h1 {
            font-size: clamp(2.5rem, 6vw, 4.5rem);
            line-height: 1.05;
            margin: 0 0 1.5rem 0;
            max-width: 900px;
            letter-spacing: -2px;
        }

        p {
            font-size: 1.25rem;
            color: var(--secondary);
            max-width: 600px;
            margin-bottom: 3.5rem;
            line-height: 1.6;
        }

        /* Primary Button Style */
        .btn {
            padding: 12px 24px;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            border: none;
        }

        .btn-nav-login { color: var(--text); background: transparent; }
        .btn-nav-register { background: var(--text); color: white; }

        .btn-dashboard {
            background: var(--accent);
            color: white;
            padding: 18px 48px;
            font-size: 1.1rem;
            box-shadow: 0 10px 20px rgba(46, 125, 50, 0.2);
        }

        .btn-dashboard:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(46, 125, 50, 0.3);
            background: #246327;
        }

        /* Modal / Overlay */
        .overlay {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.4);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 2000;
            backdrop-filter: blur(6px);
        }

        .auth-card {
            background: white;
            padding: 40px;
            border-radius: 24px;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 30px 60px rgba(0,0,0,0.12);
        }

        .auth-card h2 { margin-top: 0; margin-bottom: 12px; font-size: 1.8rem; letter-spacing: -0.5px; }
        
        .input-group { margin-bottom: 20px; text-align: left; }
        .input-group label { display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 8px; color: var(--secondary); }

        input {
            width: 100%;
            padding: 14px;
            border: 1px solid var(--border);
            border-radius: 12px;
            font-size: 1rem;
            background: #fcfdfc;
        }

        input:focus { outline: none; border-color: var(--accent); box-shadow: 0 0 0 4px rgba(46, 125, 50, 0.1); }

        .submit-btn {
            width: 100%;
            background: var(--accent);
            color: white;
            border: none;
            padding: 16px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            margin-top: 10px;
        }

        .close-btn { background: none; border: none; color: #ccc; float: right; cursor: pointer; font-size: 1.5rem; margin-top: -10px; }
        .close-btn:hover { color: var(--text); }

        footer {
            padding: 40px;
            text-align: center;
            color: var(--secondary);
            font-size: 0.85rem;
            border-top: 1px solid var(--border);
        }
    </style>
</head>
<body>

    <nav>
        <a href="#" class="logo">AGRIFLOW.</a>
        <div style="display: flex; gap: 8px;">
            <button class="btn btn-nav-login" onclick="openAuth('login')">Log In</button>
            <button class="btn btn-nav-register" onclick="openAuth('register')">Register</button>
        </div>
    </nav>

    <main>
        <div class="status-pill">
            <span class="dot"></span> ESP8266 Online & Monitoring
        </div>
        <h1>Effortless Hydration.<br>Driven by Data.</h1>
        <p>Integrate NPK, DHT, and moisture sensors into one automated ecosystem. Manage your fields with precision from any device.</p>
        
        <button class="btn btn-dashboard" onclick="openAuth('login')">Access Dashboard</button>
    </main>

    <div class="overlay" id="authOverlay">
        <div class="auth-card">
            <button class="close-btn" onclick="closeAuth()">&times;</button>
            <h2 id="authTitle">Welcome Back</h2>
            <p id="authSubtitle" style="color: var(--secondary); margin-bottom: 24px;">Enter your credentials to access the cloud server.</p>
            
            <form action="auth.php" method="POST">
            <input type="hidden" name="action" id="authAction" value="login">
            
            <div class="input-group" id="nameField" style="display: none;">
                <label>Full Name</label>
                <input type="text" name="fullname" placeholder="Admin Name">
            </div>
            
            <div class="input-group">
                <label>Email</label>
                <input type="email" name="email" placeholder="name@admin.com" required>
            </div>
            
            <div class="input-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="••••••••" required>
            </div>
            
            <button type="submit" class="submit-btn" id="submitBtn">Sign In</button>
        </form>
        </div>
    </div>

    <footer>
        &copy; 2026 Agriflow IoT Crop-Driven Hydration System. All rights reserved.
    </footer>

    <script>
        function openAuth(mode) {
        const overlay = document.getElementById('authOverlay');
        const title = document.getElementById('authTitle');
        const subtitle = document.getElementById('authSubtitle');
        const nameField = document.getElementById('nameField');
        const submitBtn = document.getElementById('submitBtn');
        const authAction = document.getElementById('authAction'); // Add this

        overlay.style.display = 'flex';

        if (mode === 'register') {
            title.innerText = "Create Account";
            subtitle.innerText = "Setup your irrigation network and ESP32 nodes.";
            nameField.style.display = 'block';
            submitBtn.innerText = "Get Started";
            authAction.value = "register"; // Set action to register
        } else {
            title.innerText = "Welcome Back";
            subtitle.innerText = "Enter your credentials to access the Admin Dashboard.";
            nameField.style.display = 'none';
            submitBtn.innerText = "Sign In";
            authAction.value = "login"; // Set action to login
        }
    }

        function closeAuth() {
            document.getElementById('authOverlay').style.display = 'none';
        }

        window.onclick = function(event) {
            if (event.target == document.getElementById('authOverlay')) {
                closeAuth();
            }
        }
    </script>

</body>
</html>