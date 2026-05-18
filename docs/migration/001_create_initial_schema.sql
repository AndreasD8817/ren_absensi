-- Migration: 001_create_initial_schema
-- Description: Membuat struktur tabel awal untuk Aplikasi Absensi PT REN.
-- Engine: InnoDB
-- Collation: utf8mb4_general_ci

CREATE DATABASE IF NOT EXISTS `db_absensi_pt_ren` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `db_absensi_pt_ren`;

-- 1. Tabel cabang
CREATE TABLE `cabang` (
  `id_cabang` INT NOT NULL AUTO_INCREMENT,
  `nama_cabang` VARCHAR(100) NOT NULL,
  `latitude` VARCHAR(50) NOT NULL,
  `longitude` VARCHAR(50) NOT NULL,
  `radius_meter` INT NOT NULL DEFAULT 50,
  `tarif_lembur_per_jam` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `denda_1_5` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `denda_6_10` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `denda_11_15` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `denda_16_30` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `denda_31_60` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `denda_alfa` DECIMAL(10,2) NOT NULL DEFAULT 50000.00,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_cabang`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 2. Tabel jam_kerja_cabang
CREATE TABLE `jam_kerja_cabang` (
  `id_jam_kerja` INT NOT NULL AUTO_INCREMENT,
  `id_cabang` INT NOT NULL,
  `hari` ENUM('Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu') NOT NULL,
  `jam_masuk` TIME NOT NULL,
  `jam_pulang` TIME NOT NULL,
  `is_libur_akhir_pekan` BOOLEAN NOT NULL DEFAULT FALSE,
  PRIMARY KEY (`id_jam_kerja`),
  FOREIGN KEY (`id_cabang`) REFERENCES `cabang`(`id_cabang`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 3. Tabel users
CREATE TABLE `users` (
  `id_user` INT NOT NULL AUTO_INCREMENT,
  `id_cabang` INT NOT NULL,
  `nip` VARCHAR(50) NOT NULL UNIQUE,
  `nama_lengkap` VARCHAR(150) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('pegawai', 'admin_cabang', 'superadmin') NOT NULL DEFAULT 'pegawai',
  `jabatan` VARCHAR(100) DEFAULT NULL,
  `gaji_pokok` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `is_active` BOOLEAN NOT NULL DEFAULT TRUE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_user`),
  FOREIGN KEY (`id_cabang`) REFERENCES `cabang`(`id_cabang`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 4. Tabel absensi
CREATE TABLE `absensi` (
  `id_absensi` INT NOT NULL AUTO_INCREMENT,
  `id_user` INT NOT NULL,
  `tanggal` DATE NOT NULL,
  `jam_masuk` TIME NOT NULL,
  `foto_masuk` VARCHAR(255) NOT NULL,
  `lat_masuk` VARCHAR(50) NOT NULL,
  `lng_masuk` VARCHAR(50) NOT NULL,
  `jam_pulang` TIME DEFAULT NULL,
  `foto_pulang` VARCHAR(255) DEFAULT NULL,
  `lat_pulang` VARCHAR(50) DEFAULT NULL,
  `lng_pulang` VARCHAR(50) DEFAULT NULL,
  `status` ENUM('hadir', 'telat', 'alfa') NOT NULL DEFAULT 'hadir',
  `menit_terlambat` INT NOT NULL DEFAULT 0,
  `total_denda` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `durasi_lembur` INT NOT NULL DEFAULT 0,
  `total_uang_lembur` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`id_absensi`),
  FOREIGN KEY (`id_user`) REFERENCES `users`(`id_user`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 5. Tabel cuti
CREATE TABLE `cuti` (
  `id_cuti` INT NOT NULL AUTO_INCREMENT,
  `id_user` INT NOT NULL,
  `tgl_mulai` DATE NOT NULL,
  `tgl_selesai` DATE NOT NULL,
  `alasan` TEXT NOT NULL,
  `status_pengajuan` ENUM('pending_cabang', 'pending_pusat', 'disetujui', 'ditolak') NOT NULL DEFAULT 'pending_cabang',
  `tgl_pengajuan` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_cuti`),
  FOREIGN KEY (`id_user`) REFERENCES `users`(`id_user`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 6. Tabel hari_libur (Sesuai kebutuhan sebelumnya, menyimpan hari libur nasional)
CREATE TABLE `hari_libur` (
  `id_libur` INT NOT NULL AUTO_INCREMENT,
  `tanggal` DATE NOT NULL UNIQUE,
  `keterangan` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id_libur`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 6.b Tabel libur_override (Sesuai kebutuhan sebelumnya, paksaan masuk / libur lokal)
CREATE TABLE `libur_override` (
  `id_override` INT NOT NULL AUTO_INCREMENT,
  `id_cabang` INT NOT NULL,
  `tanggal` DATE NOT NULL,
  `status` ENUM('tetap_masuk', 'libur_lokal') NOT NULL,
  `keterangan` VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (`id_override`),
  FOREIGN KEY (`id_cabang`) REFERENCES `cabang`(`id_cabang`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 7. Tabel insentif_cabang (Sesuai kebutuhan sebelumnya)
CREATE TABLE `insentif_cabang` (
  `id_insentif` INT NOT NULL AUTO_INCREMENT,
  `id_cabang` INT NOT NULL,
  `bulan` INT NOT NULL,
  `tahun` YEAR NOT NULL,
  `total_pool_insentif` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_insentif`),
  FOREIGN KEY (`id_cabang`) REFERENCES `cabang`(`id_cabang`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 8. Tabel penggajian_bulanan
CREATE TABLE `penggajian_bulanan` (
  `id_gaji` INT NOT NULL AUTO_INCREMENT,
  `id_user` INT NOT NULL,
  `bulan` INT NOT NULL,
  `tahun` YEAR NOT NULL,
  `nilai_gaji_pokok` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `nilai_insentif` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `total_lembur` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `total_potongan_telat_alfa` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `potongan_bpjs_kesehatan` DECIMAL(10,2) NOT NULL DEFAULT 150000.00,
  `potongan_bpjs_tk` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `komponen_tambahan_lain` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `total_gaji_bersih` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `status` ENUM('draft', 'published') NOT NULL DEFAULT 'draft',
  PRIMARY KEY (`id_gaji`),
  FOREIGN KEY (`id_user`) REFERENCES `users`(`id_user`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 9. Tabel login_attempts (Rate Limiting)
CREATE TABLE `login_attempts` (
  `id_attempt` INT NOT NULL AUTO_INCREMENT,
  `ip_address` VARCHAR(45) NOT NULL,
  `waktu` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `is_success` BOOLEAN NOT NULL DEFAULT FALSE,
  PRIMARY KEY (`id_attempt`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
