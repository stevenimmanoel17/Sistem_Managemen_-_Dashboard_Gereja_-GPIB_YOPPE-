<?php
session_start();
include '../koneksi.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama   = mysqli_real_escape_string($conn, $_POST['nama_gereja']);
    $alamat = mysqli_real_escape_string($conn, $_POST['alamat']);
    
    // Ambil data logo lama dari database
    $old_res  = mysqli_query($conn, "SELECT logo FROM profil_gereja WHERE id=1");
    $old_data = mysqli_fetch_assoc($old_res);
    $logo_path = $old_data['logo'];

    // Cek apakah ada file baru yang diunggah
    if (!empty($_FILES['logo_baru']['name'])) {
        $nama_file = $_FILES['logo_baru']['name'];
        $tmp_file  = $_FILES['logo_baru']['tmp_name'];
        $ekstensi  = pathinfo($nama_file, PATHINFO_EXTENSION);
        
        // Buat nama unik agar tidak bentrok
        $baru_nama = "logo_" . time() . "." . $ekstensi;
        $tujuan    = "../icon/" . $baru_nama;

        if (move_uploaded_file($tmp_file, $tujuan)) {
            // HAPUS FILE LAMA: Jika logo lama bukan logo default, hapus dari folder
            if (!empty($old_data['logo']) && $old_data['logo'] != "icon/GPIB-NoCapt.png") {
                $file_lama = "../" . $old_data['logo'];
                if (file_exists($file_lama)) {
                    unlink($file_lama); // Perintah hapus file di PHP
                }
            }
            $logo_path = "icon/" . $baru_nama; 
        }
    }

    // Update database dengan path baru
    $query = "UPDATE profil_gereja SET 
              nama_gereja = '$nama', 
              alamat = '$alamat', 
              logo = '$logo_path' 
              WHERE id = 1";

    if (mysqli_query($conn, $query)) {
        echo "success";
    } else {
        echo "error";
    }
}
?>