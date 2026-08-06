/*
 * Salary Manager - NodeMCU ESP8266 Biometric & RFID Attendance Terminal
 * 
 * Hardware Connections:
 * - NodeMCU ESP8266 (ESP-12E)
 * - 1.3" OLED Display (I2C): SDA -> D2 (GPIO4), SCL -> D1 (GPIO5), VCC -> 3V3/5V, GND -> GND
 * - PN532 RFID Module (I2C): SDA -> D2 (GPIO4), SCL -> D1 (GPIO5), VCC -> 3V3/5V, GND -> GND
 * - Buzzer: Positive -> D5 (GPIO14), Negative -> GND
 * - Tactile Button: Terminal 1 -> D6 (GPIO12), Terminal 2 -> GND
 * 
 * Requirements & Features:
 * 1. Initial / Fallback Access Point Mode:
 *    - IP: 192.168.4.1
 *    - SSID: attendance
 *    - Password: password
 * 2. Web Server & mDNS:
 *    - Web Server at IP & http://attendance.local
 *    - Web UI to configure Wi-Fi credentials, Server URL, Device ID
 * 3. PN532 RFID Reader & Buzzer Audio Feedback
 * 4. 1.3" OLED Display Interface
 * 5. Backend Integration with /attendance/save API
 * 6. Offline Punch Storage & Auto-Sync Queue (LittleFS)
 */

#include <ESP8266WiFi.h>
#include <ESP8266WebServer.h>
#include <ESP8266mDNS.h>
#include <ESP8266HTTPClient.h>
#include <WiFiClient.h>
#include <Wire.h>
#include <EEPROM.h>
#include <LittleFS.h>
#include <ArduinoJson.h>
#include <time.h>

// Display Libraries (Adafruit GFX + SH1106 / SSD1306)
#include <Adafruit_GFX.h>
#include <Adafruit_SH1106.h>
#include <Adafruit_PN532.h>

#include "config.h"

// Hardware Objects
Adafruit_SH1106 display(OLED_SDA_PIN, OLED_SCL_PIN); // I2C OLED
Adafruit_PN532 nfc(OLED_SDA_PIN, OLED_SCL_PIN);     // I2C PN532

ESP8266WebServer server(80);
Config currentConfig;

bool inAPMode = false;
unsigned long buttonPressStart = 0;
unsigned long lastSyncCheck = 0;
String statusMessage = "Ready";

// Offline Punch Structure
struct OfflinePunch {
    char tagid[32];
    char dateStr[16];
    char timeStr[16];
};

// ==========================================
// Buzzer Helper Functions
// ==========================================
void beep(int durationMs, int count = 1) {
    for (int i = 0; i < count; i++) {
        digitalWrite(BUZZER_PIN, HIGH);
        delay(durationMs);
        digitalWrite(BUZZER_PIN, LOW);
        if (i < count - 1) delay(80);
    }
}

void beepSuccess() {
    beep(70, 2); // Double short beep
}

void beepError() {
    beep(400, 1); // Long warning beep
}

void beepScan() {
    beep(50, 1); // Quick scan beep
}

// ==========================================
// Display Helper Functions
// ==========================================
void updateDisplay(String line1, String line2 = "", String line3 = "", String line4 = "") {
    display.clearDisplay();
    display.setTextSize(1);
    display.setTextColor(WHITE);
    display.setCursor(0, 0);
    
    // Header
    display.println("Salary Manager");
    display.println("--------------------");
    
    display.println(line1);
    if (line2.length() > 0) display.println(line2);
    if (line3.length() > 0) display.println(line3);
    if (line4.length() > 0) display.println(line4);
    
    display.display();
}

// ==========================================
// EEPROM Config Management
// ==========================================
void loadConfig() {
    EEPROM.begin(EEPROM_SIZE);
    EEPROM.get(0, currentConfig);
    
    if (currentConfig.configured != true) {
        // Defaults
        strncpy(currentConfig.wifi_ssid, "", sizeof(currentConfig.wifi_ssid));
        strncpy(currentConfig.wifi_pass, "", sizeof(currentConfig.wifi_pass));
        strncpy(currentConfig.server_url, DEFAULT_SERVER_URL, sizeof(currentConfig.server_url));
        strncpy(currentConfig.device_id, DEFAULT_DEVICE_ID, sizeof(currentConfig.device_id));
        currentConfig.configured = false;
    }
}

void saveConfig() {
    currentConfig.configured = true;
    EEPROM.put(0, currentConfig);
    EEPROM.commit();
}

void resetConfig() {
    currentConfig.configured = false;
    EEPROM.put(0, currentConfig);
    EEPROM.commit();
}

// ==========================================
// Web Server & Captive Portal Configuration
// ==========================================
void handleRoot() {
    String html = "<!DOCTYPE html><html><head><title>Attendance Terminal Config</title>";
    html += "<meta name='viewport' content='width=device-width, initial-scale=1'>";
    html += "<style>body{font-family:Arial,sans-serif;margin:20px;background:#f4f6f9;color:#333}";
    html += ".card{background:#fff;padding:20px;border-radius:8px;max-width:400px;margin:auto;box-shadow:0 2px 10px rgba(0,0,0,0.1)}";
    html += "h2{color:#1e3a5f;margin-top:0}label{font-weight:bold;display:block;margin-top:12px}";
    html += "input[type=text],input[type=password]{width:100%;padding:10px;margin-top:4px;border:1px solid #ccc;border-radius:4px;box-sizing:border-box}";
    html += "input[type=submit]{background:#10b981;color:#fff;border:0;padding:12px;width:100%;margin-top:20px;border-radius:4px;font-weight:bold;cursor:pointer}";
    html += ".status{background:#e0f2fe;color:#075985;padding:10px;border-radius:4px;margin-bottom:15px}";
    html += "</style></head><body><div class='card'>";
    html += "<h2>Salary Manager Terminal</h2>";
    html += "<div class='status'>Status: " + String(inAPMode ? "Access Point Mode (192.168.4.1)" : "Connected (" + WiFi.localIP().toString() + ")") + "</div>";
    
    html += "<form action='/save' method='POST'>";
    html += "<label>Wi-Fi Network SSID:</label>";
    html += "<input type='text' name='ssid' value='" + String(currentConfig.wifi_ssid) + "' required>";
    
    html += "<label>Wi-Fi Password:</label>";
    html += "<input type='password' name='pass' value='" + String(currentConfig.wifi_pass) + "'>";
    
    html += "<label>Attendance API Server URL:</label>";
    html += "<input type='text' name='server' value='" + String(currentConfig.server_url) + "' required>";
    
    html += "<label>Device / Machine Code:</label>";
    html += "<input type='text' name='device' value='" + String(currentConfig.device_id) + "' required>";
    
    html += "<input type='submit' value='Save & Connect'>";
    html += "</form></div></body></html>";
    
    server.send(200, "text/html", html);
}

void handleSave() {
    if (server.hasArg("ssid") && server.hasArg("server")) {
        strncpy(currentConfig.wifi_ssid, server.arg("ssid").c_str(), sizeof(currentConfig.wifi_ssid));
        strncpy(currentConfig.wifi_pass, server.arg("pass").c_str(), sizeof(currentConfig.wifi_pass));
        strncpy(currentConfig.server_url, server.arg("server").c_str(), sizeof(currentConfig.server_url));
        strncpy(currentConfig.device_id, server.arg("device").c_str(), sizeof(currentConfig.device_id));
        
        saveConfig();
        
        String html = "<html><body><h2>Configuration Saved!</h2><p>Rebooting and connecting to Wi-Fi...</p></body></html>";
        server.send(200, "text/html", html);
        delay(1500);
        ESP.restart();
    } else {
        server.send(400, "text/plain", "Bad Request");
    }
}

void setupWebServer() {
    server.on("/", handleRoot);
    server.on("/save", HTTP_POST, handleSave);
    server.onNotFound([]() {
        server.sendHeader("Location", "http://192.168.4.1/", true);
        server.send(302, "text/plain", "");
    });
    server.begin();
}

// ==========================================
// Access Point & Wi-Fi Management
// ==========================================
void startAPMode() {
    inAPMode = true;
    WiFi.mode(WIFI_AP);
    
    IPAddress local_IP(192, 168, 4, 1);
    IPAddress gateway(192, 168, 4, 1);
    IPAddress subnet(255, 255, 255, 0);
    
    WiFi.softAPConfig(local_IP, gateway, subnet);
    WiFi.softAP(AP_SSID, AP_PASSWORD);
    
    MDNS.begin(MDNS_NAME);
    MDNS.addService("http", "tcp", 80);
    
    setupWebServer();
    updateDisplay("AP Mode Active", "SSID: " String(AP_SSID), "Pass: " String(AP_PASSWORD), "IP: 192.168.4.1");
    beep(100, 3);
}

void connectWiFi() {
    if (strlen(currentConfig.wifi_ssid) == 0) {
        startAPMode();
        return;
    }
    
    WiFi.mode(WIFI_STA);
    WiFi.begin(currentConfig.wifi_ssid, currentConfig.wifi_pass);
    
    updateDisplay("Connecting Wi-Fi...", String(currentConfig.wifi_ssid));
    
    int attempts = 0;
    while (WiFi.status() != WL_CONNECTED && attempts < 25) {
        delay(500);
        attempts++;
    }
    
    if (WiFi.status() == WL_CONNECTED) {
        inAPMode = false;
        configTime(19800, 0, "pool.ntp.org", "time.nist.gov"); // IST UTC+5:30
        
        MDNS.begin(MDNS_NAME);
        MDNS.addService("http", "tcp", 80);
        setupWebServer();
        
        updateDisplay("Wi-Fi Connected!", "IP: " + WiFi.localIP().toString(), "Domain: attendance.local");
        beepSuccess();
        delay(1500);
    } else {
        // Fall back to Access Point Mode
        startAPMode();
    }
}

// ==========================================
// Offline Queue (LittleFS Storage)
// ==========================================
void saveOfflinePunch(String tagid, String dateStr, String timeStr) {
    DynamicJsonDocument doc(4096);
    JsonArray array;
    
    if (LittleFS.exists("/punches.json")) {
        File file = LittleFS.open("/punches.json", "r");
        deserializeJson(doc, file);
        file.close();
    }
    
    array = doc.as<JsonArray>();
    if (array.isNull()) {
        array = doc.to<JsonArray>();
    }
    
    JsonObject obj = array.createNestedObject();
    obj["tagid"] = tagid;
    obj["dt"] = dateStr;
    obj["tim"] = timeStr;
    
    File file = LittleFS.open("/punches.json", "w");
    serializeJson(doc, file);
    file.close();
}

void syncOfflinePunches() {
    if (WiFi.status() != WL_CONNECTED || !LittleFS.exists("/punches.json")) return;
    
    File file = LittleFS.open("/punches.json", "r");
    DynamicJsonDocument doc(4096);
    DeserializationError error = deserializeJson(doc, file);
    file.close();
    
    if (error || !doc.is<JsonArray>()) return;
    
    JsonArray array = doc.as<JsonArray>();
    if (array.size() == 0) return;
    
    DynamicJsonDocument remainingDoc(4096);
    JsonArray remainingArray = remainingDoc.to<JsonArray>();
    
    WiFiClient client;
    HTTPClient http;
    
    for (JsonObject obj : array) {
        String tagid = obj["tagid"].as<String>();
        String dt = obj["dt"].as<String>();
        String tim = obj["tim"].as<String>();
        
        String url = String(currentConfig.server_url);
        url += "?tagid=" + tagid + "&tagms=" + String(currentConfig.device_id) + "&dt=" + dt + "&tim=" + tim;
        
        http.begin(client, url);
        int httpCode = http.GET();
        http.end();
        
        if (httpCode != 200) {
            // Keep in queue for next retry
            remainingArray.add(obj);
        }
    }
    
    File writeFile = LittleFS.open("/punches.json", "w");
    serializeJson(remainingDoc, writeFile);
    writeFile.close();
}

// ==========================================
// Attendance API Punch Submission
// ==========================================
void submitPunch(String tagid) {
    beepScan();
    
    time_t now = time(nullptr);
    struct tm* timeinfo = localtime(&now);
    
    char dateBuf[16];
    char timeBuf[16];
    
    if (now > 100000) {
        strftime(dateBuf, sizeof(dateBuf), "%Y-%m-%d", timeinfo);
        strftime(timeBuf, sizeof(timeBuf), "%H:%M", timeinfo);
    } else {
        // Fallback default
        strcpy(dateBuf, "2026-08-01");
        strcpy(timeBuf, "09:00");
    }
    
    if (WiFi.status() == WL_CONNECTED) {
        WiFiClient client;
        HTTPClient http;
        
        String url = String(currentConfig.server_url);
        url += "?tagid=" + tagid + "&tagms=" + String(currentConfig.device_id) + "&dt=" + String(dateBuf) + "&tim=" + String(timeBuf);
        
        updateDisplay("Sending Punch...", "Tag: " + tagid);
        
        http.begin(client, url);
        int httpCode = http.GET();
        
        if (httpCode == 200) {
            String payload = http.getString();
            http.end();
            
            DynamicJsonDocument doc(1024);
            deserializeJson(doc, payload);
            
            String employee = doc["employee"] | "Employee";
            String msg = doc["message"] | "Success";
            
            if (msg == "Success") {
                beepSuccess();
                updateDisplay("Attendance Saved!", employee, "Status: " + msg, timeBuf);
            } else if (msg == "Already Exists") {
                beep(150, 1);
                updateDisplay("Already Marked", employee, timeBuf);
            } else {
                beepError();
                updateDisplay("Invalid Employee", "Tag: " + tagid, msg);
            }
        } else {
            http.end();
            saveOfflinePunch(tagid, String(dateBuf), String(timeBuf));
            beep(100, 2);
            updateDisplay("Saved Offline", "Tag: " + tagid, "Queued to Memory");
        }
    } else {
        saveOfflinePunch(tagid, String(dateBuf), String(timeBuf));
        beep(100, 2);
        updateDisplay("Saved Offline", "Tag: " + tagid, "No Wi-Fi Connection");
    }
    
    delay(2000);
}

// ==========================================
// Setup
// ==========================================
void setup() {
    Serial.begin(115200);
    Wire.begin(OLED_SDA_PIN, OLED_SCL_PIN);
    
    pinMode(BUZZER_PIN, OUTPUT);
    digitalWrite(BUZZER_PIN, LOW);
    
    pinMode(BUTTON_PIN, INPUT_PULLUP);
    
    LittleFS.begin();
    loadConfig();
    
    // OLED Init
    display.begin(SH1106_SWITCHCAPVCC, OLED_I2C_ADDR);
    display.clearDisplay();
    display.display();
    
    updateDisplay("Salary Manager", "Initialising...", "RFID Terminal");
    beep(80, 1);
    delay(1000);
    
    // PN532 Init
    nfc.begin();
    uint32_t versiondata = nfc.getFirmwareVersion();
    if (!versiondata) {
        updateDisplay("PN532 Error!", "Check Wiring", "SDA: D2, SCL: D1");
        beepError();
        while (1) delay(100);
    }
    
    nfc.SAMConfig(); // Configure board to read RFID tags
    connectWiFi();
}

// ==========================================
// Main Loop
// ==========================================
void loop() {
    server.handleClient();
    MDNS.update();
    
    // Tactile Button Check (Hold >3s to reset Wi-Fi & launch AP Mode)
    if (digitalRead(BUTTON_PIN) == LOW) {
        if (buttonPressStart == 0) buttonPressStart = millis();
        if (millis() - buttonPressStart > 3000) {
            beep(200, 2);
            updateDisplay("Resetting Wi-Fi...", "Launching AP Mode");
            resetConfig();
            startAPMode();
            buttonPressStart = 0;
        }
    } else {
        buttonPressStart = 0;
    }
    
    // Periodic Offline Sync (Every 30s)
    if (millis() - lastSyncCheck > 30000) {
        lastSyncCheck = millis();
        syncOfflinePunches();
    }
    
    // Read PN532 RFID Cards
    uint8_t success;
    uint8_t uid[7];
    uint8_t uidLength;
    
    success = nfc.readPassiveTargetID(PN532_MIFARE_ISO14443A, uid, &uidLength, 100);
    
    if (success) {
        String tagidStr = "";
        for (uint8_t i = 0; i < uidLength; i++) {
            if (i > 0) tagidStr += " ";
            if (uid[i] < 0x10) tagidStr += "0";
            tagidStr += String(uid[i], HEX);
        }
        tagidStr.toUpperCase();
        
        submitPunch(tagidStr);
    }
    
    // Ready Idle Display
    static unsigned long lastIdleUpdate = 0;
    if (millis() - lastIdleUpdate > 5000) {
        lastIdleUpdate = millis();
        if (inAPMode) {
            updateDisplay("AP Mode (Config)", "SSID: " String(AP_SSID), "Pass: " String(AP_PASSWORD), "IP: 192.168.4.1");
        } else {
            updateDisplay("Ready to Scan", "SSID: " + WiFi.SSID(), "IP: " + WiFi.localIP().toString(), "Domain: attendance.local");
        }
    }
}
