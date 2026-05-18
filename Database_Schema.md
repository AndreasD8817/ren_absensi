# **Skema Database (MySQL)**

**Nama Database:** db\_absensi\_pt\_ren

*Catatan: Menggunakan Engine InnoDB, collation utf8mb4_general_ci. Seluruh query nantinya wajib menggunakan PDO Prepared Statements.*

## **1\. Tabel cabang**

* id\_cabang (INT, PK, A-I)  
* nama\_cabang (VARCHAR 100\) \-\> misal: Makassar, Surabaya, dsb.  
* latitude (VARCHAR 50\)  
* longitude (VARCHAR 50\)  
* radius\_meter (INT)  
* tarif\_lembur\_per\_jam (DECIMAL) \-\> Ditetapkan oleh Cabang (Flat)  
* denda\_1\_5 (DECIMAL)  
* denda\_6\_10 (DECIMAL)  
* denda\_11\_15 (DECIMAL)  
* denda\_16\_30 (DECIMAL)  
* denda\_31\_60 (DECIMAL)  
* denda\_alfa (DECIMAL) \-\> Default 50000  
* created\_at (TIMESTAMP)

## **2\. Tabel jam\_kerja\_cabang (Baru)**

Menyimpan jadwal masuk dan pulang untuk tiap hari per cabang.

* id\_jam\_kerja (INT, PK, A-I)  
* id\_cabang (INT, FK)  
* hari (ENUM: 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu')  
* jam\_masuk (TIME) \-\> Misal: 07:00  
* jam\_pulang (TIME) \-\> Misal: 17:00 (Jumat: 16:00)  
* is\_libur\_akhir\_pekan (BOOLEAN) \-\> 1 jika hari tersebut libur rutin (misal Minggu)

## **3\. Tabel users**

* id\_user (INT, PK, A-I)  
* id\_cabang (INT, FK)  
* nip (VARCHAR 50, UNIQUE)  
* nama\_lengkap (VARCHAR 150\)  
* password (VARCHAR 255\) \-\> Disimpan menggunakan password\_hash() Bcrypt.  
* role (ENUM: 'pegawai', 'admin\_cabang', 'superadmin')  
* jabatan (VARCHAR 100\)  
* gaji\_pokok (DECIMAL)  
* is\_active (BOOLEAN)  
* created\_at (TIMESTAMP)

## **4\. Tabel absensi**

* id\_absensi (INT, PK, A-I)  
* id\_user (INT, FK)  
* tanggal (DATE)  
* jam\_masuk (TIME)  
* foto\_masuk (VARCHAR 255\)  
* lat\_masuk (VARCHAR 50\)  
* lng\_masuk (VARCHAR 50\)  
* jam\_pulang (TIME, NULL)  
* foto\_pulang (VARCHAR 255, NULL)  
* lat\_pulang (VARCHAR 50, NULL)  
* lng\_pulang (VARCHAR 50, NULL)  
* status (ENUM: 'hadir', 'telat', 'alfa')  
* menit\_terlambat (INT)  
* total\_denda (DECIMAL)  
* durasi\_lembur (INT)  
* total\_uang\_lembur (DECIMAL)

## **5\. Tabel cuti**

* id\_cuti (INT, PK, A-I)  
* id\_user (INT, FK)  
* tgl\_mulai (DATE)  
* tgl\_selesai (DATE)  
* alasan (TEXT)  
* status\_pengajuan (ENUM: 'pending\_cabang', 'pending\_pusat', 'disetujui', 'ditolak')  
* tgl\_pengajuan (TIMESTAMP)

## **6\. Tabel hari\_libur & libur\_override**

*(Sama seperti sebelumnya: mengelola libur nasional, lokal, dan paksaan masuk/override)*

## **7\. Tabel insentif\_cabang**

*(Sama seperti sebelumnya: pencatatan pembagian pool insentif)*

## **8\. Tabel penggajian\_bulanan**

* id\_gaji (INT, PK, A-I)  
* id\_user (INT, FK)  
* bulan (INT 1-12)  
* tahun (YEAR)  
* nilai\_gaji\_pokok (DECIMAL)  
* nilai\_insentif (DECIMAL)  
* total\_lembur (DECIMAL)  
* total\_potongan\_telat\_alfa (DECIMAL)  
* potongan\_bpjs\_kesehatan (DECIMAL) \-\> Default Rp 150.000, tapi bisa diedit.  
* potongan\_bpjs\_tk (DECIMAL) \-\> Untuk JHT/JKK, bisa diisi manual saat edit gaji.  
* komponen\_tambahan\_lain (DECIMAL) \-\> Kolom dinamis jika ada bonus/potongan manual.  
* total\_gaji\_bersih (DECIMAL)  
* status (ENUM: 'draft', 'published') \-\> Saat 'draft', SuperAdmin bisa edit komponennya.

## **9\. Tabel login\_attempts (Baru \- Rate Limiting)**

Mencegah Brute Force Attack.

* id\_attempt (INT, PK, A-I)  
* ip\_address (VARCHAR 45\)  
* waktu (TIMESTAMP)  
* is\_success (BOOLEAN)