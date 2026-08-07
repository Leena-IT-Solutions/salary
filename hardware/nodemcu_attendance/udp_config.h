#ifndef UDP_CONFIG_H
#define UDP_CONFIG_H

#include <Arduino.h>
#include "config.h"

// Starts the UDP config listener on UDP_CONFIG_PORT. Works in both AP and STA mode.
void udpConfigStart(Config *cfg);

// Call once per main loop() iteration to process any pending packet.
void udpConfigLoop();

// Transmits a UDP broadcast packet to 255.255.255.255 on specified port (default 7778)
void udpSendBroadcast(const String &message, uint16_t port = UDP_CONFIG_PORT);

// Transmits a discovery beacon (Device Code, IP, Company Name) on local network
void udpSendBeacon();

#endif // UDP_CONFIG_H
