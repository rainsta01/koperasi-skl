<?php
session_start();
include '../config.php';

if (!isset($_SESSION['user_id'])) {
    die("Anda harus login terlebih dahulu.");
}
$role = $_SESSION['role'];

$search = isset($_GET['search']) ? trim($_GET['search']) : "";
$kategori = isset($_GET['kategori']) ? trim($_GET['kategori']) : "";

$query = "SELECT * FROM produk WHERE 1";
$params = [];
$types = "";

if (!empty($search)) {
    $query .= " AND (nama_produk LIKE ? OR kategori LIKE ?)";
    $search_param = "%$search%";
    $params[] = &$search_param;
    $params[] = &$search_param;
    $types .= "ss";
}

$kategori_valid = ["Buku", "Aksesoris Seragam", "Alat Tulis", "Makanan", "Minuman", "Lainnya"];

if (!empty($kategori) && in_array($kategori, $kategori_valid)) {
    $query .= " AND kategori = ?";
    $params[] = &$kategori;
    $types .= "s";
}

$query .= " ORDER BY nama_produk ASC";

$stmt = $conn->prepare($query);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html>

<head>
    <title>Katalog Produk</title>
    <link rel="stylesheet" href="pagewTable.css">
    <script>
 function autoCheck(checkboxId) {
    document.getElementById(checkboxId).checked = true;
    toggleSubmit();
}

function toggleSubmit() {
    const checkboxes = document.querySelectorAll('input[type="checkbox"]');
    const button = document.getElementById('submitBtn');
    let isChecked = false;

    checkboxes.forEach(cb => {
        const productId = cb.value;
        const quantityInput = document.querySelector(`input[name="jumlah_${productId}"]`);
        
        if (cb.checked && quantityInput && parseInt(quantityInput.value) > 0) {
            isChecked = true;
        }
    });

    button.disabled = !isChecked;
}

window.onload = () => {
    const checkboxes = document.querySelectorAll('input[type="checkbox"]');
    checkboxes.forEach(cb => cb.addEventListener('change', toggleSubmit));

    const quantityInputs = document.querySelectorAll('input[type="number"]');
    quantityInputs.forEach(input => input.addEventListener('input', toggleSubmit));

    toggleSubmit();
};
    </script>
</head>

<body>
    <div class="main-content">
    <h2>Katalog Produk</h2>

    <form method="GET">
        <input type="text" name="search" placeholder="Cari produk..." value="<?php echo htmlspecialchars($search); ?>">
        <select name="kategori">
            <option value="">Semua Kategori</option>
            <?php foreach ($kategori_valid as $kat) : ?>
            <option value="<?php echo htmlspecialchars($kat); ?>" <?php if ($kategori==$kat) echo "selected" ; ?>>
                <?php echo htmlspecialchars($kat); ?>
            </option>
            <?php endforeach; ?>
        </select>
        <button type="submit">Cari</button>
    </form>

    <form method="POST" action="transaksi/">
        <table>
            <tr>
                <th>Pilih</th>
                <th>Nama Produk</th>
                <th>Kategori</th>
                <th>Harga Jual</th>
                <th>Stok</th>
                <th>Jumlah</th>
            </tr>
           <?php if ($result->num_rows > 0) : ?>
                <?php while ($row = $result->fetch_assoc()) : ?>
                    <tr>
                        <td><input type="checkbox" id="check_<?php echo $row['id']; ?>" name="produk[]" value="<?php echo $row['id']; ?>"></td>
                        <td><?php echo htmlspecialchars($row['nama_produk']); ?></td>
                        <td><?php echo htmlspecialchars($row['kategori']); ?></td>
                        <td>Rp<?php echo number_format($row['harga_jual'], 2, ',', '.'); ?></td>
                        <td><?php echo $row['stok']; ?></td>
                        <td><input type="number" name="jumlah_<?php echo $row['id']; ?>" min="1" max="<?php echo $row['stok']; ?>" oninput="autoCheck('check_<?php echo $row['id']; ?>')"></td>
                    </tr>
                <?php endwhile; ?>
            <?php else : ?>
                <tr>
                    <td colspan="6" style="text-align: center; color: red;">❌ Tidak ada produk ditemukan.</td>
                </tr>
            <?php endif; ?>
        </table>
        <button type="submit" id="submitBtn">Konfirmasi Pilihan</button>
    </form>

    <br>
    <div class="container">
        <a href="../riwayat"><button>📋 Riwayat Transaksi</button></a>
        <a href="../logout"><button class="back-btn">🚪 Logout</button></a>

        <?php if ($role === 'admin') : ?>
        <a href="../"><button>🔙 Kembali</button></a>
        <?php endif; ?>
    </div>
    </div>
</body>

</html>
