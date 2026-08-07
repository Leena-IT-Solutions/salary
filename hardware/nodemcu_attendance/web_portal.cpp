#include "web_portal.h"
#include "wifi_manager.h"
#include "storage.h"
#include "audio.h"
#include <ESP8266WebServer.h>
#include <ESP8266WiFi.h>
#include <ESP8266HTTPUpdateServer.h>

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
// Shared Modern Responsive Header & 6 Tab Bar
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
    
    // 6 Navigation Tabs Styling
    html += ".nav-tabs{display:flex;background:#e2e8f0;border-radius:10px;padding:4px;margin-bottom:20px;overflow-x:auto;gap:4px}";
    html += ".nav-tab{flex:1;text-align:center;padding:10px 6px;font-size:12px;font-weight:600;color:#475569;text-decoration:none;border-radius:7px;white-space:nowrap;transition:all 0.2s}";
    html += ".nav-tab:hover{background:#cbd5e1;color:var(--primary)}";
    html += ".nav-tab.active{background:var(--primary);color:#ffffff;box-shadow:0 2px 4px rgba(0,0,0,0.1)}";
    
    html += ".card{background:var(--card);border-radius:12px;padding:20px;margin-bottom:20px;box-shadow:0 4px 15px rgba(0,0,0,0.05)}";
    html += ".card h2{margin-top:0;font-size:16px;color:var(--primary);border-bottom:1px solid #f3f4f6;padding-bottom:8px}";
    html += "label{display:block;font-size:13px;font-weight:600;margin-top:12px;color:#374151}";
    html += "input[type=text],input[type=password],input[type=file],select{width:100%;padding:10px 12px;margin-top:4px;border:1px solid #d1d5db;border-radius:6px;font-size:14px;box-sizing:border-box}";
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
    
    // 6 Navigation Tabs
    html += "<div class='nav-tabs'>";
    html += "<a href='/' class='nav-tab" + String(activeTab == "home" ? " active" : "") + "'>Home</a>";
    html += "<a href='/write' class='nav-tab" + String(activeTab == "write" ? " active" : "") + "'>Write Card</a>";
    html += "<a href='/queue' class='nav-tab" + String(activeTab == "queue" ? " active" : "") + "'>Offline Queue</a>";
    html += "<a href='/wifi' class='nav-tab" + String(activeTab == "wifi" ? " active" : "") + "'>AP & Wifi</a>";
    html += "<a href='/password' class='nav-tab" + String(activeTab == "password" ? " active" : "") + "'>Password</a>";
    html += "<a href='/update' class='nav-tab" + String(activeTab == "update" ? " active" : "") + "'>Update</a>";
    html += "</div>";
    
    return html;
}

static String renderFooter() {
    return "</div></body></html>";
}

// ==========================================
// TAB 1: HOME PAGE (Diagnostics & Settings)
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
    html += "</select>";
    html += "<div class='hint'>ℹ️ If set to non-Read mode, the terminal auto-reverts to Read mode after 15 minutes of inactivity.</div>";
    html += "<label>Buzzer Sound Effects:</label>";
    html += "<select name='buzzer_enabled'>";
    html += "<option value='1'" + String(config->buzzer_enabled == 1 ? " selected" : "") + ">Enabled (Sound On)</option>";
    html += "<option value='0'" + String(config->buzzer_enabled == 0 ? " selected" : "") + ">Muted (Silent Mode)</option>";
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
// TAB 2: WRITE CARD PAGE
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
// TAB 4: AP & WIFI PAGE (With Live Network Scanner)
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
    html += "<button type='button' class='btn btn-success' onclick='scanWifi()'>📶 Scan Nearby Wi-Fi Networks</button>";
    html += "<div id='scanResults' style='margin-top:10px;'></div>";

    html += "<label>Wi-Fi Network SSID:</label>";
    html += "<input type='text' id='wifi_ssid' name='wifi_ssid' value='" + String(config->wifi_ssid) + "' placeholder='Enter Wi-Fi Name'>";
    html += "<label>Wi-Fi Network Password:</label>";
    html += "<input type='password' name='wifi_pass' value='" + String(config->wifi_pass) + "' placeholder='Enter Wi-Fi Password'>";
    html += "<input type='submit' class='btn' value='Save Wi-Fi Settings (Live)'>";
    html += "</div>";
    html += "</form>";

    // Live Scanner JS
    html += "<script>";
    html += "function selectSsid(name){ document.getElementById('wifi_ssid').value = name; }";
    html += "function scanWifi(){";
    html += "var box = document.getElementById('scanResults');";
    html += "box.innerHTML = '<p style=\"color:var(--primary);font-weight:bold;\">⏳ Scanning Wi-Fi networks...</p>';";
    html += "fetch('/wifi_scan').then(function(r){return r.json();}).then(function(list){";
    html += "if(list.length===0){ box.innerHTML='<p style=\"color:#dc2626;\">No Wi-Fi networks found.</p>'; return; }";
    html += "var h='<div style=\"max-height:150px;overflow-y:auto;background:#edf2f7;border-radius:6px;padding:8px;\">';";
    html += "list.forEach(function(item){";
    html += "h += '<div style=\"padding:6px;border-bottom:1px solid #cbd5e1;cursor:pointer;display:flex;justify-content:space-between;\" onclick=\"selectSsid(\\''+item.ssid+'\\')\">';";
    html += "h += '<span><strong>'+item.ssid+'</strong></span>';";
    html += "h += '<span style=\"color:#6b7280;font-size:12px;\">'+item.rssi+' dBm '+(item.secure?'🔒':'')+'</span></div>';";
    html += "});";
    html += "h += '</div>'; box.innerHTML = h;";
    html += "}).catch(function(){ box.innerHTML='<p style=\"color:#dc2626;\">Scan failed - try again.</p>'; });";
    html += "}";
    html += "</script>";

    html += renderFooter();
    server.send(200, "text/html", html);
}

// Live Wi-Fi Scanner API Handler
static void handleWifiScanApi() {
    if (!requireAuth()) return;

    int n = WiFi.scanNetworks();
    String json = "[";
    for (int i = 0; i < n; ++i) {
        if (i > 0) json += ",";
        json += "{\"ssid\":\"" + WiFi.SSID(i) + "\",\"rssi\":" + String(WiFi.RSSI(i)) + ",\"secure\":" + String(WiFi.encryptionType(i) != ENC_TYPE_NONE ? "true" : "false") + "}";
    }
    json += "]";
    server.send(200, "application/json", json);
}

// ==========================================
// TAB 5: PASSWORD PAGE
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
// TAB 6: FIRMWARE UPDATE PAGE (Wireless OTA)
// ==========================================
static void handleUpdateTab() {
    if (!requireAuth()) return;

    String html = renderHeader("update");

    html += "<div class='card'><h2>Wireless OTA Firmware Update</h2>";
    html += "<p class='hint'>Upload a new compiled <code>.bin</code> firmware file to wirelessly update the terminal software over Wi-Fi.</p>";
    html += "<form method='POST' action='/update' enctype='multipart/form-data'>";
    html += "<label>Select Firmware File (.bin):</label>";
    html += "<input type='file' name='update' accept='.bin' required>";
    html += "<input type='submit' class='btn btn-danger' value='Upload & Flash Firmware'>";
    html += "</form></div>";

    html += renderFooter();
    server.send(200, "text/html", html);
}

// ==========================================
// ACTION HANDLERS
// ==========================================

static void handleSaveHomeWeb() {
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

    String msgHtml = changed ? "<h2>✓ Home Settings Updated Live!</h2><p>Changes saved and applied in real-time.</p>" 
                             : "<h2>ℹ️ No Changes Detected</h2><p>Submitted values match current settings - unchanged.</p>";

    String html = "<!DOCTYPE html><html><head><meta charset='UTF-8'><meta http-equiv='refresh' content='2;url=/'><style>body{font-family:-apple-system,BlinkMacSystemFont,sans-serif;background:#f4f6f9;text-align:center;padding:50px;} .card{background:#fff;padding:30px;border-radius:12px;display:inline-block;box-shadow:0 4px 15px rgba(0,0,0,0.1);} h2{color:" + String(changed ? "#10b981" : "#1e3a5f") + ";margin-top:0;} a{color:#1e3a5f;text-decoration:none;font-weight:bold;}</style></head><body><div class='card'>" + msgHtml + "<p><a href='/'>Returning to Home...</a></p></div></body></html>";
    server.send(200, "text/html", html);
}

static void handleSaveWifiWeb() {
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

    String msgHtml = anyChanged ? "<h2>✓ Wi-Fi Settings Updated Live!</h2><p>Network credentials updated and applied without rebooting.</p>" 
                                : "<h2>ℹ️ No Changes Detected</h2><p>Wi-Fi credentials match current settings - unchanged.</p>";

    String html = "<!DOCTYPE html><html><head><meta charset='UTF-8'><meta http-equiv='refresh' content='2;url=/wifi'><style>body{font-family:-apple-system,BlinkMacSystemFont,sans-serif;background:#f4f6f9;text-align:center;padding:50px;} .card{background:#fff;padding:30px;border-radius:12px;display:inline-block;box-shadow:0 4px 15px rgba(0,0,0,0.1);} h2{color:" + String(anyChanged ? "#10b981" : "#1e3a5f") + ";margin-top:0;} a{color:#1e3a5f;text-decoration:none;font-weight:bold;}</style></head><body><div class='card'>" + msgHtml + "<p><a href='/wifi'>Returning to AP & Wifi...</a></p></div></body></html>";
    server.send(200, "text/html", html);
}

static void handleSavePasswordWeb() {
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

        String html = "<!DOCTYPE html><html lang='en'><head><meta charset='UTF-8'>";
        html += "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
        html += "<title>Rebooting Terminal | Attendance System</title>";
        html += "<style>";
        html += ":root{--primary:#1e3a5f;--accent:#10b981;--bg:#f4f6f9;--card:#ffffff;--text:#1f2937}";
        html += "body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;background:var(--bg);color:var(--text);text-align:center;padding:40px 15px;margin:0}";
        html += ".card{background:var(--card);border-radius:12px;padding:30px;max-width:500px;margin:auto;box-shadow:0 4px 15px rgba(0,0,0,0.08)}";
        html += "h2{color:#dc2626;margin-top:0;font-size:22px}";
        html += ".btn-group{margin-top:25px;display:flex;gap:10px;justify-content:center;flex-wrap:wrap}";
        html += ".btn{background:var(--primary);color:#fff;border:0;padding:12px 20px;border-radius:6px;font-weight:bold;cursor:pointer;text-decoration:none;font-size:14px;transition:background 0.2s}";
        html += ".btn:hover{background:#0f2744}";
        html += ".btn-success{background:var(--accent)}";
        html += ".btn-success:hover{background:#059669}";
        html += ".status{margin:20px 0;font-size:14px;color:var(--primary);font-weight:bold;padding:10px;background:#edf2f7;border-radius:8px}";
        html += "</style></head><body><div class='card'>";
        html += "<h2>🔄 Rebooting Terminal...</h2>";
        html += "<p>Credentials updated successfully. Machine is restarting now...</p>";
        html += "<div id='status' class='status'>⏳ Checking machine availability...</div>";
        html += "<div class='btn-group'>";
        html += "<a href='/' class='btn'>Go to Home Page</a>";
        html += "<button onclick='checkMachine()' class='btn btn-success'>Check Machine Now</button>";
        html += "</div>";
        html += "</div>";

        html += "<script>";
        html += "var attempts = 0;";
        html += "function checkMachine(){";
        html += "attempts++;";
        html += "var statusEl = document.getElementById('status');";
        html += "statusEl.style.color = '#1e3a5f';";
        html += "statusEl.textContent = '⏳ Checking machine availability... (Attempt ' + attempts + ')';";
        html += "fetch('/', { method: 'GET', cache: 'no-store' })";
        html += ".then(function(r){";
        html += "if (r.ok || r.status === 401) {";
        html += "statusEl.style.color = '#10b981';";
        html += "statusEl.textContent = '✓ Machine is ONLINE! Redirecting to Home...';";
        html += "setTimeout(function(){ window.location.href = '/'; }, 1000);";
        html += "} else {";
        html += "setTimeout(checkMachine, 2000);";
        html += "}";
        html += "})";
        html += ".catch(function(){";
        html += "setTimeout(checkMachine, 2000);";
        html += "});";
        html += "}";
        html += "setTimeout(checkMachine, 3000);";
        html += "</script>";

        html += "</body></html>";
        server.send(200, "text/html", html);

        delay(1000);
        ESP.restart();
    } else {
        String html = "<!DOCTYPE html><html><head><meta charset='UTF-8'><meta http-equiv='refresh' content='2;url=/password'><style>body{font-family:-apple-system,BlinkMacSystemFont,sans-serif;background:#f4f6f9;text-align:center;padding:50px;} .card{background:#fff;padding:30px;border-radius:12px;display:inline-block;box-shadow:0 4px 15px rgba(0,0,0,0.1);} h2{color:#1e3a5f;margin-top:0;} a{color:#1e3a5f;text-decoration:none;font-weight:bold;}</style></head><body><div class='card'><h2>ℹ️ No Changes Detected</h2><p>Username and password match current credentials - no reboot required.</p><p><a href='/password'>Returning to Password...</a></p></div></body></html>";
        server.send(200, "text/html", html);
    }
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
    httpUpdater.setup(&server, "/update", config->portal_user, config->portal_pass);

    server.on("/", handleHomeTab);
    server.on("/write", handleWriteTab);
    server.on("/queue", handleQueueTab);
    server.on("/wifi", handleWifiTab);
    server.on("/wifi_scan", handleWifiScanApi);
    server.on("/password", handlePasswordTab);
    server.on("/update", HTTP_GET, handleUpdateTab);
    
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
