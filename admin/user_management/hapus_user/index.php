<?php
session_start();
include '../../../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    die("Akses ditolak. Anda bukan admin.");
}

if (isset($_GET['id'])) {
    $user_id = htmlspecialchars($_GET['id']);

    if ($user_id == $_SESSION['user_id']) {
        die("Anda tidak bisa menghapus akun Anda sendiri!");
    }

    $check_stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $check_stmt->bind_param("i", $user_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();

    if ($result->num_rows > 0) {
        $user_data = $result->fetch_assoc();
        $data_lama = json_encode(["nama" => $user_data['nama'], "username" => $user_data['username'], "role" => $user_data['role']]);

        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);

        if ($stmt->execute()) {
            $log_stmt = $conn->prepare("INSERT INTO log_perubahan (user_id, aksi, tabel, data_lama) VALUES (?, 'DELETE', 'users', ?)");
            $log_stmt->bind_param("is", $_SESSION['user_id'], $data_lama);
            $log_stmt->execute();

            header("Location: ../?msg=User berhasil dihapus!");
            exit();
        } else {
            echo "Gagal menghapus user!";
        }
    } else {
        echo "User tidak ditemukan!";
    }
}
?>
