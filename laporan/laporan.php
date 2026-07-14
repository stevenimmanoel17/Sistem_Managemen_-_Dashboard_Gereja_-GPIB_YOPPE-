<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "db_gpib");

if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: ../login/Login.php");
    exit;
}

// LOGIKA DATA JEMAAT
$query_jemaat = "SELECT * FROM jemaat ORDER BY nama_lengkap ASC";
$tampil_jemaat = mysqli_query($conn, $query_jemaat);

// LOGIKA DATA KEUANGAN
$periode = $_GET['periode'] ?? 'Semua Waktu';
if ($periode == 'Hari Ini') { $filter_sql = "WHERE DATE(tanggal) = CURRENT_DATE()"; } 
elseif ($periode == 'Bulan Ini') { $filter_sql = "WHERE MONTH(tanggal) = MONTH(CURRENT_DATE()) AND YEAR(tanggal) = YEAR(CURRENT_DATE())"; } 
elseif ($periode == 'Tahun Ini') { $filter_sql = "WHERE YEAR(tanggal) = YEAR(CURRENT_DATE())"; } 
else { $filter_sql = ""; }

$query_transaksi = "SELECT * FROM keuangan $filter_sql ORDER BY tanggal DESC";
$tampil_transaksi = mysqli_query($conn, $query_transaksi);

$res_in = mysqli_query($conn, "SELECT SUM(nominal) as total FROM keuangan $filter_sql " . ($filter_sql ? "AND" : "WHERE") . " tipe = 'pemasukan'");
$pemasukan = mysqli_fetch_assoc($res_in)['total'] ?? 0;
$res_out = mysqli_query($conn, "SELECT SUM(nominal) as total FROM keuangan $filter_sql " . ($filter_sql ? "AND" : "WHERE") . " tipe = 'pengeluaran'");
$pengeluaran = mysqli_fetch_assoc($res_out)['total'] ?? 0;

// LOGIKA DATA INVENTARIS
$query_inventaris = "SELECT * FROM inventaris ORDER BY nama_barang ASC";
$tampil_inventaris = mysqli_query($conn, $query_inventaris);

$nama_user = $_SESSION['username'] ?? 'User';
$waktu_masuk_sistem = $_SESSION['waktu_login'] ?? date('d/m/Y H:i');
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <link rel="stylesheet" href="laporan.css">
    <link rel="icon" type="image/png" href="../icon/GPIB-NoCapt.png">
</head>
<body>
    <div id="loading-screen">
        <div class="loader-content">
            <img src="../icon/GPIB-NoCapt.png" alt="Logo" class="loader-logo">
            <div class="progress-container">
                <div class="progress-bar"></div>
            </div>
            <p>Memuat Laporan...</p>
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
            <a href="../jemaat/jemaat.php" class="nav-link"><img src="../icon/Jemaat.png" class="menu-icon"> Data Jemaat</a>
            <a href="../keluarga/keluarga.php" class="nav-link"><img src="../icon/keluarga.png" class="menu-icon"> Data Keluarga</a>
            <a href="../inventaris/inventaris.php" class="nav-link"><img src="../icon/inventory.png" class="menu-icon"> Inventaris</a>
            <a href="laporan.php" class="nav-link active"><img src="../icon/report.png" class="menu-icon"> Laporan</a>
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
                                    <span class="dropdown-value" id="waktuLoginDropdown">
                                        <?= $waktu_masuk_sistem; ?>
                                    </span>
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
            <h2>Pusat Laporan</h2>
            <p>Cetak dan ekspor data gereja ke berbagai format dokumen.</p>
        </header>
        <div class="tab-navigation">
            <button class="tab-btn active" onclick="openReportTab(event, 'jemaatTab')">
                <i class="fa-solid fa-user-group"></i> Data Jemaat
            </button>
            <button class="tab-btn" onclick="openReportTab(event, 'keuanganTab')">
                <i class="fa-solid fa-hand-holding-dollar"></i> Laporan Keuangan
            </button>
            <button class="tab-btn" onclick="openReportTab(event, 'inventarisTab')">
                <i class="fa-solid fa-boxes-stacked"></i> Data Inventaris
            </button>
        </div>

        <div class="report-toolbar">
            <div class="toolbar-right">
                <div class="export-dropdown">
                    <button class="btn-export" onclick="toggleExportMenu()">
                        <i class="fa-solid fa-file-export"></i> Export Data <span class="arrow-down-custom"></span>
                    </button>
                    <div id="exportMenu" class="dropdown-content">
                        <a href="javascript:void(0)" onclick="eksporData('excel')" class="hover-excel"><i class="fa-solid fa-file-excel"></i> Excel (.xls)</a>
                        <a href="javascript:void(0)" onclick="eksporData('csv')" class="hover-csv"><i class="fa-solid fa-file-csv"></i> CSV</a>
                        <a href="javascript:void(0)" onclick="eksporData('pdf')" class="hover-pdf"><i class="fa-solid fa-file-pdf"></i> Simpan PDF</a>
                        <a href="javascript:void(0)" onclick="eksporData('print')" class="hover-print"><i class="fa-solid fa-print"></i> Print Langsung</a>
                    </div>
                </div>
                <div class="refresh-group">
                    <button type="button" class="btn-refresh-data" id="btn-refresh-laporan">
                        <span class="material-symbols-outlined">refresh</span>
                        Refresh
                    </button>
                </div>
            </div>
        </div>

        <div id="jemaatTab" class="tab-content active">
            <div class="table-container">
                <div class="table-responsive">
                <table class="report-table">
                    <thead>
                        <tr>
                            <th>NIK</th>
                            <th>Nama Lengkap</th>
                            <th>Jenis Kelamin</th>
                            <th>Tgl Lahir</th>
                            <th>Pelkat</th>
                            <th>Sektor</th>
                            <th>Tempat Baptis</th>
                            <th>Tanggal Baptis</th>
                            <th>Tempat Sidi</th>
                            <th>Tanggal Sidi</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="bodyJemaat">
                        <?php if (mysqli_num_rows($tampil_jemaat) > 0): ?>
                        <?php while($row = mysqli_fetch_assoc($tampil_jemaat)): ?>
                        <tr>
                            <td><?= $row['nik'] ?: '-'; ?></td>
                            <td><?= $row['nama_lengkap']; ?></td>
                            <td><?= $row['gender']; ?></td>
                            <td><?= ($row['tgl_lahir'] && $row['tgl_lahir'] != '0000-00-00') ? date('d-m-Y', strtotime($row['tgl_lahir'])) : '-'; ?></td>
                            <td><?= $row['pelkat'] ?: '-'; ?></td>
                            <td><?= $row['sektor'] ?: '-'; ?></td>
                            <td><?= $row['tempat_baptis'] ?: '-'; ?></td>
                            <td><?= ($row['tgl_baptis'] && $row['tgl_baptis'] != '0000-00-00') ? date('d-m-Y', strtotime($row['tgl_baptis'])) : '-'; ?></td>
                            <td><?= $row['tempat_sidi'] ?: '-'; ?></td>
                            <td><?= ($row['tgl_sidi'] && $row['tgl_sidi'] != '0000-00-00') ? date('d-m-Y', strtotime($row['tgl_sidi'])) : '-'; ?></td>
                            <td>
                                <span class="status-badge <?= ($row['status'] == 'Aktif') ? 'status-laporan-aktif' : 'status-laporan-non'; ?>">
                                        <?= $row['status']; ?>
                                </span>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="11" style="text-align: center; padding: 40px; color: #aaa; font-style: italic;">
                                    Data Jemaat tidak ditemukan untuk periode ini.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                </div>
            </div>
        </div>

        <div id="inventarisTab" class="tab-content">
            <div class="table-container">
                <div class="table-responsive">
                    <table class="report-table">
                        <thead>
                            <tr>
                                <th>Kode</th>
                                <th>Nama Barang</th>
                                <th>Kategori</th>
                                <th>Lokasi</th>
                                <th>Kondisi</th>
                                <th>Penanggung Jawab</th>
                            </tr>
                        </thead>
                        <tbody id="bodyInventaris">
                            <?php if(mysqli_num_rows($tampil_inventaris) > 0): ?>
                                <?php while($row = mysqli_fetch_assoc($tampil_inventaris)): ?>
                                <tr>
                                    <td><strong><?= $row['kode_barang']; ?></strong></td>
                                    <td><?= $row['nama_barang']; ?></td>
                                    <td><?= $row['kategori']; ?></td>
                                    <td><?= $row['lokasi']; ?></td>
                                    <td><?= $row['kondisi']; ?></td>
                                    <td><?= $row['penanggung_jawab'] ?: '-'; ?></td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" style="text-align:center; padding: 30px; color: #aaa;">Data inventaris tidak ditemukan.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div id="keuanganTab" class="tab-content">
            
            <div class="filter-item" style="margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                <label class="label-grey"><i class="fa-solid fa-filter icon-grey"></i> Periode:</label>
                
                <div class="custom-dropdown-modern" id="dropdownPeriode">
                    <div class="dropdown-selected-modern" onclick="togglePeriodeDropdown()">
                        <span id="periodeText"><?= $periode; ?></span>
                        <i class="fa-solid fa-chevron-down arrow-icon"></i>
                    </div>
                    
                    <div class="dropdown-options-modern" id="periodeOptions">
                        <ul>
                            <li onclick="updateLaporan('Hari Ini')">Hari Ini</li>
                            <li onclick="updateLaporan('Bulan Ini')">Bulan Ini</li>
                            <li onclick="updateLaporan('Tahun Ini')">Tahun Ini</li>
                            <li onclick="updateLaporan('Semua Waktu')">Semua Waktu</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <div class="info-banner-spaced">
                <i class="fa-solid fa-circle-info"></i> Menampilkan Laporan Periode: <?= $periode ?>
            </div>

            <div class="table-container">
                <table class="report-table">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Tipe</th>
                            <th>Kategori</th>
                            <th>Uraian</th>
                            <th>Nominal</th>
                        </tr>
                    </thead>
                    <tbody id="bodyKeuangan">
                        <?php if(mysqli_num_rows($tampil_transaksi) > 0): ?>
                            <?php while($row = mysqli_fetch_assoc($tampil_transaksi)): ?>
                            <tr>
                                <td><?= date('d/m/Y', strtotime($row['tanggal'])); ?></td>
                                <td><?= ucfirst($row['tipe']) ?></td>
                                <td><?= $row['kategori']; ?></td>
                                <td><?= $row['keterangan']; ?></td>
                                <td class="<?= $row['tipe'] == 'pemasukan' ? 'text-green' : 'text-red' ?>">
                                    Rp <?= number_format($row['nominal'], 0, ',', '.') ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" style="text-align:center; padding: 30px; color: #aaa;">Data Keuangan tidak ditemukan untuk periode ini.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                    <tfoot>
                        <tr class="row-total">
                            <td colspan="4" class="text-right">Total Pemasukan</td>
                            <td class="text-green">Rp <?= number_format($pemasukan, 0, ',', '.') ?></td>
                        </tr>
                        <tr class="row-total">
                            <td colspan="4" class="text-right">Total Pengeluaran</td>
                            <td class="text-red">Rp <?= number_format($pengeluaran, 0, ',', '.') ?></td>
                        </tr>
                        <tr class="row-total bold-border">
                            <td colspan="4" class="text-right">Surplus/Defisit</td>
                            <td style="font-weight: 700; color: #2392ED; text-align: right;">Rp <?= number_format($pemasukan - $pengeluaran, 0, ',', '.') ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
        <footer class="main-footer">
            <p>&copy; 2026 Church Management System - Versi 3.0</p>
        </footer>
</div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="laporan.js"></script>
</body>
</html>