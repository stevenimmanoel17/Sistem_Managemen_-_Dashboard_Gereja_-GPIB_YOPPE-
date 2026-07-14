<?php
session_start();
include '../koneksi.php';

// Proteksi: Hanya Super Admin yang bisa hapus
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'Super Admin') {
    header("Location: ../index.php");
    exit;
}

if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    $current_user_id = $_SESSION['user_id']; // Pastikan ID login disimpan di session saat login

    // Jangan izinkan hapus diri sendiri
    if ($id == $current_user_id) {
        header("Location: pengaturan.php?status=self_delete_error");
        exit;
    }

    $query = "DELETE FROM users WHERE id = '$id'";

    if (mysqli_query($conn, $query)) {
        echo "success";
    } else {
        echo "error";
    }
}
?>