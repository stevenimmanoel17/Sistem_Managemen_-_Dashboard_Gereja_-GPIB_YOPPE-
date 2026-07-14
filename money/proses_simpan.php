<?php
session_start();
// Cek login
if (!isset($_SESSION['login'])) {
    header("Location: ../login/Login.html");
    exit;
}

// Koneksi ke database 
$host = "localhost";
$user = "root";
$pass = "";
$db   = "db_gpib"; // Ganti dengan nama database kamu

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil data dari form
    $tanggal    = $_POST['tanggal'];
    $tipe       = $_POST['tipe']; // pemasukan atau pengeluaran
    $kategori   = $_POST['kategori'];
    $nominal    = $_POST['nominal'];
    $keterangan = $_POST['keterangan'];

    // Validasi nominal tidak boleh negatif
    if ($nominal < 0) {
        header("Location: money.php?status=error&msg=nominal_negatif");
        exit;
    }

    // Query simpan data menggunakan Prepared Statement
    $query = "INSERT INTO keuangan (tanggal, tipe, kategori, nominal, keterangan) VALUES (?, ?, ?, ?, ?)";
    $stmt  = mysqli_prepare($conn, $query);
    
    // "sssis" berarti string, string, string, integer, string
    mysqli_stmt_bind_param($stmt, "sssis", $tanggal, $tipe, $kategori, $nominal, $keterangan);

    if (mysqli_stmt_execute($stmt)) {
        // Berhasil simpan, balik ke money.php dengan status sukses
        echo "success";
    } else {
        // Gagal simpan
        echo "error";
    }
exit;
    mysqli_stmt_close($stmt);
    mysqli_close($conn);
}
?>