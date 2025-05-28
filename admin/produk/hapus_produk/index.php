<?php
session_start();
include '../../../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    die("Akses ditolak. Anda bukan admin.");
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    $check_stmt = $conn->prepare("SELECT * FROM produk WHERE id = ?");
    $check_stmt->bind_param("i", $id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();

    if ($result->num_rows > 0) {
        $produk = $result->fetch_assoc();
        $data_lama = json_encode(["nama_produk" => $produk['nama_produk'], "kategori" => $produk['kategori'], "harga_beli" => $produk['harga_beli'], "harga_jual" => $produk['harga_jual'], "stok" => $produk['stok']]);

        $stmt = $conn->prepare("DELETE FROM produk WHERE id = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            $log_stmt = $conn->prepare("INSERT INTO log_perubahan (user_id, aksi, tabel, data_lama) VALUES (?, 'DELETE', 'produk', ?)");
            $log_stmt->bind_param("is", $_SESSION['user_id'], $data_lama);
            $log_stmt->execute();

            header("Location: ../?msg=Produk berhasil dihapus!");
            exit();
        } else {
            echo "Gagal menghapus produk!";
        }
    } else {
        echo "Produk tidak ditemukan!";
    }
} else {
    echo "ID produk tidak valid!";
}
?>
