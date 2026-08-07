#ifndef RFID_H
#define RFID_H

#include <Arduino.h>

bool rfidInit();
bool rfidPoll(uint8_t *uid, uint8_t *uidLength, uint16_t timeoutMs);

bool rfidReadMessage(uint8_t *uid, uint8_t uidLength, char *out, size_t outSize);
bool rfidWriteMessage(uint8_t *uid, uint8_t uidLength, const char *value);
bool rfidClearMessage(uint8_t *uid, uint8_t uidLength);

void rfidDiagnoseCard(uint8_t *uid, uint8_t uidLength);

#endif // RFID_H
