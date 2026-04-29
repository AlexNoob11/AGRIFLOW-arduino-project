<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

include 'db_connect.php';

try {
    // Fixed: Added the missing ' at the end of the query string
    $stmt = $pdo->query("SELECT id, moisture, temp, ph, timestamp FROM sensor_logs ORDER BY timestamp DESC LIMIT 1");
    $data = $stmt->fetch();

    // Fallback values if database is empty
    $moisture = $data['moisture'] ?? 0;
    $temp     = $data['temp'] ?? 0;
    $ph       = $data['ph'] ?? 0;
    
    // Check if timestamp exists before formatting
    $last_update = isset($data['timestamp']) ? date("H:i:s", strtotime($data['timestamp'])) : "No Data";

} catch (Exception $e) {
    die("Error fetching sensor data: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agriflow | Sensor Monitoring</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .monitoring-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-top: 1rem;
        }

        .sensor-card {
            background: white;
            padding: 2rem;
            border-radius: 24px;
            text-align: center;
            box-shadow: 0 10px 25px rgba(0,0,0,0.02);
            position: relative;
            overflow: hidden;
        }

        .gauge-container {
            position: relative;
            width: 160px;
            height: 160px;
            margin: 0 auto 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .gauge-svg { transform: rotate(-90deg); width: 100%; height: 100%; }
        .gauge-bg { fill: none; stroke: #edf2ed; stroke-width: 10; }
        .gauge-fill { 
            fill: none; stroke-width: 10; 
            stroke-linecap: round; transition: stroke-dasharray 1s ease;
        }

        .gauge-value {
            position: absolute;
            font-size: 1.8rem;
            font-weight: 800;
            color: var(--text);
        }

        .sensor-label { font-size: 1.1rem; font-weight: 600; color: var(--secondary); margin-bottom: 0.5rem; }
        .status-dot { height: 8px; width: 8px; background: #4caf50; border-radius: 50%; display: inline-block; margin-right: 5px; }
        
        .data-meta { 
            display: flex; justify-content: space-between; 
            margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid #eee;
            font-size: 0.85rem; color: var(--secondary);
        }

        .refresh-tag { font-size: 0.75rem; background: #f0f0f0; padding: 4px 10px; border-radius: 20px; }
    </style>
</head>
<body>

    <?php include('sidebar/sidebar.php'); ?>

    <main>
        <header>
            <div>
                <h1>Sensor Monitoring</h1>
                <p style="color: var(--secondary);">Live telemetry from ESP8266 Field Node</p>
            </div>
            <div class="refresh-tag">Last Synced: <?php echo $last_update; ?></div>
        </header>

        <div class="monitoring-grid">
            <div class="sensor-card">
                <div class="sensor-label">Soil Moisture</div>
                <div class="gauge-container">
                    <svg class="gauge-svg" viewBox="0 0 100 100">
                        <circle class="gauge-bg" cx="50" cy="50" r="45"></circle>
                        <circle class="gauge-fill" cx="50" cy="50" r="45" 
                                style="stroke: #4caf50; stroke-dasharray: <?php echo ($moisture / 100) * 283; ?>, 283;"></circle>
                    </svg>
                    <div class="gauge-value"><?php echo round($moisture, 1); ?>%</div>
                </div>
                <div style="color: <?php echo $moisture < 35 ? '#d32f2f' : '#2e7d32'; ?>; font-weight: 600;">
                    <?php echo $moisture < 35 ? '⚠️ Needs Water' : '✅ Optimal Hydration'; ?>
                </div>
                <div class="data-meta">
                    <span>Range: 0-100%</span>
                    <span>Modbus RS485</span>
                </div>
            </div>

            <div class="sensor-card">
                <div class="sensor-label">Soil Temperature</div>
                <div style="font-size: 4rem; margin: 1rem 0; color: #f57c00;">
                    <?php echo round($temp, 1); ?><span style="font-size: 1.5rem; vertical-align: top;">°C</span>
                </div>
                <p style="color: var(--secondary);">
                    <?php echo $temp > 30 ? 'High Thermal Stress' : 'Stable Temperature'; ?>
                </p>
                <div class="data-meta">
                    <span>Range: -40 to 80°C</span>
                    <span>Modbus RS485</span>
                </div>
            </div>

            <div class="sensor-card">
                <div class="sensor-label">Soil pH Level</div>
                <div class="gauge-container">
                    <svg class="gauge-svg" viewBox="0 0 100 100">
                        <circle class="gauge-bg" cx="50" cy="50" r="45" style="stroke: #f3e5f5;"></circle>
                        <circle class="gauge-fill" cx="50" cy="50" r="45" 
                                style="stroke: #9c27b0; stroke-dasharray: <?php echo ($ph / 14) * 283; ?>, 283;"></circle>
                    </svg>
                    <div class="gauge-value" style="color: #9c27b0;"><?php echo round($ph, 1); ?></div>
                </div>
                <p style="color: var(--secondary);">
                    <?php 
                        if($ph < 6) echo "Acidic Soil";
                        elseif($ph > 7.5) echo "Alkaline Soil";
                        else echo "Neutral Soil";
                    ?>
                </p>
                <div class="data-meta">
                    <span>Range: 0-14 pH</span>
                    <span>Modbus RS485</span>
                </div>
            </div>
        </div>

        <div class="sensor-card" style="margin-top: 2rem; text-align: left; padding: 1.5rem;">
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <div style="display: flex; align-items: center;">
                    <span class="status-dot" style="background: <?php echo (time() - strtotime($data['timestamp']) < 120) ? '#4caf50' : '#f44336'; ?>;"></span>
                    <b style="font-size: 0.9rem;">
                        Hardware Status: <?php echo (time() - strtotime($data['timestamp']) < 120) ? 'Online' : 'Offline (Check Hardware)'; ?>
                    </b>
                </div>
                <span style="font-size: 0.8rem; color: var(--secondary);">Database ID: #<?php echo $data['id'] ?? '0'; ?></span>
            </div>
        </div>
    </main>

    <script>
        // Refresh the page every 30 seconds to show latest sensor data
        setTimeout(function(){
           window.location.reload();
        }, 30000);
    </script>

</body>
</html>