<?php
session_start();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log in</title>
    <link rel="icon" type="image/png" href="../icon/GPIB-NoCapt.png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="Login.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="container">
        <div class="login-card">
            <div class="login-banner">
                <img src="../icon/GPIB-Yoppe.png" alt="Banner Gereja">
            </div>
                <div class="sign-in-section">
                    <h1>GPIB YOPPE</h1>
                    <p>Management System Dashboard</p>
                    
                    <form id="loginForm" method="POST">
                        <div class="input-group">
                            <label>Username</label>
                            <input type="text" name="username" required autocomplete="off">
                        </div>

                        <div class="input-group">
                            <label>Password</label>
                            <div class="password-wrapper">
                                <input type="password" id="passwordField" name="password" required autocomplete="current-password">
                                <span class="toggle-password" onclick="togglePassword('passwordField', 'eyeIcon')">
                                    <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#666" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                </span>
                            </div>
                        </div>

                        <a href="../Lupa_Password/Lupa_password.html" class="forgot-password">Lupa password?</a>
                        
                        <div class="remember-me-group" style="display: flex; align-items: center; gap: 8px; margin-bottom: 25px; cursor: pointer;">
                            <input type="checkbox" id="rememberMe" style="width: 16px; height: 16px; cursor: pointer;">
                            <label for="rememberMe" style="font-size: 13px; color: #666; cursor: pointer; font-weight: 500;">Ingati Saya!</label>
                        </div>

                        <button type="submit" class="btn-signin" id="loginBtn">
                            <span id="btnText">Login</span>
                            <div id="loaderContainer" style="display: none; align-items: center; justify-content: center; gap: 10px;">
                                <div class="loader"></div>
                                <span style="font-style: normal; font-size: 14px;">Memproses...</span>
                            </div>
                        </button>
                    </form>
                </div>
        </div>
    </div>

<script src="login.js"></script>
</body>
</html>