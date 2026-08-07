#include "wifi_manager.h"
#include "web_portal.h"
#include "udp_config.h"
#include "audio.h"
#include <ESP8266WiFi.h>
#include <ESP8266mDNS.h>
#include <time.h>

static bool apMode = false;
static unsigned long lastReconnectAttempt = 0;
static const unsigned long RECONNECT_INTERVAL_MS = 20000; // retry every 20s
// ~6 * 20s = 2 minutes of retrying a dropped connection before giving up
// and falling back to the config portal.
static const uint8_t MAX_RECONNECT_FAILURES_BEFORE_AP = 6;
static uint8_t reconnectFailures = 0;

bool wifiIsAPMode() { return apMode; }

static bool udpStarted = false;

static void startMDNSAndPortal(Config &cfg) {
    MDNS.begin(DEFAULT_MDNS_NAME);
    MDNS.addService("http", "tcp", 80);
    webPortalStart();
    // Only needs binding once - the UDP socket keeps working across an
    // AP<->STA mode switch since it isn't tied to a specific interface.
    if (!udpStarted) {
        udpConfigStart(&cfg);
        udpStarted = true;
    }
}

void wifiStartAPMode(Config &cfg) {
    apMode = true;
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

    startMDNSAndPortal(cfg);
    beep(100, 3);
}

void wifiConnect(Config &cfg) {
    if (strlen(cfg.wifi_ssid) == 0) {
        wifiStartAPMode(cfg);
        return;
    }

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

    if (apMode) return; // an intentional/fallback AP session isn't "dropped"

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
        WiFi.disconnect();
        WiFi.begin(cfg.wifi_ssid, cfg.wifi_pass);
    }
}

void wifiApplyLiveApCredentials(Config &cfg) {
    if (apMode) {
        WiFi.softAP(cfg.ap_ssid, cfg.ap_pass);
    }
}
