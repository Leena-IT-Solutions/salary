#ifndef WIFI_MANAGER_H
#define WIFI_MANAGER_H

#include <Arduino.h>
#include "config.h"

// Initial connect attempt at boot: tries stored STA credentials, falls
// back to SoftAP config-portal mode if they're empty or don't connect.
void wifiConnect(Config &cfg);

// Forces SoftAP config-portal mode (used by the button-hold reset too).
void wifiStartAPMode(Config &cfg);

// Call once per main loop() iteration. Services mDNS and, when previously
// connected in STA mode, periodically retries a dropped connection instead
// of requiring a manual power-cycle - only falls back to AP mode after
// several minutes of failed retries.
void wifiLoop(Config &cfg);

bool wifiIsAPMode();

// Re-applies STA/AP credentials live (without reboot) after the config
// portal saves a change to them.
void wifiApplyLiveWifiCredentials(Config &cfg);
void wifiApplyLiveApCredentials(Config &cfg);

#endif // WIFI_MANAGER_H
