#include "audio.h"
#include "config.h"

static bool gAudioMuted = false;

void audioInit() {
    pinMode(BUZZER_PIN, OUTPUT);
    digitalWrite(BUZZER_PIN, LOW);
}

void audioSetMute(bool muted) {
    gAudioMuted = muted;
}

void beep(int durationMs, int count, int freq) {
    if (gAudioMuted) return;
    for (int i = 0; i < count; i++) {
        digitalWrite(BUZZER_PIN, HIGH);
        tone(BUZZER_PIN, freq);
        delay(durationMs);
        digitalWrite(BUZZER_PIN, LOW);
        noTone(BUZZER_PIN);
        if (i < count - 1) delay(80);
    }
}

// High-pitch bright double chime: Punch Recorded Successfully (2800Hz -> 3400Hz)
void beepSuccess() {
    if (gAudioMuted) return;
    digitalWrite(BUZZER_PIN, HIGH); tone(BUZZER_PIN, 2800); delay(70); noTone(BUZZER_PIN); digitalWrite(BUZZER_PIN, LOW); delay(40);
    digitalWrite(BUZZER_PIN, HIGH); tone(BUZZER_PIN, 3400); delay(100); noTone(BUZZER_PIN); digitalWrite(BUZZER_PIN, LOW);
}

// Distinct dual medium warning chime: Punch Already Exists (1800Hz -> 1400Hz)
void beepAlreadyExists() {
    if (gAudioMuted) return;
    digitalWrite(BUZZER_PIN, HIGH); tone(BUZZER_PIN, 1800); delay(100); noTone(BUZZER_PIN); digitalWrite(BUZZER_PIN, LOW); delay(60);
    digitalWrite(BUZZER_PIN, HIGH); tone(BUZZER_PIN, 1400); delay(120); noTone(BUZZER_PIN); digitalWrite(BUZZER_PIN, LOW);
}

void beepError()   { if (!gAudioMuted) beep(400, 1, 1200); }
void beepScan()    { if (!gAudioMuted) beep(350, 1, 2400); }

void beepPowerOn() {
    if (gAudioMuted) return;
    digitalWrite(BUZZER_PIN, HIGH); tone(BUZZER_PIN, 2000); delay(60); noTone(BUZZER_PIN); digitalWrite(BUZZER_PIN, LOW); delay(40);
    digitalWrite(BUZZER_PIN, HIGH); tone(BUZZER_PIN, 2500); delay(60); noTone(BUZZER_PIN); digitalWrite(BUZZER_PIN, LOW); delay(40);
    digitalWrite(BUZZER_PIN, HIGH); tone(BUZZER_PIN, 3000); delay(120); noTone(BUZZER_PIN); digitalWrite(BUZZER_PIN, LOW);
}

void beepReady() { if (!gAudioMuted) beep(90, 2, 3200); }
