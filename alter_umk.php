<?php
require_once 'app/Config/database.php';
try {
    $db = new Database();
    $conn = $db->getConnection();
    $stmt = $conn->query("SHOW COLUMNS FROM cabang LIKE 'umk'");
    if($stmt->rowCount() == 0) {
        $conn->exec("ALTER TABLE cabang ADD COLUMN umk INT NOT NULL DEFAULT 0 AFTER radius_meter");
        echo "Kolom 'umk' berhasil ditambahkan ke tabel cabang.";
    } else {
        echo "Kolom 'umk' sudah ada.";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
