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

    // Hitung denda keterlambatan berdasarkan menit terlambat SATU KEJADIAN
    // Jika > 60 menit, denda tetap di tarif 31-60 menit (tidak terakumulasi lebih)
    public function hitungDendaTelat($menit_terlambat, $cabang) {
        if ($menit_terlambat >= 1  && $menit_terlambat <= 5)  return $cabang['denda_1_5'];
        if ($menit_terlambat <= 10) return $cabang['denda_6_10'];
        if ($menit_terlambat <= 15) return $cabang['denda_11_15'];
        if ($menit_terlambat <= 30) return $cabang['denda_16_30'];
        if ($menit_terlambat <= 60) return $cabang['denda_31_60'];
        // > 60 menit: CAP di denda_31_60 (tidak terakumulasi lebih tinggi)
        return $cabang['denda_31_60'];
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

            // Hitung denda telat PER KEJADIAN (bukan total akumulasi menit)
            // Ambil semua baris absensi bertatus 'telat' bulan ini untuk pegawai ini
            $stmt_telat = $this->db->prepare("
                SELECT menit_terlambat FROM absensi 
                WHERE id_user = :id_user 
                  AND status = 'telat' 
                  AND MONTH(tanggal) = :bulan 
                  AND YEAR(tanggal) = :tahun
            ");
            $stmt_telat->bindParam(':id_user', $p['id_user'], PDO::PARAM_INT);
            $stmt_telat->bindParam(':bulan', $bulan, PDO::PARAM_INT);
            $stmt_telat->bindParam(':tahun', $tahun, PDO::PARAM_INT);
            $stmt_telat->execute();
            $rows_telat = $stmt_telat->fetchAll(PDO::FETCH_ASSOC);

            // Jumlahkan denda per kejadian — setiap keterlambatan dicap di tier 31-60 menit
            $total_denda_telat = 0;
            foreach ($rows_telat as $row) {
                $total_denda_telat += $this->hitungDendaTelat($row['menit_terlambat'], $p);
            }

            $total_denda_alfa = ($p['total_alfa'] ?? 0) * $p['denda_alfa'];

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
            // PPh 21 CALCULATION (Skema TER PP 58 Tahun 2023)
            // -------------------------------------------------------------
            // 1. Penghasilan Bruto (Gaji Pokok + Semua Tunjangan + BPJS Kes & Jamsostek yang dibayar perusahaan kecuali JHT & JP)
            $pendapatan_bruto = $basis_bpjs_jamsostek + $nilai_overtime + $tunj_jkk_024 + $tunj_jk_03 + $tunj_bpjs_kes_4;

            $status_pajak = $p['status_pajak'] ?? 'TK/0';
            $pot_pph21 = 0;

            if ($bulan >= 1 && $bulan <= 11) {
                // 2. Kategori TER
                $kategori_ter = 'A';
                if (in_array($status_pajak, ['TK/2', 'TK/3', 'K/1', 'K/2'])) {
                    $kategori_ter = 'B';
                } elseif (in_array($status_pajak, ['K/3'])) {
                    $kategori_ter = 'C';
                }

                // 3 & 4. Persentase Pajak dan Potongan
                $persentase_ter = $this->getTarifTER($kategori_ter, $pendapatan_bruto);
                $pot_pph21 = $pendapatan_bruto * ($persentase_ter / 100);
            } else {
                // DESEMBER: Perhitungan Normal Pasal 17 Disetahunkan
                $biaya_jabatan = $pendapatan_bruto * 0.05;
                if ($biaya_jabatan > 500000) $biaya_jabatan = 500000;
                $total_pengurang = $biaya_jabatan + $pot_jht_2 + $pot_jp_1;

                $netto_sebulan = $pendapatan_bruto - $total_pengurang;
                $netto_setahun = $netto_sebulan * 12;

                $ptkp_map = [
                    'TK/0' => 54000000, 'TK/1' => 58500000, 'TK/2' => 63000000, 'TK/3' => 67500000,
                    'K/0'  => 58500000, 'K/1'  => 63000000, 'K/2'  => 67500000, 'K/3'  => 72000000
                ];
                $ptkp_setahun = $ptkp_map[$status_pajak] ?? 54000000;

                $pkp_setahun = $netto_setahun - $ptkp_setahun;
                $pph21_setahun = 0;

                if ($pkp_setahun > 0) {
                    $pkp_setahun = floor($pkp_setahun / 1000) * 1000;
                    if ($pkp_setahun <= 60000000) {
                        $pph21_setahun = $pkp_setahun * 0.05;
                    } else if ($pkp_setahun <= 250000000) {
                        $pph21_setahun = (60000000 * 0.05) + (($pkp_setahun - 60000000) * 0.15);
                    } else if ($pkp_setahun <= 500000000) {
                        $pph21_setahun = (60000000 * 0.05) + (190000000 * 0.15) + (($pkp_setahun - 250000000) * 0.25);
                    } else {
                        $pph21_setahun = (60000000 * 0.05) + (190000000 * 0.15) + (250000000 * 0.25) + (($pkp_setahun - 500000000) * 0.30);
                    }
                }

                // Kurangi dengan pajak yang sudah dibayar Jan-Nov
                $stmt_terbayar = $this->db->prepare("SELECT COALESCE(SUM(pot_pph21), 0) AS total_dibayar, COUNT(id_gaji) AS jml_bulan FROM penggajian_bulanan WHERE id_user = :id_user AND tahun = :tahun AND bulan < 12");
                $stmt_terbayar->execute([':id_user' => $p['id_user'], ':tahun' => $tahun]);
                $row_pajak = $stmt_terbayar->fetch(PDO::FETCH_ASSOC);
                $pajak_sudah_dibayar = (float)$row_pajak['total_dibayar'];
                $bulan_terbayar = (int)$row_pajak['jml_bulan'];

                if ($bulan_terbayar > 0) {
                    $pot_pph21 = $pph21_setahun - $pajak_sudah_dibayar;
                } else {
                    $pot_pph21 = $pph21_setahun / 12; // Fallback jika tidak ada histori sama sekali di sistem
                }
                
                if ($pot_pph21 < 0) $pot_pph21 = 0;
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

    // Tabel Tarif Efektif Rata-Rata (TER) PPh 21 PP No. 58 Tahun 2023
    private function getTarifTER($kategori, $bruto) {
        $tarif = 0;
        if ($kategori === 'A') {
            if ($bruto <= 5400000) $tarif = 0;
            elseif ($bruto <= 5650000) $tarif = 0.25;
            elseif ($bruto <= 5950000) $tarif = 0.5;
            elseif ($bruto <= 6300000) $tarif = 0.75;
            elseif ($bruto <= 6750000) $tarif = 1;
            elseif ($bruto <= 7500000) $tarif = 1.25;
            elseif ($bruto <= 8550000) $tarif = 1.5;
            elseif ($bruto <= 9650000) $tarif = 1.75;
            elseif ($bruto <= 10050000) $tarif = 2;
            elseif ($bruto <= 10350000) $tarif = 2.25;
            elseif ($bruto <= 10700000) $tarif = 2.5;
            elseif ($bruto <= 11050000) $tarif = 3;
            elseif ($bruto <= 11600000) $tarif = 4;
            elseif ($bruto <= 12500000) $tarif = 5;
            elseif ($bruto <= 13750000) $tarif = 6;
            elseif ($bruto <= 15100000) $tarif = 7;
            elseif ($bruto <= 16950000) $tarif = 8;
            elseif ($bruto <= 19750000) $tarif = 9;
            elseif ($bruto <= 24100000) $tarif = 10;
            elseif ($bruto <= 26450000) $tarif = 11;
            elseif ($bruto <= 28000000) $tarif = 12;
            elseif ($bruto <= 30050000) $tarif = 13;
            elseif ($bruto <= 32400000) $tarif = 14;
            elseif ($bruto <= 35400000) $tarif = 15;
            elseif ($bruto <= 39100000) $tarif = 16;
            elseif ($bruto <= 43850000) $tarif = 17;
            elseif ($bruto <= 47800000) $tarif = 18;
            elseif ($bruto <= 51400000) $tarif = 19;
            elseif ($bruto <= 56300000) $tarif = 20;
            elseif ($bruto <= 62200000) $tarif = 21;
            elseif ($bruto <= 68600000) $tarif = 22;
            elseif ($bruto <= 77500000) $tarif = 23;
            elseif ($bruto <= 89000000) $tarif = 24;
            elseif ($bruto <= 103000000) $tarif = 25;
            elseif ($bruto <= 125000000) $tarif = 26;
            elseif ($bruto <= 157000000) $tarif = 27;
            elseif ($bruto <= 206000000) $tarif = 28;
            elseif ($bruto <= 337000000) $tarif = 29;
            elseif ($bruto <= 454000000) $tarif = 30;
            elseif ($bruto <= 550000000) $tarif = 31;
            elseif ($bruto <= 695000000) $tarif = 32;
            elseif ($bruto <= 910000000) $tarif = 33;
            else $tarif = 34;
        } elseif ($kategori === 'B') {
            if ($bruto <= 6200000) $tarif = 0;
            elseif ($bruto <= 6500000) $tarif = 0.25;
            elseif ($bruto <= 6850000) $tarif = 0.5;
            elseif ($bruto <= 7300000) $tarif = 0.75;
            elseif ($bruto <= 7800000) $tarif = 1;
            elseif ($bruto <= 8850000) $tarif = 1.25;
            elseif ($bruto <= 9800000) $tarif = 1.5;
            elseif ($bruto <= 10500000) $tarif = 1.75;
            elseif ($bruto <= 10900000) $tarif = 2;
            elseif ($bruto <= 11200000) $tarif = 2.25;
            elseif ($bruto <= 11600000) $tarif = 2.5;
            elseif ($bruto <= 12050000) $tarif = 3;
            elseif ($bruto <= 12650000) $tarif = 4;
            elseif ($bruto <= 13600000) $tarif = 5;
            elseif ($bruto <= 14950000) $tarif = 6;
            elseif ($bruto <= 16400000) $tarif = 7;
            elseif ($bruto <= 18450000) $tarif = 8;
            elseif ($bruto <= 21850000) $tarif = 9;
            elseif ($bruto <= 26000000) $tarif = 10;
            elseif ($bruto <= 27700000) $tarif = 11;
            elseif ($bruto <= 29350000) $tarif = 12;
            elseif ($bruto <= 31450000) $tarif = 13;
            elseif ($bruto <= 33950000) $tarif = 14;
            elseif ($bruto <= 37100000) $tarif = 15;
            elseif ($bruto <= 41100000) $tarif = 16;
            elseif ($bruto <= 45800000) $tarif = 17;
            elseif ($bruto <= 49500000) $tarif = 18;
            elseif ($bruto <= 53800000) $tarif = 19;
            elseif ($bruto <= 58500000) $tarif = 20;
            elseif ($bruto <= 64000000) $tarif = 21;
            elseif ($bruto <= 71000000) $tarif = 22;
            elseif ($bruto <= 80000000) $tarif = 23;
            elseif ($bruto <= 93000000) $tarif = 24;
            elseif ($bruto <= 109000000) $tarif = 25;
            elseif ($bruto <= 132000000) $tarif = 26;
            elseif ($bruto <= 165000000) $tarif = 27;
            elseif ($bruto <= 218000000) $tarif = 28;
            elseif ($bruto <= 339000000) $tarif = 29;
            elseif ($bruto <= 455000000) $tarif = 30;
            elseif ($bruto <= 551000000) $tarif = 31;
            elseif ($bruto <= 704000000) $tarif = 32;
            elseif ($bruto <= 928000000) $tarif = 33;
            else $tarif = 34;
        } elseif ($kategori === 'C') {
            if ($bruto <= 6600000) $tarif = 0;
            elseif ($bruto <= 6950000) $tarif = 0.25;
            elseif ($bruto <= 7350000) $tarif = 0.5;
            elseif ($bruto <= 7800000) $tarif = 0.75;
            elseif ($bruto <= 8300000) $tarif = 1;
            elseif ($bruto <= 9400000) $tarif = 1.25;
            elseif ($bruto <= 10300000) $tarif = 1.5;
            elseif ($bruto <= 10900000) $tarif = 1.75;
            elseif ($bruto <= 11300000) $tarif = 2;
            elseif ($bruto <= 11600000) $tarif = 2.25;
            elseif ($bruto <= 12000000) $tarif = 2.5;
            elseif ($bruto <= 12600000) $tarif = 3;
            elseif ($bruto <= 13150000) $tarif = 4;
            elseif ($bruto <= 14100000) $tarif = 5;
            elseif ($bruto <= 15550000) $tarif = 6;
            elseif ($bruto <= 17050000) $tarif = 7;
            elseif ($bruto <= 19500000) $tarif = 8;
            elseif ($bruto <= 22700000) $tarif = 9;
            elseif ($bruto <= 26600000) $tarif = 10;
            elseif ($bruto <= 28100000) $tarif = 11;
            elseif ($bruto <= 30100000) $tarif = 12;
            elseif ($bruto <= 32600000) $tarif = 13;
            elseif ($bruto <= 35400000) $tarif = 14;
            elseif ($bruto <= 38900000) $tarif = 15;
            elseif ($bruto <= 43000000) $tarif = 16;
            elseif ($bruto <= 47400000) $tarif = 17;
            elseif ($bruto <= 51200000) $tarif = 18;
            elseif ($bruto <= 55800000) $tarif = 19;
            elseif ($bruto <= 60400000) $tarif = 20;
            elseif ($bruto <= 66700000) $tarif = 21;
            elseif ($bruto <= 74500000) $tarif = 22;
            elseif ($bruto <= 83200000) $tarif = 23;
            elseif ($bruto <= 95600000) $tarif = 24;
            elseif ($bruto <= 113000000) $tarif = 25;
            elseif ($bruto <= 136000000) $tarif = 26;
            elseif ($bruto <= 173000000) $tarif = 27;
            elseif ($bruto <= 221000000) $tarif = 28;
            elseif ($bruto <= 340000000) $tarif = 29;
            elseif ($bruto <= 456000000) $tarif = 30;
            elseif ($bruto <= 551000000) $tarif = 31;
            elseif ($bruto <= 706000000) $tarif = 32;
            elseif ($bruto <= 930000000) $tarif = 33;
            else $tarif = 34;
        }

        return $tarif;
    }
}
