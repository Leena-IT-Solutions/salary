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

void beepSuccess() { beep(70, 2, 2800); }
void beepError()   { beep(400, 1, 1500); }
// Long single tone acknowledging a detected card tap (all modes).
void beepScan()    { beep(350, 1, 2400); }

void beepPowerOn() {
    digitalWrite(BUZZER_PIN, HIGH); tone(BUZZER_PIN, 2000); delay(60); noTone(BUZZER_PIN); digitalWrite(BUZZER_PIN, LOW); delay(40);
    digitalWrite(BUZZER_PIN, HIGH); tone(BUZZER_PIN, 2500); delay(60); noTone(BUZZER_PIN); digitalWrite(BUZZER_PIN, LOW); delay(40);
    digitalWrite(BUZZER_PIN, HIGH); tone(BUZZER_PIN, 3000); delay(120); noTone(BUZZER_PIN); digitalWrite(BUZZER_PIN, LOW);
}

// Distinct "boot finished, ready to scan" cue - separate from the power-on
// jingle and from the Wi-Fi-connected/AP-mode beeps that may fire before it.
void beepReady() { beep(90, 2, 3200); }
