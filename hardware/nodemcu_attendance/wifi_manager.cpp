#include "wifi_manager.h"
#include "web_portal.h"
#include "audio.h"
#include "storage.h"
#include <ESP8266WiFi.h>
#include <ESP8266mDNS.h>
#include <time.h>

static bool apMode = false;
static bool portalStarted = false;
static unsigned long disconnectedStart = 0;
static const unsigned long AP_FALLBACK_TIMEOUT_MS = 30000; // 30s before showing fallback AP

bool wifiIsAPMode() { return apMode; }

static void startMDNSAndPortal(Config &cfg) {
    const char *domain = strlen(cfg.mdns_name) > 0 ? cfg.mdns_name : DEFAULT_MDNS_NAME;
    MDNS.begin(domain);
    MDNS.addService("http", "tcp", 80);

    if (!portalStarted) {
        webPortalStart();
        portalStarted = true;
    }
    Serial.printf("[mDNS] Terminal accessible at http://%s.local\n", domain);
}

void wifiStartAPMode(Config &cfg) {
    apMode = true;
    WiFi.persistent(false);
    WiFi.setOutputPower(17.5);

    // Keep STA active if credentials exist so auto-reconnect can work in background!
    if (strlen(cfg.wifi_ssid) > 0) {
        WiFi.mode(WIFI_AP_STA);
    } else {
        WiFi.mode(WIFI_AP);
    }

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
    while (WiFi.status() != WL_CONNECTED && attempts < 30) { // 30 * 500ms = 15 seconds initial wait
        delay(500);
        yield();
        attempts++;
        if (attempts % 4 == 0) Serial.print(".");
    }
    Serial.println();

    if (WiFi.status() == WL_CONNECTED) {
        apMode = false;
        disconnectedStart = 0;
        Serial.printf("[WIFI] Connected cleanly! IP Address: %s | RSSI: %d dBm\n", 
                      WiFi.localIP().toString().c_str(), WiFi.RSSI());
        configTime(cfg.tz_offset, 0, "pool.ntp.org", "time.nist.gov");
        startMDNSAndPortal(cfg);
        beepSuccess();
    } else {
        Serial.printf("[WIFI] Initial connection attempt to '%s' pending. Starting dual AP+STA mode...\n", cfg.wifi_ssid);
        disconnectedStart = millis();
        wifiStartAPMode(cfg);
    }
}

void wifiLoop(Config &cfg) {
    MDNS.update();

    // Check actual connection status from hardware driver
    if (WiFi.status() == WL_CONNECTED) {
        // If we were in AP mode, switch cleanly back to pure Station mode!
        if (apMode) {
            apMode = false;
            WiFi.mode(WIFI_STA); // Disable SoftAP radio!
            Serial.printf("[WIFI] Re-connected to '%s'! IP: %s (SoftAP disabled)\n", 
                          cfg.wifi_ssid, WiFi.localIP().toString().c_str());
            beepSuccess();
        }
        disconnectedStart = 0;
        return;
    }

    // Wi-Fi is currently disconnected
    if (strlen(cfg.wifi_ssid) == 0) return; // No Wi-Fi configured

    if (!apMode) {
        if (disconnectedStart == 0) {
            disconnectedStart = millis();
        } else if (millis() - disconnectedStart >= AP_FALLBACK_TIMEOUT_MS) {
            Serial.println("[WIFI] Wi-Fi connection lost for >30s. Enabling fallback AP mode (192.168.4.1)...");
            wifiStartAPMode(cfg);
        }
    }
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
