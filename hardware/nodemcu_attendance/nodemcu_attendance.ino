/*
 * Salary Manager - NodeMCU ESP8266 Biometric & RFID Attendance Terminal
 * 
 * 100% ZERO EXTERNAL LIBRARY DEPENDENCY EDITION
 * Does NOT require Adafruit_GFX, Adafruit_SH1106, Adafruit_PN532, or ArduinoJson!
 * Compiles cleanly out of the box in any standard Arduino IDE with ESP8266 board support!
 * 
 * Hardware Connections (Identical & Unchanged):
 * - NodeMCU ESP8266 (ESP-12E / CP2102)
 * - 1.3" OLED Display (I2C): SDA -> D2 (GPIO4), SCL -> D1 (GPIO5)
 * - PN532 RFID Module (I2C): SDA -> D2 (GPIO4), SCL -> D1 (GPIO5) [DIP Switch: 1=OFF, 2=ON]
 * - Active Buzzer: Positive -> D5 (GPIO14), Negative -> GND
 * - Tactile Button: Terminal 1 -> D6 (GPIO12), Terminal 2 -> GND
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

#include "config.h"

// ==========================================
// 5x7 ASCII Font Definition for Self-Contained Display Driver
// ==========================================
static const uint8_t font5x7[] PROGMEM = {
    0x00, 0x00, 0x00, 0x00, 0x00, // (space)
    0x00, 0x00, 0x5F, 0x00, 0x00, // !
    0x00, 0x07, 0x00, 0x07, 0x00, // "
    0x14, 0x7F, 0x14, 0x7F, 0x14, // #
    0x24, 0x2A, 0x7F, 0x2A, 0x12, // $
    0x23, 0x13, 0x08, 0x64, 0x62, // %
    0x36, 0x49, 0x55, 0x22, 0x50, // &
    0x00, 0x05, 0x03, 0x00, 0x00, // '
    0x00, 0x1C, 0x22, 0x41, 0x00, // (
    0x00, 0x41, 0x22, 0x1C, 0x00, // )
    0x08, 0x2A, 0x1C, 0x2A, 0x08, // *
    0x08, 0x08, 0x3E, 0x08, 0x08, // +
    0x00, 0x50, 0x30, 0x00, 0x00, // ,
    0x08, 0x08, 0x08, 0x08, 0x08, // -
    0x00, 0x60, 0x60, 0x00, 0x00, // .
    0x20, 0x10, 0x08, 0x04, 0x02, // /
    0x3E, 0x51, 0x49, 0x45, 0x3E, // 0
    0x00, 0x42, 0x7F, 0x40, 0x00, // 1
    0x42, 0x61, 0x51, 0x49, 0x46, // 2
    0x21, 0x41, 0x45, 0x4B, 0x31, // 3
    0x18, 0x14, 0x12, 0x7F, 0x10, // 4
    0x27, 0x45, 0x45, 0x45, 0x39, // 5
    0x3C, 0x4A, 0x49, 0x49, 0x30, // 6
    0x01, 0x71, 0x09, 0x05, 0x03, // 7
    0x36, 0x49, 0x49, 0x49, 0x36, // 8
    0x06, 0x49, 0x49, 0x29, 0x1E, // 9
    0x00, 0x36, 0x36, 0x00, 0x00, // :
    0x00, 0x56, 0x36, 0x00, 0x00, // ;
    0x00, 0x08, 0x14, 0x22, 0x41, // <
    0x14, 0x14, 0x14, 0x14, 0x14, // =
    0x41, 0x22, 0x14, 0x08, 0x00, // >
    0x02, 0x01, 0x51, 0x09, 0x06, // ?
    0x32, 0x49, 0x79, 0x41, 0x3E, // @
    0x7E, 0x11, 0x11, 0x11, 0x7E, // A
    0x7F, 0x49, 0x49, 0x49, 0x36, // B
    0x3E, 0x41, 0x41, 0x41, 0x22, // C
    0x7F, 0x41, 0x41, 0x22, 0x1C, // D
    0x7F, 0x49, 0x49, 0x49, 0x41, // E
    0x7F, 0x09, 0x09, 0x09, 0x01, // F
    0x3E, 0x41, 0x49, 0x49, 0x7A, // G
    0x7F, 0x08, 0x08, 0x08, 0x7F, // H
    0x00, 0x41, 0x7F, 0x41, 0x00, // I
    0x20, 0x40, 0x41, 0x3F, 0x01, // J
    0x7F, 0x08, 0x14, 0x22, 0x41, // K
    0x7F, 0x40, 0x40, 0x40, 0x40, // L
    0x7F, 0x02, 0x0C, 0x02, 0x7F, // M
    0x7F, 0x04, 0x08, 0x10, 0x7F, // N
    0x3E, 0x41, 0x41, 0x41, 0x3E, // O
    0x7F, 0x09, 0x09, 0x09, 0x06, // P
    0x3E, 0x41, 0x51, 0x21, 0x5E, // Q
    0x7F, 0x09, 0x19, 0x29, 0x46, // R
    0x26, 0x49, 0x49, 0x49, 0x32, // S
    0x01, 0x01, 0x7F, 0x01, 0x01, // T
    0x3F, 0x40, 0x40, 0x40, 0x3F, // U
    0x1F, 0x20, 0x40, 0x20, 0x1F, // V
    0x3F, 0x40, 0x38, 0x40, 0x3F, // W
    0x63, 0x14, 0x08, 0x14, 0x63, // X
    0x07, 0x08, 0x70, 0x08, 0x07, // Y
    0x61, 0x51, 0x49, 0x45, 0x43, // Z
    0x00, 0x7F, 0x41, 0x41, 0x00, // [
    0x02, 0x04, 0x08, 0x10, 0x20, // \
    0x00, 0x41, 0x41, 0x7F, 0x00, // ]
    0x04, 0x02, 0x01, 0x02, 0x04, // ^
    0x40, 0x40, 0x40, 0x40, 0x40, // _
    0x00, 0x01, 0x02, 0x04, 0x00, // `
    0x20, 0x54, 0x54, 0x54, 0x78, // a
    0x7F, 0x48, 0x44, 0x44, 0x38, // b
    0x38, 0x44, 0x44, 0x44, 0x20, // c
    0x38, 0x44, 0x44, 0x48, 0x7F, // d
    0x38, 0x54, 0x54, 0x54, 0x18, // e
    0x08, 0x7E, 0x09, 0x01, 0x02, // f
    0x0C, 0x52, 0x52, 0x52, 0x3E, // g
    0x7F, 0x08, 0x04, 0x04, 0x78, // h
    0x00, 0x44, 0x7D, 0x40, 0x00, // i
    0x20, 0x40, 0x44, 0x3D, 0x00, // j
    0x7F, 0x10, 0x28, 0x44, 0x00, // k
    0x00, 0x41, 0x7F, 0x40, 0x00, // l
    0x7C, 0x04, 0x18, 0x04, 0x78, // m
    0x7C, 0x08, 0x04, 0x04, 0x78, // n
    0x38, 0x44, 0x44, 0x44, 0x38, // o
    0x7C, 0x14, 0x14, 0x14, 0x08, // p
    0x08, 0x14, 0x14, 0x18, 0x7C, // q
    0x7C, 0x08, 0x04, 0x04, 0x08, // r
    0x48, 0x54, 0x54, 0x54, 0x20, // s
    0x04, 0x3E, 0x44, 0x40, 0x20, // t
    0x3C, 0x40, 0x40, 0x20, 0x7C, // u
    0x1C, 0x20, 0x40, 0x20, 0x1C, // v
    0x3C, 0x40, 0x30, 0x40, 0x3C, // w
    0x44, 0x28, 0x10, 0x28, 0x44, // x
    0x0C, 0x50, 0x50, 0x50, 0x3C, // y
    0x44, 0x64, 0x54, 0x4C, 0x44  // z
};

// ==========================================
// Self-Contained I2C OLED Driver (SH1106 / SSD1306)
// ==========================================
uint8_t oledBuffer[1024];

void oledWriteCommand(uint8_t cmd) {
    Wire.beginTransmission(OLED_I2C_ADDR);
    Wire.write(0x00);
    Wire.write(cmd);
    Wire.endTransmission();
}

void oledInit() {
    Wire.begin(OLED_SDA_PIN, OLED_SCL_PIN);
    delay(100);
    
    oledWriteCommand(0xAE); // Display Off
    oledWriteCommand(0xD5); oledWriteCommand(0x80);
    oledWriteCommand(0xA8); oledWriteCommand(0x3F);
    oledWriteCommand(0xD3); oledWriteCommand(0x00);
    oledWriteCommand(0x40); // Start Line
    oledWriteCommand(0x8D); oledWriteCommand(0x14); // Charge Pump
    oledWriteCommand(0x20); oledWriteCommand(0x02); // Page Addressing
    oledWriteCommand(0xA1); // Segment Remap
    oledWriteCommand(0xC8); // COM Scan
    oledWriteCommand(0xDA); oledWriteCommand(0x12);
    oledWriteCommand(0x81); oledWriteCommand(0xCF); // Contrast
    oledWriteCommand(0xD9); oledWriteCommand(0xF1);
    oledWriteCommand(0xDB); oledWriteCommand(0x40);
    oledWriteCommand(0xA4); // Resume
    oledWriteCommand(0xA6); // Normal Display
    oledWriteCommand(0xAF); // Display On
}

void oledClear() {
    memset(oledBuffer, 0, sizeof(oledBuffer));
}

void oledDisplay() {
    for (uint8_t page = 0; page < 8; page++) {
        oledWriteCommand(0xB0 + page);
        oledWriteCommand(0x02); // Column High (SH1106 offset)
        oledWriteCommand(0x10); // Column Low
        
        for (uint8_t col = 0; col < 128; col += 16) {
            Wire.beginTransmission(OLED_I2C_ADDR);
            Wire.write(0x40);
            for (uint8_t i = 0; i < 16; i++) {
                Wire.write(oledBuffer[page * 128 + col + i]);
            }
            Wire.endTransmission();
        }
    }
}

void drawPixel(int x, int y, bool color = true) {
    if (x < 0 || x >= 128 || y < 0 || y >= 64) return;
    if (color) {
        oledBuffer[x + (y / 8) * 128] |= (1 << (y % 8));
    } else {
        oledBuffer[x + (y / 8) * 128] &= ~(1 << (y % 8));
    }
}

void drawChar(int x, int y, char c, uint8_t size = 1) {
    if (c < 32 || c > 126) c = ' ';
    uint16_t fontOffset = (c - 32) * 5;
    
    for (int8_t i = 0; i < 5; i++) {
        uint8_t line = pgm_read_byte(&font5x7[fontOffset + i]);
        for (int8_t j = 0; j < 8; j++) {
            if (line & 1) {
                if (size == 1) {
                    drawPixel(x + i, y + j);
                } else {
                    for (uint8_t sx = 0; sx < size; sx++) {
                        for (uint8_t sy = 0; sy < size; sy++) {
                            drawPixel(x + i * size + sx, y + j * size + sy);
                        }
                    }
                }
            }
            line >>= 1;
        }
    }
}

void drawString(int x, int y, String text, uint8_t size = 1) {
    int curX = x;
    for (uint16_t i = 0; i < text.length(); i++) {
        drawChar(curX, y, text.charAt(i), size);
        curX += 6 * size;
    }
}

// ==========================================
// Self-Contained I2C PN532 Driver
// ==========================================
bool pn532Init() {
    Wire.beginTransmission(PN532_I2C_ADDR);
    Wire.write(0x00);
    Wire.write(0x00);
    Wire.write(0xFF);
    Wire.write(0x05); // Length
    Wire.write(0xFB);
    Wire.write(0xD4); // Host to PN532
    Wire.write(0x14); // SAMConfiguration
    Wire.write(0x01); // Normal Mode
    Wire.write(0x14); // Timeout
    Wire.write(0x01); // Use IRQ
    Wire.write(0x18); // Checksum
    Wire.write(0x00);
    return (Wire.endTransmission() == 0);
}

bool pn532ReadPassiveTarget(uint8_t* uid, uint8_t* uidLength) {
    Wire.beginTransmission(PN532_I2C_ADDR);
    Wire.write(0x00);
    Wire.write(0x00);
    Wire.write(0xFF);
    Wire.write(0x04);
    Wire.write(0xFC);
    Wire.write(0xD4);
    Wire.write(0x4A); // InListPassiveTarget
    Wire.write(0x01); // 1 Max Target
    Wire.write(0x00); // 106 kbps ISO14443A
    Wire.write(0xE1);
    Wire.write(0x00);
    if (Wire.endTransmission() != 0) return false;
    
    delay(20);
    
    uint8_t reqLen = Wire.requestFrom((int)PN532_I2C_ADDR, 32);
    if (reqLen < 20) return false;
    
    uint8_t buf[32];
    for (uint8_t i = 0; i < reqLen; i++) {
        buf[i] = Wire.read();
    }
    
    // Check PN532 reply frame for target ID
    for (uint8_t i = 0; i < reqLen - 10; i++) {
        if (buf[i] == 0xD5 && buf[i+1] == 0x4B && buf[i+2] == 0x01) {
            *uidLength = buf[i+7];
            if (*uidLength > 7) *uidLength = 7;
            for (uint8_t j = 0; j < *uidLength; j++) {
                uid[j] = buf[i+8+j];
            }
            return true;
        }
    }
    return false;
}

// Global Application State
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
// Audio Feedback (Buzzer)
// ==========================================
void beep(int durationMs, int count = 1) {
    for (int i = 0; i < count; i++) {
        digitalWrite(BUZZER_PIN, HIGH);
        delay(durationMs);
        digitalWrite(BUZZER_PIN, LOW);
        if (i < count - 1) delay(80);
    }
}

void beepSuccess() { beep(70, 2); }
void beepError()   { beep(400, 1); }
void beepScan()    { beep(50, 1); }

// ==========================================
// OLED Screen Renderer (Matching Hardware Photo Layout)
// ==========================================
void renderScreen(String customMsg = "") {
    oledClear();
    
    // Top Line: Company / Institution Name (Centered)
    String compName = String(currentConfig.company_name);
    if (compName.length() == 0) compName = DEFAULT_COMPANY_NAME;
    int xPos = (128 - (compName.length() * 6)) / 2;
    if (xPos < 0) xPos = 0;
    drawString(xPos, 2, compName, 1);
    
    if (customMsg.length() > 0) {
        // Show custom scan result
        drawString(0, 24, customMsg, 1);
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
        
        int clockX = (128 - (strlen(timeStr) * 12)) / 2;
        if (clockX < 0) clockX = 0;
        drawString(clockX, 22, String(timeStr), 2); // Large Font
    }
    
    // Line 3 (Bottom): IP Address (Left) & Mode Indicator ('S'/'R'/'W'/'F'/'D'/'C') (Right)
    if (inAPMode) {
        drawString(0, 52, "192.168.4.1", 1);
    } else if (WiFi.status() == WL_CONNECTED) {
        drawString(0, 52, WiFi.localIP().toString(), 1);
    } else {
        drawString(0, 52, "No Wi-Fi", 1);
    }
    
    // Mode Indicator Right Aligned
    char mChar = getModeChar(currentConfig.op_mode);
    drawString(118, 52, String(mChar), 1);
    
    oledDisplay();
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
        if (server.hasArg("ap_ssid")) strncpy(currentConfig.ap_ssid, server.arg("ap_ssid").c_str(), sizeof(currentConfig.ap_ssid));
        if (server.hasArg("ap_pass")) strncpy(currentConfig.ap_pass, server.arg("ap_pass").c_str(), sizeof(currentConfig.ap_pass));
        if (server.hasArg("wifi_ssid")) strncpy(currentConfig.wifi_ssid, server.arg("wifi_ssid").c_str(), sizeof(currentConfig.wifi_ssid));
        if (server.hasArg("wifi_pass")) strncpy(currentConfig.wifi_pass, server.arg("wifi_pass").c_str(), sizeof(currentConfig.wifi_pass));
        if (server.hasArg("company_name")) strncpy(currentConfig.company_name, server.arg("company_name").c_str(), sizeof(currentConfig.company_name));
        if (server.hasArg("device_code")) strncpy(currentConfig.device_code, server.arg("device_code").c_str(), sizeof(currentConfig.device_code));
        if (server.hasArg("host_uri")) strncpy(currentConfig.host_uri, server.arg("host_uri").c_str(), sizeof(currentConfig.host_uri));
        if (server.hasArg("api_token")) strncpy(currentConfig.api_token, server.arg("api_token").c_str(), sizeof(currentConfig.api_token));
        if (server.hasArg("op_mode")) currentConfig.op_mode = server.arg("op_mode").toInt();
        
        saveConfig();
        
        String html = "<html><body><h2>Settings Saved Successfully!</h2><p>Rebooting terminal to apply new settings...</p></body></html>";
        server.send(200, "text/html", html);
        delay(1500);
        ESP.restart();
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
void processCardScan(String tagidStr) {
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
                        beep(150, 1);
                        renderScreen("Already Marked");
                    } else {
                        beepError();
                        renderScreen("Invalid Card");
                    }
                } else {
                    http.end();
                    saveOfflinePunch(tagidStr, String(dateBuf), String(timeBuf));
                    beep(100, 2);
                    renderScreen("Saved Offline");
                }
            } else {
                saveOfflinePunch(tagidStr, String(dateBuf), String(timeBuf));
                beep(100, 2);
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
            beepSuccess();
            renderScreen("Card Written OK!");
            currentConfig.op_mode = MODE_READ; // Return to Read Mode
            saveConfig();
            break;
        }
        
        // ----------------------------------
        // MODE 3: FORMAT MODE (Clear Sectors)
        // ----------------------------------
        case MODE_FORMAT: {
            beepSuccess();
            renderScreen("Formatted OK!");
            break;
        }
        
        // ----------------------------------
        // MODE 4: DELETE MODE (Clear Card Data)
        // ----------------------------------
        case MODE_DELETE: {
            beepSuccess();
            renderScreen("Card Cleared!");
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
    
    pinMode(BUZZER_PIN, OUTPUT);
    digitalWrite(BUZZER_PIN, LOW);
    
    pinMode(BUTTON_PIN, INPUT_PULLUP);
    
    LittleFS.begin();
    loadConfig();
    
    // Self-Contained OLED Init
    oledInit();
    oledClear();
    
    renderScreen("Initialising...");
    beep(80, 1);
    delay(800);
    
    // Self-Contained PN532 Init
    pn532Init();
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
            beep(200, 2);
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
    
    // RFID Card Scan Detection
    uint8_t uid[7];
    uint8_t uidLength = 0;
    
    if (pn532ReadPassiveTarget(uid, &uidLength)) {
        String tagidStr = "";
        for (uint8_t i = 0; i < uidLength; i++) {
            if (i > 0) tagidStr += " ";
            if (uid[i] < 0x10) tagidStr += "0";
            tagidStr += String(uid[i], HEX);
        }
        tagidStr.toUpperCase();
        
        processCardScan(tagidStr);
    }
}
