<?php
date_default_timezone_set('Asia/Jakarta');
$hariIni = date('l, d F Y');
$hariMap = ['Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => "Jum'at", 'Saturday' => 'Sabtu'];
$bulanMap = ['January' => 'Januari', 'February' => 'Februari', 'March' => 'Maret', 'April' => 'April', 'May' => 'Mei', 'June' => 'Juni', 'July' => 'Juli', 'August' => 'Agustus', 'September' => 'September', 'October' => 'Oktober', 'November' => 'November', 'December' => 'Desember'];
$hariIni = strtr($hariIni, array_merge($hariMap, $bulanMap));
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "db_sensor";
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Monitoring Sensor</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }

        h1,
        h2 {
            color: #333;
        }

        .stats-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin: 20px 0;
        }

        .card {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            border-top: 4px solid #4CAF50;
        }

        .card h3 {
            margin-top: 0;
            color: #333;
        }

        .card-stat {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 10px 0;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }

        .card-stat:last-child {
            border-bottom: none;
        }

        .card-label {
            font-weight: bold;
            color: #666;
        }

        .card-value {
            font-size: 18px;
            color: #4CAF50;
            font-weight: bold;
        }

        .content-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin: 20px 0;
        }

        .content-left {
            grid-column: 1;
        }

        .content-right {
            grid-column: 2;
        }

        @media (max-width: 768px) {

            .stats-container,
            .content-container {
                grid-template-columns: 1fr;
            }

            .content-left,
            .content-right {
                grid-column: 1;
            }

            .content-full {
                grid-column: 1 / -1;
            }
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
            margin: 20px 0;
        }

        table th {
            background-color: #4CAF50;
            color: white;
            padding: 10px;
            text-align: left;
        }

        table td {
            padding: 8px;
            border-bottom: 1px solid #ddd;
        }

        table tr:hover {
            background-color: #f5f5f5;
        }

        .pagination {
            text-align: center;
            padding: 20px;
            background-color: white;
            margin: 20px 0;
            border-radius: 5px;
        }

        .pagination a {
            padding: 8px 12px;
            margin: 5px;
            text-decoration: none;
            background-color: #4CAF50;
            color: white;
            border-radius: 3px;
            cursor: pointer;
        }

        .pagination a:hover {
            background-color: #45a049;
        }

        .info {
            background-color: white;
            padding: 10px;
            margin: 10px 0;
            border-left: 4px solid #4CAF50;
        }

        canvas {
            max-width: 100%;
            margin: 20px 0;
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <div
        style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 16px; margin-bottom: 20px; border-bottom: 2px solid #4CAF50;">
        <div style="display: flex; align-items: center; gap: 15px;">
            <a href="index.php" style="text-decoration: none; background: #4CAF50; color: white; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: 0.3s;" title="Kembali ke Beranda">
                <i class="fas fa-home"></i>
            </a>
            <div>
                <h1 style="margin: 0; font-size: 1.4rem; color: #222;">🧪 SensoLab — Log Data Sensor</h1>
                <div style="font-size: 0.72rem; color: #888; margin-top: 4px; font-style: italic;">Riset Instrumentasi Tahap
                    1 — Sensor Validation</div>
            </div>
        </div>
        <div style="text-align: right;">
            <div
                style="font-size: 0.7rem; color: #aaa; font-weight: 600; letter-spacing: 0.5px; text-transform: uppercase;">
                <span id="live-time"><?= date('H:i:s') ?></span>
            </div>
            <div style="font-size: 0.85rem; font-weight: 700; color: #333; margin-top: 2px;"><?= $hariIni ?></div>
        </div>
    </div>

    <?php
    // Koneksi ke Database
    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Koneksi Gagal: " . $conn->connect_error);
    }

    // Tangkap data dari NodeMCU (Metode GET)
    if (isset($_GET['cahaya']) && isset($_GET['gas'])) {
        // PERBARUI HEARTBEAT (Hardware Terdeteksi Aktif)
        $now = time();
        $conn->query("INSERT INTO tb_settings (name, value) VALUES ('last_heartbeat', '$now') ON DUPLICATE KEY UPDATE value = '$now'");

        // Cek apakah perekaman sedang aktif
        $recordingRes = $conn->query("SELECT value FROM tb_settings WHERE name = 'recording_service'");
        $recordingStatus = $recordingRes ? $recordingRes->fetch_assoc()['value'] : '1';

        if ($recordingStatus == '1') {
            $cahaya = $conn->real_escape_string($_GET['cahaya']); // Nilai Raw Gas
            $gas_status = $conn->real_escape_string($_GET['gas']); // Aman/Bahaya
            $ldr = isset($_GET['ldr']) ? $conn->real_escape_string($_GET['ldr']) : '-'; // Terang/Gelap
            $suhu = isset($_GET['suhu']) ? (float) $_GET['suhu'] : null;
            $hum = isset($_GET['hum']) ? (float) $_GET['hum'] : null;

            $sql = "INSERT INTO tb_monitoring (cahaya, status_gas, ldr, suhu, kelembaban) 
                    VALUES ('$cahaya', '$gas_status', '$ldr', '$suhu', '$hum')";

            if ($conn->query($sql) === TRUE) {
                echo "<div class='info'>✓ [RECORDING ON] Data Masuk: Gas=$cahaya, Status=$gas_status, Suhu=$suhu, Hum=$hum, LDR=$ldr</div>";
            } else {
                echo "<div class='info' style='border-left-color: #f44336;'>✗ Error Database: " . $conn->error . "</div>";
            }
        } else {
            echo "<div class='info' style='border-left-color: #607D8B;'>⏸ [RECORDING PAUSED] Data diterima tapi tidak disimpan ke database.</div>";
        }
    }

    // Ambil statistik (Hanya untuk kolom numerik 'cahaya')
    $statSql = "SELECT 
    COUNT(*) as total,
    AVG(cahaya) as avg_cahaya,
    MIN(cahaya) as min_cahaya,
    MAX(cahaya) as max_cahaya
FROM tb_monitoring";
    $statResult = $conn->query($statSql);
    $stats = $statResult ? $statResult->fetch_assoc() : null;

    // Ambil data terbaru untuk status gas
    $latestGasSql = "SELECT status_gas FROM tb_monitoring ORDER BY id DESC LIMIT 1";
    $latestGasResult = $conn->query($latestGasSql);
    $latestGas = ($latestGasResult && $latestGasResult->num_rows > 0) ? $latestGasResult->fetch_assoc()['status_gas'] : "-";

    // Tampilkan card monitoring
    if ($stats && $stats['total'] > 0) {
        echo "<div class='stats-container'>";

        // Card Monitoring Gas
        echo "<div class='card'>";
        echo "<h3>🔥 Monitoring Gas (MQ135)</h3>";
        echo "<div class='card-stat'>";
        echo "<span class='card-label'>Kadar Gas:</span>";
        echo "<span class='card-value' id='stat-max-gas'>" . htmlspecialchars($stats['max_cahaya']) . " PPM</span>";
        echo "</div>";
        echo "<div class='card-stat'>";
        echo "<span class='card-label'>Rata-rata Gas:</span>";
        echo "<span class='card-value' id='stat-avg-gas'>" . round($stats['avg_cahaya'], 2) . "</span>";
        echo "</div>";
        echo "<div class='card-stat'>";
        echo "<span class='card-label'>Minimum:</span>";
        echo "<span class='card-value' id='stat-min-gas'>" . htmlspecialchars($stats['min_cahaya']) . "</span>";
        echo "</div>";
        echo "</div>";

        echo "<div class='card' style='border-top-color: #2196F3;'>";
        echo "<h3>🌡️ Lingkungan (DHT11)</h3>";

        // Ambil data terbaru untuk suhu/hum
        $latestDhtSql = "SELECT suhu, kelembaban, ldr FROM tb_monitoring ORDER BY id DESC LIMIT 1";
        $latestDht = $conn->query($latestDhtSql)->fetch_assoc();

        echo "<div class='card-stat'>";
        echo "<span class='card-label'>Suhu:</span>";
        echo "<span class='card-value' id='stat-suhu'>" . ($latestDht['suhu'] ?? '0') . " °C</span>";
        echo "</div>";
        echo "<div class='card-stat'>";
        echo "<span class='card-label'>Kelembaban:</span>";
        echo "<span class='card-value' id='stat-hum'>" . ($latestDht['kelembaban'] ?? '0') . " %</span>";
        echo "</div>";
        echo "<div class='card-stat'>";
        echo "<span class='card-label'>Cahaya:</span>";
        $ldrColor = (isset($latestDht['ldr']) && $latestDht['ldr'] == 'Terang') ? '#FF9800' : '#607D8B';
        echo "<span class='card-value' id='stat-ldr' style='color: $ldrColor;'>" . ($latestDht['ldr'] ?? '-') . "</span>";
        echo "</div>";
        echo "</div>";

        echo "</div>";
    }

    // Konfigurasi paginasi
    $rowsPerPage = 15;
    $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
    if ($page < 1)
        $page = 1;
    $offset = ($page - 1) * $rowsPerPage;

    // Hitung total baris
    $countSql = "SELECT COUNT(*) as total FROM tb_monitoring";
    $countResult = $conn->query($countSql);
    $totalRows = ($countResult) ? $countResult->fetch_assoc()['total'] : 0;
    $totalPages = ceil($totalRows / $rowsPerPage);

    echo "<div class='content-container'>";
    echo "<div class='content-left'>";

    // Ambil data dengan paginasi
    $displaySql = "SELECT * FROM tb_monitoring ORDER BY id DESC LIMIT $rowsPerPage OFFSET $offset";
    $result = $conn->query($displaySql);

    if ($result && $result->num_rows > 0) {
        echo "<div class='info'><strong>📋 Data Monitoring Terbaru</strong> (Total: $totalRows)</div>";
        echo "<table>";
        echo "<tr>
            <th>ID</th>
            <th>Gas</th>
            <th>Status</th>
            <th>LDR</th>
            <th>Suhu(C)</th>
            <th>Hum(%)</th>
            <th>Waktu</th>
          </tr>";

        while ($row = $result->fetch_assoc()) {
            $gasClass = ($row['status_gas'] == 'Bahaya') ? 'color: red; font-weight: bold;' : 'color: green;';
            echo "<tr>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td>" . htmlspecialchars($row['cahaya']) . "</td>";
            echo "<td style='$gasClass'>" . htmlspecialchars($row['status_gas']) . "</td>";
            echo "<td>" . ($row['ldr'] ?? '-') . "</td>";
            echo "<td>" . ($row['suhu'] ?? '-') . "</td>";
            echo "<td>" . ($row['kelembaban'] ?? '-') . "</td>";
            echo "<td>" . $row['waktu'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";

        // Pagination UI
        if ($totalPages > 1) {
            echo "<div class='pagination'>";
            if ($page > 1)
                echo "<a href='?page=1'>&laquo;</a> <a href='?page=" . ($page - 1) . "'>&lt;</a>";
            echo "<span> Halaman $page dari $totalPages </span>";
            if ($page < $totalPages)
                echo "<a href='?page=" . ($page + 1) . "'>&gt;</a> <a href='?page=$totalPages'>&raquo;</a>";
            echo "</div>";
        }
    } else {
        echo "<div class='info'>Belum ada data monitoring.</div>";
    }

    echo "</div>"; // close content-left
    echo "<div class='content-right'>";

    // Graph data (Latest 30 points)
    $graphSql = "SELECT * FROM (SELECT * FROM tb_monitoring ORDER BY id DESC LIMIT 30) sub ORDER BY id ASC";
    $graphResult = $conn->query($graphSql);

    if ($graphResult && $graphResult->num_rows > 0) {
        $labels = [];
        $gasData = [];
        $suhuData = [];
        $ldrData = [];
        while ($row = $graphResult->fetch_assoc()) {
            $labels[] = date('H:i:s', strtotime($row['waktu']));
            $gasData[] = (int) $row['cahaya'];
            $suhuData[] = (float) $row['suhu'];
            // LDR selalu penuh (100), warna dikontrol via ldrStatus
            $ldrData[] = 100;
            $ldrStatus[] = ($row['ldr'] == 'Terang') ? 1 : 0;
        }

        $labelsJson = json_encode($labels);
        $gasJson = json_encode($gasData);
        $suhuJson = json_encode($suhuData);
        $ldrJson = json_encode($ldrData);
        $ldrStatusJson = json_encode($ldrStatus);

        echo "<h2>📊 Grafik Terpadu Multi-Sensor</h2>";
        echo "<p style='font-size: 12px; color: #888; margin: -10px 0 10px;'>
            <span style='color:#4CAF50'>&#9632;</span> Gas (kiri) &nbsp;
            <span style='color:#f44336'>&#9632;</span> Suhu (kanan) &nbsp;
            <span style='color:rgba(255,152,0,0.8)'>&#9646;</span> LDR Terang (Oranye) &nbsp;
            <span style='color:rgba(100,180,255,0.8)'>&#9646;</span> LDR Gelap (Biru)
        </p>";
        echo "<canvas id='multiChart' style='background: white; border-radius: 8px;'></canvas>";
        echo "<script src='https://cdn.jsdelivr.net/npm/chart.js'></script>";
        echo "<script>
    const ctx = document.getElementById('multiChart').getContext('2d');
    const ldrStatus = $ldrStatusJson;
    new Chart(ctx, {
        data: {
            labels: $labelsJson,
            datasets: [
                {
                    type: 'bar',
                    label: 'LDR',
                    data: $ldrJson,
                    backgroundColor: function(ctx) {
                        return ldrStatus[ctx.dataIndex] === 1 ? 'rgba(255,152,0,0.5)' : 'rgba(100,180,255,0.45)';
                    },
                    borderColor: function(ctx) {
                        return ldrStatus[ctx.dataIndex] === 1 ? 'rgba(255,152,0,0.8)' : 'rgba(80,160,255,0.8)';
                    },
                    borderWidth: 1,
                    yAxisID: 'y2',
                    order: 3,
                    barPercentage: 1.0,
                    categoryPercentage: 1.0
                },
                {
                    type: 'line',
                    label: 'Gas (MQ135)',
                    data: $gasJson,
                    borderColor: '#4CAF50',
                    backgroundColor: 'rgba(76,175,80,0.05)',
                    yAxisID: 'y',
                    tension: 0.4,
                    pointRadius: 3,
                    borderWidth: 2,
                    order: 1
                },
                {
                    type: 'line',
                    label: 'Suhu (°C)',
                    data: $suhuJson,
                    borderColor: '#f44336',
                    backgroundColor: 'rgba(244,67,54,0.05)',
                    yAxisID: 'y1',
                    tension: 0.3,
                    pointRadius: 3,
                    borderWidth: 2,
                    order: 2
                }
            ]
        },
        options: {
            responsive: true,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { display: true },
                tooltip: {
                    callbacks: {
                        label: function(ctx) {
                            if (ctx.dataset.label === 'LDR') {
                                return 'LDR: ' + (ldrStatus[ctx.dataIndex] === 1 ? 'Terang ☀️' : 'Gelap 🌙');
                            }
                            return ctx.dataset.label + ': ' + ctx.raw;
                        }
                    }
                }
            },
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: { display: true, text: 'Gas (PPM)' }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: { display: true, text: 'Suhu (°C)' },
                    grid: { drawOnChartArea: false }
                },
                y2: {
                    type: 'linear',
                    display: false,
                    min: 0,
                    max: 100,
                    grid: { drawOnChartArea: false }
                }
            }
        }
    });
    </script>";
    }

    echo "</div>"; // close content-right
    echo "</div>"; // close content-container
    
    $conn->close();
    ?>

    <script>
        // Auto-refresh hanya aktif saat dibuka di browser (bukan dari NodeMCU)
        // NodeMCU selalu menyertakan parameter 'cahaya', browser tidak
        <?php if (!isset($_GET['cahaya'])): ?>
            setInterval(() => {
                fetch('api_stats.php')
                    .then(r => r.json())
                    .then(d => {
                        // Update statistik gas
                        const el = id => document.getElementById(id);
                        if (el('stat-max-gas')) el('stat-max-gas').textContent = d.max_gas + ' PPM';
                        if (el('stat-avg-gas')) el('stat-avg-gas').textContent = d.avg_gas;
                        if (el('stat-min-gas')) el('stat-min-gas').textContent = d.min_gas;

                        // Update suhu & lembab
                        if (el('stat-suhu')) el('stat-suhu').textContent = d.suhu + ' °C';
                        if (el('stat-hum')) el('stat-hum').textContent = d.kelembaban + ' %';
                        if (el('stat-ldr')) {
                            el('stat-ldr').textContent = d.ldr;
                            el('stat-ldr').style.color = (d.ldr === 'Terang') ? '#FF9800' : '#607D8B';
                        }

                        // Update jam di header
                        if (el('live-time')) el('live-time').textContent = d.server_time;

                        // Update total data di judul section (jika ada)
                        if (el('total-records')) el('total-records').textContent = d.total.toLocaleString('id-ID');
                    })
                    .catch(() => { });
            }, 5000);
        <?php endif; ?>
    </script>

</body>

</html>