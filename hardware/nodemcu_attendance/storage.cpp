#include "storage.h"
#include <EEPROM.h>
#include <LittleFS.h>
#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
#include <WiFiClientSecure.h>
#include <WiFiClient.h>

static void sanitizeString(char *str, size_t maxLen) {
    str[maxLen - 1] = '\0';
    for (size_t i = 0; i < maxLen && str[i] != '\0'; i++) {
        if ((uint8_t)str[i] < 32 || (uint8_t)str[i] > 126) {
            str[i] = '\0';
            break;
        }
    }
}

static void applyDefaults(Config &cfg) {
    cfg.magic = CONFIG_MAGIC;
    strncpy(cfg.ap_ssid, DEFAULT_AP_SSID, sizeof(cfg.ap_ssid));
    strncpy(cfg.ap_pass, DEFAULT_AP_PASS, sizeof(cfg.ap_pass));
    strncpy(cfg.wifi_ssid, DEFAULT_WIFI_SSID, sizeof(cfg.wifi_ssid));
    strncpy(cfg.wifi_pass, DEFAULT_WIFI_PASS, sizeof(cfg.wifi_pass));
    strncpy(cfg.company_name, DEFAULT_COMPANY_NAME, sizeof(cfg.company_name));
    strncpy(cfg.location_name, DEFAULT_LOCATION, sizeof(cfg.location_name));
    strncpy(cfg.host_uri, DEFAULT_HOST_URI, sizeof(cfg.host_uri));
    strncpy(cfg.api_token, "", sizeof(cfg.api_token));
    strncpy(cfg.device_code, DEFAULT_DEVICE_CODE, sizeof(cfg.device_code));
    strncpy(cfg.card_value, "", sizeof(cfg.card_value));
    strncpy(cfg.portal_user, DEFAULT_PORTAL_USER, sizeof(cfg.portal_user));
    strncpy(cfg.portal_pass, DEFAULT_PORTAL_PASS, sizeof(cfg.portal_pass));
    strncpy(cfg.mdns_name, DEFAULT_MDNS_NAME, sizeof(cfg.mdns_name));
    cfg.op_mode = MODE_READ;
    cfg.buzzer_enabled = 1;
    cfg.tz_offset = 19800; // IST UTC+5:30
}

void storageSaveConfig(Config &cfg) {
    cfg.magic = CONFIG_MAGIC;
    EEPROM.begin(EEPROM_SIZE);
    EEPROM.put(0, cfg);
    bool ok = EEPROM.commit();
    EEPROM.end();
    Serial.printf("[STORAGE] Config saved to EEPROM (Magic: 0x%08X | SSID: '%s' | Flash Commit: %s)\n", 
                  cfg.magic, cfg.wifi_ssid, ok ? "SUCCESS" : "FAILED");
}

void storageLoadConfig(Config &cfg) {
    EEPROM.begin(EEPROM_SIZE);
    EEPROM.get(0, cfg);
    EEPROM.end();

    if (cfg.magic != CONFIG_MAGIC) {
        Serial.println("[STORAGE] Magic header invalid. Initializing default settings...");
        applyDefaults(cfg);
        storageSaveConfig(cfg);
        return;
    }

    sanitizeString(cfg.ap_ssid, sizeof(cfg.ap_ssid));
    sanitizeString(cfg.ap_pass, sizeof(cfg.ap_pass));
    sanitizeString(cfg.wifi_ssid, sizeof(cfg.wifi_ssid));
    sanitizeString(cfg.wifi_pass, sizeof(cfg.wifi_pass));
    sanitizeString(cfg.company_name, sizeof(cfg.company_name));
    sanitizeString(cfg.location_name, sizeof(cfg.location_name));
    sanitizeString(cfg.host_uri, sizeof(cfg.host_uri));
    sanitizeString(cfg.api_token, sizeof(cfg.api_token));
    sanitizeString(cfg.device_code, sizeof(cfg.device_code));
    sanitizeString(cfg.card_value, sizeof(cfg.card_value));
    sanitizeString(cfg.portal_user, sizeof(cfg.portal_user));
    sanitizeString(cfg.portal_pass, sizeof(cfg.portal_pass));
    sanitizeString(cfg.mdns_name, sizeof(cfg.mdns_name));

    if (strlen(cfg.ap_ssid) == 0) strncpy(cfg.ap_ssid, DEFAULT_AP_SSID, sizeof(cfg.ap_ssid));
    if (strlen(cfg.ap_pass) == 0) strncpy(cfg.ap_pass, DEFAULT_AP_PASS, sizeof(cfg.ap_pass));
    if (strlen(cfg.company_name) == 0) strncpy(cfg.company_name, DEFAULT_COMPANY_NAME, sizeof(cfg.company_name));
    if (strlen(cfg.host_uri) == 0) strncpy(cfg.host_uri, DEFAULT_HOST_URI, sizeof(cfg.host_uri));
    if (strlen(cfg.device_code) == 0) strncpy(cfg.device_code, DEFAULT_DEVICE_CODE, sizeof(cfg.device_code));
    if (strlen(cfg.portal_user) == 0) strncpy(cfg.portal_user, DEFAULT_PORTAL_USER, sizeof(cfg.portal_user));
    if (strlen(cfg.portal_pass) == 0) strncpy(cfg.portal_pass, DEFAULT_PORTAL_PASS, sizeof(cfg.portal_pass));

    Serial.printf("[STORAGE] Config loaded from EEPROM! (SSID: '%s' | AP: '%s' | Domain: '%s')\n", 
                  cfg.wifi_ssid, cfg.ap_ssid, cfg.mdns_name);
}

void storageResetConfig(Config &cfg) {
    cfg.magic = 0x00000000;
    EEPROM.begin(EEPROM_SIZE);
    EEPROM.put(0, cfg);
    EEPROM.commit();
    EEPROM.end();
    storageLoadConfig(cfg);
}

// ==========================================
// High-Capacity Line-Based Offline Storage Queue
// Stores 10,000+ punches in LittleFS without RAM limits.
// ==========================================
void storageSaveOfflinePunch(const String &tagms, const String &tagid,
                              const String &dateStr, const String &timeStr) {
    File file = LittleFS.open("/punches.txt", "a");
    if (file) {
        file.print(tagms);
        file.print(",");
        file.print(tagid);
        file.print(",");
        file.print(dateStr);
        file.print(",");
        file.println(timeStr);
        file.close();
    }
}

void storageClearOfflineQueue() {
    if (LittleFS.exists("/punches.txt")) {
        LittleFS.remove("/punches.txt");
    }
}

int storageGetOfflineQueueCount() {
    if (!LittleFS.exists("/punches.txt")) return 0;
    File file = LittleFS.open("/punches.txt", "r");
    if (!file) return 0;

    int count = 0;
    while (file.available()) {
        String line = file.readStringUntil('\n');
        line.trim();
        if (line.length() > 0) count++;
    }
    file.close();
    return count;
}

String storageGetOfflineQueueContents() {
    if (!LittleFS.exists("/punches.txt")) return "";
    File file = LittleFS.open("/punches.txt", "r");
    if (!file) return "";

    String contents = file.readString();
    file.close();
    return contents;
}

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

void storageSyncOfflinePunches(const String &hostUri, const String &apiToken) {
    if (hostUri.length() == 0 || WiFi.status() != WL_CONNECTED) return;
    if (!LittleFS.exists("/punches.txt")) return;

    File file = LittleFS.open("/punches.txt", "r");
    if (!file || file.size() == 0) {
        if (file) file.close();
        return;
    }

    File tempFile = LittleFS.open("/punches_tmp.txt", "w");

    bool isHttps = hostUri.startsWith("https");
    const uint16_t kSyncTimeoutMs = 5000;
    const int kMaxSyncPerCall = 3;
    int attempted = 0;

    while (file.available()) {
        String line = file.readStringUntil('\n');
        line.trim();
        if (line.length() == 0) continue;

        if (attempted >= kMaxSyncPerCall) {
            tempFile.println(line);
            continue;
        }

        int comma1 = line.indexOf(',');
        int comma2 = line.indexOf(',', comma1 + 1);
        int comma3 = line.indexOf(',', comma2 + 1);

        if (comma1 != -1 && comma2 != -1 && comma3 != -1) {
            attempted++;
            String tagms = line.substring(0, comma1);
            String tagid = line.substring(comma1 + 1, comma2);
            String dt = line.substring(comma2 + 1, comma3);
            String tim = line.substring(comma3 + 1);

            String url = hostUri;
            url += (url.indexOf('?') >= 0 ? "&" : "?");
            url += "tagms=" + urlEncode(tagms) + "&tagid=" + urlEncode(tagid) + "&dt=" + urlEncode(dt) + "&tim=" + urlEncode(tim);

            Serial.printf("[SYNC REQUEST] Syncing queued punch: %s\n", url.c_str());

            HTTPClient http;
            http.setTimeout(kSyncTimeoutMs);

            int httpCode = 0;
            if (isHttps) {
                WiFiClientSecure *clientSec = new WiFiClientSecure();
                clientSec->setInsecure();
                clientSec->setBufferSizes(2048, 1024);
                clientSec->setTimeout(kSyncTimeoutMs);
                http.begin(*clientSec, url);
                if (apiToken.length() > 0) http.addHeader("Authorization", "Bearer " + apiToken);
                httpCode = http.GET();
                http.end();
                delete clientSec;
            } else {
                WiFiClient *clientPln = new WiFiClient();
                clientPln->setTimeout(kSyncTimeoutMs);
                http.begin(*clientPln, url);
                if (apiToken.length() > 0) http.addHeader("Authorization", "Bearer " + apiToken);
                httpCode = http.GET();
                http.end();
                delete clientPln;
            }

            Serial.printf("[SYNC RESPONSE] HTTP Code: %d\n", httpCode);

            if (httpCode != 200 && httpCode != 201) {
                tempFile.println(line); // Failed - keep in queue
            }
        }
        yield();
    }

    file.close();
    tempFile.close();

    LittleFS.remove("/punches.txt");
    LittleFS.rename("/punches_tmp.txt", "/punches.txt");
}
