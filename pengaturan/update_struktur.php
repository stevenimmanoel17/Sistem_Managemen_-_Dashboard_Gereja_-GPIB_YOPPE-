<?php
session_start();
include '../koneksi.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $jabatan1 = mysqli_real_escape_string($conn, $_POST['jabatan1']);
    $nama1    = mysqli_real_escape_string($conn, $_POST['nama1']);
    
    $jabatan2 = mysqli_real_escape_string($conn, $_POST['jabatan2']);
    $nama2    = mysqli_real_escape_string($conn, $_POST['nama2']);
    
    $jabatan3 = mysqli_real_escape_string($conn, $_POST['jabatan3']);
    $nama3    = mysqli_real_escape_string($conn, $_POST['nama3']);

    $query = "UPDATE struktur_organisasi SET 
              jabatan1 = '$jabatan1', nama1 = '$nama1', 
              jabatan2 = '$jabatan2', nama2 = '$nama2', 
              jabatan3 = '$jabatan3', nama3 = '$nama3' 
              WHERE id = 1";

    if (mysqli_query($conn, $query)) {
        echo "success";
    } else {
        echo "error";
    }
}
?>