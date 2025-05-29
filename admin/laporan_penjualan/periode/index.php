<?php
session_start();
include '../../../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    die("Akses ditolak. Anda bukan admin.");
}

$periode = isset($_GET['periode']) ? $_GET['periode'] : 'harian';

$allowed_periodes = ['harian', 'mingguan', 'bulanan', 'tahunan', 'custom'];
if (!in_array($periode, $allowed_periodes)) {
    die("Periode tidak valid.");
}

$tanggal_awal = isset($_GET['tanggal_awal']) ? $_GET['tanggal_awal'] : '';
$tanggal_akhir = isset($_GET['tanggal_akhir']) ? $_GET['tanggal_akhir'] : '';

if ($periode == 'custom' && (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal_awal) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal_akhir))) {
    die("Format tanggal tidak valid.");
}

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

<!DOCTYPE html>
<html>

<head>
    <title>Laporan Penjualan Berdasarkan Periode</title>
    <link rel="stylesheet" href="pagewTable.css">
    <!-- <style> 
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
    </style>-->
    <script>
        function printReport() {
            window.print();
        }
    </script>
</head>

<body>
<nav class="sidebar">
        <ul>
            <li>
                <a href="../../">
                <svg width="20px" height="20px" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M1 6V15H6V11C6 9.89543 6.89543 9 8 9C9.10457 9 10 9.89543 10 11V15H15V6L8 0L1 6Z" fill="#000000"></path> </g></svg>
                    <span>Home</span>
                </a>
            </li>
            <li>
                <a href="../../user_management">
                <svg width="20px" height="20px" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M8 7C9.65685 7 11 5.65685 11 4C11 2.34315 9.65685 1 8 1C6.34315 1 5 2.34315 5 4C5 5.65685 6.34315 7 8 7Z" fill="#000000"></path> <path d="M14 12C14 10.3431 12.6569 9 11 9H5C3.34315 9 2 10.3431 2 12V15H14V12Z" fill="#000000"></path> </g></svg>
                    <span>User Management</span>
                </a>
            </li>
            <li>
                <a href="../../laporan_penjualan">
                <svg width="20px" height="20px" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M16 1H12V15H16V1Z" fill="#000000"></path> <path d="M6 5H10V15H6V5Z" fill="#000000"></path> <path d="M0 9H4V15H0V9Z" fill="#000000"></path> </g></svg>
                    <span>Penjualan</span>
                </a>
            </li>
            <li>
                <a href="../../log_perubahan/">
                <svg width="25px" height="25px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path fill-rule="evenodd" clip-rule="evenodd" d="M5.07868 5.06891C8.87402 1.27893 15.0437 1.31923 18.8622 5.13778C22.6824 8.95797 22.7211 15.1313 18.9262 18.9262C15.1312 22.7211 8.95793 22.6824 5.13774 18.8622C2.87389 16.5984 1.93904 13.5099 2.34047 10.5812C2.39672 10.1708 2.775 9.88377 3.18537 9.94002C3.59575 9.99627 3.88282 10.3745 3.82658 10.7849C3.4866 13.2652 4.27782 15.881 6.1984 17.8016C9.44288 21.0461 14.6664 21.0646 17.8655 17.8655C21.0646 14.6664 21.046 9.44292 17.8015 6.19844C14.5587 2.95561 9.33889 2.93539 6.13935 6.12957L6.88705 6.13333C7.30126 6.13541 7.63535 6.47288 7.63327 6.88709C7.63119 7.3013 7.29372 7.63539 6.87951 7.63331L4.33396 7.62052C3.92269 7.61845 3.58981 7.28556 3.58774 6.8743L3.57495 4.32874C3.57286 3.91454 3.90696 3.57707 4.32117 3.57498C4.73538 3.5729 5.07285 3.907 5.07493 4.32121L5.07868 5.06891Z" fill="#000000"></path> <path opacity="0.5" d="M12 7.25C12.4142 7.25 12.75 7.58579 12.75 8V11.6893L15.0303 13.9697C15.3232 14.2626 15.3232 14.7374 15.0303 15.0303C14.7374 15.3232 14.2626 15.3232 13.9697 15.0303L11.5429 12.6036C11.3554 12.416 11.25 12.1617 11.25 11.8964V8C11.25 7.58579 11.5858 7.25 12 7.25Z" fill="#000000"></path> </g></svg>
                    <span>Histori</span>
                </a>
            </li>
            <li>
                <a href="../../../user">
                <svg width="25px" height="25px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M3 9H21M7 15H9M6.2 19H17.8C18.9201 19 19.4802 19 19.908 18.782C20.2843 18.5903 20.5903 18.2843 20.782 17.908C21 17.4802 21 16.9201 21 15.8V8.2C21 7.0799 21 6.51984 20.782 6.09202C20.5903 5.71569 20.2843 5.40973 19.908 5.21799C19.4802 5 18.9201 5 17.8 5H6.2C5.0799 5 4.51984 5 4.09202 5.21799C3.71569 5.40973 3.40973 5.71569 3.21799 6.09202C3 6.51984 3 7.07989 3 8.2V15.8C3 16.9201 3 17.4802 3.21799 17.908C3.40973 18.2843 3.71569 18.5903 4.09202 18.782C4.51984 19 5.07989 19 6.2 19Z" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path> </g></svg>
                    <span>Transaksi</span>
                </a>
            </li>
            <li>
                <a href="../">
                <svg width="25px" height="25px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M16.19 2H7.81C4.17 2 2 4.17 2 7.81V16.18C2 19.83 4.17 22 7.81 22H16.18C19.82 22 21.99 19.83 21.99 16.19V7.81C22 4.17 19.83 2 16.19 2ZM13.92 16.13H9C8.59 16.13 8.25 15.79 8.25 15.38C8.25 14.97 8.59 14.63 9 14.63H13.92C15.2 14.63 16.25 13.59 16.25 12.3C16.25 11.01 15.21 9.97 13.92 9.97H8.85L9.11 10.23C9.4 10.53 9.4 11 9.1 11.3C8.95 11.45 8.76 11.52 8.57 11.52C8.38 11.52 8.19 11.45 8.04 11.3L6.47 9.72C6.18 9.43 6.18 8.95 6.47 8.66L8.04 7.09C8.33 6.8 8.81 6.8 9.1 7.09C9.39 7.38 9.39 7.86 9.1 8.15L8.77 8.48H13.92C16.03 8.48 17.75 10.2 17.75 12.31C17.75 14.42 16.03 16.13 13.92 16.13Z" fill="#292D32"></path> </g></svg>
                    <span>Back</span>
                </a>
            </li>
        </ul>
    </nav>
    <div class="main-content">
    <h2>Laporan Penjualan Berdasarkan Periode</h2>
    <br>

    <button class="btn-print" onclick="printReport()"><svg width="15px" height="15px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M18 16.75H16C15.8011 16.75 15.6103 16.671 15.4697 16.5303C15.329 16.3897 15.25 16.1989 15.25 16C15.25 15.8011 15.329 15.6103 15.4697 15.4697C15.6103 15.329 15.8011 15.25 16 15.25H18C18.3315 15.25 18.6495 15.1183 18.8839 14.8839C19.1183 14.6495 19.25 14.3315 19.25 14V10C19.25 9.66848 19.1183 9.35054 18.8839 9.11612C18.6495 8.8817 18.3315 8.75 18 8.75H6C5.66848 8.75 5.35054 8.8817 5.11612 9.11612C4.8817 9.35054 4.75 9.66848 4.75 10V14C4.75 14.3315 4.8817 14.6495 5.11612 14.8839C5.35054 15.1183 5.66848 15.25 6 15.25H8C8.19891 15.25 8.38968 15.329 8.53033 15.4697C8.67098 15.6103 8.75 15.8011 8.75 16C8.75 16.1989 8.67098 16.3897 8.53033 16.5303C8.38968 16.671 8.19891 16.75 8 16.75H6C5.27065 16.75 4.57118 16.4603 4.05546 15.9445C3.53973 15.4288 3.25 14.7293 3.25 14V10C3.25 9.27065 3.53973 8.57118 4.05546 8.05546C4.57118 7.53973 5.27065 7.25 6 7.25H18C18.7293 7.25 19.4288 7.53973 19.9445 8.05546C20.4603 8.57118 20.75 9.27065 20.75 10V14C20.75 14.7293 20.4603 15.4288 19.9445 15.9445C19.4288 16.4603 18.7293 16.75 18 16.75Z" fill="#ffffff"></path> <path d="M16 8.75C15.8019 8.74741 15.6126 8.66756 15.4725 8.52747C15.3324 8.38737 15.2526 8.19811 15.25 8V4.75H8.75V8C8.75 8.19891 8.67098 8.38968 8.53033 8.53033C8.38968 8.67098 8.19891 8.75 8 8.75C7.80109 8.75 7.61032 8.67098 7.46967 8.53033C7.32902 8.38968 7.25 8.19891 7.25 8V4.5C7.25 4.16848 7.3817 3.85054 7.61612 3.61612C7.85054 3.3817 8.16848 3.25 8.5 3.25H15.5C15.8315 3.25 16.1495 3.3817 16.3839 3.61612C16.6183 3.85054 16.75 4.16848 16.75 4.5V8C16.7474 8.19811 16.6676 8.38737 16.5275 8.52747C16.3874 8.66756 16.1981 8.74741 16 8.75Z" fill="#ffffff"></path> <path d="M15.5 20.75H8.5C8.16848 20.75 7.85054 20.6183 7.61612 20.3839C7.3817 20.1495 7.25 19.8315 7.25 19.5V12.5C7.25 12.1685 7.3817 11.8505 7.61612 11.6161C7.85054 11.3817 8.16848 11.25 8.5 11.25H15.5C15.8315 11.25 16.1495 11.3817 16.3839 11.6161C16.6183 11.8505 16.75 12.1685 16.75 12.5V19.5C16.75 19.8315 16.6183 20.1495 16.3839 20.3839C16.1495 20.6183 15.8315 20.75 15.5 20.75ZM8.75 19.25H15.25V12.75H8.75V19.25Z" fill="#ffffff"></path> </g></svg> Cetak Laporan</button>
    <button class="btn-excel" onclick="window.location.href='export_excel?periode=<?php echo htmlspecialchars($periode); ?>'"><svg width="15px" height="15px" viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg" fill="#000000"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"><title>file_type_excel2</title><path d="M28.781,4.405H18.651V2.018L2,4.588V27.115l16.651,2.868V26.445H28.781A1.162,1.162,0,0,0,30,25.349V5.5A1.162,1.162,0,0,0,28.781,4.405Zm.16,21.126H18.617L18.6,23.642h2.487v-2.2H18.581l-.012-1.3h2.518v-2.2H18.55l-.012-1.3h2.549v-2.2H18.53v-1.3h2.557v-2.2H18.53v-1.3h2.557v-2.2H18.53v-2H28.941Z" style="fill:#ffffff;fill-rule:evenodd"></path><rect x="22.487" y="7.439" width="4.323" height="2.2" style="fill:#ffffff"></rect><rect x="22.487" y="10.94" width="4.323" height="2.2" style="fill:#ffffff"></rect><rect x="22.487" y="14.441" width="4.323" height="2.2" style="fill:#ffffff"></rect><rect x="22.487" y="17.942" width="4.323" height="2.2" style="fill:#ffffff"></rect><rect x="22.487" y="21.443" width="4.323" height="2.2" style="fill:#ffffff"></rect><polygon points="6.347 10.673 8.493 10.55 9.842 14.259 11.436 10.397 13.582 10.274 10.976 15.54 13.582 20.819 11.313 20.666 9.781 16.642 8.248 20.513 6.163 20.329 8.585 15.666 6.347 10.673" style="fill:#ffffff;fill-rule:evenodd"></polygon></g></svg> Ekspor ke Excel</button>

    <br><br>
    <form method="GET">
        <label>Pilih Periode:</label>
        <select name="periode" onchange="this.form.submit()">
            <option value="harian" <?php if ($periode=='harian' ) echo 'selected' ; ?>>Harian</option>
            <option value="mingguan" <?php if ($periode=='mingguan' ) echo 'selected' ; ?>>Mingguan</option>
            <option value="bulanan" <?php if ($periode=='bulanan' ) echo 'selected' ; ?>>Bulanan</option>
            <option value="tahunan" <?php if ($periode=='tahunan' ) echo 'selected' ; ?>>Tahunan</option>
            <option value="custom" <?php if ($periode=='custom' ) echo 'selected' ; ?>>Custom</option>
        </select>

        <?php if ($periode == 'custom') { ?>
        <label>Dari:</label>
        <input type="date" name="tanggal_awal" value="<?php echo htmlspecialchars($tanggal_awal); ?>" required>
        <label>Hingga:</label>
        <input type="date" name="tanggal_akhir" value="<?php echo htmlspecialchars($tanggal_akhir); ?>" required>
        <button type="submit">Tampilkan</button>
        <?php } ?>
    </form>

    <br>

    <table border="1">
        <tr>
            <th>ID Transaksi</th>
            <th>Nama User</th>
            <th>Nama Produk</th>
            <th>Harga</th>
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
            <td>Rp
                <?php echo number_format($row['harga_jual'], 2, ',', '.'); ?>
            </td>
            <td>
                <?php echo $row['jumlah']; ?>
            </td>
            <td>Rp
                <?php echo number_format($row['total_harga'], 2, ',', '.'); ?>
            </td>
            <td>
                <?php echo $row['tanggal_transaksi']; ?>
            </td>
        </tr>
        <?php } ?>
        <tr>
            <th colspan="5">Total Penjualan</th>
            <th colspan="2">Rp
                <?php echo number_format($total_penjualan, 2, ',', '.'); ?>
            </th>
        </tr>
    </table>

    <br>
    </div>
</body>

</html>
