#include "web_portal.h"
#include "wifi_manager.h"
#include "storage.h"
#include "audio.h"
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

// ==========================================
// Shared Modern Responsive Header & Tab Bar
// ==========================================
static String renderHeader(const String &activeTab) {
    String html = "<!DOCTYPE html><html lang='en'><head><meta charset='UTF-8'>";
    html += "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
    html += "<title>Attendance System | Leena IT Solutions</title>";
    html += "<style>";
    html += ":root{--primary:#1e3a5f;--accent:#10b981;--bg:#f4f6f9;--card:#ffffff;--text:#1f2937;--border:#e5e7eb}";
    html += "body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;background:var(--bg);color:var(--text);margin:0;padding:15px}";
    html += ".container{max-width:650px;margin:auto}";
    html += ".header{text-align:center;margin-bottom:15px;padding-bottom:10px;border-bottom:2px solid var(--border)}";
    html += ".header h1{margin:0;font-size:22px;color:var(--primary)}";
    html += ".header p{margin:4px 0 0;font-size:12px;color:#6b7280}";
    
    // 5 Navigation Tabs Styling
    html += ".nav-tabs{display:flex;background:#e2e8f0;border-radius:10px;padding:4px;margin-bottom:20px;overflow-x:auto;gap:4px}";
    html += ".nav-tab{flex:1;text-align:center;padding:10px 8px;font-size:13px;font-weight:600;color:#475569;text-decoration:none;border-radius:7px;white-space:nowrap;transition:all 0.2s}";
    html += ".nav-tab:hover{background:#cbd5e1;color:var(--primary)}";
    html += ".nav-tab.active{background:var(--primary);color:#ffffff;box-shadow:0 2px 4px rgba(0,0,0,0.1)}";
    
    html += ".card{background:var(--card);border-radius:12px;padding:20px;margin-bottom:20px;box-shadow:0 4px 15px rgba(0,0,0,0.05)}";
    html += ".card h2{margin-top:0;font-size:16px;color:var(--primary);border-bottom:1px solid #f3f4f6;padding-bottom:8px}";
    html += "label{display:block;font-size:13px;font-weight:600;margin-top:12px;color:#374151}";
    html += "input[type=text],input[type=password],select{width:100%;padding:10px 12px;margin-top:4px;border:1px solid #d1d5db;border-radius:6px;font-size:14px;box-sizing:border-box}";
    html += ".btn{background:var(--primary);color:#fff;border:0;padding:12px 20px;border-radius:6px;font-weight:bold;cursor:pointer;width:100%;font-size:15px;margin-top:15px;transition:background 0.2s}";
    html += ".btn:hover{background:#0f2744}";
    html += ".btn-success{background:var(--accent)}";
    html += ".btn-success:hover{background:#059669}";
    html += ".btn-danger{background:#dc2626}";
    html += ".btn-danger:hover{background:#b91c1c}";
    html += ".status-badge{display:inline-block;padding:4px 10px;border-radius:20px;font-size:12px;font-weight:bold;background:#d1fae5;color:#065f46}";
    html += ".status-badge.ap{background:#feefc3;color:#7c2d12}";
    html += ".grid{display:grid;grid-template-columns:1fr 1fr;gap:10px}";
    html += ".hint{font-size:12px;color:#6b7280;margin-top:4px}";
    html += "table{width:100%;border-collapse:collapse;font-size:13px;white-space:nowrap}";
    html += "th,td{text-align:left;padding:8px;border-bottom:1px solid #f3f4f6}";
    html += "th{color:#6b7280;font-size:11px;text-transform:uppercase}";
    html += "</style></head><body><div class='container'>";
    
    html += "<div class='header'><h1>Attendance System</h1><p>Powered By Leena IT Solutions</p></div>";
    
    // 5 Navigation Tabs
    html += "<div class='nav-tabs'>";
    html += "<a href='/' class='nav-tab" + String(activeTab == "home" ? " active" : "") + "'>Home</a>";
    html += "<a href='/write' class='nav-tab" + String(activeTab == "write" ? " active" : "") + "'>Write Card</a>";
    html += "<a href='/queue' class='nav-tab" + String(activeTab == "queue" ? " active" : "") + "'>Offline Queue</a>";
    html += "<a href='/wifi' class='nav-tab" + String(activeTab == "wifi" ? " active" : "") + "'>AP & Wifi</a>";
    html += "<a href='/password' class='nav-tab" + String(activeTab == "password" ? " active" : "") + "'>Password</a>";
    html += "</div>";
    
    return html;
}

static String renderFooter() {
    return "</div></body></html>";
}

// ==========================================
// TAB 1: HOME PAGE (Diagnostics & Company Settings - Live Update)
// ==========================================
static void handleHomeTab() {
    if (!requireAuth()) return;

    String html = renderHeader("home");

    // Device Diagnostics Card
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

    // Terminal & Organization Settings Form
    html += "<div class='card'><h2>Terminal & Company Settings</h2>";
    html += "<div class='hint'>Updates settings live in real-time without rebooting the terminal.</div>";
    html += "<form action='/save_home' method='POST'>";
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
    html += "<input type='submit' class='btn' value='Save Settings (Live)'>";
    html += "</form></div>";

    html += renderFooter();
    server.send(200, "text/html", html);
}

// ==========================================
// TAB 2: WRITE CARD PAGE (Totally Separate Page)
// ==========================================
static void handleWriteTab() {
    if (!requireAuth()) return;

    String html = renderHeader("write");

    html += "<div class='card'><h2>Write RFID Card</h2>";
    html += "<p class='hint'>Enter the employee code to write onto the RFID card block 4. Placing a card near the reader will burn this value live.</p>";
    html += "<form id='writeCardForm'>";
    html += "<label>Card Value / Employee Code (max " + String(RFID_MESSAGE_MAX_LEN) + " characters):</label>";
    html += "<input type='text' id='cardValInput' name='card_val' value='" + String(config->card_value) + "' maxlength='" + String(RFID_MESSAGE_MAX_LEN) + "' placeholder='Enter value to write' required>";
    html += "<input type='submit' class='btn btn-success' value='Arm Write Card'>";
    html += "</form>";
    html += "<div id='writeStatusMsg' style='margin-top:15px;font-weight:bold;font-size:14px;color:var(--primary);'></div>";
    html += "</div>";

    // AJAX Live Polling Script
    html += "<script>";
    html += "(function(){";
    html += "var form=document.getElementById('writeCardForm');";
    html += "var input=document.getElementById('cardValInput');";
    html += "var msg=document.getElementById('writeStatusMsg');";
    html += "var pollTimer=null,stopTimer=null;";
    html += "function stopPolling(){if(pollTimer){clearInterval(pollTimer);pollTimer=null;}if(stopTimer){clearTimeout(stopTimer);stopTimer=null;}}";
    html += "function poll(){fetch('/write_status').then(function(r){return r.json();}).then(function(data){";
    html += "if(data.status==='armed'){msg.style.color='#1e3a5f';msg.textContent='\\u23F3 Waiting for card tap on reader...';}";
    html += "else if(data.status==='success'){msg.style.color='#10b981';msg.textContent='\\u2713 Written successfully: '+data.value;input.value='';stopPolling();}";
    html += "else if(data.status==='failed'){msg.style.color='#dc2626';msg.textContent='\\u2717 Write failed - try tapping card again.';stopPolling();}";
    html += "}).catch(function(){});}";
    html += "form.addEventListener('submit',function(e){";
    html += "e.preventDefault();";
    html += "var params=new URLSearchParams();params.append('card_val',input.value);";
    html += "fetch('/write_card',{method:'POST',body:params}).then(function(r){return r.json();}).then(function(){";
    html += "msg.style.color='#1e3a5f';msg.textContent='\\u23F3 Armed! Place RFID card near reader...';";
    html += "stopPolling();pollTimer=setInterval(poll,1000);stopTimer=setTimeout(stopPolling,30000);";
    html += "}).catch(function(){msg.style.color='#dc2626';msg.textContent='Request failed - check connection.';});";
    html += "});";
    html += "})();";
    html += "</script>";

    html += renderFooter();
    server.send(200, "text/html", html);
}

// ==========================================
// TAB 3: OFFLINE QUEUE PAGE
// ==========================================
static void handleQueueTab() {
    if (!requireAuth()) return;

    String contents = storageGetOfflineQueueContents();
    String html = renderHeader("queue");

    int count = 0;
    html += "<div class='card'><h2>Offline Punches Queue</h2>";

    if (contents.length() == 0) {
        html += "<p style='color:#10b981;font-weight:bold;'>✓ No punches currently queued - all attendance punches synced to server.</p>";
    } else {
        html += "<div style='overflow-x:auto;'><table><tr><th>Employee Code</th><th>Tag ID</th><th>Date</th><th>Time</th></tr>";
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
        html += "</table></div>";
    }
    html += "</div>";

    if (count > 0) {
        html += "<form action='/queue/clear' method='POST' onsubmit=\"return confirm('Discard all " +
                String(count) + " queued punches without syncing them? This action cannot be undone.');\">";
        html += "<button type='submit' class='btn btn-danger'>Clear Offline Queue (" + String(count) + " Punches)</button>";
        html += "</form>";
    }

    html += renderFooter();
    server.send(200, "text/html", html);
}

// ==========================================
// TAB 4: AP & WIFI PAGE (Live Update)
// ==========================================
static void handleWifiTab() {
    if (!requireAuth()) return;

    String html = renderHeader("wifi");

    html += "<form action='/save_wifi' method='POST'>";
    
    // Access Point Setup Card
    html += "<div class='card'><h2>Access Point Setup</h2>";
    html += "<label>Access Point SSID (Hotspot Name):</label>";
    html += "<input type='text' name='ap_ssid' value='" + String(config->ap_ssid) + "' required>";
    html += "<label>Access Point Password:</label>";
    html += "<input type='password' name='ap_pass' value='" + String(config->ap_pass) + "' required>";
    html += "</div>";

    // WiFi Setup Card
    html += "<div class='card'><h2>Wi-Fi Network Setup</h2>";
    html += "<label>Wi-Fi Network SSID:</label>";
    html += "<input type='text' name='wifi_ssid' value='" + String(config->wifi_ssid) + "' placeholder='Enter Wi-Fi Name'>";
    html += "<label>Wi-Fi Network Password:</label>";
    html += "<input type='password' name='wifi_pass' value='" + String(config->wifi_pass) + "' placeholder='Enter Wi-Fi Password'>";
    html += "<input type='submit' class='btn' value='Save Wi-Fi Settings (Live)'>";
    html += "</div>";
    html += "</form>";

    html += renderFooter();
    server.send(200, "text/html", html);
}

// ==========================================
// TAB 5: PASSWORD PAGE (Saves & Resets Machine)
// ==========================================
static void handlePasswordTab() {
    if (!requireAuth()) return;

    String html = renderHeader("password");

    html += "<div class='card'><h2>Portal Admin Credentials</h2>";
    html += "<div class='hint' style='color:#dc2626;font-weight:bold;margin-bottom:10px;'>⚠️ Changing your Admin username or password will save settings and reboot the machine automatically.</div>";
    html += "<form action='/save_password' method='POST' onsubmit=\"return confirm('System will reboot immediately to apply new password credentials. Continue?');\">";
    html += "<label>Portal Admin Username:</label>";
    html += "<input type='text' name='portal_user' value='" + String(config->portal_user) + "' required>";
    html += "<label>Portal Admin Password:</label>";
    html += "<input type='password' name='portal_pass' value='" + String(config->portal_pass) + "' required>";
    html += "<input type='submit' class='btn btn-danger' value='Save & Reboot Machine'>";
    html += "</form></div>";

    html += renderFooter();
    server.send(200, "text/html", html);
}

// ==========================================
// ACTION HANDLERS
// ==========================================

// Save Home Settings Live (No Reboot)
static void handleSaveHomeWeb() {
    if (!requireAuth()) return;

    if (server.hasArg("company_name")) strncpy(config->company_name, server.arg("company_name").c_str(), sizeof(config->company_name));
    if (server.hasArg("host_uri")) strncpy(config->host_uri, server.arg("host_uri").c_str(), sizeof(config->host_uri));
    if (server.hasArg("api_token")) strncpy(config->api_token, server.arg("api_token").c_str(), sizeof(config->api_token));
    if (server.hasArg("op_mode")) config->op_mode = server.arg("op_mode").toInt();

    storageSaveConfig(*config);
    if (savedCallback) savedCallback();

    String html = "<!DOCTYPE html><html><head><meta charset='UTF-8'><meta http-equiv='refresh' content='2;url=/'><style>body{font-family:-apple-system,BlinkMacSystemFont,sans-serif;background:#f4f6f9;text-align:center;padding:50px;} .card{background:#fff;padding:30px;border-radius:12px;display:inline-block;box-shadow:0 4px 15px rgba(0,0,0,0.1);} h2{color:#10b981;margin-top:0;} a{color:#1e3a5f;text-decoration:none;font-weight:bold;}</style></head><body><div class='card'><h2>✓ Home Settings Applied Live!</h2><p>Company name, Mode, and Host URI updated in real-time without rebooting.</p><p><a href='/'>Returning to Home...</a></p></div></body></html>";
    server.send(200, "text/html", html);
}

// Save AP & Wifi Settings Live (No Reboot)
static void handleSaveWifiWeb() {
    if (!requireAuth()) return;

    String oldWifiSsid = String(config->wifi_ssid);
    String oldWifiPass = String(config->wifi_pass);
    String oldApSsid = String(config->ap_ssid);
    String oldApPass = String(config->ap_pass);

    if (server.hasArg("ap_ssid")) strncpy(config->ap_ssid, server.arg("ap_ssid").c_str(), sizeof(config->ap_ssid));
    if (server.hasArg("ap_pass")) strncpy(config->ap_pass, server.arg("ap_pass").c_str(), sizeof(config->ap_pass));
    if (server.hasArg("wifi_ssid")) strncpy(config->wifi_ssid, server.arg("wifi_ssid").c_str(), sizeof(config->wifi_ssid));
    if (server.hasArg("wifi_pass")) strncpy(config->wifi_pass, server.arg("wifi_pass").c_str(), sizeof(config->wifi_pass));

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

    String html = "<!DOCTYPE html><html><head><meta charset='UTF-8'><meta http-equiv='refresh' content='2;url=/wifi'><style>body{font-family:-apple-system,BlinkMacSystemFont,sans-serif;background:#f4f6f9;text-align:center;padding:50px;} .card{background:#fff;padding:30px;border-radius:12px;display:inline-block;box-shadow:0 4px 15px rgba(0,0,0,0.1);} h2{color:#10b981;margin-top:0;} a{color:#1e3a5f;text-decoration:none;font-weight:bold;}</style></head><body><div class='card'><h2>✓ Wi-Fi Settings Applied Live!</h2><p>Access Point and Wi-Fi credentials updated without rebooting.</p><p><a href='/wifi'>Returning to AP & Wifi...</a></p></div></body></html>";
    server.send(200, "text/html", html);
}

// Save Password Settings & Reboot Machine!
static void handleSavePasswordWeb() {
    if (!requireAuth()) return;

    if (server.hasArg("portal_user") && server.arg("portal_user").length() > 0) {
        strncpy(config->portal_user, server.arg("portal_user").c_str(), sizeof(config->portal_user));
    }
    if (server.hasArg("portal_pass") && server.arg("portal_pass").length() > 0) {
        strncpy(config->portal_pass, server.arg("portal_pass").c_str(), sizeof(config->portal_pass));
    }

    storageSaveConfig(*config);

    String html = "<!DOCTYPE html><html><head><meta charset='UTF-8'><style>body{font-family:-apple-system,BlinkMacSystemFont,sans-serif;background:#f4f6f9;text-align:center;padding:50px;} .card{background:#fff;padding:30px;border-radius:12px;display:inline-block;box-shadow:0 4px 15px rgba(0,0,0,0.1);} h2{color:#dc2626;margin-top:0;}</style></head><body><div class='card'><h2>🔄 Rebooting Terminal...</h2><p>Password updated successfully. Machine is restarting now...</p></div></body></html>";
    server.send(200, "text/html", html);

    delay(1000);
    ESP.restart();
}

static void handleWriteCardWeb() {
    if (!requireAuth()) return;

    if (!server.hasArg("card_val")) {
        server.send(400, "text/plain", "Bad Request");
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

static void handleQueueClearWeb() {
    if (!requireAuth()) return;
    storageClearOfflineQueue();
    server.sendHeader("Location", "/queue", true);
    server.send(302, "text/plain", "");
}

void webPortalStart() {
    server.on("/", handleHomeTab);
    server.on("/write", handleWriteTab);
    server.on("/queue", handleQueueTab);
    server.on("/wifi", handleWifiTab);
    server.on("/password", handlePasswordTab);
    
    server.on("/save_home", HTTP_POST, handleSaveHomeWeb);
    server.on("/save_wifi", HTTP_POST, handleSaveWifiWeb);
    server.on("/save_password", HTTP_POST, handleSavePasswordWeb);
    
    server.on("/write_card", HTTP_POST, handleWriteCardWeb);
    server.on("/write_status", handleWriteStatusWeb);
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
