<?php
include '../../../../config.php';

if (!isset($_GET['periode'])) {
    die("Periode tidak dipilih.");
}

$periode = $_GET['periode'];
$allowed_periodes = ['harian', 'mingguan', 'bulanan', 'tahunan', 'custom'];
if (!in_array($periode, $allowed_periodes)) {
    die("Periode tidak valid.");
}

$tanggal_awal = isset($_GET['tanggal_awal']) ? $_GET['tanggal_awal'] : '';
$tanggal_akhir = isset($_GET['tanggal_akhir']) ? $_GET['tanggal_akhir'] : '';

if ($periode == 'custom' && (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal_awal) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal_akhir))) {
    die("Format tanggal tidak valid.");
}

header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
header("Content-Disposition: attachment; filename=Laporan_Penjualan_$periode.xls");
echo "\xEF\xBB\xBF"; 

$where = "";
$params = [];

if ($periode == 'harian') {
    $where = "DATE(tanggal_transaksi) = CURDATE()";
} elseif ($periode == 'mingguan') {
    $where = "YEARWEEK(tanggal_transaksi, 1) = YEARWEEK(CURDATE(), 1)";
} elseif ($periode == 'bulanan') {
    $where = "MONTH(tanggal_transaksi) = MONTH(CURDATE()) AND YEAR(tanggal_transaksi) = YEAR(CURDATE())";
} elseif ($periode == 'tahunan') {
    $where = "YEAR(tanggal_transaksi) = YEAR(CURDATE())";
} elseif ($periode == 'custom') {
    $where = "tanggal_transaksi BETWEEN ? AND ?";
    $params[] = $tanggal_awal;
    $params[] = $tanggal_akhir;
}

$query = "SELECT transaksi.id, users.nama AS nama_user, produk.nama_produk, produk.harga_jual, transaksi.jumlah, transaksi.total_harga, transaksi.tanggal_transaksi
          FROM transaksi
          JOIN users ON transaksi.user_id = users.id
          JOIN produk ON transaksi.id_produk = produk.id
          WHERE $where
          ORDER BY transaksi.tanggal_transaksi DESC";

$stmt = $conn->prepare($query);
if ($periode == 'custom') {
    $stmt->bind_param("ss", ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$total_penjualan = 0;
?>

<table border="1">
    <tr>
        <th>ID Transaksi</th>
        <th>Nama User</th>
        <th>Nama Produk</th>
        <th>Harga Satuan</th>
        <th>Jumlah</th>
        <th>Total Harga</th>
        <th>Tanggal Transaksi</th>
    </tr>
    <?php while ($row = $result->fetch_assoc()) { 
        $total_penjualan += $row['total_harga']; 
    ?>
    <tr>
        <td>
            <?php echo $row['id']; ?>
        </td>
        <td>
            <?php echo htmlspecialchars($row['nama_user']); ?>
        </td>
        <td>
            <?php echo htmlspecialchars($row['nama_produk']); ?>
        </td>
        <td>
            <?php echo $row['harga_jual']; ?>
        </td>
        <td>
            <?php echo $row['jumlah']; ?>
        </td>
        <td>
            <?php echo $row['total_harga']; ?>
        </td>
        <td>
            <?php echo $row['tanggal_transaksi']; ?>
        </td>
    </tr>
    <?php } ?>
    <tr>
        <th colspan="5">Total Penjualan</th>
        <th colspan="2">
            <?php echo $total_penjualan; ?>
        </th>
    </tr>
</table>
