#ifndef DISPLAY_H
#define DISPLAY_H

#include <Arduino.h>

// Initializes the OLED over the shared I2C bus. Call after Wire.begin().
void displayInit();

// Renders one full screen. Mirrors the priority the firmware has always
// used: if cardMsg is set, show cardMsg (+ customMsg below it) - this is
// the "tap result" screen. Otherwise if customMsg is set, show just that
// (e.g. a status alert). Otherwise show the big centered clockStr - the
// idle/home screen. statusLine (bottom-left) and modeChar (bottom-right)
// are always drawn.
void displayShowScreen(const String &companyName, const String &cardMsg,
                        const String &customMsg, const String &clockStr,
                        const String &statusLine, char modeChar);

#endif // DISPLAY_H
