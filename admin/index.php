<?php
session_start();
include '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login");
    exit();
}

session_regenerate_id(true); 
$nama = htmlspecialchars($_SESSION['nama'], ENT_QUOTES, 'UTF-8');
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta http-equiv="X-Frame-Options" content="DENY">
    <title>Halaman Utama Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: #f0f2f5;
            color: #1a1a1a;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .container {
            background: white;
            max-width: 1000px;
            width: 90%;
            margin: 20px auto;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 8px 24px rgba(149, 157, 165, 0.2);
        }

        h2 {
            color: #1a73e8;
            font-size: 2.2em;
            margin-bottom: 10px;
        }

        p {
            color: #5f6368;
            font-size: 1.1em;
            margin-bottom: 30px;
        }

        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }

        .menu-item {
            text-decoration: none;
            transition: transform 0.2s;
        }

        .menu-item:hover {
            transform: translateY(-5px);
        }

        button {
            width: 100%;
            padding: 20px;
            font-size: 1.1em;
            cursor: pointer;
            border: none;
            border-radius: 12px;
            background: #ffffff;
            color: #1a1a1a;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        button:hover {
            background: #f8f9fa;
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.15);
        }

        .history-btn { background: #4285f4; color: white; }
        .history-btn:hover { background: #3574e2; }
        
        .transaction-btn { background: #34a853; color: white; }
        .transaction-btn:hover { background: #2d9147; }
        
        .product-btn { background: #fbbc05; color: white; }
        .product-btn:hover { background: #e2a904; }
        
        .report-btn { background: #ea4335; color: white; }
        .report-btn:hover { background: #d33426; }
        
        .users-btn { background: #9c27b0; color: white; }
        .users-btn:hover { background: #8e24aa; }
        
        .logout-btn { 
            background: #dc3545;
            color: white;
        }
        .logout-btn:hover { 
            background: #c82333;
        }

        @media (max-width: 768px) {
            .container {
                padding: 20px;
                margin: 10px;
            }

            h2 {
                font-size: 1.8em;
            }

            .menu-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>Selamat Datang di Sistem Koperasi</h2>
        <p>Halo, <b><?php echo $nama; ?></b>! Silakan pilih menu di bawah ini</p>

        <div class="menu-grid">
            <a href="log_perubahan" class="menu-item">
                <button class="history-btn">📜 History Perubahan</button>
            </a>
            <a href="../user" class="menu-item">
                <button class="transaction-btn">💳 Transaksi</button>
            </a>
            <a href="produk" class="menu-item">
                <button class="product-btn">📦 Kelola Produk</button>
            </a>
            <a href="laporan_penjualan" class="menu-item">
                <button class="report-btn">📊 Laporan Penjualan</button>
            </a>
            <a href="user_management" class="menu-item">
                <button class="users-btn">👥 Kelola Pengguna</button>
            </a>
            <a href="../logout" class="menu-item">
                <button class="logout-btn">🚪 Logout</button>
            </a>
        </div>
    </div>
</body>

</html>
