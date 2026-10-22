CREATE TABLE IF NOT EXISTS `customer_totp_mfa` (
  `email_user` VARCHAR(254) NOT NULL,
  `secret_cipher` TEXT NULL,
  `pending_secret_cipher` TEXT NULL,
  `enabled` TINYINT(1) NOT NULL DEFAULT 0,
  `last_counter` BIGINT NOT NULL DEFAULT -1,
  `recovery_codes` TEXT NOT NULL,
  `challenge_hash` CHAR(64) NULL,
  `challenge_expires_at` DATETIME NULL,
  `challenge_device_name` VARCHAR(100) NULL,
  `challenge_attempts` TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  PRIMARY KEY (`email_user`),
  UNIQUE KEY `uq_customer_totp_mfa_challenge` (`challenge_hash`),
  KEY `idx_customer_totp_mfa_expiry` (`challenge_expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
