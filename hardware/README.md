# NodeMCU SalaryManager Attendance Hardware Firmware

Complete firmware and circuit specifications for the **NodeMCU ESP8266 Biometric & RFID Attendance Terminal**.

---

## 🛠️ Hardware Requirements

- **NodeMCU ESP8266** (ESP-12E / CP2102)
- **1.3" OLED Display** (SH1106 / SSD1306, I2C, 128x64)
- **PN532 NFC / RFID Module** (Configured in I2C Mode via 2-position DIP switch)
- **Active 5V Buzzer**
- **Tactile Switch / Push Button**
- **Custom Power & Header PCB**

---

## ⚡ Circuit & Pinout Summary

| Component | NodeMCU Pin | GPIO | Function |
|---|---|---|---|
| **OLED & PN532 SCL** | D1 | GPIO5 | Shared I2C Clock Line |
| **OLED & PN532 SDA** | D2 | GPIO4 | Shared I2C Data Line |
| **Buzzer (+)** | D5 | GPIO14 | Audio Beep Feedback |
| **Tactile Button** | D6 | GPIO12 | Config Reset / Mode Switch (Active LOW) |
| **VCC (+3.3V / +5V)** | 3V3 / VIN | - | Power Supply Rail |
| **Ground** | GND | - | Common Ground Plane |

> 📌 **Full Schematic & Diagram:** Refer to [`CIRCUIT_DIAGRAM.md`](./CIRCUIT_DIAGRAM.md).

---

## 💻 Software Setup & Libraries Required

Install the following libraries in the **Arduino IDE Library Manager**:

1. **Adafruit SH1106** (or `Adafruit_SSD1306`)
2. **Adafruit GFX Library**
3. **Adafruit PN532**
4. **ArduinoJson** (v6.x)

---

## 🚀 Flashing & Setup Guide

1. Open `hardware/nodemcu_attendance/nodemcu_attendance.ino` in Arduino IDE.
2. Select Board: **NodeMCU 1.0 (ESP-12E Module)**.
3. Upload speed: **115200**.
4. Flash the code to your NodeMCU.

---

## 🌐 Initial Wi-Fi & Terminal Setup

1. On first boot (or if Wi-Fi isn't configured), the terminal creates an Access Point:
   - **AP SSID**: `attendance`
   - **AP Password**: `password`
   - **IP Address**: `http://192.168.4.1`
2. Connect your phone or laptop to `attendance` Wi-Fi and open `http://192.168.4.1` or `http://attendance.local`.
3. Enter your Wi-Fi SSID, Password, Server URL (e.g. `https://payroll.sarvodayavidyalay.com/attendance/save` or `http://192.168.0.100:8000/attendance/save`), and Device Code.
4. Click **Save & Connect**. The device will connect to your local network.

---

## 🔄 Features & Resilience

- **mDNS Domain**: Access local configuration page via `http://attendance.local`.
- **Audio & Display Feedback**: Real-time scan result on OLED + buzzer tone.
- **Offline Storage Queue**: If Wi-Fi/Internet drops, punches are stored in LittleFS flash memory and auto-synced as soon as connection is restored.
- **Hardware Reset Button**: Hold tactile switch on D6 for >3 seconds to wipe stored Wi-Fi settings and re-enter AP Config Mode.
