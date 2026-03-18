<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "db_sensor";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Koneksi Gagal: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
    if ($_POST['confirm_delete'] === 'HAPUS_SEMUA_DATA_SEKARANG') {
        // Hapus juga tabel label reliabilitas (Versi 2.0)
        $sql1 = "TRUNCATE TABLE tb_monitoring";
        $sql2 = "TRUNCATE TABLE tb_reliability_labels";
        
        if ($conn->query($sql1) === TRUE && $conn->query($sql2) === TRUE) {
            $_SESSION['msg'] = "SUKSES: Seluruh data monitoring & label reliabilitas telah dihapus secara permanen.";
            $_SESSION['msg_type'] = "success";
        } else {
            $_SESSION['msg'] = "ERROR: Gagal menghapus data. " . $conn->error;
            $_SESSION['msg_type'] = "error";
        }
    } else {
        $_SESSION['msg'] = "KATA KUNCI SALAH: Data TIDAK dihapus. Pastikan Anda mengetik kata kunci dengan benar.";
        $_SESSION['msg_type'] = "error";
    }
    header("Location: delete_data.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pusat Penghapusan Data | Administrator</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --danger: #ef4444;
            --warning: #fbbf24;
            --dark: #0f172a;
            --card: #1e293b;
        }

        body {
            background-color: var(--dark);
            color: #f8fafc;
            font-family: 'Plus Jakarta Sans', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }

        .warning-box {
            background-color: var(--card);
            max-width: 500px;
            width: 100%;
            padding: 40px;
            border-radius: 24px;
            border: 2px solid var(--danger);
            box-shadow: 0 0 50px rgba(239, 68, 68, 0.3);
            text-align: center;
            animation: shake 0.5s ease-in-out;
        }

        @keyframes shake {

            0%,
            100% {
                transform: translateX(0);
            }

            25% {
                transform: translateX(-10px);
            }

            75% {
                transform: translateX(10px);
            }
        }

        .icon-danger {
            font-size: 4rem;
            color: var(--danger);
            margin-bottom: 20px;
            animation: pulse 1s infinite;
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
                opacity: 1;
            }

            50% {
                transform: scale(1.1);
                opacity: 0.7;
            }

            100% {
                transform: scale(1);
                opacity: 1;
            }
        }

        h1 {
            margin-bottom: 15px;
            font-weight: 800;
            color: #fff;
        }

        p {
            color: #94a3b8;
            line-height: 1.6;
            margin-bottom: 25px;
            font-size: 0.95rem;
        }

        .danger-text {
            color: var(--danger);
            font-weight: 700;
            text-transform: uppercase;
        }

        .alert {
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .alert-success {
            background: rgba(34, 197, 94, 0.2);
            color: #4ade80;
            border: 1px solid #22c55e;
        }

        .alert-error {
            background: rgba(239, 68, 68, 0.2);
            color: #f87171;
            border: 1px solid #ef4444;
        }

        input[type="text"] {
            width: 100%;
            padding: 15px;
            background: #0f172a;
            border: 1px solid #334155;
            border-radius: 12px;
            color: white;
            text-align: center;
            font-size: 1rem;
            margin-bottom: 20px;
            box-sizing: border-box;
        }

        button {
            width: 100%;
            padding: 15px;
            background: var(--danger);
            color: white;
            border: none;
            border-radius: 12px;
            font-weight: 800;
            cursor: pointer;
            transition: 0.3s;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        button:hover {
            background: #dc2626;
            transform: scale(1.02);
        }

        .back-link {
            display: block;
            margin-top: 20px;
            color: #64748b;
            text-decoration: none;
            font-size: 0.85rem;
        }

        .back-link:hover {
            color: #fff;
        }
    </style>
</head>

<body>

    <div class="warning-box">
        <i class="fas fa-radiation icon-danger"></i>
        <h1>PERINGATAN KERAS!</h1>

        <?php if (isset($_SESSION['msg'])): ?>
            <div class="alert alert-<?= $_SESSION['msg_type'] ?>">
                <?= $_SESSION['msg'] ?>
            </div>
            <?php unset($_SESSION['msg']);
            unset($_SESSION['msg_type']); ?>
        <?php endif; ?>

        <p>Tindakan ini akan <span class="danger-text">MENGHAPUS SELURUH RIWAYAT DATA</span> sensor secara permanen dari
            database. Data yang sudah dihapus <u>tidak dapat dikembalikan</u> dengan cara apapun.</p>

        <form method="POST">
            <label style="display:block; margin-bottom: 10px; font-size: 0.8rem; color: #64748b;">Ketik:
                <strong>HAPUS_SEMUA_DATA_SEKARANG</strong> untuk konfirmasi</label>
            <input type="text" name="confirm_delete" placeholder="Ketik kata kunci konfirmasi..." required
                autocomplete="off">
            <button type="submit"
                onclick="return confirm('APAKAH ANDA BENAR-BENAR YAKIN? INI ADALAH KESEMPATAN TERAKHIR UNTUK MEMBATALKAN.')">
                <i class="fas fa-trash-alt mr-2"></i> Eksekusi Penghapusan Data
            </button>
        </form>

        <a href="dashboard.php" class="back-link"><i class="fas fa-arrow-left"></i> Kembali ke Dashboard</a>
    </div>

</body>

</html>
<?php $conn->close(); ?>