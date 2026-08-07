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
#include <Wire.h>
#include <Adafruit_SH110X.h>
#include <FS.h>
#include <ArduinoJson.h>
#include <ESPAsyncWebServer.h>
#include <Ticker.h>
#include "time.h"
#include <PN532.h>
#include <PN532_I2C.h>
#include <NfcAdapter.h>

Ticker timer1;
Ticker timer2;

#define i2c_Address 0x3c
#define SCREEN_WIDTH 128
#define SCREEN_HEIGHT 64
Adafruit_SH1106G oled(SCREEN_WIDTH, SCREEN_HEIGHT, &Wire, -1);

const char* ntpServer = "in.pool.ntp.org";
const long gmtOffset = 0;
const int daylightOffset = 19800;

const uint8_t fingerprint[32] = { 0xfe, 0x8e, 0xe2, 0x35, 0xea, 0xad, 0xe2, 0xbb, 0x0d, 0x48, 0xd4, 0x08, 0xb1, 0x2c, 0x6a, 0x77, 0xc3, 0x8a, 0x0d, 0xf6, 0x8b, 0xaf, 0x8a, 0x97, 0x94, 0x83, 0x76, 0x7a, 0xed, 0x33, 0x8c, 0x6f };

PN532_I2C pn532_i2c(Wire);
NfcAdapter nfc = NfcAdapter(pn532_i2c);

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

AsyncWebServer server(80);

void notFound(AsyncWebServerRequest *request){
  request->send(404, "text/html", "<h1>Page Not Found</h1>");
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
  server.on("/", HTTP_GET, [](AsyncWebServerRequest *request){
    request->send(200, "text/html", webpage);
  });
  server.on("/settings", HTTP_GET, [](AsyncWebServerRequest *request){
    String message = getSettings();
    request->send(200, "text/json", message);
  });
  server.on("/save", HTTP_GET, [](AsyncWebServerRequest *request){
    String message = "";
    if(request->hasParam("q")){
      message = request->getParam("q")->value();
      saveSettings(message);
      //Serial.println(message);
    }
    request->send(200, "text/json", message);
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
  writeCompanyName();
  accessCard();
}