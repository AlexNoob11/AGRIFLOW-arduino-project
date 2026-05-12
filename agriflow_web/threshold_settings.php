<?php
session_start();
include 'db_connect.php'; // Ensure you have your DB connection

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Fetch existing profiles from database
$stmt = $pdo->query("SELECT * FROM plant_profiles ORDER BY id DESC");
$profiles = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agriflow | Threshold Settings</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .threshold-container { background: white; padding: 2rem; border-radius: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
        th { text-align: left; padding: 12px; border-bottom: 2px solid #eee; color: #666; font-size: 0.9rem; }
        td { padding: 15px 12px; border-bottom: 1px solid #eee; }
        
        .badge { padding: 4px 10px; border-radius: 6px; font-size: 0.8rem; font-weight: 600; display: inline-block; }
        .badge-moisture { background: #e3f2fd; color: #1976d2; }
        .badge-action { background: #e8f5e9; color: #2e7d32; }

        .modal-overlay { 
            position: fixed; top: 0; left: 0; width: 100%; height: 100%; 
            background: rgba(0,0,0,0.5); display: none; justify-content: center; 
            align-items: center; z-index: 2000; backdrop-filter: blur(4px);
        }
        .modal-card { background: white; padding: 2rem; border-radius: 20px; width: 90%; max-width: 450px; }
        .form-group { margin-bottom: 1.2rem; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; font-size: 0.9rem; }
        .form-group input, .form-group select { 
            width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px; box-sizing: border-box;
        }

        .btn-group { display: flex; gap: 10px; margin-top: 1.5rem; }
        .btn-save { background: #2ecc71; color: white; border: none; flex: 2; padding: 12px; border-radius: 10px; cursor: pointer; font-weight: 600; }
        .btn-cancel { background: #eee; color: #333; border: none; flex: 1; padding: 12px; border-radius: 10px; cursor: pointer; }
        .btn-action { background: #2ecc71; color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; }
    </style>
</head>
<body>

    <?php include('sidebar/sidebar.php'); ?>

    <main>
        <header style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <div>
                <h1>Control Thresholds</h1>
                <p style="color: #666;">Define the "Turn On" and "Turn Off" moisture points for your plants.</p>
            </div>
            <button class="btn-action" onclick="openModal()">+ Add New Profile</button>
        </header>

        <section class="threshold-container">
            <table>
                <thead>
                    <tr>
                        <th>Plant Key (Slug)</th>
                        <th>Display Name</th>
                        <th>Low (ON)</th>
                        <th>High (OFF)</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="thresholdTableBody">
                    <?php foreach ($profiles as $p): ?>
                    <tr>
                        <td><code><?php echo $p['plant_key']; ?></code></td>
                        <td><strong><?php echo $p['plant_name']; ?></strong></td>
                        <td><span class="badge badge-moisture">Below <?php echo $p['low_threshold']; ?>%</span></td>
                        <td><span class="badge badge-moisture">Above <?php echo $p['high_threshold']; ?>%</span></td>
                        <td>
                            <button onclick='editProfile(<?php echo json_encode($p); ?>)' style="background:none; border:none; color:blue; cursor:pointer; margin-right:10px;">Edit</button>
                            <button onclick="deleteProfile(<?php echo $p['id']; ?>)" style="background:none; border:none; color:red; cursor:pointer;">Delete</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    </main>

    <!-- Modal -->
    <div class="modal-overlay" id="modalOverlay">
        <div class="modal-card">
            <h2 id="modalTitle" style="margin-top:0">Plant Profile</h2>
            <form id="thresholdForm" action="save_profile.php" method="POST">
                <input type="hidden" name="id" id="profileId">
                
                <div class="form-group">
                    <label>Plant Key (Matches PHP Library)</label>
                    <input type="text" name="plant_key" id="plantKey" placeholder="e.g. tomato" required>
                    <small style="color: #999;">Lowercase, no spaces (e.g., leafy_greens)</small>
                </div>

                <div class="form-group">
                    <label>Display Name</label>
                    <input type="text" name="plant_name" id="plantName" placeholder="e.g. Cherry Tomatoes" required>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group">
                        <label>Low Threshold (ON %)</label>
                        <input type="number" step="0.1" name="low" id="lowThreshold" required>
                    </div>
                    <div class="form-group">
                        <label>High Threshold (OFF %)</label>
                        <input type="number" step="0.1" name="high" id="highThreshold" required>
                    </div>
                </div>

                <div class="btn-group">
                    <button type="button" class="btn-cancel" onclick="closeModal()">Cancel</button>
                    <button type="submit" class="btn-save">Save to Database</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const modal = document.getElementById('modalOverlay');
        const form = document.getElementById('thresholdForm');

        function openModal() {
            document.getElementById('modalTitle').innerText = "Add New Plant Profile";
            document.getElementById('profileId').value = "";
            form.reset();
            modal.style.display = 'flex';
        }

        function closeModal() { modal.style.display = 'none'; }

        function editProfile(data) {
            document.getElementById('modalTitle').innerText = "Edit " + data.plant_name;
            document.getElementById('profileId').value = data.id;
            document.getElementById('plantKey').value = data.plant_key;
            document.getElementById('plantName').value = data.plant_name;
            document.getElementById('lowThreshold').value = data.low_threshold;
            document.getElementById('highThreshold').value = data.high_threshold;
            modal.style.display = 'flex';
        }

        function deleteProfile(id) {
            if(confirm("Are you sure? This will remove these settings from the system.")) {
                window.location.href = "delete_profile.php?id=" + id;
            }
        }
    </script>
</body>
</html>
