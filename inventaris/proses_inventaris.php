<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "db_gpib");

$kode = $_POST['kode_barang'];
$nama = $_POST['nama_barang'];
$kategori = $_POST['kategori'];
$lokasi = $_POST['lokasi'];
$kondisi = $_POST['kondisi'];
$pj = $_POST['penanggung_jawab'];
$tgl = $_POST['tanggal_masuk'];
$asal = $_POST['asal_barang'];
$harga = $_POST['harga_beli'];

$keterangan      = mysqli_real_escape_string($conn, $_POST['keterangan']);

$query = "INSERT INTO inventaris (kode_barang, nama_barang, kategori, lokasi, kondisi, penanggung_jawab, tanggal_masuk, asal_barang, harga_beli, keterangan) VALUES ('$kode', '$nama', '$kategori', '$lokasi', '$kondisi', '$pj', '$tgl', '$asal', '$harga', '$keterangan')";

if (mysqli_query($conn, $query)) {
header("Location: inventaris.php?status=success");
} else {
header("Location: inventaris.php?status=error");
}
?>