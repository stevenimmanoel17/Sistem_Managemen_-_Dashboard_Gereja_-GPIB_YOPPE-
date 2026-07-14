<?php
session_start();
include '../koneksi.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Bersihkan input
    $username = mysqli_real_escape_string($conn, trim($_POST['username']));
    $password = trim($_POST['password']);
    
    $max_attempts = 5; 
    $lockout_time = 10; // Menit

    $query = "SELECT * FROM users WHERE username='$username'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) === 1) {
        $row = mysqli_fetch_assoc($result);

        // Cek penguncian akun
        if ($row['login_attempts'] >= $max_attempts) {
            $last_time = strtotime($row['last_attempt_time']);
            $diff_minutes = (time() - $last_time) / 60;

            if ($diff_minutes < $lockout_time) {
                echo "locked|" . ceil($lockout_time - $diff_minutes);
                exit;
            } else {
                mysqli_query($conn, "UPDATE users SET login_attempts = 0 WHERE id = " . $row['id']);
                $row['login_attempts'] = 0;
            }
        }

        // Verifikasi password
        if (password_verify($password, $row['password'])) {
            mysqli_query($conn, "UPDATE users SET login_attempts = 0 WHERE id = " . $row['id']);
            $_SESSION['login']    = true;
            $_SESSION['username'] = $row['username'];
            $_SESSION['role']     = $row['role'];
            date_default_timezone_set('Asia/Jakarta');
            $_SESSION['waktu_login'] = date('d/m/Y H:i');
            echo "success";
        } else {
            // Update attempt jika salah password
            mysqli_query($conn, "UPDATE users SET login_attempts = login_attempts + 1, last_attempt_time = NOW() WHERE id = " . $row['id']);
            echo "failed";
        }
    } else {
        echo "failed";
    }
}
?>