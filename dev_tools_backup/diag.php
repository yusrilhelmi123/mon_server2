<?php
$conn = new mysqli('localhost', 'root', '', 'db_sensor');
$res = $conn->query("SELECT value FROM tb_settings WHERE name = 'last_heartbeat'");
$row = $res->fetch_assoc();
$hb = (int) $row['value'];
$now = time();
$diff = $now - $hb;

echo "last_heartbeat  : $hb\n";
echo "time() sekarang : $now\n";
echo "Selisih         : {$diff} detik\n";
echo "Status          : " . ($diff < 30 ? "ONLINE" : "OFFLINE") . "\n";
$conn->close();
?>