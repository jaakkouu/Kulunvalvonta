#include <Arduino.h>
#include <WiFi.h>

const char* ssid = "ZyXEL7EAEB8";
const char* password = "4MWVTX94P7MP3";
int status = WL_IDLE_STATUS;
int switchReed = 25;
int led1 = 33; // pin red
int led2 = 32; // pin green
int done = 0; // prevent multiple requests
int state = 0;
IPAddress server (192,168,1,1);

WiFiClient client;

void setup() {
	pinMode(led1, OUTPUT);
	pinMode(led2, OUTPUT);
	pinMode(switchReed, INPUT);
    Serial.begin(115200);
	for (int loops = 10; loops > 0; loops--) {
		status = WiFi.begin(ssid, password);
		if (status == WL_CONNECTED) {
			Serial.println("");
			Serial.print("WiFi connected ");
			break;
		} else {
			Serial.println(loops);
			delay(1000);
		}
    }
    if (status != WL_CONNECTED) {
		Serial.println("Connection to WiFi Failed");
		delay(1000);
		ESP.restart();
    }
}

void login(){
	client.println("root");
	delay(100);
	client.println("2803");
	delay(100);
}

void leases() {
	delay(100);
	client.println("./test.sh");
	delay(200);
}

void loop() {
	if(digitalRead(switchReed) == HIGH) {
		digitalWrite(led1, LOW);
		digitalWrite(led2, HIGH);
		done = 1;
	} else {
		digitalWrite(led1, HIGH);
		digitalWrite(led2, LOW);
	}
	delay(1);
	if(done == 1) {
		if (WiFi.status() == WL_CONNECTED) {
			if(!client.connected()) {
				if(client.connect(server, 23)) {
					Serial.println("Connected Telnet");
				} else{
					Serial.println("Telnet Connection Failed");
				}
			} else {
				if(client.available()){
					char c = client.read();
					Serial.print(c);
					switch (state){
						case 0:
							if(c == ':'){
								state = 1;
							}
							break;
						case 1:
							login();
							state = 2;
							break;
						case 2:
							if(c == '#'){
								state = 3;
							}
							break;
						case 3:
							leases();
							done = 0;
							state = 4;
							break;
						case 4:
							if(!client.available()){
								client.stop();
								state = 0;
								delay(10000);
							}
							break;
						default:
							break;
					}
				}
			}
		} else {
			Serial.println("WiFi Not Connected!");
			delay(1000);
		}
	}
}
