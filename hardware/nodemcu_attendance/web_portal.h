#ifndef WEB_PORTAL_H
#define WEB_PORTAL_H

#include <Arduino.h>
#include "config.h"

// Stores the Config pointer the portal reads/writes, and a callback fired
// right after a successful "/save" (used by the .ino to refresh the OLED
// and beep, mirroring the previous inline behavior). Call once from
// setup() before webPortalStart().
void webPortalInit(Config *cfg, void (*onConfigSaved)());

// Registers HTTP routes and starts the server. Called by wifi_manager
// after Wi-Fi/AP comes up (and again on live AP/Wi-Fi changes if needed -
// ESP8266WebServer.begin() is safe to call again to pick up route/state
// changes).
void webPortalStart();

// Call once per main loop() iteration.
void webPortalHandleClient();

// Called by processCardScan() (MODE_WRITE) once a write attempt against a
// tapped card has been decided, so the portal's live "Write Card" status
// polling (see /write_status) can report the real outcome instead of just
// "armed and waiting" forever.
void webPortalSetWriteResult(bool success);

#endif // WEB_PORTAL_H
