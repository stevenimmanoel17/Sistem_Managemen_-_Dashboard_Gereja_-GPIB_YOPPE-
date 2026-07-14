<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "db_gpib");

if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: login/Login.php");
    exit;
}

// Ambil data dari tabel profil_gereja
$query_profil = mysqli_query($conn, "SELECT * FROM profil_gereja LIMIT 1");
$profil = mysqli_fetch_assoc($query_profil);

// Definisikan variabel agar HTML tidak error
$nama_gereja   = $profil['nama_gereja'] ?? "GPIB 'Yoppe' Belawan";
$alamat_gereja = $profil['alamat'] ?? "Jl. Veteran No. 123, Belawan";
$logo_gereja   = $profil['logo'] ?? "icon/GPIB-NoCapt.png";

// --- LOGIKA DATA ---
$total_jemaat = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM jemaat"));

// Hitung Total KK unik dari tabel jemaat
$total_kk = mysqli_num_rows(mysqli_query($conn, "SELECT DISTINCT no_kk FROM jemaat"));

// Ambil data jemaat yang ulang tahun hari ini
$query_ultah = "SELECT nama_lengkap FROM jemaat WHERE DAY(tgl_lahir) = DAY(CURRENT_DATE()) AND MONTH(tgl_lahir) = MONTH(CURRENT_DATE())";
$res_ultah = mysqli_query($conn, $query_ultah);
$jumlah_ultah = mysqli_num_rows($res_ultah);

// Hitung Pemasukan & Saldo
$res_in_month = mysqli_query($conn, "SELECT SUM(nominal) as total FROM keuangan WHERE tipe = 'pemasukan' AND MONTH(tanggal) = MONTH(CURRENT_DATE()) AND YEAR(tanggal) = YEAR(CURRENT_DATE())");
$pemasukan_bulan_ini = mysqli_fetch_assoc($res_in_month)['total'] ?? 0;

$total_masuk = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(nominal) as total FROM keuangan WHERE tipe = 'pemasukan'"))['total'] ?? 0;
$total_keluar = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(nominal) as total FROM keuangan WHERE tipe = 'pengeluaran'"))['total'] ?? 0;
$saldo_akhir = $total_masuk - $total_keluar;

$query_struktur = mysqli_query($conn, "SELECT * FROM struktur_organisasi LIMIT 1");
$st = mysqli_fetch_assoc($query_struktur);

$nama_user = $_SESSION['username'] ?? 'User';
$waktu_masuk_sistem = $_SESSION['waktu_login'] ?? date('d/m/Y H:i');

// --- LOGIKA DATA GRAFIK CHART.JS (PERBAIKAN TOTAL) ---
$tahun_ini = date('Y');

// Inisialisasi data keuangan 12 bulan dengan nilai 0
$pemasukan_bulanan = array_fill(1, 12, 0);
$pengeluaran_bulanan = array_fill(1, 12, 0);

// Gunakan COALESCE dan pengecekan error kueri agar tidak memicu error HTML jika tabel bermasalah
$query_keuangan = "SELECT MONTH(tanggal) as bulan, tipe, SUM(nominal) as total 
                   FROM keuangan 
                   WHERE YEAR(tanggal) = '$tahun_ini' 
                   GROUP BY MONTH(tanggal), tipe";
$res_keuangan = mysqli_query($conn, $query_keuangan);

if ($res_keuangan) {
    while ($row = mysqli_fetch_assoc($res_keuangan)) {
        $bln = (int)$row['bulan'];
        if ($bln >= 1 && $bln <= 12) {
            if ($row['tipe'] == 'pemasukan') {
                $pemasukan_bulanan[$bln] = (int)$row['total'];
            } else {
                $pengeluaran_bulanan[$bln] = (int)$row['total'];
            }
        }
    }
}

// QUERY JUMLAH JEMAAT PER SEKTOR (DENGAN PENGAMAN ERROR)
$nama_sektor = [];
$jumlah_per_sektor = [];

$query_sektor = "SELECT sektor, COUNT(id) as total 
                 FROM jemaat 
                 WHERE status = 'Aktif' AND sektor IS NOT NULL AND sektor != ''
                 GROUP BY sektor 
                 ORDER BY sektor ASC";
$res_sektor = mysqli_query($conn, $query_sektor);

if ($res_sektor) {
    while ($row = mysqli_fetch_assoc($res_sektor)) {
        $nama_sektor[] = $row['sektor'];
        $jumlah_per_sektor[] = (int)$row['total'];
    }
}

// Pastikan hasil konversi ke JSON selalu valid dalam bentuk string array kosong [] jika database kosong
$data_pemasukan_json   = !empty($pemasukan_bulanan) ? json_encode(array_values($pemasukan_bulanan)) : json_encode(array_fill(0, 12, 0));
$data_pengeluaran_json = !empty($pengeluaran_bulanan) ? json_encode(array_values($pengeluaran_bulanan)) : json_encode(array_fill(0, 12, 0));
$labels_sektor_json    = !empty($nama_sektor) ? json_encode($nama_sektor) : json_encode([]);
$data_sektor_json      = !empty($jumlah_per_sektor) ? json_encode($jumlah_per_sektor) : json_encode([]);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="index.css">
    <link rel="icon" type="image/png" href="icon/GPIB-NoCapt.png">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <?php if (isset($_GET['pesan']) && $_GET['pesan'] == 'akses_ditolak'): ?>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Akses Terbatas',
                text: 'Maaf, halaman ini hanya dapat diakses oleh Super Admin.',
                confirmButtonColor: '#2392ED'
            });
            window.history.replaceState({}, document.title, "index.php");
        </script>
    <?php endif; ?>

    <div id="loading-screen">
        <div class="loader-content">
            <img src="icon/GPIB-NoCapt.png" alt="Logo" class="loader-logo">
            <div class="progress-container">
                <div class="progress-bar"></div>
            </div>
            <p>Memuat Dashboard...</p>
        </div>
    </div> 

    <div class="sidebar">
        <div class="brand">
            <img src="icon/Chruch.png" alt="Logo" class="logo">
            <h1>GPIB <i>"Yoppe"</i><br>Belawan</h1>
        </div>
        <nav class="nav-menu">
            <a href="index.php" class="nav-link active">
                <img src="icon/dashboard.png" class="menu-icon"> Dashboard
            </a>
            <a href="money/money.php" class="nav-link">
                <img src="icon/money.png" class="menu-icon"> Keuangan
            </a>
            <a href="jemaat/jemaat.php" class="nav-link">
                <img src="icon/Jemaat.png" class="menu-icon"> Data Jemaat
            </a>
            <a href="keluarga/keluarga.php" class="nav-link">
                <img src="icon/keluarga.png" class="menu-icon"> Data Keluarga
            </a>
            <a href="inventaris/inventaris.php" class="nav-link">
                <img src="icon/inventory.png" class="menu-icon"> Inventaris
            </a>
            <a href="laporan/laporan.php" class="nav-link">
                <img src="icon/report.png" class="menu-icon"> Laporan
            </a>
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'Super Admin'): ?>
                <a href="pengaturan/pengaturan.php" class="nav-link">
                    <img src="icon/settings.png" class="menu-icon"> <span>Pengaturan</span>
                </a>
            <?php endif; ?>
        </nav>
        <div class="sidebar-footer">
            <hr class="divider">
            <a href="#" class="logout-link" id="logoutBtn"><img src="icon/logout1.png" class="menu-icon"> Log Out</a>
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
            <header class="welcome-header">
                <h2>Selamat Datang, <span><?= $nama_user; ?>!</span></h2>
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

            <div class="charts-grid-container">
                <div class="chart-card-wrapper">
                    <h3><span class="material-symbols-outlined" style="color: #2392ED; vertical-align: middle; margin-right: 5px;">bar_chart</span> Tren Keuangan Tahun <?= $tahun_ini; ?></h3>
                    <div class="canvas-height-limiter">
                        <canvas id="keuanganChart"></canvas>
                    </div>
                </div>
                
                <div class="chart-card-wrapper">
                    <h3><span class="material-symbols-outlined" style="color: #2ecc71; vertical-align: middle; margin-right: 5px;">pie_chart</span> Jumlah Jemaat per Sektor</h3>
                    <div class="canvas-height-limiter">
                        <canvas id="jemaatChart"></canvas>
                    </div>
                </div>
            </div>

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
                                <p style="color: #2392ED; font-size : 18px; font-weight: 600; margin: 2px 0;">
                                <?= $ultah['nama_lengkap']; ?>
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
        
        <footer class="main-footer">
            <p>&copy; 2026 Church Management System - Versi 3.0</p>
        </footer>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.js"></script>

    <div id="php-data-bridge" 
         style="display: none;"
         data-pemasukan='<?= $data_pemasukan_json ?: "[]"; ?>'
         data-pengeluaran='<?= $data_pengeluaran_json ?: "[]"; ?>'
         data-labels-sektor='<?= $labels_sektor_json ?: "[]"; ?>'
         data-data-sektor='<?= $data_sektor_json ?: "[]"; ?>'>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const labelBulan = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
            
            // Inisialisasi nilai dasar (default)
            let dataPemasukan = Array(12).fill(0);
            let dataPengeluaran = Array(12).fill(0);
            let labelSektor = ['Belum Ada Data Sektor'];
            let dataSektor = [1];
            let warnaSektor = ['#e0e0e0'];

            // Ambil data mentah langsung dari DOM HTML (Kebal terhadap galat sintaksis teks)
            const bridge = document.getElementById('php-data-bridge');
            if (bridge) {
                try {
                    const rawPemasukan = JSON.parse(bridge.getAttribute('data-pemasukan'));
                    const rawPengeluaran = JSON.parse(bridge.getAttribute('data-pengeluaran'));
                    const rawLabelsSektor = JSON.parse(bridge.getAttribute('data-labels-sektor'));
                    const rawDataSektor = JSON.parse(bridge.getAttribute('data-data-sektor'));

                    if (Array.isArray(rawPemasukan) && rawPemasukan.length === 12) dataPemasukan = rawPemasukan;
                    if (Array.isArray(rawPengeluaran) && rawPengeluaran.length === 12) dataPengeluaran = rawPengeluaran;
                    
                    if (Array.isArray(rawLabelsSektor) && rawLabelsSektor.length > 0) {
                        labelSektor = rawLabelsSektor;
                        dataSektor = rawDataSektor.map(Number);
                        warnaSektor = ['#2ecc71', '#2392ED', '#9b59b6', '#f1c40f', '#e67e22', '#1abc9c'];
                    }
                } catch (e) {
                    console.error("Gagal membaca payload data dari database, menggunakan data default.", e);
                }
            }

            const apakahKeuanganKosong = dataPemasukan.every(v => v === 0) && dataPengeluaran.every(v => v === 0);

            // RENDER GRAFIK TREN KEUANGAN (BAR CHART)
            const canvasKeuangan = document.getElementById('keuanganChart');
            if (canvasKeuangan) {
                new Chart(canvasKeuangan.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: labelBulan,
                        datasets: [
                            { label: 'Pemasukan', data: dataPemasukan, backgroundColor: '#2392ED', borderRadius: 5 },
                            { label: 'Pengeluaran', data: dataPengeluaran, backgroundColor: '#ff4d4d', borderRadius: 5 }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { labels: { font: { family: 'Poppins', size: 12 } } }
                        },
                        scales: {
                            x: { grid: { display: false } },
                            y: { 
                                min: 0, 
                                max: apakahKeuanganKosong ? 100000 : undefined, 
                                ticks: { callback: function(value) { return 'Rp ' + Number(value).toLocaleString('id-ID'); } } 
                            }
                        }
                    }
                });
            }

            // RENDER GRAFIK SEBARAN JEMAAT (DOUGHNUT CHART)
            const canvasJemaat = document.getElementById('jemaatChart');
            if (canvasJemaat) {
                new Chart(canvasJemaat.getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels: labelSektor,
                        datasets: [{ label: 'Total Jemaat', data: dataSektor, backgroundColor: warnaSektor, borderWidth: 2, borderColor: '#ffffff' }]
                    },
                    options: { 
                        responsive: true, 
                        maintainAspectRatio: false, 
                        plugins: { 
                            legend: { position: 'right', labels: { font: { family: 'Poppins', size: 12 } } } 
                        } 
                    }
                });
            }
        });
    </script>

    <script src="index.js"></script>
</body>
</html>