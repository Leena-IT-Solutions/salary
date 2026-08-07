#include "web_portal.h"
#include "wifi_manager.h"
#include "storage.h"
#include <ESP8266WebServer.h>
#include <ESP8266WiFi.h>

static ESP8266WebServer server(80);
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

static void handleWebRoot() {
    if (!requireAuth()) return;

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
    html += ".hint{font-size:12px;color:#6b7280;margin-top:4px}";
    html += "</style></head><body><div class='container'>";

    html += "<div class='header'><h1>Attendance System</h1><p>Powered By Leena IT Solutions</p></div>";

    // Status Bar Card
    html += "<div class='card'><h2>Device Diagnostics</h2>";
    html += "<div style='margin-bottom:10px;'>System Status: ";
    if (wifiIsAPMode()) {
        html += "<span class='status-badge ap'>Access Point Mode (192.168.4.1)</span>";
    } else {
        html += "<span class='status-badge'>Connected (" + WiFi.localIP().toString() + ")</span>";
    }
    html += "</div>";
    html += "<div class='grid'>";
    html += "<div><strong>mDNS Domain:</strong> http://attendance.local</div>";
    html += "<div><strong>Signal Strength:</strong> " + String(WiFi.RSSI()) + " dBm</div>";
    html += "</div></div>";

    // Offline Queue Card
    int queueCount = storageGetOfflineQueueCount();
    html += "<div class='card'><h2>Offline Queue</h2>";
    html += "<p>" + String(queueCount) + " punch(es) waiting to sync.</p>";
    html += "<a href='/queue' style='color:var(--primary);font-weight:bold;'>View Queue &rarr;</a>";
    html += "</div>";

    // Main Config Form
    html += "<form action='/save' method='POST'>";

    // Access Point Setup Card
    html += "<div class='card'><h2>Access Point Setup</h2>";
    html += "<label>Enter Accesspoint SSID:</label>";
    html += "<input type='text' name='ap_ssid' value='" + String(config->ap_ssid) + "' required>";
    html += "<label>Enter Accesspoint Password:</label>";
    html += "<input type='password' name='ap_pass' value='" + String(config->ap_pass) + "' required>";
    html += "</div>";

    // WiFi Setup Card
    html += "<div class='card'><h2>WiFi Setup</h2>";
    html += "<label>Enter WiFi SSID:</label>";
    html += "<input type='text' name='wifi_ssid' value='" + String(config->wifi_ssid) + "'>";
    html += "<label>Enter WiFi Password:</label>";
    html += "<input type='password' name='wifi_pass' value='" + String(config->wifi_pass) + "'>";
    html += "</div>";

    // Terminal & Organization Settings Card
    html += "<div class='card'><h2>Terminal & Company Settings</h2>";
    html += "<label>Company / Institution Name:</label>";
    html += "<input type='text' name='company_name' value='" + String(config->company_name) + "' placeholder='Sarvodaya Vidyalay'>";
    html += "<label>Operation Mode:</label>";
    html += "<select name='op_mode'>";
    html += "<option value='0'" + String(config->op_mode == MODE_SETUP ? " selected" : "") + ">Setup (S)</option>";
    html += "<option value='1'" + String(config->op_mode == MODE_READ ? " selected" : "") + ">Read (R) - Normal Attendance</option>";
    html += "<option value='2'" + String(config->op_mode == MODE_WRITE ? " selected" : "") + ">Write (W) - Card Burning</option>";
    html += "<option value='3'" + String(config->op_mode == MODE_FORMAT ? " selected" : "") + ">Format (F) - Format Card</option>";
    html += "<option value='4'" + String(config->op_mode == MODE_DELETE ? " selected" : "") + ">Delete (D) - Clear Card Data</option>";
    html += "<option value='5'" + String(config->op_mode == MODE_CLEAR ? " selected" : "") + ">Clear (C) - Flush Offline Queue</option>";
    html += "</select>";
    html += "<label>Host URI Endpoint:</label>";
    html += "<input type='text' name='host_uri' value='" + String(config->host_uri) + "' required>";
    html += "<label>Bearer API Access Token (Optional):</label>";
    html += "<input type='password' name='api_token' value='" + String(config->api_token) + "' placeholder='Bearer Token'>";
    html += "</div>";

    // Portal Security Card
    html += "<div class='card'><h2>Portal Admin Login</h2>";
    html += "<div class='hint'>Protects this configuration page. Change these from the defaults.</div>";
    html += "<label>Portal Username:</label>";
    html += "<input type='text' name='portal_user' value='" + String(config->portal_user) + "' required>";
    html += "<label>Portal Password:</label>";
    html += "<input type='password' name='portal_pass' value='" + String(config->portal_pass) + "' required>";
    html += "<input type='submit' class='btn' value='Save Settings'>";
    html += "</div>";
    html += "</form>";

    // RFID Card Writer Tool Card
    html += "<div class='card'><h2>Write Card</h2>";
    html += "<form id='writeCardForm'>";
    html += "<label>Card Value / Employee Code (max " + String(RFID_MESSAGE_MAX_LEN) + " characters):</label>";
    html += "<input type='text' id='cardValInput' name='card_val' value='" + String(config->card_value) + "' maxlength='" + String(RFID_MESSAGE_MAX_LEN) + "' placeholder='Enter value to write'>";
    html += "<input type='submit' class='btn btn-success' value='Write Card'>";
    html += "</form>";
    html += "<div id='writeStatusMsg' class='hint' style='margin-top:10px;'></div>";
    html += "</div>";

    html += "</div>";

    // Live write flow: arm the device via fetch (no page navigation),
    // then poll /write_status until the actual tap result comes back, and
    // clear the field once a write is confirmed successful.
    html += "<script>";
    html += "(function(){";
    html += "var form=document.getElementById('writeCardForm');";
    html += "var input=document.getElementById('cardValInput');";
    html += "var msg=document.getElementById('writeStatusMsg');";
    html += "var pollTimer=null,stopTimer=null;";
    html += "function stopPolling(){if(pollTimer){clearInterval(pollTimer);pollTimer=null;}if(stopTimer){clearTimeout(stopTimer);stopTimer=null;}}";
    html += "function poll(){fetch('/write_status').then(function(r){return r.json();}).then(function(data){";
    html += "if(data.status==='armed'){msg.textContent='Waiting for card tap...';}";
    html += "else if(data.status==='success'){msg.textContent='\\u2713 Written successfully: '+data.value;input.value='';stopPolling();}";
    html += "else if(data.status==='failed'){msg.textContent='\\u2717 Write failed - try tapping the card again.';stopPolling();}";
    html += "}).catch(function(){});}";
    html += "form.addEventListener('submit',function(e){";
    html += "e.preventDefault();";
    html += "var params=new URLSearchParams();params.append('card_val',input.value);";
    html += "fetch('/write_card',{method:'POST',body:params}).then(function(r){return r.json();}).then(function(){";
    html += "msg.textContent='Waiting for card tap...';";
    html += "stopPolling();pollTimer=setInterval(poll,1000);stopTimer=setTimeout(stopPolling,30000);";
    html += "}).catch(function(){msg.textContent='Request failed - check connection.';});";
    html += "});";
    html += "})();";
    html += "</script>";

    html += "</body></html>";

    server.send(200, "text/html", html);
}

static void handleSaveWeb() {
    if (!requireAuth()) return;

    if (!server.hasArg("host_uri")) {
        server.send(400, "text/plain", "Bad Request");
        return;
    }

    String oldWifiSsid = String(config->wifi_ssid);
    String oldWifiPass = String(config->wifi_pass);
    String oldApSsid = String(config->ap_ssid);
    String oldApPass = String(config->ap_pass);

    if (server.hasArg("ap_ssid")) strncpy(config->ap_ssid, server.arg("ap_ssid").c_str(), sizeof(config->ap_ssid));
    if (server.hasArg("ap_pass")) strncpy(config->ap_pass, server.arg("ap_pass").c_str(), sizeof(config->ap_pass));
    if (server.hasArg("wifi_ssid")) strncpy(config->wifi_ssid, server.arg("wifi_ssid").c_str(), sizeof(config->wifi_ssid));
    if (server.hasArg("wifi_pass")) strncpy(config->wifi_pass, server.arg("wifi_pass").c_str(), sizeof(config->wifi_pass));
    if (server.hasArg("company_name")) strncpy(config->company_name, server.arg("company_name").c_str(), sizeof(config->company_name));
    if (server.hasArg("host_uri")) strncpy(config->host_uri, server.arg("host_uri").c_str(), sizeof(config->host_uri));
    if (server.hasArg("api_token")) strncpy(config->api_token, server.arg("api_token").c_str(), sizeof(config->api_token));
    if (server.hasArg("op_mode")) config->op_mode = server.arg("op_mode").toInt();
    if (server.hasArg("portal_user") && server.arg("portal_user").length() > 0) {
        strncpy(config->portal_user, server.arg("portal_user").c_str(), sizeof(config->portal_user));
    }
    if (server.hasArg("portal_pass") && server.arg("portal_pass").length() > 0) {
        strncpy(config->portal_pass, server.arg("portal_pass").c_str(), sizeof(config->portal_pass));
    }

    storageSaveConfig(*config);

    if (savedCallback) savedCallback();

    // Live Wi-Fi reconnect if credentials changed
    if (String(config->wifi_ssid) != oldWifiSsid || String(config->wifi_pass) != oldWifiPass) {
        wifiApplyLiveWifiCredentials(*config);
    }

    // Live AP update if AP credentials changed
    if (String(config->ap_ssid) != oldApSsid || String(config->ap_pass) != oldApPass) {
        wifiApplyLiveApCredentials(*config);
    }

    String html = "<!DOCTYPE html><html><head><meta charset='UTF-8'><meta http-equiv='refresh' content='2;url=/'><style>body{font-family:-apple-system,BlinkMacSystemFont,sans-serif;background:#f4f6f9;text-align:center;padding:50px;} .card{background:#fff;padding:30px;border-radius:12px;display:inline-block;box-shadow:0 4px 15px rgba(0,0,0,0.1);} h2{color:#10b981;margin-top:0;} a{color:#1e3a5f;text-decoration:none;font-weight:bold;}</style></head><body><div class='card'><h2>&#10003; Settings Applied Live!</h2><p>Changes are saved and running in real-time without rebooting.</p><p><a href='/'>Returning to Dashboard...</a></p></div></body></html>";
    server.send(200, "text/html", html);
}

static void handleWriteCardWeb() {
    if (!requireAuth()) return;

    if (!server.hasArg("card_val")) {
        server.send(400, "text/plain", "Bad Request");
        return;
    }

    // Clamp here (not just via the form's maxlength) so config->card_value
    // always matches exactly what will actually be written to the card -
    // no silent truncation surprise later in rfidWriteMessage().
    String cardVal = server.arg("card_val");
    if (cardVal.length() > RFID_MESSAGE_MAX_LEN) {
        cardVal = cardVal.substring(0, RFID_MESSAGE_MAX_LEN);
    }
    strncpy(config->card_value, cardVal.c_str(), sizeof(config->card_value));
    config->op_mode = MODE_WRITE;

    storageSaveConfig(*config);

    writeStatus = WRITE_ARMED;
    writeStatusValue = cardVal;

    server.send(200, "application/json", "{\"status\":\"armed\"}");
}

static void handleWriteStatusWeb() {
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

static void handleQueueWeb() {
    if (!requireAuth()) return;

    String contents = storageGetOfflineQueueContents();

    String html = "<!DOCTYPE html><html lang='en'><head><meta charset='UTF-8'>";
    html += "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
    html += "<title>Offline Queue | Attendance System</title>";
    html += "<style>";
    html += "body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;background:#f4f6f9;color:#1f2937;margin:0;padding:20px}";
    html += ".container{max-width:700px;margin:auto}";
    html += ".card{background:#fff;border-radius:12px;padding:20px;margin-bottom:20px;box-shadow:0 4px 15px rgba(0,0,0,0.05);overflow-x:auto}";
    html += "table{width:100%;border-collapse:collapse;font-size:13px;white-space:nowrap}";
    html += "th,td{text-align:left;padding:8px;border-bottom:1px solid #f3f4f6}";
    html += "th{color:#6b7280;font-size:11px;text-transform:uppercase}";
    html += ".btn{background:#dc2626;color:#fff;border:0;padding:12px 20px;border-radius:6px;font-weight:bold;cursor:pointer;width:100%;font-size:15px;margin-top:15px}";
    html += ".btn:hover{background:#b91c1c}";
    html += "a.back{color:#1e3a5f;text-decoration:none;font-weight:bold;display:inline-block;margin-bottom:15px}";
    html += "</style></head><body><div class='container'>";
    html += "<a class='back' href='/'>&larr; Back to Dashboard</a>";

    int count = 0;
    html += "<div class='card'><h2>Offline Queue</h2>";

    if (contents.length() == 0) {
        html += "<p>No punches are currently queued - everything has been synced.</p>";
    } else {
        html += "<table><tr><th>Employee Code</th><th>Tag ID</th><th>Date</th><th>Time</th></tr>";
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
                    html += "<tr><td>" + line.substring(0, c1) + "</td><td>" +
                            line.substring(c1 + 1, c2) + "</td><td>" +
                            line.substring(c2 + 1, c3) + "</td><td>" +
                            line.substring(c3 + 1) + "</td></tr>";
                    count++;
                }
            }
            if (nl == -1) break;
            start = nl + 1;
        }
        html += "</table>";
    }
    html += "</div>";

    if (count > 0) {
        html += "<form action='/queue/clear' method='POST' onsubmit=\"return confirm('Discard all " +
                String(count) + " queued punches without syncing them? This cannot be undone.');\">";
        html += "<button type='submit' class='btn'>Clear Queue Now (Discard " + String(count) + ")</button>";
        html += "</form>";
    }

    html += "</div></body></html>";
    server.send(200, "text/html", html);
}

static void handleQueueClearWeb() {
    if (!requireAuth()) return;
    storageClearOfflineQueue();
    server.sendHeader("Location", "/queue", true);
    server.send(302, "text/plain", "");
}

void webPortalStart() {
    server.on("/", handleWebRoot);
    server.on("/save", HTTP_POST, handleSaveWeb);
    server.on("/write_card", HTTP_POST, handleWriteCardWeb);
    server.on("/write_status", handleWriteStatusWeb);
    server.on("/queue", handleQueueWeb);
    server.on("/queue/clear", HTTP_POST, handleQueueClearWeb);
    server.onNotFound([]() {
        server.sendHeader("Location", "http://192.168.4.1/", true);
        server.send(302, "text/plain", "");
    });
    server.begin();
}

void webPortalHandleClient() {
    server.handleClient();
}
