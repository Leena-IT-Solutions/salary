#ifndef CONFIG_H
#define CONFIG_H

#include <Arduino.h>

// ==========================================
// Hardware Pin Definitions (NodeMCU ESP8266)
// ==========================================
#define OLED_SDA_PIN    4   // NodeMCU D2 (GPIO4)
#define OLED_SCL_PIN    5   // NodeMCU D1 (GPIO5)

#define PN532_SDA_PIN   4   // NodeMCU D2 (GPIO4) - Shared I2C
#define PN532_SCL_PIN   5   // NodeMCU D1 (GPIO5) - Shared I2C

#define BUZZER_PIN      14  // NodeMCU D5 (GPIO14)
#define BUTTON_PIN      12  // NodeMCU D6 (GPIO12)

// ==========================================
// I2C Addresses & Settings
// ==========================================
#define OLED_I2C_ADDR   0x3C // 1.3" OLED I2C Address (0x3C or 0x3D)
#define PN532_I2C_ADDR  0x24 // PN532 RFID I2C Address

// ==========================================
// Wi-Fi Access Point & Config Mode
// ==========================================
#define AP_SSID         "attendance"
#define AP_PASSWORD     "password"
#define AP_IP           "192.168.4.1"
#define MDNS_NAME       "attendance" // Resolves to http://attendance.local

// ==========================================
// Default API Configuration
// ==========================================
#define DEFAULT_SERVER_URL  "http://192.168.0.100:8000/attendance/save"
#define DEFAULT_DEVICE_ID   "DEV_001"

// EEPROM / Memory Storage Config
#define EEPROM_SIZE         512
#define MAX_QUEUE_ITEMS     50

struct Config {
    char wifi_ssid[64];
    char wifi_pass[64];
    char server_url[128];
    char device_id[32];
    bool configured;
};

#endif // CONFIG_H
