<?php

class User {
    private $db;

    public function __construct() {
        $this->db = (new Database())->getConnection();
    }

    // Mengambil semua pegawai beserta nama cabangnya
    public function getAllPegawai() {
        $stmt = $this->db->prepare("
            SELECT u.*, c.nama_cabang
            FROM users u
            JOIN cabang c ON u.id_cabang = c.id_cabang
            ORDER BY u.is_active DESC, u.nama_lengkap ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Mengambil data satu pegawai berdasarkan ID
    public function getPegawaiById($id_user) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id_user = :id");
        $stmt->bindParam(':id', $id_user, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    // Menambahkan pegawai baru
    public function insertPegawai($data) {
        // Cek NIP duplikat
        $stmt_cek = $this->db->prepare("SELECT id_user FROM users WHERE nip = :nip");
        $stmt_cek->bindParam(':nip', $data['nip']);
        $stmt_cek->execute();
        if ($stmt_cek->rowCount() > 0) {
            return ['status' => false, 'message' => 'NIP sudah terdaftar dalam sistem.'];
        }

        $hash = password_hash($data['password'], PASSWORD_BCRYPT);
        $stmt = $this->db->prepare("
            INSERT INTO users (id_cabang, nip, nama_lengkap, jabatan, password, role, tipe_lembur, gaji_pokok, status_pajak, saldo_awal_pph21, tunj_jabatan, tunj_transportasi, tunj_makan, tunj_kehadiran, tunj_lainnya)
            VALUES (:id_cabang, :nip, :nama, :jabatan, :password, :role, :tipe_lembur, :gaji, :status_pajak, :saldo_awal_pph21, :tunj_jab, :tunj_trans, :tunj_mak, :tunj_hadir, :tunj_lain)
        ");
        $stmt->bindParam(':id_cabang', $data['id_cabang']);
        $stmt->bindParam(':nip',       $data['nip']);
        $stmt->bindParam(':nama',      $data['nama_lengkap']);
        $stmt->bindParam(':jabatan',   $data['jabatan']);
        $stmt->bindParam(':password',  $hash);
        $stmt->bindParam(':role',      $data['role']);
        $stmt->bindParam(':tipe_lembur', $data['tipe_lembur']);
        $stmt->bindParam(':gaji',      $data['gaji_pokok']);
        
        $stmt->bindParam(':status_pajak', $data['status_pajak']);
        $stmt->bindParam(':saldo_awal_pph21', $data['saldo_awal_pph21']);
        $stmt->bindParam(':tunj_jab',     $data['tunj_jabatan']);
        $stmt->bindParam(':tunj_trans',   $data['tunj_transportasi']);
        $stmt->bindParam(':tunj_mak',     $data['tunj_makan']);
        $stmt->bindParam(':tunj_hadir',   $data['tunj_kehadiran']);
        $stmt->bindParam(':tunj_lain',    $data['tunj_lainnya']);

        if ($stmt->execute()) {
            return ['status' => true, 'message' => 'Pegawai berhasil ditambahkan.'];
        }
        return ['status' => false, 'message' => 'Gagal menyimpan data pegawai.'];
    }

    // Mengupdate data pegawai
    public function updatePegawai($data) {
        // Jika password diisi, update sekalian. Jika tidak, jangan ubah password.
        if (!empty($data['password'])) {
            $hash = password_hash($data['password'], PASSWORD_BCRYPT);
            $stmt = $this->db->prepare("
                UPDATE users SET id_cabang=:id_cabang, nip=:nip, nama_lengkap=:nama, jabatan=:jabatan,
                password=:password, role=:role, tipe_lembur=:tipe_lembur, gaji_pokok=:gaji, status_pajak=:status_pajak, saldo_awal_pph21=:saldo_awal_pph21,
                tunj_jabatan=:tunj_jab, tunj_transportasi=:tunj_trans, tunj_makan=:tunj_mak, 
                tunj_kehadiran=:tunj_hadir, tunj_lainnya=:tunj_lain 
                WHERE id_user=:id
            ");
            $stmt->bindParam(':password', $hash);
        } else {
            $stmt = $this->db->prepare("
                UPDATE users SET id_cabang=:id_cabang, nip=:nip, nama_lengkap=:nama, jabatan=:jabatan,
                role=:role, tipe_lembur=:tipe_lembur, gaji_pokok=:gaji, status_pajak=:status_pajak, saldo_awal_pph21=:saldo_awal_pph21,
                tunj_jabatan=:tunj_jab, tunj_transportasi=:tunj_trans, tunj_makan=:tunj_mak, 
                tunj_kehadiran=:tunj_hadir, tunj_lainnya=:tunj_lain 
                WHERE id_user=:id
            ");
        }
        $stmt->bindParam(':id_cabang', $data['id_cabang']);
        $stmt->bindParam(':nip',       $data['nip']);
        $stmt->bindParam(':nama',      $data['nama_lengkap']);
        $stmt->bindParam(':jabatan',   $data['jabatan']);
        $stmt->bindParam(':role',      $data['role']);
        $stmt->bindParam(':tipe_lembur', $data['tipe_lembur']);
        $stmt->bindParam(':gaji',      $data['gaji_pokok']);
        
        $stmt->bindParam(':status_pajak', $data['status_pajak']);
        $stmt->bindParam(':saldo_awal_pph21', $data['saldo_awal_pph21']);
        $stmt->bindParam(':tunj_jab',     $data['tunj_jabatan']);
        $stmt->bindParam(':tunj_trans',   $data['tunj_transportasi']);
        $stmt->bindParam(':tunj_mak',     $data['tunj_makan']);
        $stmt->bindParam(':tunj_hadir',   $data['tunj_kehadiran']);
        $stmt->bindParam(':tunj_lain',    $data['tunj_lainnya']);
        
        $stmt->bindParam(':id',        $data['id_user'], PDO::PARAM_INT);

        if ($stmt->execute()) {
            return ['status' => true, 'message' => 'Data pegawai berhasil diperbarui.'];
        }
        return ['status' => false, 'message' => 'Gagal memperbarui data pegawai.'];
    }

    // Toggle status aktif/nonaktif pegawai
    public function toggleStatusPegawai($id_user) {
        $stmt = $this->db->prepare("UPDATE users SET is_active = NOT is_active WHERE id_user = :id");
        $stmt->bindParam(':id', $id_user, PDO::PARAM_INT);
        return $stmt->execute();
    }

    // Hapus permanen pegawai beserta foto-fotonya
    public function hapusPegawai($id_user) {
        $dir_uploads = PUBLIC_PATH . '/uploads/';

        // 1. Lacak dan Hapus File Foto Absensi
        $stmt_abs = $this->db->prepare("SELECT foto_masuk, foto_pulang FROM absensi WHERE id_user = :id");
        $stmt_abs->bindParam(':id', $id_user, PDO::PARAM_INT);
        $stmt_abs->execute();
        $absensi = $stmt_abs->fetchAll();
        foreach ($absensi as $a) {
            if (!empty($a['foto_masuk']) && file_exists($dir_uploads . $a['foto_masuk'])) {
                @unlink($dir_uploads . $a['foto_masuk']);
            }
            if (!empty($a['foto_pulang']) && file_exists($dir_uploads . $a['foto_pulang'])) {
                @unlink($dir_uploads . $a['foto_pulang']);
            }
        }

        // 2. Lacak dan Hapus Bukti Foto Cuti
        $stmt_cuti = $this->db->prepare("SELECT bukti_foto FROM pengajuan_cuti WHERE id_user = :id");
        $stmt_cuti->bindParam(':id', $id_user, PDO::PARAM_INT);
        $stmt_cuti->execute();
        $cuti = $stmt_cuti->fetchAll();
        foreach ($cuti as $c) {
            if (!empty($c['bukti_foto']) && file_exists($dir_uploads . $c['bukti_foto'])) {
                @unlink($dir_uploads . $c['bukti_foto']);
            }
        }

        // 3. Eksekusi Hapus dari Database (Asumsi jika belum ON DELETE CASCADE)
        $tables = ['absensi', 'pengajuan_cuti', 'pengajuan_lembur', 'penggajian_bulanan', 'distribusi_insentif'];
        foreach ($tables as $tbl) {
            try {
                $this->db->prepare("DELETE FROM $tbl WHERE id_user = :id")->execute([':id' => $id_user]);
            } catch (Exception $e) {}
        }

        $stmt = $this->db->prepare("DELETE FROM users WHERE id_user = :id");
        $stmt->bindParam(':id', $id_user, PDO::PARAM_INT);
        return $stmt->execute();
    }

    // Fungsi verifikasi login dengan Rate Limiting
    public function login($nip, $password, $ip_address) {
        // 1. Cek Rate Limiting (Mencegah Brute Force)
        // Maksimal 5x gagal dalam 15 menit
        if ($this->isIpBlocked($ip_address)) {
            return ['status' => false, 'message' => 'Terlalu banyak percobaan gagal. Silakan coba lagi dalam 15 menit.'];
        }

        // 2. Ambil data user berdasarkan NIP
        $stmt = $this->db->prepare("SELECT * FROM users WHERE nip = :nip AND is_active = 1");
        $stmt->bindParam(':nip', $nip);
        $stmt->execute();
        $user = $stmt->fetch();

        // 3. Verifikasi Password jika user ditemukan
        if ($user && password_verify($password, $user['password'])) {
            // Catat login sukses
            $this->recordLoginAttempt($ip_address, true);
            
            // Kembalikan data user tanpa hash password
            unset($user['password']);
            return ['status' => true, 'data' => $user];
        } else {
            // Catat login gagal
            $this->recordLoginAttempt($ip_address, false);
            return ['status' => false, 'message' => 'NIP atau Password salah!'];
        }
    }

    // Mengecek apakah IP diblokir karena > 5x gagal dalam 15 menit terakhir
    private function isIpBlocked($ip_address) {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as failed_attempts 
            FROM login_attempts 
            WHERE ip_address = :ip 
            AND is_success = 0 
            AND waktu > (NOW() - INTERVAL 15 MINUTE)
        ");
        $stmt->bindParam(':ip', $ip_address);
        $stmt->execute();
        $result = $stmt->fetch();
        
        return $result['failed_attempts'] >= 5;
    }

    // Mencatat aktivitas percobaan login
    private function recordLoginAttempt($ip_address, $is_success) {
        $status = $is_success ? 1 : 0;
        $stmt = $this->db->prepare("INSERT INTO login_attempts (ip_address, is_success) VALUES (:ip, :status)");
        $stmt->bindParam(':ip', $ip_address);
        $stmt->bindParam(':status', $status);
        $stmt->execute();
    }
}
