#include "DHT.h"
#include <ESP8266HTTPClient.h>
#include <ESP8266WiFi.h>

// ================= KONFIGURASI WIFI & SERVER =================
const char *ssid = "YusriLizer_NetCrowd2";
const char *password = "xxxxxxxxxx";
String serverIP = "192.168.1.11";
// =============================================================

// PIN SENSOR SESUAI TABEL WIRING
const int pinMQ135 = A0; // MQ-135 Analog (Data angka gas)
const int pinLDR = D1;   // LDR Digital (Terang/Gelap)
#define DHTPIN D2        // DHT11 Digital (Suhu/Lembab)
#define DHTTYPE DHT11

DHT dht(DHTPIN, DHTTYPE);
int ambangBatasGas = 400;

void setup() {
  Serial.begin(115200);

  // Inisialisasi Pin
  pinMode(pinLDR, INPUT);
  dht.begin();

  // Koneksi WiFi
  WiFi.begin(ssid, password);
  Serial.print("Menghubungkan ke WiFi");
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }
  Serial.println("\n[SISTEM] Terhubung ke WiFi!");
  Serial.print("[IP] NodeMCU: ");
  Serial.println(WiFi.localIP());
}

void loop() {
  // 1. Baca Data Sensor
  int nilaiGas = analogRead(pinMQ135);
  int statusCahaya = digitalRead(pinLDR);
  float hum = dht.readHumidity();
  float temp = dht.readTemperature();

  // Proteksi jika DHT11 gagal baca
  if (isnan(hum) || isnan(temp)) {
    Serial.println("[ERROR] Gagal membaca dari sensor DHT11!");
    hum = 0;
    temp = 0;
  }

  // 2. Tentukan Status Teks
  String ketGas = (nilaiGas > ambangBatasGas) ? "Bahaya" : "Aman";
  // Ingat: LDR LOW = Terang, HIGH = Gelap
  String ketLDR = (statusCahaya == LOW) ? "Terang" : "Gelap";

  // 3. Tampilkan di Serial Monitor
  Serial.printf("GAS: %d (%s) | LDR: %s | TEMP: %.1f°C | HUM: %.1f%%\n",
                nilaiGas, ketGas.c_str(), ketLDR.c_str(), temp, hum);

  // 4. Kirim ke Database (PHP)
  if (WiFi.status() == WL_CONNECTED) {
    WiFiClient client;
    HTTPClient http;

    // Menyusun URL pengiriman data
    // Kita gunakan nama parameter yang sesuai dengan struktur database/PHP kamu
    String url = "http://" + serverIP + "/mon_server/input.php";
    url +=
        "?cahaya=" + String(nilaiGas); // Kolom 'cahaya' di DB diisi angka gas
    url += "&gas=" + ketGas;        // Kolom 'status_gas' diisi teks Aman/Bahaya
    url += "&suhu=" + String(temp); // Kolom 'suhu'
    url += "&hum=" + String(hum);   // Kolom 'hum'
    url += "&ldr=" + ketLDR;        // Kolom 'ldr_status'

    http.begin(client, url);
    int httpCode = http.GET(); // Kirim data via GET

    if (httpCode > 0) {
      Serial.println("[SERVER] Data Berhasil Terkirim. Respons: " +
                     String(httpCode));
    } else {
      Serial.println("[SERVER] Gagal Kirim. Error: " +
                     http.errorToString(httpCode));
    }
    http.end();
  }

  delay(5000); // Interval pengiriman data 5 detik
}