document.addEventListener('DOMContentLoaded', function() {
    
    // --- PENGATURAN SIDEBAR & RESPONSIVITAS ---
    const btnToggle = document.getElementById('sidebarToggle');
    if (btnToggle) {
        btnToggle.addEventListener('click', function() {
            document.body.classList.toggle('sidebar-closed');
            const isClosed = document.body.classList.contains('sidebar-closed');
            localStorage.setItem('sidebarStatus', isClosed ? 'closed' : 'open');
        });
    }
    if (localStorage.getItem('sidebarStatus') === 'closed') {
        document.body.classList.add('sidebar-closed');
    }

    // --- LOGIKA LOADING SCREEN ---
    window.addEventListener('load', function() {
        const loader = document.getElementById('loading-screen');
        if (loader) {
            setTimeout(() => {
                loader.classList.add('loader-hidden');
            }, 500);
        }
    });

    // --- NOTIFIKASI SWEETALERT (STATUS URL) ---
    const urlParams = new URLSearchParams(window.location.search);
    const status = urlParams.get('status');
    if (status) {
        let config = { timer: 2000, showConfirmButton: false };
        if (status === 'success') { config.icon = 'success'; config.title = 'Data Tersimpan!'; }
        else if (status === 'updated') { config.icon = 'success'; config.title = 'Berhasil!'; config.text = 'Data telah diperbarui.'; }
        else if (status === 'deleted') { config.icon = 'success'; config.title = 'Terhapus!'; config.text = 'Data berhasil dihapus.'; }
        else if (status === 'error') { config.icon = 'error'; config.title = 'Waduh!'; config.text = 'Terjadi kesalahan sistem.'; }
        Swal.fire(config);
    }

    // --- AJAX SUBMIT FORM (TAMBAH & EDIT) ---
    const handleFormSubmit = (e) => {
        const form = e.target;
        const action = form.getAttribute('action');
        
        // Hanya proses jika action ke proses_jemaat atau update_jemaat
        if (action === 'proses_jemaat.php' || action === 'update_jemaat.php') {
            e.preventDefault();
            const formData = new FormData(form);

            fetch(action, {
                method: 'POST',
                body: formData
            })
            .then(res => res.text())
            .then(data => {
                if (data.trim() === 'success') {
                    // Tutup modal yang sedang terbuka
                    closeModal();
                    closeEditModal();
                    form.reset();

                    // Notifikasi Sukses
                    Swal.fire({
                        icon: 'success',
                        title: action === 'proses_jemaat.php' ? 'Data Tersimpan!' : 'Berhasil!',
                        text: action === 'proses_jemaat.php' ? 'Jemaat baru telah ditambahkan.' : 'Data telah diperbarui.',
                        showConfirmButton: false,
                        timer: 2000,
                        timerProgressBar: true
                    });

                    // Refresh tabel tanpa reload halaman (Memanfaatkan fungsi yang sudah ada)
                    document.getElementById('btn-refresh-jemaat').click();
                } else {
                    Swal.fire('Waduh!', 'Terjadi kesalahan sistem.', 'error');
                }
            })
            .catch(err => {
                console.error(err);
                Swal.fire('Error', 'Gagal terhubung ke server', 'error');
            });
        }
    };

    // Pasang listener ke semua form di halaman
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', handleFormSubmit);
    });

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

// --- LOGIKA FILTER & PENCARIAN (CLIENT-SIDE) ---
function combinedFilter() {
    let searchInput = document.getElementById("searchInput").value.toLowerCase();
    let filterValue = document.getElementById("filterCategory").value;
    let tr = document.querySelectorAll(".data-table .data-row");
    let visibleCount = 0;

    tr.forEach(row => {
        let txtNama = row.children[1].textContent.toLowerCase();
        let txtPelkat = row.children[4].textContent.trim();
        let txtSektor = row.children[5].textContent.trim();
        let txtStatus = row.querySelector(".status-badge").textContent.trim();

        let matchSearch = txtNama.includes(searchInput);
        let matchFilter = (filterValue === "all") || 
            (filterValue === "Aktif" || filterValue === "Non-Aktif" ? txtStatus === filterValue : 
            (filterValue === "Galatia" || filterValue === "Filipi" || filterValue === "Yudea" ? txtSektor === filterValue : txtPelkat === filterValue));

        if (matchSearch && matchFilter) {
            row.style.display = "";
            visibleCount++;
        } else {
            row.style.display = "none";
        }
    });
    document.getElementById("noDataRow").style.display = visibleCount === 0 ? "" : "none";
}

// --- LOGIKA MODAL (TAMBAH & EDIT) ---
function showModal() { document.getElementById('modalJemaat').style.display = 'flex'; }
function closeModal() { document.getElementById('modalJemaat').style.display = 'none'; }
function closeEditModal() { document.getElementById('modalEditJemaat').style.display = 'none'; }

function showEditModal(data) {
    document.getElementById('modalEditJemaat').style.display = 'flex';
    document.getElementById('edit_id').value = data.id;
    document.getElementById('edit_no_kk').value = data.no_kk;
    document.getElementById('edit_nik').value = data.nik;
    document.getElementById('edit_nama').value = data.nama_lengkap;
    document.getElementById('edit_tmpt_lahir').value = data.tmpt_lahir;
    document.getElementById('edit_tgl_lahir').value = data.tgl_lahir;
    document.getElementById('edit_no_hp').value = data.no_hp;
    document.getElementById('edit_alamat').value = data.alamat;

    const statusBaptisVal = data.status_baptis || 'Belum';
    document.getElementById('edit_status_baptis').value = statusBaptisVal;
    document.getElementById('editBaptisDisplay').innerText = statusBaptisVal;
    document.getElementById('edit_tempat_baptis').value = data.tempat_baptis || '';
    document.getElementById('edit_tgl_baptis').value = data.tgl_baptis || '';
    
    const statusSidiVal = data.status_sidi || 'Belum';
    document.getElementById('edit_status_sidi').value = statusSidiVal;
    document.getElementById('editSidiDisplay').innerText = statusSidiVal;
    document.getElementById('edit_tempat_sidi').value = data.tempat_sidi || '';
    document.getElementById('edit_tgl_sidi').value = data.tgl_sidi || '';

    // Sinkronisasi Custom Dropdown Edit
    const fields = ['posisi', 'gender', 'pelkat', 'status_nikah', 'status', 'sektor'];
    fields.forEach(field => {
        const val = (field === 'status') ? data.status : data[field];
        let displayId = (field === 'status') ? 'editStatusAktifDisplay' : 
                        (field === 'status_nikah') ? 'editStatusNikahDisplay' : 
                        'edit' + field.charAt(0).toUpperCase() + field.slice(1) + 'Display';

        const selectId = (field === 'status') ? 'edit_status_aktif' : 'edit_' + field;
        
        if(document.getElementById(selectId)) {
            document.getElementById(selectId).value = val || '';
            document.getElementById(displayId).innerText = val || 'Pilih...';
        }
    });

    const statusAnggota = data.status ? data.status.trim() : "";
    document.getElementById('edit_check_status').checked = (statusAnggota === 'Non-Aktif');

    const sektorVal = data.sektor || '';
    document.getElementById('edit_sektor').value = sektorVal;
    document.getElementById('editSektorDisplay').innerText = sektorVal || 'Pilih Sektor...';
}

// --- AJAX REFRESH DATA ---
const btnRefresh = document.getElementById('btn-refresh-jemaat');
if (btnRefresh) {
    btnRefresh.addEventListener('click', function() {
        const icon = this.querySelector('.material-symbols-outlined');
        const tableBody = document.querySelector('.data-table tbody');
        const colCount = document.querySelector('.data-table thead tr').childElementCount;
        
        // Ambil kategori yang sedang aktif dipilih di dropdown
        const kategoriAktif = document.getElementById("filterCategory").value;
        
        // Buat URL yang menyertakan parameter kategori agar PHP tahu apa yang harus difilter
        const refreshUrl = `jemaat.php?filter=${encodeURIComponent(kategoriAktif)}`;

        icon.style.animation = 'spin 1s linear infinite';
        this.style.opacity = '0.7';
        this.style.pointerEvents = 'none';

        tableBody.style.opacity = '0.5';
        tableBody.innerHTML = `
            <tr>
                <td colspan="${colCount}" style="text-align: center; padding: 60px;">
                    <div class="loader-spinner" style="margin: 0 auto 15px;"></div>
                    <p style="color: #666; font-weight: 500;">Memperbarui Data ${kategoriAktif === 'all' ? '' : kategoriAktif}...</p>
                </td>
            </tr>
        `;

        fetch(refreshUrl)
            .then(res => res.text())
            .then(html => {
                const doc = new DOMParser().parseFromString(html, 'text/html');
                const newData = doc.querySelector('.data-table tbody').innerHTML;
                
                setTimeout(() => {
                    tableBody.innerHTML = newData;
                    
                    // Panggil kembali combinedFilter untuk memastikan tampilan baris sinkron
                    combinedFilter(); 

                    tableBody.style.opacity = '1';
                    icon.style.animation = 'none';
                    this.style.opacity = '1';
                    this.style.pointerEvents = 'auto';
                }, 600);
            })
            .catch(err => {
                console.error(err);
                icon.style.animation = 'none';
                this.style.opacity = '1';
                this.style.pointerEvents = 'auto';
            });
    });
}

// --- GLOBAL & CUSTOM DROPDOWN ---
function toggleDropdown() { document.getElementById('customOptions').classList.toggle('show'); }
function selectOption(val, text) {
    document.getElementById('selectedDisplay').innerText = text;
    document.getElementById('filterCategory').value = val;
    combinedFilter();
    toggleDropdown();
}

function toggleModalDropdown(id) {
    document.querySelectorAll('.custom-options').forEach(opt => opt.id !== id && opt.classList.remove('show'));
    document.getElementById(id).classList.toggle('show');
}

function selectModalOption(selectId, displayId, val, text, optId) {
    document.getElementById(displayId).innerText = text;
    document.getElementById(selectId).value = val;
    document.getElementById(optId).classList.remove('show');
    
    // Link otomatis ke checkbox kematian
    if (selectId.includes('status_aktif')) {
        const checkId = selectId.startsWith('add') ? 'checkNonAktif' : 'edit_check_status';
        document.getElementById(checkId).checked = (val === 'Non-Aktif');
    }
}

window.onclick = function(e) {
    if (e.target.className === 'modal') { 
        closeModal(); 
        closeEditModal(); 
        closeDetailModal();
    }
    if (!e.target.closest('.custom-select-wrapper')) {
        document.querySelectorAll('.custom-options').forEach(opt => opt.classList.remove('show'));
    }
};

// --- FUNGSI HAPUS ---
function confirmDelete(id) {
    Swal.fire({
        title: 'Hapus Data?',
        text: "Data jemaat ini akan dihapus permanen!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#FF5252',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) { 
            fetch('hapus_jemaat.php?id=' + id)
            .then(res => res.text())
            .then(data => {
                // Membersihkan sisa spasi tak terlihat dari server
                const responBersih = data.trim();
                
                if (responBersih === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Terhapus!',
                        text: 'Data berhasil dihapus.',
                        showConfirmButton: false,
                        timer: 1500,
                        timerProgressBar: true
                    });
                    
                    // JALAN KELUAR: Otomatis memicu klik tombol refresh jemaat agar tabel langsung bersih
                    const btnRefreshJemaat = document.getElementById('btn-refresh-jemaat');
                    if (btnRefreshJemaat) {
                        btnRefreshJemaat.click();
                    }
                } else {
                    console.error("Respons server: ", data); // Membantu melacak isi error di console
                    Swal.fire('Gagal!', 'Gagal menghapus data dari sistem.', 'error');
                }
            })
            .catch(err => {
                console.error(err);
                Swal.fire('Error', 'Gagal terhubung ke server.', 'error');
            });
        }
    });
}

function showDetailModal(data) {
    document.getElementById('modalDetailJemaat').style.display = 'flex';
    
    // Fungsi format tanggal ke DD-MM-YYYY
    const formatDate = (dateStr) => {
        if (!dateStr || dateStr === '0000-00-00') return '-';
        const parts = dateStr.split('-');
        if (parts.length === 3) return `${parts[2]}-${parts[1]}-${parts[0]}`;
        return dateStr;
    };
    
    document.getElementById('det_nama').innerText = data.nama_lengkap || '-';
    document.getElementById('det_gender').innerText = data.gender || '-';
    document.getElementById('det_tgl_lahir').innerText = formatDate(data.tgl_lahir);
    
    document.getElementById('det_pelkat').innerText = data.pelkat || '-';
    document.getElementById('det_no_hp').innerText = data.no_hp || '-';
    document.getElementById('det_status_nikah').innerText = data.status_nikah || '-';
    
    document.getElementById('det_status_baptis').innerText = data.status_baptis || 'Belum';
    document.getElementById('det_tempat_baptis').innerText = data.tempat_baptis || '-';
    document.getElementById('det_tgl_baptis').innerText = formatDate(data.tgl_baptis);
    
    document.getElementById('det_status_sidi').innerText = data.status_sidi || 'Belum';
    document.getElementById('det_tempat_sidi').innerText = data.tempat_sidi || '-';
    document.getElementById('det_tgl_sidi').innerText = formatDate(data.tgl_sidi);
    
    document.getElementById('det_sektor').innerText = data.sektor || '-';
    document.getElementById('det_status').innerText = data.status || '-';
    
    document.getElementById('det_alamat').innerText = data.alamat || '-';
}

function closeDetailModal() {
    document.getElementById('modalDetailJemaat').style.display = 'none';
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