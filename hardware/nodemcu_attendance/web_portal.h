#ifndef WEB_PORTAL_H
#define WEB_PORTAL_H

#include <Arduino.h>
#include "config.h"

void webPortalInit(Config *cfg, void (*onConfigSaved)());
void webPortalStart();
void webPortalHandleClient();
void webPortalSetWriteResult(bool success);

// Recent activity helper
String getRecentScansHtml();

#endif // WEB_PORTAL_H
