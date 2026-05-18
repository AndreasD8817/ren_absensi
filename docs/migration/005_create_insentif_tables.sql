-- Migration: 005_create_insentif_tables
-- Description: Membuat tabel untuk modul insentif yang terpisah dari penggajian
-- Tanggal: 2026-05-17

USE `db_absensi_pt_ren`;

-- Tabel insentif_global: menyimpan data gelondongan per cabang per periode
CREATE TABLE IF NOT EXISTS `insentif_global` (
    `id_insentif`   INT         NOT NULL AUTO_INCREMENT,
    `id_cabang`     INT         NOT NULL,
    `bulan`         TINYINT     NOT NULL COMMENT '1=Januari ... 12=Desember',
    `tahun`         YEAR        NOT NULL,
    `tanggal_input` DATE        NOT NULL,
    `keterangan`    VARCHAR(255) DEFAULT NULL COMMENT 'Contoh: Insentif Kinerja Q2',
    `total_nilai`   DECIMAL(15,2) NOT NULL DEFAULT 0,
    `status`        ENUM('draft','published') NOT NULL DEFAULT 'draft',
    `created_at`    TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_insentif`),
    FOREIGN KEY (`id_cabang`) REFERENCES `cabang`(`id_cabang`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel insentif_pegawai: rincian insentif per individu pegawai
CREATE TABLE IF NOT EXISTS `insentif_pegawai` (
    `id_detail`     INT         NOT NULL AUTO_INCREMENT,
    `id_insentif`   INT         NOT NULL,
    `id_user`       INT         NOT NULL,
    `nilai_didapat` DECIMAL(15,2) NOT NULL DEFAULT 0,
    `status`        ENUM('draft','published') NOT NULL DEFAULT 'draft',
    PRIMARY KEY (`id_detail`),
    FOREIGN KEY (`id_insentif`) REFERENCES `insentif_global`(`id_insentif`) ON DELETE CASCADE,
    FOREIGN KEY (`id_user`)     REFERENCES `users`(`id_user`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
