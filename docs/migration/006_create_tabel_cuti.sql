-- Migration: 006_create_tabel_cuti
-- Description: Membuat tabel untuk fitur pengajuan cuti, sakit, dan izin pegawai
-- Tanggal: 2026-05-17

USE `db_absensi_pt_ren`;

CREATE TABLE IF NOT EXISTS `pengajuan_cuti` (
    `id_cuti`         INT NOT NULL AUTO_INCREMENT,
    `id_user`         INT NOT NULL,
    `jenis`           ENUM('Cuti','Sakit','Izin') NOT NULL,
    `tanggal_mulai`   DATE NOT NULL,
    `tanggal_selesai` DATE NOT NULL,
    `keterangan`      TEXT NOT NULL,
    `bukti_foto`      VARCHAR(255) DEFAULT NULL COMMENT 'Nama file foto surat dokter/izin',
    `status`          ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    `id_approver`     INT DEFAULT NULL COMMENT 'ID Admin Cabang yang menyetujui/menolak',
    `tanggal_respon`  DATETIME DEFAULT NULL,
    `created_at`      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_cuti`),
    FOREIGN KEY (`id_user`) REFERENCES `users`(`id_user`) ON DELETE CASCADE,
    FOREIGN KEY (`id_approver`) REFERENCES `users`(`id_user`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
