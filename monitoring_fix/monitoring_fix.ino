#include "DHT.h"
#include <ESP8266HTTPClient.h>
#include <ESP8266WiFi.h>

// ================= KONFIGURASI WIFI & SERVER =================
const char *ssid = "YusriLizer_NetCrowd2";
const char *password = "120351anya";

// --- MODE SWITCHER ---
bool isLocalMode = true; // GANTI KE 'false' JIKA INGIN KIRIM KE ONLINE (DOMAIN)

// Jika Lokal (XAMPP), ganti dengan IP Laptop Anda:
String localIP = "192.168.1.15";
String localPath = "/mon_server2/input.php"; // Nama folder di htdocs XAMPP

// Jika Online (InfinityFree):
String domainURL = "vanya.page.gd";
String domainPath = "/input.php"; // Di htdocs hosting biasanya ditaruh di root
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

  // === TES KONEKSI KE SERVER ===
  delay(500);
  WiFiClient client;
  HTTPClient http;

  String testURL = "";
  if (isLocalMode) {
    testURL = "http://" + localIP + "/mon_server2/hw_test.php";
  } else {
    testURL = "http://" + domainURL + "/hw_test.php";
  }

  Serial.println("[TEST] Mencoba koneksi ke: " + testURL);
  http.begin(client, testURL);
  http.setUserAgent("NodeMCU-ESP8266");
  int code = http.GET();

  if (code > 0) {
    Serial.println("[TEST] Server TERJANGKAU! Kode: " + String(code));
    Serial.println("[TEST] Respons server: " + http.getString());
  } else {
    Serial.println("[TEST] GAGAL menjangkau server!");
    Serial.println("[TEST] Error: " + http.errorToString(code));
    Serial.println("[TEST] >> Periksa IP atau Firewall Windows!");
  }
  http.end();
  // === AKHIR TES KONEKSI ===
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
  String ketLDR = (statusCahaya == LOW) ? "Terang" : "Gelap";

  // 3. Tampilkan di Serial Monitor
  Serial.printf("GAS: %d (%s) | LDR: %s | TEMP: %.1f°C | HUM: %.1f%%\n",
                nilaiGas, ketGas.c_str(), ketLDR.c_str(), temp, hum);

  // 4. Kirim ke Database (PHP) via HTTP
  if (WiFi.status() == WL_CONNECTED) {
    WiFiClient client;
    HTTPClient http;

    // Pilih URL target berdasarkan mode
    String targetURL = "";
    if (isLocalMode) {
      targetURL = "http://" + localIP + localPath;
    } else {
      targetURL = "http://" + domainURL + domainPath;
    }

    // Susun parameter data sensor (Query String)
    targetURL += "?cahaya=" + String(nilaiGas);
    targetURL += "&gas=" + ketGas;
    targetURL += "&suhu=" + String(temp);
    targetURL += "&hum=" + String(hum);
    targetURL += "&ldr=" + ketLDR;

    http.begin(client, targetURL);

    // HEADERS - Penting untuk bypass proteksi bot di Hosting Online
    http.setUserAgent(
        "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, "
        "like Gecko) Chrome/91.0.4472.124 Safari/537.36");
    http.addHeader("Connection", "close");

    int httpCode = http.GET(); // Kirim data via GET

    if (httpCode > 0) {
      Serial.println("[SERVER] Terkirim ke " +
                     (isLocalMode ? String("LOKAL") : String("ONLINE")));
      Serial.println("[SERVER] Status: " + String(httpCode));
    } else {
      Serial.println("[SERVER] Gagal Kirim. Error: " +
                     http.errorToString(httpCode));
    }
    http.end();
  }

  delay(5000); // Interval pengiriman 5 detik (Atur sesuai kebutuhan riset)
}