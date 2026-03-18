<?php
/**
 * SENNOLAB HOSTING AUTO-SETUP
 * Upload file ini ke htdocs dan jalankan sekali via browser: vanya.page.gd/setup_hosting.php
 */

require_once 'db_connect.php';

echo "<h2>🔧 SensoLab - Database Auto-Setup</h2>";
echo "<hr>";

// 1. BUAT TABEL tb_monitoring (Data Sensor)
$sql1 = "CREATE TABLE IF NOT EXISTS tb_monitoring (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cahaya INT,
    status_gas VARCHAR(50),
    ldr VARCHAR(50),
    suhu FLOAT,
    kelembaban FLOAT,
    waktu TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
if ($conn->query($sql1)) {
    echo "✅ Tabel 'tb_monitoring' siap.<br>";
} else {
    echo "❌ Gagal membuat 'tb_monitoring': " . $conn->error . "<br>";
}

// 2. BUAT TABEL tb_settings (Pengaturan & Status)
$sql2 = "CREATE TABLE IF NOT EXISTS tb_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) UNIQUE,
    value VARCHAR(255)
)";
if ($conn->query($sql2)) {
    echo "✅ Tabel 'tb_settings' siap.<br>";
} else {
    echo "❌ Gagal membuat 'tb_settings': " . $conn->error . "<br>";
}

// 3. BUAT TABEL tb_reliability_labels (V2.0 Research)
$sql3 = "CREATE TABLE IF NOT EXISTS tb_reliability_labels (
    id INT AUTO_INCREMENT PRIMARY KEY,
    monitoring_id INT NOT NULL,
    is_valid TINYINT(1) DEFAULT 1,
    reliability_score FLOAT,
    analysis_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY (monitoring_id)
)";
if ($conn->query($sql3)) {
    echo "✅ Tabel 'tb_reliability_labels' siap.<br>";
} else {
    echo "❌ Gagal membuat 'tb_reliability_labels': " . $conn->error . "<br>";
}

// 4. INISIALISASI SETTING AWAL
$now = time();
$setting1 = $conn->query("INSERT IGNORE INTO tb_settings (name, value) VALUES ('recording_service', '1')");
$setting2 = $conn->query("INSERT IGNORE INTO tb_settings (name, value) VALUES ('last_heartbeat', '$now')");

if ($setting1 && $setting2) {
    echo "✅ Inisialisasi pengaturan berhasil.<br>";
} else {
    echo "⚠️ Inisialisasi pengaturan mungkin sudah ada (INSERT IGNORE).<br>";
}

echo "<hr>";
echo "<h3>Selesai! Sekarang silakan buka kembali <a href='index.php'>Dashboard</a>.</h3>";
echo "Jika NodeMCU menyala, status akan segera berubah jadi HIJAU (ONLINE).";

$conn->close();
?>
