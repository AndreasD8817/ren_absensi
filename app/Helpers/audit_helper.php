<?php

/**
 * Helper Log Aktivitas (Audit Trail) untuk PT REN Absensi
 */

/**
 * Mencatat log aktivitas ke database
 *
 * @param string $aksi Aksi yang dilakukan (misal: 'LOGIN', 'CREATE', 'UPDATE', 'DELETE')
 * @param string $entitas Modul/Data yang dimodifikasi (misal: 'Pegawai', 'Cuti', 'Autentikasi')
 * @param string $keterangan Deskripsi detail (misal: 'Menambahkan pegawai NIP 12345')
 */
function catat_log($aksi, $entitas, $keterangan) {
    if (!isset($_SESSION['user'])) return; // Hanya catat jika user sudah login

    $id_user = $_SESSION['user']['id_user'];
    $nama_user = $_SESSION['user']['nama_lengkap'];
    $role = $_SESSION['user']['role'];
    $ip_address = $_SERVER['REMOTE_ADDR'];

    try {
        $db = (new Database())->getConnection();
        $stmt = $db->prepare("
            INSERT INTO audit_logs (id_user, nama_user, role, aksi, entitas, keterangan, ip_address)
            VALUES (:id_user, :nama_user, :role, :aksi, :entitas, :keterangan, :ip_address)
        ");
        $stmt->bindParam(':id_user', $id_user, PDO::PARAM_INT);
        $stmt->bindParam(':nama_user', $nama_user);
        $stmt->bindParam(':role', $role);
        $stmt->bindParam(':aksi', $aksi);
        $stmt->bindParam(':entitas', $entitas);
        $stmt->bindParam(':keterangan', $keterangan);
        $stmt->bindParam(':ip_address', $ip_address);
        
        $stmt->execute();
    } catch (Exception $e) {
        // Gagal mencatat log, bisa diabaikan agar tidak mengganggu transaksi utama
    }
}
