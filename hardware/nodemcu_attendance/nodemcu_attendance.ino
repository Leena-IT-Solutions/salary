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
    String timestamp;
    String status;
};

#define MAX_RECENT_SCANS 10
RecentScan recentScans[MAX_RECENT_SCANS];
int recentScanCount = 0;

void recordRecentScan(const String &tagms, const String &tagid,
                      const String &timeStr, const String &status) {
    if (recentScanCount < MAX_RECENT_SCANS) {
        recentScans[recentScanCount] = {tagms, tagid, timeStr, status};
        recentScanCount++;
    } else {
        for (int i = 0; i < MAX_RECENT_SCANS - 1; i++) {
            recentScans[i] = recentScans[i + 1];
        }
        recentScans[MAX_RECENT_SCANS - 1] = {tagms, tagid, timeStr, status};
    }
}

static char getModeChar(uint8_t opMode) {
    switch (opMode) {
        case MODE_SETUP:  return 'S';
        case MODE_READ:   return 'R';
        case MODE_WRITE:  return 'W';
        case MODE_FORMAT: return 'F';
        case MODE_DELETE: return 'D';
        case MODE_CLEAR:  return 'C';
        default:          return 'R';
    }
}

void renderScreen(const String &cardMsg = "", const String &customMsg = "") {
    String compName = String(currentConfig.company_name);
    if (compName.length() == 0) compName = DEFAULT_COMPANY_NAME;

    time_t now = time(nullptr);
    struct tm *timeinfo = localtime(&now);
    char clockStr[32] = "";
    if (now > 100000) {
        strftime(clockStr, sizeof(clockStr), "%d/%m/%Y %H:%M:%S", timeinfo);
    } else {
        snprintf(clockStr, sizeof(clockStr), "System Starting...");
    }

    String statusLine = "";
    if (wifiIsAPMode()) {
        statusLine = "AP: 192.168.4.1";
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
        String decoded = String(blockText);
        decoded.trim();
        if (decoded.length() > 0) {
            tagmsStr = decoded;
        }
    }

    LOG_PRINTF("[CARD SCAN] UID: %s | tagms: %s\n", tagidStr.c_str(), tagmsStr.c_str());

    renderScreen("Card Scanned", "ID: " + tagmsStr);

    switch (currentConfig.op_mode) {
        case MODE_READ: {
            time_t now = time(nullptr);
            char dateBuf[16] = "1970-01-01";
            char timeBuf[16] = "00:00:00";
            if (now > 100000) {
                struct tm *timeinfo = localtime(&now);
                strftime(dateBuf, sizeof(dateBuf), "%Y-%m-%d", timeinfo);
                strftime(timeBuf, sizeof(timeBuf), "%H:%M:%S", timeinfo);
            } else {
                snprintf(timeBuf, sizeof(timeBuf), "UP-%lu", millis() / 1000);
            }

            if (WiFi.status() == WL_CONNECTED) {
                String url = String(currentConfig.host_uri);
                url += (url.indexOf('?') >= 0 ? "&" : "?");
                url += "tagms=" + urlEncode(tagmsStr) + "&tagid=" + urlEncode(tagidStr) + "&dt=" + urlEncode(String(dateBuf)) + "&tim=" + urlEncode(String(timeBuf));

                LOG_PRINTF("[API REQUEST] Sending: %s\n", url.c_str());

                bool isHttps = url.startsWith("https");
                HTTPClient http;
                http.setTimeout(5000);

                int httpCode = 0;
                String payload = "";

                if (isHttps) {
                    WiFiClientSecure *clientSec = new WiFiClientSecure();
                    clientSec->setInsecure();
                    clientSec->setBufferSizes(1024, 512);
                    clientSec->setTimeout(5000);
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

                LOG_PRINTF("[API RESPONSE] HTTP Code: %d\n", httpCode);

                if (httpCode == 200 || httpCode == 201) {
                    LOG_PRINTF("[API RESPONSE] Payload: %s\n", payload.c_str());
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
                    LOG_PRINTF("[API ERROR] Request failed (Code %d). Queueing offline.\n", httpCode);
                    recordRecentScan(tagmsStr, tagidStr, String(timeBuf), "Queued");
                    storageSaveOfflinePunch(tagmsStr, tagidStr, String(dateBuf), String(timeBuf));
                    beep(100, 2, 2200);
                }
            } else {
                LOG_PRINTLN("[API ERROR] Wi-Fi Disconnected. Queueing offline.");
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
                renderScreen("Write Success!", String(currentConfig.card_value));
                beepSuccess();
                webPortalSetWriteResult(true);
            } else {
                renderScreen("Write Failed!", "Try Again");
                beepError();
                webPortalSetWriteResult(false);
            }
            break;
        }

        case MODE_FORMAT: {
            renderScreen("Formatting Card...", "Please wait");
            bool ok = rfidFormatCard(uid, uidLength);
            if (ok) {
                renderScreen("Format Success!", "Clean Card");
                beepSuccess();
            } else {
                renderScreen("Format Failed!", "Try Again");
                beepError();
            }
            break;
        }

        case MODE_DELETE: {
            renderScreen("Deleting Data...", "Please wait");
            bool ok = rfidDeleteMessage(uid, uidLength);
            if (ok) {
                renderScreen("Delete Success!", "Cleared Block 4");
                beepSuccess();
            } else {
                renderScreen("Delete Failed!", "Try Again");
                beepError();
            }
            break;
        }

        default:
            renderScreen("Card Scanned", tagidStr);
            break;
    }
}

void setup() {
#if ENABLE_SERIAL
    Serial.begin(115200);
    delay(500);
    LOG_PRINTLN("\n==========================================");
    LOG_PRINTLN("NodeMCU Attendance Terminal Starting...");
    LOG_PRINTLN("==========================================");
#endif

    audioInit();
    beepPowerOn();

    if (!LittleFS.begin()) {
        LOG_PRINTLN("[LittleFS] Mount failed! Formatting filesystem...");
        LittleFS.format();
        LittleFS.begin();
    } else {
        LOG_PRINTLN("[LittleFS] Filesystem mounted successfully.");
    }

    storageLoadConfig(currentConfig);
    audioSetMute(currentConfig.buzzer_enabled == 0);

    Wire.begin(OLED_SDA_PIN, OLED_SCL_PIN);
    Wire.setClock(100000);

    LOG_PRINTLN("[I2C SCANNER] Scanning for active I2C devices...");
    int found = 0;
    for (uint8_t addr = 1; addr < 127; addr++) {
        Wire.beginTransmission(addr);
        if (Wire.endTransmission() == 0) {
            LOG_PRINTF(" -> Found I2C Device at Address 0x%02X\n", addr);
            found++;
        }
    }
    LOG_PRINTF(" -> Total %d I2C device(s) found.\n", found);

    displayInit();
    renderScreen("Initialising...");
    delay(200);

    if (!rfidInit()) {
        LOG_PRINTLN("[PN532] PN532 Board Not Responding!");
        renderScreen("PN532 Offline");
    } else {
        LOG_PRINTLN("[PN532] Found and initialized.");
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
    yield();

    // 15-Minute Inactivity Safeguard: Auto Revert to Read Mode (Normal Attendance)
    if (currentConfig.op_mode != MODE_READ) {
        if (nonReadModeStartTime == 0) nonReadModeStartTime = millis();
        if (millis() - nonReadModeStartTime >= 900000) { // 15 minutes = 900,000 ms
            currentConfig.op_mode = MODE_READ;
            storageSaveConfig(currentConfig);
            renderScreen();
            nonReadModeStartTime = 0;
            LOG_PRINTLN("[AUTO MODE] 15-minute inactivity timeout reached -> Auto Reverted to Read Mode (Normal Attendance)");
        }
    } else {
        nonReadModeStartTime = 0;
    }

    // Hardware Push Button Handler (Cycles mode / resets config)
    int btnState = digitalRead(BUTTON_PIN);
    if (btnState == LOW) {
        if (buttonPressStart == 0) buttonPressStart = millis();
        unsigned long held = millis() - buttonPressStart;
        if (held >= 5000) {
            renderScreen("Resetting...", "Factory Reset");
            beep(200, 5, 3000);
            storageResetConfig(currentConfig);
            ESP.restart();
        }
    } else {
        if (buttonPressStart > 0) {
            unsigned long held = millis() - buttonPressStart;
            buttonPressStart = 0;
            if (held > 50 && held < 3000) {
                cycleOperationMode();
            }
        }
    }

    // RFID Card Polling Loop
    uint8_t uid[7];
    uint8_t uidLength = 0;
    if (rfidPoll(uid, &uidLength, 50)) {
        char tagidBuf[20] = "";
        char *ptr = tagidBuf;
        for (uint8_t i = 0; i < uidLength; i++) {
            if (i > 0) ptr += sprintf(ptr, " ");
            ptr += sprintf(ptr, "%02X", uid[i]);
        }
        String tagidStr = String(tagidBuf);

        unsigned long now = millis();
        if (!lastCardPresent || (tagidStr != lastProcessedTagId) || (now - lastRfidScanTime > 3000)) {
            lastCardPresent = true;
            lastProcessedTagId = tagidStr;
            lastRfidScanTime = now;
            processCardScan(tagidStr, uid, uidLength);
        }
    } else {
        lastCardPresent = false;
    }

    // Dynamic Display Update (Clock / Wi-Fi IP refresh every 1 second)
    unsigned long now = millis();
    if (now - lastDisplayUpdate >= 1000) {
        lastDisplayUpdate = now;
        renderScreen();
    }
}
