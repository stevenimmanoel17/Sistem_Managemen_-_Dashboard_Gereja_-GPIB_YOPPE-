<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "db_gpib");

$id = $_POST['id'];
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

$query = "UPDATE inventaris SET kode_barang='$kode', nama_barang='$nama', kategori='$kategori', lokasi='$lokasi', kondisi='$kondisi', penanggung_jawab='$pj', tanggal_masuk='$tgl', asal_barang='$asal', harga_beli='$harga', keterangan = '$keterangan' WHERE id='$id'";

if (mysqli_query($conn, $query)) {
header("Location: inventaris.php?status=updated");
} else {
header("Location: inventaris.php?status=error");
}
?>