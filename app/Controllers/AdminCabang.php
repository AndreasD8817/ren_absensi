<?php

class AdminCabang extends Controller {
    private $id_cabang;

    public function __construct() {
        if (!isset($_SESSION['user'])) {
            header('Location: ' . BASE_URL . '/auth');
            exit;
        }
        if ($_SESSION['user']['role'] !== 'admin_cabang') {
            $roleUrl = $_SESSION['user']['role'] === 'admin_cabang' ? 'admincabang' : $_SESSION['user']['role'];
            header('Location: ' . BASE_URL . '/' . $roleUrl);
            exit;
        }
        $this->id_cabang = $_SESSION['user']['id_cabang'];
    }

    // ==================== DASHBOARD ====================
    public function index() {
        $data['judul'] = 'Dashboard Admin Cabang | PT REN';
        
        $absensiModel = $this->model('Absensi');
        $cabangModel  = $this->model('Cabang');
        $cutiModel    = $this->model('Cuti');
        
        $data['cabang']       = $cabangModel->getById($this->id_cabang);
        $data['statistik']    = $absensiModel->getStatistikHariIniCabang($this->id_cabang);
        $data['belum_absen']  = $absensiModel->getPegawaiBelumAbsenCabang($this->id_cabang);
        $data['cuti_pending'] = $cutiModel->getPengajuanByCabang($this->id_cabang);

        $tren_raw            = $absensiModel->getTren7HariCabang($this->id_cabang);
        $data['tren_labels'] = json_encode(array_column($tren_raw, 'tanggal'));
        $data['tren_hadir']  = json_encode(array_column($tren_raw, 'hadir'));
        $data['tren_telat']  = json_encode(array_column($tren_raw, 'telat'));

        $this->view('layouts/header', $data);
        $this->view('admin_cabang/dashboard', $data);
        $this->view('layouts/footer');
    }

    // ==================== PENGATURAN CABANG ====================
    public function pengaturan() {
        $data['judul']  = 'Pengaturan Cabang | PT REN';
        $cabangModel    = $this->model('Cabang');
        $data['cabang'] = $cabangModel->getById($this->id_cabang);

        $this->view('layouts/header', $data);
        $this->view('admin_cabang/pengaturan', $data);
        $this->view('layouts/footer');
    }

    // Hanya boleh update denda, tidak boleh radius/koordinat
    public function simpan_pengaturan() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->jsonError('Metode tidak valid.'); return; }
        
        $cabangModel = $this->model('Cabang');
        // Ambil data lama agar latitude, longitude, radius, dan nama tidak berubah
        $lama = $cabangModel->getById($this->id_cabang);
        
        $update_data = [
            'id_cabang'            => $this->id_cabang,
            'nama_cabang'          => $lama['nama_cabang'],
            'latitude'             => $lama['latitude'],
            'longitude'            => $lama['longitude'],
            'radius_meter'         => $lama['radius_meter'],
            'denda_1_5'            => $_POST['denda_1_5'],
            'denda_6_10'           => $_POST['denda_6_10'],
            'denda_11_15'          => $_POST['denda_11_15'],
            'denda_16_30'          => $_POST['denda_16_30'],
            'denda_31_60'          => $_POST['denda_31_60'],
            'denda_alfa'           => $_POST['denda_alfa'],
            'tarif_lembur_per_jam' => $_POST['tarif_lembur_per_jam']
        ];

        $result = $cabangModel->update($update_data);
        echo json_encode(['status' => $result['status'] ? 'success' : 'error', 'message' => $result['message']]);
    }

    // ==================== KELOLA CUTI ====================
    public function cuti() {
        $data['judul'] = 'Persetujuan Cuti & Izin | PT REN';
        $cutiModel = $this->model('Cuti');
        
        $data['list_cuti'] = $cutiModel->getPengajuanByCabang($this->id_cabang);

        $this->view('layouts/header', $data);
        $this->view('admin_cabang/cuti', $data);
        $this->view('layouts/footer');
    }

    public function respon_cuti() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->jsonError('Metode tidak valid.'); return; }
        
        $cutiModel = $this->model('Cuti');
        $id_cuti = $_POST['id_cuti'];
        $status  = $_POST['status']; // 'approved' atau 'rejected'
        $id_admin = $_SESSION['user']['id_user'];

        $result = $cutiModel->responCuti($id_cuti, $id_admin, $status);
        if ($result) {
            echo json_encode(['status' => 'success', 'message' => 'Berhasil memberikan respon pada pengajuan cuti/izin.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Gagal memberikan respon.']);
        }
    }

    // ==================== PERSETUJUAN LEMBUR ====================
    public function lembur() {
        $data['judul'] = 'Persetujuan Lembur | PT REN';
        $lemburModel = $this->model('Lembur');
        
        $data['list_lembur'] = $lemburModel->getPengajuanByCabang($this->id_cabang);

        $this->view('layouts/header', $data);
        $this->view('admin_cabang/lembur', $data);
        $this->view('layouts/footer');
    }

    public function respon_lembur() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->jsonError('Metode tidak valid.'); return; }
        
        $lemburModel = $this->model('Lembur');
        $id_lembur = $_POST['id_lembur'];
        $status  = $_POST['status']; // 'approved' atau 'rejected'
        $alasan = $_POST['alasan_reject'] ?? null;
        $id_admin = $_SESSION['user']['id_user'];

        $result = $lemburModel->responLembur($id_lembur, $id_admin, $status, $alasan);
        if ($result) {
            echo json_encode(['status' => 'success', 'message' => 'Berhasil memberikan respon pengajuan lembur.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Gagal memberikan respon.']);
        }
    }

    // ==================== KAS DENDA (READ-ONLY) ====================
    public function kas_denda() {
        $data['judul'] = 'Monitoring Kas Denda | PT REN';
        $kasModel      = $this->model('KasDenda');
        
        $data['rekap']   = $kasModel->getRekapCabang($this->id_cabang);
        $data['riwayat'] = $kasModel->getRiwayatCabang($this->id_cabang);

        $this->view('layouts/header', $data);
        $this->view('admin_cabang/kas_denda/index', $data);
        $this->view('layouts/footer');
    }

    // ==================== LIBUR LOKAL ====================
    public function libur_lokal() {
        $data['judul'] = 'Kelola Libur Lokal | PT REN';
        $liburModel = $this->model('LiburCabang');
        $data['libur'] = $liburModel->getByCabang($this->id_cabang);

        $this->view('layouts/header', $data);
        $this->view('admin_cabang/libur_lokal', $data);
        $this->view('layouts/footer');
    }

    public function simpan_libur_lokal() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->jsonError('Metode tidak valid.'); return; }
        $liburModel = $this->model('LiburCabang');
        
        $data = $_POST;
        $data['id_cabang'] = $this->id_cabang; // Paksa gunakan ID cabang yang login
        
        $result = $liburModel->simpan($data);
        echo json_encode(['status' => $result['status'] ? 'success' : 'error', 'message' => $result['message']]);
    }

    public function hapus_libur_lokal($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->jsonError('Metode tidak valid.'); return; }
        $liburModel = $this->model('LiburCabang');
        if ($liburModel->hapus($id, $this->id_cabang)) {
            echo json_encode(['status' => 'success', 'message' => 'Hari libur lokal berhasil dihapus.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Gagal menghapus hari libur lokal.']);
        }
    }

    // ==================== HELPER ====================
    private function jsonError($msg) {
        echo json_encode(['status' => 'error', 'message' => $msg]);
    }
}
