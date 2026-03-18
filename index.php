<?php
date_default_timezone_set('Asia/Jakarta');
$conn = new mysqli('localhost', 'root', '', 'db_sensor');

$totalData = 0;
$latestWaktu = '-';
$isOnline = false;
$recordingActive = false;

if (!$conn->connect_error) {
    $totalRes = $conn->query("SELECT COUNT(*) as total FROM tb_monitoring");
    $totalData = $totalRes ? $totalRes->fetch_assoc()['total'] : 0;

    $latestRes = $conn->query("SELECT waktu FROM tb_monitoring ORDER BY id DESC LIMIT 1");
    if ($latestRes && $latestRes->num_rows > 0)
        $latestWaktu = $latestRes->fetch_assoc()['waktu'];

    $hbRes = $conn->query("SELECT value FROM tb_settings WHERE name = 'last_heartbeat'");
    $hbRow = ($hbRes && $hbRes->num_rows > 0) ? $hbRes->fetch_assoc() : null;
    $lastHB = $hbRow ? (int) $hbRow['value'] : 0;
    $diff = ($lastHB > 0 && $lastHB <= time()) ? (time() - $lastHB) : 999;
    $isOnline = ($diff < 30);

    $recRes = $conn->query("SELECT value FROM tb_settings WHERE name = 'recording_service'");
    $recRow = ($recRes && $recRes->num_rows > 0) ? $recRes->fetch_assoc() : null;
    $recordingActive = $recRow ? ($recRow['value'] == '1') : false;

    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SensoLab — IoT Research Portal</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #6366f1;
            --bg: #080c1a;
            --card: rgba(15, 23, 42, 0.8);
            --border: rgba(255, 255, 255, 0.08);
            --success: #22c55e;
            --danger: #ef4444;
            --warning: #fbbf24;
            --text: #f1f5f9;
            --muted: #64748b;
        }

        *,
        *::before,
        *::after {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        body {
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            background-image:
                radial-gradient(ellipse at 20% 20%, rgba(99, 102, 241, 0.15) 0%, transparent 60%),
                radial-gradient(ellipse at 80% 80%, rgba(34, 197, 94, 0.08) 0%, transparent 60%);
        }

        .container {
            max-width: 1100px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        /* HERO */
        .hero {
            text-align: center;
            padding: 60px 20px 40px;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(99, 102, 241, 0.15);
            border: 1px solid rgba(99, 102, 241, 0.3);
            border-radius: 50px;
            padding: 6px 16px;
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: #a5b4fc;
            margin-bottom: 24px;
        }

        .hero h1 {
            font-size: 3.5rem;
            font-weight: 800;
            letter-spacing: -1px;
            background: linear-gradient(135deg, #fff 40%, #6366f1 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            line-height: 1.1;
        }

        .hero h1 span {
            -webkit-text-fill-color: #6366f1;
        }

        .hero-subtitle {
            margin-top: 12px;
            font-size: 0.85rem;
            color: var(--muted);
            font-style: italic;
            border-left: 3px solid var(--primary);
            display: inline-block;
            padding-left: 12px;
            text-align: left;
            line-height: 1.7;
        }

        /* STATUS BAR */
        .status-bar {
            display: flex;
            justify-content: center;
            gap: 24px;
            flex-wrap: wrap;
            margin: 30px 0 50px;
        }

        .status-chip {
            display: flex;
            align-items: center;
            gap: 8px;
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 50px;
            padding: 8px 18px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .dot.pulse {
            animation: pulse 1.5s infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
                transform: scale(1);
            }

            50% {
                opacity: 0.5;
                transform: scale(1.3);
            }
        }

        /* CARDS */
        .section-label {
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: var(--muted);
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-label::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--border);
        }

        .cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .nav-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 28px;
            text-decoration: none;
            color: inherit;
            display: flex;
            flex-direction: column;
            gap: 16px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            backdrop-filter: blur(12px);
        }

        .nav-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--card-accent, var(--primary));
            border-radius: 20px 20px 0 0;
        }

        .nav-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4), 0 0 0 1px rgba(255, 255, 255, 0.06);
            border-color: rgba(255, 255, 255, 0.15);
        }

        .card-icon {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
        }

        .card-title {
            font-size: 1.05rem;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .card-desc {
            font-size: 0.8rem;
            color: var(--muted);
            line-height: 1.5;
        }

        .card-arrow {
            margin-top: auto;
            font-size: 0.75rem;
            color: var(--muted);
            display: flex;
            align-items: center;
            gap: 6px;
            transition: gap 0.2s, color 0.2s;
        }

        .nav-card:hover .card-arrow {
            gap: 10px;
            color: var(--text);
        }

        /* FOOTER */
        .footer {
            text-align: center;
            padding: 30px 0;
            border-top: 1px solid var(--border);
            font-size: 0.72rem;
            color: var(--muted);
            line-height: 1.8;
        }

        .footer strong {
            color: var(--primary);
        }

        @media (max-width: 600px) {
            .hero h1 {
                font-size: 2.2rem;
            }

            .cards-grid {
                grid-template-columns: 1fr;
            }

            .status-bar {
                gap: 12px;
            }
        }
    </style>
</head>

<body>
    <div class="container">

        <!-- HERO -->
        <div class="hero">
            <div class="hero-badge"><i class="fas fa-flask"></i> IoT Research Portal</div>
            <h1>Senso<span>Lab</span></h1>
            <div class="hero-subtitle">
                Riset Instrumentasi Tahap 1 — Sensor Validation<br>
                <span style="font-style: normal; color: #94a3b8; font-size: 0.8rem;">Oleh : Vanya Clianta Evelyn
                    Pasha</span>
            </div>
        </div>

        <!-- STATUS BAR -->
        <div class="status-bar">
            <div class="status-chip">
                <div class="dot <?= $isOnline ? 'pulse' : '' ?>"
                    style="background: <?= $isOnline ? 'var(--success)' : 'var(--danger)' ?>;"></div>
                <span>Hardware: <strong><?= $isOnline ? 'Online' : 'Offline' ?></strong></span>
            </div>
            <div class="status-chip">
                <div class="dot" style="background: <?= $recordingActive ? 'var(--success)' : 'var(--warning)' ?>;">
                </div>
                <span>Recording: <strong><?= $recordingActive ? 'Aktif' : 'Dijeda' ?></strong></span>
            </div>
            <div class="status-chip">
                <i class="fas fa-database" style="color: var(--primary); font-size: 0.75rem;"></i>
                <span>Total Log: <strong><?= number_format($totalData) ?></strong></span>
            </div>
            <div class="status-chip">
                <i class="fas fa-clock" style="color: var(--muted); font-size: 0.75rem;"></i>
                <span>Update:
                    <strong><?= $latestWaktu !== '-' ? date('H:i:s', strtotime($latestWaktu)) : '-' ?></strong></span>
            </div>
        </div>

        <!-- MAIN PAGES -->
        <div class="section-label">Halaman Utama</div>
        <div class="cards-grid">
            <a href="dashboard.php" class="nav-card" style="--card-accent: #6366f1;">
                <div class="card-icon" style="background: rgba(99,102,241,0.15); color: #818cf8;">
                    <i class="fas fa-gauge-high"></i>
                </div>
                <div>
                    <div class="card-title">Dashboard Realtime</div>
                    <div class="card-desc">Pantau kondisi seluruh sensor secara langsung — Gas, Suhu, Kelembaban, dan
                        LDR. Termasuk kontrol <strong style="color:#fbbf24;">Start/Stop Recording</strong> dan indikator
                        status koneksi hardware.</div>
                </div>
                <div class="card-arrow">Buka Dashboard <i class="fas fa-arrow-right"></i></div>
            </a>

            <a href="input.php" class="nav-card" style="--card-accent: #22c55e;">
                <div class="card-icon" style="background: rgba(34,197,94,0.15); color: #4ade80;">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div>
                    <div class="card-title">Log Data Sensor</div>
                    <div class="card-desc">Riwayat lengkap seluruh data yang diterima dari NodeMCU, disertai tabel
                        paginasi dan grafik multi-sensor terpadu (Gas, Suhu, LDR).</div>
                </div>
                <div class="card-arrow">Lihat Log <i class="fas fa-arrow-right"></i></div>
            </a>

            <!-- Card: Versi 2.0 (Research Edition) -->
            <a href="versi2_0/index.php" class="nav-card" style="--card-accent: #f5139c;">
                <div class="card-icon" style="background: rgba(245, 19, 156, 0.15); color: #f472b6;">
                    <i class="fas fa-flask-vial"></i>
                </div>
                <div>
                    <div class="card-title">SensoLab V2.0 <span
                            style="font-size: 0.6rem; background: #f5139c; color: white; padding: 2px 6px; border-radius: 4px; vertical-align: middle; margin-left: 5px;">RESEARCH</span>
                    </div>
                    <div class="card-desc">Edisi validasi reliabilitas sinyal. Dilengkapi algoritma pelabelan otomatis
                        (VALID/INVALID), analisis noise RMS, dan ekspor dataset bersih untuk kebutuhan publikasi ilmiah
                        & AI.</div>
                </div>
                <div class="card-arrow" style="color: #f472b6;">Eksperimen Lanjut <i class="fas fa-arrow-right"></i>
                </div>
            </a>
        </div>

        <!-- ADMIN & DOKUMENTASI HARDWARE -->
        <div class="section-label">Administrasi &amp; Dokumentasi Hardware</div>
        <div class="cards-grid" style="grid-template-columns: 1fr 1.8fr; gap: 20px; align-items: start;">

            <!-- Kolom Kiri: Admin + Pinout -->
            <div style="display: flex; flex-direction: column; gap: 20px;">
                <!-- Card: Kosongkan Database -->
                <a href="delete_data.php" class="nav-card" style="--card-accent: #ef4444; height: auto;">
                    <div class="card-icon" style="background: rgba(239,68,68,0.15); color: #f87171;">
                        <i class="fas fa-trash-can"></i>
                    </div>
                    <div>
                        <div class="card-title">Kosongkan Database</div>
                        <div class="card-desc">Hapus seluruh riwayat data monitoring secara permanen untuk mengontrol
                            kapasitas penyimpanan. Dilindungi kata kunci konfirmasi.</div>
                    </div>
                    <div class="card-arrow" style="color: #f87171;">⚠ Area Admin <i class="fas fa-arrow-right"></i>
                    </div>
                </a>

                <!-- Card: Tabel Koneksi Pin -->
                <div class="nav-card" style="--card-accent: #f59e0b; cursor: default;">
                    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 12px;">
                        <div class="card-icon"
                            style="background: rgba(245,158,11,0.15); color: #fbbf24; width: 40px; height: 40px; font-size: 1.1rem;">
                            <i class="fas fa-plug"></i>
                        </div>
                        <div class="card-title" style="margin:0; font-size: 0.95rem;">Koneksi Pin NodeMCU 1.0</div>
                    </div>

                    <div
                        style="overflow-x: auto; overflow-y: auto; border-radius: 8px; border: 1px solid var(--border);">
                        <table
                            style="width: 100%; border-collapse: collapse; font-size: 0.7rem; text-align: left; background: rgba(15,23,42,0.4);">
                            <thead>
                                <tr style="background: rgba(245,158,11,0.1); border-bottom: 1px solid var(--border);">
                                    <th style="padding: 8px; color: #fbbf24;">Komponen</th>
                                    <th style="padding: 8px; color: #fbbf24;">Pin Sensor</th>
                                    <th style="padding: 8px; color: #fbbf24;">Pin NodeMCU</th>
                                </tr>
                            </thead>
                            <tbody style="color: #cbd5e1;">
                                <tr style="border-bottom: 1px solid var(--border);">
                                    <td style="padding: 6px 8px;">MQ-135</td>
                                    <td style="padding: 6px 8px;">A0 (Analog)</td>
                                    <td style="padding: 6px 8px;">A0</td>
                                </tr>
                                <tr style="border-bottom: 1px solid var(--border);">
                                    <td style="padding: 6px 8px;">MQ-135</td>
                                    <td style="padding: 6px 8px;">VCC</td>
                                    <td style="padding: 6px 8px;">VIN</td>
                                </tr>
                                <tr style="border-bottom: 1px solid var(--border);">
                                    <td style="padding: 6px 8px;">MQ-135</td>
                                    <td style="padding: 6px 8px;">Ground</td>
                                    <td style="padding: 6px 8px;">G (GND)</td>
                                </tr>
                                <tr style="border-bottom: 1px solid var(--border);">
                                    <td style="padding: 6px 8px;">MQ-135</td>
                                    <td style="padding: 6px 8px;">D0</td>
                                    <td style="padding: 6px 8px;">Tidak Dipakai</td>
                                </tr>
                                <tr style="border-bottom: 1px solid var(--border);">
                                    <td style="padding: 6px 8px;">LDR</td>
                                    <td style="padding: 6px 8px;">D0 (Digital)</td>
                                    <td style="padding: 6px 8px;">D1</td>
                                </tr>
                                <tr style="border-bottom: 1px solid var(--border);">
                                    <td style="padding: 6px 8px;">LDR</td>
                                    <td style="padding: 6px 8px;">VCC</td>
                                    <td style="padding: 6px 8px;">3V (3.3V)</td>
                                </tr>
                                <tr style="border-bottom: 1px solid var(--border);">
                                    <td style="padding: 6px 8px;">LDR</td>
                                    <td style="padding: 6px 8px;">Ground</td>
                                    <td style="padding: 6px 8px;">G (GND)</td>
                                </tr>
                                <tr style="border-bottom: 1px solid var(--border);">
                                    <td style="padding: 6px 8px;">LDR</td>
                                    <td style="padding: 6px 8px;">A0</td>
                                    <td style="padding: 6px 8px;">Tidak Dipakai</td>
                                </tr>
                                <tr style="border-bottom: 1px solid var(--border);">
                                    <td style="padding: 6px 8px;">DHT11</td>
                                    <td style="padding: 6px 8px;">Data (out)</td>
                                    <td style="padding: 6px 8px;">D2</td>
                                </tr>
                                <tr style="border-bottom: 1px solid var(--border);">
                                    <td style="padding: 6px 8px;">DHT11</td>
                                    <td style="padding: 6px 8px;">+ (VCC)</td>
                                    <td style="padding: 6px 8px;">3V (3.3V)</td>
                                </tr>
                                <tr style="border-bottom: 1px solid var(--border);">
                                    <td style="padding: 6px 8px;">DHT11</td>
                                    <td style="padding: 6px 8px;">- (GND)</td>
                                    <td style="padding: 6px 8px;">G (GND)</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Card: Firmware NodeMCU (.ino) -->
            <div class="nav-card"
                style="--card-accent: #06b6d4; cursor: default; display: flex; flex-direction: column; height: 100%;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div class="card-icon" style="background: rgba(6,182,212,0.15); color: #22d3ee; flex-shrink:0;">
                            <i class="fas fa-microchip"></i>
                        </div>
                        <div>
                            <div class="card-title" style="margin-bottom: 2px;">Firmware NodeMCU</div>
                            <div style="font-size: 0.72rem; color: var(--muted);">
                                <i class="fas fa-file-code" style="color:#22d3ee"></i>
                                monitoring_fix.ino &nbsp;·&nbsp;
                                <?php
                                $inoPath = __DIR__ . '/monitoring_fix.ino';
                                $inoCode = file_exists($inoPath) ? file_get_contents($inoPath) : '// File tidak ditemukan';
                                $lineCount = substr_count($inoCode, "\n") + 1;
                                echo $lineCount . ' baris';
                                ?>
                            </div>
                        </div>
                    </div>
                    <!-- Tombol copy -->
                    <button onclick="copyIno()" title="Salin kode" style="background: rgba(6,182,212,0.12); border: 1px solid rgba(6,182,212,0.3);
                           color: #22d3ee; border-radius: 8px; padding: 5px 12px;
                           font-size: 0.72rem; cursor: pointer; transition: background .2s;
                           display: flex; align-items: center; gap: 5px;"
                        onmouseover="this.style.background='rgba(6,182,212,0.25)'"
                        onmouseout="this.style.background='rgba(6,182,212,0.12)'">
                        <i class="fas fa-copy"></i> Salin
                    </button>
                </div>

                <!-- Code Block -->
                <pre id="ino-code"
                    style="background: #0d1117; border: 1px solid rgba(6,182,212,0.2);
                       border-radius: 10px; padding: 16px; margin: 0;
                       overflow-y: auto; overflow-x: auto; max-height: 520px;
                       font-family: 'JetBrains Mono', 'Courier New', monospace;
                       font-size: 0.72rem; line-height: 1.65; color: #c9d1d9;
                       white-space: pre; tab-size: 2;
                       scrollbar-width: thin; scrollbar-color: #334155 #0d1117;"><?= htmlspecialchars($inoCode) ?></pre>
            </div>

        </div>


        <!-- FOOTER -->
        <div class="footer">
            <strong>SensoLab</strong> — Platform Riset Instrumentasi IoT<br>
            Riset Instrumentasi Tahap 1 (Sensor Validation) &nbsp;|&nbsp; NodeMCU ESP8266 + MQ135 + LDR + DHT11<br>
            &copy; <?= date('Y') ?> Vanya Clianta Evelyn Pasha
        </div>

    </div>

    <script>
        setTimeout(() => location.reload(), 10000);

        function copyIno() {
            const code = document.getElementById('ino-code').innerText;
            navigator.clipboard.writeText(code).then(() => {
                const btn = event.currentTarget;
                const originalText = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-check"></i> Tersalin!';
                btn.style.background = 'rgba(34, 197, 94, 0.2)';
                btn.style.color = '#4ade80';

                setTimeout(() => {
                    btn.innerHTML = originalText;
                    btn.style.background = 'rgba(6, 182, 212, 0.12)';
                    btn.style.color = '#22d3ee';
                }, 2000);
            });
        }
    </script>
</body>

</html>