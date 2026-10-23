-- Tambahkan kolom profil pribadi untuk akun staf/admin.
-- Aman dijalankan ulang pada MySQL/MariaDB karena setiap kolom diperiksa dahulu.
SET @schema_name = DATABASE();

SET @has_display_name = (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @schema_name AND TABLE_NAME = 'user' AND COLUMN_NAME = 'display_name'
);
SET @sql = IF(@has_display_name = 0,
    'ALTER TABLE `user` ADD COLUMN `display_name` VARCHAR(100) NULL DEFAULT NULL AFTER `email`',
    'SELECT ''Kolom display_name sudah tersedia'' AS status'
);
PREPARE staff_profile_stmt FROM @sql;
EXECUTE staff_profile_stmt;
DEALLOCATE PREPARE staff_profile_stmt;

SET @has_phone = (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @schema_name AND TABLE_NAME = 'user' AND COLUMN_NAME = 'phone'
);
SET @sql = IF(@has_phone = 0,
    'ALTER TABLE `user` ADD COLUMN `phone` VARCHAR(30) NULL DEFAULT NULL AFTER `display_name`',
    'SELECT ''Kolom phone sudah tersedia'' AS status'
);
PREPARE staff_profile_stmt FROM @sql;
EXECUTE staff_profile_stmt;
DEALLOCATE PREPARE staff_profile_stmt;
