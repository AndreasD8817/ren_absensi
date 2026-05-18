-- Migration: 008_tahap5_tables
-- Deskripsi: Membuat tabel pengajuan_lembur, kas_denda, dan pengumuman

USE `db_absensi_pt_ren`;

-- =====================================================================
-- TABEL PENGAJUAN LEMBUR
-- =====================================================================
CREATE TABLE IF NOT EXISTS `pengajuan_lembur` (
  `id_lembur` INT NOT NULL AUTO_INCREMENT,
  `id_user` INT NOT NULL,
  `id_cabang` INT NOT NULL,
  `tanggal` DATE NOT NULL,
  `jam_mulai` TIME NOT NULL,
  `jam_selesai` TIME NOT NULL,
  `durasi_jam` DECIMAL(5,2) NOT NULL DEFAULT 0,
  `keterangan` TEXT NOT NULL,
  `status` ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
  `id_admin_approval` INT NULL,
  `alasan_reject` TEXT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_lembur`),
  FOREIGN KEY (`id_user`) REFERENCES `users`(`id_user`) ON DELETE CASCADE,
  FOREIGN KEY (`id_cabang`) REFERENCES `cabang`(`id_cabang`) ON DELETE CASCADE,
  FOREIGN KEY (`id_admin_approval`) REFERENCES `users`(`id_user`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =====================================================================
-- TABEL KAS DENDA CABANG
-- =====================================================================
CREATE TABLE IF NOT EXISTS `kas_denda_cabang` (
  `id_kas` INT NOT NULL AUTO_INCREMENT,
  `id_cabang` INT NOT NULL,
  `tanggal` DATE NOT NULL,
  `jenis` ENUM('pemasukan', 'pengeluaran') NOT NULL,
  `nominal` DECIMAL(15,2) NOT NULL DEFAULT 0,
  `keterangan` TEXT NOT NULL,
  `id_penggajian` INT NULL, -- Jika dari hasil generate gaji
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_kas`),
  FOREIGN KEY (`id_cabang`) REFERENCES `cabang`(`id_cabang`) ON DELETE CASCADE
  -- FOREIGN KEY (`id_penggajian`) REFERENCES `penggajian_bulanan`(`id_gaji`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =====================================================================
-- TABEL PENGUMUMAN (BROADCAST)
-- =====================================================================
CREATE TABLE IF NOT EXISTS `pengumuman` (
  `id_pengumuman` INT NOT NULL AUTO_INCREMENT,
  `id_cabang` INT NULL, -- NULL jika untuk seluruh cabang
  `judul` VARCHAR(255) NOT NULL,
  `isi_pengumuman` TEXT NOT NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_pengumuman`),
  FOREIGN KEY (`id_cabang`) REFERENCES `cabang`(`id_cabang`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Update penggajian_bulanan untuk Overtime
ALTER TABLE `penggajian_bulanan`
  ADD COLUMN `nilai_overtime` DECIMAL(15,2) NOT NULL DEFAULT 0 AFTER `nilai_tunj_lainnya`;
