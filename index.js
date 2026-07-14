document.addEventListener('DOMContentLoaded', function() {
    
    // --- LOGIKA SIDEBAR ---
    const btnToggle = document.getElementById('sidebarToggle');
    
    // Fungsi untuk mengubah status sidebar (buka/tutup)
    if (btnToggle) {
        btnToggle.addEventListener('click', function() {
            document.body.classList.toggle('sidebar-closed');
            
            // Simpan status di memori browser agar tidak berubah saat refresh
            const isClosed = document.body.classList.contains('sidebar-closed');
            localStorage.setItem('sidebarStatus', isClosed ? 'closed' : 'open');

            setTimeout(() => {
                const seluruhGrafik = Object.values(Chart.instances);
                seluruhGrafik.forEach(grafik => {
                    grafik.resize();
                });
            }, 320);
        });
    }

    // Cek status terakhir sidebar saat halaman pertama kali dimuat
    if (localStorage.getItem('sidebarStatus') === 'closed') {
        document.body.classList.add('sidebar-closed');
    }


    // --- LOGIKA LOGOUT DENGAN SWEETALERT2 ---
    const logoutBtn = document.getElementById('logoutBtn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', function(e) {
            e.preventDefault(); // Mencegah link langsung berpindah halaman

            Swal.fire({
                title: 'Apakah Anda Yakin?',
                text: "Sesi Anda akan segera berakhir!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#2392ED',
                cancelButtonColor: '#ff4d4d',
                confirmButtonText: 'Ya, Keluar!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil Keluar',
                        text: 'Tuhan Yesus Memberkati!',
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
                        window.location.href = 'logout.php'; 
                    });
                }
            });
        });
    }
});


// --- LOGIKA LOADING SCREEN ---
// Menggunakan window load agar loader hilang hanya setelah SEMUA aset (gambar/css) selesai dimuat
window.addEventListener('load', function() {
    const loader = document.getElementById('loading-screen');
    if (loader) {
        // Memberikan jeda 800ms agar transisi visual terasa lebih halus
        setTimeout(() => {
            loader.classList.add('loader-hidden');
        }, 500); 
    }
});

// Fungsi memicu munculnya kotak informasi
function toggleUserCard(event) {
    event.stopPropagation();
    const dropdown = document.getElementById('userProfileDropdown');
    if (dropdown) {
        dropdown.classList.toggle('show');
    }
}

// Fungsi menutup kotak informasi
function closeUserCard(event) {
    if (event) event.stopPropagation();
    const dropdown = document.getElementById('userProfileDropdown');
    if (dropdown) {
        dropdown.classList.remove('show');
    }
}

// Tutup otomatis jika pengguna mengklik area luar bodi halaman manapun
window.addEventListener('click', function(e) {
    const dropdown = document.getElementById('userProfileDropdown');
    const infoMenu = document.getElementById('userInfoMenu');
    
    if (dropdown && dropdown.classList.contains('show') && infoMenu && !infoMenu.contains(e.target)) {
        dropdown.classList.remove('show');
    }
});