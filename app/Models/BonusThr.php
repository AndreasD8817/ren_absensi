<?php

class BonusThr {
    private $db;

    public function __construct() {
        $this->db = (new Database())->getConnection();
    }

    public function getBonusByBulanTahun($bulan, $tahun) {
        $stmt = $this->db->prepare("
            SELECT b.*, u.nama_lengkap, u.nip, c.nama_cabang
            FROM bonus_thr_bulanan b
            JOIN users u ON b.id_user = u.id_user
            JOIN cabang c ON u.id_cabang = c.id_cabang
            WHERE b.bulan = :bulan AND b.tahun = :tahun
            ORDER BY b.id_bonus DESC
        ");
        $stmt->bindParam(':bulan', $bulan, PDO::PARAM_INT);
        $stmt->bindParam(':tahun', $tahun, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function simpanBonusManual($data) {
        $stmt = $this->db->prepare("
            INSERT INTO bonus_thr_bulanan (id_user, bulan, tahun, nominal, keterangan)
            VALUES (:id_user, :bulan, :tahun, :nominal, :keterangan)
        ");
        $stmt->bindParam(':id_user', $data['id_user'], PDO::PARAM_INT);
        $stmt->bindParam(':bulan', $data['bulan'], PDO::PARAM_INT);
        $stmt->bindParam(':tahun', $data['tahun'], PDO::PARAM_INT);
        $stmt->bindParam(':nominal', $data['nominal']);
        $stmt->bindParam(':keterangan', $data['keterangan']);
        
        if ($stmt->execute()) {
            return ['status' => true, 'message' => 'Bonus/THR berhasil dicatat.'];
        }
        return ['status' => false, 'message' => 'Gagal mencatat Bonus/THR.'];
    }

    public function simpanBonusMassal($data_array) {
        try {
            $this->db->beginTransaction();
            $stmt = $this->db->prepare("
                INSERT INTO bonus_thr_bulanan (id_user, bulan, tahun, nominal, keterangan)
                VALUES (:id_user, :bulan, :tahun, :nominal, :keterangan)
            ");
            
            $berhasil = 0;
            foreach ($data_array as $row) {
                $stmt->bindParam(':id_user', $row['id_user'], PDO::PARAM_INT);
                $stmt->bindParam(':bulan', $row['bulan'], PDO::PARAM_INT);
                $stmt->bindParam(':tahun', $row['tahun'], PDO::PARAM_INT);
                $stmt->bindParam(':nominal', $row['nominal']);
                $stmt->bindParam(':keterangan', $row['keterangan']);
                $stmt->execute();
                $berhasil++;
            }
            
            $this->db->commit();
            return ['status' => true, 'message' => "$berhasil data bonus/THR berhasil diupload."];
        } catch (Exception $e) {
            $this->db->rollBack();
            return ['status' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
}
