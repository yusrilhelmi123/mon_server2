# Dokumentasi: Perhitungan Reliability Score
**SensoLab — Research Edition V2.0**

---

## 📄 File yang Bertanggung Jawab

| File | Peran |
|---|---|
| `versi2_0/api_process_labels.php` | **Menghitung** dan **menyimpan** `reliability_score` ke database |
| `versi2_0/export_dataset.php` | **Mengekspor** `reliability_score` ke dalam file CSV |
| `versi2_0/api_dataset_stats.php` | Mengambil statistik dataset (total valid, invalid, pending) |

---

## 🧮 Formula Perhitungan

### 1. Hitung Standar Deviasi (SD) per Sensor
Menggunakan **Moving Window** (20 data terakhir di setiap titik):

```
SD_sensor = sqrt( Σ(xi - x̄)² / n )
```

Dihitung untuk tiga sensor:
- `SD_suhu` → Standar Deviasi Suhu (DHT11)
- `SD_hum` → Standar Deviasi Kelembaban (DHT11)
- `SD_gas` → Standar Deviasi Gas/MQ-135

---

### 2. Normalisasi Skor per Sensor

Setiap SD dinormalisasi terhadap batas toleransi maksimum (threshold × 3):

```
score_suhu = max(0, 1 - (SD_suhu / (threshold_suhu × 3)))
score_hum  = max(0, 1 - (SD_hum  / (threshold_hum  × 3)))
score_gas  = max(0, 1 - (SD_gas  / (threshold_gas  × 3)))
```

**Nilai threshold yang digunakan:**

| Sensor | Threshold Dasar | Batas Maks (×3) |
|---|---|---|
| Suhu (°C) | 0.5 | 1.5 |
| Kelembaban (%) | 1.0 | 3.0 |
| Gas/MQ-135 (PPM) | 20 | 60 |

---

### 3. Reliability Score Akhir (Weighted Average)

Gas diberi bobot lebih besar karena merupakan **sensor utama** penelitian:

```
reliability_score = (score_suhu × 0.25) + (score_hum × 0.25) + (score_gas × 0.50)
```

| Sensor | Bobot |
|---|---|
| Suhu (DHT11) | 25% |
| Kelembaban (DHT11) | 25% |
| Gas MQ-135 | **50%** |

---

## 📊 Interpretasi Nilai

| Rentang Skor | Interpretasi | Keputusan Sistem |
|---|---|---|
| `0.75 – 1.00` | ⭐ Data sangat stabil | **VALID** — Ideal untuk dataset AI |
| `0.50 – 0.74` | ✅ Data cukup baik | **VALID** — Dapat digunakan |
| `0.41 – 0.49` | ⚠️ Data kurang stabil | **VALID** — Perlu perhatian |
| `0.00 – 0.40` | ❌ Data sangat noisy | **INVALID** — Dibuang dari dataset |
| `0.50` (tetap) | 📊 Data Pending | Belum cukup data untuk dihitung |

> **Catatan:** Data yang dinyatakan **INVALID** akan dibatasi skor maksimalnya menjadi `≤ 0.40` secara paksa, meskipun skor mentahnya lebih tinggi.

---

## ⚙️ Alur Kerja Sistem

```
NodeMCU → input.php → tb_monitoring (raw data)
                              ↓
                  [Tekan "Proses Labeling"]
                              ↓
                  api_process_labels.php
                    ├─ Ambil 20 data terakhir (Moving Window)
                    ├─ Hitung SD (Suhu, Hum, Gas)
                    ├─ Normalisasi → score_suhu, score_hum, score_gas
                    ├─ Weighted Average → reliability_score
                    ├─ Tentukan is_valid (0 atau 1)
                    └─ Simpan ke tb_reliability_labels
                              ↓
                  [Ekspor CSV]
                              ↓
                  export_dataset.php
                    └─ Kolom: ID, Waktu, Suhu, Hum, Gas, LDR, Valid_Status, Reliability_Score
```

---

## 🗄️ Struktur Tabel `tb_reliability_labels`

```sql
CREATE TABLE tb_reliability_labels (
    id                INT AUTO_INCREMENT PRIMARY KEY,
    monitoring_id     INT NOT NULL,            -- FK ke tb_monitoring.id
    is_valid          TINYINT(1) DEFAULT 1,    -- 1=VALID, 0=INVALID
    reliability_score FLOAT,                   -- Skor 0.0000 – 1.0000
    analysis_date     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY (monitoring_id)
);
```

---

## 📝 Penggunaan dalam Publikasi Ilmiah

Contoh kalimat yang dapat digunakan dalam laporan/jurnal:

> *"Dataset yang digunakan dalam penelitian ini telah melalui proses automated labeling menggunakan metode Moving Window Standard Deviation. Setiap sampel data diberikan Reliability Score (rentang 0–1) berdasarkan stabilitas pembacaan tiga sensor (Gas MQ-135 dengan bobot 50%, Suhu DHT11 dengan bobot 25%, dan Kelembaban DHT11 dengan bobot 25%). Data dengan Reliability Score di bawah 0.40 diklasifikasikan sebagai noise dan dieksklusi dari dataset akhir."*
