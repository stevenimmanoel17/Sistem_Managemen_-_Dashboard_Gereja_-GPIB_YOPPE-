<?php
session_start();
include '../koneksi.php';

// Proteksi Akses
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'Super Admin') {
    header("Location: ../index.php?pesan=akses_ditolak");
    exit;
}

// Hitung Total User terdaftar
$total_user = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM users"));

// Ambil Data
$p = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM profil_gereja LIMIT 1"));
$st = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM struktur_organisasi LIMIT 1"));

$nama_user = $_SESSION['username'] ?? 'User';
$waktu_masuk_sistem = $_SESSION['waktu_login'] ?? date('d/m/Y H:i');

if (isset($_GET['action']) && $_GET['action'] == 'refresh_table') {
    $res_u = mysqli_query($conn, "SELECT * FROM users ORDER BY id DESC");
    while($u = mysqli_fetch_assoc($res_u)): ?>
        <tr style="font-size: 13px;">
            <td><strong><?= htmlspecialchars($u['username']); ?></strong></td>
            <td>
                <span class="badge <?= ($u['role'] === 'Super Admin') ? 'badge-super' : 'badge-admin'; ?>">
                    <?= $u['role']; ?>
                </span>
            </td>
            <td><?= htmlspecialchars($u['nama_lengkap']); ?></td>
            <td><?= htmlspecialchars($u['email']); ?></td>
            <td><small style="font-weight: bold; color: <?= ($u['status'] === 'Nonaktif') ? '#ff4d4d' : '#2392ED'; ?>;"><?= $u['status']; ?></small></td>
            <td style="text-align: center;">
                <?php if ($u['username'] !== $_SESSION['username']): ?>
                    <a href="javascript:void(0)" onclick="openEditModal('<?= $u['id']; ?>', '<?= addslashes($u['nama_lengkap']); ?>', '<?= addslashes($u['username']); ?>', '<?= $u['email']; ?>', '<?= $u['role']; ?>', '<?= $u['status']; ?>')" style="color: #2392ED; font-weight: 600; text-decoration: none; margin-right: 10px;">Edit</a>
                    <a href="javascript:void(0)" onclick="confirmDelete(<?= $u['id']; ?>)" style="color: #ff4d4d; font-weight: 600; text-decoration: none;">Hapus</a>
                <?php else: ?>
                    <small style="color: #888;">(Anda)</small>
                <?php endif; ?>
            </td>
        </tr>
    <?php endwhile;
    exit; // PENTING: Menghentikan PHP agar tidak mengirim sisa HTML ke bawah
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan</title>
    <link rel="icon" type="image/png" href="../icon/GPIB-NoCapt.png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="pengaturan.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div id="loading-screen">
        <div class="loader-content">
            <img src="../icon/GPIB-NoCapt.png" alt="Logo" class="loader-logo">
            <div class="progress-container">
                <div class="progress-bar"></div>
            </div>
            <p>Memuat Pengaturan...</p>
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
            <a href="../laporan/laporan.php" class="nav-link"><img src="../icon/report.png" class="menu-icon"> Laporan</a>
            <a href="../inventaris/inventaris.php" class="nav-link"><img src="../icon/inventory.png" class="menu-icon"> Inventaris</a>
            <a href="pengaturan.php" class="nav-link active"><img src="../icon/settings.png" class="menu-icon"> Pengaturan</a>
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
        <div class="tab-navigation">
            <button class="tab-btn active" onclick="openTab(event, 'profil')">Profil</button>
            <button class="tab-btn" onclick="openTab(event, 'struktur')">Struktur</button>
            <button class="tab-btn" onclick="openTab(event, 'users')">Users</button>
        </div>

        <div id="profil" class="tab-content active">
            <div class="setting-card">
                <h3>Identitas Gereja</h3>
                <form action="update_profil.php" method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>Nama Gereja</label>
                        <input type="text" name="nama_gereja" class="form-input" value="<?= htmlspecialchars($p['nama_gereja'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Alamat Lengkap</label>
                        <textarea name="alamat" class="form-input" rows="3"><?= htmlspecialchars($p['alamat'] ?? ''); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>Ganti Logo</label>
                        <div class="custom-file-upload">
                            <label for="logo_baru" class="btn-upload">
                                <img src="../icon/upload-white.png" style="width: 18px; height: 18px; margin-right: 10px; vertical-align: middle;"> 
                                <span style="vertical-align: middle;">Pilih File</span>
                            </label>
                            <input type="file" id="logo_baru" name="logo_baru" class="form-input-file" accept="image/*" onchange="previewImage(this)">
                            <span id="file-name" class="file-name-text">Tidak ada file dipilih</span>
                        </div>
                        <div class="preview-container">
                            <img id="logo-preview" src="../<?= $p['logo'] ?? 'icon/GPIB-NoCapt.png'; ?>" class="logo-circle-preview">
                            <div class="preview-info">
                                <p class="label-info">Logo saat ini:</p>
                                <p id="current-file-name" class="filename-info"><?= basename($p['logo'] ?? 'logo_gpib.png'); ?></p>
                                <p id="status-text" class="status-info">Terakhir dipilih</p>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn-save-main">Simpan</button>
                </form>
            </div>
        </div>

        <div id="struktur" class="tab-content">
            <div class="setting-card">
                <h3>Struktur Kepengurusan</h3>
                <form action="update_struktur.php" method="POST">
                    <table class="table-setting">
                        <thead>
                            <tr style="text-align: left; font-weight: 600; color: #555;">
                                <th style="padding: 10px;">Jabatan</th>
                                <th style="padding: 10px;">Nama</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><input type="text" name="jabatan1" class="form-input" placeholder="Ketua" value="<?= htmlspecialchars($st['jabatan1'] ?? 'Majelis'); ?>"></td>
                                <td><input type="text" name="nama1" class="form-input" value="<?= htmlspecialchars($st['nama1'] ?? 'Paulus'); ?>"></td>
                            </tr>
                            <tr>
                                <td><input type="text" name="jabatan2" class="form-input" placeholder="Sekretaris" value="<?= htmlspecialchars($st['jabatan2'] ?? 'Sekretaris'); ?>"></td>
                                <td><input type="text" name="nama2" class="form-input" value="<?= htmlspecialchars($st['nama2'] ?? 'Maria Magdalena'); ?>"></td>
                            </tr>
                            <tr>
                                <td><input type="text" name="jabatan3" class="form-input" placeholder="Bendahara" value="<?= htmlspecialchars($st['jabatan3'] ?? 'Bendahara'); ?>"></td>
                                <td><input type="text" name="nama3" class="form-input" value="<?= htmlspecialchars($st['nama3'] ?? 'Petrus'); ?>"></td>
                            </tr>
                        </tbody>
                    </table>
                    <button type="submit" class="btn-save-main" style="margin-top: 25px;">Simpan</button>
                </form>
            </div>
        </div>

        <div id="users" class="tab-content">
            <div class="user-layout">
                <div class="setting-card">
                    <h3>Tambah User Baru</h3>
                    <form action="add_user.php" method="POST">
                        <div class="form-group"><input type="text" name="nama_lengkap" class="form-input" placeholder="Nama Lengkap" required></div>
                        <div class="form-group"><input type="text" name="username" class="form-input" placeholder="Username" required></div>
                        <div class="form-group"><input type="email" name="email" class="form-input" placeholder="nama@email.com" required></div>
                        <div class="form-group"><input type="password" name="password" class="form-input" placeholder="Password" required></div>
                        <div class="form-group">
                            <div class="custom-dropdown-modern" id="dropdownRoleTambah">
                                <div class="dropdown-selected-modern" onclick="toggleDropdownModern('roleOptionsTambah', 'dropdownRoleTambah')">
                                    <span id="roleTextTambah">Admin</span>
                                    <i class="fa-solid fa-chevron-down arrow-icon"></i>
                                </div>
                                <ul class="dropdown-options-modern" id="roleOptionsTambah">
                                    <li onclick="selectOptionModern('role', 'Admin', 'Tambah')">Admin</li>
                                    <li onclick="selectOptionModern('role', 'Super Admin', 'Tambah')">Super Admin</li>
                                </ul>
                                <input type="hidden" name="role" id="roleInputTambah" value="Admin">
                            </div>
                        </div>
                        <button type="submit" class="btn-save-main" style="width:100%">Buat Akun</button>
                    </form>
                </div>
                <div class="setting-card" style="flex: 1;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                        <h3 style="font-size: 20px; font-weight: 700; color: #333; margin: 0;">Daftar Users Aktif</h3>
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <span class="badge-total" id="total-user-badge" style="background: #e3f2fd; color: #2392ED; padding: 6px 14px; border-radius: 50px; font-size: 12px; font-weight: 600;">
                                Total: <?= $total_user; ?> User
                            </span>
                            <button type="button" class="btn-refresh-user" id="btn-refresh-user">
                                <span class="material-symbols-outlined icon-refresh-user">refresh</span>
                                Refresh
                            </button>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table-setting">
                            <thead>
                                <tr style="font-size: 13px;">
                                    <th>User</th>
                                    <th>Role</th>
                                    <th>Nama</th>
                                    <th>Email</th>
                                    <th>Status</th>
                                    <th style="text-align: center;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="userTableBody">
                                <?php 
                                $res_u = mysqli_query($conn, "SELECT * FROM users ORDER BY id DESC");
                                while($u = mysqli_fetch_assoc($res_u)): 
                                ?>
                                <tr style="font-size: 13px;">
                                    <td><strong><?= htmlspecialchars($u['username']); ?></strong></td>
                                    <td>
                                        <span class="badge <?= ($u['role'] === 'Super Admin') ? 'badge-super' : 'badge-admin'; ?> masonry-badge">
                                            <?= $u['role']; ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars($u['nama_lengkap']); ?></td>
                                    <td><?= htmlspecialchars($u['email']); ?></td>
                                    <td><small style="font-weight: bold; color: <?= ($u['status'] == 'Nonaktif') ? '#ff4d4d' : '#2392ED'; ?>;"><?= $u['status']; ?></small></td>
                                    <td style="text-align: center;">
                                        <?php if ($u['username'] !== $_SESSION['username']): ?>
                                            <a href="javascript:void(0)" onclick="openEditModal('<?= $u['id']; ?>', '<?= addslashes($u['nama_lengkap']); ?>', '<?= addslashes($u['username']); ?>', '<?= $u['email']; ?>', '<?= $u['role']; ?>', '<?= $u['status']; ?>')" style="color: #2392ED; font-weight: 600; text-decoration: none; margin-right: 10px;">Edit</a>
                                            <a href="javascript:void(0)" onclick="confirmDelete(<?= $u['id']; ?>)" style="color: #ff4d4d; font-weight: 600; text-decoration: none;">Hapus</a>
                                        <?php else: ?>
                                            <small style="color: #888;">(Anda)</small>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
        <footer class="main-footer">
            <p>&copy; 2026 Church Management System - Versi 3.0</p>
        </footer>
</div>

    <script src="pengaturan.js"></script>
</body>
</html>