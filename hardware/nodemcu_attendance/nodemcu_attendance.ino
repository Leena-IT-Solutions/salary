/*
 * Salary Manager - NodeMCU ESP8266 Biometric & RFID Attendance Terminal
 *
 * Hardware Pinout:
 * - SCL -> NodeMCU D1 (GPIO5)
 * - SDA -> NodeMCU D2 (GPIO4)
 * - Buzzer (+) -> NodeMCU D7 (GPIO13)
 * - Tactile Switch -> NodeMCU D4 (GPIO2)
 *
 * See README.md for the full request/response contract this firmware
 * speaks with the SalaryManager backend's /attendance/save endpoint - it
 * must stay unchanged for this device to keep working with that backend.
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

// Tracks whether the card currently in the reader's field has already
// been processed, so a card left resting there doesn't get reprocessed
// on every ~50ms poll - see the RFID scan block in loop().
bool lastCardPresent = false;
String lastProcessedTagId = "";

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

// ==========================================
// Screen Composition
// Figures out the dynamic bits (clock/status/mode) and hands a fully
// formed screen to the display module.
// ==========================================
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

// Fired by the web portal right after a config save, so the OLED/buzzer
// reflect the change immediately (previously inlined into handleSaveWeb).
void onConfigSaved() {
    renderScreen();
    beepSuccess();
}

// ==========================================
// Mode Action Handlers (Setup, Read, Write, Format, Delete, Clear)
// ==========================================
void processCardScan(String tagidStr, uint8_t *uid, uint8_t uidLength) {
    beepScan();

    // Read the card's message (tagms), falling back to the configured
    // device code if it's unreadable/empty.
    char blockText[RFID_MESSAGE_MAX_LEN + 1];
    String tagmsStr = String(currentConfig.device_code);
    if (rfidReadMessage(uid, uidLength, blockText, sizeof(blockText))) {
        tagmsStr = String(blockText);
    }

    Serial.printf("[CARD SCAN] UID: %s | tagms: %s\n", tagidStr.c_str(), tagmsStr.c_str());

    // Read mode (the normal attendance-tap flow) just shows the employee
    // code in the clock's spot for the duration of the hold - success vs.
    // failure is communicated purely through the buzzer tone. The admin
    // modes (Write/Format/Delete/Clear/Setup) keep the more detailed
    // "Tag: <uid>" / "MSG: <tagms>" preview plus their own result screens.
    if (currentConfig.op_mode == MODE_READ) {
        renderScreen("", "", tagmsStr);
    } else {
        renderScreen("Tag: " + tagidStr, "MSG: " + tagmsStr);
    }

    switch (currentConfig.op_mode) {

        // ----------------------------------
        // MODE 1: READ MODE (Default Attendance)
        // ----------------------------------
        case MODE_READ: {
            time_t now = time(nullptr);
            if (now <= 100000) {
                // Clock hasn't synced via NTP yet - refuse to guess a date.
                // Sending a fabricated timestamp would silently mis-record
                // real attendance, so just ask for a retry once synced
                // (NTP normally finishes within seconds of Wi-Fi connect).
                beepError();
                break;
            }

            struct tm *timeinfo = localtime(&now);
            char dateBuf[16];
            char timeBuf[16];
            strftime(dateBuf, sizeof(dateBuf), "%Y-%m-%d", timeinfo);
            strftime(timeBuf, sizeof(timeBuf), "%H:%M", timeinfo);

            if (WiFi.status() == WL_CONNECTED) {
                String url = String(currentConfig.host_uri);
                url += (url.indexOf('?') >= 0 ? "&" : "?");
                url += "tagms=" + tagmsStr + "&tagid=" + tagidStr + "&dt=" + String(dateBuf) + "&tim=" + String(timeBuf);

                Serial.printf("[API REQUEST] Sending: %s\n", url.c_str());

                bool isHttps = url.startsWith("https");
                HTTPClient http;
                http.setTimeout(4000);

                int httpCode = 0;
                String payload = "";

                if (isHttps) {
                    WiFiClientSecure *clientSec = new WiFiClientSecure();
                    clientSec->setInsecure();
                    clientSec->setTimeout(4000);
                    http.begin(*clientSec, url);
                    if (strlen(currentConfig.api_token) > 0) {
                        http.addHeader("Authorization", "Bearer " + String(currentConfig.api_token));
                    }
                    httpCode = http.GET();
                    if (httpCode == 200) payload = http.getString();
                    http.end();
                    delete clientSec;
                } else {
                    WiFiClient *clientPln = new WiFiClient();
                    clientPln->setTimeout(4000);
                    http.begin(*clientPln, url);
                    if (strlen(currentConfig.api_token) > 0) {
                        http.addHeader("Authorization", "Bearer " + String(currentConfig.api_token));
                    }
                    httpCode = http.GET();
                    if (httpCode == 200) payload = http.getString();
                    http.end();
                    delete clientPln;
                }

                if (httpCode == 200) {
                    JsonDocument doc;
                    DeserializationError jsonErr = deserializeJson(doc, payload);

                    String msg = "Success";
                    if (!jsonErr) {
                        const char *m = doc["message"] | "";
                        if (strlen(m) > 0) msg = String(m);
                    }

                    if (msg == "Success") {
                        beepSuccess();
                    } else if (msg == "Already Exists") {
                        beep(150, 1, 2000);
                    } else {
                        beepError();
                    }
                } else {
                    storageSaveOfflinePunch(tagmsStr, tagidStr, String(dateBuf), String(timeBuf));
                    beep(100, 2, 2200);
                }
            } else {
                storageSaveOfflinePunch(tagmsStr, tagidStr, String(dateBuf), String(timeBuf));
                beep(100, 2, 2200);
            }
            break;
        }

        // ----------------------------------
        // MODE 2: WRITE MODE (Card Burning)
        // ----------------------------------
        case MODE_WRITE: {
            if (strlen(currentConfig.card_value) == 0) {
                beepError();
                renderScreen("No Value Set!", "MSG: " + tagmsStr);
                webPortalSetWriteResult(false);
                break;
            }

            if (rfidWriteMessage(uid, uidLength, currentConfig.card_value)) {
                beepSuccess();
                renderScreen("Card Written OK!", "Value: " + String(currentConfig.card_value));
                currentConfig.op_mode = MODE_READ;
                storageSaveConfig(currentConfig);
                webPortalSetWriteResult(true);
            } else {
                beepError();
                renderScreen("Write Failed!", "ID: " + tagidStr);
                webPortalSetWriteResult(false);
            }
            break;
        }

        // ----------------------------------
        // MODE 3: FORMAT MODE (Clear Sectors)
        // ----------------------------------
        case MODE_FORMAT: {
            if (rfidClearMessage(uid, uidLength)) {
                beepSuccess();
                renderScreen("Formatted OK!", "ID: " + tagidStr);
            } else {
                beepError();
                renderScreen("Format Failed!", "ID: " + tagidStr);
            }
            break;
        }

        // ----------------------------------
        // MODE 4: DELETE MODE (Clear Card Data)
        // ----------------------------------
        case MODE_DELETE: {
            if (rfidClearMessage(uid, uidLength)) {
                beepSuccess();
                renderScreen("Card Cleared!", "ID: " + tagidStr);
            } else {
                beepError();
                renderScreen("Delete Failed!", "ID: " + tagidStr);
            }
            break;
        }

        // ----------------------------------
        // MODE 5: CLEAR MODE (Clear Queue)
        // ----------------------------------
        case MODE_CLEAR: {
            storageClearOfflineQueue();
            beepSuccess();
            renderScreen("Queue Cleared!", "ID: " + tagidStr);
            currentConfig.op_mode = MODE_READ;
            storageSaveConfig(currentConfig);
            break;
        }

        // ----------------------------------
        // MODE 0: SETUP MODE (Diagnostic)
        // ----------------------------------
        case MODE_SETUP: {
            beepScan();
            renderScreen("UID: " + tagidStr, "MSG: " + tagmsStr);
            // Check the Serial Monitor for the key/sector this card
            // actually authenticates with - useful for cards written by
            // a different/unknown device (e.g. an old machine whose
            // firmware source is lost).
            rfidDiagnoseCard(uid, uidLength);
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
    delay(200);
    Serial.println("\n\n==========================================");
    Serial.println("NodeMCU Attendance Terminal Starting...");
    Serial.println("==========================================");

    audioInit();

    // 1. Power-On Audio Feedback FIRST!
    beepPowerOn();

    pinMode(BUTTON_PIN, INPUT_PULLUP);

    LittleFS.begin();
    storageLoadConfig(currentConfig);

    // 2. Initialize Hardware Wire I2C
    Wire.begin(OLED_SDA_PIN, OLED_SCL_PIN);
    Wire.setClock(50000);
    delay(50);

    // 3. Clean I2C Scanner (diagnostic only, logged to Serial)
    Serial.println("[I2C SCANNER] Scanning for active I2C devices...");
    uint8_t found = 0;
    for (uint8_t addr = 1; addr < 127; addr++) {
        Wire.beginTransmission(addr);
        if (Wire.endTransmission() == 0) {
            Serial.printf(" -> Found I2C Device at Address 0x%02X\n", addr);
            found++;
        }
    }
    Serial.printf(" -> Total %d I2C device(s) found.\n", found);

    // 4. OLED Init
    displayInit();
    renderScreen("Initialising...");
    delay(200);

    // 5. Initialize PN532
    if (!rfidInit()) {
        Serial.println("[PN532] PN532 Board Not Responding!");
        renderScreen("PN532 Offline");
    } else {
        Serial.println("[PN532] Found and initialized.");
    }

    // 6. Web Portal + Wi-Fi
    webPortalInit(&currentConfig, onConfigSaved);
    wifiConnect(currentConfig);

    // 7. Boot finished - ready to scan.
    beepReady();
}

// Advances through the safe modes only (Read -> Write -> Setup -> Read).
// Format/Delete/Clear stay reachable solely through the authenticated web
// portal, so a stray extra button press can't leave the device armed to
// silently wipe a card on the next tap. Any other current mode (e.g. one
// set via the portal) also falls through to Read on the next press.
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

// ==========================================
// Main Loop
// ==========================================
void loop() {
    webPortalHandleClient();
    wifiLoop(currentConfig);
    yield();

    // Tactile Switch: short press (<10s) cycles the mode; holding >=10s
    // triggers a full factory reset. longPressHandled makes sure the
    // reset fires exactly once per hold and never also triggers a mode
    // cycle when the button is finally released.
    static bool longPressHandled = false;
    static bool lastButtonState = HIGH;
    bool currentButtonState = digitalRead(BUTTON_PIN);
    if (currentButtonState != lastButtonState) {
        Serial.printf("[BUTTON] Pin %d changed to %s\n", BUTTON_PIN,
                      currentButtonState == LOW ? "LOW (pressed)" : "HIGH (released)");
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

    // Periodic Offline Queue Sync (Every 30 seconds)
    if (millis() - lastSyncCheck > 30000) {
        lastSyncCheck = millis();
        storageSyncOfflinePunches(String(currentConfig.host_uri), String(currentConfig.api_token));
    }

    // Periodic Screen Refresh for Digital Clock (Every 1 second)
    if (millis() - lastDisplayUpdate > 1000) {
        lastDisplayUpdate = millis();
        renderScreen();
    }

    // Non-Blocking RFID Card Scan Detection (Throttled to 20Hz / 50ms)
    if (millis() - lastRfidScanTime > 50) {
        lastRfidScanTime = millis();

        uint8_t uid[7];
        uint8_t uidLength = 0;
        bool present = rfidPoll(uid, &uidLength, 20);

        if (present) {
            String tagidStr = "";
            for (uint8_t i = 0; i < uidLength; i++) {
                if (i > 0) tagidStr += " ";
                if (uid[i] < 0x10) tagidStr += "0";
                tagidStr += String(uid[i], HEX);
            }
            tagidStr.toUpperCase();

            // A card left resting on/near the reader is still "present" on
            // every single poll - without this check it would be
            // reprocessed continuously (e.g. re-sending attendance every
            // ~2s, or in Setup mode re-running the ~20s key/sector
            // diagnostic scan over and over). Only process on a genuinely
            // new tap: a different UID, or this UID after having been
            // removed and re-presented.
            if (!lastCardPresent || tagidStr != lastProcessedTagId) {
                processCardScan(tagidStr, uid, uidLength);
                lastProcessedTagId = tagidStr;
            }
            lastCardPresent = true;
        } else {
            lastCardPresent = false;
            lastProcessedTagId = "";
        }
    }
}
