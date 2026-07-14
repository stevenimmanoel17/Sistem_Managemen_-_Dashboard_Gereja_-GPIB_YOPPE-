document.addEventListener('DOMContentLoaded', function() {
    
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

    const logoutBtn = document.getElementById('logoutBtn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', function(e) {
            e.preventDefault(); 

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
                            if (progressBar) progressBar.style.backgroundColor = '#2ecc71';
                        }
                    }).then(() => { 
                        window.location.href = '../logout.php'; 
                    });
                }
            });
        });
    }

    // --- AJAX PROSES TAMBAH BARANG ---
    const formTambah = document.getElementById('formTambahBarang');
    if (formTambah) {
        formTambah.addEventListener('submit', function(e) {
            e.preventDefault(); // Mencegah browser refresh halaman

            const formData = new FormData(this);

            fetch('proses_inventaris.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                // Tutup modal tambah dan reset isi form
                closeModal();
                formTambah.reset();

                // Tampilkan notifikasi sukses modern
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil Disimpan',
                    text: 'Data aset inventaris baru telah berhasil ditambahkan!',
                    showConfirmButton: false,
                    timer: 2000,
                    timerProgressBar: true,
                    didOpen: (toast) => {
                        const progressBar = toast.querySelector('.swal2-timer-progress-bar');
                        if (progressBar) progressBar.style.backgroundColor = '#2ecc71';
                    }
                });

                // Perbarui data tabel secara instan tanpa refresh halaman
                updateTabelSecaraSiluman();
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Menyimpan',
                    text: 'Terjadi kesalahan sistem saat mengirim data.'
                });
            });
        });
    }

    // --- AJAX PROSES EDIT BARANG ---
    const formEdit = document.getElementById('formEditBarang');
    if (formEdit) {
        formEdit.addEventListener('submit', function(e) {
            e.preventDefault(); // Mencegah browser refresh halaman

            const formData = new FormData(this);

            fetch('update_inventaris.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                // Tutup modal edit dan reset isi form
                closeEditModal();
                formEdit.reset();

                // Tampilkan notifikasi sukses modern
                Swal.fire({
                    icon: 'success',
                    title: 'Perubahan Disimpan',
                    text: 'Data aset inventaris berhasil diperbarui!',
                    showConfirmButton: false,
                    timer: 2000,
                    timerProgressBar: true,
                    didOpen: (toast) => {
                        const progressBar = toast.querySelector('.swal2-timer-progress-bar');
                        if (progressBar) progressBar.style.backgroundColor = '#2ecc71';
                    }
                });

                // Perbarui data tabel secara instan tanpa refresh halaman
                updateTabelSecaraSiluman();
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Memperbarui',
                    text: 'Terjadi kesalahan sistem saat memperbarui data.'
                });
            });
        });
    }
});

// --- FUNGSI PEMBANTU REFRESH DATA TABEL DI LATAR BELAKANG ---
function updateTabelSecaraSiluman() {
    const tableBody = document.getElementById('inventarisTableBody');
    
    fetch('inventaris.php')
        .then(response => response.text())
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newData = doc.getElementById('inventarisTableBody').innerHTML;
            
            // Perbarui konten tabel utama secara instan
            tableBody.innerHTML = newData;
            
            // Jalankan ulang fungsi filter agar baris tersembunyi "Tidak Ditemukan" tereset
            if (typeof filterInventaris === "function") {
                filterInventaris();
            }
        });
}

window.addEventListener('load', function() {
    const loader = document.getElementById('loading-screen');
    if (loader) {
        setTimeout(() => {
            loader.classList.add('loader-hidden');
        }, 500); 
    }

    // Otomatis scroll sidebar ke menu yang sedang aktif
    const activeLink = document.querySelector('.nav-link.active');
    const sidebarMenu = document.querySelector('.nav-menu');
    
    if (activeLink && sidebarMenu) {
        // Menggeser scrollbar tepat ke posisi menu aktif berada di tengah
        sidebarMenu.scrollTop = activeLink.offsetTop - (sidebarMenu.clientHeight / 2) + (activeLink.clientHeight / 2);
    }
});

function showModal() { document.getElementById('modalAdd').style.display = 'flex'; }

// RESET DROPDOWN TAMBAH SAAT MODAL DITUTUP
function closeModal() { 
    document.getElementById('modalAdd').style.display = 'none'; 
    
    // Reset Kategori
    document.getElementById('add_kategori').value = '';
    document.getElementById('addKategoriText').querySelector('span').innerText = 'Pilih Kategori';

    // Reset Lokasi Ruangan (Disinkronkan)
    document.getElementById('add_lokasi').value = '';
    document.getElementById('addLokasiText').querySelector('span').innerText = 'Pilih Lokasi Ruangan';

    // Reset Kondisi
    document.getElementById('add_kondisi').value = '';
    document.getElementById('addKondisiText').querySelector('span').innerText = 'Pilih Kondisi';

    // Reset asal barang
    document.getElementById('add_asal').value = '';
    document.getElementById('addAsalText').querySelector('span').innerText = 'Pilih Asal Barang';
}

function closeEditModal() { document.getElementById('modalEdit').style.display = 'none'; }
function closeModalEdit() { document.getElementById('modalEdit').style.display = 'none'; }
function closeDetailModal() { document.getElementById('modalDetail').style.display = 'none'; }

// MENGISI DATA KE FORM EDIT BERBASIS DROPDOWN
function showEditModal(data) {
    document.getElementById('modalEdit').style.display = 'flex';
    document.getElementById('edit_id').value = data.id;
    document.getElementById('edit_kode').value = data.kode_barang;
    document.getElementById('edit_nama').value = data.nama_barang;
    document.getElementById('edit_pj').value = data.penanggung_jawab;
    document.getElementById('edit_tgl').value = data.tanggal_masuk;
    document.getElementById('edit_asal').value = data.asal_barang;
    document.getElementById('edit_harga').value = data.harga_beli;

    document.getElementById('edit_keterangan').value = data.keterangan || '';
    
    // Set nilai awal dan teks untuk custom dropdown Kategori Edit
    document.getElementById('edit_kategori').value = data.kategori || '';
    document.getElementById('editKategoriText').querySelector('span').innerText = data.kategori || 'Pilih Kategori';
    
    // Set nilai awal dan teks untuk custom dropdown Lokasi Edit
    document.getElementById('edit_lokasi').value = data.lokasi || '';
    document.getElementById('editLokasiText').querySelector('span').innerText = data.lokasi || 'Pilih Lokasi Ruangan';
    
    // Set nilai awal dan teks untuk custom dropdown Kondisi Edit
    document.getElementById('edit_kondisi').value = data.kondisi || '';
    document.getElementById('editKondisiText').querySelector('span').innerText = data.kondisi || 'Pilih Kondisi';

    // Set nilai awal dan teks untuk custom dropdown Asal Barang Edit
    document.getElementById('edit_asal').value = data.asal_barang || '';
    document.getElementById('editAsalText').querySelector('span').innerText = data.asal_barang || 'Pilih Asal Barang';
}

function showDetailModal(data) {
    document.getElementById('modalDetail').style.display = 'flex';
    document.getElementById('det_kode').innerText = data.kode_barang || '-';
    document.getElementById('det_nama').innerText = data.nama_barang || '-';
    document.getElementById('det_kategori').innerText = data.kategori || '-';
    document.getElementById('det_lokasi').innerText = data.lokasi || '-';
    document.getElementById('det_kondisi').innerText = data.kondisi || '-';
    document.getElementById('det_pj').innerText = data.penanggung_jawab || '-';
    document.getElementById('det_tgl').innerText = data.tanggal_masuk || '-';
    document.getElementById('det_asal').innerText = data.asal_barang || '-';
    document.getElementById('det_harga').innerText = data.harga_beli ? 'Rp ' + parseInt(data.harga_beli).toLocaleString('id-ID') : '-';
    document.getElementById('det_keterangan').innerText = data.keterangan || 'Tidak ada catatan tambahan.';
}

function filterInventaris() {
    let searchInput = document.getElementById("searchInput").value.toLowerCase();
    let filterKondisi = document.getElementById("filterKondisi").value;
    let rows = document.querySelectorAll("#inventarisTableBody .data-row");
    let visibleRowsCount = 0;

    rows.forEach(row => {
        let kode = row.children[0].textContent.toLowerCase();
        let nama = row.children[1].textContent.toLowerCase();
        let kondisi = row.children[4].textContent.trim();

        let matchSearch = kode.includes(searchInput) || nama.includes(searchInput);
        let matchKondisi = (filterKondisi === "all") || (kondisi === filterKondisi);

        if (matchSearch && matchKondisi) {
            row.style.display = "";
            visibleRowsCount++;
        } else {
            row.style.display = "none";
        }
    });

    let notFoundRow = document.getElementById("searchNotFoundRow");
    let defaultNoDataRow = document.getElementById("noDataRow");

    if (visibleRowsCount === 0) {
        if (notFoundRow) notFoundRow.style.display = "";
        if (defaultNoDataRow) defaultNoDataRow.style.display = "none";
    } else {
        if (notFoundRow) notFoundRow.style.display = "none";
    }
}

// --- FUNGSI HAPUS DATA ASYNC ---
function confirmDelete(id) {
    Swal.fire({
        title: 'Hapus Data?',
        text: "Data aset inventaris ini akan dihapus permanen!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e74c3c',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            // Menggunakan fetch untuk menghapus data di latar belakang
            fetch('hapus_inventaris.php?id=' + id)
            .then(response => response.text())
            .then(data => {
                if (data.trim() === 'success') {
                    // Tampilkan notifikasi modern sukses
                    Swal.fire({
                        icon: 'success',
                        title: 'Terhapus!',
                        text: 'Data aset inventaris berhasil dihapus dari sistem.',
                        showConfirmButton: false,
                        timer: 2000,
                        timerProgressBar: true,
                        didOpen: (toast) => {
                            const progressBar = toast.querySelector('.swal2-timer-progress-bar');
                            if (progressBar) progressBar.style.backgroundColor = '#2ecc71';
                        }
                    });

                    // Perbarui baris tabel secara instan tanpa memuat ulang halaman
                    updateTabelSecaraSiluman();
                } else {
                    console.error("Respons server:", data);
                    Swal.fire('Gagal!', 'Gagal menghapus data dari database.', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire('Error', 'Gagal terhubung ke server.', 'error');
            });
        }
    });
}

window.onclick = function(e) {
    if (e.target.className === 'modal') {
        closeModal();
        closeEditModal();
        closeDetailModal();
    }
};

function toggleFilterDropdown() {
    document.getElementById('dropdownFilterKondisi').classList.toggle('active');
}

function pilihFilter(nilai, teks) {
    document.getElementById('filterKondisi').value = nilai;
    document.getElementById('filterText').querySelector('span').innerText = teks;
    filterInventaris();
}

window.addEventListener('click', function(e) {
    if (!e.target.closest('.custom-dropdown-box')) {
        let dropdowns = document.querySelectorAll('.custom-dropdown-box');
        dropdowns.forEach(dropdown => {
            dropdown.classList.remove('active');
        });
    }
});

// --- FITUR SELEKTOR DROPDOWN FORM TAMBAH BARANG ---
function pilihKategoriAdd(nilai) {
    document.getElementById('add_kategori').value = nilai;
    document.getElementById('addKategoriText').querySelector('span').innerText = nilai;
}

function pilihLokasiAdd(nilai) {
    if (window.event) {
        window.event.stopPropagation();
    }

    document.getElementById('add_lokasi').value = nilai;
    const boxTeks = document.querySelector('#addLokasiText span');
    if (boxTeks) {
        boxTeks.textContent = nilai;
    }

    const dropdown = document.getElementById('addLokasiText').closest('.custom-dropdown-box');
    if (dropdown) {
        dropdown.classList.remove('active');
    }
}

function pilihKondisiAdd(nilai) {
    document.getElementById('add_kondisi').value = nilai;
    document.getElementById('addKondisiText').querySelector('span').innerText = nilai;
}

// --- FITUR SELEKTOR DROPDOWN FORM EDIT BARANG ---
function pilihKategoriEdit(nilai) {
    document.getElementById('edit_kategori').value = nilai;
    document.getElementById('editKategoriText').querySelector('span').innerText = nilai;
}

function pilihLokasiEdit(nilai) {
    if (window.event) {
        window.event.stopPropagation();
    }
    
    document.getElementById('edit_lokasi').value = nilai;
   
    const boxTeks = document.querySelector('#editLokasiText span');
    if (boxTeks) {
        boxTeks.textContent = nilai;
    }
  
    const dropdown = document.getElementById('editLokasiText').closest('.custom-dropdown-box');
    if (dropdown) {
        dropdown.classList.remove('active');
    }
}

function pilihKondisiEdit(nilai) {
    document.getElementById('edit_kondisi').value = nilai;
    document.getElementById('editKondisiText').querySelector('span').innerText = nilai;
}

// --- LOGIKA AJAX REFRESH DATA TABEL INVENTARIS ---
function refreshTabelSaja() {
    const btn = document.getElementById('btnRefreshTabel');
    const icon = btn.querySelector('.icon-refresh');
    const tableBody = document.getElementById('inventarisTableBody');
    
    icon.style.animation = 'spin-tabel 1s linear infinite';
    btn.style.opacity = '0.6';
    btn.style.pointerEvents = 'none';

    tableBody.innerHTML = `
        <tr>
            <td colspan="7" style="text-align: center; padding: 50px;">
                <div style="width: 35px; height: 35px; border: 3px solid #f3f3f3; border-top: 3px solid #2392ED; border-radius: 50%; animation: spin-tabel 1s linear infinite; margin: 0 auto 15px;"></div>
                <p style="color: #666; font-weight: 500; font-size: 14px;">Memuat ulang data aset...</p>
            </td>
        </tr>
    `;

    fetch('inventaris.php')
        .then(response => response.text())
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newData = doc.getElementById('inventarisTableBody').innerHTML;

            setTimeout(() => {
                tableBody.innerHTML = newData;
                
                document.getElementById('searchInput').value = '';
                document.getElementById('filterKondisi').value = 'all';
                document.getElementById('filterText').querySelector('span').innerText = 'Semua Kondisi';
                
                icon.style.animation = 'none';
                btn.style.opacity = '1';
                btn.style.pointerEvents = 'auto';
            }, 600);
        })
        .catch(error => {
            console.error('Error saat me-refresh tabel:', error);
            tableBody.innerHTML = `<tr><td colspan="7" style="text-align:center; color:#e74c3c; font-weight:600; padding:30px;">Gagal memuat ulang data inventaris.</td></tr>`;
            icon.style.animation = 'none';
            btn.style.opacity = '1';
            btn.style.pointerEvents = 'auto';
        });
}

// --- FITUR SELEKTOR DROPDOWN ASAL BARANG FORM TAMBAH ---
function pilihAsalAdd(nilai) {
    if (window.event) {
        window.event.stopPropagation();
    }
    document.getElementById('add_asal').value = nilai;
    const boxTeks = document.querySelector('#addAsalText span');
    if (boxTeks) {
        boxTeks.textContent = nilai;
    }
    const dropdown = document.getElementById('addAsalText').closest('.custom-dropdown-box');
    if (dropdown) {
        dropdown.classList.remove('active');
    }
}

// --- FITUR SELEKTOR DROPDOWN ASAL BARANG FORM EDIT ---
function pilihAsalEdit(nilai) {
    if (window.event) {
        window.event.stopPropagation();
    }
    document.getElementById('edit_asal').value = nilai;
    const boxTeks = document.querySelector('#editAsalText span');
    if (boxTeks) {
        boxTeks.textContent = nilai;
    }
    const dropdown = document.getElementById('editAsalText').closest('.custom-dropdown-box');
    if (dropdown) {
        dropdown.classList.remove('active');
    }
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