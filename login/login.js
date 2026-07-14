    // Fungsi Toggle Lihat Password
    function togglePassword(fieldId, iconId) {
        const passwordField = document.getElementById(fieldId);
        const eyeIcon = document.getElementById(iconId);
        if (passwordField.type === "password") {
            passwordField.type = "text";
            eyeIcon.style.stroke = "#2392ED";
        } else {
            passwordField.type = "password";
            eyeIcon.style.stroke = "#666";
        }
    }

    // Fungsi Reset Tampilan Tombol
    function resetButton(btn, btnText, loader) {
        btn.disabled = false;
        if (btnText) btnText.style.display = 'inline';
        if (loader) loader.style.display = 'none';
    }

    // Muat Username yang Tersimpan (Remember Me)
    document.addEventListener("DOMContentLoaded", function() {
        const savedUser = localStorage.getItem("savedUsername");
        if (savedUser) {
            const userField = document.getElementsByName("username")[0];
            const rememberCheckbox = document.getElementById("rememberMe");
            if(userField) userField.value = savedUser;
            if(rememberCheckbox) rememberCheckbox.checked = true;
        }
    });

    // Handler Pengiriman Form via Fetch API
    document.getElementById('loginForm').addEventListener('submit', function(e) {
        e.preventDefault(); 
        
        const btn = document.getElementById('loginBtn');
        const btnText = document.getElementById('btnText');
        const loader = document.getElementById('loaderContainer');
        const formData = new FormData(this);

        // Aktifkan Efek Loading
        btn.disabled = true;
        btnText.style.display = 'none';
        loader.style.display = 'flex';

        fetch('login_proses.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.text())
        .then(data => {
            const cleanData = data.trim(); // Bersihkan spasi liar dari PHP
            
            if (cleanData === "success") {
                // Kelola Fitur Ingat Saya
                const rememberMe = document.getElementById("rememberMe").checked;
                const username = document.getElementsByName("username")[0].value;
                if (rememberMe) { 
                    localStorage.setItem("savedUsername", username); 
                } else { 
                    localStorage.removeItem("savedUsername"); 
                }

                // Notifikasi Sukses dan Redirect
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: 'Login Berhasil!',
                    showConfirmButton: false,
                    timer: 1500,
                    timerProgressBar: true,
                    didOpen: (toast) => {
                    const progressBar = toast.querySelector('.swal2-timer-progress-bar');
                    if (progressBar) {
                        progressBar.style.backgroundColor = '#2ecc71';
                    }
                }
                }).then(() => {
                    window.location.href = '../index.php'; 
                });

            } else if (cleanData.includes("locked")) {
                // Penanganan Akun Terkunci
                const sisa = cleanData.split('|')[1];
                Swal.fire({
                    icon: 'error',
                    title: 'Akun Terkunci',
                    text: 'Terlalu banyak percobaan. Coba lagi dalam ' + sisa + ' menit.'
                });
                resetButton(btn, btnText, loader);
            } else {
                // Penanganan Gagal Biasa (Password Salah/User Tidak Ada)
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Login',
                    text: 'Username atau Password salah!',
                    confirmButtonColor: '#ff4d4d'
                });
                resetButton(btn, btnText, loader);
            }
        })
        .catch(error => {
            console.error('Fetch Error:', error);
            Swal.fire({ icon: 'error', title: 'Error Sistem', text: 'Tidak dapat terhubung ke server.' });
            resetButton(btn, btnText, loader);
        });
    });