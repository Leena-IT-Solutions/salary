#ifndef DISPLAY_H
#define DISPLAY_H

#include <Arduino.h>

void displayInit();

void displayShowScreen(const String &companyName, const String &cardMsg,
                        const String &customMsg, const String &clockStr,
                        const String &statusLine, char modeChar);

#endif // DISPLAY_H
