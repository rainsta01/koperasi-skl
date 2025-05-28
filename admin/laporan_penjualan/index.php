<?php
session_start();

$timeout = 1800;
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > $timeout)) {
    session_unset();
    session_destroy();
    header("Location: ../login.php");
    exit();
}
$_SESSION['LAST_ACTIVITY'] = time();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    die("Akses ditolak. Anda bukan admin.");
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Penjualan</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
        }

        .container {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        button {
            padding: 10px 15px;
            margin: 10px;
            font-size: 16px;
            cursor: pointer;
            border: none;
            border-radius: 5px;
            background-color: #007bff;
            color: white;
        }

        button:hover {
            background-color: #0056b3;
        }

        .back-btn {
            background-color: #dc3545;
        }

        .back-btn:hover {
            background-color: #a71d2a;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>📊 Laporan Penjualan</h2>
        <p>Pilih jenis laporan yang ingin Anda lihat:</p>

        <a href="periode"><button>📅 Laporan Berdasarkan Periode</button></a><br>

        <a href="../"><button class="back-btn">🔙 Kembali ke Halaman Admin</button></a>
    </div>
</body>

</html>
