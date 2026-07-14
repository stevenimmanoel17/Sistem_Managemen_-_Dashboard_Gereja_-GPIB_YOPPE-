<?php 
session_start(); 
$conn = mysqli_connect("localhost", "root", "", "db_gpib");

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: ../login/login.php");
    exit;
}

$total_barang = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM inventaris"));
$kondisi_baik = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM inventaris WHERE kondisi = 'Baik'"));
$kondisi_rusak = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM inventaris WHERE kondisi = 'Rusak'"));

$query = mysqli_query($conn, "SELECT * FROM inventaris ORDER BY nama_barang ASC");

$nama_user = $_SESSION['username'] ?? 'User';
$waktu_masuk_sistem = $_SESSION['waktu_login'] ?? date('d/m/Y H:i'); 
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventaris</title>
    <link rel="stylesheet" href="inventaris.css">
    <link rel="icon" type="image/png" href="../icon/GPIB-NoCapt.png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
</head>
<body>
    <div id="loading-screen">
        <div class="loader-content">
            <img src="../icon/GPIB-NoCapt.png" alt="Logo" class="loader-logo">
            <div class="progress-container">
                <div class="progress-bar"></div>
            </div>
            <p>Memuat Invenstaris...</p>
        </div>
    </div>

    <div class="sidebar">
        <div class="brand">
            <img src="../icon/Chruch.png" alt="Logo" class="logo">
            <h1>GPIB "Yoppe"<br>Belawan</h1>
        </div>
        <nav class="nav-menu">
            <a href="../index.php" class="nav-link"><img src="../icon/dashboard.png" class="menu-icon"> Dashboard</a>
            <a href="../money/money.php" class="nav-link"><img src="../icon/money.png" class="menu-icon"> Keuangan</a>
            <a href="../jemaat/jemaat.php" class="nav-link"><img src="../icon/Jemaat.png" class="menu-icon"> Data Jemaat</a>
            <a href="../keluarga/keluarga.php" class="nav-link"><img src="../icon/keluarga.png" class="menu-icon"> Data Keluarga</a>
            <a href="inventaris.php" class="nav-link active"><img src="../icon/inventory.png" class="menu-icon"> Inventaris</a>
            <a href="../laporan/laporan.php" class="nav-link"><img src="../icon/report.png" class="menu-icon"> Laporan</a>
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'Super Admin'): ?>
                <a href="../pengaturan/pengaturan.php" class="nav-link">
                    <img src="../icon/settings.png" class="menu-icon"> <span>Pengaturan</span>
                </a>
            <?php endif; ?>
        </nav>
        <div class="sidebar-footer">
            <hr class="divider">
            <a href="#" class="logout-link" id="logoutBtn"><img src="../icon/logout1.png" class="menu-icon"> Log Out</a>
        </div>
    </div>

    <div class="main-content">
        <header class="top-nav">
            <div class="nav-left">
                <button id="sidebarToggle" class="btn-toggle">
                    <span class="material-symbols-outlined hamburger-icon">menu</span>
                </button>
            </div>
                <div class="nav-right">
                    <div class="user-info" id="userInfoMenu" onclick="toggleUserCard(event)">
                        <div class="user-text">
                            <span class="user-name"><?= htmlspecialchars($_SESSION['username']); ?></span>
                            <small class="user-role">
                                <?= isset($_SESSION['role']) && $_SESSION['role'] === 'Super Admin' ? 'Super Admin' : 'Admin'; ?>
                            </small>
                        </div>
                        <div class="user-avatar-wrapper">
                            <div class="avatar-circle">
                                <?= strtoupper(substr($_SESSION['username'], 0, 1)); ?>
                            </div>
                        </div>

                        <div class="user-profile-dropdown" id="userProfileDropdown" onclick="event.stopPropagation()">
                            <div class="profile-dropdown-header">
                                <span class="material-symbols-outlined dropdown-icon-header">person</span>
                                <h2>Informasi User</h2>
                            </div>
                            
                            <div class="profile-dropdown-body">
                                <div class="dropdown-info-row">
                                    <span class="dropdown-label">Username</span>
                                    <span class="dropdown-dots">:</span>
                                    <span class="dropdown-value"><?= htmlspecialchars($_SESSION['username']); ?></span>
                                </div>
                                <div class="dropdown-info-row">
                                    <span class="dropdown-label">Role</span>
                                    <span class="dropdown-dots">:</span>
                                    <span class="dropdown-value"><?= isset($_SESSION['role']) && $_SESSION['role'] === 'Super Admin' ? 'Super Admin' : 'Admin'; ?></span>
                                </div>
                                <div class="dropdown-info-row">
                                    <span class="dropdown-label">Status</span>
                                    <span class="dropdown-dots">:</span>
                                    <span class="dropdown-value-status">Aktif</span>
                                </div>
                                <div class="dropdown-info-row">
                                    <span class="dropdown-label">Waktu Login</span>
                                    <span class="dropdown-dots">:</span>
                                    <span class="dropdown-value" id="waktuLoginDropdown">
                                        <?= $waktu_masuk_sistem; ?>
                                    </span>
                                </div>
                            </div>

                            <div class="profile-dropdown-footer">
                                <button type="button" class="btn-dropdown-ok" onclick="closeUserCard(event)">OK</button>
                            </div>
                        </div>
                    </div>
                </div>
        </header>


        <div class="dashboard-body">
            <header class="content-header">
                <div class="header-text">
                    <h2>Manajemen Inventaris</h2>
                    <p>Kelola data aset, kondisi, lokasi, dan penanggung jawab barang gereja.</p>
                </div>
                <div>
                    <button class="btn-add" onclick="showModal()">+ Tambah Barang</button>
                </div>
            </header>

            <div class="stats-container">
                <div class="stat-card blue">
                    <div class="stat-header">
                        <h4>Total Barang</h4>
                        <img src="../icon/InventoryB.png" class="stat-icon-img" style="opacity: 0.50; width: 35px; height: auto;">
                    </div>
                    <p class="stat-number"><?= $total_barang; ?></p>
                </div>
                <div class="stat-card green">
                    <div class="stat-header">
                        <h4>Kondisi Baik</h4>
                        <img src="../icon/Good Inventory.png" class="stat-icon-img" style="opacity: 0.50; width: 35px; height: auto;">
                    </div>
                    <p class="stat-number"><?= $kondisi_baik; ?></p>
                </div>
                <div class="stat-card red">
                    <div class="stat-header">
                        <h4>Kondisi Rusak</h4>
                        <img src="../icon/Broken Inventory.png" class="stat-icon-img" style="opacity: 0.50; width: 35px; height: auto;">
                    </div>
                    <p class="stat-number"><?= $kondisi_rusak; ?></p>
                </div>
            </div>

            <div class="search-section">
                <div style="display: flex; gap: 15px;">
                    <div style="flex: 1;">
                        <label style="font-size: 16px; font-weight: 600; color: #555; margin-bottom: 5px; display: block;">Pencarian</label>
                        <div class="input-icon-wrapper">
                            <span class="material-symbols-outlined search-icon">search</span>
                            <input type="text" id="searchInput" onkeyup="filterInventaris()" placeholder="Ketik Nama atau Kode Barang..." class="select-custom search-with-icon">
                        </div>
                    </div>
                    
                    <div style="width: 250px;">
                        <label style="font-size: 16px; font-weight: 600; color: #555; margin-bottom: 5px; display: block;">Filter Kondisi</label>
                        <div class="custom-dropdown-box" id="dropdownFilterKondisi" onclick="toggleFilterDropdown()">
                            <div class="dropdown-selected" id="filterText" style="display: flex; justify-content: space-between; align-items: center;">
                                <span>Semua Kondisi</span>
                                <span class="material-symbols-outlined arrow-icon">expand_more</span>
                            </div> 

                            <div class="dropdown-options">
                                <div class="option" onclick="pilihFilter('all', 'Semua Kondisi')">Semua Kondisi</div>
                                <div class="option" onclick="pilihFilter('Baik', 'Baik')">Baik</div>
                                <div class="option" onclick="pilihFilter('Perlu Perbaikan', 'Perlu Perbaikan')">Perlu Perbaikan</div>
                                <div class="option" onclick="pilihFilter('Rusak', 'Rusak')">Rusak</div>
                            </div>
                        </div>
                        <input type="hidden" id="filterKondisi" value="all">
                    </div>
                    <div style="width: auto;">
                        <label style="font-size: 13px; font-weight: 600; color: transparent; margin-bottom: 5px; display: block; user-select: none;">Refresh</label>
                        <button type="button" class="btn-refresh-tabel" id="btnRefreshTabel" onclick="refreshTabelSaja()">
                            <span class="material-symbols-outlined icon-refresh">refresh</span>
                            Refresh
                        </button>
                    </div>
                </div>
            </div>

            <div class="table-container">
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Kode</th>
                                <th>Nama Barang</th>
                                <th>Kategori</th>
                                <th>Lokasi</th>
                                <th>Kondisi</th>
                                <th>Penanggung Jawab</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="inventarisTableBody">
                            <?php if(mysqli_num_rows($query) > 0): ?>
                                <?php while($row = mysqli_fetch_assoc($query)): ?>
                                <tr class="data-row">
                                    <td><strong><?= $row['kode_barang']; ?></strong></td>
                                    <td><?= $row['nama_barang']; ?></td>
                                    <td><?= $row['kategori']; ?></td>
                                    <td><?= $row['lokasi']; ?></td>
                                    <td>
                                        <?php 
                                            $kondisi = $row['kondisi'];
                                            $badge_class = 'status-aktif';
                                            if ($kondisi == 'Perlu Perbaikan') $badge_class = 'status-none';
                                            if ($kondisi == 'Rusak') $badge_class = 'status-non';
                                        ?>
                                        <span class="status-badge <?= $badge_class; ?>"><?= $kondisi; ?></span>
                                    </td>
                                    <td><?= $row['penanggung_jawab']; ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn-detail" onclick='showDetailModal(<?= json_encode($row); ?>)'>
                                                <span class="material-symbols-outlined" style="font-size:18px; color:white;">visibility</span>
                                            </button>
                                            <button class="btn-edit" onclick='showEditModal(<?= json_encode($row); ?>)'>
                                                <img src="../icon/edit.png" alt="Edit" width="16">
                                            </button>
                                            <button onclick="confirmDelete(<?= $row['id']; ?>)" class="btn-delete">
                                                <img src="../icon/delete.png" alt="Hapus" width="16">
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr id="noDataRow">
                                    <td colspan="7" style="text-align:center; padding: 40px; color: #999;">Belum ada data inventaris.</td>
                                </tr>
                            <?php endif; ?>
                            <tr id="searchNotFoundRow" style="display: none;">
                                <td colspan="7" style="text-align:center; padding: 50px; color: #999;">
                                    <div style="display: flex; flex-direction: column; align-items: center; gap: 10px;">
                                        <span class="material-symbols-outlined" style="font-size: 48px; color: #e74c3c; opacity: 0.7;">search_off</span>
                                        <span style="font-weight: 600; font-size: 16px; color: #555;">Barang Tidak Ditemukan</span>
                                        <span style="font-size: 13px; color: #888;">Coba periksa kembali kata kunci atau filter kondisi Anda.</span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div id="modalAdd" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 style="font-size: 26px; color: #2392ED;">Tambah Barang Inventaris</h3>
                    <span class="close" onclick="closeModal()">&times;</span>
                </div>
                <form id="formTambahBarang" action="proses_inventaris.php" method="POST">
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Kode Barang</label>
                            <input type="text" name="kode_barang" placeholder="BBXX-20XX-00X" minlength="11" maxlength="11" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Nama Barang</label>
                            <input type="text" name="nama_barang"  placeholder="Contoh: Piano" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Kategori</label>
                            <div class="custom-dropdown-box" onclick="this.classList.toggle('active')">
                                <div class="dropdown-selected" id="addKategoriText" style="display: flex; justify-content: space-between; align-items: center;">
                                    <span>Pilih Kategori</span>
                                    <span class="material-symbols-outlined" style="font-size: 18px;">expand_more</span>
                                </div>
                                <div class="dropdown-options">
                                    <div class="option" onclick="pilihKategoriAdd('Alat Musik & Multimedia')">Alat Musik & Multimedia</div>
                                    <div class="option" onclick="pilihKategoriAdd('Peralatan Ibadah')">Peralatan Ibadah</div>
                                    <div class="option" onclick="pilihKategoriAdd('Perabotan Ruangan')">Perabotan Ruangan</div>
                                    <div class="option" onclick="pilihKategoriAdd('Elektronik')">Elektronik</div>
                                    <div class="option" onclick="pilihKategoriAdd('Perlengkapan Kantor')">Perlengkapan Kantor</div>
                                    <div class="option" onclick="pilihKategoriAdd('Peralatan Dapur')">Peralatan Dapur</div>
                                    <div class="option" onclick="pilihKategoriAdd('Kendaraan Transportasi')">Kendaraan Transportasi</div>
                                </div>
                            </div>
                            <input type="hidden" name="kategori" id="add_kategori" required>
                        </div>

                        <div class="form-group">
                            <label>Lokasi Ruangan</label>
                            <div class="custom-dropdown-box" onclick="this.classList.toggle('active')">
                                <div class="dropdown-selected" id="addLokasiText" style="display: flex; justify-content: space-between; align-items: center;">
                                    <span>Pilih Lokasi Ruangan</span>
                                    <span class="material-symbols-outlined" style="font-size: 18px;">expand_more</span>
                                </div>
                                <div class="dropdown-options">
                                    <div class="option" onclick="pilihLokasiAdd('Gereja Yoppe')">Gereja Yoppe</div>
                                    <div class="option" onclick="pilihLokasiAdd('GSG Syalom')">GSG Syalom</div>
                                    <div class="option" onclick="pilihLokasiAdd('Kantor')">Kantor</div>
                                    <div class="option" onclick="pilihLokasiAdd('Dapur')">Dapur</div>
                                    <div class="option" onclick="pilihLokasiAdd('Pastori')">Pastori</div>
                                    <div class="option" onclick="pilihLokasiAdd('Konsistori')">Konsistori</div>
                                    <div class="option" onclick="pilihLokasiAdd('Gereja Oukumene')">Gereja Oukumene</div>
                                    <div class="option" onclick="pilihLokasiAdd('Kapel Titipapan')">Kapel Titipapan</div>
                                </div>
                            </div>
                            <input type="hidden" name="lokasi" id="add_lokasi" required>
                        </div>

                        <div class="form-group">
                            <label>Kondisi</label>
                            <div class="custom-dropdown-box" onclick="this.classList.toggle('active')">
                                <div class="dropdown-selected" id="addKondisiText" style="display: flex; justify-content: space-between; align-items: center;">
                                    <span>Pilih Kondisi</span>
                                    <span class="material-symbols-outlined" style="font-size: 18px;">expand_more</span>
                                </div>
                                <div class="dropdown-options">
                                    <div class="option" onclick="pilihKondisiAdd('Baik')">Baik</div>
                                    <div class="option" onclick="pilihKondisiAdd('Perlu Perbaikan')">Perlu Perbaikan</div>
                                    <div class="option" onclick="pilihKondisiAdd('Rusak')">Rusak</div>
                                </div>
                            </div>
                            <input type="hidden" name="kondisi" id="add_kondisi" value="" required>
                        </div>
                        
                        <div class="form-group"><label>Penanggung Jawab</label><input type="text" name="penanggung_jawab"  placeholder="Contoh: Pedro"></div>
                        <div class="form-group"><label>Tanggal Masuk</label><input type="date" name="tanggal_masuk"></div>
                        <div class="form-group">
                            <label>Asal Barang</label>
                            <div class="custom-dropdown-box" onclick="this.classList.toggle('active')">
                                <div class="dropdown-selected" id="addAsalText" style="display: flex; justify-content: space-between; align-items: center;">
                                    <span>Pilih Asal Barang</span>
                                    <span class="material-symbols-outlined" style="font-size: 18px;">expand_more</span>
                                </div>
                                <div class="dropdown-options">
                                    <div class="option" onclick="pilihAsalAdd('Beli Baru / Bekas')">Beli Baru / Bekas</div>
                                    <div class="option" onclick="pilihAsalAdd('Pemberian / Sumbangan')">Pemberian / Sumbangan</div>
                                </div>
                            </div>
                            <input type="hidden" name="asal_barang" id="add_asal" required>
                        </div>

                        <div class="form-group">
                            <label>Harga Beli (Rp)</label>
                            <input type="number" name="harga_beli" placeholder="Contoh: 1000000">
                        </div>

                        <div class="form-group" style="grid-column: span 2;">
                            <label>Keterangan</label>
                            <input type="text" name="keterangan" placeholder="Tambahkan catatan atau keterangan barang di sini...">
                        </div>
                    </div>
                    
                    <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px; border-top: 1px solid #eee; padding-top: 15px;">
                        <button type="button" class="btn-cancel" onclick="closeModal()">Batal</button>
                        <button type="submit" class="btn-save">Simpan Data</button>
                    </div>
                </form>
            </div>
        </div>

        <div id="modalEdit" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 style="font-size: 26px; color: #2392ED;">Edit Barang Inventaris</h3>
                    <span class="close" onclick="closeEditModal()">&times;</span>
                </div>
                <form id="formEditBarang" action="proses_edit_inventaris.php" method="POST">
                    <input type="hidden" name="id" id="edit_id">

                    <div class="form-grid">
                        <div class="form-group">
                            <label>Kode Barang</label>
                            <input type="text" name="kode_barang" id="edit_kode" readonly style="background-color: #e9ecef; cursor: not-allowed;">
                        </div>
                        
                        <div class="form-group">
                            <label>Nama Barang</label>
                            <input type="text" name="nama_barang" id="edit_nama" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Kategori</label>
                            <div class="custom-dropdown-box" onclick="this.classList.toggle('active')">
                                <div class="dropdown-selected" id="editKategoriText" style="display: flex; justify-content: space-between; align-items: center;">
                                    <span>Pilih Kategori</span>
                                    <span class="material-symbols-outlined style-icon" style="font-size: 18px;">expand_more</span>
                                </div>
                                <div class="dropdown-options">
                                    <div class="option" onclick="pilihKategoriEdit('Alat Musik & Multimedia')">Alat Musik & Multimedia</div>
                                    <div class="option" onclick="pilihKategoriEdit('Peralatan Ibadah')">Peralatan Ibadah</div>
                                    <div class="option" onclick="pilihKategoriEdit('Perabotan Ruangan')">Perabotan Ruangan</div>
                                    <div class="option" onclick="pilihKategoriEdit('Elektronik')">Elektronik</div>
                                    <div class="option" onclick="pilihKategoriEdit('Perlengkapan Kantor')">Perlengkapan Kantor</div>
                                    <div class="option" onclick="pilihKategoriEdit('Peralatan Dapur')">Peralatan Dapur</div>
                                    <div class="option" onclick="pilihKategoriEdit('Kendaraan Transportasi')">Kendaraan Transportasi</div>
                                </div>
                            </div>
                            <input type="hidden" name="kategori" id="edit_kategori" required>
                        </div>

                        <div class="form-group">
                            <label>Lokasi Ruangan</label>
                            <div class="custom-dropdown-box" onclick="this.classList.toggle('active')">
                                <div class="dropdown-selected" id="editLokasiText" style="display: flex; justify-content: space-between; align-items: center;">
                                    <span>Pilih Lokasi Ruangan</span>
                                    <span class="material-symbols-outlined style-icon" style="font-size: 18px;">expand_more</span>
                                </div>
                                <div class="dropdown-options">
                                    <div class="option" onclick="pilihLokasiEdit('Gereja Yoppe')">Gereja Yoppe</div>
                                    <div class="option" onclick="pilihLokasiEdit('GSG Syalom')">GSG Syalom</div>
                                    <div class="option" onclick="pilihLokasiEdit('Kantor')">Kantor</div>
                                    <div class="option" onclick="pilihLokasiAdd('Dapur')">Dapur</div>
                                    <div class="option" onclick="pilihLokasiEdit('Pastori')">Pastori</div>
                                    <div class="option" onclick="pilihLokasiEdit('Konsistori')">Konsistori</div>
                                    <div class="option" onclick="pilihLokasiEdit('Gereja Oukumene')">Gereja Oukumene</div>
                                    <div class="option" onclick="pilihLokasiEdit('Kapel Titipapan')">Kapel Titipapan</div>
                                </div>
                            </div>
                            <input type="hidden" name="lokasi" id="edit_lokasi" required>
                        </div>

                        <div class="form-group">
                            <label>Kondisi</label>
                            <div class="custom-dropdown-box" onclick="this.classList.toggle('active')">
                                <div class="dropdown-selected" id="editKondisiText" style="display: flex; justify-content: space-between; align-items: center;">
                                    <span>Pilih Kondisi</span>
                                    <span class="material-symbols-outlined style-icon" style="font-size: 18px;">expand_more</span>
                                </div>
                                <div class="dropdown-options">
                                    <div class="option" onclick="pilihKondisiEdit('Baik')">Baik</div>
                                    <div class="option" onclick="pilihKondisiEdit('Perlu Perbaikan')">Perlu Perbaikan</div>
                                    <div class="option" onclick="pilihKondisiEdit('Rusak')">Rusak</div>
                                </div>
                            </div>
                            <input type="hidden" name="kondisi" id="edit_kondisi" required>
                        </div>
                        
                        <div class="form-group"><label>Penanggung Jawab</label><input type="text" name="penanggung_jawab" id="edit_pj"></div>
                        <div class="form-group"><label>Tanggal Masuk</label><input type="date" name="tanggal_masuk" id="edit_tgl"></div>
                        <div class="form-group">
                            <label>Asal Barang</label>
                            <div class="custom-dropdown-box" onclick="this.classList.toggle('active')">
                                <div class="dropdown-selected" id="editAsalText" style="display: flex; justify-content: space-between; align-items: center;">
                                    <span>Pilih Asal Barang</span>
                                    <span class="material-symbols-outlined style-icon" style="font-size: 18px;">expand_more</span>
                                </div>
                                <div class="dropdown-options">
                                    <div class="option" onclick="pilihAsalEdit('Beli Baru / Bekas')">Beli Baru / Bekas</div>
                                    <div class="option" onclick="pilihAsalEdit('Pemberian / Sumbangan')">Pemberian / Sumbangan</div>
                                </div>
                            </div>
                            <input type="hidden" name="asal_barang" id="edit_asal" required>
                        </div>

                        <div class="form-group">
                            <label>Harga Beli (Rp)</label>
                            <input type="number" name="harga_beli" id="edit_harga">
                        </div>

                        <div class="form-group" style="grid-column: span 2;">
                            <label>Keterangan</label>
                            <input type="text" name="keterangan" id="edit_keterangan" placeholder="Tambahkan catatan atau keterangan barang di sini...">
                        </div>
                    </div>
                    
                    <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px; border-top: 1px solid #eee; padding-top: 15px;">
                        <button type="button" class="btn-cancel" onclick="closeModalEdit()">Batal</button>
                        <button type="submit" class="btn-save">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>

        <div id="modalDetail" class="modal">
            <div class="modal-content" style="width: 700px;">
                <div class="modal-header">
                    <h3 style="font-size: 26px; color: #2392ED;">Detail Informasi Barang</h3>
                    <span class="close" onclick="closeDetailModal()">&times;</span>
                </div>
                <div class="form-grid" style="grid-template-columns: 1fr 1fr; gap: 15px; padding: 10px 0;">
                    <div><label style="font-size: 13px; font-weight:600; color:#888;">Kode Barang</label><p id="det_kode" style="padding:5px 0; font-size:15px; color: #333;"></p></div>
                    <div><label style="font-size: 13px; font-weight:600; color:#888;">Nama Barang</label><p id="det_nama" style="padding:5px 0; font-size:15px; color: #333;"></p></div>
                    <div><label style="font-size: 13px; font-weight:600; color:#888;">Kategori</label><p id="det_kategori" style="padding:5px 0; font-size:15px; color: #333;"></p></div>
                    <div><label style="font-size: 13px; font-weight:600; color:#888;">Lokasi</label><p id="det_lokasi" style="padding:5px 0; font-size:15px; color: #333;"></p></div>
                    <div><label style="font-size: 13px; font-weight:600; color:#888;">Kondisi</label><p id="det_kondisi" style="padding:5px 0; font-size:15px; color: #333;"></p></div>
                    <div><label style="font-size: 13px; font-weight:600; color:#888;">Penanggung Jawab</label><p id="det_pj" style="padding:5px 0; font-size:15px; color: #333;"></p></div>
                    <div><label style="font-size: 13px; font-weight:600; color:#888;">Tanggal Masuk</label><p id="det_tgl" style="padding:5px 0; font-size:15px; color: #333;"></p></div>
                    <div><label style="font-size: 13px; font-weight:600; color:#888;">Asal Barang</label><p id="det_asal" style="padding:5px 0; font-size:15px; color: #333;"></p></div>
                    <div><label style="font-size: 13px; font-weight:600; color:#888;">Harga Beli</label><p id="det_harga" style="padding:5px 0; font-size:15px; color: #333;"></p></div>
                    <div style="grid-column: span 2; margin-top: 5px;">
                        <label style="font-size: 13px; font-weight:600; color:#888;">Keterangan / Catatan</label>
                        <p id="det_keterangan" style="padding:8px 12px; font-size:14px; color: #333; background-color: #f8f9fa; border-radius: 8px; border: 1px solid #eee; margin-top: 5px; min-height: 40px; white-space: normal;"></p>
                    </div>
                </div>
                <div style="display: flex; justify-content: flex-end; margin-top: 20px; border-top: 1px solid #eee; padding-top: 15px;">
                    <button type="button" class="btn-cancel" onclick="closeDetailModal()">Tutup</button>
                </div>
            </div>
        </div>
        
        <footer class="main-footer">
            <p>&copy; 2026 Church Management System - Versi 3.0</p>
        </footer>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="inventaris.js"></script>
</body>
</html>