-- Backup first. Select the website database in phpMyAdmin before importing.
-- No customer records or passwords are modified. Adjust table names if DBPrefix is configured.
CREATE TABLE IF NOT EXISTS `customer_usernames` (
    `username` VARCHAR(30) NOT NULL,
    `email_user` VARCHAR(254) NOT NULL,
    PRIMARY KEY (`username`),
    UNIQUE KEY `uq_customer_usernames_email` (`email_user`)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;
