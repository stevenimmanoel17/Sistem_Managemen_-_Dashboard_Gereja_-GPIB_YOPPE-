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

// TANGKAP FILTER KATEGORI
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$where_clause = "";
if ($filter !== 'all') {
    //filter Status
    if ($filter === 'Aktif' || $filter === 'Non-Aktif') {
        $where_clause = "WHERE status = '$filter'";
    } 
    //filter Sektor
    elseif ($filter === 'Galatia' || $filter === 'Filipi' || $filter === 'Yudea') {
        $where_clause = "WHERE sektor = '$filter'";
    } 
    //filter Pelkat
    else {
        $where_clause = "WHERE pelkat = '$filter'";
    }
}

// LOGIKA PAGINATION (Harus di atas query data)
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$halaman = isset($_GET['halaman']) ? (int)$_GET['halaman'] : 1;
$offset = ($halaman - 1) * $limit;

// HITUNG TOTAL DATA BERDASARKAN FILTER
$query_total = "SELECT COUNT(*) AS total FROM jemaat $where_clause";
$result_total = mysqli_query($conn, $query_total);
$row_total = mysqli_fetch_assoc($result_total);
$total_data = $row_total['total'];
$total_halaman = ceil($total_data / $limit);

// AMBIL DATA DENGAN FILTER DAN LIMIT
$query_jemaat = "SELECT * FROM jemaat $where_clause ORDER BY nama_lengkap ASC LIMIT $offset, $limit";
$tampil_jemaat = mysqli_query($conn, $query_jemaat);

$nama_user = $_SESSION['username'] ?? 'User';
$waktu_masuk_sistem = $_SESSION['waktu_login'] ?? date('d/m/Y H:i');

if (!$tampil_jemaat) {
    die("Error pada query: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Jemaat</title>
    <link rel="stylesheet" href="jemaat.css">
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
            <p>Memuat Data Jemaat...</p>
        </div>
    </div> 

    <div class="sidebar">
        <div class="brand">
            <img src="../icon/Chruch.png" alt="Logo" class="logo">
            <h1>GPIB <i>"Yoppe"</i><br>Belawan</h1>
        </div>
        <nav class="nav-menu">
            <a href="../index.php" class="nav-link"><img src="../icon/dashboard.png" class="menu-icon"> Dashboard</a>
            <a href="../money/money.php" class="nav-link"><img src="../icon/money.png" class="menu-icon"> Keuangan</a>
            <a href="jemaat.php" class="nav-link active"><img src="../icon/Jemaat.png" class="menu-icon"> Data Jemaat</a>
            <a href="../keluarga/keluarga.php" class="nav-link"><img src="../icon/keluarga.png" class="menu-icon"> Data Keluarga</a>
            <a href="../inventaris/inventaris.php" class="nav-link"><img src="../icon/inventory.png" class="menu-icon"> Inventaris</a>
            <a href="../laporan/laporan.php" class="nav-link"><img src="../icon/report.png" class="menu-icon"> Laporan</a>
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'Super Admin'): ?>
                <a href="../pengaturan/pengaturan.php" class="nav-link">
                    <img src="../icon/settings.png" class="menu-icon"> <span>Pengaturan</span>
                </a>
            <?php endif; ?>
        </nav>
        <div class="sidebar-footer">
            <hr class="divider">
            <a href="../logout.php" class="logout-link" id="logoutBtn"><img src="../icon/logout1.png" class="menu-icon"> Log Out</a>
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
            <h2>Data Jemaat</h2>
            <p style="font-size: 16px; color: #666; margin-top: 5px;">
                Informasi lengkap mengenai biodata, status, dan kategori pelkat seluruh jemaat.
            </p>
        </div>
        <div style="display: flex; gap: 10px;">
            <button class="btn-add" onclick="showModal()">+ Tambah Jemaat</button>
        </div>
    </header>

<div class="search-section">
    <div class="search-grid">
        <div class="search-input-group">
            <label>Pencarian</label>
            <div class="search-box">
                <img src="../icon/search-people.png" class="search-icon">
                <input type="text" id="searchInput" onkeyup="combinedFilter()" placeholder="Ketik Nama Jemaat...">
            </div>
        </div>

        <div class="filter-group">
            <label>Fitur Kategori</label>
            <div class="custom-select-wrapper">
                <select id="filterCategory" onchange="combinedFilter()" style="display:none;">
                    <option value="all">Tampilkan Semua</option>
                    <option value="Aktif">Hanya Jemaat Aktif</option>
                    <option value="Non-Aktif">Jemaat Non-Aktif/Meninggal</option>
                    <option value="Galatia">Sektor Galatia</option>
                    <option value="Filipi">Sektor Filipi</option>
                    <option value="Yudea">Sektor Yudea</option>
                    <option value="PA">Pelkat PA</option>
                    <option value="PT">Pelkat PT</option>
                    <option value="GP">Pelkat GP</option>
                    <option value="PKP">Pelkat PKP</option>
                    <option value="PKB">Pelkat PKB</option>
                    <option value="PKLU">Pelkat PKLU</option>
                </select>

                <div class="custom-select" onclick="toggleDropdown()">
                    <span id="selectedDisplay">Tampilkan Semua</span>
                    <div class="custom-arrow"></div>
                </div>

                <ul class="custom-options" id="customOptions">
                    <li class="opt-group">Berdasarkan Status</li>
                    <li onclick="selectOption('all', 'Tampilkan Semua')">Tampilkan Semua</li>
                    <li onclick="selectOption('Aktif', 'Hanya Jemaat Aktif')">Hanya Jemaat Aktif</li>
                    <li onclick="selectOption('Non-Aktif', 'Jemaat Non-Aktif/Meninggal')">Jemaat Non-Aktif/Meninggal</li>
                    <li class="opt-group">Berdasarkan Sektor</li>
                    <li onclick="selectOption('Galatia', 'Sektor Galatia')">Sektor Galatia</li>
                    <li onclick="selectOption('Filipi', 'Sektor Filipi')">Sektor Filipi</li>
                    <li onclick="selectOption('Yudea', 'Sektor Yudea')">Sektor Yudea</li>
                    <li class="opt-group">Berdasarkan Pelkat</li>
                    <li onclick="selectOption('PA', 'Pelkat PA')">Pelkat PA</li>
                    <li onclick="selectOption('PT', 'Pelkat PT')">Pelkat PT</li>
                    <li onclick="selectOption('GP', 'Pelkat GP')">Pelkat GP</li>
                    <li onclick="selectOption('PKP', 'Pelkat PKP')">Pelkat PKP</li>
                    <li onclick="selectOption('PKB', 'Pelkat PKB')">Pelkat PKB</li>
                    <li onclick="selectOption('PKLU', 'Pelkat PKLU')">Pelkat PKLU</li>
                </ul>
            </div>
        </div>

    <div class="refresh-group">
        <label>&nbsp;</label>
        <button type="button" class="btn-refresh" id="btn-refresh-jemaat">
            <span class="material-symbols-outlined">refresh</span> 
            Refresh
        </button>
    </div>
    </div> 
</div>

                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Nama Lengkap</th>
                                    <th>Jenis Kelamin</th>
                                    <th>Pelkat</th>
                                    <th>Sektor</th>
                                    <th>Baptis</th>
                                    <th>Sidi</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(mysqli_num_rows($tampil_jemaat) > 0): ?>
                                    <?php while($row = mysqli_fetch_assoc($tampil_jemaat)): ?>
                                    <tr class="data-row">
                                        <td><?= $row['nama_lengkap']; ?></td>
                                        <td><?= $row['gender']; ?></td>
                                        <td><?= $row['pelkat']; ?></td>
                                        <td><?= $row['sektor']; ?></td>
                                        <td>
                                            <span class="status-badge <?= ($row['status_baptis'] == 'Sudah') ? 'status-aktif' : 'status-non'; ?>">
                                                <?= $row['status_baptis'] ?: 'Belum'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="status-badge <?= ($row['status_sidi'] == 'Sudah') ? 'status-aktif' : 'status-non'; ?>">
                                                <?= $row['status_sidi'] ?: 'Belum'; ?>
                                            </span>
                                        </td>
                                        <td>
                                                <?php 
                                                    $status_data = $row['status'] ?: 'None'; 
                                                    $class_status = 'status-none'; // Default abu-abu
                                                    if ($status_data == 'Aktif') $class_status = 'status-aktif';
                                                    if ($status_data == 'Non-Aktif') $class_status = 'status-non';
                                                ?>
                                                <span class="status-badge <?= $class_status; ?>">
                                                    <?= ($status_data == 'None') ? '-' : $status_data; ?> </span>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="btn-detail" onclick="showDetailModal(<?= htmlspecialchars(json_encode($row)); ?>)">
                                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                                        <circle cx="12" cy="12" r="3"></circle>
                                                    </svg>
                                                </button>
                                                <button class="btn-edit" onclick="showEditModal(<?= htmlspecialchars(json_encode($row)); ?>)">
                                                    <img src="../icon/edit.png" alt="Edit" width="16">
                                                </button>
                                                <button onclick="confirmDelete(<?= $row['id']; ?>)" class="btn-delete">
                                                    <img src="../icon/delete.png" alt="Hapus" width="16">
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                                    <tr id="noDataRow" style="<?= (mysqli_num_rows($tampil_jemaat) > 0) ? 'display: none;' : ''; ?>">
                                        <td colspan="7" class="empty-state-container">
                                            <div class="empty-state">
                                                <p>Belum ada data jemaat yang sesuai.</p>
                                                <span>Silakan klik tombol "+ Tambah Jemaat" untuk menambahkan.</span>
                                            </div>
                                        </td>
                                    </tr>
                            </tbody>
                        </table>
                    </div> <div class="table-footer">
                        <div class="show-entries">
                            <span>Tampilkan</span>
                            <select id="entryLimit" onchange="changeLimit(this.value)">
                                <option value="10" <?= $limit == 10 ? 'selected' : ''; ?>>10</option>
                                <option value="20" <?= $limit == 20 ? 'selected' : ''; ?>>20</option>
                                <option value="50" <?= $limit == 30 ? 'selected' : ''; ?>>30</option>
                            </select>
                            <span>data per halaman.</span>
                        </div>
                        <div class="pagination">
                            <span><?= ($offset + 1); ?>-<?= min($offset + $limit, $total_data); ?> dari <?= $total_data; ?></span>
                            <div class="pagination-buttons">
                                <?php if($halaman > 1): ?>
                                    <a href="?halaman=<?= $halaman - 1; ?>&limit=<?= $limit; ?>" class="btn-page"><</a>
                                <?php endif; ?>
                                
                                <button class="btn-page active"><?= $halaman; ?></button>
                                
                                <?php if($halaman < $total_halaman): ?>
                                    <a href="?halaman=<?= $halaman + 1; ?>&limit=<?= $limit; ?>" class="btn-page">></a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
    </div>
        <footer class="main-footer">
            <p>&copy; 2026 Church Management System - Versi 3.0</p>
        </footer>
    </div>

<div id="modalJemaat" class="modal">
    <div class="modal-content">
        <div class="modal-header"><h3>Tambah Jemaat Baru</h3>
        <span class="close" onclick="closeModal()">&times;</span>
    </div>
        <form action="proses_jemaat.php" method="POST">
            <div class="form-grid">
                <div class="form-group"><label>No KK</label><input type="text" name="no_kk" placeholder="123456XXXXXXXXXX" minlength="16" maxlength="16" pattern="\d{16}" inputmode="numeric" title="No KK harus berupa 16 digit angka" required></div>
                
                <div class="form-group">
                    <label>Posisi</label>
                    <div class="custom-select-wrapper">
                        <select name="posisi" id="add_posisi" style="display:none;" required>
                            <option value="">Pilih Posisi...</option>
                            <option value="Kepala Keluarga">Kepala Keluarga</option>
                            <option value="Istri">Istri</option>
                            <option value="Anak">Anak</option>
                        </select>
                        <div class="custom-select" onclick="toggleModalDropdown('addPosisiOptions')">
                            <span id="addPosisiDisplay">Pilih Posisi...</span><div class="custom-arrow"></div>
                        </div>
                        <ul class="custom-options" id="addPosisiOptions">
                            <li onclick="selectModalOption('add_posisi', 'addPosisiDisplay', 'Kepala Keluarga', 'Kepala Keluarga', 'addPosisiOptions')">Kepala Keluarga</li>
                            <li onclick="selectModalOption('add_posisi', 'addPosisiDisplay', 'Istri', 'Istri', 'addPosisiOptions')">Istri</li>
                            <li onclick="selectModalOption('add_posisi', 'addPosisiDisplay', 'Anak', 'Anak', 'addPosisiOptions')">Anak</li>
                        </ul>
                    </div>
                </div>

                <div class="form-group"><label>NIK</label><input type="text" name="nik" placeholder="123456XXXXXXXXXX" minlength="16" maxlength="16" pattern="\d{16}" inputmode="numeric" title="No KK harus berupa 16 digit angka" required></div>
                <div class="form-group"><label>Nama Lengkap</label><input type="text" name="nama" placeholder="Contoh: Immanoel" required></div>

                <div class="form-row-3 full-width">
                    <div class="form-group">
                        <label>Jenis Kelamin</label>
                        <div class="custom-select-wrapper">
                            <select name="gender" id="add_gender" style="display:none;" required>
                                <option value="">Pilih Gender...</option>
                                <option value="Laki-laki">Laki-laki</option>
                                <option value="Perempuan">Perempuan</option>
                            </select>
                            <div class="custom-select" onclick="toggleModalDropdown('addGenderOptions')">
                                <span id="addGenderDisplay">Pilih Jenis Kelamin...</span><div class="custom-arrow"></div>
                            </div>
                            <ul class="custom-options" id="addGenderOptions">
                                <li onclick="selectModalOption('add_gender', 'addGenderDisplay', 'Laki-laki', 'Laki-laki', 'addGenderOptions')">Laki-laki</li>
                                <li onclick="selectModalOption('add_gender', 'addGenderDisplay', 'Perempuan', 'Perempuan', 'addGenderOptions')">Perempuan</li>
                            </ul>
                        </div>
                    </div>
                    <div class="form-group"><label>Tempat Lahir</label><input type="text" name="tmpt_lahir"  placeholder="Contoh: Medan"></div>
                    <div class="form-group"><label>Tgl Lahir</label><input type="date" name="tgl_lahir"></div>
                </div>

                <div class="form-row-3 full-width">
                    <div class="form-group">
                        <label>Pelkat</label>
                        <div class="custom-select-wrapper">
                            <select name="pelkat" id="add_pelkat" style="display:none;">
                                <option value="">Pilih Pelkat...</option>
                                <option value="PA">PA</option><option value="PT">PT</option><option value="GP">GP</option>
                                <option value="PKP">PKP</option><option value="PKB">PKB</option><option value="PKLU">PKLU</option>
                            </select>
                            <div class="custom-select" onclick="toggleModalDropdown('addPelkatOptions')">
                                <span id="addPelkatDisplay">Pilih Pelkat...</span><div class="custom-arrow"></div>
                            </div>
                            <ul class="custom-options" id="addPelkatOptions">
                                <li onclick="selectModalOption('add_pelkat', 'addPelkatDisplay', 'PA', 'PA', 'addPelkatOptions')">PA</li>
                                <li onclick="selectModalOption('add_pelkat', 'addPelkatDisplay', 'PT', 'PT', 'addPelkatOptions')">PT</li>
                                <li onclick="selectModalOption('add_pelkat', 'addPelkatDisplay', 'GP', 'GP', 'addPelkatOptions')">GP</li>
                                <li onclick="selectModalOption('add_pelkat', 'addPelkatDisplay', 'PKP', 'PKP', 'addPelkatOptions')">PKP</li>
                                <li onclick="selectModalOption('add_pelkat', 'addPelkatDisplay', 'PKB', 'PKB', 'addPelkatOptions')">PKB</li>
                                <li onclick="selectModalOption('add_pelkat', 'addPelkatDisplay', 'PKLU', 'PKLU', 'addPelkatOptions')">PKLU</li>
                            </ul>
                        </div>
                    </div>
                    <div class="form-group"><label>No HP / WA</label><input type="text" name="no_hp" placeholder="0812XXXXXXXX"></div>
                    
                    <div class="form-group">
                        <label>Status Nikah</label>
                        <div class="custom-select-wrapper">
                            <select name="status_nikah" id="add_status_nikah" style="display:none;">
                                <option value="">Pilih Status Nikah...</option>
                                <option value="Belum Menikah">Belum Menikah</option>
                                <option value="Menikah">Menikah</option>
                                <option value="Janda/Duda">Janda/Duda</option>
                            </select>
                            <div class="custom-select" onclick="toggleModalDropdown('addNikahOptions')">
                                <span id="addNikahDisplay">Pilih Status Nikah...</span><div class="custom-arrow"></div>
                            </div>
                            <ul class="custom-options" id="addNikahOptions">
                                <li onclick="selectModalOption('add_status_nikah', 'addNikahDisplay', 'Belum Menikah', 'Belum Menikah', 'addNikahOptions')">Belum Menikah</li>
                                <li onclick="selectModalOption('add_status_nikah', 'addNikahDisplay', 'Menikah', 'Menikah', 'addNikahOptions')">Menikah</li>
                                <li onclick="selectModalOption('add_status_nikah', 'addNikahDisplay', 'Janda/Duda', 'Janda/Duda', 'addNikahOptions')">Janda/Duda</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="form-row-3 full-width">
                    <div class="form-group">
                        <label>Status Baptis</label>
                        <div class="custom-select-wrapper">
                            <select name="status_baptis" id="add_status_baptis" style="display:none;">
                                <option value="">Pilih Status Baptis...</option>
                                <option value="Belum">Belum</option>
                                <option value="Sudah">Sudah</option>
                            </select>
                            <div class="custom-select" onclick="toggleModalDropdown('addBaptisOptions')">
                                <span id="addBaptisDisplay">Pilih Status Baptis...</span>
                                <div class="custom-arrow"></div>
                            </div>
                            <ul class="custom-options" id="addBaptisOptions">
                                <li onclick="selectModalOption('add_status_baptis', 'addBaptisDisplay', 'Belum', 'Belum', 'addBaptisOptions')">Belum</li>
                                <li onclick="selectModalOption('add_status_baptis', 'addBaptisDisplay', 'Sudah', 'Sudah', 'addBaptisOptions')">Sudah</li>
                            </ul>
                        </div>
                    </div>
                    <div class="form-group"><label>Tempat Baptis</label><input type="text" name="tempat_baptis" placeholder="Contoh: GPIB Yoppe"></div>
                    <div class="form-group"><label>Tgl Baptis</label><input type="date" name="tgl_baptis"></div>
                </div>

                <div class="form-row-3 full-width">
                    <div class="form-group">
                        <label>Status Sidi</label>
                        <div class="custom-select-wrapper">
                            <select name="status_sidi" id="add_status_sidi" style="display:none;">
                                <option value="">Pilih Status Sidi...</option>
                                <option value="Belum">Belum</option>
                                <option value="Sudah">Sudah</option>
                            </select>
                            <div class="custom-select" onclick="toggleModalDropdown('addSidiOptions')">
                                <span id="addSidiDisplay">Pilih Status Sidi...</span>
                                <div class="custom-arrow"></div>
                            </div>
                            <ul class="custom-options" id="addSidiOptions">
                                <li onclick="selectModalOption('add_status_sidi', 'addSidiDisplay', 'Belum', 'Belum', 'addSidiOptions')">Belum</li>
                                <li onclick="selectModalOption('add_status_sidi', 'addSidiDisplay', 'Sudah', 'Sudah', 'addSidiOptions')">Sudah</li>
                            </ul>
                        </div>
                    </div>
                    <div class="form-group"><label>Tempat Sidi</label><input type="text" name="tempat_sidi" placeholder="Contoh: GPIB Yoppe"></div>
                    <div class="form-group"><label>Tgl Sidi</label><input type="date" name="tgl_sidi"></div>
                </div>


                <div class="form-grid full-width" style="margin-bottom: 20px;">
                    <div class="form-group">
                        <label>Sektor</label>
                        <div class="custom-select-wrapper">
                            <select name="sektor" id="add_sektor" style="display:none;" required>
                                <option value="">Pilih Sektor...</option>
                                <option value="Galatia">Galatia</option>
                                <option value="Filipi">Filipi</option>
                                <option value="Yudea">Yudea</option>
                            </select>
                            <div class="custom-select" onclick="toggleModalDropdown('addSektorOptions')">
                                <span id="addSektorDisplay">Pilih Sektor...</span><div class="custom-arrow"></div>
                            </div>
                            <ul class="custom-options" id="addSektorOptions">
                                <li onclick="selectModalOption('add_sektor', 'addSektorDisplay', 'Galatia', 'Galatia', 'addSektorOptions')">Galatia</li>
                                <li onclick="selectModalOption('add_sektor', 'addSektorDisplay', 'Filipi', 'Filipi', 'addSektorOptions')">Filipi</li>
                                <li onclick="selectModalOption('add_sektor', 'addSektorDisplay', 'Yudea', 'Yudea', 'addSektorOptions')">Yudea</li>
                            </ul>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Status Keanggotaan Jemaat</label>
                        <div class="custom-select-wrapper">
                            <select name="status_aktif" id="add_status_aktif" style="display:none;">
                                <option value="None">Pilih Status...</option>
                                <option value="Aktif">Aktif</option>
                                <option value="Non-Aktif">Non-Aktif</option>
                            </select>
                            <div class="custom-select" onclick="toggleModalDropdown('addStatusAktifOptions')">
                                <span id="addStatusAktifDisplay">Pilih Status...</span><div class="custom-arrow"></div>
                            </div>
                            <ul class="custom-options" id="addStatusAktifOptions">
                                <li onclick="selectModalOption('add_status_aktif', 'addStatusAktifDisplay', 'None', 'Pilih Status...', 'addStatusAktifOptions')">Pilih Status...</li>
                                <li onclick="selectModalOption('add_status_aktif', 'addStatusAktifDisplay', 'Aktif', 'Aktif', 'addStatusAktifOptions')">Aktif</li>
                                <li onclick="selectModalOption('add_status_aktif', 'addStatusAktifDisplay', 'Non-Aktif', 'Non-Aktif', 'addStatusAktifOptions')">Non-Aktif</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="form-group full-width"><label>Alamat</label><textarea name="alamat" rows="2" placeholder="Contoh: JL. Serangkai No.12 ..."></textarea></div>
            </div>
            
            <div class="form-group full-width" style="background-color: #fceaea; padding: 15px; border-radius: 8px; border: 1px solid #f5c2c7; margin-top: 10px;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <input type="checkbox" name="is_non_aktif" id="checkNonAktif" style="width: 18px; height: 18px; cursor: pointer;">
                    <label for="checkNonAktif" style="color: #842029; font-weight: 600; cursor: pointer; margin: 0;">Jemaat ini sudah Meninggal Dunia / Non-Aktif</label>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeModal()">Batal</button>
                <button type="submit" class="btn-save">Simpan Data</button>
            </div>
        </form>
    </div>
</div>

<div id="modalEditJemaat" class="modal">
    <div class="modal-content">
        <div class="modal-header"><h3>Edit Data Jemaat</h3>
        <span class="close" onclick="closeEditModal()">&times;</span>
    </div>
        <form action="update_jemaat.php" method="POST">
            <input type="hidden" name="id" id="edit_id">
            <div class="form-grid">
                <div class="form-group"><label>No KK</label><input type="text" name="no_kk" id="edit_no_kk" required></div>
                
                <div class="form-group">
                    <label>Posisi</label>
                    <div class="custom-select-wrapper">
                        <select name="posisi" id="edit_posisi" style="display:none;">
                            <option value="Kepala Keluarga">Kepala Keluarga</option>
                            <option value="Istri">Istri</option>
                            <option value="Anak">Anak</option>
                        </select>
                        <div class="custom-select" onclick="toggleModalDropdown('editPosisiOptions')">
                            <span id="editPosisiDisplay">Pilih Posisi...</span><div class="custom-arrow"></div>
                        </div>
                        <ul class="custom-options" id="editPosisiOptions">
                            <li onclick="selectModalOption('edit_posisi', 'editPosisiDisplay', 'Kepala Keluarga', 'Kepala Keluarga', 'editPosisiOptions')">Kepala Keluarga</li>
                            <li onclick="selectModalOption('edit_posisi', 'editPosisiDisplay', 'Istri', 'Istri', 'editPosisiOptions')">Istri</li>
                            <li onclick="selectModalOption('edit_posisi', 'editPosisiDisplay', 'Anak', 'Anak', 'editPosisiOptions')">Anak</li>
                        </ul>
                    </div>
                </div>

                <div class="form-group"><label>NIK</label><input type="text" name="nik" id="edit_nik"></div>
                <div class="form-group"><label>Nama Lengkap</label><input type="text" name="nama" id="edit_nama"></div>

                <div class="form-row-3 full-width">
                    <div class="form-group">
                        <label>Jenis Kelamin</label>
                        <div class="custom-select-wrapper">
                            <select name="gender" id="edit_gender" style="display:none;">
                                <option value="Laki-laki">Laki-laki</option>
                                <option value="Perempuan">Perempuan</option>
                            </select>
                            <div class="custom-select" onclick="toggleModalDropdown('editGenderOptions')">
                                <span id="editGenderDisplay">Pilih Jenis Kelamin...</span><div class="custom-arrow"></div>
                            </div>
                            <ul class="custom-options" id="editGenderOptions">
                                <li onclick="selectModalOption('edit_gender', 'editGenderDisplay', 'Laki-laki', 'Laki-laki', 'editGenderOptions')">Laki-laki</li>
                                <li onclick="selectModalOption('edit_gender', 'editGenderDisplay', 'Perempuan', 'Perempuan', 'editGenderOptions')">Perempuan</li>
                            </ul>
                        </div>
                    </div>
                    <div class="form-group"><label>Tempat Lahir</label><input type="text" name="tmpt_lahir" id="edit_tmpt_lahir"></div>
                    <div class="form-group"><label>Tgl Lahir</label><input type="date" name="tgl_lahir" id="edit_tgl_lahir"></div>
                </div>

                <div class="form-row-3 full-width">
                    <div class="form-group">
                        <label>Pelkat</label>
                        <div class="custom-select-wrapper">
                            <select name="pelkat" id="edit_pelkat" style="display:none;">
                                <option value="PA">PA</option><option value="PT">PT</option><option value="GP">GP</option>
                                <option value="PKP">PKP</option><option value="PKB">PKB</option><option value="PKLU">PKLU</option>
                            </select>
                            <div class="custom-select" onclick="toggleModalDropdown('editPelkatOptions')">
                                <span id="editPelkatDisplay">Pilih Pelkat...</span><div class="custom-arrow"></div>
                            </div>
                            <ul class="custom-options" id="editPelkatOptions">
                                <li onclick="selectModalOption('edit_pelkat', 'editPelkatDisplay', 'PA', 'PA', 'editPelkatOptions')">PA</li>
                                <li onclick="selectModalOption('edit_pelkat', 'editPelkatDisplay', 'PT', 'PT', 'editPelkatOptions')">PT</li>
                                <li onclick="selectModalOption('edit_pelkat', 'editPelkatDisplay', 'GP', 'GP', 'editPelkatOptions')">GP</li>
                                <li onclick="selectModalOption('edit_pelkat', 'editPelkatDisplay', 'PKP', 'PKP', 'editPelkatOptions')">PKP</li>
                                <li onclick="selectModalOption('edit_pelkat', 'editPelkatDisplay', 'PKB', 'PKB', 'editPelkatOptions')">PKB</li>
                                <li onclick="selectModalOption('edit_pelkat', 'editPelkatDisplay', 'PKLU', 'PKLU', 'editPelkatOptions')">PKLU</li>
                            </ul>
                        </div>
                    </div>
                    <div class="form-group"><label>No HP / WA</label><input type="text" name="no_hp" id="edit_no_hp"></div>
                    
                    <div class="form-group">
                        <label>Status Nikah</label>
                        <div class="custom-select-wrapper">
                            <select name="status_nikah" id="edit_status_nikah" style="display:none;">
                                <option value="Belum Menikah">Belum Menikah</option>
                                <option value="Menikah">Menikah</option>
                                <option value="Janda/Duda">Janda/Duda</option>
                            </select>
                            <div class="custom-select" onclick="toggleModalDropdown('editNikahOptions')">
                                <span id="editStatusNikahDisplay">Pilih Status Nikah...</span><div class="custom-arrow"></div>
                            </div>
                            <ul class="custom-options" id="editNikahOptions">
                                <li onclick="selectModalOption('edit_status_nikah', 'editStatusNikahDisplay', 'Belum Menikah', 'Belum Menikah', 'editNikahOptions')">Belum Menikah</li>
                                <li onclick="selectModalOption('edit_status_nikah', 'editStatusNikahDisplay', 'Menikah', 'Menikah', 'editNikahOptions')">Menikah</li>
                                <li onclick="selectModalOption('edit_status_nikah', 'editStatusNikahDisplay', 'Janda/Duda', 'Janda/Duda', 'editNikahOptions')">Janda/Duda</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="form-row-3 full-width">
                    <div class="form-group">
                        <label>Status Baptis</label>
                        <div class="custom-select-wrapper">
                            <select name="status_baptis" id="edit_status_baptis" style="display:none;">
                                <option value="Belum">Belum</option>
                                <option value="Sudah">Sudah</option>
                            </select>
                            <div class="custom-select" onclick="toggleModalDropdown('editBaptisOptions')">
                                <span id="editBaptisDisplay">Belum</span>
                                <div class="custom-arrow"></div>
                            </div>
                            <ul class="custom-options" id="editBaptisOptions">
                                <li onclick="selectModalOption('edit_status_baptis', 'editBaptisDisplay', 'Belum', 'Belum', 'editBaptisOptions')">Belum</li>
                                <li onclick="selectModalOption('edit_status_baptis', 'editBaptisDisplay', 'Sudah', 'Sudah', 'editBaptisOptions')">Sudah</li>
                            </ul>
                        </div>
                    </div>
                    <div class="form-group"><label>Tempat Baptis</label><input type="text" name="tempat_baptis" id="edit_tempat_baptis"></div>
                    <div class="form-group"><label>Tgl Baptis</label><input type="date" name="tgl_baptis" id="edit_tgl_baptis"></div>
                </div>

                <div class="form-row-3 full-width">
                    <div class="form-group">
                        <label>Status Sidi</label>
                        <div class="custom-select-wrapper">
                            <select name="status_sidi" id="edit_status_sidi" style="display:none;">
                                <option value="Belum">Belum</option>
                                <option value="Sudah">Sudah</option>
                            </select>
                            <div class="custom-select" onclick="toggleModalDropdown('editSidiOptions')">
                                <span id="editSidiDisplay">Belum</span>
                                <div class="custom-arrow"></div>
                            </div>
                            <ul class="custom-options" id="editSidiOptions">
                                <li onclick="selectModalOption('edit_status_sidi', 'editSidiDisplay', 'Belum', 'Belum', 'editSidiOptions')">Belum</li>
                                <li onclick="selectModalOption('edit_status_sidi', 'editSidiDisplay', 'Sudah', 'Sudah', 'editSidiOptions')">Sudah</li>
                            </ul>
                        </div>
                    </div>
                    <div class="form-group"><label>Tempat Sidi</label><input type="text" name="tempat_sidi" id="edit_tempat_sidi"></div>
                    <div class="form-group"><label>Tgl Sidi</label><input type="date" name="tgl_sidi" id="edit_tgl_sidi"></div>
                </div>

                <div class="form-grid full-width" style="margin-bottom: 20px;">
                    <div class="form-group">
                        <label>Sektor</label>
                        <div class="custom-select-wrapper">
                            <select name="sektor" id="edit_sektor" style="display:none;" required>
                                <option value="">Pilih Sektor...</option>
                                <option value="Galatia">Galatia</option>
                                <option value="Filipi">Filipi</option>
                                <option value="Yudea">Yudea</option>
                            </select>
                            <div class="custom-select" onclick="toggleModalDropdown('editSektorOptions')">
                                <span id="editSektorDisplay">Pilih Sektor...</span><div class="custom-arrow"></div>
                            </div>
                            <ul class="custom-options" id="editSektorOptions">
                                <li onclick="selectModalOption('edit_sektor', 'editSektorDisplay', 'Galatia', 'Galatia', 'editSektorOptions')">Galatia</li>
                                <li onclick="selectModalOption('edit_sektor', 'editSektorDisplay', 'Filipi', 'Filipi', 'editSektorOptions')">Filipi</li>
                                <li onclick="selectModalOption('edit_sektor', 'editSektorDisplay', 'Yudea', 'Yudea', 'editSektorOptions')">Yudea</li>
                            </ul>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Status Keanggotaan Jemaat</label>
                        <div class="custom-select-wrapper">
                            <select name="status_aktif" id="edit_status_aktif" style="display:none;">
                                <option value="None">Pilih Status...</option>
                                <option value="Aktif">Aktif</option>
                                <option value="Non-Aktif">Non-Aktif</option>
                            </select>
                            <div class="custom-select" onclick="toggleModalDropdown('editStatusAktifOptions')">
                                <span id="editStatusAktifDisplay">Pilih Status...</span><div class="custom-arrow"></div>
                            </div>
                            <ul class="custom-options" id="editStatusAktifOptions">
                                <li onclick="selectModalOption('edit_status_aktif', 'editStatusAktifDisplay', 'None', 'Pilih Status...', 'editStatusAktifOptions')">Pilih Status...</li>
                                <li onclick="selectModalOption('edit_status_aktif', 'editStatusAktifDisplay', 'Aktif', 'Aktif', 'editStatusAktifOptions')">Aktif</li>
                                <li onclick="selectModalOption('edit_status_aktif', 'editStatusAktifDisplay', 'Non-Aktif', 'Non-Aktif', 'editStatusAktifOptions')">Non-Aktif</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="form-group full-width"><label>Alamat</label><textarea name="alamat" id="edit_alamat" rows="2"></textarea></div>
            </div>
            
            <div class="form-group full-width" style="background-color: #fceaea; padding: 15px; border-radius: 8px; border: 1px solid #f5c2c7; margin-top: 10px;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <input type="checkbox" name="is_non_aktif" id="edit_check_status" style="width: 18px; height: 18px; cursor: pointer;">
                    <label for="edit_check_status" style="color: #842029; font-weight: 600; cursor: pointer; margin: 0;">Jemaat ini sudah Meninggal Dunia / Non-Aktif</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeEditModal()">Batal</button>
                <button type="submit" class="btn-save">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<div id="modalDetailJemaat" class="modal">
    <div class="modal-content" style="width: 800px;">
        <div class="modal-header">
            <h3>Detail Informasi Jemaat</h3>
            <span class="close" onclick="closeDetailModal()">&times;</span>
        </div>
        <div class="detail-grid">
            <div class="detail-group"><label>Nama Lengkap</label><p id="det_nama">-</p></div>
            <div class="detail-group"><label>Jenis Kelamin</label><p id="det_gender">-</p></div>
            <div class="detail-group"><label>Tanggal Lahir</label><p id="det_tgl_lahir">-</p></div>
            
            <div class="detail-group"><label>Pelkat</label><p id="det_pelkat">-</p></div>
            <div class="detail-group"><label>No HP / WA</label><p id="det_no_hp">-</p></div>
            <div class="detail-group"><label>Status Nikah</label><p id="det_status_nikah">-</p></div>
            
            <div class="detail-group"><label>Status Baptis</label><p id="det_status_baptis">-</p></div>
            <div class="detail-group"><label>Tempat Baptis</label><p id="det_tempat_baptis">-</p></div>
            <div class="detail-group"><label>Tanggal Baptis</label><p id="det_tgl_baptis">-</p></div>
            
            <div class="detail-group"><label>Status Sidi</label><p id="det_status_sidi">-</p></div>
            <div class="detail-group"><label>Tempat Sidi</label><p id="det_tempat_sidi">-</p></div>
            <div class="detail-group"><label>Tanggal Sidi</label><p id="det_tgl_sidi">-</p></div>
            
            <div class="detail-row-2-full">
                <div class="detail-group"><label>Sektor</label><p id="det_sektor">-</p></div>
                <div class="detail-group"><label>Status Keanggotaan</label><p id="det_status">-</p></div>
            </div>
            
            <div class="detail-group full-width"><label>Alamat Lengkap</label><p id="det_alamat">-</p></div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-cancel" onclick="closeDetailModal()">Tutup</button>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="jemaat.js"></script>
</body>
</html>