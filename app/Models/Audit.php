<?php

class Audit {
    private $db;

    public function __construct() {
        $this->db = (new Database())->getConnection();
    }

    /**
     * Mengambil semua data log aktivitas terbaru
     */
    public function getSemuaLog() {
        $stmt = $this->db->prepare("SELECT * FROM audit_logs ORDER BY created_at DESC");
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
