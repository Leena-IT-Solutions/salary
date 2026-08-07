#ifndef UDP_CONFIG_H
#define UDP_CONFIG_H

#include <Arduino.h>
#include "config.h"

// Starts the UDP config listener on UDP_CONFIG_PORT. Works in both AP and
// STA mode - call after Wi-Fi/AP is up. See README's "Remote Config via
// UDP" section for the payload format and its same-LAN limitation.
void udpConfigStart(Config *cfg);

// Call once per main loop() iteration to process any pending packet.
void udpConfigLoop();

#endif // UDP_CONFIG_H
