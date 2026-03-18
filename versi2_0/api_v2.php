<?php
header('Content-Type: application/json');
date_default_timezone_set('Asia/Jakarta');

$conn = new mysqli('localhost', 'root', '', 'db_sensor');
if ($conn->connect_error) {
    echo json_encode(['error' => 'Koneksi Database Gagal']);
    exit;
}

// 1. Ambil 50 data terakhir untuk grafik (Histori)
// Dibalik urutannya agar dari lama ke baru untuk Chart.js
$historyResult = $conn->query("SELECT * FROM (SELECT id, cahaya, status_gas, ldr, suhu, kelembaban, waktu FROM tb_monitoring ORDER BY id DESC LIMIT 50) sub ORDER BY id ASC");
$history = [];
while ($row = $historyResult->fetch_assoc()) {
    // Mapping LDR string ke numerik untuk kenyamanan grafik (Terang: 800, Gelap: 200)
    $ldrNum = ($row['ldr'] == 'Terang') ? 800 : 200;

    $history[] = [
        'id' => (int) $row['id'],
        'gas' => (int) $row['cahaya'],
        'suhu' => $row['suhu'] !== null ? (float) $row['suhu'] : 0,
        'kelembaban' => $row['kelembaban'] !== null ? (float) $row['kelembaban'] : 0,
        'ldr_num' => $ldrNum,
        'ldr_raw' => $row['ldr'],
        'waktu' => date('H:i:s', strtotime($row['waktu']))
    ];
}

// 2. Ambil data paling baru
if (!empty($history)) {
    $latest = end($history);
} else {
    $latest = null;
}

// 3. Cek Status Hardware (Heartbeat) & Recording
$hbRow = $conn->query("SELECT value FROM tb_settings WHERE name='last_heartbeat'")->fetch_assoc();
$recRow = $conn->query("SELECT value FROM tb_settings WHERE name='recording_service'")->fetch_assoc();

$lastHB = $hbRow ? (int) $hbRow['value'] : 0;
$diff = ($lastHB > 0) ? time() - $lastHB : 999;
$isOnline = ($diff < 30);
$isRecording = $recRow ? ($recRow['value'] == '1') : false;

// 4. Ambil Statistik Global (Total Data)
$totalAll = $conn->query("SELECT COUNT(*) as total FROM tb_monitoring")->fetch_assoc()['total'];
$today = date('Y-m-d');
$totalToday = $conn->query("SELECT COUNT(*) as total FROM tb_monitoring WHERE DATE(waktu) = '$today'")->fetch_assoc()['total'];
$totalInvalid = $conn->query("SELECT COUNT(*) as total FROM tb_reliability_labels WHERE is_valid = 0")->fetch_assoc()['total'];

// 5. Hitung Statistik Sederhana untuk Data di Buffer (Histori)
$gasArr = array_column($history, 'gas');
$count = count($gasArr);
$mean = $count > 0 ? array_sum($gasArr) / $count : 0;

$variance = 0;
if ($count > 1) {
    foreach ($gasArr as $val) {
        $variance += pow(($val - $mean), 2);
    }
    $variance /= $count;
}
$stdDev = sqrt($variance);

// Response Akhir
echo json_encode([
    'status' => [
        'is_online' => $isOnline,
        'is_recording' => $isRecording,
        'total_all' => (int) $totalAll,
        'total_today' => (int) $totalToday,
        'total_invalid' => (int) $totalInvalid,
        'last_update' => $latest ? $latest['waktu'] : '-',
        'server_time' => date('H:i:s')
    ],
    'latest' => $latest,
    'history' => $history,
    'stats_buffer' => [
        'mean' => round($mean, 2),
        'std_dev' => round($stdDev, 2),
        'variance' => round($variance, 4),
        'sample_size' => $count
    ]
]);

$conn->close();
?>