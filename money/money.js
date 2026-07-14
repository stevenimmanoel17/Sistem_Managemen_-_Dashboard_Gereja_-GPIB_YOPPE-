function showModal() { document.getElementById('modalTransaksi').style.display = 'flex'; }
function closeModal() { document.getElementById('modalTransaksi').style.display = 'none'; }

    // fungsi filterPeriode
    function filterPeriode(val, event) {
    if (event) {
        event.stopPropagation();
    }

    const tableBody = document.querySelector('.transaction-table tbody');
    const statsContainer = document.querySelector('.stats-container');
    const dropdown = document.getElementById('dropdownPeriode');
    const selectedText = dropdown.querySelector('.dropdown-selected');

    // LANGSUNG TUTUP DROPDOWN (Pindahkan ke paling atas)
    dropdown.classList.remove('active');

    // Tampilkan teks pilihan segera agar user tahu input diterima
    selectedText.innerHTML = val + ' <img src="../icon/chevron.png" class="chevron-icon">';

    // Berikan indikasi loading pada area data
    tableBody.innerHTML = `
        <tr>
            <td colspan="5" style="text-align: center; padding: 50px;">
                <div class="loader-spinner" style="margin: 0 auto 15px;"></div>
                <p style="color: #666; font-weight: 500;">Memuat Data ...</p>
            </td>
        </tr>
    `;
    statsContainer.style.opacity = '0.5';

    // Ambil data melalui AJAX
    fetch('money.php?periode=' + encodeURIComponent(val))
        .then(response => response.text())
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            
            // Ambil konten baru dari file yang di-fetch
            const newTable = doc.querySelector('.transaction-table tbody').innerHTML;
            const newStats = doc.querySelector('.stats-container').innerHTML;

            // Masukkan data baru ke halaman saat ini
            setTimeout(() => {
                tableBody.innerHTML = newTable;
                statsContainer.innerHTML = newStats;
                statsContainer.style.opacity = '1';
                window.history.replaceState({}, '', 'money.php?periode=' + val);
            }, 500);
        })
        .catch(error => {
            console.error('Error:', error);
            tableBody.innerHTML = '<tr><td colspan="5" style="text-align:center; color:red;">Gagal memuat data.</td></tr>';
            statsContainer.style.opacity = '1';
        });
}

const radioIn = document.getElementById('type-in');
const radioOut = document.getElementById('type-out');

const kategoriPemasukan = [
    'Kolekte Ibadah Minggu', 'Kolekte Ibadah Rumah Tangga', 'Kolekte PKB', 'Kolekte PKP',
    'Kolekte GP', 'Kolekte IHMPT', 'Kolekte IHMPA', 'Katekisasi',
    'Persembahan Persepuluhan', 'Persembahan Syukur', 'Usaha Dana', 'Pembangunan', 'Aksi Pelayanan', 'Lainnya...'
];

const kategoriPengeluaran = [
    'PENGADAAN TATA IBADAH', 'Buku Katekisasi, Pengajar & Retreat ', 'Trans. Organis', 'Trans. Kantoria',
    'Trans. Multimedia', 'PELAYANAN IBADAH KELUARGA', 'PELAYANAN IBADAH PELKAT',
    'Bantuan Kedukaan/ Rumah Sakit Diakonia', 'KEGIATAN PELKAT JEMAAT/MUPEL/SINODAL',
    'ATK - FOTOCOPY', 'Belanja Rumah Tangga Kantor, Gereja-RSG', 'Gereja & Pastori', 'Lainnya...'
];

function updateKategori() {
    const optionsContainer = document.getElementById('optionsKategori');
    const selectedText = document.getElementById('selectedKategori');
    const inputHidden = document.getElementById('inputKategori');
    const dropdownKat = document.getElementById('dropdownKategori');

    optionsContainer.innerHTML = '';
    selectedText.innerHTML = 'Pilih Kategori... <img src="../icon/chevron.png" class="chevron-icon">';
    inputHidden.value = '';

    let pilihan = radioIn.checked ? kategoriPemasukan : kategoriPengeluaran;

    pilihan.forEach(k => {
        let div = document.createElement('div');
        div.className = 'option';
        div.innerText = k;
        div.onclick = function(e) {
            e.stopPropagation();
            selectedText.innerHTML = k + ' <img src="../icon/chevron.png" class="chevron-icon">';
            inputHidden.value = k;
            dropdownKat.classList.remove('active');
        };
        optionsContainer.appendChild(div);
    });
}

radioIn.addEventListener('change', updateKategori);
radioOut.addEventListener('change', updateKategori);
updateKategori();

window.onclick = function(e) {
    const dropPeriode = document.getElementById('dropdownPeriode');
    const dropKategori = document.getElementById('dropdownKategori');

    if (dropPeriode && !dropPeriode.contains(e.target)) dropPeriode.classList.remove('active');
    if (dropKategori && !dropKategori.contains(e.target)) dropKategori.classList.remove('active');
    
    let modal = document.getElementById('modalTransaksi');
    if (e.target == modal) { modal.style.display = "none"; }
}

function confirmDelete(id) {
    Swal.fire({
        title: 'Hapus Transaksi?',
        text: "Data yang dihapus tidak dapat dikembalikan!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#2392ED',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => { 
        if (result.isConfirmed) { 
            fetch('hapus.php?id=' + id)
                .then(response => response.text())
                .then(data => {
                    if (data.trim() === "success") {
                        Swal.fire({
                            icon: 'success',
                            title: 'Terhapus!',
                            text: 'Data transaksi telah dihapus.',
                            showConfirmButton: false,
                            timer: 1500,
                            timerProgressBar: true
                        });

                        // Refresh tabel secara instan menggunakan fungsi yang sudah ada
                        const periodeAktif = document.querySelector('.dropdown-selected').innerText.trim();
                        filterPeriode(periodeAktif, null);
                    } else {
                        Swal.fire('Gagal!', 'Terjadi kesalahan saat menghapus data.', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire('Error!', 'Koneksi bermasalah.', 'error');
                });
        }
    });
}

        document.getElementById('logoutBtn').addEventListener('click', function(e) {
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
                }).then(() => { window.location.href = '../logout.php'; });
            }
        });
    });

    function editTransaksi(id) {
        // Munculkan loading SweetAlert
        Swal.fire({
            title: 'Memuat Data...',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        // Cukup panggil fetch SATU KALI saja
        fetch('get_transaksi.php?id=' + id)
            .then(response => response.json())
            .then(data => {
                // Tutup loading setelah data masuk
                Swal.close();

                // Set identitas modal ke mode Edit
                document.getElementById('modalTitle').innerText = 'Edit Transaksi';
                document.getElementById('formTransaksi').action = 'proses_edit.php';
                document.getElementById('btnSubmit').innerText = 'Update Transaksi';
                
                // Masukkan data ke input form
                document.getElementById('editId').value = data.id;
                document.getElementById('editTanggal').value = data.tanggal;
                document.getElementById('editNominal').value = Math.round(data.nominal);
                document.getElementById('editKeterangan').value = data.keterangan;
                
                // Atur status Radio Button
                if(data.tipe === 'pemasukan') {
                    document.getElementById('type-in').checked = true;
                } else {
                    document.getElementById('type-out').checked = true;
                }
                
                updateKategori(); 
                document.getElementById('inputKategori').value = data.kategori;
                document.getElementById('selectedKategori').innerHTML = data.kategori + ' <img src="../icon/chevron.png" class="chevron-icon">';
                
                // Buka modalnya
                showModal();
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: 'Data tidak ditemukan atau koneksi bermasalah.'
                });
            });
}

// Reset modal saat tombol "+ Catat Transaksi" ditekan
function showModalTambah() {
    document.getElementById('modalTitle').innerText = 'Catat Transaksi';
    document.getElementById('formTransaksi').action = 'proses_simpan.php';
    document.getElementById('btnSubmit').innerText = 'Simpan Transaksi';
    document.getElementById('formTransaksi').reset();
    document.getElementById('editId').value = '';
    document.getElementById('selectedKategori').innerHTML = 'Pilih Kategori... <img src="../icon/chevron.png" class="chevron-icon">';
    document.getElementById('inputKategori').value = '';
    updateKategori();
    showModal();
}

// Cek parameter status di URL
const urlParams = new URLSearchParams(window.location.search);
const status = urlParams.get('status');

if (status === 'update_success') {
    Swal.fire({
        icon: 'success',
        title: 'Berhasil diperbarui!',
        text: 'Data transaksi telah diperbarui ke sistem.',
        showConfirmButton: false,
        timer: 2000,
        timerProgressBar: true
    }).then(() => {
        // Bersihkan URL dari parameter status agar pesan tidak muncul lagi saat refresh
        window.history.replaceState({}, document.title, "money.php");
    });
} else if (status === 'error') {
    Swal.fire({
        icon: 'error',
        title: 'Gagal!',
        text: 'Terjadi kesalahan saat memperbarui data.',
        confirmButtonColor: '#2392ED'
    });
}

    document.addEventListener('DOMContentLoaded', function() {
        const btnToggle = document.getElementById('sidebarToggle');
        
        btnToggle.addEventListener('click', function() {
            document.body.classList.toggle('sidebar-closed');
            
            const isClosed = document.body.classList.contains('sidebar-closed');
            localStorage.setItem('sidebarStatus', isClosed ? 'closed' : 'open');
        });

        if (localStorage.getItem('sidebarStatus') === 'closed') {
            document.body.classList.add('sidebar-closed');
        }

        const formTransaksi = document.getElementById('formTransaksi');
        if (formTransaksi) {
            formTransaksi.addEventListener('submit', function(e) {
                const isAdd = this.action.includes('proses_simpan.php');

                if (isAdd) {
                    e.preventDefault(); // STOP REFRESH

                    const formData = new FormData(this);

                    fetch('proses_simpan.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.text())
                    .then(data => {
                        if (data.trim() === "success") {
                            closeModal(); // Tutup modal catat
                            this.reset(); // Kosongkan form

                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil Disimpan!',
                                text: 'Transaksi baru telah ditambahkan.',
                                showConfirmButton: false,
                                timer: 1500,
                                timerProgressBar: true
                            });

                            // Update tabel & saldo secara instan
                            const periodeAktif = document.querySelector('.dropdown-selected').innerText.trim();
                            filterPeriode(periodeAktif, null);
                        } else {
                            Swal.fire('Gagal!', 'Terjadi kesalahan saat menyimpan data.', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire({ 
                        icon: 'error', 
                        title: 'Gagal!', 
                        text: 'Terjadi kesalahan saat mengirim data.',
                        confirmButtonColor: '#2392ED' // Tambahkan ini agar tombol jadi biru
                    });
                    });
                }

                // Cek apakah ini mode Edit atau Simpan Baru
                const isEdit = this.action.includes('proses_edit.php');
                
                if (isEdit) {
                    e.preventDefault(); // Stop refresh halaman

                    const formData = new FormData(this);

                    fetch('proses_edit.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.text())
                    .then(data => {
                        // Tutup modal dulu
                        closeModal();

                        // Tampilkan notifikasi instan
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil diperbarui!',
                            text: 'Data transaksi telah diperbarui ke sistem.',
                            showConfirmButton: false,
                            timer: 2000,
                            timerProgressBar: true
                        });

                        // Update data di tabel secara lokal tanpa refresh halaman
                        const periodeAktif = document.querySelector('.dropdown-selected').innerText.trim();
                        filterPeriode(periodeAktif, null); 
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire({ icon: 'error', title: 'Gagal!', text: 'Terjadi kesalahan saat mengirim data.', confirmButtonColor: '#2392ED'});
                    });
                }
            });
        }
    });

    /* Loading */
    window.addEventListener('load', function() {
    const loader = document.getElementById('loading-screen');
    
    // Memberikan jeda waktu agar transisi terasa halus
    setTimeout(() => {
        loader.classList.add('loader-hidden');
    }, 500); 
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