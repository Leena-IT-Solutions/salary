#include "wifi_manager.h"
#include "web_portal.h"
#include "udp_config.h"
#include "audio.h"
#include "storage.h"
#include <ESP8266WiFi.h>
#include <ESP8266mDNS.h>
#include <time.h>

static bool apMode = false;
static bool smartConfigActive = false;
static unsigned long lastReconnectAttempt = 0;
static const unsigned long RECONNECT_INTERVAL_MS = 20000; // retry every 20s
static const uint8_t MAX_RECONNECT_FAILURES_BEFORE_AP = 6;
static uint8_t reconnectFailures = 0;

bool wifiIsAPMode() { return apMode; }

static bool udpStarted = false;

static void startMDNSAndPortal(Config &cfg) {
    MDNS.begin(DEFAULT_MDNS_NAME);
    MDNS.addService("http", "tcp", 80);
    webPortalStart();
    if (!udpStarted) {
        udpConfigStart(&cfg);
        udpStarted = true;
    }
}

void wifiStartAPMode(Config &cfg) {
    apMode = true;
    WiFi.persistent(false);
    WiFi.mode(WIFI_AP_STA); // AP + STA mode so SmartConfig can listen!
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

    // Start SmartConfig background listener
    if (!smartConfigActive) {
        WiFi.beginSmartConfig();
        smartConfigActive = true;
        Serial.println("[SMARTCONFIG] Listening for SmartConfig packets...");
    }

    startMDNSAndPortal(cfg);
    beep(100, 3);
}

void wifiConnect(Config &cfg) {
    if (strlen(cfg.wifi_ssid) == 0) {
        wifiStartAPMode(cfg);
        return;
    }

    WiFi.persistent(false);
    WiFi.mode(WIFI_STA);
    WiFi.setOutputPower(17.5);
    WiFi.begin(cfg.wifi_ssid, cfg.wifi_pass);

    int attempts = 0;
    while (WiFi.status() != WL_CONNECTED && attempts < 20) {
        delay(500);
        yield();
        attempts++;
    }

    if (WiFi.status() == WL_CONNECTED) {
        if (smartConfigActive) {
            WiFi.stopSmartConfig();
            smartConfigActive = false;
        }
        apMode = false;
        reconnectFailures = 0;
        configTime(cfg.tz_offset, 0, "pool.ntp.org", "time.nist.gov");
        startMDNSAndPortal(cfg);
        beepSuccess();
    } else {
        wifiStartAPMode(cfg);
    }
}

void wifiLoop(Config &cfg) {
    MDNS.update();
    udpConfigLoop();

    // Check if SmartConfig successfully received credentials
    if (smartConfigActive && WiFi.smartConfigDone()) {
        String newSsid = WiFi.SSID();
        String newPass = WiFi.psk();
        if (newSsid.length() > 0) {
            strncpy(cfg.wifi_ssid, newSsid.c_str(), sizeof(cfg.wifi_ssid));
            strncpy(cfg.wifi_pass, newPass.c_str(), sizeof(cfg.wifi_pass));
            storageSaveConfig(cfg);
            Serial.printf("[SMARTCONFIG] Received Wi-Fi Credentials! SSID: %s\n", cfg.wifi_ssid);
            WiFi.stopSmartConfig();
            smartConfigActive = false;
            beepSuccess();
            wifiConnect(cfg);
            return;
        }
    }

    if (apMode) return;

    if (WiFi.status() == WL_CONNECTED) {
        reconnectFailures = 0;
        return;
    }

    unsigned long now = millis();
    if (now - lastReconnectAttempt < RECONNECT_INTERVAL_MS) return;
    lastReconnectAttempt = now;

    reconnectFailures++;
    if (reconnectFailures > MAX_RECONNECT_FAILURES_BEFORE_AP) {
        wifiStartAPMode(cfg);
        return;
    }

    WiFi.disconnect();
    WiFi.begin(cfg.wifi_ssid, cfg.wifi_pass);
}

void wifiApplyLiveWifiCredentials(Config &cfg) {
    if (strlen(cfg.wifi_ssid) > 0) {
        if (smartConfigActive) {
            WiFi.stopSmartConfig();
            smartConfigActive = false;
        }
        WiFi.disconnect();
        WiFi.begin(cfg.wifi_ssid, cfg.wifi_pass);
    }
}

void wifiApplyLiveApCredentials(Config &cfg) {
    if (apMode) {
        WiFi.softAP(cfg.ap_ssid, cfg.ap_pass);
    }
}
