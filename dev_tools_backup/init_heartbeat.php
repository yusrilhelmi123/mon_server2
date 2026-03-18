<?php
$conn = new mysqli('localhost', 'root', '', 'db_sensor');
if ($conn->connect_error)
    die("Koneksi Gagal: " . $conn->connect_error);

$now = time();

// Insert last_heartbeat kalau belum ada
$conn->query("INSERT INTO tb_settings (name, value) VALUES ('last_heartbeat', '$now') ON DUPLICATE KEY UPDATE value = value");

echo "Isi Tabel tb_settings setelah inisialisasi:\n";
$res = $conn->query("SELECT * FROM tb_settings");
while ($row = $res->fetch_assoc()) {
    echo "Name: " . $row['name'] . " | Value: " . $row['value'] . "\n";
}
$conn->close();
?>