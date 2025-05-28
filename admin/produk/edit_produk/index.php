<?php
session_start();
include '../../../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    die("Akses ditolak. Anda bukan admin.");
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID produk tidak valid!");
}

$id = intval($_GET['id']);

$stmt = $conn->prepare("SELECT * FROM produk WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$produk = $result->fetch_assoc();

if (!$produk) {
    die("Produk tidak ditemukan!");
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
        $data_lama = json_encode([
            "nama_produk" => $produk['nama_produk'], 
            "kategori" => $produk['kategori'], 
            "harga_beli" => $produk['harga_beli'], 
            "harga_jual" => $produk['harga_jual'], 
            "stok" => $produk['stok']
        ]);

        $stmt = $conn->prepare("UPDATE produk SET nama_produk=?, kategori=?, harga_beli=?, harga_jual=?, stok=? WHERE id=?");
        $stmt->bind_param("ssdddi", $nama_produk, $kategori, $harga_beli, $harga_jual, $stok, $id);

        if ($stmt->execute()) {
            $data_baru = json_encode([
                "nama_produk" => $nama_produk, 
                "kategori" => $kategori, 
                "harga_beli" => $harga_beli, 
                "harga_jual" => $harga_jual, 
                "stok" => $stok
            ]);

            $log_stmt = $conn->prepare("INSERT INTO log_perubahan (user_id, aksi, tabel, data_lama, data_baru) VALUES (?, 'UPDATE', 'produk', ?, ?)");
            $log_stmt->bind_param("iss", $_SESSION['user_id'], $data_lama, $data_baru);
            $log_stmt->execute();

            header("Location: ../?msg=Produk berhasil diperbarui!");
            exit();
        } else {
            $error = "Terjadi kesalahan saat mengupdate produk.";
        }
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Edit Produk</title>
    <style>
        body {
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
</head>

<body>
    <h2>Edit Produk</h2>
    <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
    <form method="POST">
        <label>Nama Produk:</label><br>
        <input type="text" name="nama_produk" value="<?php echo htmlspecialchars($produk['nama_produk']); ?>"
            required><br>

        <label>Kategori:</label><br>
        <select name="kategori">
            <option value="Buku" <?php if ($produk['kategori']=="Buku" ) echo "selected" ; ?>>Buku</option>
            <option value="Aksesoris Seragam" <?php if ($produk['kategori']=="Aksesoris Seragam" ) echo "selected" ; ?>
                >Aksesoris Seragam</option>
            <option value="Alat Tulis" <?php if ($produk['kategori']=="Alat Tulis" ) echo "selected" ; ?>>Alat Tulis
            </option>
            <option value="Makanan" <?php if ($produk['kategori']=="Makanan" ) echo "selected" ; ?>>Makanan</option>
            <option value="Minuman" <?php if ($produk['kategori']=="Minuman" ) echo "selected" ; ?>>Minuman</option>
            <option value="Lainnya" <?php if ($produk['kategori']=="Lainnya" ) echo "selected" ; ?>>Lainnya</option>
        </select><br>

        <label>Harga Beli:</label><br>
        <input type="number" name="harga_beli" step="0.01" min="0"
            value="<?php echo htmlspecialchars($produk['harga_beli']); ?>" required><br>

        <label>Harga Jual:</label><br>
        <input type="number" name="harga_jual" step="0.01" min="0"
            value="<?php echo htmlspecialchars($produk['harga_jual']); ?>" required><br>

        <label>Stok:</label><br>
        <input type="number" name="stok" min="0" value="<?php echo htmlspecialchars($produk['stok']); ?>"
            required><br><br>

        <button type="submit">Simpan Perubahan</button>
    </form>
    <br>
    <a href="../"><button>🔙 Kembali ke Data Produk</button></a>
</body>

</html>
