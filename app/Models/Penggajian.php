<?php

class Penggajian {
    private $db;

    public function __construct() {
        $this->db = (new Database())->getConnection();
    }

    // Mengambil ringkasan absensi pegawai untuk bulan & tahun tertentu
    public function getRingkasanAbsensi($bulan, $tahun) {
        // 1. Tarik ringkasan dasar (tanpa total_alfa)
        $stmt = $this->db->prepare("
            SELECT 
                u.id_user, u.nip, u.nama_lengkap, u.jabatan, u.gaji_pokok, u.id_cabang,
                u.status_pajak, u.tunj_jabatan, u.tunj_transportasi, u.tunj_makan, u.tunj_kehadiran, u.tunj_lainnya,
                c.nama_cabang, c.denda_1_5, c.denda_6_10, c.denda_11_15, c.denda_16_30, c.denda_31_60, c.denda_alfa,
                c.tarif_lembur_per_jam,
                COUNT(a.id_absensi) AS total_hadir,
                SUM(CASE WHEN a.status = 'telat' THEN 1 ELSE 0 END) AS total_telat,
                SUM(a.menit_terlambat) AS total_menit_terlambat
            FROM users u
            JOIN cabang c ON u.id_cabang = c.id_cabang
            LEFT JOIN absensi a ON a.id_user = u.id_user
                AND MONTH(a.tanggal) = :bulan
                AND YEAR(a.tanggal) = :tahun
            WHERE u.role = 'pegawai' AND u.is_active = 1
            GROUP BY u.id_user
            ORDER BY c.nama_cabang, u.nama_lengkap
        ");
        $stmt->bindParam(':bulan', $bulan, PDO::PARAM_INT);
        $stmt->bindParam(':tahun', $tahun, PDO::PARAM_INT);
        $stmt->execute();
        $pegawai = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 2. Tarik Data Master untuk Kalkulasi Alfa Dinamis
        // a. Hari Kerja Cabang
        $stmt_jk = $this->db->query("SELECT id_cabang, hari, is_libur_akhir_pekan FROM jam_kerja_cabang");
        $jam_kerja = [];
        foreach ($stmt_jk->fetchAll(PDO::FETCH_ASSOC) as $jk) {
            $jam_kerja[$jk['id_cabang']][$jk['hari']] = $jk['is_libur_akhir_pekan'];
        }

        // b. Libur Nasional Bulan Ini
        $stmt_ln = $this->db->prepare("SELECT tanggal FROM hari_libur WHERE MONTH(tanggal)=:bulan AND YEAR(tanggal)=:tahun");
        $stmt_ln->execute([':bulan' => $bulan, ':tahun' => $tahun]);
        $libur_nasional = array_column($stmt_ln->fetchAll(PDO::FETCH_ASSOC), 'tanggal');

        // c. Libur Cabang Bulan Ini
        $stmt_lc = $this->db->prepare("SELECT id_cabang, tanggal, status FROM libur_override WHERE MONTH(tanggal)=:bulan AND YEAR(tanggal)=:tahun");
        $stmt_lc->execute([':bulan' => $bulan, ':tahun' => $tahun]);
        $libur_cabang = [];
        foreach ($stmt_lc->fetchAll(PDO::FETCH_ASSOC) as $lc) {
            $libur_cabang[$lc['id_cabang']][$lc['tanggal']] = $lc['status'];
        }

        // d. Cuti Pegawai Bulan Ini (Approved)
        $stmt_cuti = $this->db->prepare("SELECT id_user, tanggal_mulai, tanggal_selesai FROM pengajuan_cuti WHERE status='approved' AND (MONTH(tanggal_mulai)=:bulan1 OR MONTH(tanggal_selesai)=:bulan2) AND (YEAR(tanggal_mulai)=:tahun1 OR YEAR(tanggal_selesai)=:tahun2)");
        $stmt_cuti->execute([':bulan1' => $bulan, ':bulan2' => $bulan, ':tahun1' => $tahun, ':tahun2' => $tahun]);
        $cuti_pegawai = [];
        foreach ($stmt_cuti->fetchAll(PDO::FETCH_ASSOC) as $c) {
            $start = strtotime($c['tanggal_mulai']);
            $end = strtotime($c['tanggal_selesai']);
            for ($i = $start; $i <= $end; $i += 86400) {
                if ((int)date('m', $i) == $bulan) {
                    $cuti_pegawai[$c['id_user']][] = date('Y-m-d', $i);
                }
            }
        }

        // 3. Kalkulasi Hari Wajib Hadir vs Kehadiran Aktual
        $hari_map = [1=>'Senin', 2=>'Selasa', 3=>'Rabu', 4=>'Kamis', 5=>'Jumat', 6=>'Sabtu', 7=>'Minggu'];
        $jumlah_hari_bulan_ini = cal_days_in_month(CAL_GREGORIAN, $bulan, $tahun);
        
        // Batasi perhitungan alfa sampai hari ini (jika bulan berjalan)
        $hari_terakhir_dihitung = $jumlah_hari_bulan_ini;
        if ($bulan == date('n') && $tahun == date('Y')) {
            $hari_terakhir_dihitung = date('j');
        } else if (($tahun == date('Y') && $bulan > date('n')) || $tahun > date('Y')) {
            $hari_terakhir_dihitung = 0; // Bulan depan, belum ada hari
        }

        foreach ($pegawai as &$p) {
            $id_cabang = $p['id_cabang'];
            $id_user = $p['id_user'];
            $total_wajib_hadir = 0;

            for ($d = 1; $d <= $hari_terakhir_dihitung; $d++) {
                $tgl = sprintf("%04d-%02d-%02d", $tahun, $bulan, $d);
                $hari_index = date('N', strtotime($tgl));
                $nama_hari = $hari_map[$hari_index];

                // Cek Cuti
                if (isset($cuti_pegawai[$id_user]) && in_array($tgl, $cuti_pegawai[$id_user])) continue;

                // Cek Status Libur Override Cabang
                $is_override_libur = false;
                $is_override_masuk = false;
                if (isset($libur_cabang[$id_cabang][$tgl])) {
                    if ($libur_cabang[$id_cabang][$tgl] === 'libur_lokal') $is_override_libur = true;
                    if ($libur_cabang[$id_cabang][$tgl] === 'tetap_masuk') $is_override_masuk = true;
                }

                // Cek Libur Nasional
                $is_libur_nasional = in_array($tgl, $libur_nasional);

                // Cek Weekend (Tidak ada di jam_kerja_cabang atau is_libur_akhir_pekan = 1)
                $is_weekend = !isset($jam_kerja[$id_cabang][$nama_hari]) || $jam_kerja[$id_cabang][$nama_hari] == 1;

                // Logika Keputusan Hari Wajib Hadir
                if ($is_override_masuk) {
                    $total_wajib_hadir++;
                } else if ($is_override_libur) {
                    // Libur lokal, tidak wajib hadir
                } else if ($is_libur_nasional) {
                    // Libur nasional, tidak wajib hadir
                } else if ($is_weekend) {
                    // Weekend, tidak wajib hadir
                } else {
                    // Hari kerja biasa
                    $total_wajib_hadir++;
                }
            }

            // Hitung Alfa
            $alfa = $total_wajib_hadir - $p['total_hadir'];
            $p['total_alfa'] = $alfa > 0 ? $alfa : 0;
        }

        return $pegawai;
    }

    // Hitung denda keterlambatan berdasarkan akumulasi menit
    public function hitungDendaTelat($menit_terlambat, $cabang) {
        $total_denda = 0;
        if ($menit_terlambat >= 1  && $menit_terlambat <= 5)  $total_denda = $cabang['denda_1_5'];
        elseif ($menit_terlambat <= 10) $total_denda = $cabang['denda_6_10'];
        elseif ($menit_terlambat <= 15) $total_denda = $cabang['denda_11_15'];
        elseif ($menit_terlambat <= 30) $total_denda = $cabang['denda_16_30'];
        elseif ($menit_terlambat > 30)  $total_denda = $cabang['denda_31_60'];
        return $total_denda;
    }

    // Mengecek apakah gaji bulan ini sudah di-generate
    public function sudahDiGenerate($id_user, $bulan, $tahun) {
        $stmt = $this->db->prepare("SELECT id_gaji FROM penggajian_bulanan WHERE id_user=:id AND bulan=:bulan AND tahun=:tahun");
        $stmt->bindParam(':id',    $id_user, PDO::PARAM_INT);
        $stmt->bindParam(':bulan', $bulan, PDO::PARAM_INT);
        $stmt->bindParam(':tahun', $tahun, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    // Generate dan simpan penggajian bulanan semua pegawai
    public function generateGaji($bulan, $tahun) {
        // Hapus draft yang sudah ada (supaya tombol Re-generate berfungsi memperbarui data)
        $stmt_del = $this->db->prepare("DELETE FROM penggajian_bulanan WHERE bulan=:bulan AND tahun=:tahun AND status='draft'");
        $stmt_del->bindParam(':bulan', $bulan, PDO::PARAM_INT);
        $stmt_del->bindParam(':tahun', $tahun, PDO::PARAM_INT);
        $stmt_del->execute();

        $data_absensi = $this->getRingkasanAbsensi($bulan, $tahun);
        $berhasil = 0;

        foreach ($data_absensi as $p) {
            // Skip jika sudah di-generate dan statusnya 'published'
            if ($this->sudahDiGenerate($p['id_user'], $bulan, $tahun)) continue;

            // Hitung denda telat & alfa
            $total_denda_telat  = $this->hitungDendaTelat($p['total_menit_terlambat'] ?? 0, $p);
            $total_denda_alfa   = ($p['total_alfa'] ?? 0) * $p['denda_alfa'];

            // Hitung Lembur (Tahap 5)
            $stmt_lembur = $this->db->prepare("SELECT COALESCE(SUM(durasi_jam), 0) FROM pengajuan_lembur WHERE id_user=:id_user AND MONTH(tanggal)=:bulan AND YEAR(tanggal)=:tahun AND status='approved'");
            $stmt_lembur->bindParam(':id_user', $p['id_user'], PDO::PARAM_INT);
            $stmt_lembur->bindParam(':bulan', $bulan, PDO::PARAM_INT);
            $stmt_lembur->bindParam(':tahun', $tahun, PDO::PARAM_INT);
            $stmt_lembur->execute();
            $jam_lembur = (float)$stmt_lembur->fetchColumn();
            $nilai_overtime = $jam_lembur * ($p['tarif_lembur_per_jam'] ?? 0);

            $gaji_pokok = $p['gaji_pokok'];
            $total_tunjangan_tetap = $p['tunj_jabatan'] + $p['tunj_transportasi'] + $p['tunj_makan'] + $p['tunj_kehadiran'] + $p['tunj_lainnya'];
            $basis_bpjs_jamsostek = $gaji_pokok + $total_tunjangan_tetap; // Overtime tidak termasuk basis BPJS

            // Tunjangan Perusahaan (Berdasarkan Basis Gaji+Tunjangan)
            $tunj_jht_37     = $basis_bpjs_jamsostek * 0.037;
            $tunj_jkk_024    = $basis_bpjs_jamsostek * 0.0024;
            $tunj_jk_03      = $basis_bpjs_jamsostek * 0.003;
            $tunj_jp_2       = $basis_bpjs_jamsostek * 0.02;
            
            // BPJS Kesehatan Basis Min (UMK) & Max (~12jt)
            $basis_bpjs_kes  = max($basis_bpjs_jamsostek, 5290000);
            if ($basis_bpjs_kes > 12000000) $basis_bpjs_kes = 12000000; // Cap limit BPJS Kes
            $tunj_bpjs_kes_4 = $basis_bpjs_kes * 0.04;
            $pot_bpjs_kes_1  = $basis_bpjs_kes * 0.01;

            // Potongan BPJS Pegawai
            $pot_jht_2       = $basis_bpjs_jamsostek * 0.02;
            $pot_jp_1        = $basis_bpjs_jamsostek * 0.01;

            // -------------------------------------------------------------
            // PPh 21 CALCULATION (Perhitungan Klasik PTKP)
            // -------------------------------------------------------------
            // 1. Pendapatan Bruto (Gaji Pokok + Tunjangan Tetap + OVERTIME + Premi Jamsostek dari Perusahaan)
            $pendapatan_bruto = $basis_bpjs_jamsostek + $nilai_overtime + $tunj_jkk_024 + $tunj_jk_03 + $tunj_bpjs_kes_4;

            // 2. Pengurang (Biaya Jabatan + Iuran Pensiun/JHT/JP yang dibayar pegawai)
            $biaya_jabatan = $pendapatan_bruto * 0.05;
            if ($biaya_jabatan > 500000) $biaya_jabatan = 500000;
            $total_pengurang = $biaya_jabatan + $pot_jht_2 + $pot_jp_1;

            // 3. Penghasilan Netto Sebulan & Setahun
            $netto_sebulan = $pendapatan_bruto - $total_pengurang;
            $netto_setahun = $netto_sebulan * 12;

            // 4. Hitung PTKP (Penghasilan Tidak Kena Pajak)
            $ptkp_map = [
                'TK/0' => 54000000, 'TK/1' => 58500000, 'TK/2' => 63000000, 'TK/3' => 67500000,
                'K/0'  => 58500000, 'K/1'  => 63000000, 'K/2'  => 67500000, 'K/3'  => 72000000
            ];
            $status_pajak = $p['status_pajak'] ?? 'TK/0';
            $ptkp_setahun = $ptkp_map[$status_pajak] ?? 54000000;

            // 5. PKP (Penghasilan Kena Pajak) Setahun
            $pkp_setahun = $netto_setahun - $ptkp_setahun;
            
            $pot_pph21 = 0;
            if ($pkp_setahun > 0) {
                // Pembulatan ke bawah ribuan terdekat
                $pkp_setahun = floor($pkp_setahun / 1000) * 1000;
                
                // Tarif Progresif Pasal 17
                $pph21_setahun = 0;
                if ($pkp_setahun <= 60000000) {
                    $pph21_setahun = $pkp_setahun * 0.05;
                } else if ($pkp_setahun <= 250000000) {
                    $pph21_setahun = (60000000 * 0.05) + (($pkp_setahun - 60000000) * 0.15);
                } else if ($pkp_setahun <= 500000000) {
                    $pph21_setahun = (60000000 * 0.05) + (190000000 * 0.15) + (($pkp_setahun - 250000000) * 0.25);
                } else {
                    $pph21_setahun = (60000000 * 0.05) + (190000000 * 0.15) + (250000000 * 0.25) + (($pkp_setahun - 500000000) * 0.30);
                }
                
                $pot_pph21 = $pph21_setahun / 12;
            }

            // Gaji bersih
            // Denda telat dan alfa tetap dipotong dari gaji yang ditransfer pusat
            $total_potongan_pegawai = $pot_jht_2 + $pot_jp_1 + $pot_bpjs_kes_1 + $pot_pph21 + $total_denda_telat + $total_denda_alfa;
            
            $gaji_bersih = $basis_bpjs_jamsostek + $nilai_overtime - $total_potongan_pegawai;

            $stmt = $this->db->prepare("
                INSERT INTO penggajian_bulanan
                (id_user, bulan, tahun, nilai_gaji_pokok, 
                 nilai_tunj_jabatan, nilai_tunj_transportasi, nilai_tunj_makan, nilai_tunj_kehadiran, nilai_tunj_lainnya, nilai_overtime,
                 tunj_jht_37, tunj_jkk_024, tunj_jk_03, tunj_bpjs_kes_4, tunj_jp_2,
                 pot_jht_2, pot_bpjs_kes_1, pot_jp_1, pot_pph21,
                 total_potongan_telat_alfa, denda_terlambat, denda_alfa, status_pajak_snapshot,
                 total_gaji_bersih, status)
                VALUES 
                (:id_user, :bulan, :tahun, :gaji_pokok, 
                 :tunj_jabatan, :tunj_transportasi, :tunj_makan, :tunj_kehadiran, :tunj_lainnya, :nilai_overtime,
                 :tunj_jht, :tunj_jkk, :tunj_jk, :tunj_bpjs, :tunj_jp,
                 :pot_jht, :pot_bpjs, :pot_jp, :pot_pph21,
                 :total_potongan_telat_alfa, :denda_terlambat, :denda_alfa, :status_pajak,
                 :bersih, 'draft')
            ");
            
            $total_potongan_telat_alfa = $total_denda_telat + $total_denda_alfa;
            
            $stmt->bindParam(':id_user',  $p['id_user'], PDO::PARAM_INT);
            $stmt->bindParam(':bulan',    $bulan, PDO::PARAM_INT);
            $stmt->bindParam(':tahun',    $tahun, PDO::PARAM_INT);
            $stmt->bindParam(':gaji_pokok', $gaji_pokok);
            $stmt->bindParam(':tunj_jabatan', $p['tunj_jabatan']);
            $stmt->bindParam(':tunj_transportasi', $p['tunj_transportasi']);
            $stmt->bindParam(':tunj_makan', $p['tunj_makan']);
            $stmt->bindParam(':tunj_kehadiran', $p['tunj_kehadiran']);
            $stmt->bindParam(':tunj_lainnya', $p['tunj_lainnya']);
            $stmt->bindParam(':nilai_overtime', $nilai_overtime);
            $stmt->bindParam(':tunj_jht', $tunj_jht_37);
            $stmt->bindParam(':tunj_jkk', $tunj_jkk_024);
            $stmt->bindParam(':tunj_jk', $tunj_jk_03);
            $stmt->bindParam(':tunj_bpjs', $tunj_bpjs_kes_4);
            $stmt->bindParam(':tunj_jp', $tunj_jp_2);
            $stmt->bindParam(':pot_jht', $pot_jht_2);
            $stmt->bindParam(':pot_bpjs', $pot_bpjs_kes_1);
            $stmt->bindParam(':pot_jp', $pot_jp_1);
            $stmt->bindParam(':pot_pph21', $pot_pph21);
            $stmt->bindParam(':total_potongan_telat_alfa', $total_potongan_telat_alfa);
            $stmt->bindParam(':denda_terlambat', $total_denda_telat);
            $stmt->bindParam(':denda_alfa', $total_denda_alfa);
            $stmt->bindParam(':status_pajak', $p['status_pajak']);
            $stmt->bindParam(':bersih',   $gaji_bersih);
            
            if ($stmt->execute()) $berhasil++;
        }

        return ['status' => true, 'message' => "Berhasil men-generate gaji untuk $berhasil pegawai.", 'total' => $berhasil];
    }

    // Mengambil data penggajian yang sudah di-generate
    public function getDataGaji($bulan, $tahun) {
        $stmt = $this->db->prepare("
            SELECT p.*, u.nip, u.nama_lengkap, u.jabatan, c.nama_cabang
            FROM penggajian_bulanan p
            JOIN users u ON p.id_user = u.id_user
            JOIN cabang c ON u.id_cabang = c.id_cabang
            WHERE p.bulan = :bulan AND p.tahun = :tahun
            ORDER BY c.nama_cabang, u.nama_lengkap
        ");
        $stmt->bindParam(':bulan', $bulan, PDO::PARAM_INT);
        $stmt->bindParam(':tahun', $tahun, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Publish semua gaji bulan ini
    public function publishGaji($bulan, $tahun) {
        // 1. Ambil data draft yang memiliki potongan denda
        $stmt_draft = $this->db->prepare("
            SELECT p.id_gaji, p.id_user, u.id_cabang, p.total_potongan_telat_alfa, u.nama_lengkap 
            FROM penggajian_bulanan p
            JOIN users u ON p.id_user = u.id_user
            WHERE p.bulan=:bulan AND p.tahun=:tahun AND p.status='draft' AND p.total_potongan_telat_alfa > 0
        ");
        $stmt_draft->bindParam(':bulan', $bulan, PDO::PARAM_INT);
        $stmt_draft->bindParam(':tahun', $tahun, PDO::PARAM_INT);
        $stmt_draft->execute();
        $drafts = $stmt_draft->fetchAll();

        // 2. Catat denda sebagai pemasukan di kas cabang
        $tanggal_publish = date('Y-m-d');
        foreach ($drafts as $d) {
            $ket = "Potongan absen/telat: " . $d['nama_lengkap'] . " (" . str_pad($bulan, 2, '0', STR_PAD_LEFT) . "/$tahun)";
            $stmt_kas = $this->db->prepare("
                INSERT INTO kas_denda_cabang (id_cabang, tanggal, jenis, nominal, keterangan, id_penggajian)
                VALUES (:id_cabang, :tanggal, 'pemasukan', :nominal, :keterangan, :id_gaji)
            ");
            $stmt_kas->bindParam(':id_cabang', $d['id_cabang'], PDO::PARAM_INT);
            $stmt_kas->bindParam(':tanggal', $tanggal_publish);
            $stmt_kas->bindParam(':nominal', $d['total_potongan_telat_alfa']);
            $stmt_kas->bindParam(':keterangan', $ket);
            $stmt_kas->bindParam(':id_gaji', $d['id_gaji'], PDO::PARAM_INT);
            $stmt_kas->execute();
        }

        // 3. Update status menjadi published
        $stmt = $this->db->prepare("UPDATE penggajian_bulanan SET status='published' WHERE bulan=:bulan AND tahun=:tahun AND status='draft'");
        $stmt->bindParam(':bulan', $bulan, PDO::PARAM_INT);
        $stmt->bindParam(':tahun', $tahun, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
