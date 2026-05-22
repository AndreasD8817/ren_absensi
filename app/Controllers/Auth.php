<?php

class Auth extends Controller {
    public function index() {
        // Jika sudah login, langsung arahkan ke dashboard masing-masing
        if (isset($_SESSION['user'])) {
            $role = $_SESSION['user']['role'];
            if ($role === 'superadmin') {
                header('Location: ' . BASE_URL . '/superadmin');
            } elseif ($role === 'admin_cabang') {
                header('Location: ' . BASE_URL . '/admincabang');
            } else {
                header('Location: ' . BASE_URL . '/pegawai');
            }
            exit;
        }

        $data['judul'] = 'Login | Aplikasi Absensi PT REN';
        $this->view('layouts/header', $data);
        $this->view('auth/login');
        $this->view('layouts/footer');
    }

    public function proses_login() {
        // Pastikan request adalah POST
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $nip = trim($_POST['nip']);
            $password = $_POST['password'];
            $ip_address = $_SERVER['REMOTE_ADDR'];

            if (empty($nip) || empty($password)) {
                $_SESSION['flash_error'] = 'NIP dan Password wajib diisi!';
                header('Location: ' . BASE_URL . '/auth');
                exit;
            }

            // Panggil model User
            $userModel = $this->model('User');
            $loginResult = $userModel->login($nip, $password, $ip_address);

            if ($loginResult['status']) {
                // Set sesi login
                $_SESSION['user'] = $loginResult['data'];
                
                // Catat Log Aktivitas
                catat_log('LOGIN', 'Autentikasi', 'User berhasil login ke dalam sistem');

                // Redirect sesuai role
                $role = $_SESSION['user']['role'];
                if ($role === 'superadmin') {
                    header('Location: ' . BASE_URL . '/superadmin');
                } elseif ($role === 'admin_cabang') {
                    header('Location: ' . BASE_URL . '/admincabang');
                } else {
                    header('Location: ' . BASE_URL . '/pegawai');
                }
            } else {
                // Set pesan error ke flash session dan kembali ke halaman login
                $_SESSION['flash_error'] = $loginResult['message'];
                header('Location: ' . BASE_URL . '/auth');
            }
            exit;
        } else {
            // Jika bukan POST, arahkan ke halaman login
            header('Location: ' . BASE_URL . '/auth');
            exit;
        }
    }

    public function logout() {
        // Catat Log Aktivitas sebelum sesi dihapus
        if (isset($_SESSION['user'])) {
            catat_log('LOGOUT', 'Autentikasi', 'User keluar dari sistem');
        }

        // Hapus semua data sesi
        session_unset();
        session_destroy();
        
        // Mulai sesi baru untuk pesan sukses logout
        session_start();
        $_SESSION['flash_success'] = 'Anda berhasil logout.';
        
        header('Location: ' . BASE_URL . '/auth');
        exit;
    }
}
