-- Lunarea account deletion request queue. Run once in phpMyAdmin production.
CREATE TABLE IF NOT EXISTS `customer_account_deletion_requests` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `email_user` VARCHAR(254) NOT NULL,
  `status` VARCHAR(20) NOT NULL DEFAULT 'pending',
  `requested_at` DATETIME NOT NULL,
  `processed_at` DATETIME NULL DEFAULT NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_account_deletion_status_date` (`status`, `requested_at`),
  KEY `idx_account_deletion_email_status` (`email_user`, `status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
