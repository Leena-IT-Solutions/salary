#include "Adafruit_PN532.h"

byte pn532ack[] = {0x00, 0x00, 0xFF, 0x00, 0xFF, 0x00};
byte pn532response_firmwarevers[] = {0x00, 0xFF, 0x06, 0xFA, 0xD5, 0x03};

Adafruit_PN532::Adafruit_PN532(uint8_t clk, uint8_t miso, uint8_t mosi, uint8_t ss) {
    _usingI2C = false;
}

Adafruit_PN532::Adafruit_PN532(uint8_t irq, uint8_t reset) {
    _irq = irq;
    _reset = reset;
    _usingI2C = true;
}

Adafruit_PN532::Adafruit_PN532(uint8_t reset) {
    _reset = reset;
    _usingI2C = true;
}

void Adafruit_PN532::begin(void) {
    Wire.begin();
}

bool Adafruit_PN532::isready(void) {
    Wire.requestFrom((uint8_t)PN532_I2C_ADDRESS, (uint8_t)1);
    return (Wire.read() == 0x01);
}

bool Adafruit_PN532::waitready(uint16_t timeout) {
    uint16_t timer = 0;
    while (!isready()) {
        if (timeout != 0) {
            timer += 5;
            if (timer > timeout) return false;
        }
        delay(5);
    }
    return true;
}

int8_t Adafruit_PN532::readack(void) {
    uint8_t ackbuf[6];
    readdata(ackbuf, 6);
    return (0 == memcmp(ackbuf, pn532ack, 6));
}

void Adafruit_PN532::writecommand(uint8_t *cmd, uint8_t cmdlen) {
    uint8_t checksum;

    cmdlen++;

    Wire.beginTransmission(PN532_I2C_ADDRESS);
    checksum = PN532_PREAMBLE + PN532_STARTCODE1 + PN532_STARTCODE2;
    Wire.write(PN532_PREAMBLE);
    Wire.write(PN532_STARTCODE1);
    Wire.write(PN532_STARTCODE2);

    Wire.write(cmdlen);
    Wire.write(~cmdlen + 1);

    Wire.write(PN532_HOSTTOPN532);
    checksum += PN532_HOSTTOPN532;

    for (uint8_t i = 0; i < cmdlen - 1; i++) {
        Wire.write(cmd[i]);
        checksum += cmd[i];
    }

    Wire.write(~checksum + 1);
    Wire.write(PN532_POSTAMBLE);
    Wire.endTransmission();
}

void Adafruit_PN532::readdata(uint8_t *buff, uint8_t n) {
    Wire.requestFrom((uint8_t)PN532_I2C_ADDRESS, (uint8_t)(n + 1));
    Wire.read(); // Skip status byte
    for (uint8_t i = 0; i < n; i++) {
        buff[i] = Wire.read();
    }
}

bool Adafruit_PN532::sendCommandCheckAck(uint8_t *cmd, uint8_t cmdlen, uint16_t timeout) {
    writecommand(cmd, cmdlen);
    if (!waitready(timeout)) {
        return false;
    }
    if (!readack()) {
        return false;
    }
    return true;
}

uint32_t Adafruit_PN532::getFirmwareVersion(void) {
    uint32_t response;
    uint8_t pn532packet[12];

    pn532packet[0] = PN532_COMMAND_GETFIRMWAREVERSION;

    if (!sendCommandCheckAck(pn532packet, 1, 100)) {
        return 0;
    }

    readdata(pn532packet, 12);

    int offset = 0;
    if (pn532packet[offset] == 0x00 && pn532packet[offset+1] == 0xFF) {
        offset = 4;
    }
    
    if (pn532packet[offset] != PN532_PN532TOHOST || pn532packet[offset+1] != 0x03) {
        return 0;
    }

    response = pn532packet[offset+2];
    response <<= 8;
    response |= pn532packet[offset+3];
    response <<= 8;
    response |= pn532packet[offset+4];
    response <<= 8;
    response |= pn532packet[offset+5];

    return response;
}

bool Adafruit_PN532::SAMConfig(void) {
    uint8_t pn532packet[4];
    pn532packet[0] = PN532_COMMAND_SAMCONFIGURATION;
    pn532packet[1] = 0x01; // Normal mode
    pn532packet[2] = 0x14; // Timeout 50ms
    pn532packet[3] = 0x01; // Use IRQ pin

    if (!sendCommandCheckAck(pn532packet, 4, 100)) return false;

    readdata(pn532packet, 8);
    return true;
}

bool Adafruit_PN532::readPassiveTargetID(uint8_t cardbaudrate, uint8_t *uid, uint8_t *uidLength, uint16_t timeout) {
    uint8_t pn532packet[20];
    pn532packet[0] = PN532_COMMAND_INLISTPASSIVETARGET;
    pn532packet[1] = 1; // 1 max target
    pn532packet[2] = cardbaudrate;

    if (!sendCommandCheckAck(pn532packet, 3, timeout)) {
        return false;
    }

    if (!waitready(timeout)) {
        return false;
    }

    readdata(pn532packet, 20);

    int offset = 0;
    while (offset < 10 && pn532packet[offset] != PN532_PN532TOHOST) offset++;

    if (pn532packet[offset] != PN532_PN532TOHOST || pn532packet[offset + 1] != 0x4B) {
        return false;
    }

    if (pn532packet[offset + 2] != 1) return false;

    *uidLength = pn532packet[offset + 7];
    if (*uidLength > 7) *uidLength = 7;

    for (uint8_t i = 0; i < *uidLength; i++) {
        uid[i] = pn532packet[offset + 8 + i];
    }
    return true;
}

bool Adafruit_PN532::mifareclassic_AuthenticateBlock(uint8_t *uid, uint8_t uidLen, uint32_t blockNumber, uint8_t keyNumber, uint8_t *keyData) {
    uint8_t pn532packet[16];
    pn532packet[0] = PN532_COMMAND_INDATAEXCHANGE;
    pn532packet[1] = 1;
    pn532packet[2] = (keyNumber == 0) ? 0x60 : 0x61;
    pn532packet[3] = blockNumber;

    for (uint8_t i = 0; i < 6; i++) pn532packet[4 + i] = keyData[i];
    for (uint8_t i = 0; i < uidLen; i++) pn532packet[10 + i] = uid[i];

    if (!sendCommandCheckAck(pn532packet, 10 + uidLen, 100)) return false;
    if (!waitready(100)) return false;

    readdata(pn532packet, 8);
    return true;
}

bool Adafruit_PN532::mifareclassic_ReadDataBlock(uint8_t blockNumber, uint8_t *data) {
    uint8_t pn532packet[22];
    pn532packet[0] = PN532_COMMAND_INDATAEXCHANGE;
    pn532packet[1] = 1;
    pn532packet[2] = 0x30; // Mifare Read command
    pn532packet[3] = blockNumber;

    if (!sendCommandCheckAck(pn532packet, 4, 100)) return false;
    if (!waitready(100)) return false;

    readdata(pn532packet, 22);

    int offset = 0;
    while (offset < 10 && pn532packet[offset] != PN532_PN532TOHOST) offset++;

    if (pn532packet[offset + 2] != 0x00) return false;

    for (uint8_t i = 0; i < 16; i++) {
        data[i] = pn532packet[offset + 3 + i];
    }
    return true;
}

bool Adafruit_PN532::mifareclassic_WriteDataBlock(uint8_t blockNumber, uint8_t *data) {
    uint8_t pn532packet[24];
    pn532packet[0] = PN532_COMMAND_INDATAEXCHANGE;
    pn532packet[1] = 1;
    pn532packet[2] = 0xA0; // Mifare Write command
    pn532packet[3] = blockNumber;

    for (uint8_t i = 0; i < 16; i++) pn532packet[4 + i] = data[i];

    if (!sendCommandCheckAck(pn532packet, 20, 100)) return false;
    if (!waitready(100)) return false;

    readdata(pn532packet, 8);
    return true;
}
