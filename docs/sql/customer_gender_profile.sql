ALTER TABLE `pembeli`
  ADD COLUMN `gender` ENUM('pria', 'wanita') NULL DEFAULT NULL AFTER `nohp`,
  ADD COLUMN `gender_changed_at` DATETIME NULL DEFAULT NULL AFTER `gender`;

SELECT `gender`, COUNT(*) AS `jumlah_akun`
FROM `pembeli`
GROUP BY `gender`;
