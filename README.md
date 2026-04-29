# AGRIFLOW-arduino-project
AgriFlow: Intelligent Precision Irrigation Ecosystem
AgriFlow is an advanced IoT-based automated watering and environmental monitoring system designed to optimize crop health while conserving water resources. By integrating a multi-sensor array with a real-time data pipeline, AgriFlow moves beyond simple timers to provide a truly data-driven approach to agriculture.

# 🚀 Core Functionality
The system utilizes an ESP8266 microcontroller to act as the brain of the field. It continuously polls soil conditions and atmospheric data, executing local logic to manage hydration and syncing data to a central dashboard.

Autonomous Irrigation: When soil moisture drops below a pre-defined threshold, the system triggers a water pump automatically. Once optimal saturation is reached, the pump deactivates to prevent overwatering and root rot.

Precision Monitoring: Beyond simple moisture, AgriFlow tracks the chemical and physical health of your soil using NPK sensors, allowing for informed fertilization strategies.

Cloud Integration: Monitor your fields from anywhere in the world via a web-based interface powered by PHP and MySQL.

#  Component,Technology
Microcontroller,ESP8266 (NodeMCU)

Firmware,C++ (Arduino IDE)

Backend,PHP 8.x

Database,MySQL

Editor,Visual Studio Code


🛠 Installation Guide
Follow these steps to set up the AgriFlow ecosystem, from the local web server to the hardware firmware.

1. Web Dashboard Setup (PHP & MySQL)
To host the monitoring dashboard, you will need a local server environment like XAMPP or WAMP.

Deploy Source Code:

Locate your agriflow_web folder.

Copy and paste this folder into your server's root directory (e.g., C:/xampp/htdocs/).

Database Configuration:

Open your browser and navigate to localhost/phpmyadmin.

Create a new database named agriflow.

Click the Import tab, select the agriflow_database.sql on folder named agriflow_database. file from your project folder, and click Go.

Connection Check:

Ensure your config.php or database connection file in the htdocs/agriflow_web folder matches your local database credentials (usually root with no password).

2. Hardware Setup (Arduino IDE)
Before uploading, ensure you have the ESP8266 board library installed in your Arduino IDE.

Open Project:

Launch Arduino IDE.

Go to File > Open and select the agriflow_arduinoIDE.ino file.

Configure Environment:

Connect your NodeMCU v3 (ESP8266) to your computer via USB.

Go to Tools > Board and select NodeMCU 1.0 (ESP12-E Module).

Go to Tools > Port and select the COM port associated with your device.

Update Credentials:

Inside the code, locate the variables for SSID, PASSWORD, and SERVER_IP.

Input your Wi-Fi details and the local IP address of your computer (running the XAMPP server).

Upload:

Click the Upload button (right arrow icon). Wait for the "Done uploading" message.
