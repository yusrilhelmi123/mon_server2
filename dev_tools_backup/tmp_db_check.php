<?php
$conn = new mysqli('localhost', 'root', '', 'db_sensor');
if ($conn->connect_error)
    die("Conn Error");
$res = $conn->query("SELECT id, waktu FROM tb_monitoring ORDER BY id DESC LIMIT 5");
while ($row = $res->fetch_assoc()) {
    echo "ID: " . $row['id'] . " | Waktu: " . $row['waktu'] . "\n";
}
$settings = $conn->query("SELECT name, value FROM tb_settings");
while ($s = $settings->fetch_assoc()) {
    echo "Setting: " . $s['name'] . " = " . $s['value'] . "\n";
}
echo "Current Server Time: " . date("Y-m-d H:i:s") . "\n";
?>