-- Migration: tambah aturan voucher diskon maksimal dan status tidak digabung voucher pengguna baru.
-- Jalankan SQL ini di phpMyAdmin atau MySQL CLI sebelum memakai field baru di menu admin voucher.

ALTER TABLE `voucher`
    ADD COLUMN `max_potongan` INT(11) NULL DEFAULT NULL AFTER `nominal`,
    ADD COLUMN `tidak_gabung_voucher_baru` TINYINT(1) NOT NULL DEFAULT 0 AFTER `max_potongan`;

