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
    <?php if ($format === 'pdf'): ?>
    <style>
        body { font-family: sans-serif; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #000; padding: 4px; text-align: left; }
        th { background-color: #f2f2f2; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .header { text-align: center; margin-bottom: 20px; }
        @media print {
            .no-print { display: none; }
            @page { size: landscape; }
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
        <h2>Laporan Penggajian PT REN</h2>
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
                <th class="text-right">Gaji Pokok</th>
                <th class="text-right">Tunjangan Tetap</th>
                <th class="text-right">Overtime</th>
                <th class="text-right">Potongan Pegawai</th>
                <th class="text-right">Potongan Telat/Alfa</th>
                <th class="text-right">Gaji Bersih</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($gaji)): ?>
            <tr><td colspan="11" class="text-center">Tidak ada data gaji untuk periode ini.</td></tr>
            <?php else: ?>
                <?php $no = 1; $total = 0; foreach ($gaji as $g): 
                    $tunj_tetap = $g['nilai_tunj_jabatan'] + $g['nilai_tunj_transportasi'] + $g['nilai_tunj_makan'] + $g['nilai_tunj_kehadiran'] + $g['nilai_tunj_lainnya'];
                    $pot_pegawai = $g['pot_jht_2'] + $g['pot_jp_1'] + $g['pot_bpjs_kes_1'] + $g['pot_pph21'];
                    $total += $g['total_gaji_bersih'];
                ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= htmlspecialchars($g['nama_cabang']) ?></td>
                    <td><?= htmlspecialchars($g['nip']) ?></td>
                    <td><?= htmlspecialchars($g['nama_lengkap']) ?></td>
                    <td><?= htmlspecialchars($g['jabatan']) ?></td>
                    <td class="text-right"><?= number_format($g['nilai_gaji_pokok'], 0, ',', '.') ?></td>
                    <td class="text-right"><?= number_format($tunj_tetap, 0, ',', '.') ?></td>
                    <td class="text-right"><?= number_format($g['nilai_overtime'] ?? 0, 0, ',', '.') ?></td>
                    <td class="text-right"><?= number_format($pot_pegawai, 0, ',', '.') ?></td>
                    <td class="text-right"><?= number_format($g['total_potongan_telat_alfa'], 0, ',', '.') ?></td>
                    <td class="text-right font-bold"><?= number_format($g['total_gaji_bersih'], 0, ',', '.') ?></td>
                </tr>
                <?php endforeach; ?>
                <tr>
                    <td colspan="10" class="text-right font-bold">TOTAL GRAND:</td>
                    <td class="text-right font-bold"><?= number_format($total, 0, ',', '.') ?></td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

</body>
</html>
