-- Jalankan sekali pada database produksi Lunarea sebelum memakai Undang Staf.
-- Token asli tidak disimpan; masa berlaku undangan dikelola aplikasi (48 jam).
CREATE TABLE IF NOT EXISTS `admin_staff_invitations` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `email` VARCHAR(254) NOT NULL,
    `token_hash` CHAR(64) NOT NULL,
    `role_key` VARCHAR(32) NOT NULL,
    `status` VARCHAR(16) NOT NULL DEFAULT 'pending',
    `expires_at` DATETIME NOT NULL,
    `created_by` VARCHAR(254) NOT NULL,
    `created_at` DATETIME NOT NULL,
    `accepted_at` DATETIME NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_admin_staff_invite_email` (`email`),
    UNIQUE KEY `uq_admin_staff_invite_token` (`token_hash`),
    KEY `idx_admin_staff_invite_status_expiry` (`status`, `expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
