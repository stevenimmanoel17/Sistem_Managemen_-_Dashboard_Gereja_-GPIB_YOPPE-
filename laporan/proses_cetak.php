<?php
include '../koneksi.php';

$jenis = $_GET['jenis'];
$format = $_GET['format'];
$periode = $_GET['periode'];

require_once '../vendor/autoload.php'; 
use Dompdf\Dompdf;
use Dompdf\Options;

// Ambil Data Berdasarkan Jenis & Periode
if ($jenis == 'keuangan') {
    if ($periode == 'Hari Ini') { $filter = "WHERE DATE(tanggal) = CURRENT_DATE()"; }
    elseif ($periode == 'Bulan Ini') { $filter = "WHERE MONTH(tanggal) = MONTH(CURRENT_DATE()) AND YEAR(tanggal) = YEAR(CURRENT_DATE())"; }
    elseif ($periode == 'Tahun Ini') { $filter = "WHERE YEAR(tanggal) = YEAR(CURRENT_DATE())"; }
    else { $filter = ""; }

    $query = "SELECT * FROM keuangan $filter ORDER BY tanggal DESC";
    $title = "Laporan Keuangan";
} elseif ($jenis == 'inventaris') {
    $query = "SELECT * FROM inventaris ORDER BY nama_barang ASC";
    $title = "Laporan Data Inventaris";
} else {
    $query = "SELECT * FROM jemaat ORDER BY nama_lengkap ASC";
    $title = "Laporan Data Jemaat";
}

$result = mysqli_query($conn, $query);

// Logika Output Berdasarkan Format
// Excel
if ($format == 'excel') {
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=\"$title.xls\"");
    header("Pragma: no-cache");
    header("Expires: 0");

    // Header Kolom
    if ($jenis == 'keuangan') {
        echo "Tanggal\tTipe\tKategori\tUraian\tNominal\n";
    } elseif ($jenis == 'inventaris') {
        echo "Kode Barang\tNama Barang\tKategori\tLokasi Ruangan\tKondisi\tPenanggung Jawab\n";
    } else {
        echo "NIK\tNama Lengkap\tJenis Kelamin\tTgl Lahir\tPelkat\tSektor\tTempat Baptis\tTanggal Baptis\tTempat Sidi\tTanggal Sidi\tStatus\n";
    }

    // Isi Data (Gunakan \t sebagai pemisah antar kolom)
while($row = mysqli_fetch_assoc($result)) {
    if ($jenis == 'keuangan') {
        echo $row['tanggal'] . "\t" . $row['tipe'] . "\t" . $row['kategori'] . "\t" . $row['keterangan'] . "\t" . $row['nominal'] . "\n";
    } elseif ($jenis == 'inventaris') {
        echo $row['kode_barang'] . "\t" . $row['nama_barang'] . "\t" . $row['kategori'] . "\t" . $row['lokasi'] . "\t" . $row['kondisi'] . "\t" . $row['penanggung_jawab'] . "\n";
    } else {
        echo $row['nik'] . "\t" . $row['nama_lengkap'] . "\t" . $row['gender'] . "\t" . $row['tgl_lahir'] . "\t" . $row['pelkat'] . "\t" . $row['sektor'] . "\t" . $row['tempat_baptis'] . "\t" . $row['tgl_baptis'] . "\t" . $row['tempat_sidi'] . "\t" . $row['tgl_sidi'] . "\t" . $row['status'] . "\n";
    }
}
    exit;
}

// CSV
if ($format == 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="'.$title.'.csv"');
    
    $output = fopen('php://output', 'w');
    
    // Tulis Header Kolom
    if ($jenis == 'keuangan') {
        fputcsv($output, ['Tanggal', 'Tipe', 'Kategori', 'Uraian', 'Nominal']);
    } elseif ($jenis == 'inventaris') {
        fputcsv($output, ['Kode Barang', 'Nama Barang', 'Kategori', 'Lokasi Ruangan', 'Kondisi', 'Penanggung Jawab']);
    } else {
        fputcsv($output, ['NIK', 'Nama Lengkap', 'Jenis Kelamin', 'Tgl Lahir', 'Pelkat', 'Sektor', 'Tempat Baptis', 'Tanggal Baptis', 'Tempat Sidi', 'Tanggal Sidi', 'Status']);
    }
    
    // Tulis Data
    while($row = mysqli_fetch_assoc($result)) {
        if ($jenis == 'keuangan') {
            fputcsv($output, [$row['tanggal'], $row['tipe'], $row['kategori'], $row['keterangan'], $row['nominal']]);
        } elseif ($jenis == 'inventaris') {
            fputcsv($output, [$row['kode_barang'], $row['nama_barang'], $row['kategori'], $row['lokasi'], $row['kondisi'], $row['penanggung_jawab']]);
        } else {
            fputcsv($output, [$row['nik'], $row['nama_lengkap'], $row['gender'], $row['tgl_lahir'], $row['pelkat'], $row['sektor'], $row['tempat_baptis'], $row['tgl_baptis'], $row['tempat_sidi'], $row['tgl_sidi'], $row['status']]);
        }
    }
    fclose($output);
    exit;
}

// PDF dan PRINT
if ($format == 'pdf' || $format == 'print') {
    if ($jenis == 'keuangan') {
        $jabatan = 'Bendahara Jemaat';
        $nama_pejabat = 'Kasih';
    } elseif ($jenis == 'inventaris') {
        $jabatan = 'Komisi';
        $nama_pejabat = 'Minar'; 
    } else {
        $jabatan = 'Sekretaris Jemaat';
        $nama_pejabat = 'Mefi';
    }

    $path_logo = '../icon/GPIB-NoCapt.png';
    $data_logo = file_get_contents($path_logo);
    $base64_logo = 'data:image/png;base64,' . base64_encode($data_logo);

    $html = '
    <html>
    <head>
        <style>
            body { font-family: sans-serif; padding: 10px; }
            .data-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
            .data-table th, .data-table td { border: 1px solid #000; padding: 6px; text-align: center; font-size: 11px; }
            .data-table th { background-color: #f2f2f2; }
            .kop-table { width: 100%; border-collapse: collapse; border: none; }
            .kop-table td { border: none !important; padding: 0; }
            ' . (($jenis == 'jemaat' || $jenis == 'inventaris') ? '@page { size: A4 landscape; margin: 10mm; }' : '@page { size: A4 portrait; margin: 10mm; }') . '
        </style>
    </head>
    <body>
        <table class="kop-table" style="border-bottom: 3px double #000; padding-bottom: 10px; margin-bottom: 15px; width: 100%;">
            <tr>
                <td style="width: 12%; text-align: right; vertical-align: middle; padding-right: 15px;">
                    <img src="' . $base64_logo . '" style="width: 100px; height: auto;">
                </td>
                <td style="width: 88%; text-align: left; vertical-align: middle;">
                    <div style="font-size: 20px; font-weight: bold; line-height: 1.2;">GEREJA PROTESTAN di INDONESIA bagian BARAT</div>
                    <div style="font-size: 20px; font-weight: bold; line-height: 1.2;">(GPIB)</div>
                    <div style="font-size: 20px; font-weight: bold; margin-top: 4px; line-height: 1.2;">JEMAAT "YOPPE" BELAWAN</div>
                    <div style="font-size: 12px; margin-top: 4px;">Jl. Selebes Purwodadi Ling.43 Belawan - Medan 20412</div>
                    <div style="font-size: 12px;">Email: yoppe.belawan@gpib.or.id</div>
                    <div style="margin-bottom: 4px;"></div>
                </td>
            </tr>
        </table>

        <div style="text-align: center; font-size: 18px; font-weight: bold; text-transform: uppercase; margin-bottom: 15px; text-decoration: underline;">
            ' . strtoupper($title) . '
        </div>

        <table class="data-table">
            <thead>';   
    
    if ($jenis == 'keuangan') {
        $html .= '<tr><th>Tanggal</th><th>Tipe</th><th>Kategori</th><th>Uraian</th><th>Nominal</th></tr>';
    } elseif ($jenis == 'inventaris') {
        $html .= '<tr><th>Kode Barang</th><th>Nama Barang</th><th>Kategori</th><th>Lokasi Ruangan</th><th>Kondisi</th><th>Penanggung Jawab</th></tr>';
    } else {
        $html .= '<tr><th>NIK</th><th>Nama Lengkap</th><th>Jenis Kelamin</th><th>Tgl Lahir</th><th>Pelkat</th><th>Sektor</th><th>Tempat Baptis</th><th>Tanggal Baptis</th><th>Tempat Sidi</th><th>Tanggal Sidi</th><th>Status</th></tr>';
    }

    $html .= '</thead><tbody>';
    while($row = mysqli_fetch_assoc($result)) {
        $html .= '<tr>';
        if ($jenis == 'keuangan') {
            $tgl = date('d-m-Y', strtotime($row['tanggal']));
            $html .= '<td>'.$tgl.'</td><td>'.$row['tipe'].'</td><td>'.$row['kategori'].'</td><td>'.$row['keterangan'].'</td><td>Rp '.number_format($row['nominal'], 0, ',', '.').'</td>';
        } elseif ($jenis == 'inventaris') {
            $html .= '<td>'.$row['kode_barang'].'</td><td>'.$row['nama_barang'].'</td><td>'.$row['kategori'].'</td><td>'.$row['lokasi'].'</td><td>'.$row['kondisi'].'</td><td>'.($row['penanggung_jawab'] ?: '-').'</td>';
        } else {
            $tgl_lahir = ($row['tgl_lahir'] && $row['tgl_lahir'] != '0000-00-00') ? date('d-m-Y', strtotime($row['tgl_lahir'])) : '-';
            $tgl_baptis = ($row['tgl_baptis'] && $row['tgl_baptis'] != '0000-00-00') ? date('d-m-Y', strtotime($row['tgl_baptis'])) : '-';
            $tgl_sidi = ($row['tgl_sidi'] && $row['tgl_sidi'] != '0000-00-00') ? date('d-m-Y', strtotime($row['tgl_sidi'])) : '-';
            $html .= '<td>'.$row['nik'].'</td><td>'.$row['nama_lengkap'].'</td><td>'.$row['gender'].'</td><td>'.$tgl_lahir.'</td><td>'.$row['pelkat'].'</td><td>'.$row['sektor'].'</td><td>'.$row['tempat_baptis'].'</td><td>'.$tgl_baptis.'</td><td>'.$row['tempat_sidi'].'</td><td>'.$tgl_sidi.'</td><td>'.$row['status'].'</td>';
        }
        $html .= '</tr>';
    }
    $html .= '</tbody></table>
    <div style="margin-top: 20px; width: 100%;">
        <table style="border: none; width: 100%; border-collapse: collapse;">
            <tr>
                <td style="border: none; width: 70%;"></td>
                <td style="border: none; text-align: center; font-size: 12px;">
                    <p>Belawan, ' . (function() {
                        $bulan_id = [
                            'January' => 'Januari', 'February' => 'Februari', 'March' => 'Maret',
                            'April' => 'April', 'May' => 'Mei', 'June' => 'Juni',
                            'July' => 'Juli', 'August' => 'Agustus', 'September' => 'September',
                            'October' => 'Oktober', 'November' => 'November', 'December' => 'Desember'
                        ];
                        $tgl_inggris = date('d F Y');
                        return strtr($tgl_inggris, $bulan_id);
                    })() . '</p>
                    <p style="margin-bottom: 65px;">' . $jabatan . ',</p>
                    <p><strong>( ________________________ )</strong></p>
                    <p>' . $nama_pejabat . '</p>
                </td>
            </tr>
        </table>
    </div>
    </body></html>';

    // Logika Pemisahan Fitur
    if ($format == 'pdf') {
        // Eksekusi Dompdf untuk membuat file PDF asli
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        
        $orientation = ($jenis == 'jemaat' || $jenis == 'inventaris') ? 'landscape' : 'portrait';
        $dompdf->setPaper('A4', $orientation);
        
        $dompdf->render();
        $dompdf->stream($title . ".pdf", ["Attachment" => 0]);
        exit;
    } else {
        echo $html . '<script>window.print();</script>';
        exit;
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo $title; ?></title>
    <style>
        table { width: 100%; border-collapse: collapse; font-family: sans-serif; }
        th, td { border: 1px solid #000; padding: 8px; text-align: left; }
        th { background: #f2f2f2; }
        .header { text-align: center; margin-bottom: 20px; }
    </style>
</head>
<body <?php echo ($format == 'print') ? 'onload="window.print()"' : ''; ?>>
    <div class="header">
        <h2>GPIB "YOPPE" BELAWAN</h2>
        <h3><?php echo strtoupper($title); ?></h3>
    </div>
    <table border="1">
        <thead>
            <?php if ($jenis == 'keuangan'): ?>
                <tr><th>Tanggal</th><th>Tipe</th><th>Kategori</th><th>Uraian</th><th>Nominal</th></tr>
            <?php else: ?>
                <tr><th>NIK</th><th>Nama Lengkap</th><th>Jenis Kelamin</th><th>Tgl Lahir</th><th>Pelkat</th><th>Sektor</th><th>Tempat Baptis</th><th>Tanggal Baptis</th><th>Tempat Sidi</th><th>Tanggal Sidi</th><th>Status</th></tr>
            <?php endif; ?>
        </thead>
        <tbody>
            <?php while($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <?php if ($jenis == 'keuangan'): ?>
                        <td><?php echo $row['tanggal']; ?></td>
                        <td><?php echo $row['tipe']; ?></td>
                        <td><?php echo $row['kategori']; ?></td>
                        <td><?php echo $row['keterangan']; ?></td>
                        <td>Rp <?php echo number_format($row['nominal'], 0, ',', '.'); ?></td>
                    <?php else: ?>
                        <td><?php echo $row['nik']; ?></td>
                        <td><?php echo $row['nama_lengkap']; ?></td>
                        <td><?php echo $row['gender']; ?></td>
                        <td><?php echo $row['tgl_lahir']; ?></td>
                        <td><?php echo $row['pelkat']; ?></td>
                        <td><?php echo $row['sektor']; ?></td>
                        <td><?php echo $row['tempat_baptis']; ?></td>
                        <td><?php echo $row['tgl_baptis']; ?></td>
                        <td><?php echo $row['tempat_sidi']; ?></td>
                        <td><?php echo $row['tgl_sidi']; ?></td>
                        <td><?php echo $row['status']; ?></td>
                    <?php endif; ?>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>
</html>