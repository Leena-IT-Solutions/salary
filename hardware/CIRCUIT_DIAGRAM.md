# NodeMCU SalaryManager Attendance Hardware — Circuit Diagram & Pinout

This document details the exact hardware schematic, PCB pinout, and wiring mapping identified from the hardware board photos for the **NodeMCU Attendance Terminal**.

---

## 1. Components List

1. **NodeMCU ESP8266** (ESP-12E / CP2102)
2. **1.3" OLED Display** (SH1106 / SSD1306 Driver, I2C Interface, 128x64 pixels)
3. **PN532 NFC / RFID Module** (Red Board, configured in I2C mode)
4. **Active 5V Buzzer** (Audio feedback for scans)
5. **Tactile Push Button** (Wi-Fi config reset & manual punch sync)
6. **Custom PCB** with DC Barrel Jack & 4-pin JST connectors

---

## 2. Pin Mapping Table

| NodeMCU Pin | GPIO | Physical Component | Function / Signal | Notes |
|---|---|---|---|---|
| **D1** | GPIO5 | OLED & PN532 | **I2C SCL** | Shared I2C Clock Line |
| **D2** | GPIO4 | OLED & PN532 | **I2C SDA** | Shared I2C Data Line |
| **D5** | GPIO14 | Buzzer | **Audio Output** | High = Beep On, Low = Off |
| **D6** | GPIO12 | Tactile Switch | **Button Input** | Active LOW (INPUT_PULLUP) |
| **3V3 / VIN** | - | Power Bus | **VCC (+3.3V / +5V)** | Power to OLED, PN532 & Buzzer |
| **GND** | - | Ground Bus | **GND (0V)** | Common Ground Plane |

---

## 3. Component Wiring Details

### A. 1.3" OLED Display (I2C)
- **VCC** → NodeMCU 3V3 / VIN
- **GND** → NodeMCU GND
- **SCL** → NodeMCU **D1** (GPIO5)
- **SDA** → NodeMCU **D2** (GPIO4)
- **I2C Address**: Default `0x3C` (or `0x3D` if jumper set to `0x7A`)

### B. PN532 NFC/RFID Reader (I2C Mode)
> ⚠️ **PN532 DIP Switch Configuration (Verified from Board Photo):**
> 
> | Interface Mode | Switch 1 | Switch 2 | Physical Switch Position |
> |---|---|---|---|
> | **I2C Mode (Selected)** | `0` (OFF / DOWN) | `1` (ON / UP) | **Switch 1 DOWN, Switch 2 UP** |
> | HSU (High Speed UART) | `0` (OFF) | `0` (OFF) | Both Switches DOWN |
> | SPI Mode | `1` (ON) | `0` (OFF) | Switch 1 UP, Switch 2 DOWN |

- **VCC** → NodeMCU 3V3 / VIN
- **GND** → NodeMCU GND
- **SDA** → NodeMCU **D2** (GPIO4)
- **SCL** → NodeMCU **D1** (GPIO5)
- **I2C Address**: `0x24`

### C. Audio Feedback (Buzzer)
- **Positive (+)** → NodeMCU **D5** (GPIO14)
- **Negative (-)** → NodeMCU GND via current-limiting resistor

### D. Tactile Push Button
- **Terminal 1** → NodeMCU **D6** (GPIO12)
- **Terminal 2** → NodeMCU GND
- Pressing the button pulls GPIO12 to `LOW`. Holding for >3s resets Wi-Fi settings & launches Access Point Mode (`attendance` / `password`).

---

## 4. PCB Schematic Layout Diagram

```
                       +-------------------------+
                       |    NodeMCU ESP8266      |
                       |                         |
                       | [USB]                   |
                       |                         |
                       | 3V3  (VCC) ------------+-----> [VCC Bus] (OLED / PN532 / Buzzer)
                       | GND  (0V)  ------------+-----> [GND Bus] (OLED / PN532 / Button)
                       |                         |
                       | D1 (GPIO5 - SCL) -------+-----> OLED SCL & PN532 SCL
                       | D2 (GPIO4 - SDA) -------+-----> OLED SDA & PN532 SDA
                       |                         |
                       | D5 (GPIO14) ------------+-----> Buzzer (+)
                       | D6 (GPIO12) ------------+-----> Tactile Switch -> GND
                       +-------------------------+
```

---

## 5. Network & Software Specifications

- **Default AP SSID**: `attendance`
- **Default AP Password**: `password`
- **AP Configuration IP**: `http://192.168.4.1`
- **mDNS Hostname**: `http://attendance.local`
- **Default Attendance API Endpoint**: `https://payroll.sarvodayavidyalay.com/attendance/save` (or `http://<your-server-ip>:8000/attendance/save`)
