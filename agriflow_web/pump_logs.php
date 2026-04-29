<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Database Connection
$host = 'localhost';
$dbname = 'agriflow_db'; 
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch Logs with Sensor Data joined by timestamp
    // This looks for the sensor reading closest to the pump's start_time
    $stmt = $pdo->query("
        SELECT 
            pl.*, 
            dc.selected_plant,
            (SELECT moisture FROM sensor_logs WHERE timestamp <= pl.start_time ORDER BY timestamp DESC LIMIT 1) as start_moisture,
            (SELECT temp FROM sensor_logs WHERE timestamp <= pl.start_time ORDER BY timestamp DESC LIMIT 1) as start_temp,
            (SELECT ph FROM sensor_logs WHERE timestamp <= pl.start_time ORDER BY timestamp DESC LIMIT 1) as start_ph
        FROM pump_logs pl
        CROSS JOIN device_controls dc 
        WHERE dc.id = 1
        ORDER BY pl.start_time DESC
    ");
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get today's total liters
    $totalStmt = $pdo->query("SELECT SUM(liters_used) as total FROM pump_logs WHERE DATE(start_time) = CURDATE()");
    $todayTotal = $totalStmt->fetchColumn() ?: 0;

} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agriflow | Pump & Sensor Logs</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Existing styles from previous response... */
        .log-table-container { background: white; padding: 1.2rem; border-radius: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; min-width: 900px; }
        th { background: #f8f9fa; padding: 15px; text-align: left; font-size: 0.85rem; color: #777; border-bottom: 2px solid #eee; }
        td { padding: 15px; border-bottom: 1px solid #eee; font-size: 0.9rem; vertical-align: middle; }
        
        .sensor-pill {
            display: inline-flex;
            gap: 8px;
            background: #f1f3f5;
            padding: 4px 12px;
            border-radius: 50px;
            font-size: 0.8rem;
            color: #444;
        }
        .moisture-val { color: #2196f3; font-weight: bold; }
        .temp-val { color: #ff9800; }
        .ph-val { color: #9c27b0; }
        
        .plant-badge { background: #e8f5e9; color: #2e7d32; padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: bold; }
    </style>
</head>
<body>

    <?php include('sidebar/sidebar.php'); ?>

    <main>
        <header>
            <h1>Pump & Sensor History</h1>
            <p>Reviewing environmental conditions during pump activity.</p>
        </header>

        <section class="log-table-container">
            <table>
                <thead>
                    <tr>
                        <th>Date & Time</th>
                        <th>Profile</th>
                        <th>Conditions at Start</th>
                        <th>Duration</th>
                        <th>Water Usage</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $log): ?>
                    <tr>
                        <td>
                            <strong><?php echo date('M d, Y', strtotime($log['start_time'])); ?></strong><br>
                            <small style="color:#999"><?php echo date('H:i:s', strtotime($log['start_time'])); ?></small>
                        </td>
                        <td><span class="plant-badge"><?php echo str_replace('_', ' ', $log['selected_plant']); ?></span></td>
                        <td>
                            <div class="sensor-pill">
                                <span class="moisture-val">💧 <?php echo number_format($log['start_moisture'] ?? 0, 1); ?>%</span>
                                <span class="temp-val">🌡️ <?php echo number_format($log['start_temp'] ?? 0, 1); ?>°C</span>
                                <span class="ph-val">🧪 <?php echo number_format($log['start_ph'] ?? 0, 1); ?></span>
                            </div>
                        </td>
                        <td><small><?php echo $log['duration_seconds']; ?>s</small></td>
                        <td style="font-weight: bold; color: #2e7d32;">
                            <?php echo number_format($log['liters_used'], 3); ?> L
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    </main>

</body>
</html>