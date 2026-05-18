<?php

class App {
    protected $controller = 'Auth'; // Default controller diubah ke halaman Login
    protected $method = 'index';       // Default method
    protected $params = [];            // Parameter URL

    public function __construct() {
        $url = $this->parseURL();

        // Cek apakah ada controller yang sesuai dengan URL (index ke-0)
        if (isset($url[0])) {
            // Mapping alias URL ke nama controller (untuk handle nama multi-kata)
            $aliasMap = [
                'admincabang' => 'AdminCabang',
                'superadmin'  => 'Superadmin',
                'pegawai'     => 'Pegawai',
                'auth'        => 'Auth',
            ];

            $urlSegment = strtolower($url[0]);
            if (isset($aliasMap[$urlSegment])) {
                $controllerName = $aliasMap[$urlSegment];
            } else {
                $controllerName = ucfirst($urlSegment);
            }

            if (file_exists(APP_PATH . '/Controllers/' . $controllerName . '.php')) {
                $this->controller = $controllerName;
                unset($url[0]);
            }
        }

        // Muat file controller dan instansiasi
        require_once APP_PATH . '/Controllers/' . $this->controller . '.php';
        $this->controller = new $this->controller;

        // Cek apakah ada method yang dipanggil (index ke-1)
        if (isset($url[1])) {
            if (method_exists($this->controller, $url[1])) {
                $this->method = $url[1];
                unset($url[1]);
            }
        }

        // Sisa elemen array URL dianggap sebagai parameter
        if (!empty($url)) {
            $this->params = array_values($url);
        }

        // Jalankan controller & method dengan parameternya
        call_user_func_array([$this->controller, $this->method], $this->params);
    }

    // Fungsi memecah URL dari .htaccess menjadi array
    public function parseURL() {
        if (isset($_GET['url'])) {
            $url = rtrim($_GET['url'], '/');
            $url = filter_var($url, FILTER_SANITIZE_URL);
            $url = explode('/', $url);
            return $url;
        }
        return [];
    }
}
