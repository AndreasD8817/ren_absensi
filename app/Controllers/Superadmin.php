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
        if ($result['status']) catat_log('CREATE', 'Pegawai', 'Menambahkan pegawai NIP ' . $_POST['nip']);
        echo json_encode(['status' => $result['status'] ? 'success' : 'error', 'message' => $result['message']]);
    }

    public function update_pegawai() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->jsonError('Metode tidak valid.'); return; }
        $userModel = $this->model('User');
        $result    = $userModel->updatePegawai($_POST);
        if ($result['status']) catat_log('UPDATE', 'Pegawai', 'Mengubah data pegawai NIP ' . $_POST['nip']);
        echo json_encode(['status' => $result['status'] ? 'success' : 'error', 'message' => $result['message']]);
    }

    public function toggle_pegawai() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->jsonError('Metode tidak valid.'); return; }
        $userModel = $this->model('User');
        $result    = $userModel->toggleStatusPegawai($_POST['id_user']);
        if ($result) catat_log('UPDATE', 'Pegawai', 'Mengubah status aktif pegawai ID ' . $_POST['id_user']);
        echo json_encode(['status' => $result ? 'success' : 'error', 'message' => $result ? 'Status pegawai berhasil diubah.' : 'Gagal mengubah status.']);
    }

    public function hapus_pegawai() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->jsonError('Metode tidak valid.'); return; }
        $userModel = $this->model('User');
        $result    = $userModel->hapusPegawai($_POST['id_user']);
        if ($result) catat_log('DELETE', 'Pegawai', 'Menghapus permanen pegawai ID ' . $_POST['id_user']);
        echo json_encode(['status' => $result ? 'success' : 'error', 'message' => $result ? 'Pegawai beserta data dan fotonya berhasil dihapus permanen.' : 'Gagal menghapus pegawai.']);
    }

    public function download_template_pegawai() {
        $filename = "Template_Import_Pegawai.csv";
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        $output = fopen('php://output', 'w');
        // Header CSV
        fputcsv($output, ['NIP', 'Nama Lengkap', 'Username', 'Password', 'Role', 'ID Cabang', 'Jabatan', 'Gaji Pokok', 'Tipe Lembur', 'Tunj Jabatan', 'Tunj Transport', 'Tunj Makan', 'Tunj Kehadiran', 'Tunj Lainnya']);
        // Contoh Data
        fputcsv($output, ['12345678', 'Budi Santoso', 'budi_s', 'password123', 'pegawai', '1', 'Staff IT', '5000000', 'Non-Project', '500000', '200000', '300000', '100000', '0']);
        fputcsv($output, ['87654321', 'Siti Aminah', 'siti_a', 'password123', 'admin_cabang', '2', 'HR Cabang', '6000000', 'Project', '700000', '250000', '350000', '150000', '50000']);
        fclose($output);
        exit;
    }

    public function download_template_libur() {
        $filename = "Template_Import_Libur.csv";
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        $output = fopen('php://output', 'w');
        // Header CSV
        fputcsv($output, ['Tanggal', 'Keterangan']);
        // Contoh Data
        fputcsv($output, ['2026-08-17', 'Hari Kemerdekaan RI']);
        fputcsv($output, ['2026-12-25', 'Hari Raya Natal']);
        fclose($output);
        exit;
    }

    public function import_pegawai_csv() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->jsonError('Metode tidak valid.'); return; }
        if (!isset($_FILES['file_csv']) || $_FILES['file_csv']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['status' => 'error', 'message' => 'Gagal mengunggah file. Pastikan file dipilih.']);
            return;
        }

        $fileTmp = $_FILES['file_csv']['tmp_name'];
        $handle = fopen($fileTmp, 'r');
        if (!$handle) {
            echo json_encode(['status' => 'error', 'message' => 'Tidak dapat membaca file CSV.']);
            return;
        }

        $db = (new Database())->getConnection();
        $userModel = $this->model('User');
        $headerSkipped = false;
        $successCount = 0;
        $errorRows = [];
        $rowNum = 1;

        while (($data = fgetcsv($handle)) !== FALSE) {
            if (!$headerSkipped) { $headerSkipped = true; $rowNum++; continue; }
            if (count($data) < 14) {
                $errorRows[] = "Baris $rowNum: Format kolom tidak lengkap (Butuh 14 kolom).";
                $rowNum++; continue;
            }

            $nip = trim($data[0]);
            $nama = trim($data[1]);
            $username = trim($data[2]);
            $password = trim($data[3]);
            $role = trim(strtolower($data[4]));
            $id_cabang = (int)trim($data[5]);
            $jabatan = trim($data[6]);
            $gaji = (int)str_replace(['.', ','], '', trim($data[7]));
            $tipe_lembur = trim($data[8]);
            
            $t_jabatan = (int)str_replace(['.', ','], '', trim($data[9]));
            $t_transport = (int)str_replace(['.', ','], '', trim($data[10]));
            $t_makan = (int)str_replace(['.', ','], '', trim($data[11]));
            $t_hadir = (int)str_replace(['.', ','], '', trim($data[12]));
            $t_lainnya = (int)str_replace(['.', ','], '', trim($data[13]));

            if (empty($nip) || empty($password)) {
                $errorRows[] = "Baris $rowNum: NIP dan Password wajib diisi.";
                $rowNum++; continue;
            }

            // Validasi NIP
            $stmt = $db->prepare("SELECT id_user FROM users WHERE nip = :nip");
            $stmt->execute([':nip' => $nip]);
            if ($stmt->fetch()) {
                $errorRows[] = "Baris $rowNum: NIP ($nip) sudah terdaftar.";
                $rowNum++; continue;
            }

            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt_in = $db->prepare("
                INSERT INTO users (nip, nama_lengkap, password, role, id_cabang, jabatan, gaji_pokok, tipe_lembur, tunj_jabatan, tunj_transportasi, tunj_makan, tunj_kehadiran, tunj_lainnya, is_active)
                VALUES (:nip, :nama, :pass, :role, :id_cabang, :jabatan, :gaji, :tipe, :tj, :tt, :tm, :th, :tl, 1)
            ");
            
            try {
                $stmt_in->execute([
                    ':nip' => $nip, ':nama' => $nama, ':pass' => $hashed_password,
                    ':role' => $role, ':id_cabang' => $id_cabang, ':jabatan' => $jabatan, ':gaji' => $gaji, ':tipe' => $tipe_lembur,
                    ':tj' => $t_jabatan, ':tt' => $t_transport, ':tm' => $t_makan, ':th' => $t_hadir, ':tl' => $t_lainnya
                ]);
                $successCount++;
            } catch (Exception $e) {
                $errorRows[] = "Baris $rowNum: Gagal menyimpan ke database.";
            }
            $rowNum++;
        }
        fclose($handle);

        $msg = "Berhasil mengimpor $successCount pegawai.";
        if (count($errorRows) > 0) {
            $msg .= " Namun terdapat error pada beberapa baris:<br>" . implode("<br>", array_slice($errorRows, 0, 5));
            if (count($errorRows) > 5) $msg .= "<br>...dan " . (count($errorRows) - 5) . " error lainnya.";
        }
        
        catat_log('CREATE', 'Pegawai', "Import CSV: $successCount berhasil ditambahkan.");
        echo json_encode(['status' => 'success', 'message' => $msg]);
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
        if ($result['status']) catat_log('CREATE', 'Cabang', 'Menambahkan cabang baru: ' . $_POST['nama_cabang']);
        echo json_encode(['status' => $result['status'] ? 'success' : 'error', 'message' => $result['message']]);
    }

    public function update_cabang() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->jsonError('Metode tidak valid.'); return; }
        $cabangModel = $this->model('Cabang');
        $result      = $cabangModel->update($_POST);
        if ($result['status']) catat_log('UPDATE', 'Cabang', 'Mengubah data cabang: ' . $_POST['nama_cabang']);
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
        $id_cabang = $_GET['id_cabang'] ?? null;

        $data['judul']     = 'Data Absensi | PT REN';
        $data['bulan']     = (int)$bulan;
        $data['tahun']     = (int)$tahun;
        $data['id_cabang'] = $id_cabang ? (int)$id_cabang : null;

        $cabangModel       = $this->model('Cabang');
        $data['list_cabang'] = $cabangModel->getAll();

        $penggajianModel    = $this->model('Penggajian');
        $data['ringkasan']  = $penggajianModel->getRingkasanAbsensi($bulan, $tahun, $data['id_cabang']);

        $this->view('layouts/header', $data);
        $this->view('superadmin/absensi/index', $data);
        $this->view('layouts/footer');
    }

    public function detail_absensi_user() {
        $id_user = $_GET['id_user'] ?? 0;
        $bulan   = $_GET['bulan'] ?? date('n');
        $tahun   = $_GET['tahun'] ?? date('Y');

        $penggajianModel = $this->model('Penggajian');
        $detail = $penggajianModel->getDetailHarianLengkap($id_user, $bulan, $tahun);
        echo json_encode(['status' => 'success', 'data' => $detail]);
    }

    public function ringkasan_absensi_user() {
        $id_user = $_GET['id_user'] ?? 0;
        $bulan   = $_GET['bulan'] ?? date('n');
        $tahun   = $_GET['tahun'] ?? date('Y');

        $penggajianModel = $this->model('Penggajian');
        $semua_ringkasan = $penggajianModel->getRingkasanAbsensi($bulan, $tahun);
        
        $data_user = null;
        foreach ($semua_ringkasan as $r) {
            if ($r['id_user'] == $id_user) {
                $data_user = $r;
                break;
            }
        }

        if ($data_user) {
            echo json_encode(['status' => 'success', 'data' => $data_user]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Data tidak ditemukan']);
        }
    }

    private function upload_dan_kompres($file, $nip, $mode, $tanggal) {
        $year = date('Y', strtotime($tanggal));
        $month = date('m', strtotime($tanggal));
        $date_str = date('Ymd', strtotime($tanggal));
        
        $dir_uploads = PUBLIC_PATH . "/uploads/$year/$month/";
        if (!is_dir($dir_uploads)) mkdir($dir_uploads, 0777, true);
        
        $filename = "{$nip}_{$mode}_{$date_str}_" . time() . ".jpg";
        $destination = $dir_uploads . $filename;
        
        $info = getimagesize($file['tmp_name']);
        if (!$info) return null;
        
        if ($info['mime'] == 'image/jpeg') $image = imagecreatefromjpeg($file['tmp_name']);
        elseif ($info['mime'] == 'image/png') $image = imagecreatefrompng($file['tmp_name']);
        else return null; 
        
        $max_width = 600;
        $width = imagesx($image);
        $height = imagesy($image);
        if ($width > $max_width) {
            $new_width = $max_width;
            $new_height = floor($height * ($max_width / $width));
            $tmp_img = imagecreatetruecolor($new_width, $new_height);
            imagecopyresampled($tmp_img, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
            $image = $tmp_img;
        }
        
        imagejpeg($image, $destination, 60);
        imagedestroy($image);
        
        return "$year/$month/$filename";
    }

    public function edit_absensi() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->jsonError('Metode tidak valid.'); return; }
        
        $id_absensi = $_POST['id_absensi'];
        $absensiModel = $this->model('Absensi');
        
        // Ambil data referensi (Tanggal, NIP, Cabang)
        $db = (new Database())->getConnection();
        $stmt = $db->prepare("SELECT a.tanggal, u.nip, u.id_cabang FROM absensi a JOIN users u ON a.id_user = u.id_user WHERE a.id_absensi = :id");
        $stmt->bindParam(':id', $id_absensi);
        $stmt->execute();
        $info = $stmt->fetch();
        
        if (!$info) {
            $this->jsonError('Data absensi tidak ditemukan.'); return;
        }
        
        $tanggal = $info['tanggal'];
        $nip = $info['nip'] ? $info['nip'] : 'MANUAL';
        $id_cabang = $info['id_cabang'];
        
        // Proses Upload Foto (Sesuai Konvensi Nama App Mobile)
        if (isset($_FILES['foto_masuk']) && $_FILES['foto_masuk']['error'] === UPLOAD_ERR_OK) {
            $f_masuk = $this->upload_dan_kompres($_FILES['foto_masuk'], $nip, 'masuk', $tanggal);
            if ($f_masuk) $_POST['foto_masuk_baru'] = $f_masuk;
        }
        if (isset($_FILES['foto_pulang']) && $_FILES['foto_pulang']['error'] === UPLOAD_ERR_OK) {
            $f_pulang = $this->upload_dan_kompres($_FILES['foto_pulang'], $nip, 'pulang', $tanggal);
            if ($f_pulang) $_POST['foto_pulang_baru'] = $f_pulang;
        }

        // Proses Pemetaan Lokasi (Manual vs Otomatis)
        if (isset($_POST['tipe_lokasi_masuk'])) {
            if ($_POST['tipe_lokasi_masuk'] === 'otomatis') {
                $cabang = $absensiModel->getInfoCabang($id_cabang);
                $_POST['lat_masuk_baru'] = $cabang['latitude'];
                $_POST['lng_masuk_baru'] = $cabang['longitude'];
            } elseif ($_POST['tipe_lokasi_masuk'] === 'manual') {
                $_POST['lat_masuk_baru'] = $_POST['lat_masuk'];
                $_POST['lng_masuk_baru'] = $_POST['lng_masuk'];
            }
        }
        
        if (isset($_POST['tipe_lokasi_pulang'])) {
            if ($_POST['tipe_lokasi_pulang'] === 'otomatis') {
                $cabang = $absensiModel->getInfoCabang($id_cabang);
                $_POST['lat_pulang_baru'] = $cabang['latitude'];
                $_POST['lng_pulang_baru'] = $cabang['longitude'];
            } elseif ($_POST['tipe_lokasi_pulang'] === 'manual') {
                $_POST['lat_pulang_baru'] = $_POST['lat_pulang'];
                $_POST['lng_pulang_baru'] = $_POST['lng_pulang'];
            }
        }

        $result = $absensiModel->editAbsensi($_POST);
        if ($result) {
            catat_log('UPDATE', 'Absensi', "Memodifikasi absensi ID: $id_absensi menjadi status: " . $_POST['status']);
        }
        echo json_encode(['status' => $result ? 'success' : 'error', 'message' => $result ? 'Data absensi berhasil diperbarui.' : 'Gagal memperbarui data.']);
    }

    public function detail_denda_telat() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') { $this->jsonError('Metode tidak valid.'); return; }
        $id_user = $_GET['id_user'] ?? 0;
        $bulan   = $_GET['bulan'] ?? date('n');
        $tahun   = $_GET['tahun'] ?? date('Y');

        $userModel = $this->model('User');
        $user = $userModel->getPegawaiById($id_user);
        if (!$user) { $this->jsonError('Pegawai tidak ditemukan.'); return; }

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
            // Cek Lupa Absen Pulang (Ada jam masuk, tapi tidak ada jam pulang)
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

        $cabangModel       = $this->model('Cabang');
        $data['list_cabang'] = $cabangModel->getAll();

        $this->view('layouts/header', $data);
        $this->view('superadmin/penggajian/index', $data);
        $this->view('layouts/footer');
    }

    public function generate_gaji() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->jsonError('Metode tidak valid.'); return; }
        $penggajianModel = $this->model('Penggajian');
        $id_cabang = empty($_POST['id_cabang']) ? null : $_POST['id_cabang'];
        $is_pph21_active = isset($_POST['is_pph21_active']) ? (int)$_POST['is_pph21_active'] : 1;
        $result = $penggajianModel->generateGaji($_POST['bulan'], $_POST['tahun'], $id_cabang, $is_pph21_active);
        catat_log('CREATE', 'Penggajian', 'Melakukan generate gaji periode ' . $_POST['bulan'] . '/' . $_POST['tahun'] . ($id_cabang ? ' untuk cabang ID ' . $id_cabang : ' untuk semua cabang'));
        echo json_encode(['status' => 'success', 'message' => $result['message'], 'total' => $result['total']]);
    }

    public function publish_gaji() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->jsonError('Metode tidak valid.'); return; }
        
        $currentMonth = (int)date('n');
        $currentYear = (int)date('Y');
        $selectedMonth = (int)$_POST['bulan'];
        $selectedYear = (int)$_POST['tahun'];
        
        if ($selectedYear > $currentYear || ($selectedYear === $currentYear && $selectedMonth >= $currentMonth)) {
            echo json_encode(['status' => 'error', 'message' => 'Gaji hanya dapat dipublikasikan untuk bulan yang sudah berlalu.']);
            return;
        }

        $penggajianModel = $this->model('Penggajian');
        $id_cabang = empty($_POST['id_cabang']) || $_POST['id_cabang'] === 'all' ? null : $_POST['id_cabang'];
        $penggajianModel->publishGaji($_POST['bulan'], $_POST['tahun'], $id_cabang);
        catat_log('UPDATE', 'Penggajian', 'Mempublikasikan gaji periode ' . $_POST['bulan'] . '/' . $_POST['tahun'] . ($id_cabang ? ' untuk cabang ID ' . $id_cabang : ' untuk semua cabang'));
        $msg = $id_cabang ? 'Gaji cabang terpilih berhasil dipublikasikan!' : 'Gaji berhasil dipublikasikan ke semua pegawai!';
        echo json_encode(['status' => 'success', 'message' => $msg]);
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
        if ($result['status']) catat_log('CREATE', 'Insentif', 'Menambahkan insentif: ' . $_POST['keterangan']);
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
        if ($result['status']) catat_log('UPDATE', 'Insentif', 'Mempublikasikan insentif ID ' . $_POST['id_insentif']);
        echo json_encode(['status' => $result['status'] ? 'success' : 'error', 'message' => $result['message']]);
    }

    public function hapus_insentif() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->jsonError('Metode tidak valid.'); return; }
        $insentifModel = $this->model('Insentif');
        $result = $insentifModel->hapus($_POST['id_insentif']);
        if ($result['status']) catat_log('DELETE', 'Insentif', 'Menghapus insentif ID ' . $_POST['id_insentif']);
        echo json_encode(['status' => $result['status'] ? 'success' : 'error', 'message' => $result['message']]);
    }

    public function detail_insentif() {
        if (!isset($_GET['id'])) { $this->jsonError('ID tidak valid.'); return; }
        $insentifModel = $this->model('Insentif');
        $detail = $insentifModel->getDetailPegawai($_GET['id']);
        echo json_encode(['status' => 'success', 'data' => $detail]);
    }

    // ==================== BONUS & THR ====================
    public function bonus_thr() {
        $bulan  = $_GET['bulan'] ?? date('n');
        $tahun  = $_GET['tahun'] ?? date('Y');

        $data['judul']      = 'Kelola Bonus & THR | PT REN';
        $data['bulan']      = (int)$bulan;
        $data['tahun']      = (int)$tahun;

        $bonusModel         = $this->model('BonusThr');
        $userModel          = $this->model('User');
        
        $data['list_bonus'] = $bonusModel->getBonusByBulanTahun($bulan, $tahun);
        $semua = $userModel->getAllPegawai();
        $data['semua_pegawai'] = array_filter($semua, function($u) { return $u['role'] === 'pegawai'; });

        $this->view('layouts/header', $data);
        $this->view('superadmin/bonus_thr/index', $data);
        $this->view('layouts/footer');
    }

    public function simpan_bonus_manual() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->jsonError('Metode tidak valid.'); return; }
        $bonusModel = $this->model('BonusThr');
        $result = $bonusModel->simpanBonusManual($_POST);
        if ($result['status']) catat_log('CREATE', 'BonusThr', 'Menambahkan bonus/THR manual untuk user ID ' . $_POST['id_user']);
        echo json_encode(['status' => $result['status'] ? 'success' : 'error', 'message' => $result['message']]);
    }

    public function download_template_bonus() {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=Template_Bonus_THR_' . date('Ymd') . '.csv');
        $output = fopen('php://output', 'w');
        fputcsv($output, ['ID User (JANGAN DIUBAH)', 'NIK', 'Nama Pegawai', 'Cabang', 'Nominal Bonus/THR', 'Keterangan']);
        
        $userModel = $this->model('User');
        $semua = $userModel->getAllPegawai();
        $pegawai = array_filter($semua, function($u) { return $u['role'] === 'pegawai'; });
        
        foreach ($pegawai as $p) {
            fputcsv($output, [$p['id_user'], $p['nip'], $p['nama_lengkap'], $p['nama_cabang'], 0, 'THR / Bonus Akhir Tahun']);
        }
        fclose($output);
        exit;
    }

    public function upload_bonus_csv() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['file_csv'])) {
            echo json_encode(['status' => 'error', 'message' => 'Upload tidak valid.']); return;
        }

        $file = $_FILES['file_csv']['tmp_name'];
        if (($handle = fopen($file, "r")) !== FALSE) {
            $header = fgetcsv($handle, 1000, ","); // Skip header
            $data_insert = [];
            $bulan = (int)$_POST['bulan'];
            $tahun = (int)$_POST['tahun'];

            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                // Ensure data has enough columns and nominal > 0
                if (isset($data[0]) && isset($data[4]) && (float)$data[4] > 0) {
                    $data_insert[] = [
                        'id_user' => (int)$data[0],
                        'bulan' => $bulan,
                        'tahun' => $tahun,
                        'nominal' => (float)$data[4],
                        'keterangan' => isset($data[5]) ? trim($data[5]) : 'Bonus/THR'
                    ];
                }
            }
            fclose($handle);

            if (empty($data_insert)) {
                echo json_encode(['status' => 'error', 'message' => 'Tidak ada data valid dengan nominal > 0 di CSV tersebut.']); return;
            }

            $bonusModel = $this->model('BonusThr');
            $result = $bonusModel->simpanBonusMassal($data_insert);
            
            if ($result['status']) catat_log('CREATE', 'BonusThr', 'Upload CSV Bonus/THR sebanyak ' . count($data_insert) . ' data');
            echo json_encode(['status' => $result['status'] ? 'success' : 'error', 'message' => $result['message']]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Gagal membaca file CSV.']);
        }
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
        if ($result['status']) catat_log('CREATE', 'KasDenda', 'Menambahkan transaksi kas ' . $_POST['jenis'] . ' sebesar ' . $_POST['nominal'] . ' untuk cabang ID ' . $_POST['id_cabang']);
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
        if ($result['status']) catat_log('CREATE', 'Libur', 'Menambahkan libur nasional pada ' . $_POST['tanggal']);
        echo json_encode(['status' => $result['status'] ? 'success' : 'error', 'message' => $result['message']]);
    }

    public function hapus_libur_nasional($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->jsonError('Metode tidak valid.'); return; }
        $liburModel = $this->model('LiburNasional');
        if ($liburModel->hapus($id)) {
            catat_log('DELETE', 'Libur', 'Menghapus libur nasional ID ' . $id);
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
        
        $stmt = $db->query("SELECT id_pengumuman, id_cabang, judul, isi_pengumuman AS isi, is_active, created_at FROM pengumuman ORDER BY created_at DESC");
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
        
        try {
            if ($id) {
                $stmt = $db->prepare("UPDATE pengumuman SET judul=:judul, isi_pengumuman=:isi, is_active=:is_active WHERE id_pengumuman=:id");
                $stmt->bindParam(':id', $id);
            } else {
                $stmt = $db->prepare("INSERT INTO pengumuman (judul, isi_pengumuman, is_active) VALUES (:judul, :isi, :is_active)");
            }
            $stmt->bindParam(':judul', $judul);
            $stmt->bindParam(':isi', $isi);
            $stmt->bindParam(':is_active', $is_active);

            if ($stmt->execute()) {
                catat_log($id ? 'UPDATE' : 'CREATE', 'Pengumuman', ($id ? 'Mengubah' : 'Menambahkan') . ' pengumuman: ' . $judul);
                echo json_encode(['status' => 'success', 'message' => 'Pengumuman berhasil disimpan.']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Gagal menyimpan pengumuman.']);
            }
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => 'Kesalahan database: ' . $e->getMessage()]);
        }
    }
    
    public function hapus_pengumuman($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->jsonError('Invalid method.'); return; }
        $db = (new Database())->getConnection();
        $stmt = $db->prepare("DELETE FROM pengumuman WHERE id_pengumuman = :id");
        $stmt->bindParam(':id', $id);
        if ($stmt->execute()) {
            catat_log('DELETE', 'Pengumuman', 'Menghapus pengumuman ID ' . $id);
            echo json_encode(['status' => 'success', 'message' => 'Pengumuman berhasil dihapus.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Gagal menghapus pengumuman.']);
        }
    }

    // ==================== LOG AKTIVITAS ====================
    public function audit_log() {
        $data['judul'] = 'Log Aktivitas Sistem | PT REN';
        $auditModel = $this->model('Audit');
        
        $data['logs'] = $auditModel->getSemuaLog();

        $this->view('layouts/header', $data);
        $this->view('superadmin/audit_log', $data);
        $this->view('layouts/footer');
    }

    // ==================== PENGATURAN & BACKUP ====================
    public function toggle_maintenance() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->jsonError('Metode tidak valid.'); return; }
        $status = $_POST['status'] === '1';
        $file = APP_PATH . '/Config/maintenance.json';
        file_put_contents($file, json_encode(['is_maintenance' => $status]));
        catat_log('SISTEM', 'Pengaturan', 'Mengubah status Mode Pemeliharaan menjadi: ' . ($status ? 'AKTIF' : 'MATI'));
        echo json_encode(['status' => 'success']);
    }

    public function hitung_kapasitas() {
        $dir = PUBLIC_PATH . '/uploads';
        $size = 0;
        if (is_dir($dir)) {
            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
            foreach ($iterator as $file) {
                if ($file->isFile()) $size += $file->getSize();
            }
        }
        $terpakai_mb = round($size / 1048576, 2);
        $kuota_mb = 4096; // Asumsi batas maksimal khusus foto adalah 4096 MB (4 GB)
        $persentase = min(100, round(($terpakai_mb / $kuota_mb) * 100, 1));
        
        echo json_encode([
            'status' => 'success',
            'terpakai_mb' => $terpakai_mb,
            'kuota_mb' => $kuota_mb,
            'persentase' => $persentase
        ]);
    }

    public function hapus_foto_berkala() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->jsonError('Metode tidak valid.'); return; }
        $bulan_input = $_POST['bulan']; // format YYYY-MM
        if (empty($bulan_input)) { $this->jsonError('Bulan belum dipilih.'); return; }
        
        if ($bulan_input === date('Y-m')) {
            $this->jsonError('Anda tidak bisa menghapus foto untuk bulan yang sedang berjalan!');
            return;
        }

        list($tahun, $bulan) = explode('-', $bulan_input);
        $dir = PUBLIC_PATH . "/uploads/$tahun/$bulan";
        
        $count = 0;
        if (is_dir($dir)) {
            $files = glob("$dir/*.jpg");
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                    $count++;
                }
            }
        }
        
        catat_log('DELETE', 'Penyimpanan', "Menghapus $count foto absensi pada bulan $bulan_input");
        echo json_encode(['status' => 'success', 'message' => "$count foto berhasil dihapus permanen dari server."]);
    }

    public function backup_data() {
        $data['judul'] = 'Backup & Pencadangan | PT REN';
        $this->view('layouts/header', $data);
        $this->view('superadmin/backup/index', $data);
        $this->view('layouts/footer');
    }

    public function proses_download_backup() {
        // Matikan batas waktu agar zip besar bisa didownload
        set_time_limit(0);
        ini_set('memory_limit', '1024M');

        $db = (new Database())->getConnection();
        
        // 1. Ekspor Database ke SQL String
        $tables = [];
        $stmt = $db->query("SHOW TABLES");
        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $tables[] = $row[0];
        }

        $sql = "-- Backup Database PT REN\n";
        $sql .= "-- Waktu: " . date('Y-m-d H:i:s') . "\n\n";
        $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

        foreach ($tables as $table) {
            $stmt = $db->query("SHOW CREATE TABLE `$table`");
            $row2 = $stmt->fetch(PDO::FETCH_NUM);
            $sql .= "DROP TABLE IF EXISTS `$table`;\n";
            $sql .= $row2[1] . ";\n\n";

            $stmt2 = $db->query("SELECT * FROM `$table`");
            while ($row = $stmt2->fetch(PDO::FETCH_ASSOC)) {
                $sql .= "INSERT INTO `$table` VALUES(";
                $first = true;
                foreach ($row as $val) {
                    if (!$first) $sql .= ", ";
                    if ($val === null) {
                        $sql .= "NULL";
                    } else {
                        $sql .= $db->quote($val);
                    }
                    $first = false;
                }
                $sql .= ");\n";
            }
            $sql .= "\n";
        }
        $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";

        // 2. Persiapkan ZIP Archive
        $zipFilename = 'Backup_PTREN_' . date('Ymd_His') . '.zip';
        $zipPath = sys_get_temp_dir() . '/' . $zipFilename;
        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
            die("Gagal membuat file ZIP.");
        }

        // Tambahkan file SQL
        $zip->addFromString('database.sql', $sql);

        // 3. Tambahkan folder uploads
        $dirUploads = PUBLIC_PATH . '/uploads';
        if (is_dir($dirUploads)) {
            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($dirUploads),
                RecursiveIteratorIterator::LEAVES_ONLY
            );

            foreach ($files as $name => $file) {
                if (!$file->isDir()) {
                    $filePath = $file->getRealPath();
                    // Sesuaikan slash windows vs linux
                    $relativePath = 'uploads/' . str_replace('\\', '/', substr($filePath, strlen($dirUploads) + 1));
                    $zip->addFile($filePath, $relativePath);
                }
            }
        }
        
        $zip->close();
        catat_log('BACKUP', 'Sistem', 'Melakukan ekspor penuh Database (.sql) dan File Upload (.zip)');

        // 4. Kirim ke Browser
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . $zipFilename . '"');
        header('Content-Length: ' . filesize($zipPath));
        header('Pragma: no-cache');
        readfile($zipPath);
        
        // Hapus file temp
        @unlink($zipPath);
        exit;
    }

    // ==================== HELPER ====================
    private function jsonError($msg) {
        echo json_encode(['status' => 'error', 'message' => $msg]);
    }
}
