-- Migration: 004_update_jam_kerja
-- Description: Update jam kerja PT REN sesuai jadwal resmi terbaru.
-- Senin-Jumat: 08.00 - 16.30, Sabtu: 08.00 - 13.00

USE `db_absensi_pt_ren`;

-- Update Senin-Jumat
UPDATE `jam_kerja_cabang` SET `jam_masuk` = '08:00:00', `jam_pulang` = '16:30:00'
WHERE `hari` IN ('Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat');

-- Update Sabtu
UPDATE `jam_kerja_cabang` SET `jam_masuk` = '08:00:00', `jam_pulang` = '13:00:00'
WHERE `hari` = 'Sabtu';
