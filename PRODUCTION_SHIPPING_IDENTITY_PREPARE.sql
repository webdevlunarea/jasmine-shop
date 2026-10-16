-- LUNAREA WEBSITE ONLY. Tested on an isolated MySQL 8.0.42 restored copy.
-- Prerequisites: current verified backup, scheduled maintenance window,
-- and review that the production columns still match the supplied schema.
-- This file does not enable integrations, migrate product IDs, or adjust inventory.
-- DDL implicitly commits; do not expect START TRANSACTION / ROLLBACK to undo ALTER.

-- PRECHECK: stop if the target columns have unexpected nullability, collation,
-- defaults, type, or indexes. Review these results before executing ALTER below.
SELECT TABLE_SCHEMA, TABLE_NAME, COLUMN_NAME, COLUMN_TYPE,
       IS_NULLABLE, COLUMN_DEFAULT, COLLATION_NAME, COLUMN_KEY
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = 'luna_lunareafurniture'
  AND ((TABLE_NAME = 'konstanta' AND COLUMN_NAME = 'value')
    OR (TABLE_NAME = 'stok' AND COLUMN_NAME = 'nama'));

-- Limits only the wait for metadata locks, NOT total ALTER execution time.
SET SESSION lock_wait_timeout = 10;

ALTER TABLE `luna_lunareafurniture`.`konstanta`
  MODIFY COLUMN `value` TEXT
  CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL;

ALTER TABLE `luna_lunareafurniture`.`stok`
  MODIFY COLUMN `nama` VARCHAR(100)
  CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL;

-- POSTCHECK: expected text / varchar(100), NOT NULL, same collation.
SELECT TABLE_SCHEMA, TABLE_NAME, COLUMN_NAME, COLUMN_TYPE,
       IS_NULLABLE, COLUMN_DEFAULT, COLLATION_NAME
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = 'luna_lunareafurniture'
  AND ((TABLE_NAME = 'konstanta' AND COLUMN_NAME = 'value')
    OR (TABLE_NAME = 'stok' AND COLUMN_NAME = 'nama'));

-- Do not shrink the columns automatically on rollback.
-- Keep the wider schema, disable new feature activation, and investigate first.
-- Do not run any ALTER, UPDATE, DELETE or stock correction in wms_db for this patch.
