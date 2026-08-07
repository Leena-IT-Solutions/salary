#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
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
#include <U8g2lib.h>
#include <FS.h>
#include <ArduinoJson.h>
#include <ESP8266WebServer.h>
#include <Ticker.h>
#include "time.h"
#include <Adafruit_PN532.h>

static Adafruit_PN532 pn532(-1, -1, &Wire);

class NdefRecordCompat {
public:
    String _payload;
    byte getPayloadLength() { return _payload.length(); }
    void getPayload(byte *out) {
        memcpy(out, _payload.c_str(), _payload.length());
    }
};

class NdefMessageCompat {
public:
    String _text;
    void addTextRecord(const String &val) { _text = val; }
    NdefRecordCompat getRecord(int idx) {
        NdefRecordCompat r;
        r._payload = String((char)0x02) + "en" + _text;
        return r;
    }
};

typedef NdefMessageCompat NdefMessage;
typedef NdefRecordCompat NdefRecord;

class NfcTagCompat {
public:
    String _uidStr;
    String _text;
    bool _hasNdef;

    String getUidString() { return _uidStr; }
    bool hasNdefMessage() { return _hasNdef; }
    NdefMessageCompat getNdefMessage() {
        NdefMessageCompat msg;
        msg._text = _text;
        return msg;
    }
};

typedef NfcTagCompat NfcTag;

class NfcAdapterCompat {
private:
    uint8_t _lastUid[7];
    uint8_t _lastUidLen;
public:
    void begin() {
        pn532.begin();
        pn532.SAMConfig();
    }

    bool tagPresent(uint16_t timeout = 50) {
        _lastUidLen = 0;
        return pn532.readPassiveTargetID(PN532_MIFARE_ISO14443A, _lastUid, &_lastUidLen, timeout);
    }

    NfcTag read() {
        NfcTag tag;
        tag._hasNdef = false;

        char buf[20] = "";
        char *ptr = buf;
        for (uint8_t i = 0; i < _lastUidLen; i++) {
            if (i > 0) ptr += sprintf(ptr, " ");
            ptr += sprintf(ptr, "%02X", _lastUid[i]);
        }
        tag._uidStr = String(buf);

        uint8_t keyNDEF[6] = {0xD3, 0xF7, 0xD3, 0xF7, 0xD3, 0xF7};
        uint8_t keyDef[6]  = {0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF};
        uint8_t keyMAD[6]  = {0xA0, 0xA1, 0xA2, 0xA3, 0xA4, 0xA5};
        uint8_t keyNDEFB[6] = {0xF7, 0xD3, 0xF7, 0xD3, 0xF7, 0xD3};

        bool auth = pn532.mifareclassic_AuthenticateBlock(_lastUid, _lastUidLen, 4, 0, keyNDEF);
        if (!auth) { pn532.inListPassiveTarget(); auth = pn532.mifareclassic_AuthenticateBlock(_lastUid, _lastUidLen, 4, 0, keyDef); }
        if (!auth) { pn532.inListPassiveTarget(); auth = pn532.mifareclassic_AuthenticateBlock(_lastUid, _lastUidLen, 4, 1, keyNDEFB); }
        if (!auth) { pn532.inListPassiveTarget(); auth = pn532.mifareclassic_AuthenticateBlock(_lastUid, _lastUidLen, 4, 0, keyMAD); }

        if (auth) {
            uint8_t raw[32];
            memset(raw, 0, sizeof(raw));
            if (pn532.mifareclassic_ReadDataBlock(4, &raw[0])) {
                pn532.mifareclassic_ReadDataBlock(5, &raw[16]);
                
                size_t start = 0;
                for (size_t i = 2; i < 28; i++) {
                    if (raw[i] == 0x02 && raw[i+1] == 'e' && raw[i+2] == 'n') {
                        start = i + 3;
                        break;
                    }
                }
                if (start == 0) {
                    for (size_t i = 0; i < 32; i++) {
                        if (isalnum((char)raw[i])) { start = i; break; }
                    }
                }

                String text = "";
                for (size_t i = start; i < 32; i++) {
                    char c = (char)raw[i];
                    if (c == 0xFE || c == '\0' || (uint8_t)c < 32 || (uint8_t)c > 126) break;
                    text += c;
                }
                if (text.length() > 0) {
                    tag._text = text;
                    tag._hasNdef = true;
                }
            }
        }
        return tag;
    }

    bool write(const NdefMessageCompat &msg) {
        uint8_t keyDef[6]   = {0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF};
        uint8_t keyMAD[6]   = {0xA0, 0xA1, 0xA2, 0xA3, 0xA4, 0xA5};
        uint8_t keyNDEF[6]  = {0xD3, 0xF7, 0xD3, 0xF7, 0xD3, 0xF7};
        uint8_t keyNDEFB[6] = {0xF7, 0xD3, 0xF7, 0xD3, 0xF7, 0xD3};

        // Format Sector 0 MAD
        bool authMAD = pn532.mifareclassic_AuthenticateBlock(_lastUid, _lastUidLen, 1, 0, keyMAD);
        if (!authMAD) { pn532.inListPassiveTarget(); authMAD = pn532.mifareclassic_AuthenticateBlock(_lastUid, _lastUidLen, 1, 0, keyDef); }
        if (authMAD) {
            uint8_t mad1[16] = {0x14, 0x01, 0x03, 0xE1, 0x03, 0xE1, 0x03, 0xE1, 0x03, 0xE1, 0x03, 0xE1, 0x03, 0xE1, 0x03, 0xE1};
            uint8_t mad2[16] = {0x03, 0xE1, 0x03, 0xE1, 0x03, 0xE1, 0x03, 0xE1, 0x03, 0xE1, 0x03, 0xE1, 0x03, 0xE1, 0x03, 0xE1};
            pn532.mifareclassic_WriteDataBlock(1, mad1);
            pn532.mifareclassic_WriteDataBlock(2, mad2);
        }

        // Format Sector 1 Trailer
        pn532.inListPassiveTarget();
        bool authTrailer = pn532.mifareclassic_AuthenticateBlock(_lastUid, _lastUidLen, 7, 0, keyDef);
        if (!authTrailer) { pn532.inListPassiveTarget(); authTrailer = pn532.mifareclassic_AuthenticateBlock(_lastUid, _lastUidLen, 7, 0, keyNDEF); }
        if (!authTrailer) { pn532.inListPassiveTarget(); authTrailer = pn532.mifareclassic_AuthenticateBlock(_lastUid, _lastUidLen, 7, 0, keyMAD); }

        if (authTrailer) {
            uint8_t trailer[16] = {0xD3, 0xF7, 0xD3, 0xF7, 0xD3, 0xF7, 0x7F, 0x07, 0x88, 0x40, 0xF7, 0xD3, 0xF7, 0xD3, 0xF7, 0xD3};
            pn532.mifareclassic_WriteDataBlock(7, trailer);
        }

        // Authenticate Block 4 for Writing NDEF Payload
        pn532.inListPassiveTarget();
        bool auth4 = pn532.mifareclassic_AuthenticateBlock(_lastUid, _lastUidLen, 4, 0, keyNDEF);
        if (!auth4) { pn532.inListPassiveTarget(); auth4 = pn532.mifareclassic_AuthenticateBlock(_lastUid, _lastUidLen, 4, 0, keyDef); }
        if (!auth4) { pn532.inListPassiveTarget(); auth4 = pn532.mifareclassic_AuthenticateBlock(_lastUid, _lastUidLen, 4, 1, keyNDEFB); }

        if (!auth4) return false;

        size_t len = msg._text.length();
        if (len > 20) len = 20;

        uint8_t buf[32];
        memset(buf, 0, sizeof(buf));
        buf[0] = 0x03; buf[1] = len + 7; buf[2] = 0xD1; buf[3] = 0x01; buf[4] = len + 3;
        buf[5] = 'T'; buf[6] = 0x02; buf[7] = 'e'; buf[8] = 'n';
        memcpy(&buf[9], msg._text.c_str(), len);
        buf[9 + len] = 0xFE;

        bool ok1 = pn532.mifareclassic_WriteDataBlock(4, &buf[0]);
        bool ok2 = pn532.mifareclassic_WriteDataBlock(5, &buf[16]);
        return (ok1 && ok2);
    }

    bool format() { return true; }
    bool erase() {
        NdefMessageCompat msg;
        msg._text = "";
        return write(msg);
    }
    bool clean() { return erase(); }
};

static NfcAdapterCompat nfc;

Ticker timer1;
Ticker timer2;

const char* ntpServer = "in.pool.ntp.org";
const long gmtOffset = 0;
const int daylightOffset = 19800;

#define i2c_Address 0x3c
#define SCREEN_WIDTH 128
#define SCREEN_HEIGHT 64

class OledCompat {
private:
    U8G2_SH1106_128X64_NONAME_F_HW_I2C _u8g2;
    int _cursorX = 0;
    int _cursorY = 0;
    int _textSize = 1;
public:
    OledCompat() : _u8g2(U8G2_R0, U8X8_PIN_NONE) {}

    void begin(uint8_t addr, bool reset) {
        _u8g2.setI2CAddress(addr << 1);
        _u8g2.begin();
        _u8g2.setFontMode(0);
        _u8g2.setDrawColor(1);
    }
    void clearDisplay() { _u8g2.clearBuffer(); }
    void setTextColor(int c) {}
    void setTextSize(int s) { _textSize = s; }
    void setCursor(int x, int y) { _cursorX = x; _cursorY = y; }

    void println(const String &str) {
        if (_textSize == 3)      _u8g2.setFont(u8g2_font_logisoso24_tf);
        else if (_textSize == 2) _u8g2.setFont(u8g2_font_logisoso20_tf);
        else                     _u8g2.setFont(u8g2_font_6x10_tf);
        _u8g2.drawStr(_cursorX, _cursorY + (_textSize == 1 ? 8 : 16), str.c_str());
    }

    void println(IPAddress ip) { println(ip.toString()); }

    void display() { _u8g2.sendBuffer(); }
};

static OledCompat oled;
#define SH110X_WHITE 1

String tagId = "";
String tagMs = "";
String dt = "";
String tim = "";

#define swi D4
#define buz D7
int swiStart = 0;
int swiEnd = 0;
int duration = 0;

const String settings_filename = "/settings.json";
String ap_ssid = "attendance";
String ap_pswd = "123456789";
String wf_ssid = "";
String wf_pswd = "";
String op_mode = "";
String sr_host = "";
String card_value = "";
String api_token = "";

String webpage = "";
IPAddress ipAddress;
bool isNetwork = false;

ESP8266WebServer server(80);

void notFound(){
  server.send(404, "text/html", "<h1>Page Not Found</h1>");
}

static const char DEFAULT_WEBPAGE_HTML[] PROGMEM = R"rawliteral(
<!DOCTYPE html>
<html>
<head>
    <title>Attendance System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #f4f6f9; color: #333; padding: 15px; }
        .max-500 { margin: 0 auto; max-width: 500px; width: 100%; background: #fff; border-radius: 12px; padding: 25px; box-shadow: 0 4px 15px rgba(0,0,0,0.08); }
        .text-center { text-align: center; }
        .mb-1 { margin-bottom: 5px; }
        .mb-3 { margin-bottom: 18px; }
        .mb-4 { margin-bottom: 22px; }
        .p-2 { padding: 10px; }
        .w-full { width: 100%; }
        h1 { color: #1e3a5f; font-size: 22px; margin-bottom: 4px; }
        p.subtitle { color: #64748b; font-size: 13px; margin-bottom: 20px; }
        h3 { color: #1e3a5f; border-bottom: 2px solid #e2e8f0; padding-bottom: 6px; margin-bottom: 15px; font-size: 15px; font-weight: 700; }
        
        .nav-tabs { display: flex; gap: 6px; border-bottom: 2px solid #e2e8f0; padding-bottom: 10px; }
        .nav-tab { flex: 1; border: 0; background: #f1f5f9; color: #475569; padding: 10px 4px; border-radius: 8px; font-weight: 600; font-size: 13px; cursor: pointer; transition: all 0.2s; }
        .nav-tab.active { background: #1e3a5f; color: #fff; box-shadow: 0 2px 8px rgba(30,58,95,0.25); }
        
        .tab-content { display: none; }
        .tab-content.active { display: block; }
        
        label, div.mb-1 { font-size: 13px; font-weight: 600; color: #475569; }
        input[type=text], input[type=password], select { width: 100%; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; margin-top: 4px; outline: none; transition: border-color 0.2s; }
        input:focus, select:focus { border-color: #1e3a5f; }
        
        .btn { background: #1e3a5f; color: #fff; border: 0; padding: 12px; border-radius: 8px; font-weight: 700; cursor: pointer; width: 100%; font-size: 14px; margin-top: 8px; transition: background 0.2s; }
        .btn:hover { background: #0f2744; }
        .btn-write { background: #2563eb; }
        .btn-write:hover { background: #1d4ed8; }
        .btn-save { background: #16a34a; }
        .btn-save:hover { background: #15803d; }
    </style>
</head>
<body>
    <div class="max-500">
        <div class="text-center">
            <h1>Attendance System</h1>
            <p class="subtitle">Powered By Leena IT Solutions</p>
        </div>

        <div class="nav-tabs mb-4">
            <button id="btn-home" class="nav-tab active" onclick="switchTab('home')">🏠 Home</button>
            <button id="btn-write" class="nav-tab" onclick="switchTab('write')">💳 Write Card</button>
            <button id="btn-wifi" class="nav-tab" onclick="switchTab('wifi')">📶 Wi-Fi & AP</button>
        </div>

        <!-- Page 1: Home -->
        <div id="tab-home" class="tab-content active">
            <h3 class="mb-3">Terminal Settings</h3>
            <div class="mb-3">
                <div class="mb-1">Operation Mode</div>
                <select name="op_mode" id="op_mode">
                    <option value="Setup">Setup</option>
                    <option value="Read">Read</option>
                    <option value="Write">Write</option>
                    <option value="Format">Format</option>
                    <option value="Delete">Delete</option>
                    <option value="Clear">Clear</option>
                </select>
            </div>
            <div class="mb-3">
                <div class="mb-1">Host URI</div>
                <input id="sr_host" name="sr_host" type="text" placeholder="https://domain.com/attendance/save">
            </div>
            <div class="mb-3">
                <div class="mb-1">Bearer API Token</div>
                <input id="api_token" name="api_token" type="password" placeholder="Sanctum Bearer Token">
            </div>
            <button onclick="saveData(false)" class="btn">💾 Save Terminal Settings</button>
        </div>

        <!-- Page 2: Write Card -->
        <div id="tab-write" class="tab-content">
            <h3 class="mb-3">Write Card / Tag</h3>
            <div class="mb-3">
                <div class="mb-1">Employee Code / Card Value</div>
                <input id="card_value" name="card_value" type="text" placeholder="e.g. SV001">
            </div>
            <button onclick="writeCard()" id="cardButton" class="btn btn-write">⚡ Write Card to NFC Tag</button>
        </div>

        <!-- Page 3: Wi-Fi & AP Setup -->
        <div id="tab-wifi" class="tab-content">
            <h3 class="mb-3">Wi-Fi Connection</h3>
            <div class="mb-3">
                <div class="mb-1">Wi-Fi Router SSID</div>
                <input id="wf_ssid" name="wf_ssid" type="text" placeholder="Router SSID">
            </div>
            <div class="mb-3">
                <div class="mb-1">Wi-Fi Router Password</div>
                <input id="wf_pswd" name="wf_pswd" type="password" placeholder="Router Password">
            </div>

            <h3 class="mb-3" style="margin-top: 25px;">Access Point (AP) Setup</h3>
            <div class="mb-3">
                <div class="mb-1">AP Accesspoint SSID</div>
                <input id="ap_ssid" name="ap_ssid" type="text" placeholder="attendance">
            </div>
            <div class="mb-3">
                <div class="mb-1">AP Accesspoint Password</div>
                <input id="ap_pswd" name="ap_pswd" type="password" placeholder="123456789">
            </div>
            <button onclick="saveData(true)" class="btn btn-save">🔄 Save & Restart Machine</button>
        </div>
    </div>

    <script>
        var url = "/settings";
        var save_url = "/save";
        setData();

        function switchTab(tabName) {
            document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
            document.querySelectorAll('.nav-tab').forEach(el => el.classList.remove('active'));
            document.getElementById('tab-' + tabName).classList.add('active');
            document.getElementById('btn-' + tabName).classList.add('active');
        }

        function setData() {
            var xmlHttp = new XMLHttpRequest();
            xmlHttp.open("GET", url, false);
            xmlHttp.send(null);
            if (xmlHttp.responseText && xmlHttp.responseText.length > 0) {
                try {
                    let data = JSON.parse(xmlHttp.responseText);
                    if(data.ap_ssid) document.getElementById("ap_ssid").value = data.ap_ssid;
                    if(data.ap_pswd) document.getElementById("ap_pswd").value = data.ap_pswd;
                    if(data.wf_ssid) document.getElementById("wf_ssid").value = data.wf_ssid;
                    if(data.wf_pswd) document.getElementById("wf_pswd").value = data.wf_pswd;
                    if(data.op_mode) document.getElementById("op_mode").value = data.op_mode;
                    if(data.sr_host) document.getElementById("sr_host").value = data.sr_host;
                    if(data.card_value) document.getElementById("card_value").value = data.card_value;
                    if(data.api_token) document.getElementById("api_token").value = data.api_token;
                } catch(e){}
            }
        }

        function saveData(isWifiSave) {
            let data = {
                ap_ssid: document.getElementById("ap_ssid").value,
                ap_pswd: document.getElementById("ap_pswd").value,
                wf_ssid: document.getElementById("wf_ssid").value,
                wf_pswd: document.getElementById("wf_pswd").value,
                op_mode: document.getElementById("op_mode").value,
                sr_host: document.getElementById("sr_host").value,
                card_value: document.getElementById("card_value").value,
                api_token: document.getElementById("api_token").value
            };
            var xmlHttp = new XMLHttpRequest();
            xmlHttp.open("GET", save_url + "?q=" + encodeURIComponent(JSON.stringify(data)), false);
            xmlHttp.send(null);
            if (!isWifiSave) {
                alert("Settings Saved Successfully!");
            }
        }

        function writeCard() {
            document.getElementById("cardButton").innerHTML = "⌛ Waiting for Card Tap...";
            document.getElementById("op_mode").value = "Write";
            saveData(false);
            setTimeout(checkStatus, 1000);
        }

        function checkStatus() {
            var xmlHttp = new XMLHttpRequest();
            xmlHttp.open("GET", url, false);
            xmlHttp.send(null);
            if (xmlHttp.responseText) {
                let data = JSON.parse(xmlHttp.responseText);
                if (data.op_mode == "Write") {
                    setTimeout(checkStatus, 1000);
                } else {
                    setData();
                    document.getElementById("cardButton").innerHTML = "⚡ Write Card to NFC Tag";
                }
            }
        }
    </script>
</body>
</html>
)rawliteral";

void getWebpage(){
  File file = SPIFFS.open("/attendanceSettingsPage.html", "r");
  if (file){
    while (file.available()){
      webpage = file.readString();
    }
    file.close();
  }
  if (webpage.length() == 0) {
    webpage = FPSTR(DEFAULT_WEBPAGE_HTML);
  }
}

void startSoftAP(){
  if(WiFi.softAP(ap_ssid, ap_pswd)){
    ipAddress = WiFi.softAPIP();
    startWebServer();
    //Serial.println("");
    //Serial.println(ipAddress);  
  }
}

void startWiFi(){
  WiFi.begin(wf_ssid, wf_pswd);
  // WL_CONNECTED | WL_IDLE_STATUS | WL_CONNECT_FAILED
  while(WiFi.status() != WL_CONNECTED){
    //Serial.print(".");
    delay(200);
    if(millis() > 20000){
      break;
    }
  }
  if(WiFi.status() != WL_CONNECTED){
    startSoftAP();
  } else {
    ipAddress = WiFi.localIP();
    //Serial.println(ipAddress);
    configTime(gmtOffset, daylightOffset, ntpServer);
    printLocalTime();
    isNetwork = true;
    startWebServer();
  }
}

void printLocalTime(){
  struct tm timeinfo;
  if(!getLocalTime(&timeinfo)){
    //Serial.println("Failed to obtain time");
    return;
  }

  // Serial.println(timeinfo.tm_sec);
  // Serial.println(timeinfo.tm_min);
  // Serial.println(timeinfo.tm_hour);
  // Serial.println(timeinfo.tm_mday);
  // Serial.println(timeinfo.tm_mon);
  // Serial.println(timeinfo.tm_year);
  // Serial.println(timeinfo.tm_wday);
  // Serial.println(timeinfo.tm_yday);
  // Serial.println(timeinfo.tm_isdst);

  char dtbuf [12];
  strftime (dtbuf, 80, "%F", &timeinfo);
  dt = dtbuf;
  //Serial.println(dt);

  char timbuf [12];
  strftime (timbuf, 80, "%T", &timeinfo);
  tim = timbuf;
  //Serial.println(tim);

}

void startWebServer(){
  server.on("/", HTTP_GET, [](){
    server.send(200, "text/html", webpage);
  });
  server.on("/settings", HTTP_GET, [](){
    String message = getSettings();
    server.send(200, "application/json", message);
  });
  server.on("/save", HTTP_GET, [](){
    String message = "";
    if(server.hasArg("q")){
      message = server.arg("q");
      saveSettings(message);
    }
    server.send(200, "application/json", message);
  });
  server.onNotFound(notFound);
  server.begin();
}

void saveSettings(String msg){
    String old_ap_ssid = ap_ssid;
    String old_ap_pswd = ap_pswd;
    String old_wf_ssid = wf_ssid;
    String old_wf_pswd = wf_pswd;

    File fl = SPIFFS.open(settings_filename, "w");
    if(!fl){
      return;
    }
    fl.print(msg);
    fl.close();

    setSettings();

    // Immediately refresh OLED display screen with new mode!
    writeCompanyName();

    bool wifiOrApChanged = (old_ap_ssid != ap_ssid) || 
                           (old_ap_pswd != ap_pswd) || 
                           (old_wf_ssid != wf_ssid) || 
                           (old_wf_pswd != wf_pswd);

    if (wifiOrApChanged) {
      server.send(200, "text/html", "<!DOCTYPE html><html><head><meta name='viewport' content='width=device-width, initial-scale=1.0'><style>body{font-family:-apple-system,sans-serif;background:#f4f6f9;text-align:center;padding:50px;} .card{background:#fff;padding:30px;border-radius:12px;display:inline-block;box-shadow:0 4px 15px rgba(0,0,0,0.1);} h2{color:#dc2626;margin-top:0;}</style></head><body><div class='card'><h2>🔄 Rebooting Machine...</h2><p>Wi-Fi / AP settings updated. Machine is restarting to apply new network parameters...</p></div><script>setTimeout(function(){ window.location.href='/'; }, 6000);</script></body></html>");
      delay(1000);
      ESP.restart();
    }
}

void updateAndSaveSettings(){
  StaticJsonDocument<1024> doc;
  doc["ap_ssid"] = ap_ssid;
  doc["ap_pswd"] = ap_pswd;
  doc["wf_ssid"] = wf_ssid;
  doc["wf_pswd"] = wf_pswd;
  doc["op_mode"] = op_mode;
  doc["sr_host"] = sr_host;
  doc["card_value"] = card_value;
  doc["api_token"] = api_token;
  String str = "";
  serializeJson(doc, str);
  saveSettings(str);
}

String getSettings(){
  String str = "";
  File file = SPIFFS.open(settings_filename, "r");
  if (file){
    str = file.readString();
    file.close();
    str.trim();
  }
  if (str.length() == 0) {
    StaticJsonDocument<1024> doc;
    doc["ap_ssid"] = (ap_ssid.length() > 0) ? ap_ssid : "attendance";
    doc["ap_pswd"] = (ap_pswd.length() > 0) ? ap_pswd : "123456789";
    doc["wf_ssid"] = wf_ssid;
    doc["wf_pswd"] = wf_pswd;
    doc["op_mode"] = (op_mode.length() > 0) ? op_mode : "Read";
    doc["sr_host"] = (sr_host.length() > 0) ? sr_host : "https://payroll.sarvodayavidyalay.com/attendance/save";
    doc["card_value"] = card_value;
    doc["api_token"] = api_token;
    serializeJson(doc, str);
  }
  return str;
}

void setSettings(){
  String str = getSettings();
  DynamicJsonDocument doc(1024);
  DeserializationError error = deserializeJson(doc, str);
  if(error){
    return;
  }
  if (doc.containsKey("ap_ssid")) ap_ssid = String(doc["ap_ssid"]);
  if (doc.containsKey("ap_pswd")) ap_pswd = String(doc["ap_pswd"]);
  if (doc.containsKey("wf_ssid")) wf_ssid = String(doc["wf_ssid"]);
  if (doc.containsKey("wf_pswd")) wf_pswd = String(doc["wf_pswd"]);
  if (doc.containsKey("op_mode")) op_mode = String(doc["op_mode"]);
  if (doc.containsKey("sr_host")) sr_host = String(doc["sr_host"]);
  if (doc.containsKey("card_value")) card_value = String(doc["card_value"]);
  if (doc.containsKey("api_token")) api_token = String(doc["api_token"]);

  if (op_mode.length() == 0) op_mode = "Read";
  if (sr_host.length() == 0) sr_host = "https://payroll.sarvodayavidyalay.com/attendance/save";
}

void sendDataToServer(){

  if ((WiFi.status() == WL_CONNECTED)) {
    std::unique_ptr<BearSSL::WiFiClientSecure>client(new BearSSL::WiFiClientSecure);
    //client->setFingerprint(fingerprint);
    client->setInsecure();
    HTTPClient https;

    //Serial.print("[HTTPS] begin...\n");

    // String encodeduri = urlEncode("tagid=" + tagId + "&tagms=" + tagMs + "&dt=" + dt + "&tim=" + tim);

    String encodeduri = "tagid=" + urlEncode(tagId) + "&tagms=" + urlEncode(tagMs) + "&dt=" + urlEncode(dt) + "&tim=" + urlEncode(tim);

    String uri = sr_host + "?" + encodeduri;

    //Serial.print(uri);

    if (https.begin(*client, uri)) {
      if (api_token.length() > 0) {
        https.addHeader("Authorization", "Bearer " + api_token);
      }
      //Serial.print("[HTTPS] GET...\n");
      int httpCode = https.GET();
      //Serial.println(httpCode);
      if (httpCode > 0) {
        //Serial.printf("[HTTPS] GET... code: %d\n", httpCode);
        if (httpCode == HTTP_CODE_OK || httpCode == HTTP_CODE_MOVED_PERMANENTLY) {
          String payload = https.getString();
          Serial.println(payload);
        }
      } else {
        //Serial.printf("[HTTPS] GET... failed, error: %s\n", https.errorToString(httpCode).c_str());
      }
      https.end();
    } else {
      //Serial.printf("[HTTPS] Unable to connect\n");
    }
  }
}

void readCard(){
  NfcTag tag = nfc.read();
  tagId = tag.getUidString();
  if(tag.hasNdefMessage()){
    NdefRecord record = tag.getNdefMessage().getRecord(0);
    byte length = record.getPayloadLength();
    byte payload[length + 1];
    memset(payload, 0, sizeof(payload));
    record.getPayload(payload);
    payload[length] = '\0';
    tagMs = String((char *)payload);
    if (tagMs.length() >= 3) {
      tagMs = tagMs.substring(3);
    }
    tagMs.trim();
    showMessage();
    printLocalTime();
    sendDataToServer();
    delay(2000); // Keep Employee Code visible on screen for 2 seconds
  }
  writeCompanyName();
}

void writeCard(){
    NdefMessage message = NdefMessage();
    message.addTextRecord(card_value);
    //message.addUriRecord("http://leenaitsolutions.com");
    bool success = nfc.write(message);
    delay(1000);
    if (success) {
      op_mode = "Read";
      card_value = "";
      updateAndSaveSettings();
      //Serial.println("\nCard Written");
    } else {
      //Serial.println("\nUnsuccessful Write.");
    }
    delay(1000);
}

void formatCard(){
  bool success = nfc.format();
  if (success) {
    //Serial.println("\nThe card (tag) successfully formatted in the NTAG.");
  } else {
    //Serial.println("\nUnsuccessful formatting.");
  }
  delay(1000);
}

void deleteCardMessage(){
  bool success = nfc.erase();
  if (success) {
    //Serial.println("\nDeleted");
  } else {
    //Serial.println("\nDelete Unsuccessful.");
  }
  delay(1000);
}

void clearCard(){
  bool success = nfc.clean();
  if (success) {
    //Serial.println("\nCard Cleared");
  } else {
    //Serial.println("\nClear Unsuccessful.");
  }
  delay(1000);
}

void accessCard(){
  while(nfc.tagPresent()){
    digitalWrite(buz, HIGH);
    if(op_mode == "Read"){ readCard(); }
    if(op_mode == "Write"){ writeCard(); }
    if(op_mode == "Format"){ formatCard(); }
    if(op_mode == "Delete"){ deleteCardMessage(); }
    if(op_mode == "Clear"){ clearCard(); }
    digitalWrite(buz, LOW);
  }
}

void drawScreenWithMiddleText(const String &middleText) {
  oled.clearDisplay();
  
  // Header at top
  oled.setTextSize(1);
  oled.setCursor(10, 0);
  oled.println(F("Sarvodaya Vidyalay"));

  // Middle text (Time or Employee Code tagMs)
  int textLength = middleText.length();
  int startX = (128 - (textLength * 12)) / 2;
  if (startX < 0) startX = 0;

  oled.setTextSize(2);
  oled.setCursor(startX, 22);
  oled.println(middleText);

  // Footer status bar
  oled.setTextSize(1);
  oled.setCursor(0, 52);
  oled.println(ipAddress);
  oled.setCursor(118, 52);
  oled.println(op_mode.length() > 0 ? String(op_mode[0]) : "R");

  oled.display();
}

void writeCompanyName(){
  String clockText = (tim.length() > 0) ? tim : "00:00:00";
  drawScreenWithMiddleText(clockText);
}

void writeBrandName(){
  oled.clearDisplay();
  oled.setTextSize(3);
  oled.setCursor(30,15);
  oled.println(F("LITS"));
  oled.setTextSize(1);
  oled.setCursor(11,44);
  oled.println(F("Leena IT Solutions"));
  oled.display();
}

void showMessage(){
  drawScreenWithMiddleText(tagMs);
}

void readSwitch(){
  if(digitalRead(swi) == 1){
    if(swiStart == 0){
      swiStart = millis();
    } else {
      swiEnd = millis();
    }
  } else {
    if(swiStart != 0 && swiEnd != 0){
      duration = swiEnd - swiStart;
    } else {
      duration = 0;
    }
    swiStart = 0;
    swiEnd = 0;
  }
  if(duration > 0){
    Serial.println(duration);
  }
  if(duration > 3000){
    if(op_mode=="Setup"){
      op_mode = "Read";
    } else {
      op_mode = "Setup";
    }
    updateAndSaveSettings();
  }
}

void hello(){
  if(op_mode=="Read" && isNetwork){
    printLocalTime();
  }
}

void setup() {
  Serial.begin(115200);
  if (!SPIFFS.begin()){
    Serial.println("FS Failed");
  }

  oled.begin(i2c_Address, false);
  oled.clearDisplay();
  oled.setTextColor(SH110X_WHITE);
  writeBrandName();

  pinMode(swi, INPUT);
  pinMode(buz, OUTPUT);

  digitalWrite(buz, LOW);

  setSettings();
  getWebpage();

  if(op_mode == "Setup"){
    startSoftAP();
  } else {
    startWiFi();
  }

  nfc.begin();

  timer1.attach(0.1, readSwitch);
  timer2.attach(1, hello);

}

void loop() {
  server.handleClient();
  writeCompanyName();
  accessCard();
}