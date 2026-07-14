<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "db_gpib");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = mysqli_real_escape_string($conn, $_POST['email']);

    // Cek apakah email terdaftar di database
    $query = mysqli_query($conn, "SELECT * FROM users WHERE email = '$email'");
    
    if (mysqli_num_rows($query) > 0) {
        // Buat token unik
        $token = bin2hex(random_bytes(16)); // Menghasilkan 32 karakter acak

        // Simpan token ke database
        $update = "UPDATE users SET reset_token = '$token' WHERE email = '$email'";
        
if (mysqli_query($conn, $update)) {
    // Gunakan JavaScript untuk pindah halaman agar tidak putih polos
    echo "<!DOCTYPE html>
        <html>
        <head>
            <link rel='icon' type='image/png' href='../icon/GPIB-NoCapt.png'>
            <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
            <link href='https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap' rel='stylesheet'>
            <style> body {      font-family: 'Poppins', sans-serif; 
                                background-image: url('../icon/Bluebg.jpg');
                                background-size: cover;             
                                background-position: center;        
                                background-repeat: no-repeat;       
                                background-attachment: fixed;  
                         }
            </style>
        </head>
        <body>
            <script>
                Swal.fire({
                    icon: 'success',
                    title: 'Permintaan Berhasil!',
                    text: 'Instruksi pemulihan telah dibuat. Menuju halaman reset...',
                    showConfirmButton: false,
                    timer: 2500,
                    timerProgressBar: true,
                    didOpen: (toast) => {
                        toast.querySelector('.swal2-timer-progress-bar').style.backgroundColor = '#2ecc71';
                    }
                }).then(() => {
                    window.location.href = 'reset_password.php?token=$token';
                });
            </script>
        </body>
        </html>";
    exit;
}
    } else {
        // Jika email tidak ditemukan
        echo "<!DOCTYPE html>
    <html>
    <head>
        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        <link rel='icon' type='image/png' href='../icon/GPIB-NoCapt.png'>
        <link href='https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap' rel='stylesheet'>
        <style>body { font-family: 'Poppins', sans-serif; }</style>
    </head>
    <body>
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Email Tidak Ditemukan',
                text: 'Maaf, email tersebut tidak terdaftar.',
                confirmButtonColor: '#2392ED'
            }).then(() => {
                window.history.back();
            });
        </script>
    </body>
    </html>";
}
}
?>