<?php

class KasDenda {
    private $db;

    public function __construct() {
        $this->db = (new Database())->getConnection();
    }

    // Mendapatkan riwayat kas denda untuk cabang tertentu (Pemasukan & Pengeluaran)
    public function getRiwayatCabang($id_cabang, $bulan = null, $tahun = null) {
        $sql = "
            SELECT k.*, c.nama_cabang 
            FROM kas_denda_cabang k
            JOIN cabang c ON k.id_cabang = c.id_cabang
            WHERE k.id_cabang = :id_cabang
        ";
        
        if ($bulan && $tahun) {
            $sql .= " AND MONTH(k.tanggal) = :bulan AND YEAR(k.tanggal) = :tahun";
        }
        $sql .= " ORDER BY k.tanggal DESC, k.id_kas DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id_cabang', $id_cabang, PDO::PARAM_INT);
        if ($bulan && $tahun) {
            $stmt->bindParam(':bulan', $bulan, PDO::PARAM_INT);
            $stmt->bindParam(':tahun', $tahun, PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Mendapatkan rekap kas (Total Pemasukan, Total Pengeluaran, Saldo) untuk setiap cabang
    public function getRekapSemuaCabang() {
        $sql = "
            SELECT 
                c.id_cabang, c.nama_cabang,
                COALESCE(SUM(CASE WHEN k.jenis = 'pemasukan' THEN k.nominal ELSE 0 END), 0) as total_pemasukan,
                COALESCE(SUM(CASE WHEN k.jenis = 'pengeluaran' THEN k.nominal ELSE 0 END), 0) as total_pengeluaran,
                (COALESCE(SUM(CASE WHEN k.jenis = 'pemasukan' THEN k.nominal ELSE 0 END), 0) - COALESCE(SUM(CASE WHEN k.jenis = 'pengeluaran' THEN k.nominal ELSE 0 END), 0)) as saldo
            FROM cabang c
            LEFT JOIN kas_denda_cabang k ON c.id_cabang = k.id_cabang
            GROUP BY c.id_cabang, c.nama_cabang
            ORDER BY c.nama_cabang ASC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    // Mendapatkan rekap kas untuk 1 cabang
    public function getRekapCabang($id_cabang) {
        $sql = "
            SELECT 
                COALESCE(SUM(CASE WHEN jenis = 'pemasukan' THEN nominal ELSE 0 END), 0) as total_pemasukan,
                COALESCE(SUM(CASE WHEN jenis = 'pengeluaran' THEN nominal ELSE 0 END), 0) as total_pengeluaran,
                (COALESCE(SUM(CASE WHEN jenis = 'pemasukan' THEN nominal ELSE 0 END), 0) - COALESCE(SUM(CASE WHEN jenis = 'pengeluaran' THEN nominal ELSE 0 END), 0)) as saldo
            FROM kas_denda_cabang
            WHERE id_cabang = :id_cabang
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id_cabang', $id_cabang, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    // Menambah transaksi manual (khususnya untuk pengeluaran oleh pusat ke cabang)
    public function insertTransaksi($data) {
        $stmt = $this->db->prepare("
            INSERT INTO kas_denda_cabang (id_cabang, tanggal, jenis, nominal, keterangan)
            VALUES (:id_cabang, :tanggal, :jenis, :nominal, :keterangan)
        ");
        $stmt->bindParam(':id_cabang', $data['id_cabang'], PDO::PARAM_INT);
        $stmt->bindParam(':tanggal', $data['tanggal']);
        $stmt->bindParam(':jenis', $data['jenis']);
        $stmt->bindParam(':nominal', $data['nominal']);
        $stmt->bindParam(':keterangan', $data['keterangan']);
        
        if ($stmt->execute()) {
            return ['status' => true, 'message' => 'Transaksi kas berhasil dicatat.'];
        }
        return ['status' => false, 'message' => 'Gagal mencatat transaksi kas.'];
    }
}
