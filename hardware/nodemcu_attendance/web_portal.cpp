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

static void sendCorsHeaders() {
    server.sendHeader("Access-Control-Allow-Origin", "*");
    server.sendHeader("Access-Control-Allow-Methods", "GET, POST, OPTIONS");
    server.sendHeader("Access-Control-Allow-Headers", "Authorization, Content-Type");
}

static bool requireAuth() {
    sendCorsHeaders();
    if (server.method() == HTTP_OPTIONS) {
        server.send(204);
        return false;
    }
    if (!server.authenticate(config->portal_user, config->portal_pass)) {
        server.requestAuthentication();
        return false;
    }
    return true;
}

// ==========================================
// LIGHTWEIGHT LOCAL PROVISIONING PAGE (AP MODE)
// ==========================================
static void handleLocalProvisionPage() {
    sendCorsHeaders();
    String html = "<!DOCTYPE html><html lang='en'><head><meta charset='UTF-8'>";
    html += "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
    html += "<title>Attendance Terminal Setup</title>";
    html += "<style>";
    html += "body{font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,sans-serif;background:#f4f6f9;color:#1f2937;margin:0;padding:20px;}";
    html += ".card{max-width:480px;margin:20px auto;background:#fff;padding:24px;border-radius:12px;box-shadow:0 4px 15px rgba(0,0,0,0.08);}";
    html += "h2{color:#1e3a5f;margin-top:0;font-size:20px;text-align:center;}";
    html += "label{display:block;font-size:13px;font-weight:600;margin-top:12px;color:#374151;}";
    html += "input,select{width:100%;padding:10px;margin-top:4px;border:1px solid #d1d5db;border-radius:6px;font-size:14px;box-sizing:border-box;}";
    html += ".btn{background:#1e3a5f;color:#fff;border:0;padding:12px;border-radius:6px;font-weight:bold;cursor:pointer;width:100%;font-size:15px;margin-top:18px;}";
    html += ".btn:hover{background:#0f2744;}";
    html += ".hint{font-size:12px;color:#6b7280;margin-top:4px;}";
    html += "</style></head><body><div class='card'>";
    html += "<h2>📶 Attendance Terminal Setup</h2>";
    html += "<p class='hint' style='text-align:center;'>Connect this terminal to your local Wi-Fi router to sync with <strong>payroll.sarvodayavidyalay.com</strong>.</p>";
    html += "<form action='/save_local' method='POST'>";
    html += "<label>Wi-Fi Network SSID:</label>";
    html += "<input type='text' name='wifi_ssid' value='" + String(config->wifi_ssid) + "' placeholder='Enter Wi-Fi Name' required>";
    html += "<label>Wi-Fi Network Password:</label>";
    html += "<input type='password' name='wifi_pass' value='" + String(config->wifi_pass) + "' placeholder='Enter Wi-Fi Password' required>";
    html += "<label>Company / Institution Name:</label>";
    html += "<input type='text' name='company_name' value='" + String(config->company_name) + "' placeholder='Sarvodaya Vidyalay'>";
    html += "<label>Server Endpoint URI:</label>";
    html += "<input type='text' name='host_uri' value='" + String(config->host_uri) + "' required>";
    html += "<label>Bearer API Access Token:</label>";
    html += "<input type='password' name='api_token' value='" + String(config->api_token) + "' placeholder='Bearer Token'>";
    html += "<input type='submit' class='btn' value='🚀 Connect Terminal to Wi-Fi'>";
    html += "</form></div></body></html>";
    server.send(200, "text/html", html);
}

static void handleSaveLocal() {
    sendCorsHeaders();
    if (server.hasArg("wifi_ssid")) strncpy(config->wifi_ssid, server.arg("wifi_ssid").c_str(), sizeof(config->wifi_ssid));
    if (server.hasArg("wifi_pass")) strncpy(config->wifi_pass, server.arg("wifi_pass").c_str(), sizeof(config->wifi_pass));
    if (server.hasArg("company_name")) strncpy(config->company_name, server.arg("company_name").c_str(), sizeof(config->company_name));
    if (server.hasArg("host_uri")) strncpy(config->host_uri, server.arg("host_uri").c_str(), sizeof(config->host_uri));
    if (server.hasArg("api_token")) strncpy(config->api_token, server.arg("api_token").c_str(), sizeof(config->api_token));

    storageSaveConfig(*config);
    if (savedCallback) savedCallback();

    wifiApplyLiveWifiCredentials(*config);

    String html = "<!DOCTYPE html><html><head><meta charset='UTF-8'><style>body{font-family:sans-serif;background:#f4f6f9;text-align:center;padding:50px;} .card{background:#fff;padding:30px;border-radius:12px;display:inline-block;box-shadow:0 4px 15px rgba(0,0,0,0.1);} h2{color:#10b981;}</style></head><body><div class='card'><h2>✓ Wi-Fi Credentials Saved!</h2><p>Terminal is connecting to <strong>" + String(config->wifi_ssid) + "</strong>...</p><p>You can now reconnect your phone/laptop to your normal Wi-Fi!</p></div></body></html>";
    server.send(200, "text/html", html);
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

    // Sleek Local Setup Form at http://192.168.4.1/ (For Initial Wi-Fi Setup)
    server.on("/", HTTP_GET, handleLocalProvisionPage);
    server.on("/save_local", HTTP_POST, handleSaveLocal);

    // Micro-REST API Endpoints (With CORS Headers for Cloud Management)
    server.on("/api/status", HTTP_GET, handleApiStatus);
    server.on("/api/status", HTTP_OPTIONS, handleApiStatus);
    
    server.on("/api/config", HTTP_POST, handleApiSaveConfig);
    server.on("/api/config", HTTP_OPTIONS, handleApiSaveConfig);

    server.on("/api/wifi", HTTP_POST, handleApiSaveWifi);
    server.on("/api/wifi", HTTP_OPTIONS, handleApiSaveWifi);

    server.on("/api/password", HTTP_POST, handleApiSavePassword);
    server.on("/api/password", HTTP_OPTIONS, handleApiSavePassword);

    server.on("/api/write", HTTP_POST, handleApiWriteCard);
    server.on("/api/write", HTTP_OPTIONS, handleApiWriteCard);

    server.on("/api/write_status", HTTP_GET, handleApiWriteStatus);
    server.on("/api/write_status", HTTP_OPTIONS, handleApiWriteStatus);

    server.on("/api/queue", HTTP_GET, handleApiQueueGet);
    server.on("/api/queue", HTTP_OPTIONS, handleApiQueueGet);

    server.on("/api/queue/clear", HTTP_POST, handleApiQueueClear);
    server.on("/api/queue/clear", HTTP_OPTIONS, handleApiQueueClear);

    server.on("/api/wifi_scan", HTTP_GET, handleApiWifiScan);
    server.on("/api/wifi_scan", HTTP_OPTIONS, handleApiWifiScan);

    server.on("/api/reboot", HTTP_POST, handleApiReboot);
    server.on("/api/reboot", HTTP_OPTIONS, handleApiReboot);

    server.onNotFound([]() {
        sendCorsHeaders();
        if (server.method() == HTTP_OPTIONS) {
            server.send(204);
            return;
        }
        server.send(404, "application/json", "{\"error\":\"Not Found\"}");
    });
    server.begin();
}

void webPortalHandleClient() {
    server.handleClient();
}
