<?php
include 'db_connect.php';

// 1. Updated Library: Set 'general' low to 17.0 as requested
$plantLibrary = [
    'general'      => ['low' => 17.0, 'high' => 60.0], 
    'leafy_greens' => ['low' => 45.0, 'high' => 70.0],
    'succulent'    => ['low' => 15.0, 'high' => 30.0],
    'tomato'       => ['low' => 40.0, 'high' => 65.0]
];

// SENSOR INPUT
$m = isset($_POST['moisture']) ? (float)$_POST['moisture'] : null;
$t = isset($_POST['temp']) ? (float)$_POST['temp'] : 0;
$p = isset($_POST['ph']) ? (float)$_POST['ph'] : 0;

// LOG SENSOR DATA
if ($m !== null) {
    $stmt = $pdo->prepare("INSERT INTO sensor_logs (moisture, temp, ph) VALUES (?, ?, ?)");
    $stmt->execute([$m, $t, $p]);
}

// GET CURRENT SYSTEM STATE
$controls = $pdo->query("SELECT * FROM device_controls WHERE id = 1")->fetch(PDO::FETCH_ASSOC);

$mode = $controls['mode'];
$dbPump = (int)$controls['pump_status'];
$plant = $controls['selected_plant'] ?? 'general';
$autoLock = (int)$controls['auto_lock'];

$desiredPump = $dbPump;

// ==========================================
// AUTO MODE LOGIC (SMART GUARD)
// ==========================================
if ($mode === 'auto' && $m !== null && $autoLock === 0) {

    $profile = $plantLibrary[$plant] ?? $plantLibrary['general'];

    // 🚩 SAFETY CHECK: If moisture is 0 or less, stop the pump.
    // This prevents flooding if the sensor falls out or breaks.
    if ($m <= 0) {
        $desiredPump = 0; 
    } 
    // 💧 TRIGGER: If moisture is 17.0 or below (Too Dry), turn ON.
    elseif ($m <= $profile['low']) {
        $desiredPump = 1;
    } 
    // ✅ STOP: If moisture reaches the high target, turn OFF.
    elseif ($m >= $profile['high']) {
        $desiredPump = 0;
    }
}

// ==========================================
// UPDATE DATABASE & SYNC LOGS
// ==========================================
if ($desiredPump !== $dbPump) {
    // 1. Update the control state
    $stmt = $pdo->prepare("UPDATE device_controls SET pump_status = ? WHERE id = 1");
    $stmt->execute([$desiredPump]);

    // 2. Handle Pump Logs & Liters
    if ($desiredPump == 1) {
        // Start Log
        $pdo->prepare("INSERT INTO pump_logs (start_time) VALUES (NOW())")->execute();
    } else {
        // Stop Log & Calculate Liters
        // Math: duration_seconds * 0.033
        $pdo->prepare("
            UPDATE pump_logs 
            SET end_time = NOW(), 
                duration_seconds = TIMESTAMPDIFF(SECOND, start_time, NOW()),
                liters_used = TIMESTAMPDIFF(SECOND, start_time, NOW()) * 0.033
            WHERE end_time IS NULL 
            ORDER BY id DESC LIMIT 1
        ")->execute();
    }
    $dbPump = $desiredPump;
}
// OUTPUT TO ESP (NodeMCU)
echo $mode . ":" . $dbPump;
exit();
?>