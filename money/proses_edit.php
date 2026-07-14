<?php
session_start();
include '../koneksi.php'; 

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = mysqli_real_escape_string($conn, $_POST['id']);
    $tanggal = mysqli_real_escape_string($conn, $_POST['tanggal']);
    $tipe = mysqli_real_escape_string($conn, $_POST['tipe']);
    $kategori = mysqli_real_escape_string($conn, $_POST['kategori']);
    $nominal = mysqli_real_escape_string($conn, $_POST['nominal']);
    $keterangan = mysqli_real_escape_string($conn, $_POST['keterangan']);

    $query = "UPDATE keuangan SET 
              tanggal = '$tanggal', 
              tipe = '$tipe', 
              kategori = '$kategori', 
              nominal = '$nominal', 
              keterangan = '$keterangan' 
              WHERE id = '$id'";

    if (mysqli_query($conn, $query)) {
        // Alihkan kembali ke money.php dengan status sukses
        echo "success"; 
    } else {
        echo "error";
    }
}
?>