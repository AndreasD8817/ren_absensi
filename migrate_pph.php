<?php
require_once __DIR__ . '/app/Config/database.php';
try {
    $db = (new Database())->getConnection();
    // Tambah is_pph21_active boolean ke penggajian_bulanan
    $db->exec("ALTER TABLE penggajian_bulanan ADD is_pph21_active TINYINT(1) DEFAULT 1 AFTER tahun;");
    echo "Berhasil!";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
