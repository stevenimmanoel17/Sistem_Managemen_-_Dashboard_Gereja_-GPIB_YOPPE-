<?php
session_start();
include '../koneksi.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id    = mysqli_real_escape_string($conn, $_POST['id']);
    $nama  = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $role = $_POST['role'];
    $status = $_POST['status'];

    $query = "UPDATE users SET 
              nama_lengkap = '$nama', 
              email = '$email', 
              role = '$role', 
              status = '$status' 
              WHERE id = '$id'";

    if (mysqli_query($conn, $query)) {
        echo "success";
    } else {
        echo "error";
    }
}
?>