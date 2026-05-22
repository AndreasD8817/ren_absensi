<?php

/**
 * Helper Keamanan untuk PT REN Absensi
 */

/**
 * Melindungi output dari serangan Cross-Site Scripting (XSS).
 * Menggunakan htmlspecialchars dengan ENT_QUOTES dan UTF-8.
 *
 * @param string $data Data yang akan di-escape
 * @return string Data yang sudah di-escape
 */
function esc($data) {
    if ($data === null) return '';
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}
