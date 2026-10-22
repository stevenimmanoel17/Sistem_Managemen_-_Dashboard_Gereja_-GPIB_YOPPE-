<?php
// ==========================================
// MODUL SESI & OTENTIKASI PENGGUNA
// ==========================================
session_start();
$conn = mysqli_connect("localhost", "root", "", "db_gpib");

if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: login/Login.php");
    exit;
}

$nama_user = $_SESSION['username'] ?? 'User';
$waktu_masuk_sistem = $_SESSION['waktu_login'] ?? date('d/m/Y H:i');

// ==========================================
// MODUL DATA PROFIL & STRUKTUR ORGANISASI
// ==========================================
$query_profil = mysqli_query($conn, "SELECT * FROM profil_gereja LIMIT 1");
$profil = mysqli_fetch_assoc($query_profil);
$nama_gereja   = $profil['nama_gereja'] ?? "GPIB 'Yoppe' Belawan";
$alamat_gereja = $profil['alamat'] ?? "Jl. Veteran No. 123, Belawan";
$logo_gereja   = $profil['logo'] ?? "icon/GPIB-NoCapt.png";

$query_struktur = mysqli_query($conn, "SELECT * FROM struktur_organisasi LIMIT 1");
$st = mysqli_fetch_assoc($query_struktur);

// ==========================================
// MODUL STATISTIK RINGKASAN & ULANG TAHUN
// ==========================================
$total_jemaat = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM jemaat"));
$total_kk = mysqli_num_rows(mysqli_query($conn, "SELECT DISTINCT no_kk FROM jemaat WHERE no_kk IS NOT NULL AND no_kk != ''"));

$query_ultah = "SELECT nama_lengkap FROM jemaat WHERE DAY(tgl_lahir) = DAY(CURRENT_DATE()) AND MONTH(tgl_lahir) = MONTH(CURRENT_DATE())";
$res_ultah = mysqli_query($conn, $query_ultah);
$jumlah_ultah = mysqli_num_rows($res_ultah);

$res_in_month = mysqli_query($conn, "SELECT SUM(nominal) as total FROM keuangan WHERE tipe = 'pemasukan' AND MONTH(tanggal) = MONTH(CURRENT_DATE()) AND YEAR(tanggal) = YEAR(CURRENT_DATE())");
$pemasukan_bulan_ini = mysqli_fetch_assoc($res_in_month)['total'] ?? 0;

$total_masuk = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(nominal) as total FROM keuangan WHERE tipe = 'pemasukan'"))['total'] ?? 0;
$total_keluar = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(nominal) as total FROM keuangan WHERE tipe = 'pengeluaran'"))['total'] ?? 0;
$saldo_akhir = $total_masuk - $total_keluar;

// ==========================================
// MODUL DATA ANALISIS KEUANGAN UNTUK GRAFIK
// ==========================================
$tahun_sekarang = date('Y');
$list_tahun_query = mysqli_query($conn, "SELECT DISTINCT YEAR(tanggal) as thn FROM keuangan ORDER BY thn DESC");
$available_years = [];
while ($t = mysqli_fetch_assoc($list_tahun_query)) {
    if (!empty($t['thn'])) $available_years[] = (int)$t['thn'];
}
if (empty($available_years)) {
    $available_years = [(int)$tahun_sekarang];
}

$keuangan_by_year = [];
foreach ($available_years as $thn) {
    $pemasukan_thn = array_fill(1, 12, 0);
    $pengeluaran_thn = array_fill(1, 12, 0);
    $q = mysqli_query($conn, "SELECT MONTH(tanggal) as bulan, tipe, SUM(nominal) as total 
                              FROM keuangan 
                              WHERE YEAR(tanggal) = '$thn' 
                              GROUP BY MONTH(tanggal), tipe");
    if ($q) {
        while ($r = mysqli_fetch_assoc($q)) {
            $m = (int)$r['bulan'];
            if ($m >= 1 && $m <= 12) {
                if ($r['tipe'] == 'pemasukan') $pemasukan_thn[$m] = (float)$r['total'];
                else $pengeluaran_thn[$m] = (float)$r['total'];
            }
        }
    }
    $keuangan_by_year[$thn] = [
        'in' => array_values($pemasukan_thn),
        'out' => array_values($pengeluaran_thn)
    ];
}

// ==========================================
// MODUL DATA DEMOGRAFI SEKTOR UNTUK GRAFIK
// ==========================================
$sektor_list = ['Galatia', 'Filipi', 'Yudea'];

$kategori_sektor = [];
$daftar_kategori = ['Semua', 'Aktif', 'PA', 'PT', 'GP', 'PKP', 'PKB', 'PKLU'];
foreach ($daftar_kategori as $kat) {
    $kategori_sektor[$kat] = [];
    foreach ($sektor_list as $sek) {
        if ($kat === 'Semua') {
            $count = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM jemaat WHERE sektor = '$sek'"));
        } elseif ($kat === 'Aktif') {
            $count = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM jemaat WHERE sektor = '$sek' AND status = 'Aktif'"));
        } else {
            $count = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM jemaat WHERE sektor = '$sek' AND pelkat = '$kat'"));
        }
        $kategori_sektor[$kat][$sek] = $count;
    }
}

$jiwa_per_sektor = [];
$kk_per_sektor = [];
foreach ($sektor_list as $sek) {
    $jiwa_per_sektor[] = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM jemaat WHERE sektor = '$sek'"));
    $kk_per_sektor[] = mysqli_num_rows(mysqli_query($conn, "SELECT DISTINCT no_kk FROM jemaat WHERE sektor = '$sek' AND no_kk IS NOT NULL AND no_kk != ''"));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <!-- ========================================== -->
    <!-- METADATA & ASSET RESOURCE HEADERS         -->
    <!-- ========================================== -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="index.css?v=<?= time(); ?>">
    <link rel="icon" type="image/png" href="icon/GPIB-NoCapt.png">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <!-- ========================================== -->
    <!-- KOMPONEN LOADER                           -->
    <!-- ========================================== -->
    <div id="loading-screen">
        <div class="loader-content">
            <img src="icon/GPIB-NoCapt.png" alt="Logo" class="loader-logo">
            <div class="progress-container">
                <div class="progress-bar"></div>
            </div>
            <p>Memuat Dashboard...</p>
        </div>
    </div> 

    <!-- ========================================== -->
    <!-- KOMPONEN SIDEBAR & NAVIGASI UTAMA         -->
    <!-- ========================================== -->
    <div class="sidebar">
        <div class="brand">
            <img src="icon/Chruch.png" alt="Logo" class="logo">
            <h1>GPIB <i>"Yoppe"</i><br>Belawan</h1>
        </div>
        <nav class="nav-menu">
            <a href="index.php" class="nav-link active"><img src="icon/dashboard.png" class="menu-icon"> Dashboard</a>
            <a href="money/money.php" class="nav-link"><img src="icon/money.png" class="menu-icon"> Keuangan</a>
            <a href="jemaat/jemaat.php" class="nav-link"><img src="icon/Jemaat.png" class="menu-icon"> Data Jemaat</a>
            <a href="keluarga/keluarga.php" class="nav-link"><img src="icon/keluarga.png" class="menu-icon"> Data Keluarga</a>
            <a href="inventaris/inventaris.php" class="nav-link"><img src="icon/inventory.png" class="menu-icon"> Inventaris</a>
            <a href="laporan/laporan.php" class="nav-link"><img src="icon/report.png" class="menu-icon"> Laporan</a>
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'Super Admin'): ?>
                <a href="pengaturan/pengaturan.php" class="nav-link"><img src="icon/settings.png" class="menu-icon"> <span>Pengaturan</span></a>
            <?php endif; ?>
        </nav>
        <div class="sidebar-footer">
            <hr class="divider">
            <a href="#" class="logout-link" id="logoutBtn"><img src="icon/logout1.png" class="menu-icon"> Log Out</a>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- AREA KONTEN UTAMA                         -->
    <!-- ========================================== -->
    <div class="main-content">
        <!-- ========================================== -->
        <!-- HEADER NAVIGASI ATAS & PROFIL USER        -->
        <!-- ========================================== -->
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
                        <small class="user-role"><?= isset($_SESSION['role']) && $_SESSION['role'] === 'Super Admin' ? 'Super Admin' : 'Admin'; ?></small>
                    </div>
                    <div class="user-avatar-wrapper">
                        <div class="avatar-circle"><?= strtoupper(substr($_SESSION['username'], 0, 1)); ?></div>
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
                                <span class="dropdown-value"><?= $waktu_masuk_sistem; ?></span>
                            </div>
                        </div>
                        <div class="profile-dropdown-footer">
                            <button type="button" class="btn-dropdown-ok" onclick="closeUserCard(event)">OK</button>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- ========================================== -->
        <!-- BADAN DASHBOARD                           -->
        <!-- ========================================== -->
        <div class="dashboard-body">    
            <!-- Banner Sambutan & Identitas Gereja -->
            <header class="welcome-header">
                <h2>Selamat Datang, <span><?= htmlspecialchars($nama_user); ?>!</span></h2>
                <p>Senang melihatmu kembali di Dashboard Gereja GPIB Yoppe.</p>
            </header>

            <div class="main-banner">
                <div class="banner-content">
                    <img src="<?= $logo_gereja; ?>" class="logo-bulat-dashboard" alt="Logo Gereja">
                    <div class="banner-text">
                        <h3><?= $nama_gereja; ?></h3>
                        <div class="location-wrapper">
                            <img src="icon/location.png"> <p><?= $alamat_gereja; ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kartu Metrik / Statistik Ringkas -->
            <div class="dashboard-stats">
                <div class="stat-card">
                    <div class="stat-info">
                        <h4>Total Jemaat</h4>
                        <span class="stat-number"><?= $total_jemaat; ?></span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-info">
                        <h4>Total KK</h4>
                        <span class="stat-number"><?= $total_kk; ?></span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-info">
                        <h4>Pemasukan (Bulan Ini)</h4>
                        <span class="stat-number">Rp <?= number_format($pemasukan_bulan_ini, 0, ',', '.'); ?></span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-info">
                        <h4>Saldo Akhir</h4>
                        <span class="stat-number">Rp <?= number_format($saldo_akhir, 0, ',', '.'); ?></span>
                    </div>
                </div>
            </div>

            <!-- Visualisasi Analisis & Tren Keuangan -->
            <div class="charts-row-title">
                <span class="material-symbols-outlined">payments</span>
                <h3>Analisis & Tren Keuangan</h3>
            </div>
            <div class="charts-grid-container">
                <div class="chart-card-wrapper">
                    <div class="chart-header-flex">
                        <div class="chart-title-box">
                            <span class="material-symbols-outlined icon-blue">bar_chart</span>
                            <h4>Tren Arus Kas Bulanan</h4>
                        </div>
                        <div class="chart-filter-box">
                            <label for="filterTahunKeuangan">Tahun:</label>
                            <select id="filterTahunKeuangan" onchange="gantiTahunKeuangan(this.value)">
                                <?php foreach ($available_years as $th): ?>
                                    <option value="<?= $th; ?>" <?= $th == $tahun_sekarang ? 'selected' : ''; ?>><?= $th; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="canvas-height-limiter">
                        <canvas id="arusKasChart"></canvas>
                    </div>
                </div>
                
                <div class="chart-card-wrapper">
                    <div class="chart-header-flex">
                        <div class="chart-title-box">
                            <span class="material-symbols-outlined icon-green">monitoring</span>
                            <h4>Surplus / Defisit Kumulatif</h4>
                        </div>
                        <span class="badge-info-year" id="labelTahunSurplus"><?= $tahun_sekarang; ?></span>
                    </div>
                    <div class="canvas-height-limiter">
                        <canvas id="surplusDefisitChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Visualisasi Distribusi & Demografi Jemaat -->
            <div class="charts-row-title" style="margin-top: 40px;">
                <span class="material-symbols-outlined">groups</span>
                <h3>Distribusi & Demografi Jemaat</h3>
            </div>
            <div class="charts-grid-container">
                <div class="chart-card-wrapper">
                    <div class="chart-header-flex">
                        <div class="chart-title-box">
                            <span class="material-symbols-outlined icon-purple">donut_small</span>
                            <h4>Distribusi Wilayah Sektor</h4>
                        </div>
                        <div class="chart-filter-box">
                            <label for="filterKategoriSektor">Kategori:</label>
                            <select id="filterKategoriSektor" onchange="gantiKategoriSektor(this.value)">
                                <option value="Semua">Semua Jemaat</option>
                                <option value="Aktif">Hanya Aktif</option>
                                <option value="PA">Pelkat PA</option>
                                <option value="PT">Pelkat PT</option>
                                <option value="GP">Pelkat GP</option>
                                <option value="PKP">Pelkat PKP</option>
                                <option value="PKB">Pelkat PKB</option>
                                <option value="PKLU">Pelkat PKLU</option>
                            </select>
                        </div>
                    </div>
                    <div class="canvas-height-limiter">
                        <canvas id="distribusiSektorChart"></canvas>
                    </div>
                </div>
                
                <div class="chart-card-wrapper">
                    <div class="chart-header-flex">
                        <div class="chart-title-box">
                            <span class="material-symbols-outlined icon-orange">stacked_bar_chart</span>
                            <h4>Total Jiwa vs Kepala Keluarga</h4>
                        </div>
                    </div>
                    <div class="canvas-height-limiter">
                        <canvas id="jiwaVsKkChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Bagian Kepengurusan & Ulang Tahun -->
            <div class="info-grid">
                <div class="org-section">
                    <div class="section-title">
                        <img src="icon/ph_tree-structure-bold.png" width="18"> 
                        <h4>Struktur Kepengurusan</h4>
                    </div>
                    <div class="org-cards">
                        <div class="stat-card mini-card">
                            <h5><?= htmlspecialchars($st['nama1'] ?? 'Paulus'); ?></h5>
                            <p><?= htmlspecialchars($st['jabatan1'] ?? 'Ketua'); ?></p>
                        </div>
                        <div class="stat-card mini-card">
                            <h5><?= htmlspecialchars($st['nama2'] ?? 'Maria'); ?></h5>
                            <p><?= htmlspecialchars($st['jabatan2'] ?? 'Sekretaris'); ?></p>
                        </div>
                        <div class="stat-card mini-card">
                            <h5><?= htmlspecialchars($st['nama3'] ?? 'Petrus'); ?></h5>
                            <p><?= htmlspecialchars($st['jabatan3'] ?? 'Bendahara'); ?></p>
                        </div>
                    </div>
                </div> 
                
                <div class="birthday-section">
                    <div class="section-title">
                        <img src="icon/Vector.png" width="18"> 
                        <h4>Ulang Tahun Hari Ini</h4>
                    </div>
                    <div class="stat-card birthday-card">
                        <?php if ($jumlah_ultah > 0): ?>
                            <div style="text-align: center;">
                            <?php while($ultah = mysqli_fetch_assoc($res_ultah)): ?> 
                                <p style="color: #2392ED; font-size: 18px; font-weight: 600; margin: 2px 0;">
                                <?= htmlspecialchars($ultah['nama_lengkap']); ?>
                                </p>
                            <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <p>Tidak ada yang ulang Tahun hari ini.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- ========================================== -->
        <!-- FOOTER HALAMAN                            -->
        <!-- ========================================== -->
        <footer class="main-footer">
            <p>&copy; 2026 Church Management System - Versi 3.0</p>
        </footer>
    </div>

    <!-- ========================================== -->
    <!-- SCRIPT EKSTERNAL & DATA BRIDGE             -->
    <!-- ========================================== -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="index.js"></script>

    <script>
        window.DASHBOARD_DATA = {
            tahunAktif: "<?= $tahun_sekarang; ?>",
            keuangan: <?= json_encode($keuangan_by_year); ?>,
            sektorList: <?= json_encode($sektor_list); ?>,
            kategoriSektor: <?= json_encode($kategori_sektor); ?>,
            jiwaPerSektor: <?= json_encode($jiwa_per_sektor); ?>,
            kkPerSektor: <?= json_encode($kk_per_sektor); ?>
        };
    </script>
</body>
</html>