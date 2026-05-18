# **Arsitektur Sistem & Pedoman UI/UX PT REN**

Dokumen ini berisi panduan implementasi struktur MVC, pedoman keamanan, struktur folder, dan prompt generator UI untuk Aplikasi Absensi PT REN.

## **1\. Arsitektur & Keamanan**

* **Pola MVC (Model-View-Controller):** Memisahkan logika database (Model), tampilan antarmuka (View), dan alur proses (Controller).  
* **Isolasi Root:** Folder /app berada di luar jangkauan akses web langsung. User berinteraksi melalui /public.  
* **Keamanan PDO & Rate Limiting:** Mencegah *SQL Injection*  
* **Rate Limiting: **Sistem akan membatasi percobaan login yang gagal untuk mencegah serangan *Brute Force*.

## **2\. Struktur Folder (MVC Pattern)**

absensi-ptren/  
│  
├── docs/                       \# DOKUMENTASI SISTEM (File .md dipisah)  
│   ├── 01\_design.md            \# Desain UI/UX  
│   ├── 02\_database.md          \# Skema Database  
│   ├── 03\_alurkerja.md         \# Alur kerja aplikasi  
│   └── 04\_arsitektur\_ui.md     \# Panduan arsitektur & prompt  
│  
├── app/                        \# AREA INTI (Privat & Terisolasi)  
│   ├── Config/                 \# Konfigurasi sistem  
│   │   └── database.php        \# Kredensial database PDO  
│   ├── Controllers/            \# Logika penengah  
│   │   ├── AuthController.php  
│   │   ├── PegawaiController.php  
│   │   └── AdminController.php  
│   ├── Models/                 \# Interaksi Database  
│   │   ├── User.php  
│   │   └── Absensi.php  
│   ├── Views/                  \# Tampilan UI (HTML/PHP)  
│   │   ├── layouts/            \# Header, footer, sidebar  
│   │   ├── pegawai/  
│   │   ├── admin\_cabang/  
│   │   └── superadmin/  
│   └── Core/                   \# Router utama MVC  
│       └── App.php  
│  
├── public/                     \# AREA PUBLIK (Dapat Diakses Web)  
│   ├── .htaccess               \# Mengarahkan semua request ke index.php  
│   ├── index.php               \# Gerbang utama aplikasi (Front Controller)  
│   ├── assets/                 \# File statis (CSS, JS, Image)  
│   │   ├── css/  
│   │   ├── js/  
│   │   └── img/  
│   └── uploads/                \# Folder simpan foto absen (isolasi)  
│  
└── vendor/                     \# (Opsional) Jika nanti pakai Composer

## **3\. Prompt Generator UI (Untuk Copas ke AI UI/UX)**

### **Role Pegawai (Tampilan Mobile)**

**1\. Dashboard Pegawai:**

"Buatkan desain UI halaman Dashboard Absensi berbasis Mobile menggunakan Tailwind CSS. Tema biru korporat. Ada Info profil, Jam digital real-time, Card status lokasi. Ada tombol 'Absen Masuk' dan 'Absen Pulang'. Tambahkan Bottom Navigation bar."

**2\. Halaman Kamera & Indikator Sinyal:**

"Buatkan desain UI mobile untuk layar 'Ambil Absen' Tailwind CSS. Ada frame kamera besar. Di atas layar, tambahkan Indikator Jaringan Real-time (seperti bar sinyal HP atau titik warna) dengan 3 state (Hijau=Baik, Kuning=Sedang, Merah=Lambat). Di bawah kamera ada tombol bulat besar (shutter) dan teks koordinat lokasi."

### **Role SuperAdmin / Admin Cabang (Tampilan Desktop)**

**1\. Dashboard Utama dengan Skeleton Loading:**

"Buatkan desain UI Dashboard Admin Desktop menggunakan Tailwind CSS dengan layout Sidebar kiri dan Konten di kanan. Buatkan 2 versi kode: Versi 1 adalah 'Skeleton Loading State' menggunakan animasi 'animate-pulse' Tailwind berupa blok-blok abu-abu untuk area Card Statistik dan Tabel. Versi 2 adalah desain asli setelah data dimuat dengan 3 Card metrik dan Tabel Persetujuan Cuti."

**2\. Modal Penggajian & Import CSV:**

"Buatkan desain UI Modal form 'Edit Penggajian' di Tailwind CSS. Input 'Gaji Pokok' dan 'Lembur' di-disable. Input 'BPJS' dan 'Insentif' bisa diedit. Juga buatkan desain Modal 'Import CSV Libur Nasional' dengan area drag-and-drop file."