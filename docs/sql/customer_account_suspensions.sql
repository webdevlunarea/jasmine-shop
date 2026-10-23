-- Account suspension state and admin audit trail. Run once in production.
CREATE TABLE IF NOT EXISTS `customer_account_suspensions` (
  `email_user` VARCHAR(254) NOT NULL,
  `reason` VARCHAR(500) NOT NULL,
  `user_message` VARCHAR(250) NOT NULL,
  `expires_at` DATETIME NULL,
  `suspended_by` VARCHAR(254) NOT NULL,
  `suspended_at` DATETIME NOT NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  PRIMARY KEY (`email_user`),
  KEY `idx_customer_suspension_date` (`suspended_at`),
  KEY `idx_customer_suspension_expiry` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `customer_account_suspension_events` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `email_user` VARCHAR(254) NOT NULL,
  `action` VARCHAR(20) NOT NULL,
  `reason` VARCHAR(500) NULL,
  `user_message` VARCHAR(250) NULL,
  `expires_at` DATETIME NULL,
  `admin_email` VARCHAR(254) NOT NULL,
  `created_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_suspension_event_customer_date` (`email_user`, `created_at`),
  KEY `idx_suspension_event_action_date` (`action`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
