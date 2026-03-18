# SensoLab — Panduan Alur Sistem untuk Umum

**Riset Instrumentasi Tahap 1 — Sensor Validation**  
Oleh : Vanya Clianta Evelyn Pasha

> 📌 Dokumen ini menjelaskan cara kerja sistem secara sederhana,  
> **tanpa istilah teknis**, agar dapat dipahami oleh semua kalangan.

---

## 🌐 Gambaran Besar: Apa yang Dilakukan Sistem Ini?

Bayangkan sistem ini seperti **stasiun cuaca mini otomatis** yang:
- 👃 **Mencium** kadar gas di udara
- 🌡️ **Merasakan** suhu dan kelembaban ruangan
- 💡 **Melihat** apakah ruangan terang atau gelap
- 📊 **Mencatat** semua temuan itu ke dalam buku catatan digital
- 📱 **Melaporkan** hasilnya secara langsung lewat layar komputer Anda

---

## Diagram 1 — Alur Kerja Utama (Gambaran Besar)

```dot
digraph HighLevel {
    rankdir=TB;
    graph [fontname="Arial" bgcolor="#fffbf0" pad="0.8" splines=curved];
    node  [fontname="Arial" fontsize=12 style="filled,rounded" shape=box];
    edge  [fontname="Arial" fontsize=10 color="#64748b"];

    // Aktor
    SENSOR  [label="🔬 Alat Sensor\n(di ruangan)\n\nMencium gas, mengukur\nsuhu & melihat cahaya"
             fillcolor="#fef9c3" color="#ca8a04" shape=box];

    OTAK    [label="🧠 Otak Mini\n(NodeMCU)\n\nMengumpulkan semua\nbacaan sensor lalu\nmengirimnya ke server"
             fillcolor="#dbeafe" color="#2563eb" shape=box];

    SERVER  [label="🖥️ Server\n(Komputer Lokal)\n\nMenerima data,\nmemeriksa apakah\nperlu dicatat"
             fillcolor="#dcfce7" color="#16a34a" shape=box];

    DB      [label="📚 Buku Catatan\n(Database)\n\nMenyimpan semua\nriwayat data\nsecara permanen"
             fillcolor="#ede9fe" color="#7c3aed" shape=cylinder];

    LAYAR   [label="📊 Layar Monitor\n(Browser)\n\nMenunjukkan grafik,\nangka, dan status\nsecara langsung"
             fillcolor="#fce7f3" color="#db2777" shape=box];

    PENGGUNA [label="👤 Pengguna / Admin\n\nMemantau kondisi\nruangan dari mana saja"
              fillcolor="#f0fdf4" color="#15803d" shape=oval];

    // Alur
    SENSOR  -> OTAK    [label="Setiap 5 detik\ndata dikirim" style=bold color="#ca8a04"];
    OTAK    -> SERVER  [label="Dikirim lewat\njaringan WiFi" color="#2563eb"];
    SERVER  -> DB      [label="Dicatat jika\nrekaman aktif" color="#16a34a"];
    SERVER  -> LAYAR   [label="Ditampilkan\nlangsung" color="#db2777" style=dashed];
    DB      -> LAYAR   [label="Diambil untuk\ngrafik & tabel" color="#7c3aed" style=dashed];
    LAYAR   -> PENGGUNA [label="Dibaca &\ndipantau" color="#15803d"];
}
```

---

## Diagram 2 — Apa yang Dilihat Pengguna di Layar?

```dot
digraph UserView {
    rankdir=LR;
    graph [fontname="Arial" bgcolor="#fffbf0" pad="0.6" splines=ortho];
    node  [fontname="Arial" fontsize=11 style="filled,rounded" shape=box];
    edge  [fontname="Arial" fontsize=9 color="#64748b"];

    PORTAL  [label="🏠 Halaman Utama\n(index.php)\n\nPintu masuk aplikasi.\nMenunjukkan status\nsingkat sistem."
             fillcolor="#fce7f3" color="#db2777"];

    DASH    [label="📊 Dashboard\n(dashboard.php)\n\nGrafik kondisi sensor\nsecara LANGSUNG.\nStatus alat: Online/Offline.\nTombol Start/Stop rekam."
             fillcolor="#dbeafe" color="#2563eb"];

    LOG     [label="📋 Log Data\n(input.php)\n\nDaftar semua catatan\nhistori sensor.\nGrafik gabungan\n3 sensor sekaligus."
             fillcolor="#dcfce7" color="#16a34a"];

    ADMIN   [label="🗑️ Kelola Database\n(delete_data.php)\n\nHapus semua catatan\nlama jika penyimpanan\nsudah penuh.\n⚠️ Perlu kata kunci khusus."
             fillcolor="#fee2e2" color="#dc2626"];

    PORTAL -> DASH  [label="Klik\nDashboard"];
    PORTAL -> LOG   [label="Klik\nLog Data"];
    PORTAL -> ADMIN [label="Klik\nAdmin"];
}
```

---

## Diagram 3 — Fitur START / STOP Rekaman

```dot
digraph RecordingSimple {
    rankdir=TB;
    graph [fontname="Arial" bgcolor="#fffbf0" pad="0.6" splines=curved];
    node  [fontname="Arial" fontsize=11 style="filled,rounded" shape=box];
    edge  [fontname="Arial" fontsize=9];

    ADMIN   [label="👤 Admin\nMenekan Tombol\ndi Dashboard"
             fillcolor="#fce7f3" color="#db2777" shape=oval];

    CHK     [label="Tombol apa\nyang ditekan?"
             fillcolor="#fef9c3" color="#ca8a04" shape=diamond];

    STOP    [label="🔴 STOP RECORD\n\nSensor tetap aktif &\ndata tetap tampil\ndi layar.\n\nTAPI tidak disimpan\nke buku catatan."
             fillcolor="#fee2e2" color="#dc2626"];

    START   [label="🟢 START RECORD\n\nSemua data dari sensor\nkembali disimpan\nke buku catatan."
             fillcolor="#dcfce7" color="#16a34a"];

    NOTIF_S [label="⚠️ Muncul banner kuning:\n'Perekaman Dijeda Manual'"
             fillcolor="#fef9c3" color="#ca8a04"];
    NOTIF_A [label="✅ Banner hilang,\nrekaman aktif kembali"
             fillcolor="#dcfce7" color="#16a34a"];

    ADMIN -> CHK;
    CHK   -> STOP  [label="STOP" color="#dc2626"];
    CHK   -> START [label="START" color="#16a34a"];
    STOP  -> NOTIF_S;
    START -> NOTIF_A;
}
```

---

## Diagram 4 — Sistem Mendeteksi Alat Mati/Nyala

```dot
digraph OnlineDetection {
    rankdir=LR;
    graph [fontname="Arial" bgcolor="#fffbf0" pad="0.6"];
    node  [fontname="Arial" fontsize=11 style="filled,rounded" shape=box];
    edge  [fontname="Arial" fontsize=9];

    KIRIM   [label="📡 Alat mengirim\nsinyal ke server\nsetiap 5 detik"
             fillcolor="#dbeafe" color="#2563eb"];

    TERIMA  [label="🖥️ Server mencatat:\n'Terakhir menerima\nsinyal jam XX:XX:XX'"
             fillcolor="#dcfce7" color="#16a34a"];

    CEK     [label="Sudah lebih\ndari 30 detik\ntanpa sinyal?"
             fillcolor="#fef9c3" color="#ca8a04" shape=diamond];

    ONLINE  [label="✅ HARDWARE: ONLINE\n\nIndikator hijau berkedip.\nSemua normal."
             fillcolor="#dcfce7" color="#16a34a"];

    OFFLINE [label="❌ HARDWARE: OFFLINE\n\nIndikator merah menyala.\nMuncul notifikasi:\n'Koneksi Hardware Terputus!\nPeriksa daya/WiFi.'"
             fillcolor="#fee2e2" color="#dc2626"];

    KIRIM  -> TERIMA -> CEK;
    CEK    -> ONLINE  [label="Tidak\n(Alat masih aktif)" color="#16a34a"];
    CEK    -> OFFLINE [label="Ya\n(Alat mati/WiFi putus)" color="#dc2626"];
}
```

---

## Diagram 5 — Siklus Hidup Data (Dari Udara ke Layar)

```dot
digraph DataLifecycle {
    rankdir=LR;
    graph [fontname="Arial" bgcolor="#fffbf0" pad="0.5" splines=curved];
    node  [fontname="Arial" fontsize=10 style="filled,rounded" shape=box width=1.6];
    edge  [fontname="Arial" fontsize=9 color="#64748b"];

    A [label="1️⃣ Sensor\nMembaca\nLingkungan" fillcolor="#fef9c3" color="#ca8a04"];
    B [label="2️⃣ NodeMCU\nMengemas\nData" fillcolor="#dbeafe" color="#2563eb"];
    C [label="3️⃣ Dikirim\nLewat WiFi\n(5 detik sekali)" fillcolor="#dbeafe" color="#2563eb"];
    D [label="4️⃣ Server\nMemeriksa\nStatus Rekam" fillcolor="#dcfce7" color="#16a34a"];
    E [label="5️⃣ Data\nDisimpan\ndi Database" fillcolor="#ede9fe" color="#7c3aed"];
    F [label="6️⃣ Ditampilkan\ndi Grafik &\nTabel" fillcolor="#fce7f3" color="#db2777"];
    G [label="7️⃣ Admin\nMembaca\n& Menganalisis" fillcolor="#f0fdf4" color="#15803d"];

    A -> B -> C -> D -> E -> F -> G;
    D -> F [label="(tetap tampil\nmeski stop rekam)" style=dashed color="#9ca3af"];
}
```

---

## 📖 Panduan Singkat: Apa yang Harus Dilakukan Pengguna?

### Pemantauan Harian
1. Buka browser → ketik alamat server
2. Halaman **Beranda** menampilkan status cepat alat dan jumlah data
3. Klik **Dashboard** untuk melihat kondisi sensor secara langsung

### Jika Ingin Berhenti Merekam (Misal: Saat Kalibrasi)
1. Buka **Dashboard**
2. Klik tombol merah **STOP RECORD** di pojok kanan atas
3. Muncul banner kuning → sensor tetap terpantau tapi data tidak dicatat
4. Klik **START RECORD** jika ingin merekam kembali

### Jika Database Sudah Penuh / Ingin Reset Data
1. Dari Beranda, klik **Kosongkan Database**
2. Baca peringatan dengan seksama
3. Ketik kata kunci konfirmasi: **`HAPUS_SEMUA_DATA_SEKARANG`**
4. Klik tombol hapus → semua riwayat data dihapus permanen

### Indikator Status yang Perlu Diperhatikan

| Indikator | Artinya | Tindakan |
|-----------|---------|----------|
| 🟢 HARDWARE: ONLINE | Alat berjalan normal | Tidak perlu tindakan |
| 🔴 HARDWARE: OFFLINE | Alat mati atau WiFi putus | Periksa kabel daya dan WiFi |
| 🟡 PEREKAMAN DIJEDA | Admin mematikan rekam manual | Klik START RECORD jika perlu |
| 🟢 STATUS: MEREKAM | Data aktif disimpan ke database | Normal |

---

*Dokumen ini dibuat untuk keperluan Riset Instrumentasi Tahap 1.*  
*SensoLab © 2026 — Vanya Clianta Evelyn Pasha*
