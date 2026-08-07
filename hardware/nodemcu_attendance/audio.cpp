#include "audio.h"
#include "config.h"

void audioInit() {
    pinMode(BUZZER_PIN, OUTPUT);
    digitalWrite(BUZZER_PIN, LOW);
}

void beep(int durationMs, int count, int freq) {
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
    digitalWrite(BUZZER_PIN, HIGH); tone(BUZZER_PIN, 2800); delay(70); noTone(BUZZER_PIN); digitalWrite(BUZZER_PIN, LOW); delay(40);
    digitalWrite(BUZZER_PIN, HIGH); tone(BUZZER_PIN, 3400); delay(100); noTone(BUZZER_PIN); digitalWrite(BUZZER_PIN, LOW);
}

// Distinct dual medium warning chime: Punch Already Exists (1800Hz -> 1500Hz)
void beepAlreadyExists() {
    digitalWrite(BUZZER_PIN, HIGH); tone(BUZZER_PIN, 1800); delay(100); noTone(BUZZER_PIN); digitalWrite(BUZZER_PIN, LOW); delay(60);
    digitalWrite(BUZZER_PIN, HIGH); tone(BUZZER_PIN, 1400); delay(120); noTone(BUZZER_PIN); digitalWrite(BUZZER_PIN, LOW);
}

void beepError()   { beep(400, 1, 1200); }
void beepScan()    { beep(350, 1, 2400); }

void beepPowerOn() {
    digitalWrite(BUZZER_PIN, HIGH); tone(BUZZER_PIN, 2000); delay(60); noTone(BUZZER_PIN); digitalWrite(BUZZER_PIN, LOW); delay(40);
    digitalWrite(BUZZER_PIN, HIGH); tone(BUZZER_PIN, 2500); delay(60); noTone(BUZZER_PIN); digitalWrite(BUZZER_PIN, LOW); delay(40);
    digitalWrite(BUZZER_PIN, HIGH); tone(BUZZER_PIN, 3000); delay(120); noTone(BUZZER_PIN); digitalWrite(BUZZER_PIN, LOW);
}

void beepReady() { beep(90, 2, 3200); }
