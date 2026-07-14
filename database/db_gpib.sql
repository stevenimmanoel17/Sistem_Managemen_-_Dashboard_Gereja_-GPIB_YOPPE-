-- ==========================================================================
-- DOKUMENTASI & SCRIPT SKEMA DATABASE: db_gpib
-- Proyek: Church Management System Dashboard
-- Eksekusi: Langsung di phpMyAdmin (Menu SQL)
-- ==========================================================================

CREATE DATABASE IF NOT EXISTS `db_gpib`;
USE `db_gpib`;

-- --------------------------------------------------------------------------
-- 1. TABEL: users (Mengelola Akun Pengguna & Proteksi Keamanan Login)
-- --------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT COMMENT 'ID unik pengguna',
  `username` VARCHAR(50) NOT NULL UNIQUE COMMENT 'Nama akun untuk masuk sistem',
  `nama_lengkap` VARCHAR(100) NOT NULL COMMENT 'Nama asli pemilik akun',
  `email` VARCHAR(100) NOT NULL COMMENT 'Alamat email aktif',
  `password` VARCHAR(255) NOT NULL COMMENT 'Kata sandi yang di-hash dengan password_hash()',
  `role` ENUM('Admin', 'Super Admin') NOT NULL DEFAULT 'Admin' COMMENT 'Hak akses menu halaman',
  `status` ENUM('Aktif', 'Nonaktif') NOT NULL DEFAULT 'Aktif' COMMENT 'Status operasional akun',
  `login_attempts` INT NOT NULL DEFAULT 0 COMMENT 'Penghitung kesalahan input password berturut-turut',
  `last_attempt_time` DATETIME DEFAULT NULL COMMENT 'Waktu terakhir kegagalan login untuk lockout sistem',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Tanggal pembuatan akun otomatis',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Tabel akun pengguna dan keamanan login';

-- --------------------------------------------------------------------------
-- 2. TABEL: keuangan (Pencatatan Arus Dana Kas Masuk & Keluar)
-- --------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `keuangan` (
  `id` INT AUTO_INCREMENT COMMENT 'Nomor unik log transaksi',
  `tanggal` DATE NOT NULL COMMENT 'Hari eksekusi transaksi keuangan',
  `tipe` ENUM('pemasukan', 'pengeluaran') NOT NULL COMMENT 'Kategori tipe arus dana kas',
  `kategori` VARCHAR(100) NOT NULL COMMENT 'Kelompok pos anggaran dana',
  `keterangan` TEXT DEFAULT NULL COMMENT 'Deskripsi rinci atau uraian catatan transaksi',
  `nominal` DECIMAL(15,2) NOT NULL COMMENT 'Jumlah uang dalam satuan Rupiah',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Tabel catatan arus dana kas gereja';

-- --------------------------------------------------------------------------
-- 3. TABEL: jemaat (Master Identitas Keanggotaan Warga Jemaat)
-- --------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `jemaat` (
  `id` INT AUTO_INCREMENT COMMENT 'Nomor data jemaat',
  `nik` VARCHAR(20) DEFAULT NULL COMMENT 'Nomor Induk Kependudukan resmi jemaat',
  `no_kk` VARCHAR(20) DEFAULT NULL COMMENT 'Nomor Kartu Keluarga jemaat untuk pengelompokan',
  `nama_lengkap` VARCHAR(150) NOT NULL COMMENT 'Nama lengkap warga jemaat',
  `gender` ENUM('Laki-laki', 'Perempuan') NOT NULL COMMENT 'Jenis kelamin jemaat',
  `tgl_lahir` DATE NOT NULL COMMENT 'Tanggal lahir jemaat',
  `pelkat` VARCHAR(50) DEFAULT NULL COMMENT 'Pelayanan Kategori jemaat',
  `sektor` VARCHAR(50) DEFAULT NULL COMMENT 'Wilayah sektor pelayanan aktif',
  `tempat_baptis` VARCHAR(100) DEFAULT NULL COMMENT 'Lokasi gereja tempat jemaat dibaptis',
  `tgl_baptis` DATE DEFAULT NULL COMMENT 'Tanggal pelaksanaan sakramen baptis',
  `tempat_sidi` VARCHAR(100) DEFAULT NULL COMMENT 'Lokasi gereja tempat peneguhan sidi',
  `tgl_sidi` DATE DEFAULT NULL COMMENT 'Tanggal peneguhan sidi jemaat',
  `status` ENUM('Aktif', 'Nonaktif') NOT NULL DEFAULT 'Aktif' COMMENT 'Status keaktifan jemaat di gereja',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Tabel master keanggotaan jemaat';

-- --------------------------------------------------------------------------
-- 4. TABEL: inventaris (Manajemen Aset Barang Milik Organisasi)
-- --------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `inventaris` (
  `id` INT AUTO_INCREMENT COMMENT 'Nomor urut identitas aset barang',
  `kode_barang` VARCHAR(20) NOT NULL UNIQUE COMMENT 'Kode registrasi unik (Format: BBXX-20XX-00X)',
  `nama_barang` VARCHAR(150) NOT NULL COMMENT 'Nama dari barang aset',
  `kategori` VARCHAR(100) NOT NULL COMMENT 'Jenis pengelompokan barang',
  `lokasi` VARCHAR(100) NOT NULL COMMENT 'Ruangan tempat barang disimpan',
  `kondisi` ENUM('Baik', 'Perlu Perbaikan', 'Rusak') NOT NULL COMMENT 'Kondisi fisik aset barang',
  `penanggung_jawab` VARCHAR(100) DEFAULT NULL COMMENT 'Nama personil pengawas barang',
  `tanggal_masuk` DATE DEFAULT NULL COMMENT 'Waktu serah terima atau pembelian barang',
  `asal_barang` VARCHAR(100) NOT NULL COMMENT 'Sumber perolehan barang',
  `harga_beli` DECIMAL(15,2) DEFAULT 0.00 COMMENT 'Nilai nominal harga pembelian barang aset',
  `keterangan` TEXT DEFAULT NULL COMMENT 'Catatan tambahan spesifikasi atau info barang',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Tabel master pengelolaan aset inventaris';

-- --------------------------------------------------------------------------
-- 5. TABEL: profil_gereja (Konfigurasi Identitas Utama Organisasi)
-- --------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `profil_gereja` (
  `id` INT AUTO_INCREMENT COMMENT 'ID profil',
  `nama_gereja` VARCHAR(150) NOT NULL COMMENT 'Nama resmi gereja lokal',
  `alamat` TEXT NOT NULL COMMENT 'Alamat lengkap domisili operasional',
  `logo` VARCHAR(255) NOT NULL COMMENT 'Path jalur penyimpanan berkas gambar logo utama',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Tabel konfigurasi utilitas identitas aplikasi';

-- --------------------------------------------------------------------------
-- 6. TABEL: struktur_organisasi (Data Pemegang Jabatan Inti Majelis)
-- --------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `struktur_organisasi` (
  `id` INT AUTO_INCREMENT COMMENT 'ID struktur',
  `jabatan1` VARCHAR(50) DEFAULT 'Ketua' COMMENT 'Nama posisi jabatan 1',
  `nama1` VARCHAR(100) NOT NULL COMMENT 'Nama pejabat aktif 1',
  `jabatan2` VARCHAR(50) DEFAULT 'Sekretaris' COMMENT 'Nama posisi jabatan 2',
  `nama2` VARCHAR(100) NOT NULL COMMENT 'Nama pejabat aktif 2',
  `jabatan3` VARCHAR(50) DEFAULT 'Bendahara' COMMENT 'Nama posisi jabatan 3',
  `nama3` VARCHAR(100) NOT NULL COMMENT 'Nama pejabat aktif 3',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Tabel dinamis kepengurusan inti majelis jemaat';