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
    strncpy(cfg.wifi_ssid, "", sizeof(cfg.wifi_ssid));
    strncpy(cfg.wifi_pass, "", sizeof(cfg.wifi_pass));
    strncpy(cfg.company_name, DEFAULT_COMPANY_NAME, sizeof(cfg.company_name));
    strncpy(cfg.location_name, DEFAULT_LOCATION, sizeof(cfg.location_name));
    strncpy(cfg.host_uri, DEFAULT_HOST_URI, sizeof(cfg.host_uri));
    strncpy(cfg.api_token, "", sizeof(cfg.api_token));
    strncpy(cfg.device_code, DEFAULT_DEVICE_CODE, sizeof(cfg.device_code));
    strncpy(cfg.card_value, "", sizeof(cfg.card_value));
    strncpy(cfg.portal_user, DEFAULT_PORTAL_USER, sizeof(cfg.portal_user));
    strncpy(cfg.portal_pass, DEFAULT_PORTAL_PASS, sizeof(cfg.portal_pass));
    cfg.op_mode = MODE_READ;
    cfg.tz_offset = 19800; // IST UTC+5:30
}

void storageSaveConfig(Config &cfg) {
    cfg.magic = CONFIG_MAGIC;
    EEPROM.put(0, cfg);
    EEPROM.commit();
}

void storageLoadConfig(Config &cfg) {
    EEPROM.begin(EEPROM_SIZE);
    EEPROM.get(0, cfg);

    if (cfg.magic != CONFIG_MAGIC) {
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

    if (strlen(cfg.ap_ssid) == 0) strncpy(cfg.ap_ssid, DEFAULT_AP_SSID, sizeof(cfg.ap_ssid));
    if (strlen(cfg.ap_pass) == 0) strncpy(cfg.ap_pass, DEFAULT_AP_PASS, sizeof(cfg.ap_pass));
    if (strlen(cfg.company_name) == 0) strncpy(cfg.company_name, DEFAULT_COMPANY_NAME, sizeof(cfg.company_name));
    if (strlen(cfg.host_uri) == 0) strncpy(cfg.host_uri, DEFAULT_HOST_URI, sizeof(cfg.host_uri));
    if (strlen(cfg.device_code) == 0) strncpy(cfg.device_code, DEFAULT_DEVICE_CODE, sizeof(cfg.device_code));
    if (strlen(cfg.portal_user) == 0) strncpy(cfg.portal_user, DEFAULT_PORTAL_USER, sizeof(cfg.portal_user));
    if (strlen(cfg.portal_pass) == 0) strncpy(cfg.portal_pass, DEFAULT_PORTAL_PASS, sizeof(cfg.portal_pass));
}

void storageResetConfig(Config &cfg) {
    cfg.magic = 0x00000000;
    EEPROM.put(0, cfg);
    EEPROM.commit();
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

void storageSyncOfflinePunches(const String &hostUri,
                                const String &apiToken) {
    if (WiFi.status() != WL_CONNECTED || !LittleFS.exists("/punches.txt")) {
        return;
    }

    File file = LittleFS.open("/punches.txt", "r");
    if (!file || file.size() == 0) {
        if (file) file.close();
        return;
    }

    File tempFile = LittleFS.open("/punches_tmp.txt", "w");

    WiFiClientSecure clientSecure;
    WiFiClient clientPlain;
    HTTPClient http;

    bool isHttps = hostUri.startsWith("https");
    if (isHttps) clientSecure.setInsecure();

    // Each request blocks the whole device (display, button, web portal)
    // for as long as it takes - a backlog of several queued punches with
    // an unreachable/slow server could otherwise stall for the sum of
    // every individual request's timeout. Cap both the per-request
    // timeout and how many are attempted per call, so a large backlog
    // drains gradually across multiple 30s cycles with only a short,
    // bounded pause each time instead of one long freeze.
    const uint16_t kSyncTimeoutMs = 3000;
    const int kMaxSyncPerCall = 3;
    int attempted = 0;

    while (file.available()) {
        String line = file.readStringUntil('\n');
        line.trim();
        if (line.length() == 0) continue;

        if (attempted >= kMaxSyncPerCall) {
            tempFile.println(line); // past this cycle's cap - keep queued
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
            url += "tagms=" + tagms + "&tagid=" + tagid + "&dt=" + dt + "&tim=" + tim;

            clientSecure.setTimeout(kSyncTimeoutMs);
            clientPlain.setTimeout(kSyncTimeoutMs);
            if (isHttps) {
                http.begin(clientSecure, url);
            } else {
                http.begin(clientPlain, url);
            }
            http.setTimeout(kSyncTimeoutMs);

            if (apiToken.length() > 0) {
                http.addHeader("Authorization", "Bearer " + apiToken);
            }

            int httpCode = http.GET();
            http.end();

            if (httpCode != 200) {
                tempFile.println(line);
            }
        }
        yield();
    }

    file.close();
    tempFile.close();

    LittleFS.remove("/punches.txt");
    LittleFS.rename("/punches_tmp.txt", "/punches.txt");
}
