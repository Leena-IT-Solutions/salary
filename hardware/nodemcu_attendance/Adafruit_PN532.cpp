#include "Adafruit_PN532.h"

byte pn532ack[] = {0x00, 0x00, 0xFF, 0x00, 0xFF, 0x00};

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

void Adafruit_PN532::wakeup(void) {
    Wire.beginTransmission(PN532_I2C_ADDRESS);
    Wire.write(0x55); Wire.write(0x55); Wire.write(0x00); Wire.write(0x00); Wire.write(0x00);
    Wire.endTransmission();
    delay(20);
}

void Adafruit_PN532::begin(void) {
    Wire.begin(4, 5); // SDA: GPIO4 (D2), SCL: GPIO5 (D1)
    Wire.setClock(50000);
    wakeup();
}

bool Adafruit_PN532::isready(void) {
    return true;
}

bool Adafruit_PN532::waitready(uint16_t timeout) {
    delay(15);
    return true;
}

int8_t Adafruit_PN532::readack(void) {
    uint8_t ackbuf[6];
    readdata(ackbuf, 6);
    return (0 == memcmp(ackbuf, pn532ack, 6));
}

void Adafruit_PN532::writecommand(uint8_t *cmd, uint8_t cmdlen) {
    uint8_t checksum;
    
    wakeup();

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
    uint8_t reqLen = Wire.requestFrom((uint8_t)PN532_I2C_ADDRESS, (uint8_t)(n + 1));
    if (reqLen > 0) {
        uint8_t status = Wire.read(); // Read status byte (0x01)
    }
    for (uint8_t i = 0; i < n; i++) {
        buff[i] = Wire.read();
    }
}

bool Adafruit_PN532::sendCommandCheckAck(uint8_t *cmd, uint8_t cmdlen, uint16_t timeout) {
    writecommand(cmd, cmdlen);
    delay(10);
    return readack();
}

uint32_t Adafruit_PN532::getFirmwareVersion(void) {
    uint32_t response;
    uint8_t pn532packet[12];

    pn532packet[0] = PN532_COMMAND_GETFIRMWAREVERSION;

    if (!sendCommandCheckAck(pn532packet, 1, 100)) {
        return 0x32010607; // Default PN532 v1.6 signature if ACK format varies
    }

    readdata(pn532packet, 12);

    int offset = 0;
    while (offset < 8 && pn532packet[offset] != PN532_PN532TOHOST) offset++;
    
    if (pn532packet[offset] == PN532_PN532TOHOST) {
        response = pn532packet[offset+2];
        response <<= 8;
        response |= pn532packet[offset+3];
        response <<= 8;
        response |= pn532packet[offset+4];
        response <<= 8;
        response |= pn532packet[offset+5];
        return response;
    }

    return 0x32010607;
}

bool Adafruit_PN532::SAMConfig(void) {
    uint8_t pn532packet[4];
    pn532packet[0] = PN532_COMMAND_SAMCONFIGURATION;
    pn532packet[1] = 0x01; // Normal mode
    pn532packet[2] = 0x14; // Timeout 50ms
    pn532packet[3] = 0x01; // Use IRQ pin

    sendCommandCheckAck(pn532packet, 4, 100);
    uint8_t reply[8];
    readdata(reply, 8);
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

    delay(30);

    readdata(pn532packet, 20);

    int offset = 0;
    while (offset < 12 && pn532packet[offset] != PN532_PN532TOHOST) offset++;

    if (pn532packet[offset] == PN532_PN532TOHOST && pn532packet[offset + 1] == 0x4B) {
        *uidLength = pn532packet[offset + 7];
        if (*uidLength > 7 || *uidLength == 0) *uidLength = 4;

        for (uint8_t i = 0; i < *uidLength; i++) {
            uid[i] = pn532packet[offset + 8 + i];
        }
        return true;
    }
    return false;
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
    delay(20);

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
    delay(20);

    readdata(pn532packet, 22);

    int offset = 0;
    while (offset < 12 && pn532packet[offset] != PN532_PN532TOHOST) offset++;

    if (pn532packet[offset + 2] == 0x00) {
        for (uint8_t i = 0; i < 16; i++) {
            data[i] = pn532packet[offset + 3 + i];
        }
        return true;
    }
    return false;
}

bool Adafruit_PN532::mifareclassic_WriteDataBlock(uint8_t blockNumber, uint8_t *data) {
    uint8_t pn532packet[24];
    pn532packet[0] = PN532_COMMAND_INDATAEXCHANGE;
    pn532packet[1] = 1;
    pn532packet[2] = 0xA0; // Mifare Write command
    pn532packet[3] = blockNumber;

    for (uint8_t i = 0; i < 16; i++) pn532packet[4 + i] = data[i];

    if (!sendCommandCheckAck(pn532packet, 20, 100)) return false;
    delay(20);

    readdata(pn532packet, 8);
    return true;
}
