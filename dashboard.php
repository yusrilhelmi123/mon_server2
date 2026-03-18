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

// Koneksi ke Database
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Koneksi Gagal: " . $conn->connect_error);
}

// Ambil data terbaru untuk Header/Summary
$latestSql = "SELECT * FROM tb_monitoring ORDER BY id DESC LIMIT 1";
$latestResult = $conn->query($latestSql);
$latest = ($latestResult && $latestResult->num_rows > 0) ? $latestResult->fetch_assoc() : ['cahaya' => 0, 'status_gas' => '-', 'ldr' => '-', 'waktu' => '-'];

// Data Summary (Hari Ini untuk statistik rata-rata)
$today = date('Y-m-d');
$summarySql = "SELECT COUNT(*) as total_today, AVG(cahaya) as avg_gas, MAX(cahaya) as peak_gas, AVG(suhu) as avg_temp, AVG(kelembaban) as avg_hum FROM tb_monitoring WHERE DATE(waktu) = '$today'";
$summaryRes = $conn->query($summarySql);
$summary = $summaryRes->fetch_assoc();
if ($summary['total_today'] == 0) {
    $summarySql = "SELECT COUNT(*) as total_today, AVG(cahaya) as avg_gas, MAX(cahaya) as peak_gas, AVG(suhu) as avg_temp, AVG(kelembaban) as avg_hum FROM tb_monitoring";
    $summaryRes = $conn->query($summarySql);
    $summary = $summaryRes->fetch_assoc();
}

// Total keseluruhan data di database (semua waktu)
$totalAllRes = $conn->query("SELECT COUNT(*) as total_all FROM tb_monitoring");
$totalAll = ($totalAllRes) ? $totalAllRes->fetch_assoc()['total_all'] : 0;


// Data Chart & Table (10 data terbaru)
$dataSql = "SELECT * FROM (SELECT * FROM tb_monitoring ORDER BY id DESC LIMIT 10) sub ORDER BY id ASC";
$dataRes = $conn->query($dataSql);
$rows = [];
while ($r = $dataRes->fetch_assoc()) {
    $rows[] = $r;
}
// Ambil status perekaman
$recResult = $conn->query("SELECT value FROM tb_settings WHERE name = 'recording_service'");
$recordingActive = ($recResult && $recResult->fetch_assoc()['value'] == '1');

// Data untuk Chart (Sorted ASC)
$labels = [];
$gasData = [];
$ldrData = [];
$tempData = [];
$humData = [];
foreach ($rows as $row) {
    $labels[] = date('H:i:s', strtotime($row['waktu']));
    $gasData[] = (int) $row['cahaya'];
    $tempData[] = (float) $row['suhu'];
    $humData[] = (float) $row['kelembaban'];
    $ldrStatus = isset($row['ldr']) ? $row['ldr'] : '-';
    $ldrData[] = ($ldrStatus == 'Terang') ? 1 : 0;
}
// Data untuk Table (Sorted DESC)
$tableRows = array_reverse($rows);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Monitor | Dual-Sensor Pro</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary: #6366f1;
            --bg-dark: #0f172a;
            --card-bg: rgba(30, 41, 59, 0.7);
            --danger: #ef4444;
            --success: #22c55e;
            --warning: #fbbf24;
            --info: #3b82f6;
            --muted: #64748b;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        body {
            background-color: var(--bg-dark);
            color: var(--text-main);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
        }

        /* Shared Components */
        .glass-card {
            background: var(--card-bg);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 20px;
        }

        .label {
            font-size: 0.75rem;
            color: var(--text-muted);
            font-weight: 700;
            text-transform: uppercase;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Header & Summary */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding: 20px;
            background: var(--card-bg);
            border-radius: 15px;
        }

        .logo {
            font-size: 1.25rem;
            font-weight: 800;
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--primary);
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 25px;
        }

        /* Main Monitoring Sections */
        .sensor-section {
            margin-bottom: 30px;
        }

        .section-title {
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 15px;
            padding-left: 10px;
            border-left: 4px solid var(--primary);
        }

        .split-view {
            display: grid;
            grid-template-columns: 1.5fr 1fr;
            gap: 20px;
        }

        /* Table Styling */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.85rem;
        }

        .data-table th {
            text-align: left;
            color: var(--text-muted);
            padding: 10px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .data-table td {
            padding: 10px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .badge {
            padding: 30px 8px;
            border-radius: 5px;
            font-weight: 700;
            font-size: 0.7rem;
        }

        .pulse {
            width: 8px;
            height: 8px;
            background: var(--success);
            border-radius: 50%;
            animation: pulse 2s infinite;
            display: inline-block;
        }

        @keyframes pulse {
            0% {
                opacity: 1;
            }

            50% {
                opacity: 0.4;
            }

            100% {
                opacity: 1;
            }
        }

        @media (max-width: 1024px) {
            .split-view {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>

    <div class="container">
        <?php
        // Deteksi Status Hardware berdasarkan Heartbeat (tetap update meski RECORDING OFF)
        $heartbeatRes = $conn->query("SELECT value FROM tb_settings WHERE name = 'last_heartbeat'");
        $hbRow = ($heartbeatRes && $heartbeatRes->num_rows > 0) ? $heartbeatRes->fetch_assoc() : null;
        $lastHeartbeat = $hbRow ? (int) $hbRow['value'] : 0;
        $currentTime = time();

        // Proteksi: Jika nilai tidak valid (0 atau lebih besar dari waktu sekarang), anggap offline
        $diff = ($lastHeartbeat > 0 && $lastHeartbeat <= $currentTime) ? ($currentTime - $lastHeartbeat) : 999;

        // Status ONLINE jika alat menyentuh server dalam 30 detik terakhir
        $isOnline = ($diff < 30);
        ?>

        <div class="header" style="border-bottom: 2px solid <?= $isOnline ? 'var(--success)' : 'var(--danger)' ?>;">
            <!-- Logo & Identitas Riset -->
            <div>
            <a href="index.php" style="text-decoration: none; color: inherit; display: block;">
                <div class="logo" style="font-size: 1.4rem; letter-spacing: 1px;">
                    <i class="fas fa-flask" style="color: var(--primary);"></i>
                    Senso<span style="color: var(--primary);">Lab</span>
                </div>
            </a>
                <div
                    style="margin-top: 4px; padding-left: 2px; border-left: 3px solid var(--primary); padding-left: 8px;">
                    <div
                        style="font-size: 0.72rem; color: var(--text-muted); font-weight: 600; letter-spacing: 0.5px; text-transform: uppercase;">
                        Riset Instrumentasi Tahap 1 &mdash; Sensor Validation
                    </div>
                    <div style="font-size: 0.65rem; color: #64748b; font-style: italic; margin-top: 2px;">
                        Oleh : Vanya Clianta Evelyn Pasha
                    </div>
                </div>
            </div>

            <!-- Tanggal Tengah -->
            <div style="text-align: center; flex: 1;">
                <div
                    style="font-size: 0.72rem; color: var(--text-muted); font-weight: 600; letter-spacing: 0.5px; text-transform: uppercase;">
                    <?= date('H:i:s') ?></div>
                <div style="font-size: 0.8rem; color: var(--text-main); font-weight: 700; margin-top: 2px;">
                    <?= $hariIni ?></div>
            </div>

            <div style="display: flex; align-items: center; gap: 20px;">
                <!-- Indikator Status Perekaman -->
                <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 4px;">
                    <button id="recordToggle" onclick="toggleRecording(<?= $recordingActive ? '0' : '1' ?>)"
                        style="background: <?= $recordingActive ? 'var(--danger)' : 'var(--success)' ?>; 
                                   color: white; border: none; padding: 6px 12px; border-radius: 6px; 
                                   font-weight: 700; cursor: pointer; font-size: 0.75rem; display: flex; align-items: center; gap: 6px;">
                        <i class="fas <?= $recordingActive ? 'fa-stop-circle' : 'fa-play-circle' ?>"></i>
                        <?= $recordingActive ? 'STOP RECORD' : 'START RECORD' ?>
                    </button>
                    <span
                        style="font-size: 0.65rem; font-weight: 800; color: <?= $recordingActive ? 'var(--success)' : 'var(--warning)' ?>;">
                        STATUS: <?= $recordingActive ? 'MEREKAM...' : 'DIJEDA (MANUAL)' ?>
                    </span>
                </div>

                <div style="text-align: right; border-left: 1px solid rgba(255,255,255,0.1); padding-left: 20px;">
                    <div
                        style="font-size: 0.85rem; font-weight: 700; color: <?= $isOnline ? 'var(--success)' : 'var(--danger)' ?>;">
                        <span class="<?= $isOnline ? 'pulse' : '' ?>"
                            style="background: <?= $isOnline ? 'var(--success)' : 'var(--danger)' ?>;"></span>
                        HARDWARE: <?= $isOnline ? 'ONLINE' : 'OFFLINE' ?>
                    </div>
                    <?php if (!$isOnline): ?>
                        <div style="font-size: 0.65rem; color: var(--danger); margin-top: 4px;">
                            Terputus: <?= $diff ?>s yang lalu
                        </div>
                    <?php else: ?>
                        <div style="font-size: 0.65rem; color: var(--success); opacity: 0.8; margin-top: 4px;">
                            Update: <?= $diff ?>s lalu
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- NOTIFIKASI STATUS -->
        <div id="status-notifications">
            <?php if (!$isOnline): ?>
                <div
                    style="background: rgba(239, 68, 68, 0.15); border: 1px solid var(--danger); padding: 12px 20px; border-radius: 12px; margin-bottom: 10px; display: flex; align-items: center; gap: 15px;">
                    <i class="fas fa-wifi-slash" style="color: var(--danger); font-size: 1.2rem;"></i>
                    <div style="font-size: 0.85rem;">
                        <strong style="color: var(--danger);">Koneksi Hardware Terputus!</strong> Periksa daya NodeMCU atau
                        koneksi WiFi.
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!$recordingActive): ?>
                <div
                    style="background: rgba(251, 191, 36, 0.1); border: 1px solid var(--warning); padding: 12px 20px; border-radius: 12px; margin-bottom: 20px; display: flex; align-items: center; gap: 15px;">
                    <i class="fas fa-pause-circle" style="color: var(--warning); font-size: 1.2rem;"></i>
                    <div style="font-size: 0.85rem;">
                        <strong style="color: var(--warning);">Perekaman Dijeda Manual.</strong> Data sensor tetap tampil
                        sebagai simulasi, namun <u style="color:#fff">tidak akan masuk</u> ke database.
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- SUMMARIES -->
        <div class="summary-grid">
            <div class="glass-card" style="border-bottom: 3px solid #ef4444;">
                <div class="label"><i class="fas fa-temperature-high"></i> Suhu Rata-rata</div>
                <div style="font-size: 1.2rem; font-weight: 700;"><?= round($summary['avg_temp'], 1) ?> °C</div>
            </div>
            <div class="glass-card" style="border-bottom: 3px solid #3b82f6;">
                <div class="label"><i class="fas fa-tint"></i> Kelembaban Rata-rata</div>
                <div style="font-size: 1.2rem; font-weight: 700;"><?= round($summary['avg_hum'], 1) ?> %</div>
            </div>
            <div class="glass-card" style="border-bottom: 3px solid var(--primary);">
                <div class="label"><i class="fas fa-smog"></i> Rerata Gas (MQ135)</div>
                <div style="font-size: 1.2rem; font-weight: 700;"><?= round($summary['avg_gas'], 1) ?> PPM</div>
            </div>
            <div class="glass-card" style="border-bottom: 3px solid var(--warning);">
                <div class="label"><i class="fas fa-sun"></i> Kondisi Cahaya</div>
                <div style="font-size: 1.2rem; font-weight: 700; color: var(--warning);"><?= $latest['ldr'] ?></div>
            </div>
            <div class="glass-card" style="border-bottom: 3px solid var(--success);">
                <div class="label"><i class="fas fa-database"></i> Total Data Terbaca</div>
                <div style="font-size: 1.2rem; font-weight: 700;"><?= number_format($totalAll) ?> Log</div>
                <div style="font-size: 0.7rem; color: var(--text-muted); margin-top: 4px;">Hari ini:
                    <?= number_format($summary['total_today']) ?> log
                </div>
            </div>
        </div>

        <!-- GAS SECTION -->
        <div class="sensor-section">
            <div class="section-title">Monitoring Kualitas Udara (MQ-135)</div>
            <div class="split-view">
                <div class="glass-card">
                    <div class="label"><i class="fas fa-chart-area"></i> Grafik Realtime</div>
                    <div style="height: 300px;"><canvas id="gasChart"></canvas></div>
                </div>
                <div class="glass-card">
                    <div class="label"><i class="fas fa-list"></i> 10 Data Terakhir</div>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Waktu</th>
                                <th>PPM</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tableRows as $tr):
                                $c = $tr['status_gas'] == 'Bahaya' ? 'var(--danger)' : 'var(--success)';
                                ?>
                                <tr>
                                    <td><?= date('H:i:s', strtotime($tr['waktu'])) ?></td>
                                    <td><strong><?= $tr['cahaya'] ?></strong></td>
                                    <td style="color: <?= $c ?>; font-weight: 800;"><?= strtoupper($tr['status_gas']) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ENVIRONMENTAL SECTION -->
        <div class="sensor-section">
            <div class="section-title">Monitoring Lingkungan (DHT11 & LDR)</div>
            <div class="split-view">
                <div class="glass-card">
                    <div class="label"><i class="fas fa-thermometer-half"></i> Grafik Suhu & Lembab</div>
                    <div style="height: 300px;"><canvas id="envChart"></canvas></div>
                </div>
                <div class="glass-card">
                    <div class="label"><i class="fas fa-list"></i> Log Kondisi Terakhir</div>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Waktu</th>
                                <th>Suhu</th>
                                <th>Hum</th>
                                <th>Cahaya</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tableRows as $tr):
                                $c = $tr['ldr'] == 'Terang' ? 'var(--warning)' : 'var(--text-muted)';
                                ?>
                                <tr>
                                    <td><?= date('H:i:s', strtotime($tr['waktu'])) ?></td>
                                    <td><strong><?= $tr['suhu'] ?>°C</strong></td>
                                    <td><strong><?= $tr['kelembaban'] ?>%</strong></td>
                                    <td style="color: <?= $c ?>; font-weight: 800; font-size: 0.7rem;">
                                        <i class="fas <?= $tr['ldr'] == 'Terang' ? 'fa-sun' : 'fa-moon' ?>"></i>
                                        <?= strtoupper($tr['ldr'] ? $tr['ldr'] : '-') ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div style="text-align: center; font-size: 0.75rem; color: var(--text-muted); margin-top: 20px;">
            Auto-Refresh Aktif • Interval 5 Detik • ID Device: NodeMCU-ESP8266
        </div>
    </div>

    <!-- ADMIN TOOLS -->
    <div style="margin-top: 20px; display: flex; justify-content: flex-end; gap: 10px;">
        <a href="delete_data.php"
            style="background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.3); padding: 8px 16px; border-radius: 8px; text-decoration: none; font-size: 0.8rem; font-weight: 700; display: flex; align-items: center; gap: 8px; transition: 0.3s;">
            <i class="fas fa-trash-alt"></i> KOSONGKAN DATABASE (ADMIN)
        </a>
    </div>

    <script>
        const chartSettings = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { display: false }, ticks: { color: '#64748b', font: { size: 10 } } },
                y: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#64748b' } }
            }
        };

        // Chart Gas
        new Chart(document.getElementById('gasChart'), {
            type: 'line',
            data: {
                labels: <?= json_encode($labels) ?>,
                datasets: [{
                    data: <?= json_encode($gasData) ?>,
                    borderColor: '#6366f1',
                    borderWidth: 3,
                    tension: 0.4,
                    fill: true,
                    backgroundColor: 'rgba(99, 102, 241, 0.1)',
                    pointRadius: 4,
                    pointBackgroundColor: '#6366f1'
                }]
            },
            options: chartSettings
        });

        // Chart Lingkungan (Suhu & Lembab)
        new Chart(document.getElementById('envChart'), {
            type: 'line',
            data: {
                labels: <?= json_encode($labels) ?>,
                datasets: [
                    {
                        label: 'Suhu (°C)',
                        data: <?= json_encode($tempData) ?>,
                        borderColor: '#ef4444',
                        borderWidth: 2,
                        tension: 0.3,
                        pointRadius: 2
                    },
                    {
                        label: 'Lembab (%)',
                        data: <?= json_encode($humData) ?>,
                        borderColor: '#3b82f6',
                        borderWidth: 2,
                        tension: 0.3,
                        pointRadius: 2
                    }
                ]
            },
            options: {
                ...chartSettings,
                plugins: { legend: { display: true, labels: { color: '#94a3b8', boxWidth: 10, font: { size: 10 } } } }
            }
        });

        function toggleRecording(status) {
            const formData = new FormData();
            formData.append('status', status);

            fetch('toggle_recording.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.text())
                .then(data => {
                    if (data === 'Success') {
                        location.reload();
                    } else {
                        alert('Gagal mengubah status perekaman');
                    }
                })
                .catch(error => console.error('Error:', error));
        }

        let refreshTimeout = setTimeout(() => { location.reload(); }, 5000);
    </script>

</body>

</html>
<?php $conn->close(); ?>