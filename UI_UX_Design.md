# **Panduan Desain UI/UX Aplikasi Absensi PT REN**

## **1\. Pendekatan & Teknologi Desain**

* **Frontend:** HTML5, CSS3, JavaScript Vanilla.  
* **Styling:** Framework CSS **Tailwind** (atau Bootstrap jika lebih familiar, namun Tailwind direkomendasikan untuk desain *custom*).  
* **Interaksi & Popup:** **SweetAlert2** untuk semua notifikasi sukses, peringatan, error, dan dialog konfirmasi.  
* **Responsive:** Mobile-first (Pegawai), Desktop-first (Admin/SuperAdmin).

## **2\. Pembaruan Antarmuka (Sesuai Kebutuhan Baru)**

### **A. Mobile App (Pegawai)**

* **Indikator Jaringan Real-time:** Di halaman Absensi (Kamera & Lokasi), terdapat ikon sinyal/titik di pojok layar yang mendeteksi kecepatan internet:  
  * **Hijau (Baik):** Koneksi stabil.  
  * **Kuning (Sedang):** Koneksi agak lambat.  
  * **Merah (Lambat):** Koneksi buruk/terputus. Jika user klik "Simpan Absen" dalam state ini, akan muncul SweetAlert Info tambahan untuk mengecek riwayat.  
* **Status Jendela Waktu (Window Time):** Di Dashboard, jika pegawai membuka aplikasi kurang dari 2 jam sebelum jam\_masuk, tombol absen berwarna abu-abu. Jika diklik, muncul *SweetAlert Info*.  
* **Feedback Validasi Terpusat:** Setelah foto diambil dan lokasi didapat, muncul *loading spinner* pada tombol "Simpan". Jika di luar radius, sistem langsung menolak dengan *SweetAlert Error* ikon silang merah beserta alasannya.

### **B. Desktop (Admin Cabang & SuperAdmin)**

* **Skeleton Loading State:** Saat perpindahan halaman atau mengambil data dari database (misal: memuat tabel pegawai atau dashboard), layar tidak boleh *freeze* (blank). Tampilkan elemen blok abu-abu yang berkedip halus (*animate-pulse* di Tailwind) sebagai *placeholder* sebelum data asli muncul (menangani koneksi lambat).  
* **Form Tambah/Edit Cabang (Dinamic Schedule):**  
  Saat menambah cabang, terdapat tabel berisi 7 baris (Senin \- Minggu). Kolom: Toggle Libur, Input Jam Masuk, Input Jam Pulang.  
* **Halaman Penggajian (Mode Draft):**  
  * Tab "Draft Gaji" dan "Gaji Terpublikasi".  
  * Tombol *Edit* di tiap pegawai untuk mengubah: Potongan BPJS Kesehatan, Potongan BPJS Ketenagakerjaan (JHT/JKK), Penyesuaian Lainnya. Gaji pokok dan lembur berstatus *Disabled*.  
  * **Input Insentif Cabang:** Kolom untuk membagi rata total insentif ke seluruh pegawai di cabang tersebut.  
* **Manajemen Libur:** Halaman melihat kalender/tabel libur nasional. SuperAdmin dapat **Import CSV** Libur Nasional di awal tahun. Admin cabang dapat melakukan Override (memaksa masuk kerja di hari libur nasional) atau menambah libur kedaerahan.