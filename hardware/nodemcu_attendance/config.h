#ifndef CONFIG_H
#define CONFIG_H

#include <Arduino.h>

// ==========================================
// Hardware Pin Definitions (NodeMCU ESP8266)
// ==========================================
#define OLED_SDA_PIN    4   // NodeMCU D2 (GPIO4)
#define OLED_SCL_PIN    5   // NodeMCU D1 (GPIO5)

#define PN532_SDA_PIN   4   // NodeMCU D2 (GPIO4) - Shared I2C Bus
#define PN532_SCL_PIN   5   // NodeMCU D1 (GPIO5) - Shared I2C Bus

#define BUZZER_PIN      13  // NodeMCU D7 (GPIO13)
// GPIO2 is an ESP8266 boot-mode strapping pin - it must read HIGH at
// power-on/reset for the chip to boot normally from flash. INPUT_PULLUP
// keeps it HIGH when idle, but holding the button down WHILE power is
// applied or the board is reset will pull it LOW during boot and can
// prevent the device from starting. Only press this button after the
// terminal has already finished booting.
#define BUTTON_PIN      2   // NodeMCU D4 (GPIO2)

// ==========================================
// I2C Addresses & Settings
// ==========================================
#define OLED_I2C_ADDR   0x3C // 1.3" OLED I2C Address (0x3C or 0x3D)
#define PN532_I2C_ADDR  0x24 // PN532 RFID I2C Address (matches Adafruit_PN532 library default)

// The PN532 module on this board has no IRQ or RESET line wired to the
// NodeMCU (see CIRCUIT_DIAGRAM.md - only VCC/GND/SDA/SCL are connected).
// Adafruit_PN532's I2C constructor still takes irq/reset parameters, but
// passing -1 for both tells the library those pins don't exist so it never
// drives them - passing a real GPIO number here (e.g. a pin shared with the
// I2C bus) would make library's reset() toggle that pin as a digital output
// and corrupt the shared I2C bus.
#define PN532_IRQ_PIN   (-1)
#define PN532_RESET_PIN (-1)

// OLED controller: this board ships with either an SH1106 or SSD1306 driver
// chip under the same "1.3-inch 128x64 I2C OLED" label. Uncomment the one
// that matches your physical board (SH1106 is the far more common chip for
// 1.3" modules; 0.96" modules are almost always SSD1306). If the screen
// stays blank or shows a shifted/garbled image, switch this flag.
#define OLED_DRIVER_SH1106
// #define OLED_DRIVER_SSD1306

// ==========================================
// Default Access Point & Config Settings
// ==========================================
#define DEFAULT_AP_SSID     "attendance"
#define DEFAULT_AP_PASS     "password"
#define DEFAULT_AP_IP       "192.168.4.1"
#define DEFAULT_MDNS_NAME   "attendance" // Accessible as http://attendance.local

#define DEFAULT_COMPANY_NAME "Sarvodaya Vidyalay"
#define DEFAULT_LOCATION     "Main Gate"
#define DEFAULT_HOST_URI     "https://payroll.sarvodayavidyalay.com/attendance/save"
#define DEFAULT_DEVICE_CODE  "SAR24101"

// Default credentials for the local Wi-Fi config portal (HTTP Basic Auth).
// These MUST be changed from the "Terminal & Company Settings" card after
// first boot - anyone on the same Wi-Fi/AP network who knows the defaults
// can otherwise repoint this device's server URL or arm card-writing mode.
#define DEFAULT_PORTAL_USER "admin"
#define DEFAULT_PORTAL_PASS "changeme"

// EEPROM Storage Configuration
#define EEPROM_SIZE         1024
#define MAX_QUEUE_ITEMS     100

// Max length of the message ("tagms") burned onto a card. A single Mifare
// Classic block is 16 bytes, so anything over that spans into a second
// block (still within the same sector, so no extra authentication call is
// needed) - see rfid.cpp's rfidWriteMessage/rfidReadMessage.
#define RFID_MESSAGE_MAX_LEN 20

// UDP port for remote config pushes (Wi-Fi/host/token changes) from a
// machine on the same local network - see README's "Remote Config via
// UDP" section for the payload format and its same-LAN limitation.
#define UDP_CONFIG_PORT     7778

// Operation Modes:
// 0 = Setup (S)
// 1 = Read (R) - Default Attendance Mode
// 2 = Write (W) - Card Burning Mode
// 3 = Format (F) - Format Mifare Card Sectors
// 4 = Delete (D) - Clear Employee Data from Card
// 5 = Clear (C) - Clear Offline Punch Queue
enum OperationMode {
    MODE_SETUP = 0,
    MODE_READ = 1,
    MODE_WRITE = 2,
    MODE_FORMAT = 3,
    MODE_DELETE = 4,
    MODE_CLEAR = 5
};

// Bumped from 0x53414C5B ('SALB') because the Config struct below gained
// new fields (portal_user/portal_pass) - this forces a one-time reset to
// defaults on old boards instead of misreading uninitialized EEPROM bytes
// as a portal username/password.
#define CONFIG_MAGIC 0x53414C5C // Magic Header Key 'SALC'

struct Config {
    uint32_t magic;  // Must equal CONFIG_MAGIC
    char ap_ssid[32];
    char ap_pass[32];
    char wifi_ssid[64];
    char wifi_pass[64];
    char company_name[64];
    char location_name[32];
    char host_uri[128];
    char api_token[64];
    char device_code[32];
    char card_value[32];
    char portal_user[32];
    char portal_pass[32];
    uint8_t op_mode; // OperationMode enum
    long tz_offset;  // Default 19800 for IST UTC+5:30
};

#endif // CONFIG_H
