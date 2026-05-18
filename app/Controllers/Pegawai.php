<?php

class Pegawai extends Controller {
    public function __construct() {
        // Cek apakah user sudah login
        if (!isset($_SESSION['user'])) {
            header('Location: ' . BASE_URL . '/auth');
            exit;
        }
        // Tolak akses jika bukan pegawai
        if ($_SESSION['user']['role'] !== 'pegawai') {
            header('Location: ' . BASE_URL . '/' . $_SESSION['user']['role']);
            exit;
        }
    }

    public function index() {
        $data['judul'] = 'Dashboard Pegawai | PT REN';

        $id_user = $_SESSION['user']['id_user'];
        $absensiModel = $this->model('Absensi');
        $insentifModel = $this->model('Insentif');

        // Ambil status absen hari ini & riwayat untuk ditampilkan di dashboard
        $data['absensi_hari_ini'] = $absensiModel->getAbsensiHariIni($id_user);
        $data['riwayat']          = $absensiModel->getRiwayat($id_user, 5);
        $data['riwayat_insentif'] = $insentifModel->getRiwayatPegawai($id_user, 6);

        // Ambil Pengumuman Aktif
        $db = (new Database())->getConnection();
        $stmt_pengumuman = $db->query("SELECT * FROM pengumuman WHERE is_active = 1 ORDER BY created_at DESC");
        $data['pengumuman'] = $stmt_pengumuman->fetchAll();

        $this->view('layouts/header', $data);
        $this->view('pegawai/dashboard', $data);
        $this->view('layouts/footer');
    }

    // Membuka halaman kamera (mode: masuk atau pulang)
    public function kamera($mode = 'masuk') {
        $data['judul'] = 'Ambil Absen | PT REN';
        $data['mode'] = $mode; // Kirim mode ke view
        $this->view('layouts/header', $data);
        $this->view('pegawai/kamera_absen', $data);
    }

    // API endpoint absen masuk (dipanggil via AJAX)
    public function simpan_absen() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['status' => 'error', 'message' => 'Metode tidak valid.']);
            return;
        }

        $lat = $_POST['latitude'];
        $lng = $_POST['longitude'];
        $foto_base64 = $_POST['foto'];
        $mode = $_POST['mode'] ?? 'masuk';

        $id_user = $_SESSION['user']['id_user'];
        $id_cabang = $_SESSION['user']['id_cabang'];

        $absensiModel = $this->model('Absensi');
        $cabang = $absensiModel->getInfoCabang($id_cabang);

        // Validasi Jarak/Radius (Haversine)
        $jarak = $absensiModel->hitungJarak($lat, $lng, $cabang['latitude'], $cabang['longitude']);
        if ($jarak > $cabang['radius_meter']) {
            echo json_encode(['status' => 'error', 'message' => 'Anda berada di luar radius kantor (' . round($jarak) . 'm dari pusat). Silakan mendekat.']);
            return;
        }

        // Simpan foto ke folder uploads
        $img = base64_decode(str_replace([' ', 'data:image/jpeg;base64,'], ['+', ''], $foto_base64));
        $nama_file = 'absen_' . $id_user . '_' . time() . '.jpg';
        $dir_uploads = PUBLIC_PATH . '/uploads/';
        if (!is_dir($dir_uploads)) mkdir($dir_uploads, 0777, true);
        file_put_contents($dir_uploads . $nama_file, $img);

        // Ambil timezone cabang (default WIB jika belum diset)
        $timezone = $cabang['timezone'] ?? 'Asia/Jakarta';

        // Pilih fungsi simpan berdasarkan mode absen
        if ($mode === 'pulang') {
            $result = $absensiModel->simpanAbsenPulang($id_user, $lat, $lng, $nama_file, $timezone);
        } else {
            $result = $absensiModel->simpanAbsenMasuk($id_user, $lat, $lng, $nama_file, $id_cabang, $timezone);
        }

        echo json_encode([
            'status' => $result['status'] ? 'success' : 'error',
            'message' => $result['message']
        ]);
    }

    // ==================== CUTI ====================
    public function cuti() {
        $data['judul'] = 'Pengajuan Cuti / Izin | PT REN';
        $cutiModel     = $this->model('Cuti');
        $data['riwayat_cuti'] = $cutiModel->getRiwayatPegawai($_SESSION['user']['id_user']);
        
        $this->view('layouts/header', $data);
        $this->view('pegawai/cuti', $data);
    }

    public function ajukan_cuti() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['status' => 'error', 'message' => 'Metode tidak valid.']);
            return;
        }

        $nama_file = null;
        if (isset($_FILES['bukti_foto']) && $_FILES['bukti_foto']['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES['bukti_foto']['name'], PATHINFO_EXTENSION);
            $nama_file = 'cuti_' . $_SESSION['user']['id_user'] . '_' . time() . '.' . $ext;
            $dir_uploads = PUBLIC_PATH . '/uploads/';
            if (!is_dir($dir_uploads)) mkdir($dir_uploads, 0777, true);
            move_uploaded_file($_FILES['bukti_foto']['tmp_name'], $dir_uploads . $nama_file);
        }

        $data_cuti = [
            'id_user'         => $_SESSION['user']['id_user'],
            'jenis'           => $_POST['jenis'],
            'tanggal_mulai'   => $_POST['tanggal_mulai'],
            'tanggal_selesai' => $_POST['tanggal_selesai'],
            'keterangan'      => $_POST['keterangan'],
            'bukti_foto'      => $nama_file
        ];

        $cutiModel = $this->model('Cuti');
        $result = $cutiModel->ajukanCuti($data_cuti);
        
        echo json_encode(['status' => $result['status'] ? 'success' : 'error', 'message' => $result['message']]);
    }

    // ==================== PENGGAJIAN ====================
    public function penggajian() {
        $data['judul'] = 'Slip Gaji Saya | PT REN';
        
        $id_user = $_SESSION['user']['id_user'];
        $db = (new Database())->getConnection();
        
        // Ambil riwayat gaji yang sudah PUBLISHED
        $stmt = $db->prepare("
            SELECT * FROM penggajian_bulanan 
            WHERE id_user = :id_user AND status = 'published'
            ORDER BY tahun DESC, bulan DESC
        ");
        $stmt->bindParam(':id_user', $id_user, PDO::PARAM_INT);
        $stmt->execute();
        $data['riwayat_gaji'] = $stmt->fetchAll();

        // Ambil detail User untuk ditampilkan di slip
        $userModel = $this->model('User');
        $data['pegawai'] = $userModel->getById($id_user);
        
        $this->view('layouts/header', $data);
        $this->view('pegawai/penggajian', $data);
        $this->view('layouts/footer');
    }

    // ==================== LEMBUR ====================
    public function lembur() {
        $data['judul'] = 'Pengajuan Lembur | PT REN';
        
        $lemburModel = $this->model('Lembur');
        $data['riwayat_lembur'] = $lemburModel->getRiwayatLemburPegawai($_SESSION['user']['id_user']);

        $this->view('layouts/header', $data);
        $this->view('pegawai/lembur', $data);
        $this->view('layouts/footer');
    }

    public function ajukan_lembur() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonError('Metode tidak valid.');
            return;
        }

        // Hitung durasi jam
        $mulai = strtotime($_POST['jam_mulai']);
        $selesai = strtotime($_POST['jam_selesai']);
        
        if ($selesai < $mulai) {
            // Asumsi lembur melewati tengah malam (ke hari berikutnya)
            $selesai += 86400; // tambah 1 hari (24 jam)
        }
        
        $durasi_detik = $selesai - $mulai;
        $durasi_jam = round($durasi_detik / 3600, 2);

        $data_lembur = [
            'id_user'     => $_SESSION['user']['id_user'],
            'id_cabang'   => $_SESSION['user']['id_cabang'],
            'tanggal'     => $_POST['tanggal'],
            'jam_mulai'   => $_POST['jam_mulai'],
            'jam_selesai' => $_POST['jam_selesai'],
            'durasi_jam'  => $durasi_jam,
            'keterangan'  => $_POST['keterangan']
        ];

        $lemburModel = $this->model('Lembur');
        $result = $lemburModel->ajukanLembur($data_lembur);
        
        echo json_encode(['status' => $result['status'] ? 'success' : 'error', 'message' => $result['message']]);
    }
}
