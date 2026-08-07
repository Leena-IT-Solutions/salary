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
| **Buzzer (+)** | D7 | GPIO13 | Audio Beep Feedback |
| **Tactile Button** | D4 | GPIO2 | Config Reset / Mode Switch (Active LOW, boot-strapping pin - see below) |
| **VCC (+3.3V / +5V)** | 3V3 / VIN | - | Power Supply Rail |
| **Ground** | GND | - | Common Ground Plane |

> 📌 **Full Schematic & Diagram:** Refer to [`CIRCUIT_DIAGRAM.md`](./CIRCUIT_DIAGRAM.md).

The PN532 module's **IRQ and RESET lines are not wired** to the NodeMCU on this board - only VCC/GND/SDA/SCL are connected. This is intentional (see `config.h`); don't wire them up without also updating `PN532_IRQ_PIN`/`PN532_RESET_PIN`.

---

## 💻 Software Setup & Libraries Required

The sketch is split across several `.ino`/`.h`/`.cpp` files in `nodemcu_attendance/` (Arduino IDE compiles all of them together automatically - just open the `.ino`). It depends on three libraries, installable from the **Arduino IDE Library Manager**:

1. **Adafruit PN532** (by Adafruit) - RFID/NFC reader driver
2. **U8g2** (by oliver) - OLED driver; auto-handles both SH1106 and SSD1306 128x64 controllers
3. **ArduinoJson** (v7.x, by Benoit Blanchon) - parses the backend's `{employee, message}` response

Board package: **esp8266 by ESP8266 Community** (Boards Manager URL: `http://arduino.esp8266.com/stable/package_esp8266com_index.json`).

If your OLED shows a blank or garbled screen, check `config.h`'s `OLED_DRIVER_SH1106` / `OLED_DRIVER_SSD1306` flag - it must match the controller chip actually on your board.

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
3. Log into the config portal (see **Portal Login** below), then enter your Wi-Fi SSID, Password, and Server URL (e.g. `https://payroll.sarvodayavidyalay.com/attendance/save` or `http://192.168.0.100:8000/attendance/save`).
4. Click **Save Settings**. The device will connect to your local network live, without rebooting.

### 🔐 Portal Login

The config portal (`/`, `/save`, `/write_card`) is protected with HTTP Basic Auth:

- **Default username**: `admin`
- **Default password**: `changeme`

**Change these from the "Portal Admin Login" card on first login** - anyone on the same Wi-Fi network or connected to the device's AP can otherwise reconfigure the terminal (repoint it at a different server, arm card-writing mode, etc.) if left on the default credentials.

---

## 🖥️ Display Layout

- **Top line**: Company / institution name (centered).
- **Middle (idle)**: Large digital clock, `HH:MM:SS`.
- **Middle (on card tap, Read mode)**: The tapped card's Employee Code (`tagms`) replaces the clock for ~2 seconds - the terminal doesn't show a separate "Saved"/"Invalid" message on screen; that result is communicated entirely through the buzzer tone (see below). Write/Format/Delete/Clear/Setup modes still show a more detailed two-line result (e.g. "Card Written OK!") since those are admin/maintenance actions, not the everyday attendance tap.
- **Bottom line**: IP address (left, or the AP address `192.168.4.1` while in config mode) and the current mode letter (right) - `R`/`W`/`F`/`D`/`C`/`S`.

## 🔊 Buzzer Patterns

| Event | Pattern |
|---|---|
| Power applied, boot starting | Rising 3-tone jingle |
| Wi-Fi connected / entered AP mode | Double beep (STA) or triple beep (AP) |
| **Boot finished, ready to scan** | Two quick high beeps |
| **Card tapped** (any mode) | One long beep (~350ms) |
| Attendance saved successfully | Two short high beeps |
| Already marked / duplicate punch | One medium beep |
| Invalid card / error / offline-queued | One long low beep (error) or two medium beeps (offline) |
| Mode changed via button | One short beep |
| Factory reset triggered | Three beeps |

## 🔘 Tactile Switch (D4)

- **Short press** (press and release in under 10 seconds): cycles the operation mode through the safe subset only - **Read → Write → Setup → Read**. Format/Delete/Clear are intentionally *not* reachable from the button (only from the web portal's Operation Mode dropdown) so a stray extra press can't leave the terminal armed to silently erase a card on the next tap. If the portal had set a destructive mode, the next button press returns straight to Read.
- **Long press (hold ≥10 seconds)**: factory reset - wipes all stored settings (Wi-Fi, portal login, server URL, everything) and re-enters AP config mode. Ten seconds is intentionally long to avoid accidental resets.
- ⚠️ **D4 is GPIO2, an ESP8266 boot-mode strapping pin** - it must read HIGH when the board powers on or resets for it to boot from flash normally. Don't hold the button down while plugging in power or triggering a reset; only press it after the terminal has already finished booting (once you hear the "ready" beep).

---

## 🔄 Features & Resilience

- **mDNS Domain**: Access local configuration page via `http://attendance.local` in both AP mode and normal (STA) mode.
- **Offline Storage Queue**: If Wi-Fi/Internet drops, punches are stored in LittleFS flash memory and auto-synced as soon as connection is restored. Syncing is capped at 3 punches per 30-second cycle (each request has a 3s timeout) so a large backlog drains gradually instead of freezing the device for a long stretch in one go. View what's currently queued, and clear it manually if needed, from **`/queue`** in the web portal (linked from the dashboard's "Offline Queue" card).
- **Automatic Wi-Fi Reconnect**: If a previously-connected Wi-Fi network drops, the device retries every 20 seconds for up to ~2 minutes before falling back to AP config mode - no manual power-cycle needed for a transient outage.
- **Clock-Safety**: Attendance punches are only sent/queued once the device's clock has synced via NTP after connecting to Wi-Fi - a card tapped in the few seconds right after boot (before NTP sync) triggers an error beep instead of recording a punch with a guessed date.

---

## 📶 Remote Config via UDP

As a faster alternative to opening the AP-mode web portal, the device also listens for config updates on **UDP port `7778`** (`UDP_CONFIG_PORT` in `config.h`), in both AP and STA mode.

**Payload** (JSON, sent as a single UDP datagram):
```json
{
  "auth": "admin:changeme",
  "wifi_ssid": "MySchoolWiFi",
  "wifi_pass": "wifipassword",
  "host_uri": "https://payroll.sarvodayavidyalay.com/attendance/save",
  "api_token": "",
  "company_name": "Sarvodaya Vidyalay",
  "device_code": "SAR24101"
}
```
- `auth` must be `"<portal_user>:<portal_pass>"` (the same credentials as the web portal login). Only include the fields you want to change - anything omitted is left as-is.
- On success the device replies with `{"status":"ok"}` from the same port. **On bad/missing auth it sends no reply at all** (by design, to avoid the port being usable as a "does an admin account exist here" probe) - check the device's Serial log if a packet doesn't seem to have worked.

**⚠️ This only works when the sender is on the same local network as the device:**
- A cloud-hosted Laravel backend (e.g. the default `payroll.sarvodayavidyalay.com`) **cannot** reach a device sitting behind a school/office router over UDP unless that router port-forwards `7778` to the device's local IP - extra networking setup per site, and fragile unless that IP is DHCP-reserved.
- For a **brand-new, never-configured device**, the sender must still join the device's own `attendance` AP network first (same as the web-portal flow) - UDP doesn't remove that one-time step, it's just a scriptable alternative to filling in the HTML form once connected to it.
- The clean use case is **reconfiguring a device that's already joined the school's LAN**: from any machine on that same LAN (e.g. an on-premise admin tool, or IT staff's laptop), UDP can push new Wi-Fi/server settings without ever touching AP mode again.

---

## 💳 Card Storage (Write Mode)

The "Write Card" tool (web portal, or Write mode via the button) burns a message onto the tapped card - this is the `tagms` value read back on every attendance tap.

- **Max length: 20 characters** (`RFID_MESSAGE_MAX_LEN` in `config.h`). It's stored across two Mifare Classic data blocks (block 4 + block 5, both in sector 1, one Key-A authentication covers both) - a single block is only 16 bytes, so 20 characters spans into the second block.
- The portal enforces this limit server-side (not just via the input field's `maxlength`), so what you type is exactly what gets written - no silent truncation.
- Format/Delete modes zero out both blocks, and Setup mode's diagnostic scan (see below) also reads/authenticates against these same blocks when investigating an unfamiliar card.
- Cards written by a *different* device (e.g. an older/other machine) won't necessarily use this same key/block scheme - see the Setup-mode diagnostic below before assuming a blank read means a blank card.

### 🔎 Diagnosing Unknown Cards (Setup Mode)

Tapping a card while in **Setup (S)** mode does more than show the UID - it also tries a handful of well-known Mifare Classic default keys (as both Key A and Key B) against all 16 sectors, and prints what it finds to the Serial Monitor (115200 baud). This takes several seconds - keep the card steady on the reader until you see `[DIAGNOSE] ...sector 15 done`. Useful when a card doesn't behave as expected (e.g. it was written by different/unknown firmware) and you need to know what key/sector it actually uses before deciding whether to just re-write it with this device's Write mode instead.

---

## 📡 Backend API Contract

This firmware is only one half of the system - it talks to the SalaryManager Laravel backend's attendance-machine endpoint. Both sides must agree on this exact shape (see `salary/app/Http/Controllers/AttendanceMachineController.php::save()`):

**Request** — `GET {host_uri}?tagid=<hex UID>&tagms=<employee_code>&dt=YYYY-MM-DD&tim=HH:MM`
Optional header: `Authorization: Bearer <api_token>`

**Response** — `{"employee": "<first name>", "message": "Success" | "Already Exists" | "Invalid Employee"}`

The firmware branches its buzzer feedback on the exact strings `"Success"` and `"Already Exists"` (anything else, including `"Invalid Employee"`, plays the error tone). If this contract ever changes on the backend, `processCardScan()` in `nodemcu_attendance.ino` must be updated to match.
