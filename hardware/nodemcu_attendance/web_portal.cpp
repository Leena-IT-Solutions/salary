#include "web_portal.h"
#include "wifi_manager.h"
#include "storage.h"
#include "audio.h"
#include <ESP8266WebServer.h>
#include <ESP8266WiFi.h>
#include <ESP8266HTTPUpdateServer.h>
#include <ArduinoJson.h>

static ESP8266WebServer server(80);
static ESP8266HTTPUpdateServer httpUpdater;
static Config *config = nullptr;
static void (*savedCallback)() = nullptr;

enum WriteStatus { WRITE_IDLE, WRITE_ARMED, WRITE_SUCCESS, WRITE_FAILED };
static WriteStatus writeStatus = WRITE_IDLE;
static String writeStatusValue = "";

void webPortalInit(Config *cfg, void (*onConfigSaved)()) {
    config = cfg;
    savedCallback = onConfigSaved;
}

void webPortalSetWriteResult(bool success) {
    writeStatus = success ? WRITE_SUCCESS : WRITE_FAILED;
}

static bool requireAuth() {
    if (!server.authenticate(config->portal_user, config->portal_pass)) {
        server.requestAuthentication();
        return false;
    }
    return true;
}

// ==========================================
// PURE LIGHTWEIGHT JSON REST API ENDPOINTS
// ==========================================

// GET /api/status - Device Diagnostics & Live Settings
static void handleApiStatus() {
    if (!requireAuth()) return;

    JsonDocument doc;
    doc["status"] = "online";
    doc["ip"] = wifiIsAPMode() ? "192.168.4.1" : WiFi.localIP().toString();
    doc["ap_mode"] = wifiIsAPMode();
    doc["rssi"] = WiFi.RSSI();
    doc["company_name"] = config->company_name;
    doc["host_uri"] = config->host_uri;
    doc["api_token"] = config->api_token;
    doc["op_mode"] = config->op_mode;
    doc["buzzer_enabled"] = config->buzzer_enabled;
    doc["portal_user"] = config->portal_user;
    doc["ap_ssid"] = config->ap_ssid;
    doc["wifi_ssid"] = config->wifi_ssid;

    String response;
    serializeJson(doc, response);
    server.send(200, "application/json", response);
}

// POST /api/config - Update Terminal & Company Settings Live (No Reboot)
static void handleApiSaveConfig() {
    if (!requireAuth()) return;

    bool changed = false;

    if (server.hasArg("company_name")) {
        String newVal = server.arg("company_name");
        if (String(config->company_name) != newVal) {
            strncpy(config->company_name, newVal.c_str(), sizeof(config->company_name));
            changed = true;
        }
    }
    if (server.hasArg("host_uri")) {
        String newVal = server.arg("host_uri");
        if (String(config->host_uri) != newVal) {
            strncpy(config->host_uri, newVal.c_str(), sizeof(config->host_uri));
            changed = true;
        }
    }
    if (server.hasArg("api_token")) {
        String newVal = server.arg("api_token");
        if (String(config->api_token) != newVal) {
            strncpy(config->api_token, newVal.c_str(), sizeof(config->api_token));
            changed = true;
        }
    }
    if (server.hasArg("op_mode")) {
        uint8_t newMode = server.arg("op_mode").toInt();
        if (config->op_mode != newMode) {
            config->op_mode = newMode;
            changed = true;
        }
    }
    if (server.hasArg("buzzer_enabled")) {
        uint8_t newBuzzer = server.arg("buzzer_enabled").toInt();
        if (config->buzzer_enabled != newBuzzer) {
            config->buzzer_enabled = newBuzzer;
            changed = true;
        }
    }

    if (changed) {
        storageSaveConfig(*config);
        if (savedCallback) savedCallback();
    }

    server.send(200, "application/json", changed ? "{\"status\":\"success\",\"message\":\"Settings updated live\"}" : "{\"status\":\"unchanged\",\"message\":\"No changes detected\"}");
}

// POST /api/wifi - Update AP & Wi-Fi Network Credentials Live
static void handleApiSaveWifi() {
    if (!requireAuth()) return;

    bool wifiChanged = false;
    bool apChanged = false;

    if (server.hasArg("ap_ssid")) {
        String newVal = server.arg("ap_ssid");
        if (String(config->ap_ssid) != newVal) {
            strncpy(config->ap_ssid, newVal.c_str(), sizeof(config->ap_ssid));
            apChanged = true;
        }
    }
    if (server.hasArg("ap_pass")) {
        String newVal = server.arg("ap_pass");
        if (String(config->ap_pass) != newVal) {
            strncpy(config->ap_pass, newVal.c_str(), sizeof(config->ap_pass));
            apChanged = true;
        }
    }
    if (server.hasArg("wifi_ssid")) {
        String newVal = server.arg("wifi_ssid");
        if (String(config->wifi_ssid) != newVal) {
            strncpy(config->wifi_ssid, newVal.c_str(), sizeof(config->wifi_ssid));
            wifiChanged = true;
        }
    }
    if (server.hasArg("wifi_pass")) {
        String newVal = server.arg("wifi_pass");
        if (String(config->wifi_pass) != newVal) {
            strncpy(config->wifi_pass, newVal.c_str(), sizeof(config->wifi_pass));
            wifiChanged = true;
        }
    }

    bool anyChanged = (wifiChanged || apChanged);

    if (anyChanged) {
        storageSaveConfig(*config);
        if (savedCallback) savedCallback();

        if (wifiChanged) wifiApplyLiveWifiCredentials(*config);
        if (apChanged) wifiApplyLiveApCredentials(*config);
    }

    server.send(200, "application/json", anyChanged ? "{\"status\":\"success\",\"message\":\"Wi-Fi credentials updated live\"}" : "{\"status\":\"unchanged\",\"message\":\"No changes detected\"}");
}

// POST /api/password - Update Admin Credentials & Reboot Machine
static void handleApiSavePassword() {
    if (!requireAuth()) return;

    bool changed = false;

    if (server.hasArg("portal_user") && server.arg("portal_user").length() > 0) {
        String newVal = server.arg("portal_user");
        if (String(config->portal_user) != newVal) {
            strncpy(config->portal_user, newVal.c_str(), sizeof(config->portal_user));
            changed = true;
        }
    }
    if (server.hasArg("portal_pass") && server.arg("portal_pass").length() > 0) {
        String newVal = server.arg("portal_pass");
        if (String(config->portal_pass) != newVal) {
            strncpy(config->portal_pass, newVal.c_str(), sizeof(config->portal_pass));
            changed = true;
        }
    }

    if (changed) {
        storageSaveConfig(*config);
        server.send(200, "application/json", "{\"status\":\"rebooting\",\"message\":\"Admin credentials updated. Terminal restarting...\"}");
        delay(1000);
        ESP.restart();
    } else {
        server.send(200, "application/json", "{\"status\":\"unchanged\",\"message\":\"No changes detected\"}");
    }
}

// POST /api/write - Arm RFID Card Write Mode
static void handleApiWriteCard() {
    if (!requireAuth()) return;

    if (!server.hasArg("card_val")) {
        server.send(400, "application/json", "{\"status\":\"error\",\"message\":\"Missing card_val parameter\"}");
        return;
    }

    String cardVal = server.arg("card_val");
    if (cardVal.length() > RFID_MESSAGE_MAX_LEN) {
        cardVal = cardVal.substring(0, RFID_MESSAGE_MAX_LEN);
    }
    strncpy(config->card_value, cardVal.c_str(), sizeof(config->card_value));
    config->op_mode = MODE_WRITE;

    storageSaveConfig(*config);

    writeStatus = WRITE_ARMED;
    writeStatusValue = cardVal;

    server.send(200, "application/json", "{\"status\":\"armed\",\"value\":\"" + cardVal + "\"}");
}

// GET /api/write_status - Live Write Status Polling
static void handleApiWriteStatus() {
    if (!requireAuth()) return;

    const char *statusStr = "idle";
    switch (writeStatus) {
        case WRITE_ARMED:   statusStr = "armed";   break;
        case WRITE_SUCCESS: statusStr = "success"; break;
        case WRITE_FAILED:  statusStr = "failed";  break;
        default:             statusStr = "idle";    break;
    }

    String json = "{\"status\":\"" + String(statusStr) + "\",\"value\":\"" + writeStatusValue + "\"}";
    server.send(200, "application/json", json);
}

// GET /api/queue - Get Offline Queue Items JSON
static void handleApiQueueGet() {
    if (!requireAuth()) return;

    String contents = storageGetOfflineQueueContents();
    JsonDocument doc;
    JsonArray items = doc.to<JsonArray>();

    int start = 0;
    while (start < (int)contents.length()) {
        int nl = contents.indexOf('\n', start);
        String line = (nl == -1) ? contents.substring(start) : contents.substring(start, nl);
        line.trim();
        if (line.length() > 0) {
            int c1 = line.indexOf(',');
            int c2 = line.indexOf(',', c1 + 1);
            int c3 = line.indexOf(',', c2 + 1);
            if (c1 != -1 && c2 != -1 && c3 != -1) {
                JsonObject item = items.add<JsonObject>();
                item["tagms"] = line.substring(0, c1);
                item["tagid"] = line.substring(c1 + 1, c2);
                item["date"] = line.substring(c2 + 1, c3);
                item["time"] = line.substring(c3 + 1);
            }
        }
        if (nl == -1) break;
        start = nl + 1;
    }

    String response;
    serializeJson(doc, response);
    server.send(200, "application/json", response);
}

// POST /api/queue/clear - Flush Offline Queue
static void handleApiQueueClear() {
    if (!requireAuth()) return;
    storageClearOfflineQueue();
    server.send(200, "application/json", "{\"status\":\"success\",\"message\":\"Offline queue cleared\"}");
}

// GET /api/wifi_scan - Scan Nearby Wi-Fi Networks
static void handleApiWifiScan() {
    if (!requireAuth()) return;

    int n = WiFi.scanNetworks();
    JsonDocument doc;
    JsonArray list = doc.to<JsonArray>();

    for (int i = 0; i < n; ++i) {
        JsonObject item = list.add<JsonObject>();
        item["ssid"] = WiFi.SSID(i);
        item["rssi"] = WiFi.RSSI(i);
        item["secure"] = (WiFi.encryptionType(i) != ENC_TYPE_NONE);
    }

    String response;
    serializeJson(doc, response);
    server.send(200, "application/json", response);
}

// POST /api/reboot - Restart Machine
static void handleApiReboot() {
    if (!requireAuth()) return;
    server.send(200, "application/json", "{\"status\":\"rebooting\",\"message\":\"Terminal restarting...\"}");
    delay(1000);
    ESP.restart();
}

void webPortalStart() {
    httpUpdater.setup(&server, "/api/update", config->portal_user, config->portal_pass);

    server.on("/api/status", HTTP_GET, handleApiStatus);
    server.on("/api/config", HTTP_POST, handleApiSaveConfig);
    server.on("/api/wifi", HTTP_POST, handleApiSaveWifi);
    server.on("/api/password", HTTP_POST, handleApiSavePassword);
    
    server.on("/api/write", HTTP_POST, handleApiWriteCard);
    server.on("/api/write_status", HTTP_GET, handleApiWriteStatus);
    
    server.on("/api/queue", HTTP_GET, handleApiQueueGet);
    server.on("/api/queue/clear", HTTP_POST, handleApiQueueClear);
    
    server.on("/api/wifi_scan", HTTP_GET, handleApiWifiScan);
    server.on("/api/reboot", HTTP_POST, handleApiReboot);
    
    server.onNotFound([]() {
        server.send(404, "application/json", "{\"error\":\"Not Found\",\"message\":\"Use Laravel Configure Machine Portal\"}");
    });
    server.begin();
}

void webPortalHandleClient() {
    server.handleClient();
}
