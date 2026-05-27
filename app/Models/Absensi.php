<?php

class Absensi {
    private $db;

    public function __construct() {
        $this->db = (new Database())->getConnection();
    }

    // Fungsi matematika (Haversine) untuk menghitung jarak antara 2 titik GPS dalam METER
    public function hitungJarak($lat1, $lon1, $lat2, $lon2) {
        $earth_radius = 6371000;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2) * sin($dLon/2);
        $c = 2 * asin(sqrt($a));
        return $earth_radius * $c;
    }

    // Mengambil profil cabang (latitude, longitude, radius, timezone)
    public function getInfoCabang($id_cabang) {
        $stmt = $this->db->prepare("SELECT latitude, longitude, radius_meter, timezone FROM cabang WHERE id_cabang = :id_cabang");
        $stmt->bindParam(':id_cabang', $id_cabang);
        $stmt->execute();
        return $stmt->fetch();
    }

    // Mengambil jadwal jam kerja hari ini berdasarkan nama hari (Senin, Selasa, dll)
    // Timezone cabang di-set terlebih dahulu sebelum memanggil fungsi ini
    public function getJamKerjaHariIni($id_cabang, $timezone = null) {
        // Set timezone cabang agar nama hari dan jam sesuai zona waktu lokal
        if ($timezone) date_default_timezone_set($timezone);

        $hari_map = [1=>'Senin', 2=>'Selasa', 3=>'Rabu', 4=>'Kamis', 5=>'Jumat', 6=>'Sabtu', 7=>'Minggu'];
        $hari_ini = $hari_map[date('N')];

        $stmt = $this->db->prepare("SELECT jam_masuk, jam_pulang FROM jam_kerja_cabang WHERE id_cabang = :id_cabang AND hari = :hari");
        $stmt->bindParam(':id_cabang', $id_cabang);
        $stmt->bindParam(':hari', $hari_ini);
        $stmt->execute();
        return $stmt->fetch();
    }

    // Mengambil status absensi pegawai hari ini
    public function getAbsensiHariIni($id_user) {
        $tanggal = date('Y-m-d');
        $stmt = $this->db->prepare("SELECT * FROM absensi WHERE id_user = :id_user AND tanggal = :tanggal LIMIT 1");
        $stmt->bindParam(':id_user', $id_user);
        $stmt->bindParam(':tanggal', $tanggal);
        $stmt->execute();
        return $stmt->fetch();
    }

    // Mengambil riwayat 5 hari absensi terakhir
    public function getRiwayat($id_user, $limit = 5) {
        $stmt = $this->db->prepare("SELECT tanggal, jam_masuk, jam_pulang, status, menit_terlambat FROM absensi WHERE id_user = :id_user ORDER BY tanggal DESC LIMIT :limit");
        $stmt->bindParam(':id_user', $id_user, PDO::PARAM_INT);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Menyimpan absen masuk dengan validasi jendela waktu (2 jam sebelum shift)
    public function simpanAbsenMasuk($id_user, $lat, $lng, $foto, $id_cabang, $timezone = 'Asia/Jakarta') {
        // Set timezone cabang agar jam tercatat sesuai zona waktu lokal
        date_default_timezone_set($timezone);

        $tanggal = date('Y-m-d');
        $jam_sekarang = date('H:i:s');

        // 1. Cek absen ganda
        $stmt_cek = $this->db->prepare("SELECT id_absensi FROM absensi WHERE id_user = :id_user AND tanggal = :tanggal");
        $stmt_cek->bindParam(':id_user', $id_user);
        $stmt_cek->bindParam(':tanggal', $tanggal);
        $stmt_cek->execute();
        if ($stmt_cek->rowCount() > 0) {
            return ['status' => false, 'message' => 'Anda sudah melakukan absen masuk hari ini.'];
        }

        // 2. Ambil jadwal jam kerja & validasi jendela waktu (2 jam sebelum shift)
        $jadwal = $this->getJamKerjaHariIni($id_cabang, $timezone);
        if (!$jadwal) {
            return ['status' => false, 'message' => 'Hari ini bukan hari kerja atau jadwal belum diatur.'];
        }
        $batas_awal = date('H:i:s', strtotime($jadwal['jam_masuk']) - (2 * 3600));
        if ($jam_sekarang < $batas_awal) {
            $jam_boleh = date('H:i', strtotime($jadwal['jam_masuk']) - (2 * 3600));
            $tz_label = $timezone === 'Asia/Makassar' ? 'WITA' : ($timezone === 'Asia/Jayapura' ? 'WIT' : 'WIB');
            return ['status' => false, 'message' => "Absen masuk belum bisa dilakukan. Sistem baru buka pukul $jam_boleh $tz_label."];
        }

        // 3. Hitung keterlambatan
        $menit_terlambat = 0;
        $status = 'hadir';
        if ($jam_sekarang > $jadwal['jam_masuk']) {
            $menit_terlambat = round((strtotime($jam_sekarang) - strtotime($jadwal['jam_masuk'])) / 60);
            $status = 'telat';
        }

        // 4. Simpan ke database
        $stmt = $this->db->prepare("INSERT INTO absensi (id_user, tanggal, jam_masuk, foto_masuk, lat_masuk, lng_masuk, status, menit_terlambat) VALUES (:id_user, :tanggal, :jam_masuk, :foto, :lat, :lng, :status, :menit)");
        $stmt->bindParam(':id_user', $id_user);
        $stmt->bindParam(':tanggal', $tanggal);
        $stmt->bindParam(':jam_masuk', $jam_sekarang);
        $stmt->bindParam(':foto', $foto);
        $stmt->bindParam(':lat', $lat);
        $stmt->bindParam(':lng', $lng);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':menit', $menit_terlambat);

        if ($stmt->execute()) {
            $pesan = $status === 'telat' ? "Absen masuk berhasil. Anda terlambat $menit_terlambat menit." : "Absen masuk berhasil! Selamat bekerja.";
            return ['status' => true, 'message' => $pesan];
        }
        return ['status' => false, 'message' => 'Terjadi kesalahan saat menyimpan absen.'];
    }

    // Menyimpan absen pulang
    public function simpanAbsenPulang($id_user, $lat, $lng, $foto, $timezone = 'Asia/Jakarta') {
        // Set timezone cabang agar jam tercatat sesuai zona waktu lokal
        date_default_timezone_set($timezone);

        $tanggal = date('Y-m-d');
        $jam_sekarang = date('H:i:s');

        // 1. Cek apakah sudah ada absen masuk hari ini
        $stmt_cek = $this->db->prepare("
            SELECT a.id_absensi, a.jam_pulang, u.id_cabang 
            FROM absensi a 
            JOIN users u ON a.id_user = u.id_user
            WHERE a.id_user = :id_user AND a.tanggal = :tanggal LIMIT 1
        ");
        $stmt_cek->bindParam(':id_user', $id_user);
        $stmt_cek->bindParam(':tanggal', $tanggal);
        $stmt_cek->execute();
        $absensi = $stmt_cek->fetch();

        if (!$absensi) {
            return ['status' => false, 'message' => 'Anda belum melakukan absen masuk hari ini!'];
        }
        if ($absensi['jam_pulang'] !== null) {
            return ['status' => false, 'message' => 'Anda sudah melakukan absen pulang hari ini.'];
        }

        // 2. Validasi jam pulang — cek apakah sudah melewati jam pulang sesuai jadwal
        $jadwal = $this->getJamKerjaHariIni($absensi['id_cabang'], $timezone);
        if ($jadwal && isset($jadwal['jam_pulang'])) {
            if ($jam_sekarang < $jadwal['jam_pulang']) {
                $tz_label = $timezone === 'Asia/Makassar' ? 'WITA' : ($timezone === 'Asia/Jayapura' ? 'WIT' : 'WIB');
                $jam_pulang_fmt = date('H:i', strtotime($jadwal['jam_pulang']));
                return [
                    'status'  => false,
                    'code'    => 'BELUM_JAM_PULANG',
                    'message' => "Belum bisa absen pulang. Jam pulang adalah pukul $jam_pulang_fmt $tz_label."
                ];
            }
        }

        // 3. Update record absensi dengan data pulang
        $stmt = $this->db->prepare("UPDATE absensi SET jam_pulang = :jam_pulang, foto_pulang = :foto, lat_pulang = :lat, lng_pulang = :lng WHERE id_absensi = :id_absensi");
        $stmt->bindParam(':jam_pulang', $jam_sekarang);
        $stmt->bindParam(':foto', $foto);
        $stmt->bindParam(':lat', $lat);
        $stmt->bindParam(':lng', $lng);
        $stmt->bindParam(':id_absensi', $absensi['id_absensi']);

        if ($stmt->execute()) {
            return ['status' => true, 'message' => 'Absen pulang berhasil dicatat. Sampai jumpa besok!'];
        }
        return ['status' => false, 'message' => 'Terjadi kesalahan saat menyimpan absen pulang.'];
    }

    // === STATISTIK DASHBOARD ===

    // Statistik absensi hari ini untuk semua cabang (Superadmin)
    public function getStatistikHariIni() {
        $tanggal = date('Y-m-d');
        $stmt = $this->db->prepare("
            SELECT
                SUM(CASE WHEN a.id_absensi IS NOT NULL AND a.status = 'hadir' THEN 1 ELSE 0 END) AS total_hadir,
                SUM(CASE WHEN a.status = 'telat' THEN 1 ELSE 0 END) AS total_telat,
                SUM(CASE WHEN a.id_absensi IS NULL THEN 1 ELSE 0 END) AS total_belum_absen,
                COUNT(u.id_user) AS total_pegawai
            FROM users u
            LEFT JOIN absensi a ON a.id_user = u.id_user AND a.tanggal = :tanggal
            WHERE u.role = 'pegawai' AND u.is_active = 1
        ");
        $stmt->bindParam(':tanggal', $tanggal);
        $stmt->execute();
        return $stmt->fetch();
    }

    // Statistik absensi hari ini untuk satu cabang (Admin Cabang)
    public function getStatistikHariIniCabang($id_cabang) {
        $tanggal = date('Y-m-d');
        $stmt = $this->db->prepare("
            SELECT
                SUM(CASE WHEN a.id_absensi IS NOT NULL AND a.status = 'hadir' THEN 1 ELSE 0 END) AS total_hadir,
                SUM(CASE WHEN a.status = 'telat' THEN 1 ELSE 0 END) AS total_telat,
                SUM(CASE WHEN a.id_absensi IS NULL THEN 1 ELSE 0 END) AS total_belum_absen,
                COUNT(u.id_user) AS total_pegawai
            FROM users u
            LEFT JOIN absensi a ON a.id_user = u.id_user AND a.tanggal = :tanggal
            WHERE u.role = 'pegawai' AND u.is_active = 1 AND u.id_cabang = :id_cabang
        ");
        $stmt->bindParam(':tanggal', $tanggal);
        $stmt->bindParam(':id_cabang', $id_cabang, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    // Tren 7 hari terakhir (global)
    public function getTren7Hari() {
        $stmt = $this->db->query("
            SELECT
                a.tanggal,
                SUM(CASE WHEN a.status IN ('hadir','telat') THEN 1 ELSE 0 END) AS hadir,
                SUM(CASE WHEN a.status = 'telat' THEN 1 ELSE 0 END) AS telat
            FROM absensi a
            WHERE a.tanggal >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
            GROUP BY a.tanggal
            ORDER BY a.tanggal ASC
        ");
        return $stmt->fetchAll();
    }

    // Tren 7 hari terakhir (per cabang)
    public function getTren7HariCabang($id_cabang) {
        $stmt = $this->db->prepare("
            SELECT
                a.tanggal,
                SUM(CASE WHEN a.status IN ('hadir','telat') THEN 1 ELSE 0 END) AS hadir,
                SUM(CASE WHEN a.status = 'telat' THEN 1 ELSE 0 END) AS telat
            FROM absensi a
            JOIN users u ON a.id_user = u.id_user
            WHERE u.id_cabang = :id_cabang
              AND a.tanggal >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
            GROUP BY a.tanggal
            ORDER BY a.tanggal ASC
        ");
        $stmt->bindParam(':id_cabang', $id_cabang, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Daftar pegawai yang belum absen hari ini (per cabang)
    public function getPegawaiBelumAbsenCabang($id_cabang) {
        $tanggal = date('Y-m-d');
        $stmt = $this->db->prepare("
            SELECT u.nama_lengkap, u.jabatan, u.nip
            FROM users u
            LEFT JOIN absensi a ON a.id_user = u.id_user AND a.tanggal = :tanggal
            WHERE u.role = 'pegawai' AND u.is_active = 1
              AND u.id_cabang = :id_cabang
              AND a.id_absensi IS NULL
            ORDER BY u.nama_lengkap ASC
        ");
        $stmt->bindParam(':tanggal', $tanggal);
        $stmt->bindParam(':id_cabang', $id_cabang, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Mendapatkan rentang tanggal untuk cutoff (26 bulan lalu s/d 25 bulan ini)
    public function getRentangWaktuBuku($bulan, $tahun) {
        $prev_bulan = $bulan - 1;
        $prev_tahun = $tahun;
        if ($prev_bulan == 0) {
            $prev_bulan = 12;
            $prev_tahun -= 1;
        }
        $start_date = sprintf("%04d-%02d-26", $prev_tahun, $prev_bulan);
        $end_date = sprintf("%04d-%02d-25", $tahun, $bulan);
        return ['start' => $start_date, 'end' => $end_date];
    }

    // Detail absensi harian per pegawai dalam satu bulan (mengikuti cutoff)
    public function getDetailHarianUser($id_user, $bulan, $tahun) {
        $rentang = $this->getRentangWaktuBuku($bulan, $tahun);
        $stmt = $this->db->prepare("
            SELECT a.*
            FROM absensi a
            WHERE a.id_user = :id_user
              AND DATE(a.tanggal) BETWEEN :start_date AND :end_date
            ORDER BY a.tanggal ASC
        ");
        $stmt->bindParam(':id_user', $id_user, PDO::PARAM_INT);
        $stmt->bindParam(':start_date', $rentang['start']);
        $stmt->bindParam(':end_date', $rentang['end']);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    // Mengambil jadwal jam kerja spesifik untuk tanggal tertentu
    public function getJamKerjaByTanggal($id_cabang, $tanggal) {
        $hari_map = [1=>'Senin', 2=>'Selasa', 3=>'Rabu', 4=>'Kamis', 5=>'Jumat', 6=>'Sabtu', 7=>'Minggu'];
        $hari = $hari_map[date('N', strtotime($tanggal))];
        $stmt = $this->db->prepare("SELECT jam_masuk, jam_pulang FROM jam_kerja_cabang WHERE id_cabang = :id_cabang AND hari = :hari");
        $stmt->bindParam(':id_cabang', $id_cabang);
        $stmt->bindParam(':hari', $hari);
        $stmt->execute();
        return $stmt->fetch();
    }

    // Edit data absensi (Superadmin)
    public function editAbsensi($data) {
        // Ambil info absensi
        $stmt_info = $this->db->prepare("SELECT a.tanggal, u.id_cabang FROM absensi a JOIN users u ON a.id_user = u.id_user WHERE a.id_absensi = :id");
        $stmt_info->bindParam(':id', $data['id_absensi']);
        $stmt_info->execute();
        $info = $stmt_info->fetch();

        // Jika status = alfa, hapus data jam
        if ($data['status'] === 'alfa') {
            $stmt = $this->db->prepare("
                UPDATE absensi 
                SET jam_masuk = NULL, jam_pulang = NULL, status = 'alfa', menit_terlambat = 0
                WHERE id_absensi = :id
            ");
            $stmt->bindParam(':id', $data['id_absensi'], PDO::PARAM_INT);
        } else {
            $menit_terlambat = 0;
            if ($data['status'] === 'telat' && $info) {
                $jadwal = $this->getJamKerjaByTanggal($info['id_cabang'], $info['tanggal']);
                if ($jadwal && isset($jadwal['jam_masuk']) && $data['jam_masuk'] > $jadwal['jam_masuk']) {
                    $menit_terlambat = round((strtotime($data['jam_masuk']) - strtotime($jadwal['jam_masuk'])) / 60);
                } else {
                    // Jika jam masuk <= jadwal, berarti tidak telat
                    $data['status'] = 'hadir';
                }
            }

            $jam_masuk = empty($data['jam_masuk']) ? null : $data['jam_masuk'];
            $jam_pulang = empty($data['jam_pulang']) ? null : $data['jam_pulang'];

            $foto_update = "";
            if (!empty($data['foto_masuk_baru'])) { $foto_update .= ", foto_masuk = :foto_masuk"; }
            if (!empty($data['foto_pulang_baru'])) { $foto_update .= ", foto_pulang = :foto_pulang"; }
            if (!empty($data['lat_masuk_baru']) && !empty($data['lng_masuk_baru'])) {
                $foto_update .= ", lat_masuk = :lat_masuk, lng_masuk = :lng_masuk";
            }
            if (!empty($data['lat_pulang_baru']) && !empty($data['lng_pulang_baru'])) {
                $foto_update .= ", lat_pulang = :lat_pulang, lng_pulang = :lng_pulang";
            }

            $stmt = $this->db->prepare("
                UPDATE absensi 
                SET jam_masuk = :jam_masuk, jam_pulang = :jam_pulang, status = :status, menit_terlambat = :menit $foto_update
                WHERE id_absensi = :id
            ");
            $stmt->bindParam(':jam_masuk', $jam_masuk);
            $stmt->bindParam(':jam_pulang', $jam_pulang);
            $stmt->bindParam(':status', $data['status']);
            $stmt->bindParam(':menit', $menit_terlambat);
            if (!empty($data['foto_masuk_baru'])) { $stmt->bindParam(':foto_masuk', $data['foto_masuk_baru']); }
            if (!empty($data['foto_pulang_baru'])) { $stmt->bindParam(':foto_pulang', $data['foto_pulang_baru']); }
            
            if (!empty($data['lat_masuk_baru']) && !empty($data['lng_masuk_baru'])) {
                $stmt->bindParam(':lat_masuk', $data['lat_masuk_baru']);
                $stmt->bindParam(':lng_masuk', $data['lng_masuk_baru']);
            }
            if (!empty($data['lat_pulang_baru']) && !empty($data['lng_pulang_baru'])) {
                $stmt->bindParam(':lat_pulang', $data['lat_pulang_baru']);
                $stmt->bindParam(':lng_pulang', $data['lng_pulang_baru']);
            }
            
            $stmt->bindParam(':id', $data['id_absensi'], PDO::PARAM_INT);
        }
        return $stmt->execute();
    }
}
