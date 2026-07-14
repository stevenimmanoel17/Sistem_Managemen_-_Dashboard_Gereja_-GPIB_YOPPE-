<?php
session_start();

// Koneksi ke Database
$conn = mysqli_connect("localhost", "root", "", "db_gpib");

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil dan bersihkan data dari form
    $token = mysqli_real_escape_string($conn, $_POST['token']);
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Cek apakah token kosong
    if (empty($token)) {
        echo "<script>
                alert('Akses ilegal! Token tidak ditemukan.');
                window.location.href = '../login/Login.php';
              </script>";
        exit;
    }

    // Validasi: Cek apakah password cocok
    if ($new_password !== $confirm_password) {
        echo "<!DOCTYPE html>
        <html>
        <head>
            <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
            <link href='https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap' rel='stylesheet'>
            <style> body {  font-family: 'Poppins', sans-serif;
                            background-image: url('../icon/Bluebg.jpg');
                            background-size: cover;             
                            background-position: center;        
                            background-repeat: no-repeat;       
                            background-attachment: fixed;     
                         } </style>
        </head>
        <body>
            <script>
                Swal.fire({
                    icon: 'error',
                    title: 'Password Tidak Cocok',
                    text: 'Konfirmasi password tidak sama. Silakan ulangi kembali.',
                    confirmButtonColor: '#2392ED'
                }).then(() => {
                    window.history.back();
                });
            </script>
        </body>
        </html>";
        exit;
    }

    // Validasi: Cek panjang password minimal 8 karakter
    if (strlen($new_password) < 8) {
        echo "<!DOCTYPE html>
        <html>
        <head>
            <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
            <link href='https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap' rel='stylesheet'>
            <style> body {  font-family: 'Poppins', sans-serif;
                            background-image: url('../icon/Bluebg.jpg');
                            background-size: cover;             
                            background-position: center;        
                            background-repeat: no-repeat;       
                            background-attachment: fixed;     
                         } </style>
        </head>
        <body>
            <script>
                Swal.fire({
                    icon: 'warning',
                    title: 'Password Terlalu Pendek',
                    text: 'Demi keamanan, password minimal harus 8 karakter!',
                    confirmButtonColor: '#2392ED'
                }).then(() => {
                    window.history.back();
                });
            </script>
        </body>
        </html>";
        exit;
    }

    // Enkripsi password menggunakan BCRYPT
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

    // Update password ke tabel 'users' berdasarkan token
    $query = "UPDATE users SET password = '$hashed_password' WHERE reset_token = '$token'";
    
    if (mysqli_query($conn, $query)) {
        // Hapus token agar tidak bisa digunakan kembali
        mysqli_query($conn, "UPDATE users SET reset_token = NULL WHERE reset_token = '$token'");

        echo "<!DOCTYPE html>
        <html>
        <head>
            <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
            <link href='https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap' rel='stylesheet'>
                        <style> body {  font-family: 'Poppins', sans-serif;
                            background-image: url('../icon/Bluebg.jpg');
                            background-size: cover;             
                            background-position: center;        
                            background-repeat: no-repeat;       
                            background-attachment: fixed;     
                         } </style>
        </head>
        <body>
            <script>
                Swal.fire({
                    icon: 'success',
                    title: 'Password Diperbarui!',
                    text: 'Kata sandi Anda berhasil diubah. Silakan login kembali.',
                    showConfirmButton: false,
                    timer: 1500,
                    timerProgressBar: true,
                    didOpen: (toast) => {
                        toast.querySelector('.swal2-timer-progress-bar').style.backgroundColor = '#2ecc71';
                    }
                }).then(() => {
                    // Keluar satu folder untuk menemukan folder login
                    window.location.href = '../login/Login.php';
                });
            </script>
        </body>
        </html>";
        exit;
    } else {
        // Penanganan error sistem
        echo "<!DOCTYPE html>
        <html>
        <head>
            <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
            <link href='https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap' rel='stylesheet'>
            <style> body {  font-family: 'Poppins', sans-serif;
                            background-image: url('../icon/Bluebg.jpg');
                            background-size: cover;             
                            background-position: center;        
                            background-repeat: no-repeat;       
                            background-attachment: fixed;     
                         } </style>
        </head>
        <body>
            <script>
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Perbarui Password',
                    text: 'Terjadi kesalahan sistem. Silakan coba beberapa saat lagi.',
                    confirmButtonColor: '#2392ED'
                }).then(() => {
                    window.history.back();
                });
            </script>
        </body>
        </html>";
    }
} else {
    // Jika mencoba akses file langsung tanpa POST
    header("Location: ../login/Login.php");
    exit;
}
?>