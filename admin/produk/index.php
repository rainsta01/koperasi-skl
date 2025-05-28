<?php
session_start();
include '../../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    die("Akses ditolak. Anda bukan admin.");
}

$search = isset($_GET['search']) ? trim($_GET['search']) : "";
$kategori = isset($_GET['kategori']) ? trim($_GET['kategori']) : "";

$query = "SELECT * FROM produk WHERE 1";

$params = [];
$types = "";

if (!empty($search)) {
    $query .= " AND (nama_produk LIKE ? OR kategori LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $types .= "ss";
}

if (!empty($kategori) && $kategori != "Semua") {
    $query .= " AND kategori = ?";
    $params[] = $kategori;
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
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta http-equiv="X-Frame-Options" content="DENY">
    <meta http-equiv="Content-Security-Policy" content="default-src 'self'; script-src 'self' https://cdn.tailwindcss.com; style-src 'self' https://cdnjs.cloudflare.com 'unsafe-inline'; font-src 'self' https://cdnjs.cloudflare.com;">
    <title>Data Produk</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .floating-shape {
            position: absolute;
            pointer-events: none;
            transition: all 0.3s ease;
        }
        @keyframes float {
            0% { transform: translate(0, 0) rotate(0deg); }
            50% { transform: translate(15px, -15px) rotate(180deg); }
            100% { transform: translate(0, 0) rotate(360deg); }
        }
        .animate-float {
            animation: float 8s infinite ease-in-out;
        }
    </style>
</head>

<body class="bg-gray-100 min-h-screen p-8">
    <div class="floating-shape w-16 h-16 bg-indigo-200 rounded-lg animate-float" style="top: 20%; left: 20%; animation-delay: 0s;"></div>
    <div class="floating-shape w-12 h-12 bg-pink-200 rounded-full animate-float" style="top: 60%; right: 25%; animation-delay: 1s;"></div>
    <div class="floating-shape w-10 h-10 bg-green-200 rounded-lg animate-float" style="bottom: 20%; left: 30%; animation-delay: 3s;"></div>

    <div class="max-w-7xl mx-auto bg-white rounded-xl shadow-lg p-8 relative z-10 backdrop-blur-sm bg-opacity-90">
        <h2 class="text-3xl font-bold text-gray-900 mb-8">Data Produk</h2>

        <form method="GET" class="flex gap-4 mb-6">
            <input type="text" name="search" placeholder="Cari produk..." value="<?php echo htmlspecialchars($search); ?>"
                class="flex-1 px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <select name="kategori" 
                class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="Semua">Semua Kategori</option>
                <?php
                $kategori_list = ["Buku", "Aksesoris Seragam", "Alat Tulis", "Makanan", "Minuman", "Lainnya"];
                foreach ($kategori_list as $kat) {
                    echo "<option value=\"$kat\" " . ($kategori == $kat ? "selected" : "") . ">$kat</option>";
                }
                ?>
            </select>
            <button type="submit" 
                class="px-6 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Cari
            </button>
        </form>

        <a href="tambah_produk">
            <button class="mb-6 px-6 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                Tambah Produk
            </button>
        </a>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Produk</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kategori</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Harga Jual</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stok</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php while ($row = $result->fetch_assoc()) { ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($row['id']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($row['nama_produk']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($row['kategori']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap">Rp <?php echo number_format($row['harga_jual'], 2, ',', '.'); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($row['stok']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <a href="edit_produk?id=<?php echo $row['id']; ?>" 
                                class="text-indigo-600 hover:text-indigo-900 mr-3">
                                <button class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Edit</button>
                            </a>
                            <a href="hapus_produk?id=<?php echo $row['id']; ?>" 
                                onclick="return confirm('Yakin ingin menghapus produk ini?')"
                                class="text-red-600 hover:text-red-900">
                                <button class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">Hapus</button>
                            </a>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            <a href="../">
                <button class="px-6 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                    🔙 Kembali ke Halaman Admin
                </button>
            </a>
        </div>
    </div>
</body>

</html>
