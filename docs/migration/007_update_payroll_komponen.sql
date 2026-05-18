-- Migration: 007_update_payroll_komponen
-- Deskripsi: Tambah kolom tunjangan & status_pajak ke users, dan kolom komponen BPJS lengkap ke penggajian_bulanan

USE `db_absensi_pt_ren`;

-- =====================================================================
-- TABEL PENGGAJIAN_BULANAN: Rombak kolom komponen BPJS lengkap
-- =====================================================================
-- Hapus kolom lama jika ada (MySQL 8.0 compatible)
ALTER TABLE `penggajian_bulanan`
  DROP COLUMN `potongan_bpjs_kesehatan`,
  DROP COLUMN `potongan_bpjs_tk`;

-- Tambah kolom baru: snapshot tunjangan, BPJS perusahaan, BPJS pegawai, pajak, denda
ALTER TABLE `penggajian_bulanan`
  -- Pendapatan Snapshot
  ADD COLUMN `nilai_tunj_jabatan`      DECIMAL(15,2) NOT NULL DEFAULT 0  AFTER `nilai_gaji_pokok`,
  ADD COLUMN `nilai_tunj_transportasi` DECIMAL(15,2) NOT NULL DEFAULT 0  AFTER `nilai_tunj_jabatan`,
  ADD COLUMN `nilai_tunj_makan`        DECIMAL(15,2) NOT NULL DEFAULT 0  AFTER `nilai_tunj_transportasi`,
  ADD COLUMN `nilai_tunj_kehadiran`    DECIMAL(15,2) NOT NULL DEFAULT 0  AFTER `nilai_tunj_makan`,
  ADD COLUMN `nilai_tunj_lainnya`      DECIMAL(15,2) NOT NULL DEFAULT 0  AFTER `nilai_tunj_kehadiran`,
  -- Tunjangan BPJS Perusahaan (disimpan untuk laporan perusahaan)
  ADD COLUMN `tunj_jht_37`            DECIMAL(15,2) NOT NULL DEFAULT 0  AFTER `nilai_tunj_lainnya`,
  ADD COLUMN `tunj_jkk_024`           DECIMAL(15,2) NOT NULL DEFAULT 0  AFTER `tunj_jht_37`,
  ADD COLUMN `tunj_jk_03`             DECIMAL(15,2) NOT NULL DEFAULT 0  AFTER `tunj_jkk_024`,
  ADD COLUMN `tunj_bpjs_kes_4`        DECIMAL(15,2) NOT NULL DEFAULT 0  AFTER `tunj_jk_03`,
  ADD COLUMN `tunj_jp_2`              DECIMAL(15,2) NOT NULL DEFAULT 0  AFTER `tunj_bpjs_kes_4`,
  -- Potongan BPJS Pegawai
  ADD COLUMN `pot_jht_2`              DECIMAL(15,2) NOT NULL DEFAULT 0  AFTER `tunj_jp_2`,
  ADD COLUMN `pot_bpjs_kes_1`         DECIMAL(15,2) NOT NULL DEFAULT 0  AFTER `pot_jht_2`,
  ADD COLUMN `pot_jp_1`               DECIMAL(15,2) NOT NULL DEFAULT 0  AFTER `pot_bpjs_kes_1`,
  ADD COLUMN `pot_pph21`              DECIMAL(15,2) NOT NULL DEFAULT 0  AFTER `pot_jp_1`,
  -- Denda (disimpan di DB untuk transparansi internal Superadmin, tidak muncul di slip pegawai)
  ADD COLUMN `denda_terlambat`        DECIMAL(15,2) NOT NULL DEFAULT 0  AFTER `total_potongan_telat_alfa`,
  ADD COLUMN `denda_alfa`             DECIMAL(15,2) NOT NULL DEFAULT 0  AFTER `denda_terlambat`,
  ADD COLUMN `status_pajak_snapshot`  VARCHAR(10)   NOT NULL DEFAULT 'TK/0' AFTER `denda_alfa`;
