<?php

/**
 * Helper CSRF untuk PT REN Absensi
 */

/**
 * Menghasilkan token CSRF unik dan menyimpannya di session.
 *
 * @return string Token CSRF
 */
function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Menghasilkan input field tersembunyi yang berisi token CSRF.
 * Sisipkan fungsi ini di dalam form.
 *
 * @return string HTML tag input hidden
 */
function csrf_field() {
    $token = csrf_token();
    return '<input type="hidden" name="csrf_token" value="' . $token . '">';
}

/**
 * Memverifikasi token CSRF yang dikirim melalui POST.
 * Jika tidak cocok, hentikan eksekusi dengan pesan error.
 */
function verify_csrf() {
    // Hanya periksa pada metode POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            http_response_code(403);
            die("<h2>Akses Ditolak (403 Forbidden)</h2><p>Token Keamanan (CSRF) tidak valid atau kadaluarsa. Silakan refresh halaman dan coba lagi.</p>");
        }
    }
}
