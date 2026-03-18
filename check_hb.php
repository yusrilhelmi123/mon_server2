<?php
require_once 'db_connect.php';

echo "<h3>Debugging Heartbeat Sistem</h3>";

// 1. Cek semua isi tb_settings
$res = $conn->query("SELECT * FROM tb_settings");
echo "<table border='1'><tr><th>Name</th><th>Value</th></tr>";
while ($row = $res->fetch_assoc()) {
    echo "<tr><td>{$row['name']}</td><td>{$row['value']}</td></tr>";
}
echo "</table>";

// 2. Cek waktu SEKARANG di PHP
$now = time();
echo "<p>Waktu Sekarang (Epoch): $now</p>";

// 3. Hitung selisih untuk last_heartbeat
$hbRes = $conn->query("SELECT value FROM tb_settings WHERE name = 'last_heartbeat'");
if ($hbRes && $hbRes->num_rows > 0) {
    $lastHB = (int) $hbRes->fetch_assoc()['value'];
    $diff = $now - $lastHB;
    echo "<p>Last Heartbeat: $lastHB</p>";
    echo "<p>Selisih: $diff detik</p>";
    echo "<p>Status: " . ($diff < 30 ? "<span style='color:green'>ONLINE</span>" : "<span style='color:red'>OFFLINE</span>") . "</p>";
} else {
    echo "<p style='color:red'>Error: 'last_heartbeat' tidak ditemukan di tb_settings!</p>";
}

$conn->close();
?>
