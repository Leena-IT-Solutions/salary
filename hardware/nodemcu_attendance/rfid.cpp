#include "rfid.h"
#include "config.h"
#include <Adafruit_PN532.h>
#include <Wire.h>

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

struct KeyCandidate {
    const char *name;
    uint8_t key[6];
};

static const KeyCandidate kKnownKeys[] = {
    {"FFFFFFFFFFFF (factory default)", {0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF}},
    {"A0A1A2A3A4A5 (NFC Forum MAD key)", {0xA0, 0xA1, 0xA2, 0xA3, 0xA4, 0xA5}},
    {"D3F7D3F7D3F7 (NFC Forum NDEF key)", {0xD3, 0xF7, 0xD3, 0xF7, 0xD3, 0xF7}},
    {"000000000000 (blank/reset)", {0x00, 0x00, 0x00, 0x00, 0x00, 0x00}},
    {"AABBCCDDEEFF (common default)", {0xAA, 0xBB, 0xCC, 0xDD, 0xEE, 0xFF}},
    {"4D3A99C351DD (common default)", {0x4D, 0x3A, 0x99, 0xC3, 0x51, 0xDD}},
};

static const uint8_t kMessageBlocks[2] = {4, 5};

static void logHex(const char *label, const uint8_t *data, size_t len) {
    Serial.print(label);
    for (size_t i = 0; i < len; i++) Serial.printf("%02X ", data[i]);
    Serial.println();
}

// Multi-Key Authentication with automatic HALT-state card re-selection
static bool authenticateCardBlock(uint8_t *uid, uint8_t uidLength, uint8_t blockNum) {
    for (uint8_t k = 0; k < sizeof(kKnownKeys) / sizeof(kKnownKeys[0]); k++) {
        for (uint8_t keyType = 0; keyType <= 1; keyType++) { // 0 = Key A, 1 = Key B
            uint8_t keyCopy[6];
            memcpy(keyCopy, kKnownKeys[k].key, 6);

            if (nfc.mifareclassic_AuthenticateBlock(uid, uidLength, blockNum, keyType, keyCopy)) {
                Serial.printf("[RFID] Auth SUCCESS on Block %u using Key %s (%s)\n", 
                              blockNum, keyType == 0 ? "A" : "B", kKnownKeys[k].name);
                return true;
            }
            // If auth failed, card enters HALT state - re-select target to reset RF state machine
            uint8_t dumpUid[7]; uint8_t dumpLen = 0;
            nfc.readPassiveTargetID(PN532_MIFARE_ISO14443A, dumpUid, &dumpLen, 10);
        }
    }
    Serial.printf("[RFID] Auth FAILED on Block %u (all candidate keys rejected)\n", blockNum);
    return false;
}

bool rfidReadMessage(uint8_t *uid, uint8_t uidLength, char *out, size_t outSize) {
    if (!authenticateCardBlock(uid, uidLength, kMessageBlocks[0])) {
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

    size_t idx = 0;
    for (int k = 0; k < RFID_MESSAGE_MAX_LEN && idx + 1 < outSize; k++) {
        if (raw[k] < 32 || raw[k] > 126) break;
        out[idx++] = (char)raw[k];
    }
    out[idx] = '\0';
    Serial.printf("[RFID] Read: decoded = \"%s\"\n", out);
    return idx > 0;
}

static const uint16_t kWriteSettleMs = 10;

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
    if (!authenticateCardBlock(uid, uidLength, kMessageBlocks[0])) {
        return false;
    }

    uint8_t raw[32] = {0};
    strncpy((char *)raw, value, RFID_MESSAGE_MAX_LEN);
    logHex("[RFID] Write: intended raw = ", raw, sizeof(raw));

    return writeAndVerifyBlocks(raw);
}

bool rfidClearMessage(uint8_t *uid, uint8_t uidLength) {
    if (!authenticateCardBlock(uid, uidLength, kMessageBlocks[0])) {
        return false;
    }

    uint8_t raw[32] = {0};
    return writeAndVerifyBlocks(raw);
}

void rfidDiagnoseCard(uint8_t *uid, uint8_t uidLength) {
    Serial.println("[DIAGNOSE] Trying known keys (A and B) against all 16 sectors - this takes a few seconds, keep card on reader...");
    bool foundAny = false;

    for (uint8_t sector = 0; sector <= 15; sector++) {
        uint8_t block = sector * 4;

        for (uint8_t k = 0; k < sizeof(kKnownKeys) / sizeof(kKnownKeys[0]); k++) {
            for (uint8_t keyNumber = 0; keyNumber <= 1; keyNumber++) {
                uint8_t keyCopy[6];
                memcpy(keyCopy, kKnownKeys[k].key, 6);

                if (!nfc.mifareclassic_AuthenticateBlock(uid, uidLength, block, keyNumber, keyCopy)) {
                    uint8_t dumpUid[7]; uint8_t dumpLen = 0;
                    nfc.readPassiveTargetID(PN532_MIFARE_ISO14443A, dumpUid, &dumpLen, 10);
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
        yield();
    }

    if (!foundAny) {
        Serial.println("[DIAGNOSE] No known key authenticated any of the 16 sectors.");
    }
}
