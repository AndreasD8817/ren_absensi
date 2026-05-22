<?php
if ($format === 'excel') {
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename={$judul}.xls");
    header("Pragma: no-cache");
    header("Expires: 0");
}
$nama_bulan = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
$bulan_str = $nama_bulan[(int)$bulan];
?>
<!DOCTYPE html>
<html>
<head>
    <title><?= $judul ?></title>
    <!-- Favicon -->
    <link rel="icon" href="<?= BASE_URL ?>/img/logo.png" type="image/png">
    <?php if ($format === 'pdf'): ?>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #000; padding: 5px; text-align: left; }
        th { background-color: #f2f2f2; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .header { text-align: center; margin-bottom: 20px; }
        @media print {
            .no-print { display: none; }
        }
    </style>
    <?php else: ?>
    <style>
        table { border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 5px; }
    </style>
    <?php endif; ?>
</head>
<body <?= $format === 'pdf' ? 'onload="window.print()"' : '' ?>>

    <?php if ($format === 'pdf'): ?>
    <div class="no-print" style="margin-bottom: 20px;">
        <button onclick="window.print()" style="padding: 10px 20px; background: #1e3a8a; color: white; border: none; border-radius: 5px; cursor: pointer;">Cetak PDF / Print</button>
        <button onclick="window.close()" style="padding: 10px 20px; background: #dc2626; color: white; border: none; border-radius: 5px; cursor: pointer; margin-left: 10px;">Tutup</button>
    </div>
    <?php endif; ?>

    <div class="header">
        <h2>Laporan Rekap Absensi PT REN</h2>
        <p>Periode: <?= $bulan_str ?> <?= $tahun ?></p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Cabang</th>
                <th>NIK</th>
                <th>Nama Pegawai</th>
                <th>Jabatan</th>
                <th class="text-center">Hadir</th>
                <th class="text-center">Telat (x)</th>
                <th class="text-center">Menit Telat</th>
                <th class="text-center">Alfa</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($absensi)): ?>
            <tr><td colspan="9" class="text-center">Tidak ada data absensi untuk periode ini.</td></tr>
            <?php else: ?>
                <?php $no = 1; foreach ($absensi as $a): ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= esc($a['nama_cabang']) ?></td>
                    <td><?= esc($a['nip']) ?></td>
                    <td><?= esc($a['nama_lengkap']) ?></td>
                    <td><?= esc($a['jabatan']) ?></td>
                    <td class="text-center"><?= $a['total_hadir'] ?></td>
                    <td class="text-center"><?= $a['total_telat'] ?></td>
                    <td class="text-center"><?= $a['total_menit_terlambat'] ?? 0 ?> m</td>
                    <td class="text-center"><?= $a['total_alfa'] ?></td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

</body>
</html>
