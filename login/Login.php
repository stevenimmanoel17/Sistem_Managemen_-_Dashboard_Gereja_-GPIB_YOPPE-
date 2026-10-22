<?php
// ==========================================
// INISIALISASI SESI
// ==========================================
session_start();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <!-- ========================================== -->
    <!-- METADATA & ASSET HEADER                   -->
    <!-- ========================================== -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log in</title>
    <link rel="icon" type="image/png" href="../icon/GPIB-NoCapt.png">
    <!-- Google Font: Instrument Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="login.css?v=<?= time(); ?>">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <!-- ========================================== -->
    <!-- KONTEN UTAMA HALAMAN LOGIN                -->
    <!-- ========================================== -->
    <div class="container">
        <div class="login-card">
            <!-- Sisi Kiri: Gambar Gereja dengan Radius 18px -->
            <div class="login-banner">
                <img src="../icon/GPIB_Vector.jfif" alt="Banner Gereja GPIB Yoppe">
            </div>

            <!-- Sisi Kanan: Form Login -->
            <div class="sign-in-section">
                <!-- Header Branding Form -->
                <h1 class="login-title">GPIB YOPPE</h1>
                <p class="login-subtitle">Selamat Datang di Dashboard Sistem Manajemen</p>
                <div class="title-divider"></div>
                
                <!-- Form Input Kredensial -->
                <form id="loginForm" method="POST">
                    <div class="input-group">
                        <label>Username Kamu</label>
                        <div class="input-wrapper">
                            <input type="text" name="username" placeholder="Masukkan username" required autocomplete="off">
                            <span class="input-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#888" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="12" cy="7" r="4"></circle>
                                </svg>
                            </span>
                        </div>
                    </div>

                    <div class="input-group">
                        <label>Password Kamu</label>
                        <div class="password-wrapper">
                            <input type="password" id="passwordField" name="password" placeholder="••••••••" required autocomplete="current-password">
                            <span class="toggle-password" onclick="togglePassword('passwordField', 'eyeIcon')">
                                <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#888" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                            </span>
                        </div>
                    </div>

                    <button type="submit" class="btn-signin" id="loginBtn">
                        <span id="btnText">Login</span>
                        <div id="loaderContainer" style="display: none; align-items: center; justify-content: center; gap: 10px;">
                            <div class="loader"></div>
                            <span style="font-style: normal; font-size: 14px;">Memproses...</span>
                        </div>
                    </button>

                    <!-- Baris Bawah: Ingat Saya & Lupa Password Sejajar -->
                    <div class="form-bottom-row">
                        <label class="remember-me-group" for="rememberMe">
                            <input type="checkbox" id="rememberMe">
                            <span>Ingatkan Saya!</span>
                        </label>
                        <a href="../lupa_password/lupa_password.html" class="forgot-password">Lupa Password?</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- SKRIP LOGIKA INTERAKSI                    -->
    <!-- ========================================== -->
    <script src="login.js"></script>
</body>
</html>