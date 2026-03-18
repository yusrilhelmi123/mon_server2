#  SensoLab: IoT Instrumentation & Sensor Validation System
**Riset Instrumentasi Tahap 1 — Sensor Validation & Reliability Analysis**

[![Research Status](https://img.shields.io/badge/Research-Phase_1-blue.svg)](https://vanya-pasha.github.io)
[![Version](https://img.shields.io/badge/Version-2.0_Research_Edition-purple.svg)](https://vanya-pasha.github.io)
[![License](https://img.shields.io/badge/License-Academic_Use-green.svg)](https://vanya-pasha.github.io)

---

##  Overview
**SensoLab** adalah platform penelitian berbasis *Internet of Things* (IoT) yang dirancang untuk memvalidasi reliabilitas data sensor secara *real-time*. Proyek ini fokus pada pemantauan kondisi lingkungan (Gas, Suhu, Kelembaban, dan Cahaya) menggunakan NodeMCU ESP8266 dengan penekanan khusus pada analisis kualitas sinyal dan pelabelan otomatis dataset untuk kebutuhan Kecerdasan Buatan (AI) dan publikasi ilmiah.

Sistem ini terdiri dari dua lapisan utama: **Standard Monitoring** untuk pemantauan harian, dan **Research Edition (V2.0)** yang dilengkapi algoritma deteksi noise (RMS) dan stabilitas sinyal.

---

##  Research Objectives
1. **Sensor Validation**: Menilai akurasi dan konsistensi data dari sensor MQ135, DHT11, dan LDR.
2. **Reliability Labeling**: Mengembangkan sistem pelabelan otomatis (VALID/INVALID) berdasarkan ambang batas noise dan stabilitas *moving window*.
3. **Dataset Sanitization**: Memproduksi dataset "bersih" yang siap digunakan untuk algoritma *Machine Learning*.
4. **Remote Instrumentation**: Membangun sistem monitoring berbasis web yang responsif dengan latensi rendah.

---

##  Hardware Architecture
Sistem menggunakan **NodeMCU (ESP8266)** sebagai unit pemrosesan pusat yang terhubung dengan modul-modul sensor berikut:

| Komponen | Pin Sensor | Pin NodeMCU | Deskripsi Fungsi |
| :--- | :--- | :--- | :--- |
| **MQ-135** | A0 (Analog) | **A0** | Deteksi Konsentrasi Gas (PPM) |
| **DHT11** | Data (OUT) | **D2** | Monitoring Suhu & Kelembaban |
| **LDR** | D0 (Digital) | **D1** | Deteksi Kondisi Cahaya (Terang/Gelap) |
| **NodeMCU** | VIN / GND | **VIN / G** | Protokol WiFi & Pemrosesan Data |

---

##  Software Stack
- **Frontend**: Vanilla CSS (Glassmorphism UI), Javascript (ES6), Chart.js (Visualisasi Statistik), FontAwesome.
- **Backend**: Native PHP 7.4+ (LTS).
- **Database**: MySQL / MariaDB (Optimized for Time-series Log).
- **Communication Protocol**: HTTP/1.1 RESTful (GET Method for Hardware-to-Server).

---

## 📂 Project Structure
```text
mon_server/
├── index.php                 # Central Portal & Navigation
├── dashboard.php             # Standard Monitoring Dashboard
├── input.php                 # data Receiver Endpoint & Log Viewer
├── delete_data.php           # Database Administration (Protected)
├── monitoring_fix.ino        # ESP8266 C++ Firmware Source
│
├── versi2_0/                 # RESEARCH MODULE (Advanced Analysis)
│   ├── index.php             # Research Dashboard & Reliability Engine
│   ├── api_v2.php            # Statistical Buffer API
│   ├── api_process_labels.php # Automatic Labeling Engine (AI-Ready)
│   └── export_dataset.php    # Clean Dataset CSV Exporter
│
└── Dokumentasi/              # System Technical Documentation
```

---

##  Installation & Deployment

### 1. Local Environment (XAMPP)
1. Clone atau copy folder ini ke direktori `C:/xampp/htdocs/`.
2. Buka PHPMyAdmin dan buat database baru bernama `db_sensor`.
3. Import file database (SQL) yang disediakan (Cek di `dev_tools_backup/`).
4. Akses sistem melalui browser di `http://localhost/mon_server2/`.

### 2. Cloud Deployment (InfinityFree/Shared Hosting)
1. Unggah seluruh isi file ke folder `htdocs` pada server hosting Anda.
2. Update kredensial database pada file-file PHP (`servername`, `username`, `password`, `dbname`).
3. Pastikan domain hosting Anda sudah di-whitelist pada konfigurasi firmware NodeMCU.

---

##  Firmware Configuration
Untuk menghubungkan perangkat hardware ke server, buka file `monitoring_fix.ino` dan sesuaikan parameter berikut:
```cpp
const char *ssid = "NAMA_WIFI_ANDA";
const char *password = "PASSWORD_WIFI_ANDA";
String serverIP = "ALAMAT_IP_ATAU_DOMAIN_SERVER";
```

---

##  Key Features
- **Real-time Synchronization**: Pembaruan data setiap 5 detik dengan animasi indikator status koneksi.
- **Recording Control**: Fitur *Start/Stop Recording* manual untuk mengontrol penyimpanan database.
- **Integrated Graphics**: Visualisasi tren sensor secara terpadu menggunakan Chart.js.
- **Auto-Labeling (V2.0)**: Algoritma cerdas yang memisahkan data anomali/noise secara otomatis.
- **Academic Export**: Ekspor riwayat data langsung ke format `.csv` yang telah difilter berdasarkan kriteria validasi.

---

## 👨 Researcher
**Vanya Clianta Evelyn Pasha**  
*Instrumentation & Control Researcher*  
*Specializing in IoT & Sensor Reliability*

---
*&copy; 2026 SensoLab Project. All rights reserved for academic and research purposes.*
