<?php

class LiburCabang {
    private $db;

    public function __construct() {
        $this->db = (new Database())->getConnection();
    }

    public function getByCabang($id_cabang) {
        $stmt = $this->db->prepare("SELECT * FROM libur_override WHERE id_cabang = :id_cabang ORDER BY tanggal DESC");
        $stmt->bindParam(':id_cabang', $id_cabang);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function simpan($data) {
        if (!empty($data['id_override'])) {
            $stmt = $this->db->prepare("UPDATE libur_override SET tanggal = :tanggal, status = :status, keterangan = :keterangan WHERE id_override = :id");
            $stmt->bindParam(':id', $data['id_override']);
        } else {
            $stmt = $this->db->prepare("INSERT INTO libur_override (id_cabang, tanggal, status, keterangan) VALUES (:id_cabang, :tanggal, :status, :keterangan)");
            $stmt->bindParam(':id_cabang', $data['id_cabang']);
        }
        $stmt->bindParam(':tanggal', $data['tanggal']);
        $stmt->bindParam(':status', $data['status']);
        $stmt->bindParam(':keterangan', $data['keterangan']);
        
        try {
            $stmt->execute();
            return ['status' => true, 'message' => 'Data libur/override cabang berhasil disimpan.'];
        } catch (PDOException $e) {
            return ['status' => false, 'message' => 'Gagal menyimpan: ' . $e->getMessage()];
        }
    }

    public function hapus($id, $id_cabang) {
        // Validasi keamanan, pastikan hanya cabang miliknya yang dihapus
        $stmt = $this->db->prepare("DELETE FROM libur_override WHERE id_override = :id AND id_cabang = :id_cabang");
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':id_cabang', $id_cabang);
        return $stmt->execute();
    }
}
