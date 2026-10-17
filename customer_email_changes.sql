-- Lunarea: storage for secure customer email-change verification.
-- Select the production Lunarea database in phpMyAdmin before running.
-- Creates only this new table; no existing records are modified.

CREATE TABLE `customer_email_changes` (
  `email_user` VARCHAR(254) NOT NULL,
  `pending_email` VARCHAR(254) NULL,
  `otp_hash` VARCHAR(255) NULL,
  `request_id` CHAR(36) NULL,
  `expires_at` DATETIME NULL,
  `requested_at` DATETIME NULL,
  `attempts` TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `changed_at` DATETIME NULL,
  `created_at` DATETIME NULL,
  `updated_at` DATETIME NULL,
  PRIMARY KEY (`email_user`),
  UNIQUE KEY `uq_customer_email_changes_pending_email` (`pending_email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Verify after execution:
-- SHOW CREATE TABLE `customer_email_changes`;
