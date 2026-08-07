#ifndef AUDIO_H
#define AUDIO_H

#include <Arduino.h>

void audioInit();
void audioSetMute(bool mute);

void beep(uint16_t durationMs = 100, uint8_t count = 1, uint16_t freqHz = 2000);
void beepPowerOn();
void beepReady();
void beepScan();
void beepSuccess();
void beepAlreadyExists();
void beepError();

#endif // AUDIO_H
