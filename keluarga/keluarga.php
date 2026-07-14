<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "db_gpib");

if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: ../login/login.php");
    exit;
}

// Ambil input pencarian jika ada
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

// Query untuk mengambil data keluarga (Grouping No KK)
$query = "SELECT no_kk, 
          GROUP_CONCAT(nama_lengkap SEPARATOR ', ') as daftar_anggota, 
          COUNT(*) as total_orang 
          FROM jemaat ";

if (!empty($search)) {
    $query .= "WHERE no_kk LIKE '%$search%' OR nama_lengkap LIKE '%$search%' ";
}

$query .= "GROUP BY no_kk";
$res_keluarga = mysqli_query($conn, $query);

// KUNCI PERBAIKAN: Buat variabel ini agar tidak Error "Undefined"
$jumlah_data = mysqli_num_rows($res_keluarga); 

// Hitung total jemaat untuk header
$total_keluarga = mysqli_num_rows(mysqli_query($conn, "SELECT DISTINCT no_kk FROM jemaat"));

$nama_user = $_SESSION['username'] ?? 'User';
$waktu_masuk_sistem = $_SESSION['waktu_login'] ?? date('d/m/Y H:i');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Keluarga</title>
    <link rel="stylesheet" href="keluarga.css"> <link rel="icon" type="image/png" href="../icon/GPIB-NoCapt.png">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div id="loading-screen">
        <div class="loader-content">
            <img src="../icon/GPIB-NoCapt.png" alt="Logo" class="loader-logo">
            <div class="progress-container">
                <div class="progress-bar"></div>
            </div>
            <p>Memuat Data Keluarga...</p>
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
            <a href="keluarga.php" class="nav-link active"><img src="../icon/keluarga.png" class="menu-icon"> Data Keluarga</a>
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
        <header class="welcome-header">
            <div class="header-content">
                <div class="header-left" style="flex: 1;">
                    <h2>Data Keluarga</h2>
                    <p>Total <span class="highlight"><?= $total_keluarga; ?></span> Keluarga Terdaftar</p>
                </div>
                <form action="" method="GET" class="header-right">
                    <div class="search-wrapper">
                        <img src="../icon/search-people.png" class="search-icon">
                        <input type="text" name="search" id="searchInput" 
                            placeholder="Cari No KK / Nama.." 
                            value="<?= isset($_GET['search']) ? $_GET['search'] : ''; ?>">
                    </div>
                    <button type="button" class="btn-refresh" id="btn-refresh-data">
                        <span class="material-symbols-outlined">refresh</span>
                        Refresh Data
                    </button>
                </form>
            </div>
            <hr class="header-line">
        </header>

        <div class="content-body">
            <?php if ($jumlah_data > 0): ?>
                <div class="family-grid">
                    <?php while($row = mysqli_fetch_assoc($res_keluarga)): ?>
                    <div class="family-card" onclick="showFamilyDetail('<?= $row['no_kk']; ?>')">
                        <div class="card-main">
                            <div class="icon-circle">
                                <img src="../icon/keluarga.png" alt="Family">
                            </div>
                            <div class="info-text">
                                <h3>No KK: <?= $row['no_kk']; ?></h3>
                                <p><span class="badge"><?= $row['total_orang']; ?> Anggota</span></p>
                            </div>
                        </div>
                        <div class="card-footer">
                            <strong>Daftar Anggota:</strong>
                            <p><?= $row['daftar_anggota']; ?></p>
                        </div>
                    </div>
            <?php endwhile; ?>
                    </div>
                    <div id="noDataMessage" class="empty-state" style="display: none;">
                        <div class="empty-icon-wrapper">
                            <img src="../icon/Not-Found.png" alt="Not Found" class="empty-img">
                        </div>
                        <p>Belum ada data keluarga yang sesuai.</p>
                    </div>
            <?php endif; ?>
        </div>
    </div>
        <footer class="main-footer">
            <p>&copy; <?= date('Y'); ?> Church Management System - Versi 3.0</p>
        </footer>
</div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="keluarga.js"></script>

<div id="detailModal" class="modal-overlay" onclick="closeModal()">
    <div class="modal-content" onclick="event.stopPropagation()">
        <span class="close-btn" onclick="closeModal()">&times;</span>
        <div id="modalBody"></div>
    </div>
</div>
</body>
</html>