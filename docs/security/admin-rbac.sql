-- Jalankan di database Lunarea melalui phpMyAdmin.
-- Pemeriksaan INFORMATION_SCHEMA menghindari error jika kolom admin_role sudah dibuat.
SET @db_name = DATABASE();
SET @column_exists = (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @db_name AND TABLE_NAME = 'user' AND COLUMN_NAME = 'admin_role'
);
SET @sql = IF(
    @column_exists = 0,
    'ALTER TABLE `user` ADD COLUMN `admin_role` VARCHAR(32) NULL DEFAULT NULL AFTER `role`',
    'SELECT ''Kolom admin_role sudah tersedia'' AS status'
);
PREPARE rbac_column_stmt FROM @sql;
EXECUTE rbac_column_stmt;
DEALLOCATE PREPARE rbac_column_stmt;

CREATE TABLE IF NOT EXISTS `admin_roles` (
    `role_key` VARCHAR(32) NOT NULL,
    `label` VARCHAR(60) NOT NULL,
    `description` VARCHAR(255) NOT NULL DEFAULT '',
    `created_by` VARCHAR(254) NOT NULL,
    `created_at` DATETIME NOT NULL,
    `updated_at` DATETIME NOT NULL,
    PRIMARY KEY (`role_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `admin_role_permissions` (
    `role_key` VARCHAR(32) NOT NULL,
    `permission_key` VARCHAR(40) NOT NULL,
    PRIMARY KEY (`role_key`, `permission_key`),
    CONSTRAINT `fk_admin_role_permissions_role`
        FOREIGN KEY (`role_key`) REFERENCES `admin_roles` (`role_key`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `admin_role_audit` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `actor_email` VARCHAR(254) NOT NULL,
    `action` VARCHAR(32) NOT NULL,
    `role_key` VARCHAR(32) NOT NULL,
    `target_email` VARCHAR(254) NULL,
    `details` TEXT NOT NULL,
    `created_at` DATETIME NOT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_admin_role_audit_role_date` (`role_key`, `created_at`),
    KEY `idx_admin_role_audit_target_date` (`target_email`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `admin_activity_logs` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `actor_email` VARCHAR(254) NOT NULL,
    `admin_role` VARCHAR(32) NOT NULL,
    `http_method` VARCHAR(10) NOT NULL,
    `route` VARCHAR(255) NOT NULL,
    `response_code` SMALLINT UNSIGNED NOT NULL,
    `created_at` DATETIME NOT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_admin_activity_created_at` (`created_at`),
    KEY `idx_admin_activity_actor_date` (`actor_email`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

UPDATE `user`
SET `admin_role` = 'ADMIN'
WHERE `role` = '1' AND (`admin_role` IS NULL OR `admin_role` = '');

-- Built-in role examples for verified admin accounts (ganti email sebelum menjalankan):
-- UPDATE `user` SET `admin_role` = 'ORDER_ADMIN' WHERE `email` = 'verified@example.com' AND `role` = '1';
-- UPDATE `user` SET `admin_role` = 'CATALOG_ADMIN' WHERE `email` = 'verified@example.com' AND `role` = '1';
-- UPDATE `user` SET `admin_role` = 'CONTENT_ADMIN' WHERE `email` = 'verified@example.com' AND `role` = '1';
-- UPDATE `user` SET `admin_role` = 'FINANCE_ADMIN' WHERE `email` = 'verified@example.com' AND `role` = '1';
-- DEV/SUPER_ADMIN must be assigned only to separately verified, trusted accounts:
-- UPDATE `user` SET `admin_role` = 'DEV' WHERE `email` = 'verified@example.com' AND `role` = '1';
-- UPDATE `user` SET `admin_role` = 'SUPER_ADMIN' WHERE `email` = 'verified@example.com' AND `role` = '1';

SELECT `email`, `role`, `admin_role` FROM `user` WHERE `role` = '1' ORDER BY `email`;
