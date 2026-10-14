-- Pilih database website yang benar dan backup sebelum menjalankan.
-- Untuk instalasi tanpa DBPrefix. Jika memakai prefix, sesuaikan nama tabel.
-- Pilih SATU cara: SQL manual ini ATAU migration CI4, bukan keduanya.
-- Jika tabel sudah ada, hentikan dan periksa schema; jangan hapus tabel.
-- SQL ini tidak mencatat migration CI4 sebagai sudah dijalankan.

CREATE TABLE `customer_addresses` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `email_user` VARCHAR(254) NOT NULL,
    `label` VARCHAR(60) NOT NULL,
    `recipient_name` VARCHAR(120) NOT NULL,
    `recipient_phone` VARCHAR(25) NOT NULL,
    `address_data` TEXT NOT NULL,
    `is_default` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` DATETIME NULL DEFAULT NULL,
    `updated_at` DATETIME NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `email_user_is_default` (`email_user`, `is_default`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
