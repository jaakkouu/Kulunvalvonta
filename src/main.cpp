#include <Arduino.h>
#include <WiFi.h>

const char* ssid = "ZyXEL7EAEB8";
const char* password = "4MWVTX94P7MP3";
int status = WL_IDLE_STATUS;
int state = 0;
IPAddress server (192,168,1,1);

WiFiClient client;

void setup() {
    Serial.begin(115200);
    Serial.println("\nConnecting");
    Serial.println("Connecting Wifi ");
    for (int loops = 10; loops > 0; loops--) {
		status = WiFi.begin(ssid, password);
		if (status == WL_CONNECTED) {
			Serial.println("");
			Serial.print("WiFi connected ");
			Serial.print("IP address: ");
			Serial.println(WiFi.localIP());
			break;
		} else {
			Serial.println(loops);
			delay(1000);
		}
    }
    if (status != WL_CONNECTED) {
		Serial.println("WiFi connect failed");
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

void readArpTable() {

}

void arpRequest(){
	delay(200);
	client.println("arp -n");
	readArpTable();
	delay(100);
}

int x;
String str;

void loop() {
  if (WiFi.status() == WL_CONNECTED) {
		if(!client.connected()){
			if(client.connect(server, 23)){
				Serial.println("Connected telnet");
			} else{
				Serial.println("telnet connection failed");
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
						arpRequest();
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
  }
  else {
    Serial.println("WiFi not connected!");
    delay(1000);
  }
}