<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Database Connection
$host = "localhost";
$user = "root";
$pass = "";
$db   = "agriflow_db"; // Change this to your actual DB name
$conn = new mysqli($host, $user, $pass, $db);

// 1. Handle Form Update for Device Controls
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_settings'])) {
    $mode = $_POST['mode'];
    $plant = $_POST['selected_plant'];
    $conn->query("UPDATE device_controls SET mode='$mode', selected_plant='$plant' WHERE id=1");
    echo "<script>alert('Hardware Configuration Updated!');</script>";
}

// 2. Fetch Current Device Settings
$device_query = $conn->query("SELECT * FROM device_controls WHERE id=1");
$device = $device_query->fetch_assoc();

// 3. Fetch Stats for Cards
$avg_moisture_q = $conn->query("SELECT AVG(moisture) as avg_m FROM sensor_logs WHERE timestamp >= NOW() - INTERVAL 24 HOUR");
$avg_moisture = $avg_moisture_q->fetch_assoc()['avg_m'] ?? 0;

$total_water_q = $conn->query("SELECT SUM(liters_used) as total_l FROM pump_logs WHERE start_time >= CURDATE()");
$total_water = $total_water_q->fetch_assoc()['total_l'] ?? 0;

// 4. Fetch Chart Data (Last 7 Days of Water Usage)
$chart_labels = [];
$chart_data = [];
$usage_query = $conn->query("
    SELECT DATE(start_time) as day, SUM(liters_used) as total 
    FROM pump_logs 
    GROUP BY DATE(start_time) 
    ORDER BY day ASC LIMIT 7
");
while($row = $usage_query->fetch_assoc()) {
    $chart_labels[] = date('D', strtotime($row['day']));
    $chart_data[] = $row['total'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agriflow | System Settings & Reports</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .settings-container { display: grid; grid-template-columns: 1fr 1.5fr; gap: 2rem; }
        .settings-card { background: white; padding: 2rem; border-radius: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.02); }
        .form-group { margin-bottom: 1.5rem; }
        .form-group label { display: block; font-weight: 600; margin-bottom: 8px; color: #555; font-size: 0.85rem; }
        .form-group input, .form-group select { width: 100%; padding: 12px; border: 1px solid #eee; border-radius: 10px; background: #fafafa; }
        .report-preview { background: white; padding: 2rem; border-radius: 20px; border: 1px solid #f0f0f0; }
        
        @media print {
            .sidebar, .btn-action, .settings-card, .export-btn { display: none !important; }
            main { margin: 0 !important; padding: 0 !important; width: 100% !important; }
            .report-preview { border: none; width: 100%; }
        }
        .export-btn { background: #2e7d32; color: white; border: none; padding: 12px 24px; border-radius: 10px; cursor: pointer; font-weight: 600; width: 100%; transition: 0.3s; }
        .export-btn:hover { opacity: 0.9; }
    </style>
</head>
<body>

    <?php include('sidebar/sidebar.php'); ?>

    <main>
        <header style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <div>
                <h1>System Settings & Reporting</h1>
                <p>Hardware: NodeMCU v3 (Lolin) | Station: AGRI-01</p>
            </div>
            <button class="btn-action" onclick="window.print()" style="padding: 10px 20px; cursor: pointer;">🖨️ Print Report</button>
        </header>

        <div class="settings-container">
            <div class="settings-card">
                <h3>Hardware Configuration</h3>
                <form method="POST">
                    <div class="form-group">
                        <label>Operation Mode</label>
                        <select name="mode">
                            <option value="manual" <?php if($device['mode'] == 'manual') echo 'selected'; ?>>Manual (Dashboard Control)</option>
                            <option value="auto" <?php if($device['mode'] == 'auto') echo 'selected'; ?>>Automatic (Sensor Based)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Active Crop Profile</label>
                        <select name="selected_plant">
                            <option value="general" <?php if($device['selected_plant'] == 'general') echo 'selected'; ?>>General Irrigation</option>
                            <option value="tomato" <?php if($device['selected_plant'] == 'tomato') echo 'selected'; ?>>Tomato (High Hydration)</option>
                            <option value="succulent" <?php if($device['selected_plant'] == 'succulent') echo 'selected'; ?>>Succulent (Dry-Soil)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Local Server IP (Arduino Sync)</label>
                        <input type="text" value="192.168.1.8" readonly>
                    </div>
                    <button type="submit" name="update_settings" class="export-btn">Update Controller</button>
                </form>
            </div>

            <div class="report-preview" id="printableReport">
                <h3>Current Cycle Analysis</h3>
                <p style="font-size: 0.8rem; color: #666;">Report generated on: <?php echo date('F d, Y - H:i'); ?></p>
                
                <div style="height: 250px; margin: 1.5rem 0;">
                    <canvas id="reportChart"></canvas>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div style="padding: 15px; border: 1px solid #eee; border-radius: 10px;">
                        <h4 style="font-size: 0.7rem; color: #888; margin: 0;">24H AVG MOISTURE</h4>
                        <div style="font-size: 1.4rem; font-weight: bold; color: #2e7d32;"><?php echo number_format($avg_moisture, 1); ?>%</div>
                    </div>
                    <div style="padding: 15px; border: 1px solid #eee; border-radius: 10px;">
                        <h4 style="font-size: 0.7rem; color: #888; margin: 0;">TODAY'S WATER USAGE</h4>
                        <div style="font-size: 1.4rem; font-weight: bold; color: #1976d2;"><?php echo number_format($total_water, 2); ?> L</div>
                    </div>
                </div>

                <p style="font-size: 0.7rem; color: #aaa; margin-top: 2rem; border-top: 1px solid #eee; padding-top: 1rem;">
                    System ID: NodeMCU-V3-<?php echo session_id(); ?><br>
                    Data verified against `pump_logs` and `sensor_logs` tables.
                </p>
            </div>
        </div>
    </main>

    <script>
        const ctx = document.getElementById('reportChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($chart_labels); ?>,
                datasets: [{
                    label: 'Liters Used',
                    data: <?php echo json_encode($chart_data); ?>,
                    backgroundColor: '#4caf50',
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { display: false } },
                    x: { grid: { display: false } }
                }
            }
        });
    </script>
</body>
</html>