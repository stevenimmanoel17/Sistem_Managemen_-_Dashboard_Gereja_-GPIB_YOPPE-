<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "db_gpib");

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

$id           = $_POST['id'];
$no_kk        = $_POST['no_kk'];
$posisi       = $_POST['posisi'];
$nik          = $_POST['nik'];
$nama_lengkap = $_POST['nama'];
$gender       = $_POST['gender'];
$tmpt_lahir   = $_POST['tmpt_lahir'];
$tgl_lahir    = $_POST['tgl_lahir'];
$pelkat       = $_POST['pelkat'];
$no_hp        = $_POST['no_hp'];
$status_nikah = $_POST['status_nikah'];
$sektor       = $_POST['sektor'];
$alamat       = $_POST['alamat'];
$status_baptis = $_POST['status_baptis'];
$tempat_baptis = $_POST['tempat_baptis'];
$tgl_baptis    = $_POST['tgl_baptis'];
$status_sidi   = $_POST['status_sidi'];
$tempat_sidi   = $_POST['tempat_sidi'];
$tgl_sidi      = $_POST['tgl_sidi'];

// Logika: Prioritas Checkbox > Dropdown
if (isset($_POST['is_non_aktif'])) {
    $status_final = 'Non-Aktif';
} else {
    // Jika dropdown tidak dipilih, nilainya adalah 'None'
    $status_final = $_POST['status_aktif'] ?? 'None'; 
}

// Pastikan query menggunakan $status_final
$query = "UPDATE jemaat SET ..., status = '$status_final' WHERE id = '$id'";

$query = "UPDATE jemaat SET 
            no_kk = '$no_kk', 
            posisi = '$posisi', 
            nik = '$nik', 
            nama_lengkap = '$nama_lengkap', 
            gender = '$gender', 
            tmpt_lahir = '$tmpt_lahir', 
            tgl_lahir = '$tgl_lahir', 
            pelkat = '$pelkat', 
            no_hp = '$no_hp', 
            status_nikah = '$status_nikah',
            status_baptis = '$status_baptis',
            tempat_baptis = '$tempat_baptis',
            tgl_baptis = '$tgl_baptis',
            status_sidi = '$status_sidi',
            tempat_sidi = '$tempat_sidi',
            tgl_sidi = '$tgl_sidi',
            sektor = '$sektor', 
            status = '$status_final',
            alamat = '$alamat' 
          WHERE id = '$id'";
          
if (mysqli_query($conn, $query)) {
    echo "success";
} else {
    echo "error";
}

mysqli_close($conn);
?>