<?php

include 'config.php';

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

$nama = "Super Admin";
$username = "superadmin";
$password = "admin123";
$role = "admin";

$check_query = "SELECT id FROM users WHERE username = ?";

$stmt = $conn->prepare($check_query);
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    echo "Username sudah digunakan!";
} else {
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $insert_query = "INSERT INTO users (nama, username, password, role) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($insert_query);
    $stmt->bind_param("ssss", $nama, $username, $hashed_password, $role);

    if ($stmt->execute()) {
        echo "User berhasil ditambahkan!";
    } else {
        echo "Error: " . $stmt->error;
    }
}

$stmt->close();
$conn->close();
session_start();
$timeout = 1800;

if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > $timeout)) {
    session_unset();
    session_destroy();
    header("Location: login");
    exit();
}

$_SESSION['LAST_ACTIVITY'] = time();

if (!isset($_SESSION['user_id'])) {
    header("Location: login");
} else {
    $redirect_url = ($_SESSION['role'] == 'admin') ? 'admin' : 'user';
    header("Location: $redirect_url");
}
exit();
?>
