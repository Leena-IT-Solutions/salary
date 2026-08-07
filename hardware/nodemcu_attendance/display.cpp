#include "display.h"
#include "config.h"
#include <U8g2lib.h>
#include <Wire.h>

// U8g2's "F" (full frame buffer) constructor handles both SH1106 and
// SSD1306 correctly without needing the column-offset guesswork the old
// hand-rolled driver did. Explicit clock/data pins keep it on the same
// shared I2C bus as the PN532 regardless of Wire's default pin selection.
#if defined(OLED_DRIVER_SSD1306)
static U8G2_SSD1306_128X64_NONAME_F_HW_I2C u8g2(
    U8G2_R0, /* reset= */ U8X8_PIN_NONE, /* clock= */ OLED_SCL_PIN,
    /* data= */ OLED_SDA_PIN);
#else
static U8G2_SH1106_128X64_NONAME_F_HW_I2C u8g2(
    U8G2_R0, /* reset= */ U8X8_PIN_NONE, /* clock= */ OLED_SCL_PIN,
    /* data= */ OLED_SDA_PIN);
#endif

void displayInit() {
    u8g2.setI2CAddress(OLED_I2C_ADDR << 1); // u8g2 wants the 8-bit address
    u8g2.begin();
    u8g2.setFontMode(0);
    u8g2.setDrawColor(1);
    u8g2.clearBuffer();
    u8g2.sendBuffer();
}

static void drawCentered(int baselineY, const char *text,
                          const uint8_t *font) {
    u8g2.setFont(font);
    int w = u8g2.getStrWidth(text);
    int x = (128 - w) / 2;
    if (x < 0) x = 0;
    u8g2.drawStr(x, baselineY, text);
}

void displayShowScreen(const String &companyName, const String &cardMsg,
                        const String &customMsg, const String &clockStr,
                        const String &statusLine, char modeChar) {
    u8g2.clearBuffer();

    drawCentered(9, companyName.c_str(), u8g2_font_6x10_tf);

    if (cardMsg.length() > 0) {
        // Large centered display for employee code (same 20pt font as digital clock)
        drawCentered(48, cardMsg.c_str(), u8g2_font_logisoso20_tf);
    } else if (customMsg.length() > 0) {
        drawCentered(34, customMsg.c_str(), u8g2_font_6x10_tf);
    } else {
        drawCentered(48, clockStr.c_str(), u8g2_font_logisoso20_tf);
    }

    u8g2.setFont(u8g2_font_6x10_tf);
    u8g2.drawStr(0, 63, statusLine.c_str());
    char modeStr[2] = {modeChar, '\0'};
    u8g2.drawStr(118, 63, modeStr);

    u8g2.sendBuffer();
}
