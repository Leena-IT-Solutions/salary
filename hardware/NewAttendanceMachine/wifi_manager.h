#ifndef WIFI_MANAGER_H
#define WIFI_MANAGER_H

#include <Arduino.h>
#include "config.h"

void wifiConnect(Config &cfg);
void wifiStartAPMode(Config &cfg);
void wifiLoop(Config &cfg);
bool wifiIsAPMode();

void wifiApplyLiveWifiCredentials(Config &cfg);
void wifiApplyLiveApCredentials(Config &cfg);

#endif // WIFI_MANAGER_H
