#ifndef STORAGE_H
#define STORAGE_H

#include <Arduino.h>
#include "config.h"

// Loads Config from EEPROM into cfg, initializing it to defaults (and
// persisting those defaults) if the EEPROM doesn't contain a valid,
// current-version config yet.
void storageLoadConfig(Config &cfg);

// Persists cfg to EEPROM.
void storageSaveConfig(Config &cfg);

// Wipes the stored config (back to defaults) and reloads cfg from it.
void storageResetConfig(Config &cfg);

// Appends one punch to the LittleFS offline queue file.
void storageSaveOfflinePunch(const String &tagms, const String &tagid,
                              const String &dateStr, const String &timeStr);

// If Wi-Fi is connected, attempts to flush queued offline punches to
// hostUri (same GET + optional Bearer token request the live path uses).
// Entries that fail to POST are kept in the queue for the next attempt.
void storageSyncOfflinePunches(const String &hostUri, const String &apiToken);

// Deletes the offline queue file entirely (used by MODE_CLEAR).
void storageClearOfflineQueue();

// Number of punches currently queued (non-empty lines in the queue file).
int storageGetOfflineQueueCount();

// Raw contents of the offline queue file, one "tagms,tagid,date,time" line
// per queued punch. Empty string if nothing is queued.
String storageGetOfflineQueueContents();

#endif // STORAGE_H
