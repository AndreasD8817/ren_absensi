<?php

// ==================== ERROR HANDLING GLOBAL ====================
function custom_error_handler($errno, $errstr, $errfile, $errline) {
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
}

function custom_exception_handler($e) {
    $log_file = dirname(__DIR__) . '/error.log';
    $timestamp = date('Y-m-d H:i:s');
    $error_msg = "Fatal error: Uncaught " . get_class($e) . ": " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine() . "\nStack trace:\n" . $e->getTraceAsString() . "\n  thrown in " . $e->getFile() . " on line " . $e->getLine() . "\n\n";
    $error_msg = "[{$timestamp}] " . $error_msg;
    
    error_log($error_msg, 3, $log_file);

    http_response_code(500);
    include dirname(__DIR__) . '/app/Views/errors/500.php';
    exit;
}

set_error_handler("custom_error_handler");
set_exception_handler("custom_exception_handler");

register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        $log_file = dirname(__DIR__) . '/error.log';
        $timestamp = date('Y-m-d H:i:s');
        $error_msg = "[{$timestamp}] Fatal error: " . $error['message'] . " in " . $error['file'] . " on line " . $error['line'] . "\n\n";
        error_log($error_msg, 3, $log_file);
        
        http_response_code(500);
        include dirname(__DIR__) . '/app/Views/errors/500.php';
    }
});
// ===============================================================

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
define('BASE_URL', rtrim($protocol . "://" . $_SERVER['HTTP_HOST'] . str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/'));

// Memuat Helper Keamanan & Audit
require_once APP_PATH . '/Helpers/security_helper.php';
require_once APP_PATH . '/Helpers/csrf_helper.php';
require_once APP_PATH . '/Helpers/audit_helper.php';

// Lindungi seluruh aplikasi dari CSRF pada metode POST
verify_csrf();

// Memuat file inti (Core) MVC
require_once APP_PATH . '/Core/App.php';
require_once APP_PATH . '/Core/Controller.php';

// Memuat file konfigurasi database
require_once APP_PATH . '/Config/database.php';

// Jalankan aplikasi (Routing)
$app = new App();
