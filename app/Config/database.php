<?php

// Class Wrapper untuk koneksi database menggunakan PDO
class Database {
    private $host = 'localhost';
    private $user = 'root';
    private $pass = ''; // Default password Laragon kosong
    private $dbname = 'db_absensi_pt_ren';

    private $dbh; // Database Handler
    private $error;

    public function __construct() {
        // Data Source Name
        $dsn = 'mysql:host=' . $this->host . ';dbname=' . $this->dbname . ';charset=utf8mb4';

        // Opsi pengaturan keamanan PDO
        $options = [
            // Koneksi tetap persisten
            PDO::ATTR_PERSISTENT => true,
            // Error dilempar sebagai Exception (Aman & mudah didebug)
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            // Kembalikan data sebagai array asosiatif
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            // Matikan emulasi prepared statements untuk keamanan ekstra dari SQL Injection
            PDO::ATTR_EMULATE_PREPARES => false
        ];

        // Buat instance PDO di dalam try-catch block
        try {
            $this->dbh = new PDO($dsn, $this->user, $this->pass, $options);
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            // Hentikan eksekusi dan tampilkan error (Bisa diganti dengan log file di production)
            die("Koneksi Database Gagal: " . $this->error);
        }
    }

    // Mengambil instance koneksi PDO
    public function getConnection() {
        return $this->dbh;
    }
    
    // (Bisa ditambahkan fungsi-fungsi bantu query seperti bind, execute, fetchAll, fetch dll di kemudian hari)
}
