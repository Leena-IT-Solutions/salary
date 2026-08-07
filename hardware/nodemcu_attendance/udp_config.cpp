#include "udp_config.h"
#include "storage.h"
#include "wifi_manager.h"
#include <WiFiUdp.h>
#include <ArduinoJson.h>

static WiFiUDP udp;
static Config *config = nullptr;

void udpConfigStart(Config *cfg) {
    config = cfg;
    udp.begin(UDP_CONFIG_PORT);
}

static void applyIfPresent(char *dest, size_t destSize, JsonDocument &doc,
                            const char *key, bool *changedFlag = nullptr) {
    const char *v = doc[key] | (const char *)nullptr;
    if (v != nullptr) {
        strncpy(dest, v, destSize);
        dest[destSize - 1] = '\0';
        if (changedFlag) *changedFlag = true;
    }
}

void udpConfigLoop() {
    int packetSize = udp.parsePacket();
    if (packetSize <= 0 || config == nullptr) return;

    char buf[512];
    int len = udp.read(buf, sizeof(buf) - 1);
    if (len <= 0) return;
    buf[len] = '\0';

    // Snapshot the sender before doing anything else - reading another
    // packet later would overwrite these.
    IPAddress remoteIp = udp.remoteIP();
    uint16_t remotePort = udp.remotePort();

    JsonDocument doc;
    if (deserializeJson(doc, buf)) {
        Serial.println("[UDP CONFIG] Malformed JSON packet, ignoring.");
        return;
    }

    const char *givenAuth = doc["auth"] | "";
    String expectedAuth = String(config->portal_user) + ":" + String(config->portal_pass);
    if (strlen(givenAuth) == 0 || String(givenAuth) != expectedAuth) {
        // No response on purpose - don't let this port act as a "yes, an
        // admin account exists here" oracle for anything scanning the LAN.
        Serial.println("[UDP CONFIG] Unauthorized packet, dropping silently.");
        return;
    }

    bool wifiChanged = false;
    applyIfPresent(config->wifi_ssid, sizeof(config->wifi_ssid), doc, "wifi_ssid", &wifiChanged);
    applyIfPresent(config->wifi_pass, sizeof(config->wifi_pass), doc, "wifi_pass", &wifiChanged);
    applyIfPresent(config->host_uri, sizeof(config->host_uri), doc, "host_uri");
    applyIfPresent(config->api_token, sizeof(config->api_token), doc, "api_token");
    applyIfPresent(config->company_name, sizeof(config->company_name), doc, "company_name");
    applyIfPresent(config->device_code, sizeof(config->device_code), doc, "device_code");

    storageSaveConfig(*config);
    if (wifiChanged) {
        wifiApplyLiveWifiCredentials(*config);
    }

    JsonDocument ackDoc;
    ackDoc["status"] = "ok";
    char ackBuf[64];
    size_t ackLen = serializeJson(ackDoc, ackBuf, sizeof(ackBuf));

    udp.beginPacket(remoteIp, remotePort);
    udp.write((const uint8_t *)ackBuf, ackLen);
    udp.endPacket();

    Serial.println("[UDP CONFIG] Applied config update.");
}
