#include "rfid.h"
#include "config.h"
#include <Adafruit_PN532.h>
#include <Wire.h>

// -1/-1: this board has no IRQ or RESET line wired to the PN532 module
// (see the comment in config.h). Passing real GPIO numbers here (as the
// old hand-rolled driver's call site did) would make the real library's
// reset() toggle that pin as a digital output, corrupting the shared I2C
// bus - this must stay -1/-1 for this hardware.
static Adafruit_PN532 nfc(PN532_IRQ_PIN, PN532_RESET_PIN, &Wire);

bool rfidInit() {
    nfc.begin();
    uint32_t versiondata = nfc.getFirmwareVersion();
    if (!versiondata) {
        return false;
    }
    nfc.SAMConfig();
    return true;
}

bool rfidPoll(uint8_t *uid, uint8_t *uidLength, uint16_t timeoutMs) {
    return nfc.readPassiveTargetID(PN532_MIFARE_ISO14443A, uid, uidLength,
                                    timeoutMs);
}

static uint8_t defaultKeyA[6] = {0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF};

// Blocks 4 and 5 are both plain data blocks within sector 1 - a single
// authentication against block 4 covers the whole sector, so no second
// auth call is needed to reach block 5.
static const uint8_t kMessageBlocks[2] = {4, 5};

static void logHex(const char *label, const uint8_t *data, size_t len) {
    Serial.print(label);
    for (size_t i = 0; i < len; i++) Serial.printf("%02X ", data[i]);
    Serial.println();
}

bool rfidReadMessage(uint8_t *uid, uint8_t uidLength, char *out,
                      size_t outSize) {
    if (!nfc.mifareclassic_AuthenticateBlock(uid, uidLength, kMessageBlocks[0],
                                              0, defaultKeyA)) {
        Serial.println("[RFID] Read: authentication FAILED");
        return false;
    }

    uint8_t raw[32] = {0};
    for (uint8_t i = 0; i < 2; i++) {
        if (!nfc.mifareclassic_ReadDataBlock(kMessageBlocks[i], raw + i * 16)) {
            Serial.printf("[RFID] Read: block %u read FAILED\n", kMessageBlocks[i]);
            return false;
        }
    }
    logHex("[RFID] Read: raw = ", raw, sizeof(raw));

    // Stop at the first null/non-printable byte, like a normal C string.
    // The old version skipped over non-printable bytes and kept scanning
    // the whole window, which could stitch a leftover fragment from an
    // older write (sitting later in the same 20-byte span) onto the
    // current value instead of ignoring it.
    size_t idx = 0;
    for (int k = 0; k < RFID_MESSAGE_MAX_LEN && idx + 1 < outSize; k++) {
        if (raw[k] < 32 || raw[k] > 126) break;
        out[idx++] = (char)raw[k];
    }
    out[idx] = '\0';
    Serial.printf("[RFID] Read: decoded = \"%s\"\n", out);
    return idx > 0;
}

// Mifare Classic's EEPROM needs a few ms after a block write for the cell
// to actually finish committing. Reading it back immediately (zero delay)
// can see stale pre-write data and look like a failed write even though
// it would have genuinely taken - this settle delay avoids that false
// negative.
static const uint16_t kWriteSettleMs = 10;

// Writes 32 bytes (data[0..15] -> kMessageBlocks[0], data[16..31] ->
// kMessageBlocks[1]) and reads them back to confirm they actually landed.
// Adafruit_PN532's WriteDataBlock only confirms the PN532 got an ACK for
// the command - never that the card actually persisted it - so both
// Write and Clear/Format/Delete need this same verify, not just Write.
static bool writeAndVerifyBlocks(uint8_t *data) {
    for (uint8_t i = 0; i < 2; i++) {
        if (!nfc.mifareclassic_WriteDataBlock(kMessageBlocks[i], data + i * 16)) {
            Serial.printf("[RFID] Write: block %u write FAILED\n", kMessageBlocks[i]);
            return false;
        }
        delay(kWriteSettleMs);
    }

    uint8_t verify[32] = {0};
    for (uint8_t i = 0; i < 2; i++) {
        if (!nfc.mifareclassic_ReadDataBlock(kMessageBlocks[i], verify + i * 16)) {
            Serial.printf("[RFID] Write: verify read of block %u FAILED\n", kMessageBlocks[i]);
            return false;
        }
    }
    logHex("[RFID] Write: verify raw  = ", verify, 32);

    if (memcmp(data, verify, 32) != 0) {
        Serial.println("[RFID] Write: verification MISMATCH - write did not actually take");
        return false;
    }
    return true;
}

bool rfidWriteMessage(uint8_t *uid, uint8_t uidLength, const char *value) {
    if (!nfc.mifareclassic_AuthenticateBlock(uid, uidLength, kMessageBlocks[0],
                                              0, defaultKeyA)) {
        Serial.println("[RFID] Write: authentication FAILED");
        return false;
    }

    uint8_t raw[32] = {0};
    strncpy((char *)raw, value, RFID_MESSAGE_MAX_LEN);
    logHex("[RFID] Write: intended raw = ", raw, sizeof(raw));

    return writeAndVerifyBlocks(raw);
}

bool rfidClearMessage(uint8_t *uid, uint8_t uidLength) {
    if (!nfc.mifareclassic_AuthenticateBlock(uid, uidLength, kMessageBlocks[0],
                                              0, defaultKeyA)) {
        Serial.println("[RFID] Clear: authentication FAILED");
        return false;
    }

    uint8_t raw[32] = {0};
    return writeAndVerifyBlocks(raw);
}

struct KeyCandidate {
    const char *name;
    uint8_t key[6];
};

// The most commonly reused Mifare Classic default/leftover keys in the
// wild - factory default, the two NFC Forum standard keys (MAD sector /
// NDEF data sectors), and an all-zero key some tools reset cards to.
static const KeyCandidate kKnownKeys[] = {
    {"FFFFFFFFFFFF (factory default)", {0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF}},
    {"A0A1A2A3A4A5 (NFC Forum MAD key)", {0xA0, 0xA1, 0xA2, 0xA3, 0xA4, 0xA5}},
    {"D3F7D3F7D3F7 (NFC Forum NDEF key)", {0xD3, 0xF7, 0xD3, 0xF7, 0xD3, 0xF7}},
    {"000000000000 (blank/reset)", {0x00, 0x00, 0x00, 0x00, 0x00, 0x00}},
    {"AABBCCDDEEFF (common default)", {0xAA, 0xBB, 0xCC, 0xDD, 0xEE, 0xFF}},
    {"4D3A99C351DD (common default)", {0x4D, 0x3A, 0x99, 0xC3, 0x51, 0xDD}},
};

void rfidDiagnoseCard(uint8_t *uid, uint8_t uidLength) {
    Serial.println("[DIAGNOSE] Trying known keys (A and B) against all 16 sectors - this takes a few seconds, keep the card on the reader...");
    bool foundAny = false;

    for (uint8_t sector = 0; sector <= 15; sector++) {
        uint8_t block = sector * 4; // first block of the sector

        for (uint8_t k = 0; k < sizeof(kKnownKeys) / sizeof(kKnownKeys[0]); k++) {
            for (uint8_t keyNumber = 0; keyNumber <= 1; keyNumber++) {
                uint8_t keyCopy[6];
                memcpy(keyCopy, kKnownKeys[k].key, 6);

                if (!nfc.mifareclassic_AuthenticateBlock(uid, uidLength, block, keyNumber, keyCopy)) {
                    continue;
                }

                foundAny = true;
                Serial.printf("[DIAGNOSE] Sector %u (block %u) authenticates with Key %s = %s\n",
                              sector, block, keyNumber == 0 ? "A" : "B", kKnownKeys[k].name);

                uint8_t data[16] = {0};
                if (nfc.mifareclassic_ReadDataBlock(block, data)) {
                    Serial.print("  Hex:   ");
                    for (int i = 0; i < 16; i++) Serial.printf("%02X ", data[i]);
                    Serial.println();
                    Serial.print("  ASCII: ");
                    for (int i = 0; i < 16; i++) {
                        char c = (data[i] >= 32 && data[i] <= 126) ? (char)data[i] : '.';
                        Serial.print(c);
                    }
                    Serial.println();
                } else {
                    Serial.println("  (authenticated, but read failed)");
                }
            }
        }
        // Long scan (16 sectors x keys x 2 key types) - yield so Wi-Fi/OS
        // housekeeping still runs and the software watchdog doesn't reset
        // the board mid-scan.
        yield();
        Serial.printf("[DIAGNOSE] ...sector %u done\n", sector);
    }

    if (!foundAny) {
        Serial.println("[DIAGNOSE] No known key authenticated any of the 16 sectors.");
        Serial.println("[DIAGNOSE] The old machine likely used a custom key not in this list.");
    }
}
