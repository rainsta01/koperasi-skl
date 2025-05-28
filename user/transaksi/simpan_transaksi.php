<?php
session_start();
include '../../config.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['keranjang'])) {
    die("Akses tidak valid.");
}

$user_id = $_SESSION['user_id'];
$keranjang = $_SESSION['keranjang'];

foreach ($keranjang as $item) {
    $id_produk = $item['id'];
    $jumlah = $item['jumlah'];
    $subtotal = $item['subtotal'];

    $insert = $conn->prepare("INSERT INTO transaksi (user_id, id_produk, jumlah, total_harga) VALUES (?, ?, ?, ?)");
    $insert->bind_param("iiid", $user_id, $id_produk, $jumlah, $subtotal);
    $insert->execute();

    $update = $conn->prepare("UPDATE produk SET stok = stok - ? WHERE id = ?");
    $update->bind_param("ii", $jumlah, $id_produk);
    $update->execute();
}

unset($_SESSION['keranjang']);
header("Location: ../../riwayat");
exit();
