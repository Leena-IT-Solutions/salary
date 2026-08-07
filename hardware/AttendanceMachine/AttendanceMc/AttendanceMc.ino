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

        uint8_t keyA[6] = {0xD3, 0xF7, 0xD3, 0xF7, 0xD3, 0xF7};
        uint8_t keyDef[6] = {0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF};

        bool auth = pn532.mifareclassic_AuthenticateBlock(_lastUid, _lastUidLen, 4, 0, keyA);
        if (!auth) {
            pn532.inListPassiveTarget();
            auth = pn532.mifareclassic_AuthenticateBlock(_lastUid, _lastUidLen, 4, 0, keyDef);
        }

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
        uint8_t keyDef[6] = {0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF};
        uint8_t keyMAD[6] = {0xA0, 0xA1, 0xA2, 0xA3, 0xA4, 0xA5};
        uint8_t keyNDEF[6] = {0xD3, 0xF7, 0xD3, 0xF7, 0xD3, 0xF7};

        if (pn532.mifareclassic_AuthenticateBlock(_lastUid, _lastUidLen, 1, 0, keyMAD) ||
            pn532.mifareclassic_AuthenticateBlock(_lastUid, _lastUidLen, 1, 0, keyDef)) {
            uint8_t mad1[16] = {0x14, 0x01, 0x03, 0xE1, 0x03, 0xE1, 0x03, 0xE1, 0x03, 0xE1, 0x03, 0xE1, 0x03, 0xE1, 0x03, 0xE1};
            uint8_t mad2[16] = {0x03, 0xE1, 0x03, 0xE1, 0x03, 0xE1, 0x03, 0xE1, 0x03, 0xE1, 0x03, 0xE1, 0x03, 0xE1, 0x03, 0xE1};
            pn532.mifareclassic_WriteDataBlock(1, mad1);
            pn532.mifareclassic_WriteDataBlock(2, mad2);
        }

        if (pn532.mifareclassic_AuthenticateBlock(_lastUid, _lastUidLen, 7, 0, keyDef) ||
            pn532.mifareclassic_AuthenticateBlock(_lastUid, _lastUidLen, 7, 0, keyMAD)) {
            uint8_t trailer[16] = {0xD3, 0xF7, 0xD3, 0xF7, 0xD3, 0xF7, 0x7F, 0x07, 0x88, 0x40, 0xF7, 0xD3, 0xF7, 0xD3, 0xF7, 0xD3};
            pn532.mifareclassic_WriteDataBlock(7, trailer);
        }

        bool auth = pn532.mifareclassic_AuthenticateBlock(_lastUid, _lastUidLen, 4, 0, keyNDEF);
        if (!auth) {
            pn532.inListPassiveTarget();
            auth = pn532.mifareclassic_AuthenticateBlock(_lastUid, _lastUidLen, 4, 0, keyDef);
        }

        if (!auth) return false;

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

void getWebpage(){
  File file = SPIFFS.open("/attendanceSettingsPage.html", "r");
  if (!file){
    //Serial.println("Failed to read file");
  }
  while (file.available()){
    webpage = file.readString();
  }
  // Serial.println(webpage);
  file.close();
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
    File fl = SPIFFS.open(settings_filename, "w");
    if(!fl){
      //Serial.println("Failed to write file");
    }
    if(fl.print(msg)){
      //Serial.println("File was written");
      setSettings();
    } else {
      //Serial.println("Fail to write file");
    }
    fl.close();
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
  File file = SPIFFS.open("/settings.json", "r");
  if (!file){
    //Serial.println("Failed to read file");
    return str;
  }
  while (file.available()){
    str = file.readString();
  }
  file.close();
  return str;
}

void setSettings(){
  String str = getSettings();
  DynamicJsonDocument doc(1024);
  DeserializationError error = deserializeJson(doc, str);
  if(error){
    //Serial.print("Deserialization Failed: ");
    //Serial.println(error.c_str());
    return;
  }
  ap_ssid = String(doc["ap_ssid"]);
  ap_pswd = String(doc["ap_pswd"]);
  wf_ssid = String(doc["wf_ssid"]);
  wf_pswd = String(doc["wf_pswd"]);
  op_mode = String(doc["op_mode"]);
  sr_host = String(doc["sr_host"]);
  // localip = String(doc["localip"]);
  // gateway = String(doc["gateway"]);
  // subnet = String(doc["subnet"]);
  // primarydns = String(doc["primarydns"]);
  // secondarydns = String(doc["secondarydns"]);
  card_value = String(doc["card_value"]);
  if(doc.containsKey("api_token")) {
    api_token = String(doc["api_token"]);
  }
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
  //tag.print();
  tagId = tag.getUidString();
  //Serial.println(tagId);
  if(tag.hasNdefMessage()){
    NdefRecord record = tag.getNdefMessage().getRecord(0);
    byte length = record.getPayloadLength();
    byte payload[length];
    record.getPayload(payload);
    tagMs = String((char *)payload);
    tagMs.trim();
    tagMs.remove(length, -4);
    tagMs.remove(0,3);
    showMessage();
    //Serial.println(tagMs);
    printLocalTime();
    sendDataToServer();
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

void writeCompanyName(){
  oled.clearDisplay();
  oled.setTextSize(1);
  oled.setCursor(10,0);
  oled.println(F("Sarvodaya Vidyalay"));
  oled.setTextSize(2);
  oled.setCursor(30,15);
  oled.println(tim.substring(0,5));
  oled.setTextSize(1);
  oled.setCursor(0,55);
  oled.println(ipAddress);
  oled.setTextSize(1);
  oled.setCursor(120,55);
  oled.println(op_mode[0]);
  oled.display();
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
  int c = (10 - tagMs.length())*6;
  oled.setTextSize(2);
  oled.setCursor(c,35);
  oled.println(tagMs);
  oled.display();
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