<?php
// Deteksi environment: Lokal (XAMPP) atau Online (Hosting)
$serverName = $_SERVER['SERVER_NAME'] ?? '';
$serverAddr = $_SERVER['SERVER_ADDR'] ?? '';

// Dianggap LOKAL jika: localhost, 127.0.0.1, atau IP jaringan lokal (192.168.x.x / 10.x.x.x)
$isLocal = ($serverName === 'localhost'
    || $serverAddr === '127.0.0.1'
    || strpos($serverAddr, '192.168.') === 0
    || strpos($serverAddr, '10.') === 0
    || strpos($serverName, '192.168.') === 0);

if ($isLocal) {
    // PENGATURAN LOKAL (XAMPP)
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "db_sensor";
} else {
    // PENGATURAN ONLINE (domainesia-aapanel)
    $servername = "localhost";
    $username = "root";
    $password = "BjxRP8Gy6jCitc4k";
    $dbname = "db_sensor";
}

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Koneksi Database Gagal: " . $conn->connect_error);
}

date_default_timezone_set('Asia/Jakarta');
?>