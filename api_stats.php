<?php
header('Content-Type: application/json');
require_once 'db_connect.php';

// Total data
$total = $conn->query("SELECT COUNT(*) as t FROM tb_monitoring")->fetch_assoc()['t'];

// Statistik Gas
$stat = $conn->query("SELECT AVG(cahaya) as avg, MIN(cahaya) as min, MAX(cahaya) as max FROM tb_monitoring")->fetch_assoc();

// Data terbaru (DHT + LDR + Gas)
$latest = $conn->query("SELECT cahaya, status_gas, suhu, kelembaban, ldr, waktu FROM tb_monitoring ORDER BY id DESC LIMIT 1")->fetch_assoc();

// Heartbeat & recording status
$hbRow = $conn->query("SELECT value FROM tb_settings WHERE name='last_heartbeat'")->fetch_assoc();
$recRow = $conn->query("SELECT value FROM tb_settings WHERE name='recording_service'")->fetch_assoc();
$lastHB = $hbRow ? (int) $hbRow['value'] : 0;
$diff = ($lastHB > 0 && $lastHB <= time()) ? time() - $lastHB : 999;

echo json_encode([
    'total' => (int) $total,
    'avg_gas' => round($stat['avg'], 1),
    'min_gas' => (int) $stat['min'],
    'max_gas' => (int) $stat['max'],
    'suhu' => $latest['suhu'] ?? '-',
    'kelembaban' => $latest['kelembaban'] ?? '-',
    'ldr' => $latest['ldr'] ?? '-',
    'gas_raw' => $latest['cahaya'] ?? '-',
    'gas_status' => $latest['status_gas'] ?? '-',
    'waktu' => $latest ? date('H:i:s', strtotime($latest['waktu'])) : '-',
    'is_online' => ($diff < 30),
    'recording' => $recRow ? ($recRow['value'] == '1') : false,
    'server_time' => date('H:i:s'),
]);

$conn->close();
?>
