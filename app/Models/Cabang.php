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
        try {
            $stmt = $this->db->prepare("
                INSERT INTO cabang (nama_cabang, umk, timezone, latitude, longitude, radius_meter, denda_1_5, denda_6_10, denda_11_15, denda_16_30, denda_31_60, denda_alfa, denda_tidak_absen_pulang, tarif_lembur_per_jam)
                VALUES (:nama, :umk, :timezone, :lat, :lng, :radius, :d1, :d2, :d3, :d4, :d5, :dalfa, :dnotpulang, :lembur)
            ");
            
            $radius = $data['radius_meter'] === '' ? 50 : $data['radius_meter'];
            $d1 = $data['denda_1_5'] === '' ? 0 : $data['denda_1_5'];
            $d2 = $data['denda_6_10'] === '' ? 0 : $data['denda_6_10'];
            $d3 = $data['denda_11_15'] === '' ? 0 : $data['denda_11_15'];
            $d4 = $data['denda_16_30'] === '' ? 0 : $data['denda_16_30'];
            $d5 = $data['denda_31_60'] === '' ? 0 : $data['denda_31_60'];
            $dalfa = $data['denda_alfa'] === '' ? 0 : $data['denda_alfa'];
            $dnotpulang = $data['denda_tidak_absen_pulang'] === '' ? 0 : $data['denda_tidak_absen_pulang'];
            $umk = empty($data['umk']) ? 0 : str_replace('.', '', $data['umk']); // Remove dots if submitted as formatted string
            $lembur = 0; // Data usang, kini dihitung otomatis berdasarkan Tipe Pekerjaan

            $stmt->bindParam(':nama',     $data['nama_cabang']);
            $stmt->bindParam(':umk',      $umk);
            $stmt->bindParam(':timezone', $data['timezone']);
            $stmt->bindParam(':lat',      $data['latitude']);
            $stmt->bindParam(':lng',      $data['longitude']);
            $stmt->bindParam(':radius',   $radius);
            $stmt->bindParam(':d1',       $d1);
            $stmt->bindParam(':d2',       $d2);
            $stmt->bindParam(':d3',       $d3);
            $stmt->bindParam(':d4',       $d4);
            $stmt->bindParam(':d5',       $d5);
            $stmt->bindParam(':dalfa',    $dalfa);
            $stmt->bindParam(':dnotpulang', $dnotpulang);
            $stmt->bindParam(':lembur',   $lembur);

            if ($stmt->execute()) {
                return ['status' => true, 'message' => 'Cabang berhasil ditambahkan.', 'id' => $this->db->lastInsertId()];
            }
            return ['status' => false, 'message' => 'Gagal menyimpan data cabang.'];
        } catch (PDOException $e) {
            return ['status' => false, 'message' => 'Kesalahan database: ' . $e->getMessage()];
        }
    }

    // Update cabang
    public function update($data) {
        try {
            $stmt = $this->db->prepare("
                UPDATE cabang SET nama_cabang=:nama, umk=:umk, timezone=:timezone, latitude=:lat, longitude=:lng, radius_meter=:radius,
                denda_1_5=:d1, denda_6_10=:d2, denda_11_15=:d3, denda_16_30=:d4, denda_31_60=:d5,
                denda_alfa=:dalfa, denda_tidak_absen_pulang=:dnotpulang, tarif_lembur_per_jam=:lembur
                WHERE id_cabang=:id
            ");
            
            $radius = $data['radius_meter'] === '' ? 50 : $data['radius_meter'];
            $d1 = $data['denda_1_5'] === '' ? 0 : $data['denda_1_5'];
            $d2 = $data['denda_6_10'] === '' ? 0 : $data['denda_6_10'];
            $d3 = $data['denda_11_15'] === '' ? 0 : $data['denda_11_15'];
            $d4 = $data['denda_16_30'] === '' ? 0 : $data['denda_16_30'];
            $d5 = $data['denda_31_60'] === '' ? 0 : $data['denda_31_60'];
            $dalfa = $data['denda_alfa'] === '' ? 0 : $data['denda_alfa'];
            $dnotpulang = $data['denda_tidak_absen_pulang'] === '' ? 0 : $data['denda_tidak_absen_pulang'];
            $umk = empty($data['umk']) ? 0 : str_replace('.', '', $data['umk']);
            $lembur = 0; // Data usang, kini dihitung otomatis berdasarkan Tipe Pekerjaan

            $stmt->bindParam(':nama',     $data['nama_cabang']);
            $stmt->bindParam(':umk',      $umk);
            $stmt->bindParam(':timezone', $data['timezone']);
            $stmt->bindParam(':lat',      $data['latitude']);
            $stmt->bindParam(':lng',      $data['longitude']);
            $stmt->bindParam(':radius',   $radius);
            $stmt->bindParam(':d1',       $d1);
            $stmt->bindParam(':d2',       $d2);
            $stmt->bindParam(':d3',       $d3);
            $stmt->bindParam(':d4',       $d4);
            $stmt->bindParam(':d5',       $d5);
            $stmt->bindParam(':dalfa',    $dalfa);
            $stmt->bindParam(':dnotpulang', $dnotpulang);
            $stmt->bindParam(':lembur',   $lembur);
            $stmt->bindParam(':id',       $data['id_cabang'], PDO::PARAM_INT);

            if ($stmt->execute()) {
                return ['status' => true, 'message' => 'Data cabang berhasil diperbarui.'];
            }
            return ['status' => false, 'message' => 'Gagal memperbarui data cabang.'];
        } catch (PDOException $e) {
            return ['status' => false, 'message' => 'Kesalahan database: ' . $e->getMessage()];
        }
    }

    // Hitung jumlah pegawai di cabang
    public function getJumlahPegawai($id_cabang) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE id_cabang = :id AND is_active = 1");
        $stmt->bindParam(':id', $id_cabang, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchColumn();
    }
}
