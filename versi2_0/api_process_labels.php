<?php
header('Content-Type: application/json');
require_once '../db_connect.php';

// 1. Ambil data yang belum dilabeli
$sql = "SELECT m.id, m.suhu, m.kelembaban, m.cahaya as gas, m.ldr 
        FROM tb_monitoring m 
        LEFT JOIN tb_reliability_labels l ON m.id = l.monitoring_id 
        WHERE l.monitoring_id IS NULL 
        ORDER BY m.id ASC";

$result = $conn->query($sql);
$data_to_label = [];
while ($row = $result->fetch_assoc()) {
    $data_to_label[] = $row;
}

if (empty($data_to_label)) {
    echo json_encode(['success' => true, 'message' => 'Semua data sudah terlabeli.']);
    exit;
}

$processed = 0;
$valid_count = 0;

// Ambang batas (Threshold) - Bisa disesuaikan
$thresholds = [
    'suhu' => 0.5,
    'kelembaban' => 1.0,
    'gas' => 20
];

foreach ($data_to_label as $row) {
    $id = $row['id'];

    // Ambil 10 data terakhir untuk menghitung stabilitas lokal (Moving Window)
    $window_sql = "SELECT suhu, kelembaban, cahaya as gas FROM tb_monitoring WHERE id <= $id ORDER BY id DESC LIMIT 20";
    $window_res = $conn->query($window_sql);

    $suhu_arr = [];
    $hum_arr = [];
    $gas_arr = [];

    while ($w = $window_res->fetch_assoc()) {
        $suhu_arr[] = (float) $w['suhu'];
        $hum_arr[] = (float) $w['kelembaban'];
        $gas_arr[] = (float) $w['gas'];
    }

    $is_valid = 1; // Default valid
    $reliability_score = 0.5; // Default netral (data terlalu sedikit untuk dihitung)

    // Hitung SD untuk setiap sensor dalam window tersebut
    if (count($suhu_arr) > 5) {
        $sd_suhu = calculate_sd($suhu_arr);
        $sd_hum  = calculate_sd($hum_arr);
        $sd_gas  = calculate_sd($gas_arr);

        // --- Hitung Reliability Score (0.0 - 1.0) ---
        // Skor per sensor: seberapa jauh SD dari batas maksimum toleransi
        $max_suhu = $thresholds['suhu'] * 3;      // batas SD suhu
        $max_hum  = $thresholds['kelembaban'] * 3; // batas SD kelembaban
        $max_gas  = $thresholds['gas'] * 3;        // batas SD gas

        $score_suhu = max(0.0, 1.0 - ($sd_suhu / $max_suhu));
        $score_hum  = max(0.0, 1.0 - ($sd_hum  / $max_hum));
        $score_gas  = max(0.0, 1.0 - ($sd_gas  / $max_gas));

        // Rata-rata tertimbang: Gas lebih berpengaruh (bobot 0.5)
        $reliability_score = round(($score_suhu * 0.25) + ($score_hum * 0.25) + ($score_gas * 0.50), 4);

        // Kriteria Validitas: Jika salah satu sensor sangat berisik (noise), tandai invalid
        if ($sd_suhu > $max_suhu || $sd_hum > $max_hum || $sd_gas > $max_gas) {
            $is_valid = 0;
        }

        // Cek apakah data spesifik ini adalah pencilan tajam (Spike/Outlier)
        $avg_suhu = array_sum($suhu_arr) / count($suhu_arr);
        if (abs($row['suhu'] - $avg_suhu) > $sd_suhu * 2 && $sd_suhu > 0.1) {
            $is_valid = 0;
        }

        // Jika akhirnya invalid, skor tidak boleh melebihi 0.4
        if ($is_valid === 0) {
            $reliability_score = min($reliability_score, 0.40);
        }
    }

    // Masukkan ke tabel label (dengan reliability_score)
    $stmt = $conn->prepare("INSERT IGNORE INTO tb_reliability_labels (monitoring_id, is_valid, reliability_score) VALUES (?, ?, ?)");
    $stmt->bind_param("iid", $id, $is_valid, $reliability_score);
    $stmt->execute();

    $processed++;
    if ($is_valid)
        $valid_count++;
}

echo json_encode([
    'success' => true,
    'processed' => $processed,
    'valid_recorded' => $valid_count,
    'message' => "Proses labeling selesai. $processed data diproses."
]);

function calculate_sd($arr)
{
    $n = count($arr);
    if ($n <= 1)
        return 0;
    $mean = array_sum($arr) / $n;
    $sum_sq = 0;
    foreach ($arr as $x) {
        $sum_sq += pow($x - $mean, 2);
    }
    return sqrt($sum_sq / $n);
}

$conn->close();
?>