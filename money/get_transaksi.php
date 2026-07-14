<?php
include '../koneksi.php';

$id = $_GET['id'];
$query = mysqli_query($conn, "SELECT * FROM keuangan WHERE id = '$id'");
$data = mysqli_fetch_assoc($query);

// Mengirimkan data dalam format JSON agar bisa dibaca JavaScript
echo json_encode($data);
?>