-- Migration: 003_seed_jam_kerja
-- Description: Memasukkan jadwal jam kerja resmi PT REN.
-- Senin-Jumat: 07.30 - 16.30, Sabtu: 08.00 - 14.00

USE `db_absensi_pt_ren`;

-- Ambil ID Cabang Pusat
SET @id_cabang_pusat = (SELECT id_cabang FROM cabang WHERE nama_cabang = 'Kantor Pusat Jakarta' LIMIT 1);

-- Senin
INSERT INTO `jam_kerja_cabang` (`id_cabang`, `hari`, `jam_masuk`, `jam_pulang`, `is_libur_akhir_pekan`)
VALUES (@id_cabang_pusat, 'Senin', '07:30:00', '16:30:00', FALSE);

-- Selasa
INSERT INTO `jam_kerja_cabang` (`id_cabang`, `hari`, `jam_masuk`, `jam_pulang`, `is_libur_akhir_pekan`)
VALUES (@id_cabang_pusat, 'Selasa', '07:30:00', '16:30:00', FALSE);

-- Rabu
INSERT INTO `jam_kerja_cabang` (`id_cabang`, `hari`, `jam_masuk`, `jam_pulang`, `is_libur_akhir_pekan`)
VALUES (@id_cabang_pusat, 'Rabu', '07:30:00', '16:30:00', FALSE);

-- Kamis
INSERT INTO `jam_kerja_cabang` (`id_cabang`, `hari`, `jam_masuk`, `jam_pulang`, `is_libur_akhir_pekan`)
VALUES (@id_cabang_pusat, 'Kamis', '07:30:00', '16:30:00', FALSE);

-- Jumat
INSERT INTO `jam_kerja_cabang` (`id_cabang`, `hari`, `jam_masuk`, `jam_pulang`, `is_libur_akhir_pekan`)
VALUES (@id_cabang_pusat, 'Jumat', '07:30:00', '16:30:00', FALSE);

-- Sabtu
INSERT INTO `jam_kerja_cabang` (`id_cabang`, `hari`, `jam_masuk`, `jam_pulang`, `is_libur_akhir_pekan`)
VALUES (@id_cabang_pusat, 'Sabtu', '08:00:00', '14:00:00', FALSE);
