-- Gunakan hanya jika tabel android_referral_* lama sudah dibuat dari SQL/migration versi sebelumnya.
-- Backup database, pilih database produksi yang benar, lalu jalankan blok ALTER ini satu kali.
ALTER TABLE `android_referral_settings`
  ADD COLUMN `commission_percent` DECIMAL(5,2) NOT NULL DEFAULT 3.00 AFTER `referrer_reward_points`;

ALTER TABLE `android_referral_attributions`
  ADD COLUMN `commission_percent` DECIMAL(5,2) NOT NULL DEFAULT 3.00 AFTER `referrer_reward_points`,
  ADD COLUMN `channel` VARCHAR(16) NOT NULL DEFAULT 'android' AFTER `commission_percent`;

ALTER TABLE `android_referral_rewards`
  ADD COLUMN `commission_percent` DECIMAL(5,2) NOT NULL DEFAULT 3.00 AFTER `eligible_subtotal`,
  ADD COLUMN `channel` VARCHAR(16) NOT NULL DEFAULT 'android' AFTER `commission_percent`,
  DROP INDEX `uq_android_referral_reward_invitee`;
