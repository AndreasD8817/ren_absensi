<?php

class Controller {
    // Fungsi untuk memuat View ke layar
    public function view($view, $data = []) {
        // Ekstrak array $data menjadi variabel independen (misal: $data['judul'] jadi $judul)
        if (!empty($data)) {
            extract($data);
        }
        // Pastikan file view ada sebelum di-require
        if(file_exists(APP_PATH . '/Views/' . $view . '.php')){
            require_once APP_PATH . '/Views/' . $view . '.php';
        } else {
            die("View $view tidak ditemukan.");
        }
    }

    // Fungsi untuk memuat Model (koneksi spesifik tabel)
    public function model($model) {
        if(file_exists(APP_PATH . '/Models/' . $model . '.php')){
            require_once APP_PATH . '/Models/' . $model . '.php';
            return new $model;
        }
    }
}
