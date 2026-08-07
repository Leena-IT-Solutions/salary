#include <ESP8266HTTPClient.h>
#include <ESP8266WiFi.h>
#include <ESP8266mDNS.h>
#include <ESP8266HTTPUpdateServer.h>
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
#include "time.h"
#include <Adafruit_PN532.h>
#include <ArduinoJson.h>
#include <ESP8266WebServer.h>
#include <FS.h>
#include <Ticker.h>
#include <U8g2lib.h>

static Adafruit_PN532 pn532(-1, -1, &Wire);

const String settings_filename = "/settings.json";
String ap_ssid = "attendance";
String ap_pswd = "password";
String wf_ssid = "";
String wf_pswd = "";
String op_mode = "";
String sr_host = "";
String card_value = "";
String api_token = "";
String company_name = "Company";
String domain_name = "attendance.local";
String admin_user = "admin";
String admin_pass = "password";

String webpage = "";
IPAddress ipAddress;
bool isNetwork = false;

ESP8266WebServer server(80);
ESP8266HTTPUpdateServer httpUpdater;

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
    return pn532.readPassiveTargetID(PN532_MIFARE_ISO14443A, _lastUid,
                                     &_lastUidLen, timeout);
  }

  NfcTag read() {
    NfcTag tag;
    tag._hasNdef = false;

    char buf[20] = "";
    char *ptr = buf;
    for (uint8_t i = 0; i < _lastUidLen; i++) {
      if (i > 0)
        ptr += sprintf(ptr, " ");
      ptr += sprintf(ptr, "%02X", _lastUid[i]);
    }
    tag._uidStr = String(buf);

    uint8_t keyNDEF[6] = {0xD3, 0xF7, 0xD3, 0xF7, 0xD3, 0xF7};
    uint8_t keyDef[6] = {0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF};
    uint8_t keyMAD[6] = {0xA0, 0xA1, 0xA2, 0xA3, 0xA4, 0xA5};
    uint8_t keyNDEFB[6] = {0xF7, 0xD3, 0xF7, 0xD3, 0xF7, 0xD3};

    bool auth = pn532.mifareclassic_AuthenticateBlock(_lastUid, _lastUidLen, 4,
                                                      0, keyNDEF);
    if (!auth) {
      pn532.inListPassiveTarget();
      auth = pn532.mifareclassic_AuthenticateBlock(_lastUid, _lastUidLen, 4, 0,
                                                   keyDef);
    }
    if (!auth) {
      pn532.inListPassiveTarget();
      auth = pn532.mifareclassic_AuthenticateBlock(_lastUid, _lastUidLen, 4, 1,
                                                   keyNDEFB);
    }
    if (!auth) {
      pn532.inListPassiveTarget();
      auth = pn532.mifareclassic_AuthenticateBlock(_lastUid, _lastUidLen, 4, 0,
                                                   keyMAD);
    }

    if (auth) {
      uint8_t raw[32];
      memset(raw, 0, sizeof(raw));
      if (pn532.mifareclassic_ReadDataBlock(4, &raw[0])) {
        pn532.mifareclassic_ReadDataBlock(5, &raw[16]);

        size_t start = 0;
        for (size_t i = 2; i < 28; i++) {
          if (raw[i] == 0x02 && raw[i + 1] == 'e' && raw[i + 2] == 'n') {
            start = i + 3;
            break;
          }
        }
        if (start == 0) {
          for (size_t i = 0; i < 32; i++) {
            if (isalnum((char)raw[i])) {
              start = i;
              break;
            }
          }
        }

        String text = "";
        for (size_t i = start; i < 32; i++) {
          char c = (char)raw[i];
          if (c == 0xFE || c == '\0' || (uint8_t)c < 32 || (uint8_t)c > 126)
            break;
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
    uint8_t keyDef[6] = {0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF};
    uint8_t keyMAD[6] = {0xA0, 0xA1, 0xA2, 0xA3, 0xA4, 0xA5};
    uint8_t keyNDEF[6] = {0xD3, 0xF7, 0xD3, 0xF7, 0xD3, 0xF7};
    uint8_t keyNDEFB[6] = {0xF7, 0xD3, 0xF7, 0xD3, 0xF7, 0xD3};

    // Format Sector 0 MAD
    bool authMAD = pn532.mifareclassic_AuthenticateBlock(_lastUid, _lastUidLen,
                                                         1, 0, keyMAD);
    if (!authMAD) {
      pn532.inListPassiveTarget();
      authMAD = pn532.mifareclassic_AuthenticateBlock(_lastUid, _lastUidLen, 1,
                                                      0, keyDef);
    }
    if (authMAD) {
      uint8_t mad1[16] = {0x14, 0x01, 0x03, 0xE1, 0x03, 0xE1, 0x03, 0xE1,
                          0x03, 0xE1, 0x03, 0xE1, 0x03, 0xE1, 0x03, 0xE1};
      uint8_t mad2[16] = {0x03, 0xE1, 0x03, 0xE1, 0x03, 0xE1, 0x03, 0xE1,
                          0x03, 0xE1, 0x03, 0xE1, 0x03, 0xE1, 0x03, 0xE1};
      pn532.mifareclassic_WriteDataBlock(1, mad1);
      pn532.mifareclassic_WriteDataBlock(2, mad2);
    }

    // Format Sector 1 Trailer
    pn532.inListPassiveTarget();
    bool authTrailer = pn532.mifareclassic_AuthenticateBlock(
        _lastUid, _lastUidLen, 7, 0, keyDef);
    if (!authTrailer) {
      pn532.inListPassiveTarget();
      authTrailer = pn532.mifareclassic_AuthenticateBlock(_lastUid, _lastUidLen,
                                                          7, 0, keyNDEF);
    }
    if (!authTrailer) {
      pn532.inListPassiveTarget();
      authTrailer = pn532.mifareclassic_AuthenticateBlock(_lastUid, _lastUidLen,
                                                          7, 0, keyMAD);
    }

    if (authTrailer) {
      uint8_t trailer[16] = {0xD3, 0xF7, 0xD3, 0xF7, 0xD3, 0xF7, 0x7F, 0x07,
                             0x88, 0x40, 0xF7, 0xD3, 0xF7, 0xD3, 0xF7, 0xD3};
      pn532.mifareclassic_WriteDataBlock(7, trailer);
    }

    // Authenticate Block 4 for Writing NDEF Payload
    pn532.inListPassiveTarget();
    bool auth4 = pn532.mifareclassic_AuthenticateBlock(_lastUid, _lastUidLen, 4,
                                                       0, keyNDEF);
    if (!auth4) {
      pn532.inListPassiveTarget();
      auth4 = pn532.mifareclassic_AuthenticateBlock(_lastUid, _lastUidLen, 4, 0,
                                                    keyDef);
    }
    if (!auth4) {
      pn532.inListPassiveTarget();
      auth4 = pn532.mifareclassic_AuthenticateBlock(_lastUid, _lastUidLen, 4, 1,
                                                    keyNDEFB);
    }

    if (!auth4)
      return false;

    size_t len = msg._text.length();
    if (len > 20)
      len = 20;

    uint8_t buf[32];
    memset(buf, 0, sizeof(buf));
    buf[0] = 0x03;
    buf[1] = len + 7;
    buf[2] = 0xD1;
    buf[3] = 0x01;
    buf[4] = len + 3;
    buf[5] = 'T';
    buf[6] = 0x02;
    buf[7] = 'e';
    buf[8] = 'n';
    memcpy(&buf[9], msg._text.c_str(), len);
    buf[9 + len] = 0xFE;

    bool ok1 = pn532.mifareclassic_WriteDataBlock(4, &buf[0]);
    if (len > 7) {
      pn532.mifareclassic_WriteDataBlock(5, &buf[16]);
    }
    return ok1;
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

const char *ntpServer = "in.pool.ntp.org";
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
  void setCursor(int x, int y) {
    _cursorX = x;
    _cursorY = y;
  }

  void println(const String &str) {
    if (_textSize == 3)
      _u8g2.setFont(u8g2_font_logisoso24_tf);
    else if (_textSize == 2)
      _u8g2.setFont(u8g2_font_logisoso20_tf);
    else
      _u8g2.setFont(u8g2_font_6x10_tf);
    _u8g2.drawStr(_cursorX, _cursorY + (_textSize == 1 ? 8 : 16), str.c_str());
  }

  void print(const String &str) { println(str); }
  void print(int val) { println(String(val)); }

  void println(IPAddress ip) { println(ip.toString()); }

  void display() { _u8g2.sendBuffer(); }
};

static OledCompat oled;
#define SH110X_WHITE 1

String tagId = "";
String tagMs = "";
String dt = "";
String tim = "";
String lastScannedCardKey = "";
unsigned long lastScannedTimeMs = 0;

#define swi D4
#define buz D7
int swiStart = 0;
int swiEnd = 0;
int duration = 0;

const String queue_filename = "/queue.json";
unsigned long lastQueueSyncCheck = 0;
bool isQueueSyncing = false;

enum SyncResult { SYNC_SUCCESS, SYNC_SERVER_ERROR, SYNC_CLIENT_ERROR };
unsigned long serverErrorCooldownUntil = 0;
int consecutiveServerErrors = 0;

void startWebServer();
void startSoftAP();
void startWiFi();
void printLocalTime();
void getWebpage();
String getSettings();
void setSettings();
void saveSettings(String msg);
void updateAndSaveSettings();
void writeCompanyName();
void showMessage();
void readCard();
void writeCard();
void formatCard();
void deleteCardMessage();
void clearCard();
void accessCard();
void beep(int count = 1, int durationMs = 80, int delayMs = 80);

String getQueueJSON();
void writeQueueJSON(const String &jsonStr);
int getQueueCount();
bool enqueueRecord(String tId, String tMs, String d, String t);
bool dequeueRecord();
void clearQueue();
void processOfflineQueue();
void factoryReset();
SyncResult sendDataToServerParams(String tId, String tMs, String d, String t);
SyncResult sendDataToServer();

void notFound() { server.send(404, "text/html", "<h1>Page Not Found</h1>"); }

static const char DEFAULT_WEBPAGE_HTML[] PROGMEM = R"rawliteral(
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
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
        .nav-tab { flex: 1; border: 0; background: #f1f5f9; color: #475569; padding: 10px 4px; border-radius: 8px; font-weight: 600; font-size: 13px; cursor: pointer; transition: all 0.2s; display: inline-flex; align-items: center; justify-content: center; }
        .nav-tab.active { background: #1e3a5f; color: #fff; box-shadow: 0 2px 8px rgba(30,58,95,0.25); }
        
        .tab-content { display: none; }
        .tab-content.active { display: block; }
        
        label, div.mb-1 { font-size: 13px; font-weight: 600; color: #475569; }
        input[type=text], input[type=password], select { width: 100%; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; margin-top: 4px; outline: none; transition: border-color 0.2s; }
        input:focus, select:focus { border-color: #1e3a5f; }
        
        .btn { background: #1e3a5f; color: #fff; border: 0; padding: 12px; border-radius: 8px; font-weight: 700; cursor: pointer; width: 100%; font-size: 14px; margin-top: 8px; transition: background 0.2s; display: inline-flex; align-items: center; justify-content: center; }
        .btn:hover { background: #0f2744; }
        .btn-write { background: #2563eb; }
        .btn-write:hover { background: #1d4ed8; }
        .btn-save { background: #16a34a; }
        .btn-save:hover { background: #15803d; }
    </style>
</head>
<body>
    <div class="max-500">
        <div id="toast" style="display:none; background:#dcfce7; color:#15803d; border:1px solid #bbf7d0; padding:10px 14px; border-radius:8px; margin-bottom:15px; font-weight:600; font-size:13px; text-align:center;">✓ Settings Saved Successfully</div>
        <div class="text-center">
            <h1>Attendance System</h1>
            <p class="subtitle">Powered By Leena IT Solutions</p>
        </div>

        <div class="nav-tabs mb-4">
            <button id="btn-home" class="nav-tab active" onclick="window.switchTab('home')"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" style="vertical-align:-2px;margin-right:4px;"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg> Home</button>
            <button id="btn-write" class="nav-tab" onclick="window.switchTab('write')"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" style="vertical-align:-2px;margin-right:4px;"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg> Write</button>
            <button id="btn-wifi" class="nav-tab" onclick="window.switchTab('wifi')"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" style="vertical-align:-2px;margin-right:4px;"><path d="M5 12.55a11 11 0 0 1 14.08 0"></path><path d="M1.42 9a16 16 0 0 1 21.16 0"></path><path d="M8.53 16.11a6 6 0 0 1 6.95 0"></path><line x1="12" y1="20" x2="12.01" y2="20"></line></svg> Wi-Fi</button>
            <button id="btn-update" class="nav-tab" onclick="window.switchTab('update')"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" style="vertical-align:-2px;margin-right:4px;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg> Update</button>
            <button id="btn-security" class="nav-tab" onclick="window.switchTab('security')"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" style="vertical-align:-2px;margin-right:4px;"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg> Security</button>
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
                <div class="mb-1">Company Name</div>
                <input id="company_name" name="company_name" type="text" placeholder="Company">
            </div>
            <div class="mb-3">
                <div class="mb-1">Host URI</div>
                <input id="sr_host" name="sr_host" type="text" placeholder="https://domain.com/attendance/save">
            </div>
            <div class="mb-3">
                <div class="mb-1">Bearer API Token</div>
                <input id="api_token" name="api_token" type="password" placeholder="Sanctum Bearer Token">
            </div>
            <button onclick="window.saveData(false)" class="btn"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" style="vertical-align:-2px;margin-right:4px;"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg> Save Terminal Settings</button>
            
            <div style="margin-top: 25px; border-top: 2px solid #e2e8f0; padding-top: 15px;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                    <h3 style="border:0; margin:0; padding:0;">Offline Pending Queue <span id="queue-badge" style="background:#10b981; color:#fff; font-size:11px; padding:2px 8px; border-radius:12px; margin-left:6px;">0</span></h3>
                    <div>
                        <button onclick="window.syncQueue()" class="btn btn-save" style="padding:6px 12px; font-size:12px; width:auto; margin:0; display:inline-flex;">🔄 Sync Now</button>
                        <button onclick="window.clearQueue()" class="btn" style="padding:6px 12px; font-size:12px; width:auto; margin:0; background:#dc2626; display:inline-flex;">🗑️ Clear</button>
                    </div>
                </div>
                <div id="queue-list-container" style="max-height:180px; overflow-y:auto; background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:10px;">
                    <p id="no-queue-msg" style="font-size:13px; color:#64748b; text-align:center; padding:10px 0;">✓ No pending offline records</p>
                    <table id="queue-table" style="width:100%; font-size:12px; text-align:left; border-collapse:collapse; display:none;">
                        <thead>
                            <tr style="border-bottom:1px solid #cbd5e1; color:#475569;">
                                <th style="padding:4px;">Card UID</th>
                                <th style="padding:4px;">Emp Code</th>
                                <th style="padding:4px;">Date</th>
                                <th style="padding:4px;">Time</th>
                            </tr>
                        </thead>
                        <tbody id="queue-tbody"></tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Page 2: Write Card -->
        <div id="tab-write" class="tab-content">
            <h3 class="mb-3">Write Card / Tag</h3>
            <div class="mb-3">
                <div class="mb-1">Employee Code / Card Value</div>
                <input id="card_value" name="card_value" type="text" placeholder="e.g. SV001">
            </div>
            <button onclick="window.writeCard()" id="cardButton" class="btn btn-write"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" style="vertical-align:-2px;margin-right:4px;"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg> Write Card to NFC Tag</button>
        </div>

        <!-- Page 3: Wi-Fi & AP Setup -->
        <div id="tab-wifi" class="tab-content">
            <h3 class="mb-3">Domain Name (mDNS)</h3>
            <div class="mb-3">
                <div class="mb-1">Domain Name (mDNS)</div>
                <input id="domain_name" name="domain_name" type="text" placeholder="attendance.local">
            </div>

            <h3 class="mb-3" style="margin-top: 25px;">Wi-Fi Connection</h3>
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
                <input id="ap_pswd" name="ap_pswd" type="password" placeholder="password">
            </div>
            <button onclick="window.saveData(true)" class="btn btn-save"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" style="vertical-align:-2px;margin-right:4px;"><polyline points="23 4 23 10 17 10"></polyline><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path></svg> Save & Restart Machine</button>
        </div>

        <!-- Page 4: Firmware OTA Update -->
        <div id="tab-update" class="tab-content">
            <h3 class="mb-3">Firmware OTA Update</h3>
            <p style="font-size:13px; color:#64748b; margin-bottom:15px;">Upload compiled <b>.bin</b> firmware file to update Attendance Machine software over Wi-Fi network.</p>
            <form method="POST" action="/update" enctype="multipart/form-data" style="background:#f8fafc; padding:15px; border-radius:10px; border:1px solid #e2e8f0;">
                <div class="mb-3">
                    <div class="mb-1">Select Binary (.bin) File</div>
                    <input type="file" name="update" accept=".bin" style="background:#fff;" required>
                </div>
                <button type="submit" class="btn" style="background:#8b5cf6;"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" style="vertical-align:-2px;margin-right:4px;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg> Flash Firmware (.bin)</button>
            </form>
        </div>

        <!-- Page 5: Web Portal Security -->
        <div id="tab-security" class="tab-content">
            <h3 class="mb-3">Web Portal Credentials</h3>
            <div class="mb-3">
                <div class="mb-1">Portal Username</div>
                <input id="admin_user" name="admin_user" type="text" placeholder="admin">
            </div>
            <div class="mb-3">
                <div class="mb-1">Portal Password</div>
                <input id="admin_pass" name="admin_pass" type="password" placeholder="••••••••">
            </div>
            <button onclick="window.saveData(false)" class="btn btn-save" style="margin-bottom:12px;"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" style="vertical-align:-2px;margin-right:4px;"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg> Save Security Credentials</button>
            <a href="/logout" class="btn" style="background:#dc2626; text-decoration:none; text-align:center; display:block; margin-bottom:25px;">🚪 Logout from Portal</a>

            <h3 class="mb-3" style="margin-top:25px; color:#dc2626; border-color:#fecaca;">Danger Zone</h3>
            <p style="font-size:13px; color:#64748b; margin-bottom:12px;">Restore device to factory default settings. All Wi-Fi networks, saved settings, and offline pending queue records will be erased.</p>
            <button onclick="window.factoryReset()" class="btn" style="background:#991b1b;"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" style="vertical-align:-2px;margin-right:4px;"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg> ⚠️ Factory Reset Device</button>
        </div>
    </div>

    <script>
        var url = "/settings";
        var save_url = "/save";

        window.factoryReset = function() {
            if (confirm("⚠️ DANGER: Are you sure you want to perform Factory Reset?\n\nThis will permanently erase all saved Wi-Fi networks, server settings, admin credentials, and offline pending queue records.")) {
                window.location.href = "/factory-reset";
            }
        };
        
        window.switchTab = function(tabName) {
            document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
            document.querySelectorAll('.nav-tab').forEach(el => el.classList.remove('active'));
            document.getElementById('tab-' + tabName).classList.add('active');
            document.getElementById('btn-' + tabName).classList.add('active');
        };

        window.setData = function() {
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
                    if(data.card_value != undefined) document.getElementById("card_value").value = data.card_value;
                    if(data.api_token) document.getElementById("api_token").value = data.api_token;
                    document.getElementById("company_name").value = data.company_name || "Company";
                    document.getElementById("domain_name").value = data.domain_name || "attendance.local";
                    document.getElementById("admin_user").value = data.admin_user || "admin";
                    document.getElementById("admin_pass").value = data.admin_pass || "password";
                } catch(e){}
            }
            window.loadQueue();
        };

        window.loadQueue = function() {
            var xmlHttp = new XMLHttpRequest();
            xmlHttp.open("GET", "/queue", true);
            xmlHttp.onload = function() {
                if (xmlHttp.status === 200) {
                    try {
                        let list = JSON.parse(xmlHttp.responseText);
                        let badge = document.getElementById("queue-badge");
                        let noMsg = document.getElementById("no-queue-msg");
                        let table = document.getElementById("queue-table");
                        let tbody = document.getElementById("queue-tbody");
                        if (badge) badge.innerText = list.length;
                        if (list.length > 0) {
                            if (badge) badge.style.background = "#f59e0b";
                            if (noMsg) noMsg.style.display = "none";
                            if (table) table.style.display = "table";
                            if (tbody) {
                                tbody.innerHTML = list.map(item => `
                                    <tr style="border-bottom:1px solid #e2e8f0;">
                                        <td style="padding:6px 4px; font-weight:600;">${item.tagid || '-'}</td>
                                        <td style="padding:6px 4px;">${item.tagms || '-'}</td>
                                        <td style="padding:6px 4px;">${item.dt || '-'}</td>
                                        <td style="padding:6px 4px;">${item.tim || '-'}</td>
                                    </tr>
                                `).join('');
                            }
                        } else {
                            if (badge) badge.style.background = "#10b981";
                            if (noMsg) noMsg.style.display = "block";
                            if (table) table.style.display = "none";
                        }
                    } catch(e){}
                }
            };
            xmlHttp.send(null);
        };

        window.syncQueue = function() {
            window.showToast("🔄 Syncing offline queue...");
            var xmlHttp = new XMLHttpRequest();
            xmlHttp.open("GET", "/sync-queue", true);
            xmlHttp.onload = function() { window.loadQueue(); };
            xmlHttp.send(null);
        };

        window.clearQueue = function() {
            if (confirm("Are you sure you want to clear all offline pending records?")) {
                var xmlHttp = new XMLHttpRequest();
                xmlHttp.open("GET", "/clear-queue", true);
                xmlHttp.onload = function() { window.loadQueue(); };
                xmlHttp.send(null);
            }
        };

        window.showToast = function(msg) {
            let t = document.getElementById('toast');
            if (t) {
                t.innerText = msg;
                t.style.display = 'block';
                setTimeout(function() { t.style.display = 'none'; }, 3000);
            }
        };

        window.saveData = function(isWifiSave) {
            let data = {
                ap_ssid: document.getElementById("ap_ssid").value,
                ap_pswd: document.getElementById("ap_pswd").value,
                wf_ssid: document.getElementById("wf_ssid").value,
                wf_pswd: document.getElementById("wf_pswd").value,
                op_mode: document.getElementById("op_mode").value,
                sr_host: document.getElementById("sr_host").value,
                card_value: document.getElementById("card_value").value,
                api_token: document.getElementById("api_token").value,
                company_name: document.getElementById("company_name").value,
                domain_name: document.getElementById("domain_name").value,
                admin_user: document.getElementById("admin_user").value,
                admin_pass: document.getElementById("admin_pass").value
            };
            var xmlHttp = new XMLHttpRequest();
            xmlHttp.open("GET", save_url + "?q=" + encodeURIComponent(JSON.stringify(data)), false);
            xmlHttp.send(null);
            if (xmlHttp.responseText && xmlHttp.responseText.indexOf("Rebooting") !== -1) {
                document.open();
                document.write(xmlHttp.responseText);
                document.close();
            } else if (!isWifiSave) {
                window.showToast("✓ Settings Saved Successfully");
            }
        };

        window.writeCard = function() {
            document.getElementById("cardButton").innerHTML = "⌛ Waiting for Card Tap...";
            document.getElementById("op_mode").value = "Write";
            window.saveData(false);
            setTimeout(window.checkStatus, 1000);
        };

        window.checkStatus = function() {
            var xmlHttp = new XMLHttpRequest();
            xmlHttp.open("GET", url, false);
            xmlHttp.send(null);
            if (xmlHttp.responseText) {
                let data = JSON.parse(xmlHttp.responseText);
                if (data.op_mode == "Write") {
                    setTimeout(window.checkStatus, 1000);
                } else {
                    window.setData();
                    document.getElementById("card_value").value = "";
                    document.getElementById("cardButton").innerHTML = '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" style="vertical-align:-2px;margin-right:4px;"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg> Write Card to NFC Tag';
                    window.showToast("✓ Card Written Successfully");
                }
            }
        };

        window.setData();
    </script>
</body>
</html>
)rawliteral";

void getWebpage() {
  File file = SPIFFS.open("/attendanceSettingsPage.html", "r");
  if (file) {
    while (file.available()) {
      webpage = file.readString();
    }
    file.close();
  }
  if (webpage.length() == 0) {
    webpage = FPSTR(DEFAULT_WEBPAGE_HTML);
  }
}

void setupMDNS() {
  MDNS.close();
  String host = domain_name;
  host.trim();
  if (host.endsWith(".local")) {
    host.remove(host.length() - 6);
  }
  if (host.length() == 0) {
    host = "attendance";
  }
  if (MDNS.begin(host.c_str())) {
    MDNS.addService("http", "tcp", 80);
    Serial.println("[mDNS] Responder started: http://" + host + ".local");
  }
}

void startSoftAP() {
  WiFi.mode(WIFI_AP);
  if (WiFi.softAP(ap_ssid.c_str(), ap_pswd.c_str())) {
    ipAddress = WiFi.softAPIP();
    Serial.println("\n[AP MODE] Access Point Started");
    Serial.print("[AP MODE] SSID: "); Serial.println(ap_ssid);
    Serial.print("[AP MODE] IP Address: "); Serial.println(ipAddress);
    setupMDNS();
    startWebServer();
  }
}

void startWiFi() {
  if (wf_ssid.length() == 0) {
    Serial.println("\n[Wi-Fi] No Router SSID configured. Starting Access Point mode...");
    startSoftAP();
    return;
  }

  WiFi.mode(WIFI_STA);
  WiFi.disconnect();
  delay(100);
  WiFi.begin(wf_ssid.c_str(), wf_pswd.c_str());

  Serial.println("\n[Wi-Fi] Connecting to Router SSID: " + wf_ssid);
  unsigned long startAttempt = millis();
  while (WiFi.status() != WL_CONNECTED && (millis() - startAttempt < 15000)) {
    delay(300);
    Serial.print(".");
  }
  Serial.println();

  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("[Wi-Fi] Connection Failed / Timeout. Fallback to Access Point mode...");
    startSoftAP();
  } else {
    ipAddress = WiFi.localIP();
    Serial.println("[Wi-Fi] Connected Successfully!");
    Serial.print("[Wi-Fi] IP Address: "); Serial.println(ipAddress);
    configTime(gmtOffset, daylightOffset, ntpServer);
    printLocalTime();
    isNetwork = true;
    setupMDNS();
    startWebServer();
  }
}

void printLocalTime() {
  struct tm timeinfo;
  if (!getLocalTime(&timeinfo)) {
    return;
  }

  char dtbuf[12];
  strftime(dtbuf, 80, "%F", &timeinfo);
  dt = dtbuf;

  char timbuf[12];
  strftime(timbuf, 80, "%T", &timeinfo);
  tim = timbuf;
}

bool is_authenticated() {
  if (server.hasHeader("Cookie")) {
    String cookie = server.header("Cookie");
    if (cookie.indexOf("ESPSESSIONID=1") != -1) {
      return true;
    }
  }
  return false;
}

String getLoginPageHTML(String errorMsg) {
  String errBox = "";
  if (errorMsg.length() > 0) {
    errBox = "<div style='background:#fef2f2;color:#dc2626;border:1px solid #fecaca;padding:10px;border-radius:8px;font-size:13px;text-align:center;margin-bottom:18px;'>" + errorMsg + "</div>";
  }
  String page = "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Attendance System Login</title>"
                "<meta name='viewport' content='width=device-width, initial-scale=1.0'>"
                "<style>* { margin: 0; padding: 0; box-sizing: border-box; }"
                "body { font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, sans-serif; background: #f4f6f9; color: #333; padding: 15px; display: flex; align-items: center; justify-content: center; min-height: 100vh; }"
                ".card { width: 100%; max-width: 400px; background: #fff; border-radius: 12px; padding: 30px; box-shadow: 0 4px 15px rgba(0,0,0,0.08); }"
                "h1 { color: #1e3a5f; font-size: 22px; text-align: center; margin-bottom: 4px; }"
                "p.subtitle { color: #64748b; font-size: 13px; text-align: center; margin-bottom: 25px; }"
                "label { font-size: 13px; font-weight: 600; color: #475569; display: block; margin-bottom: 5px; }"
                "input { width: 100%; padding: 11px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; margin-bottom: 18px; outline: none; transition: border-color 0.2s; }"
                "input:focus { border-color: #1e3a5f; }"
                ".btn { background: #1e3a5f; color: #fff; border: 0; padding: 12px; border-radius: 8px; font-weight: 700; cursor: pointer; width: 100%; font-size: 14px; transition: background 0.2s; }"
                ".btn:hover { background: #0f2744; }</style></head><body>"
                "<div class='card'><h1>Attendance System</h1><p class='subtitle'>Powered By Leena IT Solutions</p>"
                + errBox +
                "<form method='POST' action='/login'>"
                "<label>Username</label><input type='text' name='username' placeholder='admin' required autofocus>"
                "<label>Password</label><input type='password' name='password' placeholder='••••••••' required>"
                "<button type='submit' class='btn'>Login to Web Portal</button>"
                "</form></div></body></html>";
  return page;
}

void startWebServer() {
  const char *headerkeys[] = {"Cookie"};
  size_t headerkeyssize = sizeof(headerkeys) / sizeof(char *);
  server.collectHeaders(headerkeys, headerkeyssize);

  server.on("/login", HTTP_ANY, []() {
    if (server.method() == HTTP_POST) {
      String u = server.arg("username");
      String p = server.arg("password");
      if (u == admin_user && p == admin_pass) {
        server.sendHeader("Set-Cookie", "ESPSESSIONID=1; Path=/; HttpOnly");
        server.sendHeader("Location", "/");
        server.send(302, "text/plain", "");
        return;
      } else {
        String html = getLoginPageHTML("Invalid Username or Password!");
        server.send(200, "text/html", html);
        return;
      }
    }
    if (is_authenticated()) {
      server.sendHeader("Location", "/");
      server.send(302, "text/plain", "");
      return;
    }
    String html = getLoginPageHTML("");
    server.send(200, "text/html", html);
  });

  server.on("/logout", HTTP_GET, []() {
    server.sendHeader("Set-Cookie", "ESPSESSIONID=0; Path=/; Expires=Thu, 01 Jan 1970 00:00:00 GMT");
    server.sendHeader("Location", "/login");
    server.send(302, "text/plain", "");
  });

  server.on("/", HTTP_GET, []() {
    if (!is_authenticated()) {
      server.sendHeader("Location", "/login");
      server.send(302, "text/plain", "");
      return;
    }
    server.send(200, "text/html", webpage);
  });

  server.on("/settings", HTTP_GET, []() {
    if (!is_authenticated()) {
      server.send(401, "application/json", "{\"error\":\"Unauthorized\"}");
      return;
    }
    String message = getSettings();
    server.send(200, "application/json", message);
  });

  server.on("/save", HTTP_GET, []() {
    if (!is_authenticated()) {
      server.send(401, "application/json", "{\"error\":\"Unauthorized\"}");
      return;
    }
    if (server.hasArg("q")) {
      String message = server.arg("q");
      saveSettings(message);
    } else {
      server.send(200, "application/json", "{\"status\":\"ok\"}");
    }
  });

  server.on("/queue", HTTP_GET, []() {
    if (!is_authenticated()) {
      server.send(401, "application/json", "{\"error\":\"Unauthorized\"}");
      return;
    }
    String jsonStr = getQueueJSON();
    server.send(200, "application/json", jsonStr);
  });

  server.on("/sync-queue", HTTP_GET, []() {
    if (!is_authenticated()) {
      server.send(401, "application/json", "{\"error\":\"Unauthorized\"}");
      return;
    }
    processOfflineQueue();
    String jsonStr = getQueueJSON();
    server.send(200, "application/json", jsonStr);
  });

  server.on("/clear-queue", HTTP_GET, []() {
    if (!is_authenticated()) {
      server.send(401, "application/json", "{\"error\":\"Unauthorized\"}");
      return;
    }
    clearQueue();
    server.send(200, "application/json", "[]");
  });

  server.on("/factory-reset", HTTP_ANY, []() {
    if (!is_authenticated()) {
      server.send(401, "text/plain", "Unauthorized");
      return;
    }
    factoryReset();
  });

  httpUpdater.setup(&server, "/update");
  server.onNotFound(notFound);
  server.begin();
}

void factoryReset() {
  if (SPIFFS.exists(settings_filename)) {
    SPIFFS.remove(settings_filename);
  }
  if (SPIFFS.exists(queue_filename)) {
    SPIFFS.remove(queue_filename);
  }
  ap_ssid = "attendance";
  ap_pswd = "password";
  wf_ssid = "";
  wf_pswd = "";
  op_mode = "Read";
  sr_host = "https://payroll.sarvodayavidyalay.com/attendance/save";
  card_value = "";
  api_token = "";
  company_name = "Company";
  domain_name = "attendance.local";
  admin_user = "admin";
  admin_pass = "password";

  oled.clearDisplay();
  oled.setCursor(0, 0);
  oled.println(company_name);
  oled.setCursor(0, 15);
  oled.println("FACTORY RESET!");
  oled.setCursor(0, 30);
  oled.println("Rebooting...");
  oled.display();

  beep(2, 200, 100);

  String html = "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Factory Reset</title>"
                "<meta http-equiv='refresh' content='10;url=http://attendance.local/'>"
                "<style>body{font-family:sans-serif;background:#f4f6f9;display:flex;align-items:center;justify-content:center;height:100vh;margin:0;}"
                ".card{background:#fff;padding:30px;border-radius:12px;box-shadow:0 4px 15px rgba(0,0,0,0.1);text-align:center;max-width:400px;}"
                "h2{color:#dc2626;margin-bottom:10px;}p{color:#64748b;font-size:14px;}</style></head><body>"
                "<div class='card'><h2>⚠️ Factory Reset Complete</h2>"
                "<p>Device memory cleared. Rebooting into AP Access Point mode...</p></div></body></html>";
  server.send(200, "text/html", html);
  delay(1500);
  ESP.restart();
}

void saveSettings(String msg) {
  String old_ap_ssid = ap_ssid;
  String old_ap_pswd = ap_pswd;
  String old_wf_ssid = wf_ssid;
  String old_wf_pswd = wf_pswd;
  String old_domain_name = domain_name;

  File fl = SPIFFS.open(settings_filename, "w");
  if (!fl) {
    server.send(200, "application/json", "{\"status\":\"error\"}");
    return;
  }
  fl.print(msg);
  fl.close();

  setSettings();

  // Immediately refresh OLED display screen with new mode!
  writeCompanyName();

  bool rebootNeeded = (old_ap_ssid != ap_ssid) || (old_ap_pswd != ap_pswd) ||
                       (old_wf_ssid != wf_ssid) || (old_wf_pswd != wf_pswd) ||
                       (old_domain_name != domain_name);

  if (rebootNeeded) {
    String redirectTarget = "/";
    if (old_domain_name != domain_name) {
      String cleanDom = domain_name;
      cleanDom.trim();
      if (!cleanDom.startsWith("http://") && !cleanDom.startsWith("https://")) {
        cleanDom = "http://" + cleanDom;
      }
      if (!cleanDom.endsWith("/")) {
        cleanDom += "/";
      }
      redirectTarget = cleanDom;
    }

    String responseHtml = "<!DOCTYPE html><html><head><meta name='viewport' content='width=device-width, initial-scale=1.0'>"
                          "<style>body{font-family:-apple-system,sans-serif;background:#f4f6f9;text-align:center;padding:50px;}"
                          ".card{background:#fff;padding:30px;border-radius:12px;display:inline-block;box-shadow:0 4px 15px rgba(0,0,0,0.1);}"
                          "h2{color:#2563eb;margin-top:0;} p{color:#475569;font-size:14px;}</style></head><body>"
                          "<div class='card'><h2>🔄 Rebooting Machine...</h2>"
                          "<p>Network / mDNS Domain settings updated. Machine is restarting...</p>"
                          "<p style='margin-top:10px;font-weight:600;'>Redirecting to <u>" + redirectTarget + "</u></p></div>"
                          "<script>setTimeout(function(){ window.location.href='" + redirectTarget + "'; }, 6000);</script></body></html>";

    server.send(200, "text/html", responseHtml);
    server.client().flush();
    Serial.println("[SYSTEM] Settings Saved. Software Resetting ESP8266...");
    delay(2000);
    ESP.restart();
  } else {
    server.send(200, "application/json", "{\"status\":\"ok\"}");
  }
}

void updateAndSaveSettings() {
  StaticJsonDocument<1024> doc;
  doc["ap_ssid"] = ap_ssid;
  doc["ap_pswd"] = ap_pswd;
  doc["wf_ssid"] = wf_ssid;
  doc["wf_pswd"] = wf_pswd;
  doc["op_mode"] = op_mode;
  doc["sr_host"] = sr_host;
  doc["card_value"] = card_value;
  doc["api_token"] = api_token;
  doc["company_name"] = company_name;
  doc["domain_name"] = domain_name;
  String str = "";
  serializeJson(doc, str);
  saveSettings(str);
}

String getSettings() {
  String str = "";
  File file = SPIFFS.open(settings_filename, "r");
  if (file) {
    str = file.readString();
    file.close();
    str.trim();
  }

  DynamicJsonDocument doc(1024);
  if (str.length() > 0) {
    deserializeJson(doc, str);
  }

  if (!doc.containsKey("ap_ssid") || doc["ap_ssid"].as<String>().length() == 0) doc["ap_ssid"] = (ap_ssid.length() > 0) ? ap_ssid : "attendance";
  if (!doc.containsKey("ap_pswd") || doc["ap_pswd"].as<String>().length() == 0) doc["ap_pswd"] = (ap_pswd.length() > 0) ? ap_pswd : "123456789";
  if (!doc.containsKey("wf_ssid")) doc["wf_ssid"] = wf_ssid;
  if (!doc.containsKey("wf_pswd")) doc["wf_pswd"] = wf_pswd;
  if (!doc.containsKey("op_mode") || doc["op_mode"].as<String>().length() == 0) doc["op_mode"] = (op_mode.length() > 0) ? op_mode : "Read";
  if (!doc.containsKey("sr_host") || doc["sr_host"].as<String>().length() == 0) doc["sr_host"] = (sr_host.length() > 0) ? sr_host : "https://payroll.sarvodayavidyalay.com/attendance/save";
  if (!doc.containsKey("card_value")) doc["card_value"] = card_value;
  if (!doc.containsKey("api_token")) doc["api_token"] = api_token;
  if (!doc.containsKey("company_name") || doc["company_name"].as<String>().length() == 0) doc["company_name"] = (company_name.length() > 0) ? company_name : "Company";
  if (!doc.containsKey("domain_name") || doc["domain_name"].as<String>().length() == 0) doc["domain_name"] = (domain_name.length() > 0) ? domain_name : "attendance.local";
  if (!doc.containsKey("admin_user") || doc["admin_user"].as<String>().length() == 0) doc["admin_user"] = (admin_user.length() > 0) ? admin_user : "admin";
  if (!doc.containsKey("admin_pass") || doc["admin_pass"].as<String>().length() == 0) doc["admin_pass"] = (admin_pass.length() > 0) ? admin_pass : "password";

  String result = "";
  serializeJson(doc, result);
  return result;
}

void setSettings() {
  String str = getSettings();
  DynamicJsonDocument doc(1024);
  DeserializationError error = deserializeJson(doc, str);
  if (error) {
    return;
  }
  if (doc.containsKey("ap_ssid"))
    ap_ssid = String(doc["ap_ssid"]);
  if (doc.containsKey("ap_pswd"))
    ap_pswd = String(doc["ap_pswd"]);
  if (doc.containsKey("wf_ssid"))
    wf_ssid = String(doc["wf_ssid"]);
  if (doc.containsKey("wf_pswd"))
    wf_pswd = String(doc["wf_pswd"]);
  if (doc.containsKey("op_mode"))
    op_mode = String(doc["op_mode"]);
  if (doc.containsKey("sr_host"))
    sr_host = String(doc["sr_host"]);
  if (doc.containsKey("card_value"))
    card_value = String(doc["card_value"]);
  if (doc.containsKey("api_token"))
    api_token = String(doc["api_token"]);
  if (doc.containsKey("company_name"))
    company_name = String(doc["company_name"]);
  if (doc.containsKey("domain_name"))
    domain_name = String(doc["domain_name"]);
  if (doc.containsKey("admin_user"))
    admin_user = String(doc["admin_user"]);
  if (doc.containsKey("admin_pass"))
    admin_pass = String(doc["admin_pass"]);

  if (op_mode.length() == 0)
    op_mode = "Read";
  if (sr_host.length() == 0)
    sr_host = "https://payroll.sarvodayavidyalay.com/attendance/save";
  if (company_name.length() == 0)
    company_name = "Company";
  if (domain_name.length() == 0)
    domain_name = "attendance.local";
  if (admin_user.length() == 0)
    admin_user = "admin";
  if (admin_pass.length() == 0)
    admin_pass = "password";

  setupMDNS();
}

String getQueueJSON() {
  if (!SPIFFS.exists(queue_filename)) {
    return "[]";
  }
  File f = SPIFFS.open(queue_filename, "r");
  if (!f) {
    return "[]";
  }
  String str = f.readString();
  f.close();
  str.trim();
  if (str.length() == 0 || !str.startsWith("[")) {
    return "[]";
  }
  return str;
}

void writeQueueJSON(const String &jsonStr) {
  File f = SPIFFS.open(queue_filename, "w");
  if (f) {
    f.print(jsonStr);
    f.close();
  }
}

int getQueueCount() {
  String jsonStr = getQueueJSON();
  DynamicJsonDocument doc(8192);
  DeserializationError err = deserializeJson(doc, jsonStr);
  if (err || !doc.is<JsonArray>()) {
    return 0;
  }
  return doc.as<JsonArray>().size();
}

bool enqueueRecord(String tId, String tMs, String d, String t) {
  String jsonStr = getQueueJSON();
  DynamicJsonDocument doc(8192);
  deserializeJson(doc, jsonStr);
  JsonArray arr = doc.as<JsonArray>();

  if (arr.size() >= 200) {
    Serial.println("[QUEUE] Queue full! Max 200 entries reached.");
    return false;
  }

  JsonObject item = arr.createNestedObject();
  item["tagid"] = tId;
  item["tagms"] = tMs;
  item["dt"] = d;
  item["tim"] = t;

  String output;
  serializeJson(doc, output);
  writeQueueJSON(output);
  Serial.print("[QUEUE] Enqueued offline record. Current Queue Count: ");
  Serial.println(arr.size());
  return true;
}

bool dequeueRecord() {
  String jsonStr = getQueueJSON();
  DynamicJsonDocument doc(8192);
  deserializeJson(doc, jsonStr);
  JsonArray arr = doc.as<JsonArray>();

  if (arr.size() == 0) {
    return false;
  }

  arr.remove(0);

  String output;
  serializeJson(doc, output);
  writeQueueJSON(output);
  Serial.print("[QUEUE] Dequeued 1 record. Remaining Queue Count: ");
  Serial.println(arr.size());
  return true;
}

void clearQueue() {
  writeQueueJSON("[]");
  Serial.println("[QUEUE] Queue cleared manually.");
}

SyncResult sendDataToServerParams(String tId, String tMs, String d, String t) {
  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("[API] Cannot send data: Wi-Fi not connected.");
    return SYNC_SERVER_ERROR;
  }

  if (sr_host.length() == 0) {
    Serial.println("[API] Cannot send data: Host URI is empty.");
    return SYNC_CLIENT_ERROR;
  }

  String encodeduri = "tagid=" + urlEncode(tId) +
                      "&tagms=" + urlEncode(tMs) +
                      "&dt=" + urlEncode(d) +
                      "&tim=" + urlEncode(t);

  String uri = sr_host;
  if (uri.indexOf('?') != -1) {
    uri += "&" + encodeduri;
  } else {
    uri += "?" + encodeduri;
  }

  Serial.println("\n------------------------------------");
  Serial.print("[API] Request URI: "); Serial.println(uri);
  if (api_token.length() > 0) {
    Serial.println("[API] Bearer Token Attached: YES");
  } else {
    Serial.println("[API] Bearer Token Attached: NO (token is empty)");
  }

  HTTPClient http;
  bool isHttps = uri.startsWith("https://");
  SyncResult result = SYNC_SERVER_ERROR;

  if (isHttps) {
    std::unique_ptr<BearSSL::WiFiClientSecure> client(new BearSSL::WiFiClientSecure);
    client->setInsecure();
    client->setBufferSizes(1024, 1024);
    client->setTimeout(2500);
    http.setTimeout(2500);
    http.setFollowRedirects(HTTPC_STRICT_FOLLOW_REDIRECTS);
    http.setUserAgent("ESP8266-AttendanceMachine");

    if (http.begin(*client, uri)) {
      http.addHeader("Accept", "application/json");
      if (api_token.length() > 0) {
        http.addHeader("Authorization", "Bearer " + api_token);
      }
      int httpCode = http.GET();
      Serial.print("[API] HTTPS Status Code: "); Serial.println(httpCode);
      if (httpCode >= 200 && httpCode < 300) {
        String payload = http.getString();
        Serial.print("[API] Server Response: "); Serial.println(payload);
        result = SYNC_SUCCESS;
      } else if (httpCode >= 400 && httpCode < 500) {
        Serial.print("[API] Client Error Code: "); Serial.println(httpCode);
        result = SYNC_CLIENT_ERROR;
      } else {
        Serial.print("[API] HTTPS GET Server Error / Timeout Code: "); Serial.println(httpCode);
        result = SYNC_SERVER_ERROR;
      }
      http.end();
    } else {
      Serial.println("[API] Unable to initialize HTTPS connection");
      result = SYNC_SERVER_ERROR;
    }
  } else {
    WiFiClient client;
    client.setTimeout(2500);
    http.setTimeout(2500);
    http.setFollowRedirects(HTTPC_STRICT_FOLLOW_REDIRECTS);
    http.setUserAgent("ESP8266-AttendanceMachine");

    if (http.begin(client, uri)) {
      http.addHeader("Accept", "application/json");
      if (api_token.length() > 0) {
        http.addHeader("Authorization", "Bearer " + api_token);
      }
      int httpCode = http.GET();
      Serial.print("[API] HTTP Status Code: "); Serial.println(httpCode);
      if (httpCode >= 200 && httpCode < 300) {
        String payload = http.getString();
        Serial.print("[API] Server Response: "); Serial.println(payload);
        result = SYNC_SUCCESS;
      } else if (httpCode >= 400 && httpCode < 500) {
        Serial.print("[API] Client Error Code: "); Serial.println(httpCode);
        result = SYNC_CLIENT_ERROR;
      } else {
        Serial.print("[API] HTTP GET Server Error / Timeout Code: "); Serial.println(httpCode);
        result = SYNC_SERVER_ERROR;
      }
      http.end();
    } else {
      Serial.println("[API] Unable to initialize HTTP connection");
      result = SYNC_SERVER_ERROR;
    }
  }
  Serial.println("------------------------------------\n");
  return result;
}

SyncResult sendDataToServer() {
  return sendDataToServerParams(tagId, tagMs, dt, tim);
}

void processOfflineQueue() {
  if (WiFi.status() != WL_CONNECTED || isQueueSyncing) {
    return;
  }

  if (millis() < serverErrorCooldownUntil) {
    return;
  }

  String jsonStr = getQueueJSON();
  DynamicJsonDocument doc(8192);
  DeserializationError err = deserializeJson(doc, jsonStr);
  if (err || !doc.is<JsonArray>()) {
    return;
  }

  JsonArray arr = doc.as<JsonArray>();
  if (arr.size() == 0) {
    return;
  }

  isQueueSyncing = true;
  JsonObject item = arr[0];
  String qTagId = item["tagid"].as<String>();
  String qTagMs = item["tagms"].as<String>();
  String qDt = item["dt"].as<String>();
  String qTim = item["tim"].as<String>();

  Serial.println("[QUEUE] Attempting background sync: " + qTagId + " / " + qTagMs);
  SyncResult res = sendDataToServerParams(qTagId, qTagMs, qDt, qTim);

  if (res == SYNC_SUCCESS) {
    dequeueRecord();
    consecutiveServerErrors = 0;
    serverErrorCooldownUntil = 0;
  } else if (res == SYNC_CLIENT_ERROR) {
    static int clientErrorCount = 0;
    clientErrorCount++;
    if (clientErrorCount >= 3) {
      Serial.println("[QUEUE] Client HTTP 4xx error persisted 3 times. Dropping corrupt record.");
      dequeueRecord();
      clientErrorCount = 0;
    }
  } else { // SYNC_SERVER_ERROR
    consecutiveServerErrors++;
    serverErrorCooldownUntil = millis() + 30000;
    Serial.println("[QUEUE] Server error/timeout detected. Entering 30s network cooldown penalty.");
  }
  isQueueSyncing = false;
}

void beep(int count, int durationMs, int delayMs) {
  for (int i = 0; i < count; i++) {
    digitalWrite(buz, HIGH);
    delay(durationMs);
    digitalWrite(buz, LOW);
    if (i < count - 1) {
      delay(delayMs);
    }
  }
}

void readCard() {
  NfcTag tag = nfc.read();
  tagId = tag.getUidString();
  tagMs = "";
  if (tag.hasNdefMessage()) {
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
  }

  if (tagId.length() > 0 || tagMs.length() > 0) {
    String currentCardKey = tagId + "_" + tagMs;
    if (currentCardKey == lastScannedCardKey && (millis() - lastScannedTimeMs < 2000)) {
      return;
    }
    lastScannedCardKey = currentCardKey;
    lastScannedTimeMs = millis();

    beep(1, 100, 0); // Immediate instant feedback beep!
    printLocalTime();

    // Instantly save to SPIFFS queue (0ms network blocking delay!)
    enqueueRecord(tagId, tagMs, dt, tim);

    // Display scanned Employee Code / Tag ID on OLED
    showMessage();

    // Machine is available for next card tap after 1 second!
    delay(1000);
  }
  writeCompanyName();
}

void writeCard() {
  NdefMessage message = NdefMessage();
  message.addTextRecord(card_value);
  bool success = nfc.write(message);
  if (success) {
    beep(2, 100, 100); // Double beep on card write success
  }
  delay(500);

  // Always reset mode to Read and clear card_value to reset web form input
  op_mode = "Read";
  card_value = "";
  updateAndSaveSettings();
  writeCompanyName();

  delay(1000);
}

void formatCard() {
  bool success = nfc.format();
  if (success) {
    beep(2, 100, 100);
    // Serial.println("\nThe card (tag) successfully formatted in the NTAG.");
  } else {
    // Serial.println("\nUnsuccessful formatting.");
  }
  delay(1000);
}

void deleteCardMessage() {
  bool success = nfc.clean();
  if (success) {
    beep(2, 100, 100);
  }
  delay(1000);
}

void clearCard() {
  bool success = nfc.clean();
  if (success) {
    beep(2, 100, 100);
  }
  delay(1000);
}

void accessCard() {
  if (nfc.tagPresent()) {
    if (op_mode == "Read") {
      readCard();
    } else if (op_mode == "Write") {
      writeCard();
    } else if (op_mode == "Format") {
      formatCard();
    } else if (op_mode == "Delete") {
      deleteCardMessage();
    } else if (op_mode == "Clear") {
      clearCard();
    }
  }
}

void drawScreenWithMiddleText(const String &middleText) {
  oled.clearDisplay();

  // Dynamic Header at top - Company Name
  String compStr = (company_name.length() > 0) ? company_name : "Company";
  int compLen = compStr.length();
  int startCompX = (128 - (compLen * 6)) / 2;
  if (startCompX < 0) startCompX = 0;

  oled.setTextSize(1);
  oled.setCursor(startCompX, 0);
  oled.println(compStr);

  // Middle text (Time or Employee Code tagMs)
  int textLength = middleText.length();
  int startX = (128 - (textLength * 12)) / 2;
  if (startX < 0)
    startX = 0;

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

void writeCompanyName() {
  String clockText = (tim.length() > 0) ? tim : "00:00:00";
  drawScreenWithMiddleText(clockText);
}

void writeBrandName() {
  oled.clearDisplay();
  oled.setTextSize(1);
  oled.setCursor(0, 0);
  oled.println(F("Attendance Machine"));
  oled.println(F("-----------------"));
  oled.println(F("Powered By"));
  oled.println(F("Leena IT Solutions"));
  oled.display();
}

void showMessage() { drawScreenWithMiddleText(tagMs); }

void readSwitch() {
  if (digitalRead(swi) == 1) {
    if (swiStart == 0) {
      swiStart = millis();
    } else {
      swiEnd = millis();
    }
  } else {
    if (swiStart != 0 && swiEnd != 0) {
      duration = swiEnd - swiStart;
    } else {
      duration = 0;
    }
    swiStart = 0;
    swiEnd = 0;
  }
  if (duration > 0) {
    Serial.println(duration);
  }
  if (duration > 3000) {
    if (op_mode == "Setup") {
      op_mode = "Read";
    } else {
      op_mode = "Setup";
    }
    updateAndSaveSettings();
  }
}

void hello() {
  if (op_mode == "Read" && isNetwork) {
    printLocalTime();
  }
}

void setup() {
  Serial.begin(115200);
  if (!SPIFFS.begin()) {
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

  if (op_mode == "Setup") {
    startSoftAP();
  } else {
    startWiFi();
  }

  nfc.begin();

  timer1.attach(0.1, readSwitch);
  timer2.attach(1, hello);

  // Machine Startup Beep Sequence: 3 quick short beeps!
  beep(3, 80, 80);
}

void loop() {
  MDNS.update();
  server.handleClient();
  writeCompanyName();
  accessCard();

  if (millis() - lastQueueSyncCheck > 2000) {
    lastQueueSyncCheck = millis();
    processOfflineQueue();
  }
}