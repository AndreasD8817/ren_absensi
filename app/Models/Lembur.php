<?php

class Lembur {
    private $db;

    public function __construct() {
        $this->db = (new Database())->getConnection();
    }

    // Mengajukan Lembur
    public function ajukanLembur($data) {
        $stmt = $this->db->prepare("
            INSERT INTO pengajuan_lembur (id_user, id_cabang, tanggal, jam_mulai, jam_selesai, durasi_jam, keterangan)
            VALUES (:id_user, :id_cabang, :tanggal, :jam_mulai, :jam_selesai, :durasi_jam, :keterangan)
        ");
        
        $stmt->bindParam(':id_user', $data['id_user'], PDO::PARAM_INT);
        $stmt->bindParam(':id_cabang', $data['id_cabang'], PDO::PARAM_INT);
        $stmt->bindParam(':tanggal', $data['tanggal']);
        $stmt->bindParam(':jam_mulai', $data['jam_mulai']);
        $stmt->bindParam(':jam_selesai', $data['jam_selesai']);
        $stmt->bindParam(':durasi_jam', $data['durasi_jam']);
        $stmt->bindParam(':keterangan', $data['keterangan']);
        
        if ($stmt->execute()) {
            return ['status' => true, 'message' => 'Pengajuan lembur berhasil dikirim dan menunggu persetujuan admin cabang.'];
        }
        return ['status' => false, 'message' => 'Gagal mengirim pengajuan lembur.'];
    }

    // Riwayat lembur per pegawai
    public function getRiwayatLemburPegawai($id_user) {
        $stmt = $this->db->prepare("
            SELECT * FROM pengajuan_lembur 
            WHERE id_user = :id_user 
            ORDER BY created_at DESC
        ");
        $stmt->bindParam(':id_user', $id_user, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Daftar pengajuan lembur per cabang (Untuk Admin Cabang)
    public function getPengajuanByCabang($id_cabang) {
        $stmt = $this->db->prepare("
            SELECT l.*, u.nama_lengkap, u.jabatan 
            FROM pengajuan_lembur l
            JOIN users u ON l.id_user = u.id_user
            WHERE l.id_cabang = :id_cabang
            ORDER BY l.created_at DESC
        ");
        $stmt->bindParam(':id_cabang', $id_cabang, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Approve / Reject Lembur
    public function responLembur($id_lembur, $id_admin, $status, $alasan_reject = null) {
        $sql = "UPDATE pengajuan_lembur SET status = :status, id_admin_approval = :id_admin";
        if ($status === 'rejected' && $alasan_reject) {
            $sql .= ", alasan_reject = :alasan";
        }
        $sql .= " WHERE id_lembur = :id_lembur";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':id_admin', $id_admin, PDO::PARAM_INT);
        $stmt->bindParam(':id_lembur', $id_lembur, PDO::PARAM_INT);
        if ($status === 'rejected' && $alasan_reject) {
            $stmt->bindParam(':alasan', $alasan_reject);
        }
        
        return $stmt->execute();
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

    // Mengambil total jam lembur yang APPROVED per pegawai untuk bulan & tahun tertentu (Untuk Payroll)
    public function getTotalJamLemburBulanan($id_user, $bulan, $tahun) {
        $rentang = $this->getRentangWaktuBuku($bulan, $tahun);
        $stmt = $this->db->prepare("
            SELECT COALESCE(SUM(durasi_jam), 0) as total_jam
            FROM pengajuan_lembur
            WHERE id_user = :id_user 
              AND tanggal BETWEEN :start_date AND :end_date 
              AND status = 'approved'
        ");
        $stmt->bindParam(':id_user', $id_user, PDO::PARAM_INT);
        $stmt->bindParam(':start_date', $rentang['start']);
        $stmt->bindParam(':end_date', $rentang['end']);
        $stmt->execute();
        $res = $stmt->fetch();
        return $res ? (float)$res['total_jam'] : 0;
    }
}
