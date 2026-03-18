# Struktur Direktori & Distribusi File SensoLab V2.0

Dokumen ini menjelaskan organisasi file dalam proyek SensoLab, memisahkan antara modul produksi (operasional), modul riset, dan alat bantu pengembangan.

## 📁 Pohon Direktori (Directory Tree)

```text
mon_server/
├── dashboard.php             # Dashboard Monitoring Standar
├── delete_data.php           # Alat Pengosongan Database (Admin)
├── index.php                 # Portal Utama / Entry Point
├── input.php                 # Receiver Data & Log Viewer
├── monitoring_fix.ino        # Firmware Arduino (NodeMCU)
├── toggle_recording.php      # API Kontrol Rekaman Standar
│
├── versi2_0/                 # MODUL RISET (V2.0 Research Edition)
│   ├── index.php             # Dashboard Riset & Analisis Reliabilitas
│   ├── alur_kerja.php        # Visualisasi Workflow Sistem
│   ├── teori.php             # Dokumentasi Landasan Teori Statistik
│   ├── export_dataset.php    # Script Ekspor Dataset CSV (Valid Only)
│   ├── api_v2.php            # API Utama Riset (Statistics Buffer)
│   ├── api_control.php       # API Sinkronisasi Kontrol V2.0
│   ├── api_process_labels.php # Engine Pelabelan Otomatis AI-Ready
│   ├── api_dataset_stats.php  # API Informasi Status Dataset
│   └── dokumentasi_V2/       # Asset Dokumentasi Tambahan V2
│
├── Dokumentasi/              # DOKUMEN SISTEM
│   ├── alur_sistem.md        # Penjelasan Alur Teknis & Arsitektur
│   ├── panduan_umum.md       # Panduan Penggunaan Non-Teknis
│   ├── struktur_direktori.md # Dokumen Ini
│   └── pembagian_file_produksi.md # Laporan Distribusi Akhir
│
└── dev_tools_backup/         # UTILITY & DEBUG (Bisa Dihapus di Production)
    ├── diag.php              # Diagnosa Database
    ├── fix_db.php            # Perbaikan Schema Otomatis
    ├── setup_db_v2.php       # Inisialisasi Tabel Reliabilitas
    ├── sim_multisensor.html  # Referensi Simulasi Awal
    └── ... (skrip debug lainnya)
```

## 🚀 Komponen Inti (Production Layer)

### 1. Portal & Monitoring (`Root`)
Bertugas sebagai antarmuka harian untuk memantau kondisi sensor secara cepat dan melakukan administrasi dasar seperti menghapus log jika database penuh.

### 2. Research & AI Engine (`versi2_0/`)
Modul tercanggih yang berfungsi untuk memvalidasi data. Fitur utamanya adalah **Proses Labelling** yang secara cerdas memisahkan data bersih (Valid) dari data noise (Invalid) menggunakan perhitungan statistik Moving Window.

### 3. Data Receiver (`input.php`)
Gerbang utama yang menerima kiriman data dari NodeMCU melalui protokol HTTP. Script ini menentukan apakah data akan dibuang atau disimpan ke database berdasarkan status tombol **Recording**.

## 🛠️ Alat Pengembangan (`dev_tools_backup/`)
Folder ini berisi script yang digunakan saat proses pembangunan sistem. Setelah sistem berjalan (Production), folder ini disimpan sebagai cadangan jika sewaktu-watu diperlukan perbaikan struktur database secara otomatis.

---
*SensoLab V2.0 — Sustainability & Reliability Research Project*  
*2026 © Vanya Clianta Evelyn Pasha*
