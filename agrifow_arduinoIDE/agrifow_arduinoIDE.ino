#include <ModbusMaster.h>
#include <SoftwareSerial.h>
#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
#include <WiFiClient.h>

// --- CONFIGURATION ---
const char* ssid = "ZTE_2.4G_S5LKD4";
const char* password = "11756oplok";
const char* serverUrl = "http://192.168.1.8/project1/arduino_sync.php";

#define RX_PIN 13     // D7
#define TX_PIN 12     // D6
#define RELAY_PIN 14  // D5 (GPIO 14)

// --- RELAY LOGIC FLIP ---
#define PUMP_ON HIGH    // Now HIGH = ON
#define PUMP_OFF LOW    // Now LOW = OFF
// ------------------------

SoftwareSerial rs485(RX_PIN, TX_PIN);
ModbusMaster node;

int lastPumpState = -1; 
unsigned long bootTime; 

void setup() {
  // 1. Initialize Pump to OFF immediately
  pinMode(RELAY_PIN, OUTPUT);
  digitalWrite(RELAY_PIN, PUMP_OFF);

  Serial.begin(9600);
  bootTime = millis(); // Start the 10-second safety timer
  
  delay(500); 

  WiFi.begin(ssid, password);
  Serial.print("Connecting WiFi");
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }
  Serial.println("\nWiFi Connected!");

  rs485.begin(4800);
  node.begin(1, rs485);
}

void loop() {
  // 🔒 SAFETY: if WiFi lost → FORCE OFF pump
  if (WiFi.status() != WL_CONNECTED) {
    digitalWrite(RELAY_PIN, PUMP_OFF);
    Serial.println("WiFi lost -> Pump OFF");
    delay(2000);
    return;
  }

  uint8_t result = node.readInputRegisters(0x0000, 4);

  if (result == node.ku8MBSuccess) {
    float moisture = node.getResponseBuffer(0) / 10.0;
    float temp     = node.getResponseBuffer(1) / 10.0;
    float ph       = node.getResponseBuffer(3) / 10.0;

    Serial.print("Sensor -> Moisture: "); Serial.print(moisture);
    Serial.print("% Temp: "); Serial.print(temp);
    Serial.print(" pH: "); Serial.println(ph);

    syncWithServer(moisture, temp, ph);
  } else {
    Serial.println("Modbus Read Failed -> Pump OFF");
    digitalWrite(RELAY_PIN, PUMP_OFF);
  }

  delay(5000);
}

void syncWithServer(float m, float t, float p) {
  WiFiClient client;
  HTTPClient http;

  String postData = "moisture=" + String(m) + "&temp=" + String(t) + "&ph=" + String(p);

  http.begin(client, serverUrl);
  http.addHeader("Content-Type", "application/x-www-form-urlencoded");

  int httpCode = http.POST(postData);

  if (httpCode == 200) {
    String payload = http.getString();
    payload.trim();
    processCommand(payload);
  } else {
    Serial.print("Server Error: ");
    Serial.println(httpCode);
    digitalWrite(RELAY_PIN, PUMP_OFF);
  }

  http.end();
}

void processCommand(String command) {
  int sep = command.indexOf(':');
  if (sep == -1) {
    digitalWrite(RELAY_PIN, PUMP_OFF);
    return;
  }

  int pumpCmd = command.substring(sep + 1).toInt();

  // 🛡️ 10-SECOND BOOT SAFETY
  if (millis() - bootTime < 10000) {
    Serial.println("System Booting... Locking Pump OFF for safety.");
    digitalWrite(RELAY_PIN, PUMP_OFF);
    return;
  }

  if (pumpCmd != 0 && pumpCmd != 1) pumpCmd = 0;
  if (pumpCmd == lastPumpState) return;

  lastPumpState = pumpCmd;

  if (pumpCmd == 1) {
    digitalWrite(RELAY_PIN, PUMP_ON);   
    Serial.println(">>> PHYSICAL PUMP: ON");
  } else {
    digitalWrite(RELAY_PIN, PUMP_OFF);  
    Serial.println(">>> PHYSICAL PUMP: OFF");
  }
}