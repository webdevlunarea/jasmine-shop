-- Jalankan jika dua tabel suspend sudah pernah dibuat memakai SQL versi lama.
ALTER TABLE `customer_account_suspensions`
  ADD COLUMN `user_message` VARCHAR(250) NOT NULL DEFAULT 'Akun Anda sedang dibatasi. Hubungi Lunarea melalui halaman bantuan.' AFTER `reason`,
  ADD COLUMN `expires_at` DATETIME NULL AFTER `user_message`,
  ADD KEY `idx_customer_suspension_expiry` (`expires_at`);

ALTER TABLE `customer_account_suspension_events`
  ADD COLUMN `user_message` VARCHAR(250) NULL AFTER `reason`,
  ADD COLUMN `expires_at` DATETIME NULL AFTER `user_message`;
