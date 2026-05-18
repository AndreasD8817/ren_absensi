<?php

class Cabang {
    private $db;

    public function __construct() {
        $this->db = (new Database())->getConnection();
    }

    // Ambil semua cabang
    public function getAll() {
        $stmt = $this->db->prepare("SELECT * FROM cabang ORDER BY nama_cabang ASC");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Ambil satu cabang berdasarkan ID
    public function getById($id_cabang) {
        $stmt = $this->db->prepare("SELECT * FROM cabang WHERE id_cabang = :id");
        $stmt->bindParam(':id', $id_cabang, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    // Tambah cabang baru
    public function insert($data) {
        $stmt = $this->db->prepare("
            INSERT INTO cabang (nama_cabang, timezone, latitude, longitude, radius_meter, denda_1_5, denda_6_10, denda_11_15, denda_16_30, denda_31_60, denda_alfa, tarif_lembur_per_jam)
            VALUES (:nama, :timezone, :lat, :lng, :radius, :d1, :d2, :d3, :d4, :d5, :dalfa, :lembur)
        ");
        $stmt->bindParam(':nama',     $data['nama_cabang']);
        $stmt->bindParam(':timezone', $data['timezone']);
        $stmt->bindParam(':lat',      $data['latitude']);
        $stmt->bindParam(':lng',      $data['longitude']);
        $stmt->bindParam(':radius',   $data['radius_meter']);
        $stmt->bindParam(':d1',       $data['denda_1_5']);
        $stmt->bindParam(':d2',       $data['denda_6_10']);
        $stmt->bindParam(':d3',       $data['denda_11_15']);
        $stmt->bindParam(':d4',       $data['denda_16_30']);
        $stmt->bindParam(':d5',       $data['denda_31_60']);
        $stmt->bindParam(':dalfa',    $data['denda_alfa']);
        $stmt->bindParam(':lembur',   $data['tarif_lembur_per_jam']);

        if ($stmt->execute()) {
            return ['status' => true, 'message' => 'Cabang berhasil ditambahkan.', 'id' => $this->db->lastInsertId()];
        }
        return ['status' => false, 'message' => 'Gagal menyimpan data cabang.'];
    }

    // Update cabang
    public function update($data) {
        $stmt = $this->db->prepare("
            UPDATE cabang SET nama_cabang=:nama, timezone=:timezone, latitude=:lat, longitude=:lng, radius_meter=:radius,
            denda_1_5=:d1, denda_6_10=:d2, denda_11_15=:d3, denda_16_30=:d4, denda_31_60=:d5,
            denda_alfa=:dalfa, tarif_lembur_per_jam=:lembur
            WHERE id_cabang=:id
        ");
        $stmt->bindParam(':nama',     $data['nama_cabang']);
        $stmt->bindParam(':timezone', $data['timezone']);
        $stmt->bindParam(':lat',      $data['latitude']);
        $stmt->bindParam(':lng',      $data['longitude']);
        $stmt->bindParam(':radius',   $data['radius_meter']);
        $stmt->bindParam(':d1',       $data['denda_1_5']);
        $stmt->bindParam(':d2',       $data['denda_6_10']);
        $stmt->bindParam(':d3',       $data['denda_11_15']);
        $stmt->bindParam(':d4',       $data['denda_16_30']);
        $stmt->bindParam(':d5',       $data['denda_31_60']);
        $stmt->bindParam(':dalfa',    $data['denda_alfa']);
        $stmt->bindParam(':lembur',   $data['tarif_lembur_per_jam']);
        $stmt->bindParam(':id',       $data['id_cabang'], PDO::PARAM_INT);

        if ($stmt->execute()) {
            return ['status' => true, 'message' => 'Data cabang berhasil diperbarui.'];
        }
        return ['status' => false, 'message' => 'Gagal memperbarui data cabang.'];
    }

    // Hitung jumlah pegawai di cabang
    public function getJumlahPegawai($id_cabang) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE id_cabang = :id AND is_active = 1");
        $stmt->bindParam(':id', $id_cabang, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchColumn();
    }
}
