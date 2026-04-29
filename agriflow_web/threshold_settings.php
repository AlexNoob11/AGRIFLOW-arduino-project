<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agriflow | Threshold Settings</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Threshold Specific Styles */
        .threshold-container { background: white; padding: 2rem; border-radius: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        
        table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
        th { text-align: left; padding: 12px; border-bottom: 2px solid #eee; color: var(--secondary); font-size: 0.9rem; }
        td { padding: 15px 12px; border-bottom: 1px solid #eee; }
        
        .badge { padding: 4px 10px; border-radius: 6px; font-size: 0.8rem; font-weight: 600; }
        .badge-moisture { background: #e3f2fd; color: #1976d2; }
        .badge-temp { background: #fff3e0; color: #f57c00; }

        /* Modal Styles */
        .modal-overlay { 
            position: fixed; top: 0; left: 0; width: 100%; height: 100%; 
            background: rgba(0,0,0,0.5); display: none; justify-content: center; 
            align-items: center; z-index: 2000; backdrop-filter: blur(4px);
        }
        .modal-card { background: white; padding: 2rem; border-radius: 20px; width: 90%; max-width: 400px; }
        .modal-card h2 { margin-top: 0; color: var(--accent); }
        
        .form-group { margin-bottom: 1.5rem; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; color: var(--secondary); }
        .form-group input, .form-group select { 
            width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 10px; font-size: 1rem; 
        }

        .btn-group { display: flex; gap: 10px; margin-top: 2rem; }
        .btn-save { background: var(--accent); color: white; border: none; flex: 1; padding: 12px; border-radius: 10px; cursor: pointer; font-weight: 600; }
        .btn-cancel { background: #eee; color: var(--text); border: none; flex: 1; padding: 12px; border-radius: 10px; cursor: pointer; }
    </style>
</head>
<body>

    <?php include('sidebar/sidebar.php'); ?>

    <main>
        <header>
            <div>
                <h1>Threshold Settings</h1>
                <p style="color: var(--secondary);">Define triggers for automated irrigation cycles.</p>
            </div>
            <button class="btn-action" onclick="openModal()">+ Add New Threshold</button>
        </header>

        <section class="threshold-container">
            <table>
                <thead>
                    <tr>
                        <th>Sensor Type</th>
                        <th>Condition</th>
                        <th>Value</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="thresholdTableBody">
                    </tbody>
            </table>
        </section>
    </main>

    <div class="modal-overlay" id="modalOverlay">
        <div class="modal-card">
            <h2 id="modalTitle">New Threshold</h2>
            <form id="thresholdForm">
                <input type="hidden" id="editIndex">
                <div class="form-group">
                    <label>Sensor Type</label>
                    <select id="sensorType" required>
                        <option value="Soil Moisture">Soil Moisture (%)</option>
                        <option value="Temperature">Temperature (°C)</option>
                        <option value="Humidity">Humidity (%)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Condition</label>
                    <select id="condition" required>
                        <option value="Less than">Less than (<)</option>
                        <option value="Greater than">Greater than (>)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Threshold Value</label>
                    <input type="number" id="sensorValue" placeholder="e.g. 35" required>
                </div>
                <div class="btn-group">
                    <button type="button" class="btn-cancel" onclick="closeModal()">Cancel</button>
                    <button type="submit" class="btn-save">Save Threshold</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Use LocalStorage to simulate a database
        let thresholds = JSON.parse(localStorage.getItem('agriflow_thresholds')) || [
            { type: 'Soil Moisture', condition: 'Less than', value: 35, active: true }
        ];

        const tableBody = document.getElementById('thresholdTableBody');
        const modal = document.getElementById('modalOverlay');
        const form = document.getElementById('thresholdForm');

        function renderTable() {
            tableBody.innerHTML = '';
            thresholds.forEach((t, index) => {
                const badgeClass = t.type === 'Soil Moisture' ? 'badge-moisture' : 'badge-temp';
                tableBody.innerHTML += `
                    <tr>
                        <td><span class="badge ${badgeClass}">${t.type}</span></td>
                        <td>${t.condition}</td>
                        <td><b>${t.value}${t.type === 'Temperature' ? '°C' : '%'}</b></td>
                        <td><span style="color: #4caf50;">● Active</span></td>
                        <td>
                            <button onclick="editThreshold(${index})" style="background:none; border:none; color:blue; cursor:pointer; margin-right:10px;">Edit</button>
                            <button onclick="deleteThreshold(${index})" style="background:none; border:none; color:red; cursor:pointer;">Delete</button>
                        </td>
                    </tr>
                `;
            });
            localStorage.setItem('agriflow_thresholds', JSON.stringify(thresholds));
        }

        function openModal() {
            document.getElementById('modalTitle').innerText = "New Threshold";
            document.getElementById('editIndex').value = "";
            form.reset();
            modal.style.display = 'flex';
        }

        function closeModal() { modal.style.display = 'none'; }

        function editThreshold(index) {
            const t = thresholds[index];
            document.getElementById('modalTitle').innerText = "Edit Threshold";
            document.getElementById('editIndex').value = index;
            document.getElementById('sensorType').value = t.type;
            document.getElementById('condition').value = t.condition;
            document.getElementById('sensorValue').value = t.value;
            modal.style.display = 'flex';
        }

        function deleteThreshold(index) {
            if(confirm("Delete this threshold?")) {
                thresholds.splice(index, 1);
                renderTable();
            }
        }

        form.onsubmit = (e) => {
            e.preventDefault();
            const index = document.getElementById('editIndex').value;
            const newData = {
                type: document.getElementById('sensorType').value,
                condition: document.getElementById('condition').value,
                value: document.getElementById('sensorValue').value,
                active: true
            };

            if (index === "") {
                thresholds.push(newData);
            } else {
                thresholds[index] = newData;
            }

            closeModal();
            renderTable();
        };

        // Initial Render
        renderTable();
    </script>
</body>
</html>