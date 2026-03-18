# Struktur Proyek SensoLab V2.0 (Production Ready)

Proyek telah dirapikan untuk memisahkan file inti aplikasi (Production) dari file pendukung pengembangan (Backup/Debug).

## 🚀 File Produksi Utama (Root)
File-file ini wajib ada untuk menjalankan portal utama dan dashboard standar:
- **`index.php`**: Portal utama navigasi sistem.
- **`dashboard.php`**: Dashboard monitoring realtime standar.
- **`input.php`**: Endpoint penerima data dari hardware NodeMCU.
- **`toggle_recording.php`**: API pengontrol status perekaman dashboard standar.
- **`delete_data.php`**: Alat administrasi untuk pengosongan database.
- **`monitoring_fix.ino`**: Source code firmware untuk diunggah ke NodeMCU.

## 🔬 Modul Riset (Folder `versi2_0/`)
Modul khusus penelitian dengan analisis reliabilitas tingkat lanjut:
- **`index.php`**: Dashboard Penelitian V2.0.
- **`api_v2.php`**: API penyedia data dengan kalkulasi statistik real-time.
- **`api_control.php`**: API sinkronisasi status perekaman V2.0.
- **`api_dataset_stats.php`**: API penyedia statistik dataset AI-Ready.
- **`api_process_labels.php`**: Mesin pelabelan otomatis (Stabilitas & Noise).
- **`export_dataset.php`**: Script ekspor dataset CSV (hanya data VALID).
- **`teori.php`**: Dokumentasi landasan teori statistik sensor.
- **`alur_kerja.php`**: Visualisasi arsitektur dan workflow sistem.

## 📂 Folder Backup & Utility (`dev_tools_backup/`)
File-file berikut telah dipindahkan karena hanya diperlukan saat inisialisasi awal atau debugging:
- `diag.php`, `fix_db.php`: Diagnosa database.
- `init_heartbeat.php`, `init_settings.php`: Inisialisasi konfigurasi.
- `update_schema_dht.php`: Skrip migrasi tabel.
- `setup_db_v2.php`: Pembuat tabel reliabilitas khusus V2.
- `sim_multisensor.html`: File simulasi referensi awal.
- `tmp_db_check.php`, `api_stats.php`: Script pengujian internal.

> [!TIP]
> Semua fungsi tetap dapat beroperasi normal. Struktur ini memudahkan Anda jika ingin memindahkan proyek ini ke server hosting atau melakukan presentasi kepada reviewer.
