<?php

class Cuti {
    private $db;

    public function __construct() {
        $this->db = (new Database())->getConnection();
    }

    // Helper untuk mengambil list hari libur nasional
    private function getLiburNasionalDates($tahun) {
        $stmt = $this->db->prepare("SELECT tanggal FROM hari_libur WHERE YEAR(tanggal) = :tahun");
        $stmt->execute([':tahun' => $tahun]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    // Menghitung jumlah hari cuti dengan mengabaikan Sabtu, Minggu, dan Libur Nasional
    public function hitungHariCutiEfektif($tgl_mulai, $tgl_selesai) {
        $tahun = date('Y', strtotime($tgl_mulai));
        $libur_nasional = $this->getLiburNasionalDates($tahun);
        
        $start = new DateTime($tgl_mulai);
        $end = new DateTime($tgl_selesai);
        $end->modify('+1 day'); // Supaya DatePeriod include hari terakhir
        
        $interval = new DateInterval('P1D');
        $period = new DatePeriod($start, $interval, $end);
        
        $hari_efektif = 0;
        foreach ($period as $dt) {
            $curr_date = $dt->format('Y-m-d');
            $day_of_week = $dt->format('N'); // 1 (Mon) - 7 (Sun)
            
            // Jika bukan Minggu (7) dan bukan hari libur nasional
            if ($day_of_week < 7 && !in_array($curr_date, $libur_nasional)) {
                $hari_efektif++;
            }
        }
        return $hari_efektif;
    }

    public function getSisaCutiTahunan($id_user, $tahun) {
        $kuota_awal = 12;
        
        // 1. Hitung total Cuti Bersama tahun ini
        $stmt_cb = $this->db->prepare("SELECT COUNT(*) FROM hari_libur WHERE YEAR(tanggal) = :tahun AND LOWER(keterangan) LIKE '%cuti bersama%'");
        $stmt_cb->execute([':tahun' => $tahun]);
        $total_cuti_bersama = (int)$stmt_cb->fetchColumn();
        
        // 2. Hitung total Cuti (approved) yang sudah diambil user ini tahun ini
        $stmt_terpakai = $this->db->prepare("SELECT tanggal_mulai, tanggal_selesai FROM pengajuan_cuti WHERE id_user = :id_user AND jenis = 'Cuti' AND status = 'approved' AND YEAR(tanggal_mulai) = :tahun");
        $stmt_terpakai->execute([':id_user' => $id_user, ':tahun' => $tahun]);
        $cuti_approved = $stmt_terpakai->fetchAll(PDO::FETCH_ASSOC);
        
        $total_terpakai = 0;
        foreach ($cuti_approved as $c) {
            $total_terpakai += $this->hitungHariCutiEfektif($c['tanggal_mulai'], $c['tanggal_selesai']);
        }
        
        return $kuota_awal - $total_cuti_bersama - $total_terpakai;
    }

    // Mengajukan cuti baru (Role: Pegawai)
    public function ajukanCuti($data) {
        $tgl_mulai = $data['tanggal_mulai'];
        $tgl_selesai = $data['tanggal_selesai'];
        $jenis = $data['jenis'];
        $id_user = $data['id_user'];
        
        // Validasi H-7 (Hanya untuk Cuti dan Izin, Sakit boleh mendadak)
        if ($jenis !== 'Sakit') {
            $diff_days = (strtotime($tgl_mulai) - strtotime(date('Y-m-d'))) / (60 * 60 * 24);
            if ($diff_days < 7) {
                return ['status' => false, 'message' => 'Pengajuan cuti/izin minimal harus diajukan H-7 sebelum tanggal mulai.'];
            }
        }
        
        // Validasi Kuota jika jenisnya Cuti
        if ($jenis === 'Cuti') {
            $hari_diminta = $this->hitungHariCutiEfektif($tgl_mulai, $tgl_selesai);
            if ($hari_diminta <= 0) {
                return ['status' => false, 'message' => 'Rentang tanggal yang Anda pilih jatuh sepenuhnya pada hari libur / akhir pekan.'];
            }
            
            $tahun = date('Y', strtotime($tgl_mulai));
            $sisa_cuti = $this->getSisaCutiTahunan($id_user, $tahun);
            
            if ($hari_diminta > $sisa_cuti) {
                return ['status' => false, 'message' => "Sisa kuota cuti tahunan Anda tidak cukup. Sisa kuota: $sisa_cuti hari, Anda mengajukan $hari_diminta hari efektif kerja."];
            }
        }

        $stmt = $this->db->prepare("
            INSERT INTO pengajuan_cuti (id_user, jenis, tanggal_mulai, tanggal_selesai, keterangan, bukti_foto, status)
            VALUES (:id, :jenis, :tgl_mulai, :tgl_selesai, :ket, :foto, 'pending')
        ");
        $stmt->bindParam(':id',          $id_user);
        $stmt->bindParam(':jenis',       $jenis);
        $stmt->bindParam(':tgl_mulai',   $tgl_mulai);
        $stmt->bindParam(':tgl_selesai', $tgl_selesai);
        $stmt->bindParam(':ket',         $data['keterangan']);
        $stmt->bindParam(':foto',        $data['bukti_foto']);

        if ($stmt->execute()) {
            return ['status' => true, 'message' => 'Pengajuan berhasil dikirim dan menunggu persetujuan Admin Cabang.'];
        }
        return ['status' => false, 'message' => 'Gagal mengirim pengajuan.'];
    }

    // Riwayat pengajuan cuti pegawai tertentu (Role: Pegawai)
    public function getRiwayatPegawai($id_user) {
        $stmt = $this->db->prepare("SELECT * FROM pengajuan_cuti WHERE id_user = :id ORDER BY created_at DESC");
        $stmt->bindParam(':id', $id_user, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Daftar semua pengajuan cuti di suatu cabang (Role: Admin Cabang)
    public function getPengajuanByCabang($id_cabang) {
        $stmt = $this->db->prepare("
            SELECT pc.*, u.nama_lengkap, u.nip, u.jabatan 
            FROM pengajuan_cuti pc
            JOIN users u ON pc.id_user = u.id_user
            WHERE u.id_cabang = :id
            ORDER BY pc.status = 'pending' DESC, pc.created_at DESC
        ");
        $stmt->bindParam(':id', $id_cabang, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Merespon (Approve/Reject) pengajuan cuti (Role: Admin Cabang / Superadmin)
    public function responCuti($id_cuti, $id_admin, $status, $role_admin = 'superadmin') {
        if (!in_array($status, ['approved', 'rejected'])) return false;

        $target_status = $status;
        if ($role_admin === 'admin_cabang' && $status === 'approved') {
            $target_status = 'menunggu_pusat'; // 2-Tier Approval
        }

        $stmt = $this->db->prepare("
            UPDATE pengajuan_cuti 
            SET status = :status, id_approver = :id_admin, tanggal_respon = NOW()
            WHERE id_cuti = :id_cuti
        ");
        $stmt->bindParam(':status',   $target_status);
        $stmt->bindParam(':id_admin', $id_admin);
        $stmt->bindParam(':id_cuti',  $id_cuti);
        return $stmt->execute();
    }

    // Fungsi helper untuk Penggajian: Cek apakah pegawai sedang cuti (approved) pada tanggal tertentu
    public function isCutiApproved($id_user, $tanggal) {
        $stmt = $this->db->prepare("
            SELECT id_cuti FROM pengajuan_cuti 
            WHERE id_user = :id 
            AND status = 'approved' 
            AND :tgl BETWEEN tanggal_mulai AND tanggal_selesai
        ");
        $stmt->execute([':id' => $id_user, ':tgl' => $tanggal]);
        return $stmt->rowCount() > 0;
    }

    // Semua pengajuan cuti untuk halaman Superadmin (lintas cabang)
    public function getAllForSuperadmin($filter_status = null) {
        $where = $filter_status ? "AND pc.status = :status" : "";
        $stmt = $this->db->prepare("
            SELECT pc.*, u.nama_lengkap, u.nip, u.jabatan, c.nama_cabang
            FROM pengajuan_cuti pc
            JOIN users u ON pc.id_user = u.id_user
            JOIN cabang c ON u.id_cabang = c.id_cabang
            WHERE 1=1 $where
            ORDER BY pc.status = 'menunggu_pusat' DESC, pc.status = 'pending' DESC, pc.created_at DESC
        ");
        if ($filter_status) $stmt->bindParam(':status', $filter_status);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Hanya cuti yang pending/menunggu pusat (untuk dashboard superadmin)
    public function getAllPending() {
        return $this->getAllForSuperadmin('menunggu_pusat');
    }
}
