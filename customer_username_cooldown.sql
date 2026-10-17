-- Lunarea: add the six-month username-change timestamp if it is not present.
-- Select the production Lunarea database in phpMyAdmin before running.
-- Existing usernames start their first cooldown from deployment time.

SET @add_changed_at_sql = IF(
  (SELECT COUNT(*)
   FROM information_schema.COLUMNS
   WHERE TABLE_SCHEMA = DATABASE()
     AND TABLE_NAME = 'customer_usernames'
     AND COLUMN_NAME = 'changed_at') = 0,
  'ALTER TABLE `customer_usernames` ADD COLUMN `changed_at` DATETIME NULL DEFAULT NULL',
  'SELECT ''Kolom changed_at sudah tersedia; struktur tidak diubah'' AS status'
);

PREPARE add_changed_at_stmt FROM @add_changed_at_sql;
EXECUTE add_changed_at_stmt;
DEALLOCATE PREPARE add_changed_at_stmt;

UPDATE `customer_usernames`
SET `changed_at` = UTC_TIMESTAMP()
WHERE `changed_at` IS NULL;

-- Verify after execution:
-- SHOW COLUMNS FROM `customer_usernames` LIKE 'changed_at';
-- SELECT COUNT(*) AS username_belum_memiliki_timestamp
-- FROM `customer_usernames` WHERE `changed_at` IS NULL;
