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
    Serial.printf("[UDP CONFIG] Listening on UDP Port %d\n", UDP_CONFIG_PORT);
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

void udpSendBroadcast(const String &message, uint16_t port) {
    IPAddress broadcastIp(255, 255, 255, 255);
    udp.beginPacket(broadcastIp, port);
    udp.write((const uint8_t *)message.c_str(), message.length());
    udp.endPacket();
    Serial.printf("[UDP BROADCAST] Sent to 255.255.255.255:%d -> %s\n", port, message.c_str());
}

void udpSendBeacon() {
    if (config == nullptr) return;
    String ipStr = wifiIsAPMode() ? "192.168.4.1" : WiFi.localIP().toString();
    String beacon = "DISCOVER_TERMINAL:" + String(config->device_code) + ":" + ipStr + ":" + String(config->company_name);
    udpSendBroadcast(beacon, UDP_CONFIG_PORT);
}

void udpConfigLoop() {
    int packetSize = udp.parsePacket();
    if (packetSize <= 0 || config == nullptr) return;

    char buf[512];
    int len = udp.read(buf, sizeof(buf) - 1);
    if (len <= 0) return;
    buf[len] = '\0';
    String packetStr = String(buf);
    packetStr.trim();

    IPAddress remoteIp = udp.remoteIP();
    uint16_t remotePort = udp.remotePort();

    Serial.printf("[UDP CONFIG] Packet received from %s:%d -> %s\n", 
                  remoteIp.toString().c_str(), remotePort, packetStr.c_str());

    bool wifiChanged = false;

    // Format 1: Plain Text Command "SET_WIFI:ssid:pass"
    if (packetStr.startsWith("SET_WIFI:")) {
        int c1 = packetStr.indexOf(':', 9);
        if (c1 != -1) {
            String ssid = packetStr.substring(9, c1);
            String pass = packetStr.substring(c1 + 1);
            strncpy(config->wifi_ssid, ssid.c_str(), sizeof(config->wifi_ssid));
            strncpy(config->wifi_pass, pass.c_str(), sizeof(config->wifi_pass));
            wifiChanged = true;
            Serial.printf("[UDP CONFIG] Set Wi-Fi SSID: %s\n", ssid.c_str());
        }
    }
    // Format 2: Discovery Query "DISCOVER_PING"
    else if (packetStr == "DISCOVER_PING") {
        udpSendBeacon();
        return;
    }
    // Format 3: Standard JSON Packet {"wifi_ssid":"...", "wifi_pass":"..."}
    else {
        JsonDocument doc;
        if (!deserializeJson(doc, buf)) {
            applyIfPresent(config->wifi_ssid, sizeof(config->wifi_ssid), doc, "wifi_ssid", &wifiChanged);
            applyIfPresent(config->wifi_pass, sizeof(config->wifi_pass), doc, "wifi_pass", &wifiChanged);
            applyIfPresent(config->host_uri, sizeof(config->host_uri), doc, "host_uri");
            applyIfPresent(config->api_token, sizeof(config->api_token), doc, "api_token");
            applyIfPresent(config->company_name, sizeof(config->company_name), doc, "company_name");
        }
    }

    if (wifiChanged) {
        storageSaveConfig(*config);
        wifiApplyLiveWifiCredentials(*config);

        JsonDocument ackDoc;
        ackDoc["status"] = "ok";
        ackDoc["message"] = "Wi-Fi credentials updated successfully";
        char ackBuf[128];
        size_t ackLen = serializeJson(ackDoc, ackBuf, sizeof(ackBuf));

        udp.beginPacket(remoteIp, remotePort);
        udp.write((const uint8_t *)ackBuf, ackLen);
        udp.endPacket();

        Serial.println("[UDP CONFIG] Applied Wi-Fi credentials and sent ACK.");
    }
}
