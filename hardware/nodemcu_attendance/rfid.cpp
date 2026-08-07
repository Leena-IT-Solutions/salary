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
            uint8_t dumpUid[7]; uint8_t dumpLen = 0;
            nfc.readPassiveTargetID(PN532_MIFARE_ISO14443A, dumpUid, &dumpLen, 10);
        }
    }
    Serial.printf("[RFID] Auth FAILED on Block %u (all candidate keys rejected)\n", blockNum);
    return false;
}

// Extract clean string from raw block bytes (Supports both NDEF Text Records & Plain ASCII)
static void extractMessageString(const uint8_t *raw, size_t rawLen, char *out, size_t outSize) {
    out[0] = '\0';
    
    // Check if block contains NDEF TLV (0x03 Tag or 0xD1 NDEF Record Header)
    int textStart = -1;
    int textLen = 0;

    for (size_t i = 0; i < rawLen - 4; i++) {
        // Look for NDEF Text Record Header 'T' (0x54) preceded by NDEF header 0xD1 0x01
        if (raw[i] == 0xD1 && raw[i+1] == 0x01 && raw[i+3] == 0x54) {
            uint8_t statusByte = raw[i+4];
            uint8_t langLen = statusByte & 0x1F;
            textStart = i + 5 + langLen;
            textLen = raw[i+2] - 1 - langLen;
            break;
        }
        // Direct NDEF Text Record payload check
        if (raw[i] == 0x54 && (raw[i+1] == 0x02 || raw[i+1] == 0x05) && (raw[i+2] == 'e' || raw[i+2] == 'E')) {
            textStart = i + 4;
            textLen = 16;
            break;
        }
    }

    if (textStart >= 0 && (size_t)textStart < rawLen) {
        size_t idx = 0;
        for (int k = 0; k < textLen && (textStart + k) < (int)rawLen && idx + 1 < outSize; k++) {
            char c = (char)raw[textStart + k];
            if (c < 32 || c > 126) break;
            out[idx++] = c;
        }
        out[idx] = '\0';
        if (idx > 0) return;
    }

    // Fallback: Read plain ASCII string starting at first printable byte
    size_t idx = 0;
    for (size_t k = 0; k < rawLen && idx + 1 < outSize; k++) {
        char c = (char)raw[k];
        if (c >= 32 && c <= 126) {
            out[idx++] = c;
        } else if (idx > 0) {
            break;
        }
    }
    out[idx] = '\0';
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

    extractMessageString(raw, sizeof(raw), out, outSize);
    Serial.printf("[RFID] Read: decoded = \"%s\"\n", out);
    return strlen(out) > 0;
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

// Writes text as a Universal NFC Forum NDEF Text Record (Compatible with Android & iPhone!)
bool rfidWriteMessage(uint8_t *uid, uint8_t uidLength, const char *value) {
    if (!authenticateCardBlock(uid, uidLength, kMessageBlocks[0])) {
        return false;
    }

    uint8_t raw[32] = {0};
    uint8_t textLen = strlen(value);
    if (textLen > RFID_MESSAGE_MAX_LEN) textLen = RFID_MESSAGE_MAX_LEN;

    // NDEF Text Record TLV Structure (NFC Forum Spec)
    // 0x03 = NDEF TLV Tag
    // [len] = NDEF Record Length
    // 0xD1 = Header (MB=1, ME=1, CF=0, SR=1, IL=0, TNF=1)
    // 0x01 = Type Length (1 byte: 'T')
    // [payload_len] = Language len (3) + Text len
    // 'T' = Text Record Type
    // 0x02 = UTF-8, 2-byte language code ("en")
    // 'e', 'n' = Language
    // [text] = Employee Code
    // 0xFE = NDEF Terminator TLV

    raw[0] = 0x03; // NDEF TLV Tag
    raw[1] = textLen + 7; // NDEF Message Length
    raw[2] = 0xD1; // Header
    raw[3] = 0x01; // Type Length ('T')
    raw[4] = textLen + 3; // Payload Length
    raw[5] = 0x54; // Record Type: 'T'
    raw[6] = 0x02; // UTF-8, 2-byte lang
    raw[7] = 'e';  // 'e'
    raw[8] = 'n';  // 'n'

    memcpy(raw + 9, value, textLen);
    raw[9 + textLen] = 0xFE; // NDEF Terminator

    logHex("[RFID] Write NDEF: intended raw = ", raw, sizeof(raw));

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
