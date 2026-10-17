-- Back up first, then select the production website database in phpMyAdmin before import.
-- Existing user and buyer data are not altered. The existing user table must use InnoDB.
-- Adjust names if the application uses DBPrefix. Do not drop the sessions table after resets.
CREATE TABLE IF NOT EXISTS `customer_password_reset_codes` (
    `email_user` VARCHAR(254) NOT NULL,
    `request_id` CHAR(64) NOT NULL,
    `otp_hash` VARCHAR(255) NOT NULL,
    `created_at` INT UNSIGNED NOT NULL,
    `expires_at` INT UNSIGNED NOT NULL,
    `attempts` INT UNSIGNED NOT NULL DEFAULT 0,
    `used` TINYINT(1) NOT NULL DEFAULT 0,
    PRIMARY KEY (`email_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `customer_password_sessions` (
    `email_user` VARCHAR(254) NOT NULL,
    `version` CHAR(64) NOT NULL,
    PRIMARY KEY (`email_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `customer_password_reset_limits` (
    `bucket` CHAR(64) NOT NULL,
    `window_start` INT UNSIGNED NOT NULL,
    `hits` INT UNSIGNED NOT NULL DEFAULT 0,
    PRIMARY KEY (`bucket`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
