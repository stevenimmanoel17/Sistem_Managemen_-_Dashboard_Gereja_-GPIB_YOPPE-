document.addEventListener('DOMContentLoaded', function() {

    // --- SIDEBAR & LOADING ---
    const btnToggle = document.getElementById('sidebarToggle');
    if (btnToggle) {
        btnToggle.addEventListener('click', function() {
            document.body.classList.toggle('sidebar-closed');
            localStorage.setItem('sidebarStatus', document.body.classList.contains('sidebar-closed') ? 'closed' : 'open');
        });
    }
    if (localStorage.getItem('sidebarStatus') === 'closed') document.body.classList.add('sidebar-closed');

    window.addEventListener('load', function() {
        const loader = document.getElementById('loading-screen');
        if (loader) {
            setTimeout(() => {
                loader.classList.add('loader-hidden');
            }, 500); 
        }

        const activeLink = document.querySelector('.nav-menu .nav-link.active'); 
        const sidebarMenu = document.querySelector('.nav-menu');

        if (activeLink && sidebarMenu) {
            // Menggeser scrollbar tepat ke posisi menu aktif berada di tengah
            sidebarMenu.scrollTop = activeLink.offsetTop - (sidebarMenu.clientHeight / 1) + (activeLink.clientHeight / 1);
        }
    });


    // --- SISTEM TAB LAPORAN ---
    // Fungsi ini menangani perpindahan Tab Jemaat & Keuangan
    window.openReportTab = function(evt, tabName) {
        let i, tabcontent, tablinks;
        tabcontent = document.getElementsByClassName("tab-content");
        for (i = 0; i < tabcontent.length; i++) {
            tabcontent[i].classList.remove("active");
        }
        tablinks = document.getElementsByClassName("tab-btn");
        for (i = 0; i < tablinks.length; i++) {
            tablinks[i].classList.remove("active");
        }
        document.getElementById(tabName).classList.add("active");
        evt.currentTarget.classList.add("active");
    };

    // Cek URL jika user ingin langsung ke tab keuangan
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('tab') === 'keuangan') {
        const keuanganBtn = document.querySelector('[onclick*="keuanganTab"]');
        if (keuanganBtn) keuanganBtn.click();
    }


    // --- DROPWDOWN EXPORT & PERIODE ---
    window.toggleExportMenu = function() {
        document.getElementById("exportMenu").classList.toggle("show");
    };

    window.togglePeriodeDropdown = function() {
        const options = document.getElementById('periodeOptions');
        const wrapper = document.getElementById('dropdownPeriode');
        options.classList.toggle('show');
        wrapper.classList.toggle('active');
    };


    // --- LOGIKA AJAX UPDATE LAPORAN KEUANGAN ---
    window.updateLaporan = function(val) {
        const activeTab = document.querySelector('.tab-content.active');
        const tableBody = activeTab.querySelector('tbody');
        const colCount = activeTab.querySelector('thead tr').childElementCount;

        // Visual Loading pada tabel
        tableBody.innerHTML = `
            <tr>
                <td colspan="${colCount}" style="text-align: center; padding: 50px;">
                    <div class="loader-spinner" style="margin: 0 auto 15px;"></div>
                    <p style="color: #666; font-weight: 500;">Memuat Data ...</p>
                </td>
            </tr>
        `;

        fetch('laporan.php?periode=' + encodeURIComponent(val))
            .then(response => response.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const activeTabId = activeTab.id; 
                
                // Update isi tabel dan footer (total saldo)
                tableBody.innerHTML = doc.querySelector(`#${activeTabId} tbody`).innerHTML;
                const newFooter = doc.querySelector(`#${activeTabId} tfoot`);
                if (newFooter && activeTab.querySelector('tfoot')) {
                    activeTab.querySelector('tfoot').innerHTML = newFooter.innerHTML;
                }

                // [PERBAIKAN DI SINI] Update teks dropdown dan spanduk info secara bersamaan
                document.getElementById('periodeText').innerText = val;
                
                const infoBanner = document.querySelector('.info-banner-spaced');
                if (infoBanner) {
                    infoBanner.innerHTML = `<i class="fa-solid fa-circle-info"></i> Menampilkan Laporan Periode: ${val}`;
                }

                window.history.replaceState({}, '', 'laporan.php?periode=' + val + '&tab=keuangan');
            });

        // Tutup dropdown
        document.getElementById('periodeOptions').classList.remove('show');
    };

    // --- LOGIKA AJAX REFRESH DATA ---
    const btnRefresh = document.getElementById('btn-refresh-laporan');
    if (btnRefresh) {
        btnRefresh.addEventListener('click', function() {
            const btn = this;
            const icon = btn.querySelector('.material-symbols-outlined');
            const activeTab = document.querySelector('.tab-content.active');
            const tableBody = activeTab.querySelector('tbody');
            const colCount = activeTab.querySelector('thead tr').childElementCount;
            const currentUrl = window.location.href;

            // Pastikan nama animasi sesuai dengan CSS Anda (biasanya 'spin-laporan')
            icon.style.animation = 'spin-laporan 1s linear infinite';
            btn.style.opacity = '0.5';
            btn.style.pointerEvents = 'none';

            // Tampilkan placeholder loading di tabel
            tableBody.innerHTML = `
                <tr>
                    <td colspan="${colCount}" style="text-align: center; padding: 50px;">
                        <div class="loader-spinner" style="margin: 0 auto 15px;"></div>
                        <p style="color: #666; font-weight: 500;">Memuat Data ...</p>
                    </td>
                </tr>
            `;

            fetch(currentUrl)
                .then(response => response.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const activeTabId = activeTab.id;
                    
                    // Ambil data tbody dari tab yang sedang aktif saja
                    const newData = doc.querySelector(`#${activeTabId} tbody`).innerHTML;

                    setTimeout(() => {
                        tableBody.innerHTML = newData;
                        icon.style.animation = 'none';
                        btn.style.opacity = '1';
                        btn.style.pointerEvents = 'auto';
                    }, 600);
                })
                .catch(error => {
                    console.error('Error:', error);
                    tableBody.innerHTML = `<tr><td colspan="${colCount}" style="text-align:center; color:red;">Gagal memuat data.</td></tr>`;
                    icon.style.animation = 'none';
                    btn.style.opacity = '1';
                    btn.style.pointerEvents = 'auto';
                });
        });
    }


    // --- EKSPOR DATA ---
    window.eksporData = function(format) {
        const activeTabBtn = document.querySelector('.tab-btn.active');
        let jenis = "keuangan";
        
        if (activeTabBtn.innerText.includes("Jemaat")) {
            jenis = "jemaat";
        } else if (activeTabBtn.innerText.includes("Inventaris")) {
            jenis = "inventaris";
        }
        
        const periode = document.getElementById('periodeText').innerText;
        window.open(`proses_cetak.php?jenis=${jenis}&format=${format}&periode=${periode}`, '_blank');
    };


    // --- LOGOUT NOTIFICATION ---
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
                        icon: 'success', title: 'Berhasil Keluar',
                        text: 'Tuhan Yesus Memberkati!',
                        showConfirmButton: false,
                        timer: 1500,
                        timerProgressBar: true,
                        didOpen: (toast) => {
                            const progressBar = toast.querySelector('.swal2-timer-progress-bar');
                            if (progressBar) progressBar.style.backgroundColor = '#2ecc71';
                        }
                    }).then(() => { 
                        window.location.href = '../logout.php'; 
                    });
                }
            });
        });
    }

    // --- CLOSE DROPDOWNS ON OUTSIDE CLICK ---
    window.onclick = function(event) {
        if (!event.target.closest('.btn-export')) {
            document.getElementById("exportMenu").classList.remove("show");
        }
        if (!event.target.closest('#dropdownPeriode')) {
            document.getElementById('periodeOptions').classList.remove('show');
            document.getElementById('dropdownPeriode').classList.remove('active');
        }
    };
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