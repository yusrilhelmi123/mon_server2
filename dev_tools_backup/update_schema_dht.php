<?php
$conn = new mysqli('localhost', 'root', '', 'db_sensor');
if ($conn->connect_error) {
    die("Koneksi Gagal: " . $conn->connect_error);
}

// Tambah kolom suhu dan kelembaban
$sql = "ALTER TABLE tb_monitoring 
        ADD COLUMN suhu FLOAT DEFAULT NULL AFTER ldr,
        ADD COLUMN kelembaban FLOAT DEFAULT NULL AFTER suhu";

if ($conn->query($sql)) {
    echo "Kolom 'suhu' dan 'kelembaban' berhasil ditambahkan.";
} else {
    echo "Error: " . $conn->error;
}

$conn->close();
?>