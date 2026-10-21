-- Lunarea Android API: additive production schema
-- Run once in phpMyAdmin after selecting the Lunarea database.
-- Do not run if any of these four tables already exists.

CREATE TABLE `customer_api_tokens` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `email_user` VARCHAR(254) NOT NULL,
  `family_id` CHAR(36) NOT NULL,
  `token_hash` CHAR(64) NOT NULL,
  `token_type` VARCHAR(12) NOT NULL,
  `password_version` VARCHAR(64) NOT NULL DEFAULT '',
  `device_name` VARCHAR(100) NULL,
  `expires_at` DATETIME NOT NULL,
  `revoked_at` DATETIME NULL,
  `replaced_by_hash` CHAR(64) NULL,
  `last_used_at` DATETIME NULL,
  `created_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_customer_api_tokens_hash` (`token_hash`),
  KEY `idx_customer_api_tokens_email_revoked` (`email_user`, `revoked_at`),
  KEY `idx_customer_api_tokens_family_type` (`family_id`, `token_type`),
  KEY `idx_customer_api_tokens_expires` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `customer_push_devices` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `email_user` VARCHAR(254) NOT NULL,
  `device_token_hash` CHAR(64) NOT NULL,
  `device_token` TEXT NOT NULL,
  `platform` VARCHAR(20) NOT NULL,
  `device_name` VARCHAR(100) NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `last_seen_at` DATETIME NOT NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_customer_push_devices_token_hash` (`device_token_hash`),
  KEY `idx_customer_push_devices_email_active` (`email_user`, `is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `customer_push_notifications` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `email_user` VARCHAR(254) NOT NULL,
  `notification_type` VARCHAR(50) NOT NULL,
  `title` VARCHAR(100) NOT NULL,
  `body` VARCHAR(240) NOT NULL,
  `payload` TEXT NULL,
  `dedupe_key` VARCHAR(190) NOT NULL,
  `status` VARCHAR(16) NOT NULL DEFAULT 'pending',
  `attempts` TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `next_attempt_at` DATETIME NOT NULL,
  `sent_at` DATETIME NULL,
  `last_error` VARCHAR(80) NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_customer_push_notifications_dedupe` (`dedupe_key`),
  KEY `idx_customer_push_notifications_status_next` (`status`, `next_attempt_at`),
  KEY `idx_customer_push_notifications_email_created` (`email_user`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `customer_checkout_requests` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `email_user` VARCHAR(254) NOT NULL,
  `idempotency_key_hash` CHAR(64) NOT NULL,
  `request_hash` CHAR(64) NOT NULL,
  `order_id` VARCHAR(100) NOT NULL,
  `state` VARCHAR(16) NOT NULL DEFAULT 'processing',
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_customer_checkout_email_key` (`email_user`, `idempotency_key_hash`),
  UNIQUE KEY `uq_customer_checkout_order_id` (`order_id`),
  KEY `idx_customer_checkout_state_updated` (`state`, `updated_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Register this migration in the standard CI4 migration ledger so a later
-- `php spark migrate` does not attempt to create the same four tables again.
SET @lunarea_api_migration_exists := (
  SELECT COUNT(*) FROM `migrations`
  WHERE `version` = '2026-10-18-000004'
    AND `class` = 'CreateCustomerApiTokensAndDevices'
);
SET @lunarea_api_migration_batch := (
  SELECT COALESCE(MAX(`batch`), 0) + 1 FROM `migrations`
);
INSERT INTO `migrations` (`version`, `class`, `group`, `namespace`, `time`, `batch`)
SELECT '2026-10-18-000004', 'CreateCustomerApiTokensAndDevices', 'default', 'App', UNIX_TIMESTAMP(), @lunarea_api_migration_batch
WHERE @lunarea_api_migration_exists = 0;

-- Verify the new tables and migration record after execution.
SELECT `TABLE_NAME`, `ENGINE`
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = DATABASE()
  AND `TABLE_NAME` IN (
    'customer_api_tokens',
    'customer_push_devices',
    'customer_push_notifications',
    'customer_checkout_requests'
  )
ORDER BY `TABLE_NAME`;

SELECT `version`, `class`, `group`, `namespace`, `batch`
FROM `migrations`
WHERE `version` = '2026-10-18-000004'
  AND `class` = 'CreateCustomerApiTokensAndDevices';
