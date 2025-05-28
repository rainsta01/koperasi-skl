<?php
session_start();
include '../../config.php';

if (!isset($_SESSION['user_id'])) {
    die("Anda harus login terlebih dahulu.");
}

$user_id = $_SESSION['user_id'];

$user_query = $conn->prepare("SELECT nama, role FROM users WHERE id = ?");
$user_query->bind_param("i", $user_id);
$user_query->execute();
$user_result = $user_query->get_result();
$user_data = $user_result->fetch_assoc();

$nama_user = $user_data['nama'];
$role = $user_data['role'];

$produk_dipilih = $_POST['produk'] ?? [];
$items = [];
$total = 0;

foreach ($produk_dipilih as $id_produk) {
    $jumlah = isset($_POST['jumlah_' . $id_produk]) ? intval($_POST['jumlah_' . $id_produk]) : 0;
    if ($jumlah < 1) continue;

    $stmt = $conn->prepare("SELECT * FROM produk WHERE id = ?");
    $stmt->bind_param("i", $id_produk);
    $stmt->execute();
    $result = $stmt->get_result();
    $produk = $result->fetch_assoc();

    if ($produk && $produk['stok'] >= $jumlah) {
        $subtotal = $produk['harga_jual'] * $jumlah;
        $items[] = [
            'id' => $id_produk,
            'nama' => $produk['nama_produk'],
            'harga' => $produk['harga_jual'],
            'jumlah' => $jumlah,
            'subtotal' => $subtotal,
        ];
        $total += $subtotal;
    }
}

$_SESSION['keranjang'] = $items;
?>
<!DOCTYPE html>
<html>

<head>
    <title>Konfirmasi Produk</title>
    <link rel="stylesheet" href="pagewTable.css">
</head>

<body>
    <h2>Konfirmasi Produk</h2>
    <p>Selamat datang, <b><?php echo htmlspecialchars($nama_user); ?></b></p>

    <form method="POST" action="simpan_transaksi.php">
        <table>
            <tr>
                <th>Produk</th>
                <th>Harga</th>
                <th>Jumlah</th>
                <th>Subtotal</th>
            </tr>
            <?php foreach ($items as $item): ?>
            <tr>
                <td><?php echo htmlspecialchars($item['nama']); ?></td>
                <td>Rp<?php echo number_format($item['harga'], 2, ',', '.'); ?></td>
                <td><?php echo $item['jumlah']; ?></td>
                <td>Rp<?php echo number_format($item['subtotal'], 2, ',', '.'); ?></td>
            </tr>
            <?php endforeach; ?>
            <tr>
                <td colspan="3"><strong>Total</strong></td>
                <td><strong>Rp<?php echo number_format($total, 2, ',', '.'); ?></strong></td>
            </tr>
        </table>
        <button type="submit">Simpan Transaksi</button>
    </form>

    <br>
    <a href="../">
        <button>🔙 Kembali ke <?php echo $role === 'admin' ? "Halaman Katalog Produk" : "Halaman Katalog"; ?></button>
    </a>
</body>

</html>
