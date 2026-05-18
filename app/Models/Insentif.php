<?php

class Insentif {
    private $db;

    public function __construct() {
        $this->db = (new Database())->getConnection();
    }

    // ============================================================
    // SUPERADMIN: Manajemen Insentif Global
    // ============================================================

    // Ambil semua data insentif global dengan nama cabang
    public function getAllGlobal($bulan = null, $tahun = null) {
        $where = '';
        $params = [];
        if ($bulan && $tahun) {
            $where = 'WHERE ig.bulan = :bulan AND ig.tahun = :tahun';
            $params[':bulan'] = $bulan;
            $params[':tahun'] = $tahun;
        }
        $stmt = $this->db->prepare("
            SELECT ig.*, c.nama_cabang,
                   (SELECT COUNT(*) FROM insentif_pegawai ip WHERE ip.id_insentif = ig.id_insentif) AS jumlah_penerima
            FROM insentif_global ig
            JOIN cabang c ON ig.id_cabang = c.id_cabang
            $where
            ORDER BY ig.created_at DESC
        ");
        foreach ($params as $k => &$v) $stmt->bindParam($k, $v);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Simpan insentif baru dan langsung distribusikan ke pegawai
    public function simpanDanDistribusi($data) {
        // Cek apakah sudah ada insentif untuk cabang+bulan+tahun ini
        $cek = $this->db->prepare("SELECT id_insentif FROM insentif_global WHERE id_cabang=:ic AND bulan=:b AND tahun=:y");
        $cek->execute([':ic' => $data['id_cabang'], ':b' => $data['bulan'], ':y' => $data['tahun']]);
        if ($cek->rowCount() > 0) {
            return ['status' => false, 'message' => 'Insentif untuk cabang dan periode ini sudah pernah dibuat. Hapus data lama terlebih dahulu.'];
        }

        // Hitung jumlah pegawai aktif di cabang ini
        $q_pegawai = $this->db->prepare("SELECT id_user FROM users WHERE id_cabang = :ic AND is_active = 1 AND role = 'pegawai'");
        $q_pegawai->execute([':ic' => $data['id_cabang']]);
        $pegawai_list = $q_pegawai->fetchAll(PDO::FETCH_COLUMN);
        $jumlah = count($pegawai_list);

        if ($jumlah === 0) {
            return ['status' => false, 'message' => 'Tidak ada pegawai aktif di cabang ini untuk dibagikan insentif.'];
        }

        // Hitung nilai per orang (pembagian rata)
        $nilai_per_orang = round($data['total_nilai'] / $jumlah, 2);

        // Mulai transaksi
        $this->db->beginTransaction();
        try {
            // 1. Insert ke insentif_global
            $stmt = $this->db->prepare("
                INSERT INTO insentif_global (id_cabang, bulan, tahun, tanggal_input, keterangan, total_nilai, status)
                VALUES (:ic, :b, :y, CURDATE(), :ket, :total, 'draft')
            ");
            $stmt->execute([':ic' => $data['id_cabang'], ':b' => $data['bulan'], ':y' => $data['tahun'], ':ket' => $data['keterangan'], ':total' => $data['total_nilai']]);
            $id_insentif = $this->db->lastInsertId();

            // 2. Distribusikan ke setiap pegawai
            $stmt2 = $this->db->prepare("INSERT INTO insentif_pegawai (id_insentif, id_user, nilai_didapat, status) VALUES (:id, :u, :val, 'draft')");
            foreach ($pegawai_list as $id_user) {
                $stmt2->execute([':id' => $id_insentif, ':u' => $id_user, ':val' => $nilai_per_orang]);
            }

            $this->db->commit();
            return [
                'status'   => true,
                'message'  => "Berhasil! Insentif Rp " . number_format($data['total_nilai'],0,',','.') . " dibagi rata ke <b>$jumlah pegawai</b>. Masing-masing mendapat <b>Rp " . number_format($nilai_per_orang,0,',','.') . "</b>.",
                'jumlah'   => $jumlah,
                'per_orang' => $nilai_per_orang
            ];
        } catch (Exception $e) {
            $this->db->rollBack();
            return ['status' => false, 'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()];
        }
    }

    // Publish insentif (ubah status draft -> published agar pegawai bisa lihat)
    public function publish($id_insentif) {
        $this->db->beginTransaction();
        try {
            $this->db->prepare("UPDATE insentif_global SET status='published' WHERE id_insentif=:id")->execute([':id' => $id_insentif]);
            $this->db->prepare("UPDATE insentif_pegawai SET status='published' WHERE id_insentif=:id")->execute([':id' => $id_insentif]);
            $this->db->commit();
            return ['status' => true, 'message' => 'Insentif berhasil dipublikasikan ke seluruh pegawai.'];
        } catch (Exception $e) {
            $this->db->rollBack();
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    // Hapus data insentif (hanya yang masih draft)
    public function hapus($id_insentif) {
        $cek = $this->db->prepare("SELECT status FROM insentif_global WHERE id_insentif=:id");
        $cek->execute([':id' => $id_insentif]);
        $row = $cek->fetch();
        if (!$row || $row['status'] === 'published') {
            return ['status' => false, 'message' => 'Insentif yang sudah dipublikasikan tidak dapat dihapus.'];
        }
        $this->db->prepare("DELETE FROM insentif_global WHERE id_insentif=:id")->execute([':id' => $id_insentif]);
        return ['status' => true, 'message' => 'Data insentif berhasil dihapus.'];
    }

    // Ambil detail rincian per pegawai untuk satu insentif global
    public function getDetailPegawai($id_insentif) {
        $stmt = $this->db->prepare("
            SELECT ip.*, u.nip, u.nama_lengkap, u.jabatan, c.nama_cabang
            FROM insentif_pegawai ip
            JOIN users u ON ip.id_user = u.id_user
            JOIN cabang c ON u.id_cabang = c.id_cabang
            WHERE ip.id_insentif = :id
            ORDER BY u.nama_lengkap
        ");
        $stmt->execute([':id' => $id_insentif]);
        return $stmt->fetchAll();
    }

    // ============================================================
    // PEGAWAI: Riwayat insentif yang diterima
    // ============================================================
    public function getRiwayatPegawai($id_user, $limit = 6) {
        $stmt = $this->db->prepare("
            SELECT ip.nilai_didapat, ip.status,
                   ig.bulan, ig.tahun, ig.keterangan, ig.tanggal_input, ig.total_nilai,
                   c.nama_cabang
            FROM insentif_pegawai ip
            JOIN insentif_global ig ON ip.id_insentif = ig.id_insentif
            JOIN users u ON ip.id_user = u.id_user
            JOIN cabang c ON u.id_cabang = c.id_cabang
            WHERE ip.id_user = :id AND ip.status = 'published'
            ORDER BY ig.tahun DESC, ig.bulan DESC
            LIMIT :lim
        ");
        $stmt->bindParam(':id',  $id_user, PDO::PARAM_INT);
        $stmt->bindParam(':lim', $limit,   PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
