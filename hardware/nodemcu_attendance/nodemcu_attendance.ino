/*
 * Salary Manager - NodeMCU ESP8266 Biometric & RFID Attendance Terminal
 * 
 * Hardware Connections (Identical & Unchanged):
 * - NodeMCU ESP8266 (ESP-12E / CP2102)
 * - 1.3" OLED Display (I2C): SDA -> D2 (GPIO4), SCL -> D1 (GPIO5)
 * - PN532 RFID Module (I2C): SDA -> D2 (GPIO4), SCL -> D1 (GPIO5) [DIP Switch: 1=OFF, 2=ON]
 * - Active/Passive Buzzer: Positive -> D5 (GPIO14), Negative -> GND
 * - Tactile Button: Terminal 1 -> D6 (GPIO12), Terminal 2 -> GND
 * 
 * Required Libraries (Install via Arduino IDE Library Manager):
 * 1. Adafruit GFX Library
 * 2. Adafruit SH1106 (or Adafruit SSD1306)
 * 3. Adafruit PN532
 */

#include <ESP8266WiFi.h>
#include <ESP8266WebServer.h>
#include <ESP8266mDNS.h>
#include <ESP8266HTTPClient.h>
#include <WiFiClientSecure.h>
#include <WiFiClient.h>
#include <Wire.h>
#include <EEPROM.h>
#include <LittleFS.h>
#include <time.h>

#include <Adafruit_GFX.h>
#include <Adafruit_SH1106.h>
#include <Adafruit_PN532.h>

#include "config.h"

// Hardware Objects
Adafruit_SH1106 display(OLED_SDA_PIN, OLED_SCL_PIN); // 1.3" I2C OLED
Adafruit_PN532 nfc(OLED_SDA_PIN, OLED_SCL_PIN);     // I2C PN532

ESP8266WebServer server(80);
Config currentConfig;

bool inAPMode = false;
unsigned long buttonPressStart = 0;
unsigned long lastSyncCheck = 0;
unsigned long lastDisplayUpdate = 0;

// Helper to get Mode Character
char getModeChar(uint8_t mode) {
    switch (mode) {
        case MODE_SETUP:  return 'S';
        case MODE_READ:   return 'R';
        case MODE_WRITE:  return 'W';
        case MODE_FORMAT: return 'F';
        case MODE_DELETE: return 'D';
        case MODE_CLEAR:  return 'C';
        default:          return 'R';
    }
}

// Lightweight JSON Key-Value Parser
String parseJsonResponse(String json, String key) {
    int keyIndex = json.indexOf("\"" + key + "\"");
    if (keyIndex == -1) return "";
    int colonIndex = json.indexOf(":", keyIndex);
    if (colonIndex == -1) return "";
    int q1 = json.indexOf("\"", colonIndex);
    if (q1 == -1) return "";
    int q2 = json.indexOf("\"", q1 + 1);
    if (q2 == -1) return "";
    return json.substring(q1 + 1, q2);
}

// ==========================================
// Audio Feedback (Buzzer - Compatible with Active & Passive Buzzers)
// ==========================================
void beep(int durationMs, int count = 1, int freq = 2700) {
    for (int i = 0; i < count; i++) {
        digitalWrite(BUZZER_PIN, HIGH);
        tone(BUZZER_PIN, freq);
        delay(durationMs);
        digitalWrite(BUZZER_PIN, LOW);
        noTone(BUZZER_PIN);
        if (i < count - 1) delay(80);
    }
}

void beepSuccess() { beep(70, 2, 2800); }
void beepError()   { beep(400, 1, 1500); }
void beepScan()    { beep(50, 1, 2400); }

void beepPowerOn() {
    digitalWrite(BUZZER_PIN, HIGH);
    tone(BUZZER_PIN, 2000); delay(60); noTone(BUZZER_PIN); digitalWrite(BUZZER_PIN, LOW); delay(40);
    digitalWrite(BUZZER_PIN, HIGH);
    tone(BUZZER_PIN, 2500); delay(60); noTone(BUZZER_PIN); digitalWrite(BUZZER_PIN, LOW); delay(40);
    digitalWrite(BUZZER_PIN, HIGH);
    tone(BUZZER_PIN, 3000); delay(120); noTone(BUZZER_PIN); digitalWrite(BUZZER_PIN, LOW);
}

// ==========================================
// OLED Screen Renderer (Matching Hardware Photo Layout)
// ==========================================
void renderScreen(String customMsg = "") {
    display.clearDisplay();
    display.setTextWrap(false);
    
    // Top Line: Company / Institution Name (Centered)
    display.setTextSize(1);
    display.setTextColor(WHITE);
    String compName = String(currentConfig.company_name);
    if (compName.length() == 0) compName = DEFAULT_COMPANY_NAME;
    int16_t x1, y1;
    uint16_t w, h;
    display.getTextBounds(compName, 0, 0, &x1, &y1, &w, &h);
    int xPos = (128 - w) / 2;
    if (xPos < 0) xPos = 0;
    display.setCursor(xPos, 2);
    display.print(compName);
    
    if (customMsg.length() > 0) {
        // Show custom scan result
        display.setCursor(0, 24);
        display.setTextSize(1);
        display.println(customMsg);
    } else {
        // Line 2 (Center): Large Digital Clock (NTP Time)
        time_t now = time(nullptr);
        struct tm* timeinfo = localtime(&now);
        char timeStr[16];
        if (now > 100000) {
            strftime(timeStr, sizeof(timeStr), "%H:%M", timeinfo);
        } else {
            strcpy(timeStr, "--:--");
        }
        
        display.setTextSize(2); // Large Font for Time
        display.getTextBounds(timeStr, 0, 0, &x1, &y1, &w, &h);
        int clockX = (128 - w) / 2;
        if (clockX < 0) clockX = 0;
        display.setCursor(clockX, 22);
        display.print(timeStr);
    }
    
    // Line 3 (Bottom): IP Address (Left) & Mode Indicator ('S'/'R'/'W'/'F'/'D'/'C') (Right)
    display.setTextSize(1);
    display.setCursor(0, 52);
    
    if (inAPMode) {
        display.print("192.168.4.1");
    } else if (WiFi.status() == WL_CONNECTED) {
        display.print(WiFi.localIP().toString());
    } else {
        display.print("No Wi-Fi");
    }
    
    // Mode Indicator Right Aligned
    char mChar = getModeChar(currentConfig.op_mode);
    display.setCursor(118, 52);
    display.print(mChar);
    
    display.display();
}

// ==========================================
// EEPROM Configuration Persistence
// ==========================================
void sanitizeString(char* str, size_t maxLen) {
    str[maxLen - 1] = '\0';
    for (size_t i = 0; i < maxLen && str[i] != '\0'; i++) {
        if ((uint8_t)str[i] < 32 || (uint8_t)str[i] > 126) {
            str[i] = '\0';
            break;
        }
    }
}

void saveConfig() {
    currentConfig.magic = CONFIG_MAGIC;
    EEPROM.put(0, currentConfig);
    EEPROM.commit();
}

void loadConfig() {
    EEPROM.begin(EEPROM_SIZE);
    EEPROM.get(0, currentConfig);
    
    if (currentConfig.magic != CONFIG_MAGIC) {
        currentConfig.magic = CONFIG_MAGIC;
        strncpy(currentConfig.ap_ssid, DEFAULT_AP_SSID, sizeof(currentConfig.ap_ssid));
        strncpy(currentConfig.ap_pass, DEFAULT_AP_PASS, sizeof(currentConfig.ap_pass));
        strncpy(currentConfig.wifi_ssid, "", sizeof(currentConfig.wifi_ssid));
        strncpy(currentConfig.wifi_pass, "", sizeof(currentConfig.wifi_pass));
        strncpy(currentConfig.company_name, DEFAULT_COMPANY_NAME, sizeof(currentConfig.company_name));
        strncpy(currentConfig.location_name, DEFAULT_LOCATION, sizeof(currentConfig.location_name));
        strncpy(currentConfig.host_uri, DEFAULT_HOST_URI, sizeof(currentConfig.host_uri));
        strncpy(currentConfig.api_token, "", sizeof(currentConfig.api_token));
        strncpy(currentConfig.device_code, DEFAULT_DEVICE_CODE, sizeof(currentConfig.device_code));
        strncpy(currentConfig.card_value, "", sizeof(currentConfig.card_value));
        currentConfig.op_mode = MODE_READ;
        currentConfig.tz_offset = 19800; // IST UTC+5:30
        saveConfig();
    } else {
        sanitizeString(currentConfig.ap_ssid, sizeof(currentConfig.ap_ssid));
        sanitizeString(currentConfig.ap_pass, sizeof(currentConfig.ap_pass));
        sanitizeString(currentConfig.wifi_ssid, sizeof(currentConfig.wifi_ssid));
        sanitizeString(currentConfig.wifi_pass, sizeof(currentConfig.wifi_pass));
        sanitizeString(currentConfig.company_name, sizeof(currentConfig.company_name));
        sanitizeString(currentConfig.location_name, sizeof(currentConfig.location_name));
        sanitizeString(currentConfig.host_uri, sizeof(currentConfig.host_uri));
        sanitizeString(currentConfig.api_token, sizeof(currentConfig.api_token));
        sanitizeString(currentConfig.device_code, sizeof(currentConfig.device_code));
        sanitizeString(currentConfig.card_value, sizeof(currentConfig.card_value));
        
        if (strlen(currentConfig.ap_ssid) == 0) strncpy(currentConfig.ap_ssid, DEFAULT_AP_SSID, sizeof(currentConfig.ap_ssid));
        if (strlen(currentConfig.ap_pass) == 0) strncpy(currentConfig.ap_pass, DEFAULT_AP_PASS, sizeof(currentConfig.ap_pass));
        if (strlen(currentConfig.company_name) == 0) strncpy(currentConfig.company_name, DEFAULT_COMPANY_NAME, sizeof(currentConfig.company_name));
        if (strlen(currentConfig.host_uri) == 0) strncpy(currentConfig.host_uri, DEFAULT_HOST_URI, sizeof(currentConfig.host_uri));
        if (strlen(currentConfig.device_code) == 0) strncpy(currentConfig.device_code, DEFAULT_DEVICE_CODE, sizeof(currentConfig.device_code));
    }
}

void resetConfig() {
    currentConfig.magic = 0x00000000;
    EEPROM.put(0, currentConfig);
    EEPROM.commit();
    loadConfig();
}

// ==========================================
// Upgraded Web Management Portal
// ==========================================
void handleWebRoot() {
    String html = "<!DOCTYPE html><html lang='en'><head><meta charset='UTF-8'>";
    html += "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
    html += "<title>Attendance System | Leena IT Solutions</title>";
    html += "<style>";
    html += ":root{--primary:#1e3a5f;--accent:#10b981;--bg:#f4f6f9;--card:#ffffff;--text:#1f2937}";
    html += "body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;background:var(--bg);color:var(--text);margin:0;padding:20px}";
    html += ".container{max-width:600px;margin:auto}";
    html += ".header{text-align:center;margin-bottom:20px;padding-bottom:15px;border-bottom:2px solid #e5e7eb}";
    html += ".header h1{margin:0;font-size:24px;color:var(--primary)}";
    html += ".header p{margin:5px 0 0;font-size:13px;color:#6b7280}";
    html += ".card{background:var(--card);border-radius:12px;padding:20px;margin-bottom:20px;box-shadow:0 4px 15px rgba(0,0,0,0.05)}";
    html += ".card h2{margin-top:0;font-size:16px;color:var(--primary);border-bottom:1px solid #f3f4f6;padding-bottom:8px}";
    html += "label{display:block;font-size:13px;font-weight:600;margin-top:12px;color:#374151}";
    html += "input[type=text],input[type=password],select{width:100%;padding:10px 12px;margin-top:4px;border:1px solid #d1d5db;border-radius:6px;font-size:14px;box-sizing:border-box}";
    html += ".btn{background:var(--primary);color:#fff;border:0;padding:12px 20px;border-radius:6px;font-weight:bold;cursor:pointer;width:100%;font-size:15px;margin-top:15px;transition:background 0.2s}";
    html += ".btn:hover{background:#0f2744}";
    html += ".btn-success{background:var(--accent)}";
    html += ".btn-success:hover{background:#059669}";
    html += ".status-badge{display:inline-block;padding:4px 10px;border-radius:20px;font-size:12px;font-weight:bold;background:#d1fae5;color:#065f46}";
    html += ".status-badge.ap{background:#feefc3;color:#7c2d12}";
    html += ".grid{display:grid;grid-template-columns:1fr 1fr;gap:10px}";
    html += "</style></head><body><div class='container'>";
    
    html += "<div class='header'><h1>Attendance System</h1><p>Powered By Leena IT Solutions</p></div>";
    
    // Status Bar Card
    html += "<div class='card'><h2>Device Diagnostics</h2>";
    html += "<div style='margin-bottom:10px;'>System Status: ";
    if (inAPMode) {
        html += "<span class='status-badge ap'>Access Point Mode (192.168.4.1)</span>";
    } else {
        html += "<span class='status-badge'>Connected (" + WiFi.localIP().toString() + ")</span>";
    }
    html += "</div>";
    html += "<div class='grid'>";
    html += "<div><strong>mDNS Domain:</strong> http://attendance.local</div>";
    html += "<div><strong>Signal Strength:</strong> " + String(WiFi.RSSI()) + " dBm</div>";
    html += "</div></div>";
    
    // Main Config Form
    html += "<form action='/save' method='POST'>";
    
    // Access Point Setup Card
    html += "<div class='card'><h2>Access Point Setup</h2>";
    html += "<label>Enter Accesspoint SSID:</label>";
    html += "<input type='text' name='ap_ssid' value='" + String(currentConfig.ap_ssid) + "' required>";
    html += "<label>Enter Accesspoint Password:</label>";
    html += "<input type='password' name='ap_pass' value='" + String(currentConfig.ap_pass) + "' required>";
    html += "</div>";
    
    // WiFi Setup Card
    html += "<div class='card'><h2>WiFi Setup</h2>";
    html += "<label>Enter WiFi SSID:</label>";
    html += "<input type='text' name='wifi_ssid' value='" + String(currentConfig.wifi_ssid) + "'>";
    html += "<label>Enter WiFi Password:</label>";
    html += "<input type='password' name='wifi_pass' value='" + String(currentConfig.wifi_pass) + "'>";
    html += "</div>";
    
    // Terminal & Organization Settings Card
    html += "<div class='card'><h2>Terminal & Company Settings</h2>";
    html += "<label>Company / Institution Name:</label>";
    html += "<input type='text' name='company_name' value='" + String(currentConfig.company_name) + "' placeholder='Sarvodaya Vidyalay'>";
    html += "<label>Machine Code (tagms):</label>";
    html += "<input type='text' name='device_code' value='" + String(currentConfig.device_code) + "' required>";
    html += "<label>Operation Mode:</label>";
    html += "<select name='op_mode'>";
    html += "<option value='0'" + String(currentConfig.op_mode == MODE_SETUP ? " selected" : "") + ">Setup (S)</option>";
    html += "<option value='1'" + String(currentConfig.op_mode == MODE_READ ? " selected" : "") + ">Read (R) - Normal Attendance</option>";
    html += "<option value='2'" + String(currentConfig.op_mode == MODE_WRITE ? " selected" : "") + ">Write (W) - Card Burning</option>";
    html += "<option value='3'" + String(currentConfig.op_mode == MODE_FORMAT ? " selected" : "") + ">Format (F) - Format Card</option>";
    html += "<option value='4'" + String(currentConfig.op_mode == MODE_DELETE ? " selected" : "") + ">Delete (D) - Clear Card Data</option>";
    html += "<option value='5'" + String(currentConfig.op_mode == MODE_CLEAR ? " selected" : "") + ">Clear (C) - Flush Offline Queue</option>";
    html += "</select>";
    html += "<label>Host URI Endpoint:</label>";
    html += "<input type='text' name='host_uri' value='" + String(currentConfig.host_uri) + "' required>";
    html += "<label>Bearer API Access Token (Optional):</label>";
    html += "<input type='password' name='api_token' value='" + String(currentConfig.api_token) + "' placeholder='Bearer Token'>";
    html += "<input type='submit' class='btn' value='Save Settings'>";
    html += "</div>";
    html += "</form>";
    
    // RFID Card Writer Tool Card
    html += "<div class='card'><h2>Write Card</h2>";
    html += "<form action='/write_card' method='POST'>";
    html += "<label>Card Value / Employee Code:</label>";
    html += "<input type='text' name='card_val' value='" + String(currentConfig.card_value) + "' placeholder='Enter value to write'>";
    html += "<input type='submit' class='btn btn-success' value='Write Card'>";
    html += "</form></div>";
    
    html += "</div></body></html>";
    
    server.send(200, "text/html", html);
}

void handleSaveWeb() {
    if (server.hasArg("host_uri")) {
        String oldWifiSsid = String(currentConfig.wifi_ssid);
        String oldWifiPass = String(currentConfig.wifi_pass);
        String oldApSsid = String(currentConfig.ap_ssid);
        String oldApPass = String(currentConfig.ap_pass);
        
        if (server.hasArg("ap_ssid")) strncpy(currentConfig.ap_ssid, server.arg("ap_ssid").c_str(), sizeof(currentConfig.ap_ssid));
        if (server.hasArg("ap_pass")) strncpy(currentConfig.ap_pass, server.arg("ap_pass").c_str(), sizeof(currentConfig.ap_pass));
        if (server.hasArg("wifi_ssid")) strncpy(currentConfig.wifi_ssid, server.arg("wifi_ssid").c_str(), sizeof(currentConfig.wifi_ssid));
        if (server.hasArg("wifi_pass")) strncpy(currentConfig.wifi_pass, server.arg("wifi_pass").c_str(), sizeof(currentConfig.wifi_pass));
        if (server.hasArg("company_name")) strncpy(currentConfig.company_name, server.arg("company_name").c_str(), sizeof(currentConfig.company_name));
        if (server.hasArg("device_code")) strncpy(currentConfig.device_code, server.arg("device_code").c_str(), sizeof(currentConfig.device_code));
        if (server.hasArg("host_uri")) strncpy(currentConfig.host_uri, server.arg("host_uri").c_str(), sizeof(currentConfig.host_uri));
        if (server.hasArg("api_token")) strncpy(currentConfig.api_token, server.arg("api_token").c_str(), sizeof(currentConfig.api_token));
        if (server.hasArg("op_mode")) currentConfig.op_mode = server.arg("op_mode").toInt();
        
        // Save to EEPROM
        saveConfig();
        
        // Render OLED screen immediately with new settings
        renderScreen();
        beepSuccess();
        
        // Live Wi-Fi reconnect if credentials changed
        if (String(currentConfig.wifi_ssid) != oldWifiSsid || String(currentConfig.wifi_pass) != oldWifiPass) {
            if (strlen(currentConfig.wifi_ssid) > 0) {
                WiFi.disconnect();
                WiFi.begin(currentConfig.wifi_ssid, currentConfig.wifi_pass);
            }
        }
        
        // Live AP update if AP credentials changed
        if (inAPMode && (String(currentConfig.ap_ssid) != oldApSsid || String(currentConfig.ap_pass) != oldApPass)) {
            WiFi.softAP(currentConfig.ap_ssid, currentConfig.ap_pass);
        }
        
        String html = "<!DOCTYPE html><html><head><meta charset='UTF-8'><meta http-equiv='refresh' content='2;url=/'><style>body{font-family:-apple-system,BlinkMacSystemFont,sans-serif;background:#f4f6f9;text-align:center;padding:50px;} .card{background:#fff;padding:30px;border-radius:12px;display:inline-block;box-shadow:0 4px 15px rgba(0,0,0,0.1);} h2{color:#10b981;margin-top:0;} a{color:#1e3a5f;text-decoration:none;font-weight:bold;}</style></head><body><div class='card'><h2>✓ Settings Applied Live!</h2><p>Changes are saved and running in real-time without rebooting.</p><p><a href='/'>Returning to Dashboard...</a></p></div></body></html>";
        server.send(200, "text/html", html);
    } else {
        server.send(400, "text/plain", "Bad Request");
    }
}

void handleWriteCardWeb() {
    if (server.hasArg("card_val")) {
        strncpy(currentConfig.card_value, server.arg("card_val").c_str(), sizeof(currentConfig.card_value));
        currentConfig.op_mode = MODE_WRITE;
        saveConfig();
        
        String html = "<html><body><h2>Write Mode Armed!</h2><p>Please place RFID card near reader to write value: <strong>" + String(currentConfig.card_value) + "</strong></p><p><a href='/'>Back to Dashboard</a></p></body></html>";
        server.send(200, "text/html", html);
    } else {
        server.send(400, "text/plain", "Bad Request");
    }
}

void setupWebServer() {
    server.on("/", handleWebRoot);
    server.on("/save", HTTP_POST, handleSaveWeb);
    server.on("/write_card", HTTP_POST, handleWriteCardWeb);
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
    
    String apSsid = String(currentConfig.ap_ssid);
    String apPass = String(currentConfig.ap_pass);
    if (apSsid.length() == 0) apSsid = DEFAULT_AP_SSID;
    if (apPass.length() == 0) apPass = DEFAULT_AP_PASS;
    
    WiFi.softAPConfig(local_IP, gateway, subnet);
    WiFi.softAP(apSsid.c_str(), apPass.c_str());
    
    MDNS.begin(DEFAULT_MDNS_NAME);
    MDNS.addService("http", "tcp", 80);
    
    setupWebServer();
    beep(100, 3);
}

void connectWiFi() {
    if (strlen(currentConfig.wifi_ssid) == 0) {
        startAPMode();
        return;
    }
    
    WiFi.mode(WIFI_STA);
    WiFi.begin(currentConfig.wifi_ssid, currentConfig.wifi_pass);
    
    int attempts = 0;
    while (WiFi.status() != WL_CONNECTED && attempts < 20) {
        delay(500);
        attempts++;
    }
    
    if (WiFi.status() == WL_CONNECTED) {
        inAPMode = false;
        configTime(currentConfig.tz_offset, 0, "pool.ntp.org", "time.nist.gov");
        
        MDNS.begin(DEFAULT_MDNS_NAME);
        MDNS.addService("http", "tcp", 80);
        setupWebServer();
        beepSuccess();
    } else {
        startAPMode();
    }
}

// ==========================================
// High-Capacity Line-Based Offline Storage Queue
// Stores 10,000+ punches in LittleFS without RAM limits!
// ==========================================
void saveOfflinePunch(String tagid, String dateStr, String timeStr) {
    File file = LittleFS.open("/punches.txt", "a");
    if (file) {
        file.print(tagid);
        file.print(",");
        file.print(dateStr);
        file.print(",");
        file.println(timeStr);
        file.close();
    }
}

void syncOfflinePunches() {
    if (WiFi.status() != WL_CONNECTED || !LittleFS.exists("/punches.txt")) return;
    
    File file = LittleFS.open("/punches.txt", "r");
    if (!file || file.size() == 0) {
        if (file) file.close();
        return;
    }
    
    File tempFile = LittleFS.open("/punches_tmp.txt", "w");
    
    WiFiClientSecure clientSecure;
    WiFiClient clientPlain;
    HTTPClient http;
    
    String hostUri = String(currentConfig.host_uri);
    bool isHttps = hostUri.startsWith("https");
    if (isHttps) clientSecure.setInsecure();
    
    while (file.available()) {
        String line = file.readStringUntil('\n');
        line.trim();
        if (line.length() == 0) continue;
        
        int comma1 = line.indexOf(',');
        int comma2 = line.indexOf(',', comma1 + 1);
        
        if (comma1 != -1 && comma2 != -1) {
            String tagid = line.substring(0, comma1);
            String dt = line.substring(comma1 + 1, comma2);
            String tim = line.substring(comma2 + 1);
            
            String url = hostUri;
            url += (url.indexOf('?') >= 0 ? "&" : "?");
            url += "tagid=" + tagid + "&tagms=" + String(currentConfig.device_code) + "&dt=" + dt + "&tim=" + tim;
            
            if (isHttps) {
                http.begin(clientSecure, url);
            } else {
                http.begin(clientPlain, url);
            }
            
            if (strlen(currentConfig.api_token) > 0) {
                http.addHeader("Authorization", "Bearer " + String(currentConfig.api_token));
            }
            
            int httpCode = http.GET();
            http.end();
            
            if (httpCode != 200) {
                tempFile.println(line);
            }
        }
    }
    
    file.close();
    tempFile.close();
    
    LittleFS.remove("/punches.txt");
    LittleFS.rename("/punches_tmp.txt", "/punches.txt");
}

// ==========================================
// Mode Action Handlers (Setup, Read, Write, Format, Delete, Clear)
// ==========================================
void processCardScan(String tagidStr, uint8_t* uid, uint8_t uidLength) {
    beepScan();
    
    switch (currentConfig.op_mode) {
        
        // ----------------------------------
        // MODE 1: READ MODE (Default Attendance)
        // ----------------------------------
        case MODE_READ: {
            time_t now = time(nullptr);
            struct tm* timeinfo = localtime(&now);
            char dateBuf[16];
            char timeBuf[16];
            
            if (now > 100000) {
                strftime(dateBuf, sizeof(dateBuf), "%Y-%m-%d", timeinfo);
                strftime(timeBuf, sizeof(timeBuf), "%H:%M", timeinfo);
            } else {
                strcpy(dateBuf, "2026-08-01");
                strcpy(timeBuf, "09:00");
            }
            
            if (WiFi.status() == WL_CONNECTED) {
                WiFiClientSecure clientSecure;
                WiFiClient clientPlain;
                HTTPClient http;
                
                String url = String(currentConfig.host_uri);
                url += (url.indexOf('?') >= 0 ? "&" : "?");
                url += "tagid=" + tagidStr + "&tagms=" + String(currentConfig.device_code) + "&dt=" + String(dateBuf) + "&tim=" + String(timeBuf);
                
                bool isHttps = url.startsWith("https");
                if (isHttps) clientSecure.setInsecure();
                
                if (isHttps) {
                    http.begin(clientSecure, url);
                } else {
                    http.begin(clientPlain, url);
                }
                
                if (strlen(currentConfig.api_token) > 0) {
                    http.addHeader("Authorization", "Bearer " + String(currentConfig.api_token));
                }
                
                int httpCode = http.GET();
                
                if (httpCode == 200) {
                    String payload = http.getString();
                    http.end();
                    
                    String employee = parseJsonResponse(payload, "employee");
                    String msg = parseJsonResponse(payload, "message");
                    
                    if (employee.length() == 0) employee = "Employee";
                    if (msg.length() == 0) msg = "Success";
                    
                    if (msg == "Success") {
                        beepSuccess();
                        renderScreen("Saved: " + employee);
                    } else if (msg == "Already Exists") {
                        beep(150, 1, 2000);
                        renderScreen("Already Marked");
                    } else {
                        beepError();
                        renderScreen("Invalid Card");
                    }
                } else {
                    http.end();
                    saveOfflinePunch(tagidStr, String(dateBuf), String(timeBuf));
                    beep(100, 2, 2200);
                    renderScreen("Saved Offline");
                }
            } else {
                saveOfflinePunch(tagidStr, String(dateBuf), String(timeBuf));
                beep(100, 2, 2200);
                renderScreen("Saved Offline");
            }
            break;
        }
        
        // ----------------------------------
        // MODE 2: WRITE MODE (Card Burning)
        // ----------------------------------
        case MODE_WRITE: {
            if (strlen(currentConfig.card_value) == 0) {
                beepError();
                renderScreen("No Value Set!");
                break;
            }
            
            uint8_t keyA[6] = { 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF };
            uint8_t authenticated = nfc.mifareclassic_AuthenticateBlock(uid, uidLength, 4, 0, keyA);
            
            if (authenticated) {
                uint8_t blockData[16] = {0};
                strncpy((char*)blockData, currentConfig.card_value, 16);
                
                uint8_t writeSuccess = nfc.mifareclassic_WriteDataBlock(4, blockData);
                if (writeSuccess) {
                    beepSuccess();
                    renderScreen("Card Written OK!");
                    currentConfig.op_mode = MODE_READ; // Return to Read Mode
                    saveConfig();
                } else {
                    beepError();
                    renderScreen("Write Failed!");
                }
            } else {
                beepError();
                renderScreen("Auth Failed!");
            }
            break;
        }
        
        // ----------------------------------
        // MODE 3: FORMAT MODE (Clear Sectors)
        // ----------------------------------
        case MODE_FORMAT: {
            uint8_t keyA[6] = { 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF };
            if (nfc.mifareclassic_AuthenticateBlock(uid, uidLength, 4, 0, keyA)) {
                uint8_t emptyBlock[16] = {0};
                if (nfc.mifareclassic_WriteDataBlock(4, emptyBlock)) {
                    beepSuccess();
                    renderScreen("Formatted OK!");
                } else {
                    beepError();
                    renderScreen("Format Failed!");
                }
            } else {
                beepError();
                renderScreen("Auth Failed!");
            }
            break;
        }
        
        // ----------------------------------
        // MODE 4: DELETE MODE (Clear Card Data)
        // ----------------------------------
        case MODE_DELETE: {
            uint8_t keyA[6] = { 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF };
            if (nfc.mifareclassic_AuthenticateBlock(uid, uidLength, 4, 0, keyA)) {
                uint8_t emptyBlock[16] = {0};
                nfc.mifareclassic_WriteDataBlock(4, emptyBlock);
                beepSuccess();
                renderScreen("Card Cleared!");
            } else {
                beepError();
                renderScreen("Delete Failed!");
            }
            break;
        }
        
        // ----------------------------------
        // MODE 5: CLEAR MODE (Clear Queue)
        // ----------------------------------
        case MODE_CLEAR: {
            if (LittleFS.exists("/punches.txt")) {
                LittleFS.remove("/punches.txt");
            }
            beepSuccess();
            renderScreen("Queue Cleared!");
            currentConfig.op_mode = MODE_READ;
            saveConfig();
            break;
        }
        
        // ----------------------------------
        // MODE 0: SETUP MODE (Diagnostic)
        // ----------------------------------
        case MODE_SETUP: {
            beepScan();
            renderScreen("UID: " + tagidStr);
            break;
        }
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
    
    // Instant Power-On Audio Feedback
    beepPowerOn();
    
    pinMode(BUTTON_PIN, INPUT_PULLUP);
    
    LittleFS.begin();
    loadConfig();
    
    // OLED Init
    display.begin(SH1106_SWITCHCAPVCC, OLED_I2C_ADDR);
    display.clearDisplay();
    display.display();
    
    renderScreen("Initialising...");
    delay(500);
    
    // Official Adafruit PN532 Init
    nfc.begin();
    uint32_t versiondata = nfc.getFirmwareVersion();
    if (!versiondata) {
        renderScreen("PN532 Error!");
        beepError();
    } else {
        nfc.SAMConfig(); // Configure board to read RFID tags
    }
    
    connectWiFi();
}

// ==========================================
// Main Loop
// ==========================================
void loop() {
    server.handleClient();
    MDNS.update();
    
    // Tactile Switch Reset Check (Hold >3s to reset Wi-Fi & enter AP Mode)
    if (digitalRead(BUTTON_PIN) == LOW) {
        if (buttonPressStart == 0) buttonPressStart = millis();
        if (millis() - buttonPressStart > 3000) {
            beep(200, 2, 2000);
            renderScreen("Resetting Wi-Fi");
            resetConfig();
            startAPMode();
            buttonPressStart = 0;
        }
    } else {
        buttonPressStart = 0;
    }
    
    // Periodic Offline Queue Sync (Every 30 seconds)
    if (millis() - lastSyncCheck > 30000) {
        lastSyncCheck = millis();
        syncOfflinePunches();
    }
    
    // Periodic Screen Refresh for Digital Clock (Every 1 second)
    if (millis() - lastDisplayUpdate > 1000) {
        lastDisplayUpdate = millis();
        renderScreen();
    }
    
    // RFID Card Scan Detection via Adafruit_PN532
    uint8_t success;
    uint8_t uid[7];
    uint8_t uidLength = 0;
    
    success = nfc.readPassiveTargetID(PN532_MIFARE_ISO14443A, uid, &uidLength, 100);
    
    if (success) {
        String tagidStr = "";
        for (uint8_t i = 0; i < uidLength; i++) {
            if (i > 0) tagidStr += " ";
            if (uid[i] < 0x10) tagidStr += "0";
            tagidStr += String(uid[i], HEX);
        }
        tagidStr.toUpperCase();
        
        processCardScan(tagidStr, uid, uidLength);
    }
}
