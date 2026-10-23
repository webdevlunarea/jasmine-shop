-- Jalankan sekali sebelum deploy fitur Direktori Pengguna.
ALTER TABLE `user`
  ADD COLUMN `registration_method` VARCHAR(16) NULL DEFAULT NULL,
  ADD COLUMN `registered_at` DATETIME NULL DEFAULT NULL,
  ADD KEY `idx_user_registration_method_date` (`registration_method`, `registered_at`);

-- Backfill hanya sumber Google/Facebook yang tercatat eksplisit. Akun email lama
-- dibiarkan NULL karena akun sosial tertaut atau reset sandi dapat membuat metode
-- login saat ini berbeda dari metode pendaftaran pertamanya.
UPDATE `user`
SET `registration_method` = `auth_provider`
WHERE `registration_method` IS NULL
  AND `auth_provider` IN ('google', 'facebook');

-- Tanggal pendaftaran akun lama tidak diisi karena sumber tanggal historis
-- yang andal tidak tersedia pada tabel akun.
