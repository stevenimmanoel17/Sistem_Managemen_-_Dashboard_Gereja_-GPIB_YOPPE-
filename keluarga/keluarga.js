document.addEventListener('DOMContentLoaded', function() {

    // --- LOGIKA SIDEBAR (TOGGLE & PERSISTENCE) ---
    const btnToggle = document.getElementById('sidebarToggle');
    if (btnToggle) {
        btnToggle.addEventListener('click', function() {
            document.body.classList.toggle('sidebar-closed');
            const isClosed = document.body.classList.contains('sidebar-closed');
            localStorage.setItem('sidebarStatus', isClosed ? 'closed' : 'open');
        });
    }

    // Cek status terakhir sidebar agar tidak berubah saat refresh
    if (localStorage.getItem('sidebarStatus') === 'closed') {
        document.body.classList.add('sidebar-closed');
    }


    // --- LOGIKA LOADING SCREEN AWAL ---
    window.addEventListener('load', function() {
        const loader = document.getElementById('loading-screen');
        if (loader) {
            setTimeout(() => {
                loader.classList.add('loader-hidden');
            }, 500);
        }
    });


    // --- LOGIKA LIVE SEARCH KELUARGA ---
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter')
                 {
                e.preventDefault();
            }
        });

        searchInput.addEventListener('keyup', function() {
            const filter = this.value.toLowerCase();
            const cards = document.querySelectorAll('.family-card');
            let hasResults = false;

            cards.forEach(card => {
                const text = card.innerText.toLowerCase();
                if (text.includes(filter)) {
                    card.style.display = 'block';
                    hasResults = true;
                } else {
                    card.style.display = 'none';
                }
            });

            // Munculkan pesan jika pencarian kosong
            const noData = document.getElementById('noDataMessage');
            if (noData) {
                if (hasResults) {
                    noData.style.setProperty('display', 'none', 'important');
                } else {
                    noData.style.setProperty('display', 'flex', 'important');
                }
            }
        });
    }


    // --- LOGIKA TOMBOL REFRESH (AJAX FETCH) ---
    const btnRefresh = document.getElementById('btn-refresh-data');
    if (btnRefresh) {
        btnRefresh.addEventListener('click', function() {
            const icon = this.querySelector('.material-symbols-outlined');
            const contentBody = document.querySelector('.content-body');
            
            icon.style.animation = 'spin 1s linear infinite';
            this.style.opacity = '0.7';
            this.style.pointerEvents = 'none';

            // Animasi loading di area konten
            contentBody.innerHTML = `
                <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 300px; width: 100%;">
                    <div class="loader-spinner"></div>
                    <p style="margin-top: 15px; color: #666; font-weight: 500;">Memuat Data ...</p>
                </div>
            `;

            fetch('keluarga.php')
                .then(response => response.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const newData = doc.querySelector('.content-body').innerHTML;

                    setTimeout(() => {
                        contentBody.innerHTML = newData;
                        icon.style.animation = 'none';
                        this.style.opacity = '1';
                        this.style.pointerEvents = 'auto';
                    }, 800);
                })
                .catch(error => {
                    console.error('Error:', error);
                    icon.style.animation = 'none';
                    this.style.opacity = '1';
                });
        });
    }


    // --- LOGIKA LOGOUT DENGAN SWEETALERT2 ---
    const logoutBtn = document.getElementById('logoutBtn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', function(e) {
            e.preventDefault();
            Swal.fire({
                title: 'Apakah anda yakin?',
                text: "Saudara akan keluar dari sistem Dashboard",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#2392ED',
                cancelButtonColor: '#d33',
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
                        window.location.href = '../logout.php'; 
                    });
                }
            });
        });
    }
});


// --- LOGIKA MODAL DETAIL (GLOBAL SCOPE) ---
function showFamilyDetail(no_kk) {
    const modal = document.getElementById('detailModal');
    const content = document.getElementById('modalBody');
    
    modal.style.display = 'flex';
    content.innerHTML = '<div style="text-align:center; padding:20px;">Memuat Data...</div>';

    fetch('get_family_detail.php?no_kk=' + no_kk)
        .then(response => response.text())
        .then(data => {
            content.innerHTML = data;
        })
        .catch(err => {
            content.innerHTML = '<p style="color:red;">Gagal memuat detail keluarga.</p>';
        });
}

function closeModal() {
    document.getElementById('detailModal').style.display = 'none';
}

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