#ifndef AUDIO_H
#define AUDIO_H

#include <Arduino.h>

// Sets up the buzzer pin. Call once from setup() before any beep*() call.
void audioInit();

void beep(int durationMs, int count = 1, int freq = 2700);
void beepSuccess();
void beepError();
void beepScan();
void beepPowerOn();
void beepReady();

#endif // AUDIO_H
