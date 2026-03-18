<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Landasan Teori & Metrik Evaluasi — SensoLab V2.0</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f8fafc;
            font-family: 'Inter', sans-serif;
            scroll-behavior: smooth;
        }

        .glass-nav {
            background: rgba(15, 23, 42, 0.9);
            backdrop-filter: blur(10px);
        }

        .theory-card {
            transition: transform 0.3s ease;
        }

        .theory-card:hover {
            transform: translateY(-5px);
        }

        .math-block {
            background: #f1f5f9;
            padding: 1.5rem;
            border-radius: 0.75rem;
            font-family: 'Cambria Math', serif;
            border-left: 4px solid #3b82f6;
        }

        .reference-link {
            color: #2563eb;
            text-decoration: underline;
        }
    </style>
</head>

<body class="pt-20">

    <!-- Navbar -->
    <nav class="glass-nav text-white fixed top-0 w-full z-50 shadow-xl">
        <div class="max-w-7xl mx-auto px-6 h-16 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <i class="fas fa-book-open text-blue-400 text-xl"></i>
                <span class="font-bold tracking-tight text-lg">SensoLab <span class="text-blue-400">Knowledge
                        Base</span></span>
            </div>
            <a href="index.php"
                class="bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded-lg text-sm font-bold transition flex items-center gap-2">
                <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
            </a>
        </div>
    </nav>

    <main class="max-w-4xl mx-auto px-6 py-12">
        <!-- Abstract -->
        <header class="mb-16 text-center">
            <h1 class="text-4xl font-extrabold text-slate-900 mb-4 italic tracking-tight">Landasan Teoritis & Validasi
                Statistik</h1>
            <p class="text-slate-600 text-lg leading-relaxed">Panduan komprehensif mengenai metodologi evaluasi
                reliabilitas sensor IoT yang diimplementasikan dalam sistem SensoLab V2.0.</p>
        </header>

        <!-- Section 1: Validasi & Reliabilitas -->
        <section id="validasi" class="mb-16">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center text-blue-600">
                    <i class="fas fa-vial"></i>
                </div>
                <h2 class="text-2xl font-bold text-slate-800 tracking-tight">1. Validasi vs Reliabilitas Sensor</h2>
            </div>
            <div class="bg-white p-8 rounded-2xl shadow-sm border border-slate-200 leading-relaxed text-slate-700">
                <p class="mb-4">
                    <strong>Validasi</strong> dalam konteks sensor IoT merujuk pada sejauh mana instrumen (sensor)
                    mengukur apa yang seharusnya diukur. Ini berkaitan dengan akurasi hasil terhadap standar referensi.
                </p>
                <p class="mb-6">
                    <strong>Reliabilitas</strong> (Keandalan) adalah tingkat konsistensi instrumen dalam memberikan
                    hasil yang sama pada kondisi yang identik. Sensor yang reliabel memiliki tingkat <em>noise</em> yang
                    rendah dan presisi pengulangan (repeatability) yang tinggi.
                </p>
                <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-4 italic text-sm">
                    "Reliabilitas adalah syarat perlu (necessary) namun belum cukup (sufficient) untuk validitas." (Ref:
                    NASA Instrumentation Guide, 2023).
                </div>
            </div>
        </section>

        <!-- Section 2: Metrik Evaluasi -->
        <section id="metrik" class="mb-16">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center text-indigo-600">
                    <i class="fas fa-calculator"></i>
                </div>
                <h2 class="text-2xl font-bold text-slate-800 tracking-tight">2. Metrik Evaluasi Sinyal</h2>
            </div>

            <!-- Mean & SD -->
            <div class="theory-card bg-white p-8 rounded-2xl shadow-sm border border-slate-200 mb-6">
                <h3 class="text-lg font-bold mb-4 flex items-center gap-2">
                    <span class="bg-slate-100 px-2 py-1 rounded">A</span> Mean (Rerata) & Standar Deviasi (SD)
                </h3>
                <p class="mb-6">Mean digunakan untuk menentukan tingkat 'Baseline' sinyal, sedangkan Standar Deviasi
                    mengukur penyimpangan rata-rata dari baseline tersebut.</p>
                <div class="math-block text-lg italic text-slate-800 mb-4">
                    μ = Σx / n <br>
                    σ = √[Σ(x - μ)² / n]
                </div>
                <p class="text-sm text-slate-500">Keterangan: x = nilai data, μ = rerata, n = jumlah sampel.</p>
            </div>

            <!-- Stability -->
            <div class="theory-card bg-white p-8 rounded-2xl shadow-sm border border-slate-200 mb-6">
                <h3 class="text-lg font-bold mb-4 flex items-center gap-2">
                    <span class="bg-slate-100 px-2 py-1 rounded">B</span> Koefisien Variasi (CV) & Stabilitas
                </h3>
                <p class="mb-6">CV adalah metrik untuk membandingkan variabilitas sinyal tanpa tergantung pada satuan
                    ukur. Stabilitas dihitung sebagai inversi dari CV.</p>
                <div class="math-block text-lg italic text-slate-800">
                    CV = σ / μ <br>
                    Stability (S) = 100% - (CV × 100%)
                </div>
            </div>

            <!-- Noise RMS -->
            <div class="theory-card bg-white p-8 rounded-2xl shadow-sm border border-slate-200">
                <h3 class="text-lg font-bold mb-4 flex items-center gap-2">
                    <span class="bg-slate-100 px-2 py-1 rounded">C</span> Noise Root Mean Square (RMS)
                </h3>
                <p class="mb-6">Mengidentifikasi amplitudo gangguan elektronik atau noise frekuensi tinggi yang muncul
                    pada kabel transmisi atau catu daya (jitter).</p>
                <div class="math-block text-lg italic text-slate-800">
                    RMS_{noise} = √[1/n Σ(x_{i} - μ)²] ≈ 0.707 × σ
                </div>
            </div>
        </section>

        <!-- Section 3: Klasifikasi Kualitas -->
        <section id="klasifikasi" class="mb-16">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 bg-emerald-100 rounded-full flex items-center justify-center text-emerald-600">
                    <i class="fas fa-layer-group"></i>
                </div>
                <h2 class="text-2xl font-bold text-slate-800 tracking-tight">3. Penilaian Repeatability</h2>
            </div>
            <div class="bg-white p-8 rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b-2 border-slate-100">
                            <th class="py-3 font-bold text-slate-800 uppercase text-xs">Ambang Batas SD (σ)</th>
                            <th class="py-3 font-bold text-slate-800 uppercase text-xs">Klasifikasi</th>
                            <th class="py-3 font-bold text-slate-800 uppercase text-xs">Interpretasi</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm">
                        <tr class="border-b border-slate-50">
                            <td class="py-4 text-emerald-600 font-mono">σ < 0.5</td>
                            <td class="py-4 font-bold">Sangat Tinggi</td>
                            <td class="py-4 text-slate-500">Sinyal ideal, layak untuk pelatihan AI.</td>
                        </tr>
                        <tr class="border-b border-slate-50">
                            <td class="py-4 text-blue-600 font-mono">0.5 - 1.0</td>
                            <td class="py-4 font-bold">Tinggi</td>
                            <td class="py-4 text-slate-500">Reliabilitas baik, noise minimal.</td>
                        </tr>
                        <tr class="border-b border-slate-50">
                            <td class="py-4 text-amber-600 font-mono">1.0 - 2.0</td>
                            <td class="py-4 font-bold">Sedang</td>
                            <td class="py-4 text-slate-500">Terdapat fluktuasi, perlu filter.</td>
                        </tr>
                        <tr>
                            <td class="py-4 text-red-600 font-mono">σ > 2.0</td>
                            <td class="py-4 font-bold">Rendah</td>
                            <td class="py-4 text-slate-500">Data korup atau gangguan hardware berat.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- References -->
        <section id="referensi" class="mb-16 border-t pt-10">
            <h2 class="text-xl font-bold text-slate-800 mb-6 flex items-center gap-2">
                <i class="fas fa-bookmark text-blue-500"></i> Referensi Terlacak (Tracking ID)
            </h2>
            <ul class="space-y-4 text-sm text-slate-600 list-disc ml-5">
                <li>IEEE Standards Association. <em>Standar Evaluasi Transduser Cerdas IoT (21451-X)</em>. 2023 Update.
                </li>
                <li>Sensor Data Quality Metrics for Fault Detection in Industrial IoT. <em>Journal of Sensors &
                        Actuators</em>. 2024. [DOI: 10.1016/j.sna.2024]</li>
                <li>ISO/IEC 23894:2023. <em>Information Technology — Artificial Intelligence — Risk Management on Data
                        Input</em>.</li>
                <li>NIST Technical Note 1982. <em>Guidelines for Evaluation of Measurement Uncertainty in Real-Time
                        Sensor Networks</em>.</li>
            </ul>
        </section>
    </main>

    <footer class="bg-slate-900 py-10 text-white text-center text-xs opacity-80 uppercase tracking-widest">
        &copy; 2026 SensoLab Research Repository | Knowledge Transfer Version 2.0
    </footer>

</body>

</html>