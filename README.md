# ⛪ Sistem Manajemen & Dashboard Gereja (GPIB YOPPE)

Sistem Manajemen Jemaat dan Finansial Gereja berbasis web yang dirancang modern, responsif, dan aman. Aplikasi ini mempermudah pengelolaan data warga jemaat, pencatatan arus kas keuangan, inventarisasi aset majelis, hingga pengaturan hak akses pengguna secara tersentralisasi.

---

## ✨ Fitur Utama Sistem

Sistem ini dilengkapi dengan modul-modul esensial untuk kebutuhan operasional gereja:

*   **Sistem Proteksi Keamanan Login & Lockout**: Fitur masuk akun yang aman dengan validasi password bawaan, pembatasan percobaan login salah berturut-turut untuk mencegah brute-force, serta mekanisme opsi "Ingati Saya" yang memanfaatkan penyimpanan lokal mandiri browser tanpa mengganggu keamanan.
*   **Modul Manajemen Warga Jemaat**: Pencatatan data master keanggotaan jemaat secara lengkap meliputi Nomor Induk Kependudukan (NIK), Kartu Keluarga (KK), tanggal lahir, sektor wilayah, hingga data sakramen penting seperti Baptis dan Sidi.
*   **Modul Arus Dana Kas Keuangan**: Pencatatan detail transaksi kas masuk (pemasukan) dan kas keluar (pengeluaran) lengkap dengan pengelompokan kategori pos anggaran serta grafik visualisasi laporan.
*   **Manajemen Aset & Inventaris**: Pendataan inventaris barang milik organisasi, lengkap dengan penomoran kode registrasi unik, pencatatan kondisi fisik aset, nilai harga beli, serta penanggung jawab pengawas.
*   **Dashboard Pengaturan & Dinamis**: Antarmuka konfigurasi terpusat untuk memperbaharui profil identitas utama gereja, logo aplikasi, serta susunan pemegang jabatan inti majelis jemaat secara langsung dari dashboard.

---

## 🛠️ Teknologi yang Digunakan

*   **Backend & Server**: PHP (Sisi server)
*   **Database**: MySQL / MariaDB
*   **Antarmuka/Frontend**: HTML5, CSS3 (Custom Responsive Layout), Vanilla JavaScript
*   **Pustaka Eksternal**: SweetAlert2 (Notifikasi UI)

---

## 🚀 Panduan Instalasi Lokal

Ikuti langkah berikut untuk menjalankan proyek ini di komputer lokal kamu menggunakan aplikasi XAMPP:

### 1. Persiapan Berkas Proyek
1. Pastikan aplikasi **XAMPP** sudah terinstal di komputer Anda.
2. Unduh atau klon repositori ini, lalu letakkan folder proyek ke dalam direktori server lokal:
   `C:\xampp\htdocs\DashboardManajemenYoppe`

### 2. Import Database
1. Aktifkan modul Apache dan MySQL pada XAMPP Control Panel.
2. Buka browser dan masuk ke tautan http://localhost/phpmyadmin/.
3. Buat database baru dengan nama db_gpib.
4. Masuk ke menu SQL, salin dan tempel perintah script struktur tabel database yang telah disediakan, lalu klik Go/Kirim.

### 3. Konfigurasi Koneksi Database
Pastikan file `koneksi.php` pada folder root proyek sudah disesuaikan dengan koneksi database Anda: `$conn = mysqli_connect("localhost", "root", "", "db_gpib");`

### 4. Menjalankan Aplikasi
Buka browser Anda dan akses halaman utama sistem melalui tautan berikut:
`http://localhost/DashboardManajemenYoppe/login/Login.php`

---

## 🔐 Akun Akses Default (Uji Coba)
Setelah berhasil mengimpor struktur database bersih, Anda dapat menggunakan akun bawaan berikut untuk masuk ke dalam dashboard:
* **Username**: `admin`
* **Password**: `admin123`

---

## 📱 Uji Coba Online Melalui Perangkat Mobile (Ngrok)
Jika Anda ingin mendemonstrasikan tampilan responsif mobile aplikasi ini ke perangkat smartphone Android tanpa hosting berbayar, jalankan perintah `ngrok http 80` pada terminal.

Salin tautan publik secure `https://...ngrok-free.dev` yang dihasilkan oleh Ngrok, lalu buka di perangkat ponsel Anda dengan struktur alamat:
`https://alamat-ngrok-kamu.ngrok-free.dev/DashboardManajemenYoppe/login/Login.php`
