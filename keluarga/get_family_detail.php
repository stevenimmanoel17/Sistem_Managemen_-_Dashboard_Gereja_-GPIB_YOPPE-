<?php
include '../koneksi.php'; 
$no_kk = mysqli_real_escape_string($conn, $_GET['no_kk']);

$query = mysqli_query($conn, "SELECT * FROM jemaat WHERE no_kk = '$no_kk'");

echo '<div class="modal-header-custom">
        <h2>Daftar Anggota Keluarga</h2>
        <p>Nomor KK: <span>'.$no_kk.'</span></p>
      </div>';

echo '<div class="table-responsive">
        <table class="detail-table-modern">
            <thead>
                <tr>
                    <th>NIK</th>
                    <th>Nama</th>
                    <th>Posisi</th>
                    <th>Jenis Kemalin</th>
                    <th>Tempat Lahir</th>
                    <th>Tgl Lahir</th>
                    <th>Nikah</th>
                    <th>Kontak/WA</th>
                    <th style="text-align: center;">Status</th>
                </tr>
            </thead>
            <tbody>';

while($row = mysqli_fetch_assoc($query)) { 
    $nik      = $row['nik'];
    $nama     = $row['nama_lengkap'];
    $posisi   = $row['posisi'];
    $gender   = $row['gender'];
    $tmpt     = $row['tmpt_lahir'];
    $tgl      = $row['tgl_lahir'];
    $nikah  = $row['status_nikah'];
    $no_hp  = isset($row['no_hp']) ? $row['no_hp'] : '-';
    $status   = $row['status']; // Di gambar tertulis 'Aktif' atau 'Non-Aktif'

    // Logika Warna Status
    $status_clean = strtolower(trim($status));
    $status_class = ($status_clean == 'non-aktif') ? 'status-red' : 'status-blue';

    echo '<tr>
            <td><small>'.$nik.'</small></td>
            <td><strong>'.$nama.'</strong></td>
            <td>'.$posisi.'</td>
            <td>'.$gender.'</td>
            <td>'.$tmpt.'</td>
            <td>'.$tgl.'</td>
            <td>'.$nikah.'</td>
            <td>
                <a href="https://wa.me/'.$no_hp.'" target="_blank" style="text-decoration:none; color:#25D366; font-weight:600;">
                    '.$no_hp.'
                </a>
            </td>
            <td style="text-align: center;">
                <span class="badge-status '.$status_class.'">'.$status.'</span>
            </td>
          </tr>';
}
echo '</tbody></table></div>';
?>