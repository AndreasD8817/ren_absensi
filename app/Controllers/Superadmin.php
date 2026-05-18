<?php

class Superadmin extends Controller {
    public function __construct() {
        if (!isset($_SESSION['user'])) {
            header('Location: ' . BASE_URL . '/auth');
            exit;
        }
        if ($_SESSION['user']['role'] !== 'superadmin') {
            header('Location: ' . BASE_URL . '/' . $_SESSION['user']['role']);
            exit;
        }
    }

    // ==================== DASHBOARD ====================
    public function index() {
        $data['judul'] = 'Dashboard Superadmin | PT REN';

        $absensiModel = $this->model('Absensi');
        $cutiModel    = $this->model('Cuti');

        $data['statistik']    = $absensiModel->getStatistikHariIni();
        $tren_raw             = $absensiModel->getTren7Hari();
        $data['tren_labels']  = json_encode(array_column($tren_raw, 'tanggal'));
        $data['tren_hadir']   = json_encode(array_column($tren_raw, 'hadir'));
        $data['tren_telat']   = json_encode(array_column($tren_raw, 'telat'));
        $data['cuti_pending'] = $cutiModel->getAllPending();

        $this->view('layouts/header', $data);
        $this->view('superadmin/dashboard', $data);
        $this->view('layouts/footer');
    }

    // ==================== KELOLA PEGAWAI ====================
    public function data_pegawai() {
        $data['judul']   = 'Kelola Pegawai | PT REN';
        $userModel       = $this->model('User');
        $cabangModel     = $this->model('Cabang');
        $data['pegawai'] = $userModel->getAllPegawai();
        $data['cabang']  = $cabangModel->getAll(); // Untuk dropdown di form
        $this->view('layouts/header', $data);
        $this->view('superadmin/pegawai/index', $data);
        $this->view('layouts/footer');
    }

    public function simpan_pegawai() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->jsonError('Metode tidak valid.'); return; }
        $userModel = $this->model('User');
        $result    = $userModel->insertPegawai($_POST);
        echo json_encode(['status' => $result['status'] ? 'success' : 'error', 'message' => $result['message']]);
    }

    public function update_pegawai() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->jsonError('Metode tidak valid.'); return; }
        $userModel = $this->model('User');
        $result    = $userModel->updatePegawai($_POST);
        echo json_encode(['status' => $result['status'] ? 'success' : 'error', 'message' => $result['message']]);
    }

    public function toggle_pegawai() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->jsonError('Metode tidak valid.'); return; }
        $userModel = $this->model('User');
        $result    = $userModel->toggleStatusPegawai($_POST['id_user']);
        echo json_encode(['status' => $result ? 'success' : 'error', 'message' => $result ? 'Status pegawai berhasil diubah.' : 'Gagal mengubah status.']);
    }

    // ==================== KELOLA CABANG ====================
    public function data_cabang() {
        $data['judul']  = 'Kelola Cabang | PT REN';
        $cabangModel    = $this->model('Cabang');
        $data['cabang'] = $cabangModel->getAll();
        $this->view('layouts/header', $data);
        $this->view('superadmin/cabang/index', $data);
        $this->view('layouts/footer');
    }

    public function simpan_cabang() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->jsonError('Metode tidak valid.'); return; }
        $cabangModel = $this->model('Cabang');
        $result      = $cabangModel->insert($_POST);
        echo json_encode(['status' => $result['status'] ? 'success' : 'error', 'message' => $result['message']]);
    }

    public function update_cabang() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->jsonError('Metode tidak valid.'); return; }
        $cabangModel = $this->model('Cabang');
        $result      = $cabangModel->update($_POST);
        echo json_encode(['status' => $result['status'] ? 'success' : 'error', 'message' => $result['message']]);
    }

    // ==================== CUTI (Superadmin) ====================
    public function cuti() {
        $data['judul']     = 'Kelola Cuti & Izin | PT REN';
        $cutiModel         = $this->model('Cuti');
        $filter_status     = $_GET['status'] ?? null;
        $data['list_cuti'] = $cutiModel->getAllForSuperadmin($filter_status);
        $data['filter']    = $filter_status;

        $this->view('layouts/header', $data);
        $this->view('superadmin/cuti/index', $data);
        $this->view('layouts/footer');
    }

    public function respon_cuti_pusat() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->jsonError('Metode tidak valid.'); return; }
        $cutiModel = $this->model('Cuti');
        $id_admin  = $_SESSION['user']['id_user'];
        $result    = $cutiModel->responCuti($_POST['id_cuti'], $id_admin, $_POST['status']);
        echo json_encode(['status' => $result ? 'success' : 'error', 'message' => $result ? 'Keputusan berhasil disimpan.' : 'Gagal menyimpan keputusan.']);
    }

    // ==================== ABSENSI ====================
    public function absensi() {
        $bulan  = $_GET['bulan'] ?? date('n');
        $tahun  = $_GET['tahun'] ?? date('Y');

        $data['judul']     = 'Data Absensi | PT REN';
        $data['bulan']     = (int)$bulan;
        $data['tahun']     = (int)$tahun;

        $penggajianModel    = $this->model('Penggajian');
        $data['ringkasan']  = $penggajianModel->getRingkasanAbsensi($bulan, $tahun);

        $this->view('layouts/header', $data);
        $this->view('superadmin/absensi/index', $data);
        $this->view('layouts/footer');
    }

    public function detail_absensi_user() {
        $id_user = $_GET['id_user'] ?? 0;
        $bulan   = $_GET['bulan'] ?? date('n');
        $tahun   = $_GET['tahun'] ?? date('Y');

        $absensiModel = $this->model('Absensi');
        $detail       = $absensiModel->getDetailHarianUser($id_user, $bulan, $tahun);
        echo json_encode(['status' => 'success', 'data' => $detail]);
    }

    public function edit_absensi() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->jsonError('Metode tidak valid.'); return; }
        $absensiModel = $this->model('Absensi');
        $result = $absensiModel->editAbsensi($_POST);
        echo json_encode(['status' => $result ? 'success' : 'error', 'message' => $result ? 'Data absensi berhasil diperbarui.' : 'Gagal memperbarui data.']);
    }

    // ==================== PENGGAJIAN ====================
    public function penggajian() {
        $bulan  = $_GET['bulan'] ?? date('n');
        $tahun  = $_GET['tahun'] ?? date('Y');

        $data['judul']     = 'Penggajian Bulanan | PT REN';
        $data['bulan']     = (int)$bulan;
        $data['tahun']     = (int)$tahun;

        $penggajianModel    = $this->model('Penggajian');
        $data['data_gaji']  = $penggajianModel->getDataGaji($bulan, $tahun);
        $data['ringkasan']  = $penggajianModel->getRingkasanAbsensi($bulan, $tahun);

        $this->view('layouts/header', $data);
        $this->view('superadmin/penggajian/index', $data);
        $this->view('layouts/footer');
    }

    public function generate_gaji() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->jsonError('Metode tidak valid.'); return; }
        $penggajianModel = $this->model('Penggajian');
        $result = $penggajianModel->generateGaji($_POST['bulan'], $_POST['tahun']);
        echo json_encode(['status' => 'success', 'message' => $result['message'], 'total' => $result['total']]);
    }

    public function publish_gaji() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->jsonError('Metode tidak valid.'); return; }
        $penggajianModel = $this->model('Penggajian');
        $penggajianModel->publishGaji($_POST['bulan'], $_POST['tahun']);
        echo json_encode(['status' => 'success', 'message' => 'Gaji berhasil dipublikasikan ke semua pegawai!']);
    }

    // ==================== INSENTIF ====================
    public function insentif() {
        $bulan  = $_GET['bulan'] ?? date('n');
        $tahun  = $_GET['tahun'] ?? date('Y');

        $data['judul']        = 'Kelola Insentif | PT REN';
        $data['bulan']        = (int)$bulan;
        $data['tahun']        = (int)$tahun;

        $insentifModel        = $this->model('Insentif');
        $cabangModel          = $this->model('Cabang');
        $data['list_insentif'] = $insentifModel->getAllGlobal($bulan, $tahun);
        $data['cabang']       = $cabangModel->getAll();

        $this->view('layouts/header', $data);
        $this->view('superadmin/insentif/index', $data);
        $this->view('layouts/footer');
    }

    public function simpan_insentif() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->jsonError('Metode tidak valid.'); return; }
        $insentifModel = $this->model('Insentif');
        $result = $insentifModel->simpanDanDistribusi($_POST);
        echo json_encode([
            'status'  => $result['status'] ? 'success' : 'error',
            'message' => $result['message'],
            'jumlah'  => $result['jumlah'] ?? 0,
            'per_orang' => $result['per_orang'] ?? 0
        ]);
    }

    public function publish_insentif() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->jsonError('Metode tidak valid.'); return; }
        $insentifModel = $this->model('Insentif');
        $result = $insentifModel->publish($_POST['id_insentif']);
        echo json_encode(['status' => $result['status'] ? 'success' : 'error', 'message' => $result['message']]);
    }

    public function hapus_insentif() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->jsonError('Metode tidak valid.'); return; }
        $insentifModel = $this->model('Insentif');
        $result = $insentifModel->hapus($_POST['id_insentif']);
        echo json_encode(['status' => $result['status'] ? 'success' : 'error', 'message' => $result['message']]);
    }

    public function detail_insentif() {
        if (!isset($_GET['id'])) { $this->jsonError('ID tidak valid.'); return; }
        $insentifModel = $this->model('Insentif');
        $detail = $insentifModel->getDetailPegawai($_GET['id']);
        echo json_encode(['status' => 'success', 'data' => $detail]);
    }

    // ==================== KAS DENDA CABANG ====================
    public function kas_denda() {
        $data['judul']      = 'Kas Denda Cabang | PT REN';
        $kasModel           = $this->model('KasDenda');
        $cabangModel        = $this->model('Cabang');
        
        $data['rekap']      = $kasModel->getRekapSemuaCabang();
        $data['cabang']     = $cabangModel->getAll();
        
        // Ambil filter cabang jika ada untuk riwayat
        $id_cabang = $_GET['id_cabang'] ?? null;
        if ($id_cabang) {
            $data['riwayat'] = $kasModel->getRiwayatCabang($id_cabang);
            $data['cabang_terpilih'] = $id_cabang;
        } else {
            $data['riwayat'] = [];
            $data['cabang_terpilih'] = '';
        }

        $this->view('layouts/header', $data);
        $this->view('superadmin/kas_denda/index', $data);
        $this->view('layouts/footer');
    }

    public function simpan_transaksi_kas() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->jsonError('Metode tidak valid.'); return; }
        $kasModel = $this->model('KasDenda');
        $result = $kasModel->insertTransaksi([
            'id_cabang'  => $_POST['id_cabang'],
            'tanggal'    => $_POST['tanggal'],
            'jenis'      => $_POST['jenis'],
            'nominal'    => $_POST['nominal'],
            'keterangan' => $_POST['keterangan']
        ]);
        echo json_encode(['status' => $result['status'] ? 'success' : 'error', 'message' => $result['message']]);
    }

    // ==================== EXPORT LAPORAN ====================
    public function export_absensi() {
        $bulan = $_GET['bulan'] ?? date('m');
        $tahun = $_GET['tahun'] ?? date('Y');
        $id_cabang = $_GET['id_cabang'] ?? 'all';
        $format = $_GET['format'] ?? 'excel'; // excel atau pdf

        $penggajianModel = $this->model('Penggajian');
        $data['absensi'] = $penggajianModel->getRingkasanAbsensi($bulan, $tahun);
        
        // Filter cabang
        if ($id_cabang !== 'all') {
            $data['absensi'] = array_filter($data['absensi'], function($a) use ($id_cabang) {
                return $a['id_cabang'] == $id_cabang;
            });
        }

        $data['bulan'] = $bulan;
        $data['tahun'] = $tahun;
        $data['format'] = $format;
        $data['judul'] = "Laporan_Absensi_{$bulan}_{$tahun}";

        $this->view('superadmin/export/absensi', $data);
    }

    public function export_gaji() {
        $bulan = $_GET['bulan'] ?? date('m');
        $tahun = $_GET['tahun'] ?? date('Y');
        $id_cabang = $_GET['id_cabang'] ?? 'all';
        $format = $_GET['format'] ?? 'excel';

        $db = (new Database())->getConnection();
        $sql = "SELECT p.*, u.nama_lengkap, u.nip, u.jabatan, c.nama_cabang
                FROM penggajian_bulanan p
                JOIN users u ON p.id_user = u.id_user
                JOIN cabang c ON u.id_cabang = c.id_cabang
                WHERE p.bulan = :bulan AND p.tahun = :tahun AND p.status = 'published'";
        
        if ($id_cabang !== 'all') {
            $sql .= " AND u.id_cabang = :id_cabang";
        }
        $sql .= " ORDER BY c.nama_cabang, u.nama_lengkap";

        $stmt = $db->prepare($sql);
        $stmt->bindParam(':bulan', $bulan, PDO::PARAM_INT);
        $stmt->bindParam(':tahun', $tahun, PDO::PARAM_INT);
        if ($id_cabang !== 'all') { $stmt->bindParam(':id_cabang', $id_cabang, PDO::PARAM_INT); }
        $stmt->execute();
        
        $data['gaji'] = $stmt->fetchAll();
        $data['bulan'] = $bulan;
        $data['tahun'] = $tahun;
        $data['format'] = $format;
        $data['judul'] = "Laporan_Penggajian_{$bulan}_{$tahun}";

        $this->view('superadmin/export/gaji', $data);
    }

    // ==================== LIBUR NASIONAL ====================
    public function libur_nasional() {
        $data['judul'] = 'Kalender Libur Nasional | PT REN';
        $liburModel = $this->model('LiburNasional');
        $data['libur'] = $liburModel->getAll();

        $this->view('layouts/header', $data);
        $this->view('superadmin/libur_nasional/index', $data);
        $this->view('layouts/footer');
    }

    public function simpan_libur_nasional() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->jsonError('Metode tidak valid.'); return; }
        $liburModel = $this->model('LiburNasional');
        $result = $liburModel->simpan($_POST);
        echo json_encode(['status' => $result['status'] ? 'success' : 'error', 'message' => $result['message']]);
    }

    public function hapus_libur_nasional($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->jsonError('Metode tidak valid.'); return; }
        $liburModel = $this->model('LiburNasional');
        if ($liburModel->hapus($id)) {
            echo json_encode(['status' => 'success', 'message' => 'Hari libur berhasil dihapus.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Gagal menghapus hari libur.']);
        }
    }

    public function import_libur_nasional() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->jsonError('Metode tidak valid.'); return; }
        if (isset($_FILES['file_csv']) && $_FILES['file_csv']['error'] == 0) {
            $ext = pathinfo($_FILES['file_csv']['name'], PATHINFO_EXTENSION);
            if (strtolower($ext) !== 'csv') {
                $this->jsonError('File harus berupa format CSV.'); return;
            }
            $liburModel = $this->model('LiburNasional');
            $result = $liburModel->importCsv($_FILES['file_csv']['tmp_name']);
            echo json_encode(['status' => $result['status'] ? 'success' : 'error', 'message' => $result['message']]);
        } else {
            $this->jsonError('File tidak ditemukan atau terjadi kesalahan upload.');
        }
    }

    // ==================== PENGUMUMAN ====================
    public function pengumuman() {
        $data['judul'] = 'Kelola Pengumuman | PT REN';
        $db = (new Database())->getConnection();
        
        $stmt = $db->query("SELECT * FROM pengumuman ORDER BY created_at DESC");
        $data['pengumuman'] = $stmt->fetchAll();

        $this->view('layouts/header', $data);
        $this->view('superadmin/pengumuman/index', $data);
        $this->view('layouts/footer');
    }

    public function simpan_pengumuman() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->jsonError('Invalid method.'); return; }
        
        $judul = $_POST['judul'];
        $isi = $_POST['isi'];
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        $id = $_POST['id_pengumuman'] ?? null;

        $db = (new Database())->getConnection();
        
        if ($id) {
            $stmt = $db->prepare("UPDATE pengumuman SET judul=:judul, isi=:isi, is_active=:is_active WHERE id_pengumuman=:id");
            $stmt->bindParam(':id', $id);
        } else {
            $stmt = $db->prepare("INSERT INTO pengumuman (judul, isi, is_active) VALUES (:judul, :isi, :is_active)");
        }
        $stmt->bindParam(':judul', $judul);
        $stmt->bindParam(':isi', $isi);
        $stmt->bindParam(':is_active', $is_active);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Pengumuman berhasil disimpan.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Gagal menyimpan pengumuman.']);
        }
    }
    
    public function hapus_pengumuman($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->jsonError('Invalid method.'); return; }
        $db = (new Database())->getConnection();
        $stmt = $db->prepare("DELETE FROM pengumuman WHERE id_pengumuman = :id");
        $stmt->bindParam(':id', $id);
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Pengumuman berhasil dihapus.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Gagal menghapus pengumuman.']);
        }
    }

    // ==================== HELPER ====================
    private function jsonError($msg) {
        echo json_encode(['status' => 'error', 'message' => $msg]);
    }
}
