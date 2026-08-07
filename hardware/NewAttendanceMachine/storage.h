#ifndef STORAGE_H
#define STORAGE_H

#include <Arduino.h>
#include "config.h"

void storageLoadConfig(Config &cfg);
void storageSaveConfig(Config &cfg);
void storageResetConfig(Config &cfg);

void storageSaveOfflinePunch(const String &tagms, const String &tagid,
                              const String &dateStr, const String &timeStr);
void storageClearOfflineQueue();
int storageGetOfflineQueueCount();
String storageGetOfflineQueueContents();

#endif // STORAGE_H
