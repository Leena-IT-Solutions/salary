/*
 * Salary Manager - NodeMCU ESP8266 Biometric & RFID Attendance Terminal
 *
 * Hardware Pinout:
 * - SCL -> NodeMCU D1 (GPIO5)
 * - SDA -> NodeMCU D2 (GPIO4)
 * - Buzzer (+) -> NodeMCU D7 (GPIO13)
 * - Tactile Switch -> NodeMCU D4 (GPIO2)
 */

#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
#include <WiFiClientSecure.h>
#include <WiFiClient.h>
#include <Wire.h>
#include <LittleFS.h>
#include <ArduinoJson.h>
#include <time.h>

#include "config.h"
#include "audio.h"
#include "display.h"
#include "rfid.h"
#include "storage.h"
#include "wifi_manager.h"
#include "web_portal.h"
#include "udp_config.h"

Config currentConfig;

unsigned long buttonPressStart = 0;
unsigned long lastSyncCheck = 0;
unsigned long lastDisplayUpdate = 0;
unsigned long lastRfidScanTime = 0;
unsigned long nonReadModeStartTime = 0;

bool lastCardPresent = false;
String lastProcessedTagId = "";

struct RecentScan {
    String tagms;
    String tagid;
    String timeStr;
    String status;
};

static RecentScan recentScans[5];
static int recentScanHead = 0;

static void recordRecentScan(const String &tagms, const String &tagid, const String &timeStr, const String &status) {
    recentScans[recentScanHead].tagms = tagms;
    recentScans[recentScanHead].tagid = tagid;
    recentScans[recentScanHead].timeStr = timeStr;
    recentScans[recentScanHead].status = status;
    recentScanHead = (recentScanHead + 1) % 5;
}



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

void renderScreen(String customMsg = "", String cardMsg = "", String forcedBigText = "") {
    String compName = String(currentConfig.company_name);
    if (compName.length() == 0) compName = DEFAULT_COMPANY_NAME;

    String clockStr = "--:--:--";
    time_t now = time(nullptr);
    if (now > 100000) {
        struct tm *timeinfo = localtime(&now);
        char timeStr[16];
        strftime(timeStr, sizeof(timeStr), "%H:%M:%S", timeinfo);
        clockStr = String(timeStr);
    }
    if (forcedBigText.length() > 0) clockStr = forcedBigText;

    String statusLine;
    if (wifiIsAPMode()) {
        statusLine = "192.168.4.1";
    } else if (WiFi.status() == WL_CONNECTED) {
        statusLine = WiFi.localIP().toString();
    } else {
        statusLine = "No Wi-Fi";
    }

    displayShowScreen(compName, cardMsg, customMsg, clockStr, statusLine,
                       getModeChar(currentConfig.op_mode));
}

void onConfigSaved() {
    audioSetMute(currentConfig.buzzer_enabled == 0);
    renderScreen();
    beepSuccess();
}

static String urlEncode(const String &str) {
    String encoded = "";
    for (size_t i = 0; i < str.length(); i++) {
        char c = str.charAt(i);
        if (isalnum(c) || c == '-' || c == '_' || c == '.' || c == '~') {
            encoded += c;
        } else if (c == ' ') {
            encoded += "%20";
        } else {
            char buf[4];
            sprintf(buf, "%%%02X", (unsigned char)c);
            encoded += buf;
        }
    }
    return encoded;
}

void processCardScan(String tagidStr, uint8_t *uid, uint8_t uidLength) {
    beepScan();

    char blockText[RFID_MESSAGE_MAX_LEN + 1];
    String tagmsStr = String(currentConfig.device_code);
    if (rfidReadMessage(uid, uidLength, blockText, sizeof(blockText))) {
        tagmsStr = String(blockText);
    }

    Serial.printf("[CARD SCAN] UID: %s | tagms: %s\n", tagidStr.c_str(), tagmsStr.c_str());

    if (currentConfig.op_mode == MODE_READ) {
        renderScreen("", "", tagmsStr);
    } else {
        renderScreen("Tag: " + tagidStr, "MSG: " + tagmsStr);
    }

    switch (currentConfig.op_mode) {
        case MODE_READ: {
            time_t now = time(nullptr);
            if (now <= 100000) {
                beepError();
                break;
            }

            struct tm *timeinfo = localtime(&now);
            char dateBuf[16];
            char timeBuf[16];
            strftime(dateBuf, sizeof(dateBuf), "%Y-%m-%d", timeinfo);
            strftime(timeBuf, sizeof(timeBuf), "%H:%M:%S", timeinfo);

            if (WiFi.status() == WL_CONNECTED) {
                String url = String(currentConfig.host_uri);
                url += (url.indexOf('?') >= 0 ? "&" : "?");
                url += "tagms=" + urlEncode(tagmsStr) + "&tagid=" + urlEncode(tagidStr) + "&dt=" + urlEncode(String(dateBuf)) + "&tim=" + urlEncode(String(timeBuf));

                Serial.printf("[API REQUEST] Sending: %s\n", url.c_str());

                bool isHttps = url.startsWith("https");
                HTTPClient http;
                http.setTimeout(5000);

                int httpCode = 0;
                String payload = "";

                if (isHttps) {
                    WiFiClientSecure *clientSec = new WiFiClientSecure();
                    clientSec->setInsecure();
                    clientSec->setBufferSizes(2048, 1024);
                    clientSec->setTimeout(8000);
                    http.begin(*clientSec, url);
                    if (strlen(currentConfig.api_token) > 0) {
                        http.addHeader("Authorization", "Bearer " + String(currentConfig.api_token));
                    }
                    httpCode = http.GET();
                    if (httpCode > 0) payload = http.getString();
                    http.end();
                    delete clientSec;
                } else {
                    WiFiClient *clientPln = new WiFiClient();
                    clientPln->setTimeout(5000);
                    http.begin(*clientPln, url);
                    if (strlen(currentConfig.api_token) > 0) {
                        http.addHeader("Authorization", "Bearer " + String(currentConfig.api_token));
                    }
                    httpCode = http.GET();
                    if (httpCode > 0) payload = http.getString();
                    http.end();
                    delete clientPln;
                }

                Serial.printf("[API RESPONSE] HTTP Code: %d\n", httpCode);

                if (httpCode == 200 || httpCode == 201) {
                    Serial.printf("[API RESPONSE] Payload: %s\n", payload.c_str());
                    JsonDocument doc;
                    DeserializationError jsonErr = deserializeJson(doc, payload);

                    String msg = "Success";
                    if (!jsonErr) {
                        const char *m = doc["message"] | "";
                        if (strlen(m) > 0) msg = String(m);
                    }

                    recordRecentScan(tagmsStr, tagidStr, String(timeBuf), msg);

                    if (msg == "Success") {
                        beepSuccess();
                    } else if (msg == "Already Exists") {
                        beepAlreadyExists();
                    } else {
                        beepError();
                    }
                } else {
                    Serial.printf("[API ERROR] Request failed (Code %d). Queueing offline.\n", httpCode);
                    recordRecentScan(tagmsStr, tagidStr, String(timeBuf), "Queued");
                    storageSaveOfflinePunch(tagmsStr, tagidStr, String(dateBuf), String(timeBuf));
                    beep(100, 2, 2200);
                }
            } else {
                Serial.println("[API ERROR] Wi-Fi Disconnected. Queueing offline.");
                recordRecentScan(tagmsStr, tagidStr, String(timeBuf), "Queued");
                storageSaveOfflinePunch(tagmsStr, tagidStr, String(dateBuf), String(timeBuf));
                beep(100, 2, 2200);
            }
            break;
        }

        case MODE_WRITE: {
            if (strlen(currentConfig.card_value) == 0) {
                renderScreen("Write Failed", "No value set!");
                beepError();
                webPortalSetWriteResult(false);
                break;
            }

            renderScreen("Writing Card...", String(currentConfig.card_value));

            bool ok = rfidWriteMessage(uid, uidLength, currentConfig.card_value);
            if (ok) {
                renderScreen("Card Written OK!", String(currentConfig.card_value));
                beepSuccess();
                webPortalSetWriteResult(true);
                currentConfig.op_mode = MODE_READ;
                storageSaveConfig(currentConfig);
            } else {
                renderScreen("Write Failed", "Try again");
                beepError();
                webPortalSetWriteResult(false);
            }
            break;
        }

        case MODE_FORMAT: {
            renderScreen("Formatting...");
            bool ok = rfidClearMessage(uid, uidLength);
            if (ok) {
                renderScreen("Format OK!", "Card Cleared");
                beepSuccess();
                currentConfig.op_mode = MODE_READ;
                storageSaveConfig(currentConfig);
            } else {
                renderScreen("Format Failed", "Try again");
                beepError();
            }
            break;
        }

        case MODE_DELETE: {
            renderScreen("Deleting Data...");
            bool ok = rfidClearMessage(uid, uidLength);
            if (ok) {
                renderScreen("Data Cleared!", "Card Blank");
                beepSuccess();
                currentConfig.op_mode = MODE_READ;
                storageSaveConfig(currentConfig);
            } else {
                renderScreen("Delete Failed", "Try again");
                beepError();
            }
            break;
        }

        case MODE_SETUP:
        default: {
            renderScreen("Setup Mode", "Tag: " + tagidStr);
            rfidDiagnoseCard(uid, uidLength);
            beepSuccess();
            break;
        }
    }
}

void setup() {
    Serial.begin(115200);
    delay(500);

    Serial.println("\n==========================================");
    Serial.println("NodeMCU Attendance Terminal Starting...");
    Serial.println("==========================================");

    audioInit();
    beepPowerOn();

    storageLoadConfig(currentConfig);
    audioSetMute(currentConfig.buzzer_enabled == 0);

    Wire.begin(OLED_SDA_PIN, OLED_SCL_PIN);
    Wire.setClock(100000);

    Serial.println("[I2C SCANNER] Scanning for active I2C devices...");
    int found = 0;
    for (uint8_t addr = 1; addr < 127; addr++) {
        Wire.beginTransmission(addr);
        if (Wire.endTransmission() == 0) {
            Serial.printf(" -> Found I2C Device at Address 0x%02X\n", addr);
            found++;
        }
    }
    Serial.printf(" -> Total %d I2C device(s) found.\n", found);

    displayInit();
    renderScreen("Initialising...");
    delay(200);

    if (!rfidInit()) {
        Serial.println("[PN532] PN532 Board Not Responding!");
        renderScreen("PN532 Offline");
    } else {
        Serial.println("[PN532] Found and initialized.");
    }

    webPortalInit(&currentConfig, onConfigSaved);
    wifiConnect(currentConfig);
    beepReady();
}

void cycleOperationMode() {
    switch (currentConfig.op_mode) {
        case MODE_READ:  currentConfig.op_mode = MODE_WRITE; break;
        case MODE_WRITE: currentConfig.op_mode = MODE_SETUP; break;
        default:         currentConfig.op_mode = MODE_READ;  break;
    }
    storageSaveConfig(currentConfig);
    beep(80, 1, 2600);
    renderScreen();
}

void loop() {
    webPortalHandleClient();
    wifiLoop(currentConfig);
    udpConfigLoop();
    yield();

    // 15-Minute Inactivity Safeguard: Auto Revert to Read Mode (Normal Attendance)
    if (currentConfig.op_mode != MODE_READ) {
        if (nonReadModeStartTime == 0) nonReadModeStartTime = millis();
        if (millis() - nonReadModeStartTime >= 900000) { // 15 minutes = 900,000 ms
            currentConfig.op_mode = MODE_READ;
            storageSaveConfig(currentConfig);
            renderScreen();
            nonReadModeStartTime = 0;
            Serial.println("[AUTO MODE] 15-minute inactivity timeout reached -> Auto Reverted to Read Mode (Normal Attendance)");
        }
    } else {
        nonReadModeStartTime = 0;
    }

    static bool longPressHandled = false;
    static bool lastButtonState = HIGH;
    bool currentButtonState = digitalRead(BUTTON_PIN);
    if (currentButtonState != lastButtonState) {
        lastButtonState = currentButtonState;
    }
    if (currentButtonState == LOW) {
        if (buttonPressStart == 0) {
            buttonPressStart = millis();
            longPressHandled = false;
        }
        if (!longPressHandled && millis() - buttonPressStart >= 10000) {
            longPressHandled = true;
            beep(200, 3, 2000);
            renderScreen("Factory Reset");
            storageResetConfig(currentConfig);
            wifiStartAPMode(currentConfig);
        }
    } else {
        if (buttonPressStart != 0 && !longPressHandled) {
            cycleOperationMode();
        }
        buttonPressStart = 0;
        longPressHandled = false;
    }

    if (millis() - lastSyncCheck > 30000) {
        lastSyncCheck = millis();
        storageSyncOfflinePunches(String(currentConfig.host_uri), String(currentConfig.api_token));
    }

    if (millis() - lastDisplayUpdate > 1000) {
        lastDisplayUpdate = millis();
        renderScreen();
    }

    if (millis() - lastRfidScanTime > 50) {
        lastRfidScanTime = millis();

        uint8_t uid[7];
        uint8_t uidLength = 0;

        if (rfidPoll(uid, &uidLength, 50)) {
            String currentUid = "";
            for (uint8_t i = 0; i < uidLength; i++) {
                if (i > 0) currentUid += " ";
                if (uid[i] < 0x10) currentUid += "0";
                currentUid += String(uid[i], HEX);
            }
            currentUid.toUpperCase();

            if (!lastCardPresent || currentUid != lastProcessedTagId) {
                lastCardPresent = true;
                lastProcessedTagId = currentUid;
                processCardScan(currentUid, uid, uidLength);
            }
        } else {
            lastCardPresent = false;
            lastProcessedTagId = "";
        }
    }
}
