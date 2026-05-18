-- Migration: 002_seed_dummy_data
-- Description: Menambahkan data awal cabang dan akun user (Pegawai & Superadmin) untuk testing.
-- Password untuk semua akun adalah: password123 (Sudah di-hash dengan Bcrypt)

USE `db_absensi_pt_ren`;

-- 1. Insert Cabang Pusat
INSERT INTO `cabang` (`nama_cabang`, `latitude`, `longitude`, `radius_meter`, `tarif_lembur_per_jam`, `denda_alfa`)
VALUES ('Kantor Pusat Jakarta', '-6.2088', '106.8456', 50, 20000.00, 50000.00);

-- Ambil ID Cabang Pusat
SET @id_cabang_pusat = LAST_INSERT_ID();

-- 2. Insert Superadmin
INSERT INTO `users` (`id_cabang`, `nip`, `nama_lengkap`, `password`, `role`, `jabatan`, `gaji_pokok`, `is_active`)
VALUES (@id_cabang_pusat, 'admin', 'Super Administrator', '$2y$10$ubAOyYBBfZ8VaKbM.dLqY..vwvmBB/.SvA6MkKnpHibFH7h7C8z52', 'superadmin', 'Direktur', 10000000.00, 1);

-- 3. Insert Pegawai
INSERT INTO `users` (`id_cabang`, `nip`, `nama_lengkap`, `password`, `role`, `jabatan`, `gaji_pokok`, `is_active`)
VALUES (@id_cabang_pusat, '123456', 'Budi Santoso', '$2y$10$ubAOyYBBfZ8VaKbM.dLqY..vwvmBB/.SvA6MkKnpHibFH7h7C8z52', 'pegawai', 'Staff IT', 5000000.00, 1);
