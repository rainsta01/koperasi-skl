<?php
session_start();
include '../../../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    die("Akses ditolak. Anda bukan admin.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama_produk = htmlspecialchars(trim($_POST['nama_produk']));
    $kategori = htmlspecialchars($_POST['kategori']);
    $harga_beli = floatval($_POST['harga_beli']);
    $harga_jual = floatval($_POST['harga_jual']);
    $stok = intval($_POST['stok']);

    if ($harga_beli < 0 || $harga_jual < 0 || $stok < 0) {
        $error = "Harga dan stok tidak boleh negatif!";
    } elseif ($harga_jual < $harga_beli) {
        $error = "Harga jual tidak boleh lebih kecil dari harga beli!";
    } else {
        $stmt = $conn->prepare("INSERT INTO produk (nama_produk, kategori, harga_beli, harga_jual, stok) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssddd", $nama_produk, $kategori, $harga_beli, $harga_jual, $stok);

        if ($stmt->execute()) {
            $data_baru = json_encode(["nama_produk" => $nama_produk, "kategori" => $kategori, "harga_beli" => $harga_beli, "harga_jual" => $harga_jual, "stok" => $stok]);
            $log_stmt = $conn->prepare("INSERT INTO log_perubahan (user_id, aksi, tabel, data_baru) VALUES (?, 'INSERT', 'produk', ?)");
            $log_stmt->bind_param("is", $_SESSION['user_id'], $data_baru);
            $log_stmt->execute();

            header("Location: ../?msg=Produk berhasil ditambahkan!");
            exit();
        } else {
            $error = "Terjadi kesalahan saat menambahkan produk.";
        }
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Tambah Produk</title>
    <style>
        <style>body {
            font-family: Arial, sans-serif;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        tr {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        button,
        select,
        input {
            cursor: pointer;
            padding: 8px;
            margin: 5px;
        }
    </style>
    </style>
</head>

<body>
    <h2>Tambah Produk</h2>
    <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
    <form method="POST">
        <label>Nama Produk:</label><br>
        <input type="text" name="nama_produk" required><br>

        <label>Kategori:</label><br>
        <select name="kategori">
            <option value="Buku">Buku</option>
            <option value="Aksesoris Seragam">Aksesoris Seragam</option>
            <option value="Alat Tulis">Alat Tulis</option>
            <option value="Makanan">Makanan</option>
            <option value="Minuman">Minuman</option>
            <option value="Lainnya">Lainnya</option>
        </select><br>

        <label>Harga Beli:</label><br>
        <input type="number" name="harga_beli" step="0.01" min="0" required><br>

        <label>Harga Jual:</label><br>
        <input type="number" name="harga_jual" step="0.01" min="0" required><br>

        <label>Stok:</label><br>
        <input type="number" name="stok" min="0" required><br><br>

        <button type="submit">Tambah Produk</button>
    </form>
    <br>
    <a href="../"><button>🔙 Kembali ke Data Produk</button></a>
</body>

</html>
