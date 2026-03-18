# Dokumentasi Teknis: Alur Kerja Sistem SensoLab

**Riset Instrumentasi Tahap 1 — Sensor Validation**  
Oleh : Vanya Clianta Evelyn Pasha

---

## Diagram 1 — Arsitektur Sistem Keseluruhan

```dot
digraph SensoLab_Architecture {
    rankdir=LR;
    graph [fontname="Arial" bgcolor="#f8fafc" pad="0.5" splines=ortho];
    node  [fontname="Arial" fontsize=11 style=filled shape=box];
    edge  [fontname="Arial" fontsize=9];

    // ─── LAYER HARDWARE ───────────────────────────────────────
    subgraph cluster_hw {
        label="Hardware Layer (NodeMCU ESP8266)";
        style=filled; color="#dbeafe"; fontsize=12; fontcolor="#1e40af";

        MQ135  [label="MQ-135\n(Gas Sensor)" fillcolor="#fef9c3" color="#ca8a04"];
        DHT11  [label="DHT11\n(Suhu + Hum)" fillcolor="#fef9c3" color="#ca8a04"];
        LDR    [label="LDR\n(Cahaya)" fillcolor="#fef9c3" color="#ca8a04"];
        NODEMCU [label="NodeMCU\nESP8266" fillcolor="#bfdbfe" color="#1d4ed8" shape=box3d];

        MQ135  -> NODEMCU [label="analog"];
        DHT11  -> NODEMCU [label="digital D2"];
        LDR    -> NODEMCU [label="analog A0"];
    }

    // ─── LAYER BACKEND ────────────────────────────────────────
    subgraph cluster_backend {
        label="Backend Layer (PHP/XAMPP)";
        style=filled; color="#dcfce7"; fontsize=12; fontcolor="#15803d";

        INPUT   [label="input.php\n(Data Receiver)" fillcolor="#bbf7d0" color="#16a34a"];
        API     [label="api_stats.php\n(JSON API)" fillcolor="#bbf7d0" color="#16a34a"];
        TOGGLE  [label="toggle_recording.php\n(Recording Control)" fillcolor="#bbf7d0" color="#16a34a"];
        DELETE  [label="delete_data.php\n(Admin)" fillcolor="#fee2e2" color="#dc2626"];
    }

    // ─── LAYER DATABASE ───────────────────────────────────────
    subgraph cluster_db {
        label="Database Layer (MySQL)";
        style=filled; color="#ede9fe"; fontsize=12; fontcolor="#6d28d9";

        TB_MON  [label="tb_monitoring\n(id, cahaya, suhu,\nkelembaban, ldr, waktu)" fillcolor="#ddd6fe" color="#7c3aed" shape=cylinder];
        TB_SET  [label="tb_settings\n(recording_service\nlast_heartbeat)" fillcolor="#ddd6fe" color="#7c3aed" shape=cylinder];
    }

    // ─── LAYER FRONTEND ───────────────────────────────────────
    subgraph cluster_frontend {
        label="Frontend Layer (Browser)";
        style=filled; color="#fce7f3"; fontsize=12; fontcolor="#9d174d";

        INDEX   [label="index.php\n(Portal)" fillcolor="#fbcfe8" color="#db2777"];
        DASH    [label="dashboard.php\n(Dashboard Realtime)" fillcolor="#fbcfe8" color="#db2777"];
        LOGVIEW [label="input.php\n(Log View)" fillcolor="#fbcfe8" color="#db2777"];
    }

    // ─── KONEKSI ──────────────────────────────────────────────
    NODEMCU -> INPUT   [label="HTTP GET\n(setiap 5s)" color="#1d4ed8" style=bold];
    INPUT   -> TB_MON  [label="INSERT data\n(jika recording ON)" color="#16a34a"];
    INPUT   -> TB_SET  [label="UPDATE\nlast_heartbeat" color="#16a34a"];
    API     -> TB_MON  [label="SELECT stats" color="#7c3aed" style=dashed];
    API     -> TB_SET  [label="SELECT status" color="#7c3aed" style=dashed];
    TOGGLE  -> TB_SET  [label="UPDATE\nrecording_service" color="#dc2626"];
    DELETE  -> TB_MON  [label="TRUNCATE" color="#dc2626" style=bold];
    DASH    -> TB_MON  [label="SELECT chart\ndata" color="#db2777" style=dashed];
    DASH    -> TB_SET  [label="SELECT status" color="#db2777" style=dashed];
    DASH    -> TOGGLE  [label="POST (AJAX)\nstart/stop" color="#db2777"];
    LOGVIEW -> API     [label="fetch() setiap 5s\n(AJAX)" color="#db2777" style=dashed];
    INDEX   -> DASH    [label="navigasi" style=dotted];
    INDEX   -> LOGVIEW [label="navigasi" style=dotted];
    INDEX   -> DELETE  [label="navigasi" style=dotted];
    INDEX   -> TB_MON  [label="SELECT count" color="#db2777" style=dashed];
    INDEX   -> TB_SET  [label="SELECT status" color="#db2777" style=dashed];
}
```

---

## Diagram 2 — Alur Penerimaan Data (input.php)

```dot
digraph InputFlow {
    rankdir=TB;
    graph [fontname="Arial" bgcolor="#f8fafc" pad="0.5"];
    node  [fontname="Arial" fontsize=11 style=filled];
    edge  [fontname="Arial" fontsize=9];

    START  [label="NodeMCU\nKirim HTTP GET" shape=oval fillcolor="#bfdbfe" color="#1d4ed8"];
    RECV   [label="input.php\nMenerima Request" shape=box fillcolor="#bbf7d0" color="#16a34a"];
    CHK_PARAM [label="Apakah ada\n?cahaya & ?gas?" shape=diamond fillcolor="#fef9c3" color="#ca8a04"];

    HB     [label="UPDATE last_heartbeat\n(tb_settings)" shape=box fillcolor="#ddd6fe" color="#7c3aed"];
    CHK_REC [label="recording_service\n= '1'?" shape=diamond fillcolor="#fef9c3" color="#ca8a04"];

    INSERT [label="INSERT INTO\ntb_monitoring\n(cahaya, gas, ldr,\nsuhu, kelembaban)" shape=box fillcolor="#bbf7d0" color="#16a34a"];
    PAUSE  [label="⏸ Log:\n[RECORDING PAUSED]\nData tidak disimpan" shape=box fillcolor="#fee2e2" color="#dc2626"];
    SHOW   [label="Tampilkan HTML:\nStatistik + Tabel\n+ Grafik" shape=box fillcolor="#fce7f3" color="#9d174d"];
    AJAX   [label="setInterval:\nFetch api_stats.php\nsetiap 5 detik" shape=box fillcolor="#fce7f3" color="#9d174d"];
    NO_JS  [label="(Tidak ada JS\nauto-refresh)" shape=box fillcolor="#f1f5f9" color="#64748b"];
    END    [label="Selesai" shape=oval fillcolor="#e2e8f0" color="#64748b"];

    START  -> RECV;
    RECV   -> CHK_PARAM;
    CHK_PARAM -> HB    [label="Ya" color="#16a34a"];
    CHK_PARAM -> SHOW  [label="Tidak\n(Browser)" color="#9d174d"];
    HB     -> CHK_REC;
    CHK_REC -> INSERT  [label="Ya (ON)" color="#16a34a"];
    CHK_REC -> PAUSE   [label="Tidak (OFF)" color="#dc2626"];
    INSERT -> END;
    PAUSE  -> END;
    SHOW   -> AJAX     [label="Browser\nbuka halaman"];
    SHOW   -> NO_JS    [label="NodeMCU\npanggil endpoint"];
    AJAX   -> END;
    NO_JS  -> END;
}
```

---

## Diagram 3 — Alur Deteksi Status Hardware (Heartbeat)

```dot
digraph HeartbeatFlow {
    rankdir=LR;
    graph [fontname="Arial" bgcolor="#f8fafc" pad="0.5"];
    node  [fontname="Arial" fontsize=11 style=filled];
    edge  [fontname="Arial" fontsize=9];

    HW_ON  [label="NodeMCU\nMenyala" shape=oval fillcolor="#bfdbfe" color="#1d4ed8"];
    SEND   [label="Kirim data\nsetiap 5 detik" shape=box fillcolor="#bfdbfe" color="#1d4ed8"];
    HB_UPD [label="input.php\nUPDATE last_heartbeat\n= time()" shape=box fillcolor="#bbf7d0" color="#16a34a"];
    DB_HB  [label="tb_settings\nlast_heartbeat = T" shape=cylinder fillcolor="#ddd6fe" color="#7c3aed"];

    DASH_READ [label="dashboard.php / index.php\nSELECT last_heartbeat" shape=box fillcolor="#fce7f3" color="#9d174d"];
    CALC   [label="diff = time() - last_heartbeat" shape=box fillcolor="#fef9c3" color="#ca8a04"];
    CHK    [label="diff < 30 detik?" shape=diamond fillcolor="#fef9c3" color="#ca8a04"];
    ONLINE [label="✅ HARDWARE: ONLINE\n(Hijau + Pulse)" shape=box fillcolor="#bbf7d0" color="#16a34a"];
    OFFLINE [label="❌ HARDWARE: OFFLINE\n(Merah + Notifikasi)" shape=box fillcolor="#fee2e2" color="#dc2626"];

    HW_ON  -> SEND -> HB_UPD -> DB_HB;
    DB_HB  -> DASH_READ -> CALC -> CHK;
    CHK    -> ONLINE  [label="Ya"];
    CHK    -> OFFLINE [label="Tidak"];
}
```

---

## Diagram 4 — Alur Kontrol Perekaman

```dot
digraph RecordingControl {
    rankdir=TB;
    graph [fontname="Arial" bgcolor="#f8fafc" pad="0.5"];
    node  [fontname="Arial" fontsize=11 style=filled];
    edge  [fontname="Arial" fontsize=9];

    ADMIN  [label="Admin\nKlik Tombol\nSTOP/START RECORD" shape=oval fillcolor="#fce7f3" color="#9d174d"];
    AJAX   [label="JavaScript fetch()\nPOST ke toggle_recording.php\n{status: 0 atau 1}" shape=box fillcolor="#fce7f3" color="#9d174d"];
    TOGGLE [label="toggle_recording.php\nUPDATE tb_settings\nSET recording_service = ?" shape=box fillcolor="#bbf7d0" color="#16a34a"];
    DB_SET [label="tb_settings\nrecording_service = 0 / 1" shape=cylinder fillcolor="#ddd6fe" color="#7c3aed"];
    RELOAD [label="location.reload()\nDashboard refresh" shape=box fillcolor="#fce7f3" color="#9d174d"];

    NEXT   [label="NodeMCU kirim data\nberikutnya..." shape=oval fillcolor="#bfdbfe" color="#1d4ed8"];
    CHK    [label="recording_service = 1?" shape=diamond fillcolor="#fef9c3" color="#ca8a04"];
    SAVE   [label="✅ INSERT ke DB\n[RECORDING ON]" shape=box fillcolor="#bbf7d0" color="#16a34a"];
    SKIP   [label="⏸ SKIP insert\n[RECORDING PAUSED]" shape=box fillcolor="#fee2e2" color="#dc2626"];

    ADMIN -> AJAX -> TOGGLE -> DB_SET -> RELOAD;
    NEXT  -> CHK;
    CHK   -> SAVE [label="Ya"];
    CHK   -> SKIP [label="Tidak"];
}
```

---

## Diagram 5 — Skema Database

```dot
digraph DatabaseSchema {
    rankdir=LR;
    graph [fontname="Arial" bgcolor="#f8fafc" pad="0.5"];
    node  [fontname="Arial" fontsize=11 style=filled shape=record];
    edge  [fontname="Arial" fontsize=9];

    TB_MON [label="{tb_monitoring | 
        id INT AUTO_INCREMENT PK |
        cahaya VARCHAR(20) |
        status_gas VARCHAR(10) |
        ldr VARCHAR(20) |
        suhu FLOAT |
        kelembaban FLOAT |
        waktu TIMESTAMP DEFAULT NOW()
    }" fillcolor="#ddd6fe" color="#7c3aed"];

    TB_SET [label="{tb_settings | 
        id INT AUTO_INCREMENT PK |
        name VARCHAR(50) UNIQUE |
        value TEXT
    }" fillcolor="#ddd6fe" color="#7c3aed"];

    REC  [label="{recording_service | value: '1' (ON) / '0' (OFF)}" fillcolor="#fef9c3" color="#ca8a04" shape=box];
    HB   [label="{last_heartbeat | value: Unix Timestamp}" fillcolor="#fef9c3" color="#ca8a04" shape=box];

    TB_SET -> REC [label="name ="];
    TB_SET -> HB  [label="name ="];
}
```

---

## Ringkasan Alur Kerja

| No | Komponen | Fungsi | Interval |
|----|----------|--------|----------|
| 1 | NodeMCU (firmware) | Baca sensor → kirim HTTP GET | Setiap 5 detik |
| 2 | `input.php` | Terima data → update heartbeat → insert jika ON | Per request NodeMCU |
| 3 | `api_stats.php` | Kembalikan JSON statistik terkini | Per permintaan AJAX |
| 4 | `dashboard.php` | Tampilkan data real-time + kontrol | Auto-reload 5 detik |
| 5 | `toggle_recording.php` | ON/OFF perekaman database | Per klik admin |
| 6 | `delete_data.php` | TRUNCATE seluruh data | Per konfirmasi admin |
| 7 | `index.php` | Portal navigasi + status ringkas | Auto-reload 10 detik |

> Untuk merender diagram, gunakan [Graphviz Online](https://dreampuf.github.io/GraphvizOnline/) atau install Graphviz dan jalankan:
> ```bash
> dot -Tpng alur_sistem.dot -o alur_sistem.png
> ```
