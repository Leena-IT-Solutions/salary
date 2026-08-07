#include "audio.h"
#include "config.h"

static bool gAudioMuted = false;

void audioInit() {
    pinMode(BUZZER_PIN, OUTPUT);
    digitalWrite(BUZZER_PIN, LOW);
}

void audioSetMute(bool mute) {
    gAudioMuted = mute;
}

void beep(uint16_t durationMs, uint8_t count, uint16_t freqHz) {
    if (gAudioMuted) return;

    for (uint8_t i = 0; i < count; i++) {
        tone(BUZZER_PIN, freqHz, durationMs);
        delay(durationMs);
        noTone(BUZZER_PIN);
        digitalWrite(BUZZER_PIN, LOW);
        if (i < count - 1) {
            delay(50);
        }
    }
}

void beepPowerOn() {
    if (gAudioMuted) return;
    tone(BUZZER_PIN, 1500, 100); delay(100);
    tone(BUZZER_PIN, 2000, 100); delay(100);
    tone(BUZZER_PIN, 2500, 150); delay(150);
    noTone(BUZZER_PIN);
    digitalWrite(BUZZER_PIN, LOW);
}

void beepReady() {
    beep(80, 2, 2400);
}

void beepScan() {
    beep(60, 1, 2200);
}

void beepSuccess() {
    if (gAudioMuted) return;
    tone(BUZZER_PIN, 2000, 80); delay(90);
    tone(BUZZER_PIN, 2700, 150); delay(150);
    noTone(BUZZER_PIN);
    digitalWrite(BUZZER_PIN, LOW);
}

void beepAlreadyExists() {
    if (gAudioMuted) return;
    tone(BUZZER_PIN, 1800, 120); delay(140);
    tone(BUZZER_PIN, 1800, 120); delay(140);
    noTone(BUZZER_PIN);
    digitalWrite(BUZZER_PIN, LOW);
}

void beepError() {
    if (gAudioMuted) return;
    tone(BUZZER_PIN, 800, 300); delay(350);
    noTone(BUZZER_PIN);
    digitalWrite(BUZZER_PIN, LOW);
}
