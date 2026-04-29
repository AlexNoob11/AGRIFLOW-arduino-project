<div class="mobile-nav">
    <a href="#" class="nav-logo">AGRIFLOW</a>
    <button id="menuBtn">☰</button>
</div>

<nav class="sidebar" id="sidebar">
    <div>
        <a href="#" class="nav-logo">AGRIFLOW ADMIN</a>
        <ul class="nav-links">
            <li><a href="dashboard.php" class="active"><span>📊</span> Dashboard</a></li>
            <li><a href="sensor_monitoring.php"><span>📡</span> Sensor Monitoring</a></li>
            <li><a href="threshold_settings.php"><span>⚙️</span> Threshold Settings</a></li>
            <li><a href="motor_control.php"><span>⚡</span> Motor Control</a></li>
            <li><a href="pump_logs.php"><span>📋</span> Pump Logs</a></li>
            <li><a href="data_visualtion.php"><span>📈</span> Data Visualization</a></li>
            <li><a href="profile.php"><span>👤</span> Profile Settings</a></li>
            <li><a href="system_settings.php"><span>🛠️</span> System Settings</a></li>
        </ul>
    </div>
    <a href="Index.php" class="logout-btn" onclick="return confirmLogout(event)">🚪 Log Out</a>
</nav>

<script>
    const menuBtn = document.getElementById('menuBtn');
    const sidebar = document.getElementById('sidebar');
    
    menuBtn.addEventListener('click', () => {
        sidebar.classList.toggle('active');
    });
    function confirmLogout(e) {
        if (!confirm("Are you sure you want to log out from the Agriflow Cloud?")) {
            e.preventDefault(); // Stops the link from opening
            return false;
        }
    }

    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', (e) => {
        if (window.innerWidth <= 992 && !sidebar.contains(e.target) && !menuBtn.contains(e.target)) {
            sidebar.classList.remove('active');
        }
    });
</script>