<?php
header('X-Frame-Options: SAMEORIGIN');
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SensoLab V2.0 — Real-time Monitoring & Reliability Analysis</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        body {
            background-color: #f4f7f6;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .reviewer-thinking {
            animation: bounce 2s infinite;
        }

        @keyframes bounce {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-5px);
            }
        }

        .sensor-card {
            transition: all 0.3s ease;
            border-left: 4px solid #3b82f6;
        }

        .sensor-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        .math-formula {
            font-family: 'Cambria Math', 'Times New Roman', serif;
            font-style: italic;
            font-size: 10px;
            color: #94a3b8;
            margin-top: 4px;
            padding-top: 4px;
            border-top: 1px dashed #e2e8f0;
            display: block;
        }

        .math-val {
            color: #64748b;
            font-weight: bold;
            font-style: normal;
        }

        /* Pulse for Online Status */
        .pulse-green {
            animation: pulse-green 2s infinite;
        }

        @keyframes pulse-green {
            0% {
                transform: scale(0.95);
                box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7);
            }

            70% {
                transform: scale(1);
                box-shadow: 0 0 0 10px rgba(34, 197, 94, 0);
            }

            100% {
                transform: scale(0.95);
                box-shadow: 0 0 0 0 rgba(34, 197, 94, 0);
            }
        }

        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }
    </style>
</head>

<body class="flex flex-col h-screen overflow-hidden">

    <!-- Navbar -->
    <nav class="bg-slate-900 text-white shadow-lg z-20 relative">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center">
                    <div class="bg-blue-600 p-2 rounded-lg mr-3 shadow-lg">
                        <i class="fas fa-microchip text-white text-xl"></i>
                    </div>
                    <div class="flex flex-col leading-tight">
                        <span class="font-bold text-lg tracking-wide">SensoLab <span
                                class="text-blue-400">V2.0</span></span>
                        <span class="text-[10px] text-slate-400 font-medium tracking-wider">INTELLIGENT MONITORING
                            SYSTEM</span>
                    </div>
                </div>

                <div class="flex items-center space-x-6">
                    <!-- Status Badge: Hardware -->
                    <div id="hw-status-badge"
                        class="flex items-center bg-slate-800 px-3 py-1.5 rounded-full border border-slate-700">
                        <div id="hw-dot" class="w-2 h-2 rounded-full mr-2 bg-gray-500"></div>
                        <span class="text-[10px] font-bold uppercase tracking-widest text-slate-300">HW: <span
                                id="hw-text">Checking...</span></span>
                    </div>

                    <!-- Status Badge: Recording (Interactive) -->
                    <button id="rec-status-badge" onclick="toggleRecording()"
                        class="group flex items-center bg-slate-800 hover:bg-slate-700 px-4 py-1.5 rounded-full border border-slate-700 transition active:scale-95">
                        <div id="rec-dot"
                            class="w-2.5 h-2.5 rounded-full mr-2 bg-gray-500 shadow-[0_0_8px_rgba(0,0,0,0.5)]"></div>
                        <span
                            class="text-[10px] font-bold uppercase tracking-widest text-slate-300 group-hover:text-white transition">REC:
                            <span id="rec-text">OFF</span></span>
                        <i class="fas fa-power-off ml-2 text-[10px] text-slate-500 group-hover:text-red-400"></i>
                    </button>

                    <a href="teori.php"
                        class="text-slate-400 hover:text-white transition flex items-center gap-2 px-3 py-1 rounded bg-slate-800 border border-slate-700">
                        <i class="fas fa-book-open text-xs"></i>
                        <span class="text-[10px] font-bold uppercase tracking-wider">Landasan Teori</span>
                    </a>
                    <a href="alur_kerja.php"
                        class="text-slate-400 hover:text-white transition flex items-center gap-2 px-3 py-1 rounded bg-slate-800 border border-slate-700">
                        <i class="fas fa-project-diagram text-xs"></i>
                        <span class="text-[10px] font-bold uppercase tracking-wider">Alur Kerja</span>
                    </a>
                    <a href="../index.php" class="text-slate-400 hover:text-white transition">
                        <i class="fas fa-home"></i>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="flex-1 overflow-y-auto p-4 md:p-6 bg-slate-50">
        <div class="max-w-7xl mx-auto min-h-full flex flex-col">

            <!-- Header Section -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-6">
                <div class="lg:col-span-8 bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
                    <div class="flex justify-between items-start">
                        <div>
                            <h1 class="text-2xl font-bold text-slate-800 flex items-center gap-2">
                                <i class="fas fa-vial-circle-check text-blue-500"></i>
                                Evaluasi Reliabilitas Multi-Sensor
                            </h1>
                            <p class="text-slate-500 text-sm mt-1">Implementasi nyata dari validasi statistik untuk data
                                sensor IoT sebelum diolah oleh Kecerdasan Buatan.</p>
                        </div>
                        <div class="text-right">
                            <span class="text-[10px] font-bold text-slate-400 block uppercase tracking-tighter">Peneliti
                                Utama</span>
                            <span class="text-sm font-bold text-slate-700">Vanya Clianta Evelyn Pasha</span>
                        </div>
                    </div>

                    <!-- Roadmap Riset (Visual Guide for Reviewer) -->
                    <div
                        class="flex items-center justify-between text-[10px] md:text-xs font-semibold text-slate-400 mt-8 relative px-2">
                        <div class="absolute w-full h-[2px] bg-slate-100 top-1/2 transform -translate-y-1/2 z-0 left-0">
                        </div>

                        <div class="z-10 flex flex-col items-center">
                            <div
                                class="w-7 h-7 rounded-full bg-blue-600 text-white flex items-center justify-center border-4 border-blue-100 shadow-md ring-2 ring-blue-500/20">
                                1</div>
                            <span class="mt-2 text-blue-600 font-bold bg-white px-2">Sensor Validation</span>
                            <span class="text-[9px] text-blue-400">(Fokus Riset Ini)</span>
                        </div>

                        <div class="z-10 flex flex-col items-center opacity-40">
                            <div
                                class="w-7 h-7 rounded-full bg-slate-200 text-slate-500 flex items-center justify-center border-4 border-white">
                                2</div>
                            <span class="mt-2 bg-white px-2">IoT Integration</span>
                            <span class="text-[9px] text-slate-400">Wi-Fi & Cloud Sync</span>
                        </div>

                        <div class="z-10 flex flex-col items-center opacity-40">
                            <div
                                class="w-7 h-7 rounded-full bg-slate-200 text-slate-500 flex items-center justify-center border-4 border-white">
                                3</div>
                            <span class="mt-2 bg-white px-2">Data Intelligence</span>
                            <span class="text-[9px] text-slate-400">Big Data Analysis</span>
                        </div>

                        <div class="z-10 flex flex-col items-center opacity-40">
                            <div
                                class="w-7 h-7 rounded-full bg-slate-200 text-slate-500 flex items-center justify-center border-4 border-white">
                                4</div>
                            <span class="mt-2 bg-white px-2 text-center">AI System</span>
                            <span class="text-[9px] text-slate-400">Decision Making</span>
                        </div>
                    </div>
                </div>

                <div
                    class="lg:col-span-4 bg-gradient-to-br from-blue-600 to-indigo-700 p-6 rounded-2xl shadow-lg text-white">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-bold uppercase tracking-widest opacity-80">Waktu Server</span>
                        <i class="fas fa-clock opacity-50"></i>
                    </div>
                    <div id="server-time" class="text-3xl font-mono font-bold">00:00:00</div>
                    <div class="text-[10px] opacity-70 mt-1 uppercase tracking-widest">Update terakhir: <span
                            id="last-update">-</span></div>
                </div>
            </div>

            <!-- Dashboard Grid -->
            <div class="flex-1 grid grid-cols-1 lg:grid-cols-12 gap-6 min-h-[500px]">

                <!-- Left: Live Meters & Selector -->
                <div class="lg:col-span-3 bg-white p-5 rounded-2xl shadow-sm border border-slate-200 flex flex-col">
                    <h2
                        class="text-sm font-bold text-slate-700 uppercase tracking-wider border-b pb-3 mb-4 flex items-center justify-between">
                        <span><i class="fas fa-satellite-dish mr-2 text-blue-500"></i>Data Langsung</span>
                        <span id="sync-indicator"
                            class="text-[8px] px-1.5 py-0.5 rounded bg-blue-50 text-blue-500 border border-blue-100 italic transition-opacity opacity-0">SYNCING</span>
                    </h2>

                    <div class="space-y-3 mb-6">
                        <!-- Sensor Cards -->
                        <div id="card-temp" onclick="setActiveSensor('temp')"
                            class="sensor-card bg-slate-50 p-3 rounded-xl border border-slate-100 cursor-pointer hover:bg-blue-50"
                            style="border-left-color: #3b82f6;">
                            <div class="flex justify-between items-center">
                                <span class="text-xs font-bold text-slate-600">Temperatur (DHT11)</span>
                                <i class="fas fa-temperature-half text-blue-400"></i>
                            </div>
                            <div class="text-xl font-bold text-slate-800 mt-1" id="live-temp">0.0°C</div>
                        </div>

                        <div id="card-hum" onclick="setActiveSensor('hum')"
                            class="sensor-card bg-slate-50 p-3 rounded-xl border border-slate-100 cursor-pointer hover:bg-emerald-50"
                            style="border-left-color: #10b981;">
                            <div class="flex justify-between items-center">
                                <span class="text-xs font-bold text-slate-600">Kelembaban (DHT11)</span>
                                <i class="fas fa-droplet text-emerald-400"></i>
                            </div>
                            <div class="text-xl font-bold text-slate-800 mt-1" id="live-hum">0.0%</div>
                        </div>

                        <div id="card-gas" onclick="setActiveSensor('gas')"
                            class="sensor-card bg-slate-50 p-3 rounded-xl border border-slate-100 cursor-pointer hover:bg-purple-50"
                            style="border-left-color: #8b5cf6;">
                            <div class="flex justify-between items-center">
                                <span class="text-xs font-bold text-slate-600">Gas MQ-135 (Raw)</span>
                                <i class="fas fa-cloud text-purple-400"></i>
                            </div>
                            <div class="text-xl font-bold text-slate-800 mt-1" id="live-gas">0 PPM</div>
                        </div>

                        <div id="card-light" onclick="setActiveSensor('light')"
                            class="sensor-card bg-slate-50 p-3 rounded-xl border border-slate-100 cursor-pointer hover:bg-amber-50"
                            style="border-left-color: #f59e0b;">
                            <div class="flex justify-between items-center">
                                <span class="text-xs font-bold text-slate-600">Cahaya (LDR)</span>
                                <i class="fas fa-sun text-amber-400"></i>
                            </div>
                            <div class="text-xl font-bold text-slate-800 mt-1" id="live-light">-</div>
                        </div>
                    </div>

                    <!-- Summary Card: Total Data -->
                    <div class="mb-4 p-4 bg-emerald-50 rounded-xl border border-emerald-100 shadow-sm">
                        <div class="flex items-center gap-3">
                            <div
                                class="w-10 h-10 bg-white rounded-lg flex items-center justify-center text-emerald-600 shadow-sm border border-emerald-100">
                                <i class="fas fa-database text-lg"></i>
                            </div>
                            <div>
                                <div class="text-[10px] font-bold text-emerald-700 uppercase tracking-wider">Total Data
                                    Terbaca</div>
                                <div id="summary-total-all" class="text-xl font-bold text-slate-800 leading-none">0
                                </div>
                                <div class="text-[9px] text-emerald-600 font-bold mt-1">Hari ini: <span
                                        id="summary-total-today">0</span> Log</div>
                            </div>
                        </div>
                    </div>

                    <!-- Summary Card: Total Invalid -->
                    <div class="mb-4 p-4 bg-red-50 rounded-xl border border-red-100 shadow-sm">
                        <div class="flex items-center gap-3">
                            <div
                                class="w-10 h-10 bg-white rounded-lg flex items-center justify-center text-red-600 shadow-sm border border-red-100">
                                <i class="fas fa-exclamation-triangle text-lg"></i>
                            </div>
                            <div>
                                <div class="text-[10px] font-bold text-red-700 uppercase tracking-wider flex items-center gap-1">
                                    <span>Dataset Tidak Valid</span>
                                    <i class="fas fa-circle-question opacity-40 hover:opacity-100 cursor-help text-[9px]" title="Dataset Tidak Valid (Invalid): Kumpulan data yang terdeteksi memiliki fluktuasi ekstrem atau noise sinyal di atas ambang batas toleransi riset (Software Noise Filtering)."></i>
                                </div>
                                <div id="summary-total-invalid" class="text-xl font-bold text-slate-800 leading-none">0
                                </div>
                                <div class="text-[9px] text-red-500 font-bold mt-1">Noise Terdeteksi</div>
                            </div>
                        </div>
                    </div>

                    <div
                        class="mt-auto p-4 bg-slate-800 rounded-xl text-white text-xs text-center border-t-4 border-blue-500 shadow-inner">
                        <i class="fas fa-info-circle text-blue-400 mb-2 text-base"></i>
                        <p class="leading-relaxed opacity-80">Klik pada kartu sensor di atas untuk menganalisis
                            reliabilitas datanya secara terperinci.</p>
                    </div>
                </div>

                <!-- Center: Analytics Engine & Chart -->
                <div
                    class="lg:col-span-6 bg-white p-5 rounded-2xl shadow-sm border border-slate-200 flex flex-col relative overflow-hidden">
                    <h2
                        class="text-sm font-bold text-slate-700 uppercase tracking-wider border-b pb-3 mb-4 z-10 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <span><i class="fas fa-chart-line mr-2 text-indigo-500"></i>Analisis Kualitas Sinyal: <span
                                    id="active-sensor-name" class="text-indigo-600">Temperatur</span></span>
                        </div>
                        <span id="system-status-pill"
                            class="text-[10px] bg-slate-100 text-slate-500 px-3 py-1 rounded-full font-bold">Inisialisasi...</span>
                    </h2>

                    <!-- Reviewer Dialogue (AI Logic Proxy) -->
                    <div class="flex items-center bg-slate-50 p-4 rounded-2xl border border-slate-100 mb-6 z-10">
                        <div
                            class="w-12 h-12 bg-white rounded-full flex items-center justify-center mr-4 shadow-sm border-2 border-slate-200 reviewer-thinking flex-shrink-0">
                            <i class="fas fa-robot text-indigo-500 text-xl"></i>
                        </div>
                        <div class="flex-1">
                            <div class="bg-white p-3 rounded-xl border border-slate-200 shadow-sm relative">
                                <div
                                    class="absolute w-3 h-3 bg-white border-l border-b border-slate-200 transform rotate-45 -left-1.5 top-5">
                                </div>
                                <p class="text-xs text-slate-700 font-medium leading-relaxed" id="ai-dialogue">
                                    "Menghubungkan ke API... Menunggu aliran data nyata dari sensor untuk menghitung
                                    karakteristik statistik."
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Chart Container -->
                    <div class="flex-1 min-h-[300px] z-10">
                        <canvas id="mainChart"></canvas>
                    </div>

                    <!-- Legend Insight -->
                    <div class="mt-4 grid grid-cols-2 gap-4 z-10">
                        <div class="bg-indigo-50/50 p-3 rounded-xl border border-indigo-100">
                            <h4
                                class="text-[10px] font-bold text-indigo-700 uppercase tracking-wider mb-1 flex items-center gap-1">
                                <i class="fas fa-layer-group"></i> Confidence Band (±1σ)
                            </h4>
                            <p class="text-[9px] text-slate-600">Area ungu transparan menunjukkan batas toleransi
                                normal. Data di luar area ini dianggap sebagai fluktuasi ekstrem atau noise.</p>
                        </div>
                        <div class="bg-indigo-50/50 p-3 rounded-xl border border-indigo-100">
                            <h4
                                class="text-[10px] font-bold text-indigo-700 uppercase tracking-wider mb-1 flex items-center gap-1">
                                <i class="fas fa-bullseye"></i> Outlier Marking
                            </h4>
                            <p class="text-[9px] text-slate-600">Titik merah otomatis muncul jika pembacaan menyimpang
                                >1.5σ dari rata-rata grup data saat ini.</p>
                        </div>
                    </div>
                </div>

                <!-- Right: Metrics & Formulas -->
                <div class="lg:col-span-3 bg-white p-5 rounded-2xl shadow-sm border border-slate-200 flex flex-col">
                    <h2 class="text-sm font-bold text-slate-700 uppercase tracking-wider border-b pb-3 mb-4"><i
                            class="fas fa-calculator mr-2 text-indigo-500"></i>Metrik Evaluasi</h2>

                    <div class="space-y-4 overflow-y-auto custom-scrollbar flex-1 pr-1 pb-4">

                        <!-- Metric: Mean/SD -->
                        <div class="bg-slate-50 p-3 rounded-xl border border-slate-100">
                            <div class="flex justify-between items-center mb-1">
                                <span class="text-[11px] font-bold text-slate-500 uppercase">Mean (μ) | SD (σ)</span>
                                <span class="font-mono font-bold text-blue-600 text-xs"><span id="m-mean">0.0</span> |
                                    <span id="m-sd">0.00</span></span>
                            </div>
                            <div class="w-full bg-slate-200 rounded-full h-1 mt-2">
                                <div id="bar-sd" class="bg-blue-500 h-1 rounded-full transition-all duration-500"
                                    style="width: 20%"></div>
                            </div>
                            <span class="math-formula">
                                μ = Σx/n = <span id="f-sum-x">0</span>/<span id="f-n-1">50</span> = <span id="f-mean"
                                    class="math-val">...</span><br>
                                σ = √[Σ(x-μ)²/n] = √[<span id="f-sum-diff">0</span>/<span id="f-n-2">50</span>] = <span
                                    id="f-std" class="math-val">...</span>
                            </span>
                        </div>

                        <!-- Metric: Variance -->
                        <div class="bg-slate-50 p-3 rounded-xl border border-slate-100">
                            <div class="flex justify-between items-center mb-1">
                                <span class="text-[11px] font-bold text-slate-500 uppercase">Variansi (σ²)</span>
                                <span id="m-var" class="font-mono font-bold text-indigo-600 text-xs">0.000</span>
                            </div>
                            <span class="math-formula">
                                σ² = <span id="f-std-sq">0.00</span>² = <span id="f-var" class="math-val">...</span>
                            </span>
                        </div>

                        <!-- Metric: Stability -->
                        <div class="bg-slate-50 p-3 rounded-xl border border-slate-100">
                            <div class="flex justify-between items-center mb-1">
                                <span class="text-[11px] font-bold text-slate-500 uppercase">Stabilitas</span>
                                <span id="m-stab" class="font-mono font-bold text-emerald-600 text-xs">100%</span>
                            </div>
                            <div class="w-full bg-slate-200 rounded-full h-1 mt-2">
                                <div id="bar-stab" class="bg-emerald-500 h-1 rounded-full transition-all duration-500"
                                    style="width: 100%"></div>
                            </div>
                            <span class="math-formula">
                                CV = σ/μ = <span id="f-stab-cv">0.00</span><br>
                                S = 100% - (CV × 100%) = <span id="f-stab" class="math-val">...</span>
                            </span>
                        </div>

                        <!-- Metric: Repeatability -->
                        <div class="bg-white p-3 rounded-xl border-2 border-slate-100">
                            <span
                                class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-1">Klasifikasi
                                Repeatability</span>
                            <div id="m-repeat" class="text-sm font-bold text-slate-800">Menilai...</div>
                            <div class="flex gap-1 mt-2">
                                <div id="r-st" class="h-1 flex-1 bg-emerald-500 rounded-full opacity-20"
                                    title="Sangat Tinggi (σ < 0.5)"></div>
                                <div id="r-t" class="h-1 flex-1 bg-blue-500 rounded-full opacity-20"
                                    title="Tinggi (σ 0.5 - 1.0)"></div>
                                <div id="r-s" class="h-1 flex-1 bg-amber-500 rounded-full opacity-20"
                                    title="Sedang (σ 1.0 - 2.0)"></div>
                                <div id="r-r" class="h-1 flex-1 bg-red-500 rounded-full opacity-20"
                                    title="Rendah (σ > 2.0)"></div>
                            </div>
                            <span class="math-formula">
                                RMS_{noise} = √[1/n Σ(x-μ)²]<br>
                                RMS ≈ <span id="f-rms-std">0.00</span> × 0.707 = <span id="f-noise"
                                    class="math-val">...</span>
                            </span>
                        </div>

                    </div>

                    <!-- Dataset Management Section -->
                    <div class="mt-4 bg-white p-4 rounded-2xl shadow-sm border border-slate-200 flex flex-col">
                        <h2
                            class="text-xs font-bold text-slate-700 uppercase tracking-wider border-b pb-2 mb-3 flex justify-between items-center">
                            <span><i class="fas fa-database mr-2 text-blue-500"></i>Dataset AI Ready</span>
                            <span id="label-spin" class="hidden"><i
                                    class="fas fa-sync fa-spin text-blue-500 text-[10px]"></i></span>
                        </h2>

                        <div class="grid grid-cols-2 gap-2 mb-3 text-center">
                            <div class="bg-slate-50 py-1.5 rounded-xl border border-slate-100 shadow-sm">
                                <div class="text-[9px] text-slate-400 font-bold uppercase flex items-center justify-center gap-1">
                                    Valid
                                    <i class="fas fa-circle-question opacity-60 cursor-help" title="Valid Dataset: Data yang telah melewati tahap validasi statistik dan dinyatakan memiliki stabilitas yang baik (low noise). Data inilah yang akan digunakan untuk pemodelan AI."></i>
                                </div>
                                <div id="ds-valid" class="text-sm font-bold text-emerald-600">0</div>
                            </div>
                            <div class="bg-slate-100/50 py-1.5 rounded-xl border border-slate-100 shadow-sm">
                                <div class="text-[9px] text-slate-400 font-bold uppercase flex items-center justify-center gap-1">
                                    Pending
                                    <i class="fas fa-circle-question opacity-60 cursor-help" title="Pending Dataset: Data mentah yang baru masuk dan belum diproses oleh mesin pelabelan otomatis. Anda perlu menekan tombol 'Proses Labeling' untuk memvalidasinya."></i>
                                </div>
                                <div id="ds-pending" class="text-sm font-bold text-amber-500">0</div>
                            </div>
                        </div>

                        <div class="space-y-2">
                        <div class="flex items-center gap-2">
                            <button onclick="runLabeling()"
                                class="flex-1 py-1.5 bg-slate-50 hover:bg-blue-600 hover:text-white text-slate-600 text-[9px] font-bold uppercase tracking-widest rounded-lg transition-all border border-slate-200 flex items-center justify-center gap-2">
                                <i class="fas fa-tags text-[10px]"></i> Proses Labeling
                            </button>
                            <button title="Fungsi Labelling: Menjalankan algoritma pembersihan dataset secara otomatis. Sistem akan memindai riwayat data dan memberikan label 'VALID' pada data yang stabil (berkualitas) serta 'INVALID' pada data yang terdeteksi sebagai noise/anomali. Ini sangat penting untuk menghasilkan dataset yang siap digunakan untuk pelatihan Machine Learning atau publikasi ilmiah."
                                class="w-8 py-1.5 bg-slate-50 text-slate-400 hover:text-blue-500 rounded-lg border border-slate-200 transition-colors flex items-center justify-center">
                                <i class="fas fa-circle-question text-xs"></i>
                            </button>
                        </div>
                            <a href="export_dataset.php" download="Sensolab_Dataset.csv"
                                class="w-full py-2 bg-blue-600 hover:bg-blue-700 text-white text-[9px] font-bold uppercase tracking-widest rounded-lg transition-all shadow-md shadow-blue-200 flex items-center justify-center gap-2">
                                <i class="fas fa-file-csv text-xs"></i> Export Valid Dataset (CSV)
                            </a>
                        </div>
                    </div>

                    <!-- Final Logic Output -->
                    <div id="decision-box"
                        class="mt-4 bg-slate-900 p-4 rounded-2xl text-center border-t-2 border-slate-700 transition-all duration-500">
                        <i id="decision-icon" class="fas fa-shield-halved text-3xl text-slate-500 mb-2"></i>
                        <h3 id="decision-title" class="font-bold text-white text-base">Menganalisis...</h3>
                        <p id="decision-text" class="text-[10px] text-slate-400 mt-1 uppercase tracking-widest">VALIDASI
                            DATA SEDANG BERJALAN</p>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <footer
                class="mt-12 pt-6 border-t border-slate-200 flex flex-col md:flex-row justify-between items-center gap-4 text-[10px] text-slate-400 font-bold uppercase tracking-widest pb-8">
                <div class="flex items-center gap-2">
                    <i class="fas fa-copyright"></i> 2026 SensoLab Research Project
                </div>
                <div class="flex items-center gap-3">
                    <span class="opacity-60">Research Status:</span>
                    <div class="bg-slate-200 px-4 py-1.5 rounded-full text-slate-600 border border-slate-300 shadow-sm">
                        Phase: Reliability Mapping
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <script>
        // Global State
        let activeSensor = 'temp';
        let config = {
            temp: { name: 'Temperatur', unit: '°C', threshold: 0.5, color: '#3b82f6' },
            hum: { name: 'Kelembaban', unit: '%', threshold: 1.0, color: '#10b981' },
            gas: { name: 'Gas MQ-135', unit: 'PPM', threshold: 20, color: '#8b5cf6' },
            light: { name: 'Cahaya (LDR)', unit: 'Lux', threshold: 50, color: '#f59e0b' }
        };

        // Chart Initialization
        const ctx = document.getElementById('mainChart').getContext('2d');
        const mainChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: [],
                datasets: [
                    {
                        label: 'Data Aktual',
                        data: [],
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59, 130, 246, 0.05)',
                        borderWidth: 3,
                        tension: 0.3,
                        fill: false,
                        pointRadius: 0,
                        pointBackgroundColor: '#ef4444'
                    },
                    {
                        label: 'Mean (μ)',
                        data: [],
                        borderColor: '#64748b',
                        borderWidth: 1.5,
                        borderDash: [5, 5],
                        fill: false,
                        pointRadius: 0
                    },
                    {
                        label: 'Batas Atas (μ+σ)',
                        data: [],
                        borderColor: 'transparent',
                        backgroundColor: 'rgba(124, 58, 237, 0.08)',
                        fill: 3,
                        pointRadius: 0,
                        tension: 0.3
                    },
                    {
                        label: 'Batas Bawah (μ-σ)',
                        data: [],
                        borderColor: 'transparent',
                        pointRadius: 0,
                        tension: 0.3,
                        fill: false
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: { duration: 400 },
                interaction: { intersect: false, mode: 'index' },
                scales: {
                    x: { display: false },
                    y: { grid: { color: '#f1f5f9' }, ticks: { font: { size: 10 } } }
                },
                plugins: {
                    legend: { display: false }
                }
            }
        });

        // Functions
        function setActiveSensor(id) {
            activeSensor = id;

            // Update UI Active State
            document.querySelectorAll('.sensor-card').forEach(c => c.classList.remove('bg-blue-50', 'bg-emerald-50', 'bg-purple-50', 'bg-amber-50', 'ring-2', 'ring-blue-500'));

            let card = document.getElementById(`card-${id}`);
            let c = config[id];

            if (id === 'temp') card.classList.add('bg-blue-50', 'ring-2', 'ring-blue-500');
            if (id === 'hum') card.classList.add('bg-emerald-50', 'ring-2', 'ring-emerald-500');
            if (id === 'gas') card.classList.add('bg-purple-50', 'ring-2', 'ring-purple-500');
            if (id === 'light') card.classList.add('bg-amber-50', 'ring-2', 'ring-amber-500');

            document.getElementById('active-sensor-name').innerText = c.name;
            document.getElementById('active-sensor-name').style.color = c.color;

            mainChart.data.datasets[0].borderColor = c.color;
            mainChart.update('none');

            fetchData(); // Immediate refresh
        }

        async function fetchData() {
            const sync = document.getElementById('sync-indicator');
            sync.style.opacity = '1';

            try {
                const response = await fetch('api_v2.php');
                const data = await response.json();

                if (data.error) throw new Error(data.error);

                updateUI(data);
                updateChart(data);

            } catch (err) {
                console.error("Fetch Error:", err);
                document.getElementById('ai-dialogue').innerText = "Terjadi gangguan koneksi ke server. Memulihkan...";
            } finally {
                setTimeout(() => { sync.style.opacity = '0'; }, 500);
            }
        }

        async function toggleRecording() {
            const btn = document.getElementById('rec-status-badge');
            btn.style.opacity = '0.7';
            btn.disabled = true;

            try {
                const formData = new FormData();
                formData.append('action', 'toggle_recording');

                const response = await fetch('api_control.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();

                if (result.success) {
                    await fetchData(); // Refresh UI immediate
                } else {
                    alert("Gagal merubah status: " + result.error);
                }
            } catch (err) {
                console.error("Control Error:", err);
                alert("Gangguan koneksi ke pengontrol.");
            } finally {
                btn.style.opacity = '1';
                btn.disabled = false;
            }
        }

        async function updateDatasetStats() {
            try {
                const response = await fetch('api_dataset_stats.php');
                const stats = await response.json();
                document.getElementById('ds-valid').innerText = stats.total_valid;
                document.getElementById('ds-pending').innerText = stats.pending;
            } catch (err) { console.error("Stats Error"); }
        }

        async function runLabeling() {
            const spin = document.getElementById('label-spin');
            spin.classList.remove('hidden');
            try {
                const response = await fetch('api_process_labels.php');
                const res = await response.json();
                if (res.success) {
                    await updateDatasetStats();
                }
            } catch (err) { alert("Gagal memproses dataset"); }
            finally { spin.classList.add('hidden'); }
        }

        function updateUI(data) {
            const latest = data.latest;
            if (!latest) return;

            // Header Status
            document.getElementById('server-time').innerText = data.status.server_time;
            document.getElementById('last-update').innerText = data.status.last_update;
            document.getElementById('summary-total-all').innerText = data.status.total_all.toLocaleString();
            document.getElementById('summary-total-today').innerText = data.status.total_today.toLocaleString();
            document.getElementById('summary-total-invalid').innerText = data.status.total_invalid.toLocaleString();

            // Hardware Status
            const hwText = document.getElementById('hw-text');
            const hwDot = document.getElementById('hw-dot');
            if (data.status.is_online) {
                hwText.innerText = "ONLINE";
                hwText.className = "text-emerald-400";
                hwDot.className = "w-2 h-2 rounded-full mr-2 bg-emerald-500 pulse-green";
            } else {
                hwText.innerText = "OFFLINE";
                hwText.className = "text-red-400";
                hwDot.className = "w-2 h-2 rounded-full mr-2 bg-red-500";
            }

            // Recording Status
            const recText = document.getElementById('rec-text');
            const recDot = document.getElementById('rec-dot');
            if (data.status.is_recording) {
                recText.innerText = "AKTIF";
                recText.className = "text-blue-400";
                recDot.className = "w-2 h-2 rounded-full mr-2 bg-blue-500 pulse-green";
            } else {
                recText.innerText = "STOP";
                recText.className = "text-slate-400";
                recDot.className = "w-2 h-2 rounded-full mr-2 bg-slate-500";
            }

            // Live Values
            document.getElementById('live-temp').innerText = latest.suhu.toFixed(1) + '°C';
            document.getElementById('live-hum').innerText = latest.kelembaban.toFixed(1) + '%';
            document.getElementById('live-gas').innerText = latest.gas + ' PPM';
            document.getElementById('live-light').innerText = latest.ldr_raw;
        }

        function updateChart(data) {
            const history = data.history;
            if (history.length === 0) return;

            const c = config[activeSensor];

            // Perbaikan Pemetaan Kunci API (Suhu & Kelembaban)
            let sensorKey = activeSensor;
            if (activeSensor === 'temp') sensorKey = 'suhu';
            if (activeSensor === 'hum') sensorKey = 'kelembaban';
            if (activeSensor === 'light') sensorKey = 'ldr_num';

            const rawData = history.map(h => h[sensorKey]);
            const labels = history.map(h => h.waktu);

            // Re-calculate Statistics for current buffer
            const count = rawData.length;
            const mean = rawData.reduce((a, b) => a + b, 0) / count;
            const variance = rawData.reduce((a, b) => a + Math.pow(b - mean, 2), 0) / count;
            const stdDev = Math.sqrt(variance);

            // Update Metrics Sidebar
            document.getElementById('m-mean').innerText = mean.toFixed(1);
            document.getElementById('m-sd').innerText = stdDev.toFixed(2);
            document.getElementById('m-var').innerText = variance.toFixed(3);

            const cv = mean !== 0 ? (stdDev / mean) : 0;
            const stability = Math.max(0, 100 - (cv * 100));
            document.getElementById('m-stab').innerText = stability.toFixed(1) + '%';
            document.getElementById('bar-stab').style.width = stability + '%';
            document.getElementById('bar-sd').style.width = Math.min((stdDev / (c.threshold * 4)) * 100, 100) + '%';

            // Repeatability Classifier
            const rubrics = ['r-st', 'r-t', 'r-s', 'r-r'];
            rubrics.forEach(r => document.getElementById(r).classList.replace('opacity-100', 'opacity-20'));
            const rText = document.getElementById('m-repeat');

            if (stdDev < c.threshold) {
                rText.innerText = "Sangat Tinggi"; rText.className = "text-sm font-bold text-emerald-600";
                document.getElementById('r-st').classList.replace('opacity-20', 'opacity-100');
            } else if (stdDev < c.threshold * 2) {
                rText.innerText = "Tinggi"; rText.className = "text-sm font-bold text-blue-600";
                document.getElementById('r-t').classList.replace('opacity-20', 'opacity-100');
            } else if (stdDev < c.threshold * 4) {
                rText.innerText = "Sedang"; rText.className = "text-sm font-bold text-amber-600";
                document.getElementById('r-s').classList.replace('opacity-20', 'opacity-100');
            } else {
                rText.innerText = "Rendah"; rText.className = "text-sm font-bold text-red-600";
                document.getElementById('r-r').classList.replace('opacity-20', 'opacity-100');
            }

            // AI/Decision Logic
            const decBox = document.getElementById('decision-box');
            const decIcon = document.getElementById('decision-icon');
            const decTitle = document.getElementById('decision-title');
            const decText = document.getElementById('decision-text');
            const aiDia = document.getElementById('ai-dialogue');
            const pill = document.getElementById('system-status-pill');

            if (stdDev > c.threshold * 2 || stability < 95) {
                decBox.className = "mt-4 bg-red-950 p-4 rounded-2xl text-center border-t-2 border-red-500 shadow-lg ring-1 ring-red-500/50";
                decIcon.className = "fas fa-triangle-exclamation text-3xl text-red-500 mb-2";
                decTitle.innerText = "DATASET INVALID";
                decTitle.className = "font-bold text-red-400 text-base italic";
                decText.innerText = "SIMPANGAN DATA MELAMPAUI BATAS";
                aiDia.innerHTML = `<span class="text-red-600 font-bold">Waspada!</span> Terdeteksi fluktuasi abnormal pada ${c.name}. Dataset saat ini tidak disarankan untuk training AI.`;
                pill.innerText = "NOISE TERDETEKSI";
                pill.className = "text-[10px] bg-red-100 text-red-600 px-3 py-1 rounded-full font-bold animate-pulse";
            } else {
                decBox.className = "mt-4 bg-slate-900 p-4 rounded-2xl text-center border-t-2 border-slate-700";
                decIcon.className = "fas fa-certificate text-3xl text-emerald-500 mb-2";
                decTitle.innerText = "DATASET VALID";
                decTitle.className = "font-bold text-emerald-400 text-base";
                decText.innerText = "KARAKTERISTIK SINYAL OPTIMAL";
                aiDia.innerHTML = `Sistem memvalidasi aliran data ${c.name}. <span class="text-emerald-600 font-bold">Kondisi stabil.</span> Data sangat layak untuk integrasi database AI.`;
                pill.innerText = "SISTEM STABIL";
                pill.className = "text-[10px] bg-emerald-100 text-emerald-600 px-3 py-1 rounded-full font-bold";
            }

            // Update Chart Data
            mainChart.data.labels = labels;
            mainChart.data.datasets[0].data = rawData;
            mainChart.data.datasets[1].data = Array(count).fill(mean);
            mainChart.data.datasets[2].data = Array(count).fill(mean + stdDev);
            mainChart.data.datasets[3].data = Array(count).fill(mean - stdDev);

            // Highlight Outliers in Chart
            mainChart.data.datasets[0].pointRadius = rawData.map(v => Math.abs(v - mean) > 1.5 * stdDev ? 5 : 0);

            // Update Details Formula (Real-time Implementation)
            const sumX = rawData.reduce((a, b) => a + b, 0);
            const sumDiffSq = rawData.reduce((a, b) => a + Math.pow(b - mean, 2), 0);
            const noiseRMS = stdDev * 0.707;

            document.getElementById('f-sum-x').innerText = Math.round(sumX);
            document.getElementById('f-n-1').innerText = count;
            document.getElementById('f-mean').innerText = mean.toFixed(1);
            document.getElementById('f-sum-diff').innerText = sumDiffSq.toFixed(2);
            document.getElementById('f-n-2').innerText = count;
            document.getElementById('f-std').innerText = stdDev.toFixed(2);

            document.getElementById('f-std-sq').innerText = stdDev.toFixed(2);
            document.getElementById('f-var').innerText = variance.toFixed(3);

            document.getElementById('f-stab-cv').innerText = cv.toFixed(3);
            document.getElementById('f-stab').innerText = stability.toFixed(1) + '%';

            document.getElementById('f-rms-std').innerText = stdDev.toFixed(2);
            document.getElementById('f-noise').innerText = noiseRMS.toFixed(2);

            mainChart.update('none');
        }

        // Init
        setActiveSensor('temp');
        updateDatasetStats();
        setInterval(() => {
            fetchData();
            updateDatasetStats();
        }, 3000); 
    </script>
</body>

</html>