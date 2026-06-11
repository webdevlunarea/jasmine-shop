-- Migration: buat tabel banner homepage
-- Jalankan SQL ini di phpMyAdmin atau MySQL CLI sebelum memakai menu admin banner.

CREATE TABLE IF NOT EXISTS `banner` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `judul` VARCHAR(150) NOT NULL,
    `alt` VARCHAR(180) NULL,
    `link` VARCHAR(255) NULL,
    `gambar` LONGBLOB NOT NULL,
    `urutan` INT(11) NOT NULL DEFAULT 0,
    `active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_active_urutan` (`active`, `urutan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
