#ifndef RFID_H
#define RFID_H

#include <Arduino.h>

// Initializes the PN532 over the shared I2C bus and puts it in normal
// (non-SAM-secure) reader mode. Returns false if the chip doesn't respond
// (e.g. not wired, wrong I2C address, dead board).
bool rfidInit();

// Non-blocking poll for a card. Returns true if a card was found within
// the given timeout (ms) and fills uid/uidLength.
bool rfidPoll(uint8_t *uid, uint8_t *uidLength, uint16_t timeoutMs);

// Reads the "tagms" message stored across Mifare blocks 4+5 (Key A,
// all-FF, one sector-wide authentication covers both blocks). Returns
// true and fills out[] (up to RFID_MESSAGE_MAX_LEN chars + nul) only if
// authentication + read succeeded AND the blocks contained at least one
// printable character; otherwise returns false and out is untouched, so
// callers should keep their own fallback value.
bool rfidReadMessage(uint8_t *uid, uint8_t uidLength, char *out,
                      size_t outSize);

// Writes `value` (truncated to RFID_MESSAGE_MAX_LEN chars) across Mifare
// blocks 4+5.
bool rfidWriteMessage(uint8_t *uid, uint8_t uidLength, const char *value);

// Zeroes out Mifare blocks 4+5 (used by Format/Delete modes).
bool rfidClearMessage(uint8_t *uid, uint8_t uidLength);

// Diagnostic for cards written by an unknown/different device (e.g. an
// old machine whose firmware source is lost): tries a handful of common
// Mifare Classic default keys, as both Key A and Key B, against the first
// block of all 16 sectors, and logs to Serial which (if any) key/sector/
// key-type combo authenticates, plus a hex+ASCII dump of what's actually
// stored there. Takes several seconds - keep the card on the reader.
// Used from MODE_SETUP - check the Serial Monitor after tapping a card in
// Setup mode to see the results.
void rfidDiagnoseCard(uint8_t *uid, uint8_t uidLength);

#endif // RFID_H
