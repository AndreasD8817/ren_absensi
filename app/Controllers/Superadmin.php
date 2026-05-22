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
        $result = $penggajianModel->generateGaji($_POST['bulan'], $_POST['tahun'], $id_cabang);
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

    // ==================== HELPER ====================
    private function jsonError($msg) {
        echo json_encode(['status' => 'error', 'message' => $msg]);
    }
}
