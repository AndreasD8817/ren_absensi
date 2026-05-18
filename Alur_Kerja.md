# **Alur Kerja Aplikasi (Workflow) & Keamanan**

## **1\. Alur Absensi Harian (Pegawai) Terintegrasi Validasi**

1. **Pengecekan Awal Hari:** Sistem mengecek hari ini. Melihat tabel jam\_kerja\_cabang untuk jam masuk.  
2. **Jendela Akses 2 Jam:** Jika (Waktu Sekarang \< Jam Masuk \- 2 Jam), tombol absen memunculkan **SweetAlert Warning:** *"Belum waktunya absen"*.  
3. **Perekaman & Indikator Sinyal:** Pegawai masuk ke layar kamera. Javascript mendeteksi ping/koneksi. Jika indikator Merah (Lambat), layar menampilkan peringatan visual halus.  
4. **Validasi Saat Submit:** Saat "Simpan Absen" diklik:  
   * *Cek Libur:* Apakah hari ini libur nasional/lokal (tanpa override)?  
   * *Cek Radius:* Apakah Lat/Lng pegawai \<= Radius Cabang?  
5. **Feedback & Peringatan Koneksi:**  
   * Jika di luar radius: **SweetAlert Error** *"Absen Gagal: Di luar batas 50m"*.  
   * Jika Sukses & Sinyal Hijau/Kuning: **SweetAlert Success** *"Absen Berhasil\!"*  
   * Jika Sukses tapi Sinyal Merah: **SweetAlert Warning** *"Absen dikirim, namun jaringan Anda tidak stabil. Mohon cek menu Riwayat Absen setelah ini untuk memastikan data benar-benar tersimpan di server."*

## **2\. Alur Penggajian & Edit Komponen (SuperAdmin)**

1. **Generate Draft:** SuperAdmin menekan tombol "Generate Gaji Cabang X".  
2. **Input Insentif:** Jika ada insentif (misal 10 Juta untuk cabang A), SuperAdmin memasukkan nilai, sistem membagi rata ke seluruh pegawai cabang A ke dalam komponen gaji.  
3. **Review & Edit:** SuperAdmin bisa menekan tombol **Edit** pada baris pegawai untuk menyesuaikan BPJS Kesehatan (Kelas 1), BPJS TK, dll.  
4. **Publish:** Menekan "Publish Semua" akan menjadikan gaji final dan bisa dilihat di Slip Gaji pegawai.

## **3\. Alur Cuti & Hari Libur**

1. **Cuti:** Pegawai ajukan \-\> HRD/Admin Cabang ACC \-\> SuperAdmin ACC \-\> Disetujui.  
2. **Libur Nasional:** SuperAdmin upload CSV di awal tahun. Otomatis berlaku di semua cabang.  
3. **Libur Lokal & Override:** Admin Cabang bisa menambah libur khusus daerahnya, ATAU mengubah status Libur Nasional menjadi "Tetap Masuk" (Override) untuk cabangnya.

## **4\. Lapisan Keamanan (Security Measures)**

1. **SQL Injection Prevention:** Wajib menggunakan **PDO Prepared Statements**.  
2. **Password Hashing:** Menggunakan password\_hash($pass, PASSWORD\_BCRYPT).  
3. **Rate Limiting:** IP address diblokir sementara jika gagal login berkali-kali.