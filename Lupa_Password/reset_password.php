<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link rel="stylesheet" href="lupa_password.css"> <link rel="icon" type="image/png" href="../icon/GPIB-NoCapt.png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,100..400,0,0" />
</head>
<body>
    <div class="forgot-container">
        <div class="forgot-card">
            <div class="brand-logo">
                <img src="../icon/GPIB-NoCapt.png" alt="Logo">
            </div>
            
            <h2>Atur Ulang Password</h2>
            <p>Silakan buat kata sandi baru.</p>

            <form action="proses_reset.php" method="POST">
                <input type="hidden" name="token" value="<?= $_GET['token'] ?? ''; ?>">
                <div class="input-group">
                    <label>Password Baru</label>
                    <div class="input-box">
                        <span class="material-symbols-outlined pre-icon">lock</span>
                        <input type="password" id="new_password" name="new_password" placeholder="Minimal 8 karakter" required>
                        <span class="material-symbols-outlined toggle-password outlined" onclick="togglePass('new_password', this)">visibility_off</span>
                    </div>
                </div>

                <div class="input-group">
                    <label>Konfirmasi Password Baru</label>
                    <div class="input-box">
                        <span class="material-symbols-outlined pre-icon">lock_reset</span>
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Ulangi password baru" required>
                        <span class="material-symbols-outlined toggle-password outlined" onclick="togglePass('confirm_password', this)">visibility_off</span>
                    </div>
                </div>

                <button type="submit" class="btn-reset">Perbarui Password</button>
            </form>
        </div>
    </div>
</body>
<script>
        function togglePass(inputId, iconElement) {
            const input = document.getElementById(inputId);
            if (input.type === "password") {
                input.type = "text";
                iconElement.innerText = "visibility";
                iconElement.style.color = "#888";
            } else {
                input.type = "password";
                iconElement.innerText = "visibility_off";
                iconElement.style.color = "#2392ED";
            }
        }
</script>
</html>