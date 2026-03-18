#include "DHT.h"
#include <ESP8266HTTPClient.h>
#include <ESP8266WiFi.h>

// ================= KONFIGURASI WIFI & SERVER =================
const char *ssid = "YusriLizer_NetCrowd2";
const char *password = "120351anya";

// --- MODE SWITCHER ---
// true  = Kirim ke Localhost XAMPP (untuk pengembangan)
// false = Kirim ke Hosting Online
bool isLocalMode = false; // <-- ONLINE MODE AKTIF

// Jika Lokal (XAMPP) - sesuaikan IP laptop Anda:
String localIP = "192.168.1.15";
String localPath = "/mon_server2/input.php";

// Jika Online (pointmarket.id):
String domainURL = "sensolab.pointmarket.id";
String domainPath = "/input.php";
// =============================================================

// PIN SENSOR SESUAI TABEL WIRING
const int pinMQ135 = A0; // MQ-135 Analog (Data angka gas)
const int pinLDR = D1;   // LDR Digital (Terang/Gelap)
#define DHTPIN D2        // DHT11 Digital (Suhu/Lembab)
#define DHTTYPE DHT11

DHT dht(DHTPIN, DHTTYPE);
int ambangBatasGas = 400;

// ============================================================
// SETUP - Hanya dijalankan SEKALI saat NodeMCU pertama nyala
// ============================================================
void setup() {
  Serial.begin(115200);
  delay(200);
  Serial.println("\n============ SENSOLAB BOOTING ============");

  // Inisialisasi Pin
  pinMode(pinLDR, INPUT);
  dht.begin();

  // Koneksi WiFi
  WiFi.begin(ssid, password);
  Serial.print("[WiFi] Menghubungkan ke: ");
  Serial.println(ssid);
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }
  Serial.println();
  Serial.println("[WiFi] TERHUBUNG!");
  Serial.print("[WiFi] IP NodeMCU: ");
  Serial.println(WiFi.localIP());

  // === TES KONEKSI KE SERVER ===
  delay(500);
  WiFiClient clientTest;
  HTTPClient httpTest;

  String testURL = isLocalMode
                       ? "http://" + localIP + "/mon_server2/hw_test.php"
                       : "http://" + domainURL + "/hw_test.php";

  Serial.println("[TEST] Menguji koneksi ke: " + testURL);
  httpTest.begin(clientTest, testURL);
  httpTest.setUserAgent("NodeMCU-ESP8266-Setup");
  int testCode = httpTest.GET();

  if (testCode > 0) {
    Serial.println("[TEST] >>> SERVER TERJANGKAU! Kode HTTP: " +
                   String(testCode));
    Serial.println("[TEST] Respons: " + httpTest.getString());
  } else {
    Serial.println("[TEST] >>> GAGAL! Error: " +
                   httpTest.errorToString(testCode));
    Serial.println("[TEST] Cek: IP laptop benar? Firewall Windows aktif?");
  }
  httpTest.end();
  Serial.println("==========================================");
  // === AKHIR TES KONEKSI ===
}

// ============================================================
// LOOP - Dijalankan terus tiap 5 detik
// ============================================================
void loop() {
  // 1. Baca Data Sensor
  int nilaiGas = analogRead(pinMQ135);
  int statusCahaya = digitalRead(pinLDR);
  float hum = dht.readHumidity();
  float temp = dht.readTemperature();

  // Proteksi jika DHT11 gagal baca
  if (isnan(hum) || isnan(temp)) {
    Serial.println("[ERROR] Gagal baca sensor DHT11!");
    hum = 0;
    temp = 0;
  }

  // 2. Tentukan Status Teks
  String ketGas = (nilaiGas > ambangBatasGas) ? "Bahaya" : "Aman";
  String ketLDR = (statusCahaya == LOW) ? "Terang" : "Gelap";

  // 3. Tampilkan di Serial Monitor
  Serial.printf("GAS: %d (%s) | LDR: %s | TEMP: %.1f C | HUM: %.1f%%\n",
                nilaiGas, ketGas.c_str(), ketLDR.c_str(), temp, hum);

  // 4. Kirim ke Server via HTTP GET
  if (WiFi.status() == WL_CONNECTED) {
    WiFiClient client;
    HTTPClient http;

    // Pilih URL target
    String targetURL = isLocalMode ? "http://" + localIP + localPath
                                   : "http://" + domainURL + domainPath;

    // Susun parameter sensor
    targetURL += "?cahaya=" + String(nilaiGas);
    targetURL += "&gas=" + ketGas;
    targetURL += "&suhu=" + String(temp);
    targetURL += "&hum=" + String(hum);
    targetURL += "&ldr=" + ketLDR;

    http.begin(client, targetURL);
    http.setUserAgent(
        "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36");
    http.addHeader("Connection", "close");

    int httpCode = http.GET();

    if (httpCode > 0) {
      Serial.println("[SERVER] OK >> " +
                     (isLocalMode ? String("LOKAL") : String("ONLINE")) +
                     " | Status: " + String(httpCode));
    } else {
      Serial.println("[SERVER] GAGAL >> Error: " +
                     http.errorToString(httpCode));
    }
    http.end();
  } else {
    Serial.println("[WiFi] Koneksi terputus!");
  }

  delay(5000); // Interval 5 detik
}
