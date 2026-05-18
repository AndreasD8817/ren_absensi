<?php

class LiburNasional {
    private $db;

    public function __construct() {
        $this->db = (new Database())->getConnection();
    }

    public function getAll() {
        $stmt = $this->db->query("SELECT * FROM hari_libur ORDER BY tanggal DESC");
        return $stmt->fetchAll();
    }

    public function simpan($data) {
        if (!empty($data['id_libur'])) {
            $stmt = $this->db->prepare("UPDATE hari_libur SET tanggal = :tanggal, keterangan = :keterangan WHERE id_libur = :id");
            $stmt->bindParam(':id', $data['id_libur']);
        } else {
            $stmt = $this->db->prepare("INSERT INTO hari_libur (tanggal, keterangan) VALUES (:tanggal, :keterangan)");
        }
        $stmt->bindParam(':tanggal', $data['tanggal']);
        $stmt->bindParam(':keterangan', $data['keterangan']);
        
        try {
            $stmt->execute();
            return ['status' => true, 'message' => 'Libur nasional berhasil disimpan.'];
        } catch (PDOException $e) {
            return ['status' => false, 'message' => 'Gagal menyimpan: ' . $e->getMessage()];
        }
    }

    public function hapus($id) {
        $stmt = $this->db->prepare("DELETE FROM hari_libur WHERE id_libur = :id");
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function importCsv($file_tmp) {
        $file = fopen($file_tmp, 'r');
        $berhasil = 0;
        $gagal = 0;
        
        // Skip header
        fgetcsv($file);

        $stmt = $this->db->prepare("INSERT IGNORE INTO hari_libur (tanggal, keterangan) VALUES (:tanggal, :keterangan)");
        
        while (($data = fgetcsv($file, 1000, ",")) !== FALSE) {
            if (count($data) >= 2) {
                $tanggal = trim($data[0]);
                $keterangan = trim($data[1]);
                
                // Validasi format tanggal YYYY-MM-DD
                if (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $tanggal)) {
                    $stmt->bindParam(':tanggal', $tanggal);
                    $stmt->bindParam(':keterangan', $keterangan);
                    if ($stmt->execute()) {
                        if ($stmt->rowCount() > 0) $berhasil++;
                        else $gagal++; // Duplicate (UNIQUE constraint)
                    } else {
                        $gagal++;
                    }
                } else {
                    $gagal++;
                }
            }
        }
        fclose($file);
        
        return ['status' => true, 'message' => "Import selesai. Berhasil: $berhasil baris, Gagal/Duplikat: $gagal baris."];
    }
}
