<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "db_gpib");

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Tangkap data dari form
$no_kk        = $_POST['no_kk'];
$posisi       = $_POST['posisi'];
$nik          = $_POST['nik'];
$nama_lengkap = $_POST['nama'];
$gender       = $_POST['gender'];
$tmpt_lahir   = $_POST['tmpt_lahir'];
$tgl_lahir    = $_POST['tgl_lahir'];
$status_nikah = $_POST['status_nikah'];
$sektor       = $_POST['sektor'];
$status_aktif = $_POST['status_aktif'];
$status       = isset($_POST['is_non_aktif']) ? 'Non-Aktif' : $status_aktif;
$alamat       = $_POST['alamat'];
$no_hp        = $_POST['no_hp'];
$pelkat       = $_POST['pelkat'];
$status_baptis = $_POST['status_baptis'];
$tempat_baptis = $_POST['tempat_baptis'];
$tgl_baptis    = $_POST['tgl_baptis'];
$status_sidi   = $_POST['status_sidi'];
$tempat_sidi   = $_POST['tempat_sidi'];
$tgl_sidi      = $_POST['tgl_sidi'];

// Masukkan ke database (Pastikan kolom 'status' sudah ada di tabel jemaat)
$query = "INSERT INTO jemaat (no_kk, posisi, nik, nama_lengkap, gender, tmpt_lahir, tgl_lahir, status_nikah, status_baptis, tempat_baptis, tgl_baptis, status_sidi, tempat_sidi, tgl_sidi, sektor, status, alamat, no_hp, pelkat) 
          VALUES ('$no_kk', '$posisi', '$nik', '$nama_lengkap', '$gender', '$tmpt_lahir', '$tgl_lahir', '$status_nikah', '$status_baptis', '$tempat_baptis', '$tgl_baptis', '$status_sidi', '$tempat_sidi', '$tgl_sidi', '$sektor', '$status', '$alamat', '$no_hp', '$pelkat')";
                    
if (mysqli_query($conn, $query)) {
    echo "success";
} else {
    echo "error";
}

mysqli_close($conn);
?>