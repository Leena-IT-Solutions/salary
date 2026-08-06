#ifndef ADAFRUIT_PN532_H
#define ADAFRUIT_PN532_H

#include <Arduino.h>
#include <Wire.h>

#define PN532_PREAMBLE                      (0x00)
#define PN532_STARTCODE1                    (0x00)
#define PN532_STARTCODE2                    (0xFF)
#define PN532_POSTAMBLE                     (0x00)

#define PN532_HOSTTOPN532                   (0xD4)
#define PN532_PN532TOHOST                   (0xD5)

#define PN532_COMMAND_DIAGNOSE              (0x00)
#define PN532_COMMAND_GETFIRMWAREVERSION    (0x02)
#define PN532_COMMAND_GETGENERALSTATUS      (0x04)
#define PN532_COMMAND_READREGISTER          (0x06)
#define PN532_COMMAND_WRITEREGISTER         (0x08)
#define PN532_COMMAND_READGPIO              (0x0C)
#define PN532_COMMAND_WRITEGPIO             (0x0E)
#define PN532_COMMAND_SETSERIALBAUDRATE     (0x10)
#define PN532_COMMAND_SETPARAMETERS         (0x12)
#define PN532_COMMAND_SAMCONFIGURATION      (0x14)
#define PN532_COMMAND_POWERDOWN             (0x16)
#define PN532_COMMAND_RFCONFIGURATION       (0x32)
#define PN532_COMMAND_INLISTPASSIVETARGET   (0x4A)

#define PN532_COMMAND_INDATAEXCHANGE        (0x40)
#define PN532_COMMAND_COMMUNICATETHRU       (0x42)
#define PN532_COMMAND_INDESELECT            (0x44)
#define PN532_COMMAND_INRELEASE             (0x52)
#define PN532_COMMAND_INSELECT              (0x54)

#define PN532_MIFARE_ISO14443A              (0x00)

#define PN532_I2C_ADDRESS                   (0x24)

class Adafruit_PN532 {
public:
    Adafruit_PN532(uint8_t clk, uint8_t miso, uint8_t mosi, uint8_t ss);
    Adafruit_PN532(uint8_t irq, uint8_t reset);
    Adafruit_PN532(uint8_t reset);

    void begin(void);
    void wakeup(void);
    bool SAMConfig(void);
    uint32_t getFirmwareVersion(void);
    
    bool sendCommandCheckAck(uint8_t *cmd, uint8_t cmdlen, uint16_t timeout = 100);
    
    bool readPassiveTargetID(uint8_t cardbaudrate, uint8_t *uid, uint8_t *uidLength, uint16_t timeout = 50);
    bool mifareclassic_AuthenticateBlock(uint8_t *uid, uint8_t uidLen, uint32_t blockNumber, uint8_t keyNumber, uint8_t *keyData);
    bool mifareclassic_ReadDataBlock(uint8_t blockNumber, uint8_t *data);
    bool mifareclassic_WriteDataBlock(uint8_t blockNumber, uint8_t *data);

private:
    uint8_t _clk, _miso, _mosi, _ss;
    uint8_t _irq, _reset;
    uint8_t _uid[7];
    uint8_t _uidLen;
    uint8_t _key[6];
    bool _usingI2C;

    int8_t readack(void);
    bool isready(void);
    bool waitready(uint16_t timeout);
    bool readspidata(uint8_t *buff, uint8_t n);
    void readdata(uint8_t *buff, uint8_t n);
    void writecommand(uint8_t *cmd, uint8_t cmdlen);
};

#endif
