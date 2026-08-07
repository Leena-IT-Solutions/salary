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
#define BUTTON_PIN      2   // NodeMCU D4 (GPIO2)

// ==========================================
// I2C Addresses & Settings
// ==========================================
#define OLED_I2C_ADDR   0x3C // 1.3" OLED I2C Address (0x3C or 0x3D)
#define PN532_I2C_ADDR  0x24 // PN532 RFID I2C Address

#define PN532_IRQ_PIN   (-1)
#define PN532_RESET_PIN (-1)

#define OLED_DRIVER_SH1106

// ==========================================
// Default Access Point & Config Settings
// ==========================================
#define DEFAULT_AP_SSID     "attendance"
#define DEFAULT_AP_PASS     "password"
#define DEFAULT_AP_IP       "192.168.4.1"
#define DEFAULT_MDNS_NAME   "attendance"

#define DEFAULT_WIFI_SSID   ""
#define DEFAULT_WIFI_PASS   ""

#define DEFAULT_COMPANY_NAME "Sarvodaya Vidyalay"
#define DEFAULT_LOCATION     "Main Gate"
#define DEFAULT_HOST_URI     "https://payroll.sarvodayavidyalay.com/attendance/save"
#define DEFAULT_DEVICE_CODE  "SAR24101"

#define DEFAULT_PORTAL_USER "admin"
#define DEFAULT_PORTAL_PASS "changeme"

// EEPROM Storage Configuration
#define EEPROM_SIZE         1024
#define MAX_QUEUE_ITEMS     100
#define RFID_MESSAGE_MAX_LEN 20
#define UDP_CONFIG_PORT     7778

// Operation Modes:
enum OperationMode {
    MODE_SETUP = 0,
    MODE_READ = 1,
    MODE_WRITE = 2,
    MODE_FORMAT = 3,
    MODE_DELETE = 4,
    MODE_CLEAR = 5
};

#define CONFIG_MAGIC 0x53414C60 // Magic Header Key 'SALG'

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
    char mdns_name[32]; // Domain Name (accessible at http://<mdns_name>.local)
    uint8_t op_mode; // OperationMode enum
    uint8_t buzzer_enabled; // 1 = Enabled, 0 = Muted
    long tz_offset;  // Default 19800 for IST UTC+5:30
};

#endif // CONFIG_H
