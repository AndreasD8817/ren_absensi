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

        // Cek Mode Pemeliharaan
        $maintenanceFile = APP_PATH . '/Config/maintenance.json';
        if (file_exists($maintenanceFile)) {
            $maintenanceData = json_decode(file_get_contents($maintenanceFile), true);
            if (isset($maintenanceData['is_maintenance']) && $maintenanceData['is_maintenance'] == true) {
                session_destroy();
                session_start();
                $_SESSION['flash_error'] = 'Sistem sedang dalam perbaikan rutin. Sesi Anda dihentikan sementara.';
                header('Location: ' . BASE_URL . '/auth');
                exit;
            }
        }
    }

    public function index() {
        $data['judul'] = 'Dashboard Pegawai | PT REN';

        $id_user = $_SESSION['user']['id_user'];
        $absensiModel = $this->model('Absensi');
        $insentifModel = $this->model('Insentif');

        $penggajianModel = $this->model('Penggajian');

        // Ambil status absen hari ini & riwayat untuk ditampilkan di dashboard
        $data['absensi_hari_ini'] = $absensiModel->getAbsensiHariIni($id_user);
        $data['kalender_absen']   = $penggajianModel->getDetailHarianLengkap($id_user, date('n'), date('Y'));
        $data['riwayat_insentif'] = $insentifModel->getRiwayatPegawai($id_user, 6);

        // Ambil info timezone cabang untuk jam digital real-time
        $cabang = $absensiModel->getInfoCabang($_SESSION['user']['id_cabang']);
        $timezone = $cabang['timezone'] ?? 'Asia/Jakarta';
        $dt = new DateTime('now', new DateTimeZone($timezone));
        
        $data['server_h'] = $dt->format('H');
        $data['server_m'] = $dt->format('i');
        $data['server_s'] = $dt->format('s');
        $data['server_date'] = $dt->format('Y-m-d');

        // Ambil Pengumuman Aktif
        $db = (new Database())->getConnection();
        $stmt_pengumuman = $db->query("SELECT id_pengumuman, id_cabang, judul, isi_pengumuman AS isi, is_active, created_at FROM pengumuman WHERE is_active = 1 ORDER BY created_at DESC");
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

        // Restrukturisasi Folder dan Nama Foto
        $nip = $_SESSION['user']['nip'] ?? $id_user; // Gunakan NIP jika ada
        $date_str = date('Ymd');
        $year = date('Y');
        $month = date('m');
        
        $nama_file_baru = "{$nip}_{$mode}_{$date_str}_" . time() . ".jpg";
        $folder_relatif = "{$year}/{$month}";
        
        $dir_uploads = PUBLIC_PATH . "/uploads/$folder_relatif/";
        if (!is_dir($dir_uploads)) mkdir($dir_uploads, 0777, true);
        
        // Simpan foto ke folder public/uploads/YYYY/MM/
        $img = base64_decode(str_replace([' ', 'data:image/jpeg;base64,'], ['+', ''], $foto_base64));
        file_put_contents($dir_uploads . $nama_file_baru, $img);

        // Nama file yang masuk ke Database (agar kompatibel dengan foto lama)
        $nama_file_db = "{$folder_relatif}/{$nama_file_baru}";

        // Ambil timezone cabang (default WIB jika belum diset)
        $timezone = $cabang['timezone'] ?? 'Asia/Jakarta';

        // Pilih fungsi simpan berdasarkan mode absen
        if ($mode === 'pulang') {
            $result = $absensiModel->simpanAbsenPulang($id_user, $lat, $lng, $nama_file_db, $timezone);
            if ($result['status']) catat_log('ABSEN_PULANG', 'Absensi', 'Melakukan absen pulang');
        } else {
            $result = $absensiModel->simpanAbsenMasuk($id_user, $lat, $lng, $nama_file_db, $id_cabang, $timezone);
            if ($result['status']) catat_log('ABSEN_MASUK', 'Absensi', 'Melakukan absen masuk');
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
        $data['sisa_cuti'] = $cutiModel->getSisaCutiTahunan($_SESSION['user']['id_user'], date('Y'));
        
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
        
        if ($result['status']) {
            catat_log('CREATE', 'Cuti', 'Mengajukan ' . $_POST['jenis'] . ' dari ' . $_POST['tanggal_mulai']);
        }
        
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
        $data['pegawai'] = $userModel->getPegawaiById($id_user);
        
        $this->view('layouts/header', $data);
        $this->view('pegawai/penggajian', $data);
        $this->view('layouts/footer');
    }

    public function detail_denda() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') { $this->jsonError('Metode tidak valid.'); return; }
        
        $id_user = $_SESSION['user']['id_user'];
        $bulan   = $_GET['bulan'] ?? date('n');
        $tahun   = $_GET['tahun'] ?? date('Y');

        $userModel = $this->model('User');
        $user = $userModel->getPegawaiById($id_user);

        $cabangModel = $this->model('Cabang');
        $cabang = $cabangModel->getById($user['id_cabang']);

        $absensiModel = $this->model('Absensi');
        $detail = $absensiModel->getDetailHarianUser($id_user, $bulan, $tahun);

        $penggajianModel = $this->model('Penggajian');
        
        $rincian = [];
        $total_denda = 0;
        foreach ($detail as $d) {
            // Cek Keterlambatan
            if ($d['status'] === 'telat') {
                $denda_per_kejadian = $penggajianModel->hitungDendaTelat($d['menit_terlambat'], $cabang);
                $d_telat = $d;
                $d_telat['jenis_denda'] = "Terlambat ({$d['menit_terlambat']} menit)";
                $d_telat['denda'] = $denda_per_kejadian;
                $total_denda += $denda_per_kejadian;
                $rincian[] = $d_telat;
            }
            // Cek Lupa Absen Pulang
            if ($d['jam_masuk'] !== null && $d['jam_pulang'] === null) {
                $d_lupa = $d;
                $d_lupa['jenis_denda'] = "Lupa Absen Pulang";
                $d_lupa['denda'] = $cabang['denda_tidak_absen_pulang'];
                $total_denda += $cabang['denda_tidak_absen_pulang'];
                $rincian[] = $d_lupa;
            }
        }

        // Tambahkan Rangkuman Alfa
        $ringkasan = $penggajianModel->getRingkasanAbsensi($bulan, $tahun, $user['id_cabang']);
        $total_alfa = 0;
        foreach($ringkasan as $r) {
            if ($r['id_user'] == $id_user) {
                $total_alfa = $r['total_alfa'] ?? 0;
                break;
            }
        }

        if ($total_alfa > 0) {
            $denda_alfa_total = $total_alfa * $cabang['denda_alfa'];
            $total_denda += $denda_alfa_total;
            $rincian[] = [
                'tanggal' => null,
                'jenis_denda' => "Alfa / Tidak Masuk Kerja ($total_alfa Hari)",
                'jam_masuk' => '-',
                'denda' => $denda_alfa_total,
                'is_alfa_summary' => true
            ];
        }

        echo json_encode(['status' => 'success', 'data' => $rincian, 'total_denda' => $total_denda]);
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
        
        if ($result['status']) {
            catat_log('CREATE', 'Lembur', 'Mengajukan lembur pada ' . $_POST['tanggal']);
        }
        
        echo json_encode(['status' => $result['status'] ? 'success' : 'error', 'message' => $result['message']]);
    }
}
