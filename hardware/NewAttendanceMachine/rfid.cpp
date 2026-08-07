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
    {"A0A1A2A3A4A5 (NFC Forum MAD Sector Key A)", {0xA0, 0xA1, 0xA2, 0xA3, 0xA4, 0xA5}},
    {"D3F7D3F7D3F7 (NFC Forum NDEF Sector Key A)", {0xD3, 0xF7, 0xD3, 0xF7, 0xD3, 0xF7}},
    {"B0B1B2B3B4B5 (NFC Forum MAD Sector Key B)", {0xB0, 0xB1, 0xB2, 0xB3, 0xB4, 0xB5}},
    {"E1E2E3E4E5E6 (NFC Forum NDEF Sector Key B)", {0xE1, 0xE2, 0xE3, 0xE4, 0xE5, 0xE6}},
    {"000000000000 (blank / zero key)", {0x00, 0x00, 0x00, 0x00, 0x00, 0x00}},
    {"AABBCCDDEEFF (generic default)", {0xAA, 0xBB, 0xCC, 0xDD, 0xEE, 0xFF}},
    {"1A2B3C4D5E6F (generic default)", {0x1A, 0x2B, 0x3C, 0x4D, 0x5E, 0x6F}},
    {"123456789ABC (sequential default)", {0x12, 0x34, 0x56, 0x78, 0x9A, 0xBC}},
    {"4D3A99C351DD (NXP application key)", {0x4D, 0x3A, 0x99, 0xC3, 0x51, 0xDD}},
    {"1A982C7E459A (NXP application key)", {0x1A, 0x98, 0x2C, 0x7E, 0x45, 0x9A}},
    {"8FD7B4069230 (NXP application key)", {0x8F, 0xD7, 0xB4, 0x06, 0x92, 0x30}},
    {"B5FF67CBAECC (NXP application key)", {0xB5, 0xFF, 0x67, 0xCB, 0xAE, 0xCC}},
    {"714C5C886E97 (NXP application key)", {0x71, 0x4C, 0x5C, 0x88, 0x6E, 0x97}},
    {"587EE5F9350F (NXP application key)", {0x58, 0x7E, 0xE5, 0xF9, 0x35, 0x0F}},
    {"A0B1C2D3E4F5 (access control key)", {0xA0, 0xB1, 0xC2, 0xD3, 0xE4, 0xF5}},
    {"414243444546 (ASCII ABCDEF key)", {0x41, 0x42, 0x43, 0x44, 0x45, 0x46}},
};

static const uint8_t kMessageBlocks[2] = {4, 5};

static void logHex(const char *label, const uint8_t *data, size_t len) {
#if ENABLE_SERIAL
    Serial.print(label);
    for (size_t i = 0; i < len; i++) Serial.printf("%02X ", data[i]);
    Serial.println();
#endif
}

static bool authenticateCardBlock(uint8_t *uid, uint8_t uidLength, uint8_t blockNum) {
    for (uint8_t k = 0; k < sizeof(kKnownKeys) / sizeof(kKnownKeys[0]); k++) {
        uint8_t key[6];
        memcpy(key, kKnownKeys[k].key, 6);
        if (nfc.mifareclassic_AuthenticateBlock(uid, uidLength, blockNum, 0, key)) {
            return true;
        }
        nfc.inListPassiveTarget();
        if (nfc.mifareclassic_AuthenticateBlock(uid, uidLength, blockNum, 1, key)) {
            return true;
        }
        nfc.inListPassiveTarget();
    }
    return false;
}

bool rfidReadMessage(uint8_t *uid, uint8_t uidLength, char *out, size_t outSize) {
    if (out == nullptr || outSize == 0) return false;
    out[0] = '\0';

    if (!authenticateCardBlock(uid, uidLength, kMessageBlocks[0])) {
        return false;
    }

    uint8_t buf[32];
    memset(buf, 0, sizeof(buf));

    if (!nfc.mifareclassic_ReadDataBlock(kMessageBlocks[0], &buf[0])) {
        return false;
    }
    nfc.mifareclassic_ReadDataBlock(kMessageBlocks[1], &buf[16]);

    logHex("[RFID] Read raw bytes: ", buf, sizeof(buf));

    size_t textStart = 0;
    bool foundNDEFHeader = false;

    // Standard NDEF Text Record check (header 0x02 'e' 'n')
    if (buf[0] == 0x03 && buf[1] > 0) { // NDEF TLV payload
        for (size_t i = 2; i < 28; i++) {
            if (buf[i] == 0x02 && buf[i+1] == 'e' && buf[i+2] == 'n') {
                textStart = i + 3;
                foundNDEFHeader = true;
                break;
            }
        }
    }

    if (!foundNDEFHeader) {
        // Fallback ASCII scan across blocks
        for (size_t i = 0; i < sizeof(buf); i++) {
            if (isalnum((char)buf[i])) {
                textStart = i;
                break;
            }
        }
    }

    size_t outIdx = 0;
    for (size_t i = textStart; i < sizeof(buf) && outIdx < outSize - 1; i++) {
        char c = (char)buf[i];
        if (c == 0xFE || c == '\0' || (uint8_t)c < 32 || (uint8_t)c > 126) break;
        out[outIdx++] = c;
    }
    out[outIdx] = '\0';

    LOG_PRINTF("[RFID] Decoded card text: '%s'\n", out);
    return (outIdx > 0);
}

bool rfidWriteMessage(uint8_t *uid, uint8_t uidLength, const char *value) {
    if (value == nullptr) return false;

    if (!authenticateCardBlock(uid, uidLength, kMessageBlocks[0])) {
        return false;
    }

    size_t len = strlen(value);
    if (len > RFID_MESSAGE_MAX_LEN) len = RFID_MESSAGE_MAX_LEN;

    uint8_t buf[32];
    memset(buf, 0, sizeof(buf));

    // NDEF Text Record Header
    buf[0] = 0x03; // TLV Type = NDEF Message
    buf[1] = len + 7; // TLV Length
    buf[2] = 0xD1; // Record Header (MB/ME/SR/TNF=Well-Known)
    buf[3] = 0x01; // Type Length
    buf[4] = len + 3; // Payload Length (Status + 'en' + text)
    buf[5] = 'T';  // Record Type = Text
    buf[6] = 0x02; // Status Byte (UTF-8, 2-byte lang code)
    buf[7] = 'e';  // Lang = en
    buf[8] = 'n';

    memcpy(&buf[9], value, len);
    buf[9 + len] = 0xFE; // Terminator TLV

    logHex("[RFID] Writing NDEF bytes: ", buf, sizeof(buf));

    bool ok1 = nfc.mifareclassic_WriteDataBlock(kMessageBlocks[0], &buf[0]);
    bool ok2 = nfc.mifareclassic_WriteDataBlock(kMessageBlocks[1], &buf[16]);

    return (ok1 && ok2);
}

bool rfidClearMessage(uint8_t *uid, uint8_t uidLength) {
    if (!authenticateCardBlock(uid, uidLength, kMessageBlocks[0])) {
        return false;
    }

    uint8_t blank[16];
    memset(blank, 0, sizeof(blank));

    bool ok1 = nfc.mifareclassic_WriteDataBlock(kMessageBlocks[0], blank);
    bool ok2 = nfc.mifareclassic_WriteDataBlock(kMessageBlocks[1], blank);

    return (ok1 && ok2);
}

void rfidDiagnoseCard(uint8_t *uid, uint8_t uidLength) {
    LOG_PRINTLN("[RFID DIAG] Running diagnostic scan...");
    for (uint8_t sector = 0; sector < 16; sector++) {
        uint8_t block = sector * 4;
        bool auth = authenticateCardBlock(uid, uidLength, block);
        if (auth) {
            uint8_t buf[16];
            if (nfc.mifareclassic_ReadDataBlock(block, buf)) {
                LOG_PRINTF("[RFID DIAG] Sector %2d authenticated! First Block %2d data: ", sector, block);
                logHex("", buf, 16);
            }
        }
    }
}
