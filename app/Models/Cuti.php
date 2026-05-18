<?php

class Cuti {
    private $db;

    public function __construct() {
        $this->db = (new Database())->getConnection();
    }

    // Mengajukan cuti baru (Role: Pegawai)
    public function ajukanCuti($data) {
        $stmt = $this->db->prepare("
            INSERT INTO pengajuan_cuti (id_user, jenis, tanggal_mulai, tanggal_selesai, keterangan, bukti_foto, status)
            VALUES (:id, :jenis, :tgl_mulai, :tgl_selesai, :ket, :foto, 'pending')
        ");
        $stmt->bindParam(':id',          $data['id_user']);
        $stmt->bindParam(':jenis',       $data['jenis']);
        $stmt->bindParam(':tgl_mulai',   $data['tanggal_mulai']);
        $stmt->bindParam(':tgl_selesai', $data['tanggal_selesai']);
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

    // Merespon (Approve/Reject) pengajuan cuti (Role: Admin Cabang)
    public function responCuti($id_cuti, $id_admin, $status) {
        if (!in_array($status, ['approved', 'rejected'])) return false;

        $stmt = $this->db->prepare("
            UPDATE pengajuan_cuti 
            SET status = :status, id_approver = :id_admin, tanggal_respon = NOW()
            WHERE id_cuti = :id_cuti
        ");
        $stmt->bindParam(':status',   $status);
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
            ORDER BY pc.status = 'pending' DESC, pc.created_at DESC
        ");
        if ($filter_status) $stmt->bindParam(':status', $filter_status);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Hanya cuti yang pending (untuk dashboard superadmin)
    public function getAllPending() {
        return $this->getAllForSuperadmin('pending');
    }
}
