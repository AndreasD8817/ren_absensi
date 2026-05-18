<?php

// Front Controller untuk "Aplikasi Absensi PT REN"
// Semua request diarahkan ke sini oleh .htaccess

// Mulai sesi jika belum aktif
if (!session_id()) {
    session_start();
}

// Timezone Indonesia (WIB = UTC+7)
date_default_timezone_set('Asia/Jakarta');

// Definisikan path utama (Root direktori)
// Arahkan ke root aplikasi (satu tingkat di atas public)
define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');
define('PUBLIC_PATH', ROOT_PATH . '/public');

// Definisikan BASE_URL secara dinamis (mendukung ren_absensi.test atau localhost)
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
// dirname($_SERVER['SCRIPT_NAME']) akan mengembalikan path ke folder public/
define('BASE_URL', $protocol . "://" . $_SERVER['HTTP_HOST'] . str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])));

// Memuat file inti (Core) MVC
require_once APP_PATH . '/Core/App.php';
require_once APP_PATH . '/Core/Controller.php';

// Memuat file konfigurasi database
require_once APP_PATH . '/Config/database.php';

// Jalankan aplikasi (Routing)
$app = new App();
