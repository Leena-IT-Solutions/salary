#include "wifi_manager.h"
#include "web_portal.h"
#include "audio.h"
#include "storage.h"
#include <ESP8266WiFi.h>
#include <ESP8266mDNS.h>
#include <time.h>

static bool apMode = false;
static unsigned long lastReconnectAttempt = 0;
static const unsigned long RECONNECT_INTERVAL_MS = 20000; // retry every 20s
static const uint8_t MAX_RECONNECT_FAILURES_BEFORE_AP = 6;
static uint8_t reconnectFailures = 0;

bool wifiIsAPMode() { return apMode; }

static void startMDNSAndPortal(Config &cfg) {
    const char *domain = strlen(cfg.mdns_name) > 0 ? cfg.mdns_name : DEFAULT_MDNS_NAME;
    MDNS.begin(domain);
    MDNS.addService("http", "tcp", 80);
    webPortalStart();
    Serial.printf("[mDNS] Terminal accessible at http://%s.local\n", domain);
}

void wifiStartAPMode(Config &cfg) {
    apMode = true;
    WiFi.persistent(false);
    WiFi.mode(WIFI_AP);
    WiFi.setOutputPower(17.5);

    IPAddress local_IP(192, 168, 4, 1);
    IPAddress gateway(192, 168, 4, 1);
    IPAddress subnet(255, 255, 255, 0);

    String apSsid = String(cfg.ap_ssid);
    String apPass = String(cfg.ap_pass);
    if (apSsid.length() == 0) apSsid = DEFAULT_AP_SSID;
    if (apPass.length() == 0) apPass = DEFAULT_AP_PASS;

    WiFi.softAPConfig(local_IP, gateway, subnet);
    WiFi.softAP(apSsid.c_str(), apPass.c_str());

    Serial.printf("[WIFI AP] Access Point started: SSID='%s' | IP=192.168.4.1\n", apSsid.c_str());

    startMDNSAndPortal(cfg);
    beep(100, 3);
}

void wifiConnect(Config &cfg) {
    if (strlen(cfg.wifi_ssid) == 0) {
        Serial.println("[WIFI] No Wi-Fi SSID configured. Starting AP Mode.");
        wifiStartAPMode(cfg);
        return;
    }

    Serial.printf("[WIFI] Connecting to Wi-Fi SSID: '%s'...\n", cfg.wifi_ssid);

    WiFi.persistent(false);
    WiFi.setAutoReconnect(true);
    WiFi.mode(WIFI_STA);
    WiFi.setOutputPower(17.5);
    WiFi.begin(cfg.wifi_ssid, cfg.wifi_pass);

    int attempts = 0;
    while (WiFi.status() != WL_CONNECTED && attempts < 35) { // 35 * 500ms = 17.5 seconds
        delay(500);
        yield();
        attempts++;
        if (attempts % 4 == 0) Serial.print(".");
    }
    Serial.println();

    if (WiFi.status() == WL_CONNECTED) {
        apMode = false;
        reconnectFailures = 0;
        lastReconnectAttempt = millis(); // Reset reconnect timer!
        Serial.printf("[WIFI] Connected cleanly! IP Address: %s | RSSI: %d dBm\n", 
                      WiFi.localIP().toString().c_str(), WiFi.RSSI());
        configTime(cfg.tz_offset, 0, "pool.ntp.org", "time.nist.gov");
        startMDNSAndPortal(cfg);
        beepSuccess();
    } else {
        Serial.printf("[WIFI] Failed to connect to '%s' (Status Code %d). Falling back to AP Mode.\n", 
                      cfg.wifi_ssid, WiFi.status());
        wifiStartAPMode(cfg);
    }
}

void wifiLoop(Config &cfg) {
    MDNS.update();

    if (apMode) return;

    if (WiFi.status() == WL_CONNECTED) {
        reconnectFailures = 0;
        lastReconnectAttempt = millis();
        return;
    }

    unsigned long now = millis();
    if (now - lastReconnectAttempt < RECONNECT_INTERVAL_MS) return;
    lastReconnectAttempt = now;

    reconnectFailures++;
    Serial.printf("[WIFI] Connection status disconnected! Reconnect attempt %d/%d to '%s'...\n", 
                  reconnectFailures, MAX_RECONNECT_FAILURES_BEFORE_AP, cfg.wifi_ssid);

    if (reconnectFailures >= MAX_RECONNECT_FAILURES_BEFORE_AP) {
        Serial.println("[WIFI] Continuous disconnect limit reached. Falling back to AP Mode.");
        wifiStartAPMode(cfg);
        return;
    }

    WiFi.disconnect();
    WiFi.begin(cfg.wifi_ssid, cfg.wifi_pass);
}

void wifiApplyLiveWifiCredentials(Config &cfg) {
    if (strlen(cfg.wifi_ssid) > 0) {
        Serial.printf("[WIFI] Live applying new Wi-Fi credentials for SSID: '%s'\n", cfg.wifi_ssid);
        wifiConnect(cfg);
    }
}

void wifiApplyLiveApCredentials(Config &cfg) {
    if (apMode) {
        WiFi.softAP(cfg.ap_ssid, cfg.ap_pass);
    }
}
