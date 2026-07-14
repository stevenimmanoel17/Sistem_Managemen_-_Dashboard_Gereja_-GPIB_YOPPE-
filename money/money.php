<?php
session_start();

// Koneksi database
$conn = mysqli_connect("localhost", "root", "", "db_gpib");

// Tangkap pilihan periode dari URL, defaultnya 'Bulan Ini'
$periode = $_GET['periode'] ?? 'Semua Waktu';

// Tentukan filter SQL berdasarkan pilihan
if ($periode == 'Hari Ini') {
    $filter_sql = "WHERE DATE(tanggal) = CURRENT_DATE()";
} elseif ($periode == 'Bulan Ini') {
    $filter_sql = "WHERE MONTH(tanggal) = MONTH(CURRENT_DATE()) AND YEAR(tanggal) = YEAR(CURRENT_DATE())";
} elseif ($periode == 'Tahun Ini') {
    $filter_sql = "WHERE YEAR(tanggal) = YEAR(CURRENT_DATE())";
} else {
    $filter_sql = ""; // Semua Waktu
}

// Hitung Pemasukan
$query_in = "SELECT SUM(nominal) as total FROM keuangan $filter_sql " . ($filter_sql ? "AND" : "WHERE") . " tipe = 'pemasukan'";
$res_in = mysqli_query($conn, $query_in); 
$pemasukan = mysqli_fetch_assoc($res_in)['total'] ?? 0;

// Hitung Pengeluaran
$query_out = "SELECT SUM(nominal) as total FROM keuangan $filter_sql " . ($filter_sql ? "AND" : "WHERE") . " tipe = 'pengeluaran'";
$res_out = mysqli_query($conn, $query_out); 
$pengeluaran = mysqli_fetch_assoc($res_out)['total'] ?? 0;

// Hitung Saldo Akhir
$saldo_akhir = $pemasukan - $pengeluaran;

// Ambil daftar transaksi terbaru
$query_transaksi = "SELECT * FROM keuangan $filter_sql ORDER BY tanggal DESC, id DESC";
$tampil_transaksi = mysqli_query($conn, $query_transaksi);

if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: ../login/Login.php");
    exit;
}

$nama_user = $_SESSION['username'];
$waktu_masuk_sistem = $_SESSION['waktu_login'] ?? date('d/m/Y H:i'); 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keuangan</title>
    <link rel="icon" type="image/png" href="../icon/GPIB-NoCapt.png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <link rel="stylesheet" href="money.css">
</head>
<body>
    <div id="loading-screen">
        <div class="loader-content">
            <img src="../icon/GPIB-NoCapt.png" alt="Logo" class="loader-logo">
            <div class="progress-container">
                <div class="progress-bar"></div>
            </div>
            <p>Memuat Keuangan...</p>
        </div>
    </div>

    <div class="sidebar">
        <div class="brand">
            <img src="../icon/Chruch.png" alt="Logo" class="logo">
            <h1>GPIB <i>"Yoppe"</i><br>Belawan</h1>
        </div>

        <nav class="nav-menu">
            <a href="../index.php" class="nav-link">
                <img src="../icon/dashboard.png" class="menu-icon"> Dashboard
            </a>
            <a href="money.php" class="nav-link active">
                <img src="../icon/money.png" class="menu-icon"> Keuangan
            </a>
            <a href="../jemaat/jemaat.php" class="nav-link">
                <img src="../icon/Jemaat.png" class="menu-icon"> Data Jemaat
            </a>
            <a href="../keluarga/keluarga.php" class="nav-link">
                <img src="../icon/keluarga.png" class="menu-icon"> Data Keluarga
            </a>
            <a href="../inventaris/inventaris.php" class="nav-link">
                <img src="../icon/inventory.png" class="menu-icon"> Inventaris
            </a>
            <a href="../laporan/laporan.php" class="nav-link">
                <img src="../icon/report.png" class="menu-icon"> Laporan
            </a>
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'Super Admin'): ?>
                <a href="../pengaturan/pengaturan.php" class="nav-link">
                    <img src="../icon/settings.png" class="menu-icon"> <span>Pengaturan</span>
                </a>
            <?php endif; ?>
        </nav>

        <div class="sidebar-footer">
            <hr class="divider">
            <a href="#" class="logout-link" id="logoutBtn">
                <img src="../icon/logout1.png" class="menu-icon"> Log Out
            </a>
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
                    <h2>Keuangan & Kas</h2>
                    <p style="font-size: 16px; color: #666; margin-top: 5px;">Kelola pemasukan dan pengeluaran gereja secara transparan.</p>
                </div>
                <div class="header-buttons">
                    <button class="btn-add" onclick="showModalTambah()">+ Catat Transaksi</button>
                </div>
            </header>

            <div class="filter-section">
                <div class="filter-group">
                    <img src="../icon/funnel.png" class="filter-icon">
                    <label>Periode:</label>
                    <div class="custom-dropdown" id="dropdownPeriode" onclick="this.classList.toggle('active'); event.stopPropagation();">
                        <div class="dropdown-selected">
                            <?php echo $periode; ?>
                            <img src="../icon/chevron.png" class="chevron-icon">
                        </div>
                        <div class="dropdown-options">
                            <div class="option" onclick="filterPeriode('Hari Ini', event)">Hari Ini</div>
                            <div class="option" onclick="filterPeriode('Bulan Ini', event)">Bulan Ini</div>
                            <div class="option" onclick="filterPeriode('Tahun Ini', event)">Tahun Ini</div>
                            <div class="option" onclick="filterPeriode('Semua Waktu', event)">Semua Waktu</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="stats-container">
                <div class="stat-card green">
                    <div class="stat-header">
                        <h4>PEMASUKAN</h4>
                        <img src="../icon/fat-line-up-green.png" class="stat-icon-img" style="opacity: 0.5;">
                    </div>
                    <span class="stat-number">Rp <?php echo number_format($pemasukan, 0, ',', '.'); ?></span>
                </div>
                <div class="stat-card red">
                    <div class="stat-header">
                        <h4>PENGELUARAN</h4>
                        <img src="../icon/fat-line-up-red.png" class="stat-icon-img" style="opacity: 0.5;">
                    </div>
                    <span class="stat-number">Rp <?php echo number_format($pengeluaran, 0, ',', '.'); ?></span>
                </div>
                <div class="stat-card blue">
                    <div class="stat-header">
                        <h4>MUARA / SALDO</h4>
                        <img src="../icon/wallet.png" class="stat-icon-img" style="opacity: 0.5;">
                    </div>
                    <span class="stat-number">Rp <?php echo number_format($saldo_akhir, 0, ',', '.'); ?></span>
                </div>
            </div>

            <div class="table-container">
                <table class="transaction-table">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Kategori</th>
                            <th>Keterangan</th>
                            <th>Nominal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($tampil_transaksi) > 0) {
                            while ($row = mysqli_fetch_assoc($tampil_transaksi)) { 
                                $warna_teks = ($row['tipe'] == 'pemasukan') ? 'text-green' : 'text-red';
                                $simbol = ($row['tipe'] == 'pemasukan') ? '+' : '-'; ?>
                            <tr>
                                <td><?php echo date('d/m/Y', strtotime($row['tanggal'])); ?></td>
                                <td><?php echo $row['kategori']; ?></td>
                                <td><?php echo $row['keterangan']; ?></td>
                                <td class="<?php echo $warna_teks; ?>">
                                    <strong><?php echo $simbol; ?> Rp <?php echo number_format($row['nominal'], 0, ',', '.'); ?></strong>
                                </td>
                                <td>
                                    <a href="javascript:void(0)" class="btn-edit" onclick="editTransaksi(<?php echo $row['id']; ?>)">Edit</a>
                                    <a href="javascript:void(0)" class="btn-delete" onclick="confirmDelete(<?php echo $row['id']; ?>)">Hapus</a>
                                </td>
                            </tr>
                        <?php } } else { ?>
                            <tr>
                                <td colspan="5" style="text-align: center; color: #888; padding: 40px;">Belum ada data transaksi yang tercatat.</td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div> <footer class="main-footer">
            <p>&copy; 2026 Church Management System - Versi 3.0</p>
        </footer>
    </div>

<div id="modalTransaksi" class="modal">
    <div class="modal-content">
        <div class="modal-header-box">
            <h3 class="modal-title" id="modalTitle">Catat Transaksi</h3>
            <span class="close-x" onclick="closeModal()">&times;</span>
        </div>
        <hr class="divider">
        
        <form action="proses_simpan.php" method="POST" id="formTransaksi">
            <input type="hidden" name="id" id="editId">
            
            <div class="form-group">
                <label>Tanggal</label>
                <input type="date" name="tanggal" id="editTanggal" required class="form-input">
            </div>
            
            <div class="form-group">
                <label>Tipe Transaksi</label>
                <div class="type-selection">
                    <input type="radio" name="tipe" value="pemasukan" id="type-in" checked required>
                    <label for="type-in" class="type-box in"><span class="dot"></span> Pemasukan (+)</label>

                    <input type="radio" name="tipe" value="pengeluaran" id="type-out">
                    <label for="type-out" class="type-box out"><span class="dot"></span> Pengeluaran (-)</label>
                </div>
            </div>

            <div class="form-group">
                <label>Kategori</label>
                <div class="custom-dropdown" id="dropdownKategori" onclick="this.classList.toggle('active'); event.stopPropagation();">
                    <div class="dropdown-selected" id="selectedKategori">
                        Pilih Kategori...
                        <img src="../icon/chevron.png" class="chevron-icon">
                    </div>
                    <div class="dropdown-options" id="optionsKategori"></div>
                </div>
                <input type="hidden" name="kategori" id="inputKategori" required>
            </div>

            <div class="form-group">
                <label>Nominal (Rp)</label>
                <input type="number" name="nominal" id="editNominal" step="1" placeholder="Contoh: 50000" class="form-input" required>
            </div>

            <div class="form-group">
                <label>Keterangan</label>
                <textarea name="keterangan" id="editKeterangan" class="form-input" rows="3" placeholder="Tambahkan catatan..."></textarea>
            </div>

            <div class="modal-footer">
                <button type="button" onclick="closeModal()" class="btn-cancel">Batal</button>
                <button type="submit" class="btn-save" id="btnSubmit">Simpan Transaksi</button>
            </div>
        </form>
    </div>
</div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="money.js"></script>
</body>
</html>