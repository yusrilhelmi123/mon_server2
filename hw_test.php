<?php
/**
 * SENSOLAB - ENDPOINT DIAGNOSTIK HARDWARE
 * Akses halaman ini dari browser laptop ATAU dari Serial Monitor NodeMCU
 * URL: http://localhost/mon_server2/hw_test.php
 */

// Respons JSON murni - cocok untuk NodeMCU & Browser
header('Content-Type: application/json');

require_once 'db_connect.php';

$now = time();
$clientIP = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// Catat siapa yang mengakses halaman ini
$result = [
    'status' => 'success',
    'message' => 'SensoLab endpoint aktif!',
    'server_time' => date('Y-m-d H:i:s'),
    'epoch' => $now,
    'client_ip' => $clientIP,
    'method' => $method,
    'db_connected' => !$conn->connect_error,
];

// Jika ada parameter sensor dari NodeMCU, proses dan update heartbeat
if (isset($_GET['cahaya'])) {
    // Update heartbeat
    $conn->query("INSERT INTO tb_settings (name, value) VALUES ('last_heartbeat', '$now') ON DUPLICATE KEY UPDATE value = '$now'");

    $result['heartbeat_updated'] = true;
    $result['params_received'] = [
        'cahaya' => $_GET['cahaya'] ?? null,
        'gas'    => $_GET['gas']    ?? null,
        'suhu'   => $_GET['suhu']   ?? null,
        'hum'    => $_GET['hum']    ?? null,
        'ldr'    => $_GET['ldr']    ?? null,
    ];
} else {
    $result['heartbeat_updated'] = false;
    $result['note'] = 'Tidak ada parameter sensor. Tambahkan ?cahaya=100&gas=Aman untuk update heartbeat.';
}

// Ambil status heartbeat terkini
$hbRes = $conn->query("SELECT value FROM tb_settings WHERE name='last_heartbeat'");
$lastHB = $hbRes && $hbRes->num_rows > 0 ? (int)$hbRes->fetch_assoc()['value'] : 0;
$diff = $lastHB > 0 ? ($now - $lastHB) : 999;
$result['last_heartbeat'] = $lastHB;
$result['seconds_ago'] = $diff;
$result['hardware_status'] = $diff < 30 ? 'ONLINE' : 'OFFLINE';

echo json_encode($result, JSON_PRETTY_PRINT);
$conn->close();
?>
