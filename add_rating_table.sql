-- Migration: Buat tabel rating untuk produk
-- Jalankan SQL ini di phpMyAdmin atau MySQL CLI

CREATE TABLE IF NOT EXISTS `rating` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `id_barang` VARCHAR(50) NOT NULL,
    `email_cus` VARCHAR(150) NOT NULL,
    `nama_pembeli` VARCHAR(150) NOT NULL,
    `rating` TINYINT(1) NOT NULL,
    `komentar` TEXT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uniq_barang_email` (`id_barang`, `email_cus`),
    KEY `idx_barang` (`id_barang`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Jika tabel sudah ada dengan id_barang INT, jalankan ALTER di bawah agar mendukung ID produk alphanumeric:
-- ALTER TABLE `rating` MODIFY `id_barang` VARCHAR(50) NOT NULL;
