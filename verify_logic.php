<?php
$conn = new mysqli('localhost', 'root', '', 'db_sensor');

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

$thresholds = [
    'suhu' => 0.5,
    'kelembaban' => 1.0,
    'gas' => 20
];

$ids = [125, 126, 127, 128, 129];

foreach ($ids as $id) {
    echo "Checking ID: $id\n";
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

    $sd_suhu = calculate_sd($suhu_arr);
    $sd_hum = calculate_sd($hum_arr);
    $sd_gas = calculate_sd($gas_arr);

    echo "SD Suhu: $sd_suhu (Threshold: " . ($thresholds['suhu'] * 3) . ")\n";
    echo "SD Hum: $sd_hum (Threshold: " . ($thresholds['kelembaban'] * 3) . ")\n";
    echo "SD Gas: $sd_gas (Threshold: " . ($thresholds['gas'] * 3) . ")\n";

    if ($sd_suhu > $thresholds['suhu'] * 3 || $sd_hum > $thresholds['kelembaban'] * 3 || $sd_gas > $thresholds['gas'] * 3) {
        echo "RESULT: INVALID (SD Exceeded)\n";
    }

    $avg_suhu = array_sum($suhu_arr) / count($suhu_arr);
    $current_row = $conn->query("SELECT suhu FROM tb_monitoring WHERE id = $id")->fetch_assoc();
    $diff = abs($current_row['suhu'] - $avg_suhu);
    echo "Suhu Diff: $diff (SD * 2: " . ($sd_suhu * 2) . ")\n";

    if ($diff > $sd_suhu * 2 && $sd_suhu > 0.1) {
        echo "RESULT: INVALID (Spike Detected)\n";
    }
    echo "--------------------------\n";
}

$conn->close();
?>