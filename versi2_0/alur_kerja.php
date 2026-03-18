<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Arsitektur & Alur Kerja Sistem — SensoLab V2.0</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/mermaid/dist/mermaid.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        mermaid.initialize({ startOnLoad: true, theme: 'neutral' });
    </script>
    <style>
        body {
            background-color: #f1f5f9;
            font-family: 'Inter', sans-serif;
        }

        .flow-container {
            background: white;
            padding: 2rem;
            border-radius: 1.5rem;
            border: 1px solid #e2e8f0;
            margin-bottom: 2rem;
        }

        .step-num {
            width: 32px;
            height: 32px;
            background: #3b82f6;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            flex-shrink: 0;
        }
    </style>
</head>

<body class="pt-20">

    <!-- Navbar -->
    <nav class="bg-slate-900 text-white fixed top-0 w-full z-50 shadow-lg">
        <div class="max-w-7xl mx-auto px-6 h-16 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <i class="fas fa-project-diagram text-blue-400 text-xl"></i>
                <span class="font-bold tracking-tight text-lg">SensoLab V2.0 <span class="text-slate-400 font-normal">|
                        Flowchart</span></span>
            </div>
            <a href="index.php"
                class="text-sm bg-slate-800 hover:bg-slate-700 px-4 py-2 rounded-lg transition border border-slate-700">
                <i class="fas fa-home mr-2"></i> Dashboard
            </a>
        </div>
    </nav>

    <main class="max-w-5xl mx-auto px-6 py-12">
        <header class="mb-12">
            <h1 class="text-3xl font-extrabold text-slate-800 mb-2">Arsitektur & Mekanisme Kerja</h1>
            <p class="text-slate-500">Diagram alir teknis pengolahan data dari sensor hingga ke tahap evaluasi
                statistik.</p>
        </header>

        <!-- Dynamic Flowchart Section -->
        <div class="flow-container shadow-sm">
            <h2 class="text-xl font-bold text-slate-700 mb-8 flex items-center gap-2">
                <i class="fas fa-random text-blue-500"></i>
                Diagram Alir Sistem (SOP Transmisi Data)
            </h2>
            <div class="mermaid flex justify-center">
                graph TD
                A[START: Hardware NodeMCU] --> B{Baca Sensor<br />Global Sensors}
                B --> C[Kirim via HTTP POST<br />Ke input.php]

                subgraph Backend_Process
                C --> D{Cek Status<br />Recording?}
                D -- OFF --> E[Update Heartbeat]
                D -- ON --> F[INSERT tb_monitoring]
                F --> G[Update Heartbeat]
                end

                subgraph Dashboard_V2_Analytics
                H[Fetch via api_v2.php] --> I[Real-time Monitoring]
                I --> J[Statistik Moving Window<br />Mean, SD, Stability]
                end

                subgraph Smart_Dataset_Vault
                K[Proses Labelling<br />api_process_labels.php] --> L{Analisis<br />Reliabilitas}
                L -- STABIL --> M[Label: VALID]
                L -- NOISY --> N[Label: INVALID]
                M & N --> O[Simpan tb_reliability_labels]
                O --> P((Export CSV<br />Dataset Valid))
                end

                G -.-> H
                H -.-> K

                style A fill:#eff6ff,stroke:#3b82f6
                style F fill:#f0fdf4,stroke:#22c55e
                style P fill:#f5f3ff,stroke:#8b5cf6
            </div>
        </div>

        <!-- Detailed Explanation Steps -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <!-- Step 1 & 2 -->
            <div class="space-y-8">
                <div class="flex gap-4">
                    <div class="step-num">1</div>
                    <div>
                        <h3 class="font-bold text-slate-800">Akuisisi Data (Edge Layer)</h3>
                        <p class="text-sm text-slate-600 mt-1 leading-relaxed">NodeMCU mengirim data ke
                            <code>input.php</code>. Uniknya, di V2.0 kontrol recording bisa dilakukan jarak jauh via
                            Dashboard untuk memerintah server apakah data harus disimpan atau tidak.</p>
                    </div>
                </div>
                <div class="flex gap-4">
                    <div class="step-num">2</div>
                    <div>
                        <h3 class="font-bold text-slate-800">Reliability Monitoring</h3>
                        <p class="text-sm text-slate-600 mt-1 leading-relaxed">Dashboard mengambil data mentah dan
                            menghitung metrik secara <i>live</i>. Ini adalah tahap observasi visual bagi peneliti untuk
                            melihat noise secara langsung.</p>
                    </div>
                </div>
            </div>

            <!-- Step 3 & 4 -->
            <div class="space-y-8">
                <div class="flex gap-4">
                    <div class="step-num">3</div>
                    <div>
                        <h3 class="font-bold text-slate-800">Automated Labelling (New)</h3>
                        <p class="text-sm text-slate-600 mt-1 leading-relaxed">Mesin statistik memeriksa database dan
                            memberikan "Stempel" Valid/Invalid. Setiap data kini berelasi dengan tabel reliabilitas
                            untuk filter dataset.</p>
                    </div>
                </div>
                <div class="flex gap-4">
                    <div class="step-num">4</div>
                    <div>
                        <h3 class="font-bold text-slate-800">Cloud Dataset Export</h3>
                        <p class="text-sm text-slate-600 mt-1 leading-relaxed">Peneliti dapat mengunduh hanya data yang
                            berlabel <strong>VALID</strong> dalam format CSV. Ini menjamin input data ke model AI di
                            tahap riset selanjutnya adalah data yang bersih.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Logic Section -->
        <div class="mt-16 bg-slate-800 text-white p-8 rounded-2xl">
            <h3 class="text-lg font-bold mb-4 flex items-center gap-2">
                <i class="fas fa-code-branch text-emerald-400"></i>
                Dataset Validation Cycle
            </h3>
            <div class="bg-slate-900/50 p-6 rounded-xl border border-slate-700 font-mono text-xs leading-relaxed">
                WHILE (Data_In_Monitoring) {<br />
                &nbsp;&nbsp;CHECK (Stability_Score > 90% AND SD
                < Threshold);<br />
                &nbsp;&nbsp;IF (True) Label = "VALID" (Ready for AI);<br />
                &nbsp;&nbsp;ELSE Label = "INVALID" (Discard);<br />
                &nbsp;&nbsp;SAVE_TO (tb_reliability_labels);<br />
                }
            </div>
        </div>
    </main>

    <footer class="mt-12 py-10 text-center text-slate-400 text-xs uppercase tracking-widest border-t border-slate-200">
        SensoLab Project Workflow | System Documentation V2.0
    </footer>

</body>

</html>