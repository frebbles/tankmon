/* 
 *  Dual Tank monitor and HTTP Reporter
 *  
 *  Author: F.Rebbeck
 *  github.com/frebbles/tankmon
 */

#include <Arduino.h>
#include <ESP8266WiFi.h>
#include <ESP8266WiFiMulti.h>
#include <ESP8266HTTPClient.h>
#include <WiFiClient.h>
#include "DHT.h"

// CONFIGURATION
#define SLEEP_PERIOD 3600e6
#define AP_NAME "AP_NAME_HERE"
#define AP_PSK "AP_PASSOWRD_HERE"
#define SERVER_IP "192.168.1.1"
#define DHTPIN 2        //D4     // Digital pin connected to the DHT sensor
#define DHTTYPE DHT11   // DHT 11

// Initialize DHT sensor.
DHT dht(DHTPIN, DHTTYPE);

// Use the quick WiFiMulti library for connection
ESP8266WiFiMulti WiFiMulti;

// Define pins numbers for ultrasonic sensors attached
const int trig1Pin = 5; //D1
const int echo1Pin = 4; //D2
const int trig2Pin = 12;//D6
const int echo2Pin = 14;//D5

// Allowable attempts to upload data before just going back to sleep (save battery life)
int connAttempts = 10;

// Make multiple samples and average result before returning.
float sampleAvg(int trigPin, int echoPin, int count) {
  float average = 0;
  float distance = 0;
  long duration = 0;
  int validreads = 0;

  // Waste a few samples to let US sensors settle
  for (int w = 0; w < 5; w++) {
    // Clears the trigPin
    digitalWrite(trigPin, LOW);
    delayMicroseconds(2);

    // Sets the trigPin on HIGH state for 10 micro seconds
    digitalWrite(trigPin, HIGH);
    delayMicroseconds(10);
    digitalWrite(trigPin, LOW);
    duration = pulseIn(echoPin, HIGH, 1000000);
  }
  
  Serial.print("Begin sampling...");
  for (int i = 0; i < count; i++) {
    // Clears the trigPin
    digitalWrite(trigPin, LOW);
    delayMicroseconds(2);

    // Sets the trigPin on HIGH state for 10 micro seconds
    digitalWrite(trigPin, HIGH);
    delayMicroseconds(10);
    digitalWrite(trigPin, LOW);
  
    duration = pulseIn(echoPin, HIGH, 1000000);
    if (duration > 0) validreads++;
    distance = duration*(0.034/2); //(duration / 29.0) / 2.0;
    average += distance;
    Serial.print(" reading: ");
    Serial.print(distance);
  }
  Serial.println("done.");
  if (validreads > 0) {
  return (average / validreads);
  } else {
  return 0;
  }
}


void setup() {
  pinMode(trig1Pin, OUTPUT); // Sets the trigPin1 as an Output
  pinMode(echo1Pin, INPUT);  // Sets the echoPin1 as an Input
  pinMode(trig2Pin, OUTPUT); // Sets the trigPin2 as an Output
  pinMode(echo2Pin, INPUT);  // Sets the echoPi2n as an Input
  delayMicroseconds(200);    // Settle time.
  Serial.begin(115200);      // Starts the serial communication
  Serial.println("\nStartup...");  
  delayMicroseconds(20000);
}

void loop() {
  float distance1;
  float distance2;

  // Sample data for snapshot
  distance1 = sampleAvg(trig1Pin, echo1Pin, 3);// * 0.034/2;
  distance2 = sampleAvg(trig2Pin, echo2Pin, 3);// * 0.034/2;
  
  // Prints the distance on the Serial Monitor
  Serial.print("Distances: ");
  Serial.printf("1==%f cm    2==%f cm\n", distance1, distance2);

  // Reading temperature or humidity takes about 250 milliseconds!
  dht.begin();
  // Sensor readings may also be up to 2 seconds 'old' (its a very slow sensor)
  float h = dht.readHumidity();
  // Read temperature as Celsius (the default)
  float t = dht.readTemperature();
  Serial.print(F("Humidity: "));
  Serial.print(h);
  Serial.print(F("%  Temperature: "));
  Serial.print(t);
  Serial.print(F("°C "));

  // Read system/battery voltage
  int aVal = analogRead(A0);
  Serial.print("Analog read: ");
  Serial.println(aVal);
  float vVal = 0;
  
  vVal = ((aVal*17.9)/255.0);

  WiFi.mode(WIFI_STA);       // Set as Station
  WiFiMulti.addAP(AP_NAME,AP_PSK); // Wifi credentials here.
  delayMicroseconds(1000000); // Wifi settle
  
  // Connect to AP 
  Serial.println("Connect to WiFi...");
  if ((WiFiMulti.run() == WL_CONNECTED)) {
    WiFiClient client;
    HTTPClient http;
     Serial.println("HTTP Start");
     char rstr[128];
     char d1str[16];
     char d2str[16];
     char hstr[16];
     char tstr[16];
     char vstr[16];
     strcpy(rstr, "http://");
     strcat(rstr, SERVER_IP);
     strcat(rstr, "/tankmon/clientReport.php?d1=");
     sprintf(d1str, "%f", distance1);
     sprintf(d2str, "%f", distance2);
     strcat(rstr, d1str);
     strcat(rstr, "&d2=");
     strcat(rstr, d2str);
     sprintf(hstr, "%f", h);
     sprintf(tstr, "%f", t);
     strcat(rstr, "&h=");
     strcat(rstr, hstr);
     strcat(rstr, "&t=");
     strcat(rstr, tstr);
     strcat(rstr, "&v=");
     sprintf(vstr, "%f", vVal);
     strcat(rstr, vstr);
     
     Serial.printf("HTTP REQ: %s\n",rstr);
     
     if (http.begin(client, rstr)) {
       Serial.print("HTTP Get \n");
       int httpCode = http.GET();
       if (httpCode > 0) {
         Serial.printf("HTTP Code %d \n", httpCode);
       } else {
         Serial.printf("HTTP failed error %s\n", http.errorToString(httpCode).c_str());
       }
       http.end();
       delay(1000);
       Serial.printf("Going to sleep....\n");
       ESP.deepSleep(SLEEP_PERIOD);
     } else {
      Serial.printf("HTTP Unable to connect\n");
     }
  }
  delay(5000);

  connAttempts--;
  if (connAttempts < 1) {
    // Sleep for an hour
    Serial.printf("Connection attempts failed, sleeping\n");
    ESP.deepSleep(SLEEP_PERIOD);
  }
}
