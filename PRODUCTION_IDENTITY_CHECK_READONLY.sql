-- Pilih database WEBSITE Lunarea, bukan MyLuna, pada phpMyAdmin.
-- Hanya pembacaan metadata dan produk yang sudah diketahui operator.

SELECT VERSION() AS database_version, DATABASE() AS selected_database;

SELECT TABLE_NAME, ENGINE, TABLE_COLLATION
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = 'luna_lunareafurniture'
  AND TABLE_NAME IN ('barang','konstanta','stok','pemesanan');

SELECT TABLE_NAME, COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE,
       COLUMN_DEFAULT, COLUMN_KEY, EXTRA, CHARACTER_MAXIMUM_LENGTH
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = 'luna_lunareafurniture'
  AND TABLE_NAME IN ('barang','konstanta','stok','pemesanan')
ORDER BY TABLE_NAME, ORDINAL_POSITION;

SELECT TABLE_NAME, INDEX_NAME, NON_UNIQUE, SEQ_IN_INDEX, COLUMN_NAME
FROM information_schema.STATISTICS
WHERE TABLE_SCHEMA = 'luna_lunareafurniture'
  AND TABLE_NAME IN ('barang','konstanta','stok','pemesanan')
ORDER BY TABLE_NAME, INDEX_NAME, SEQ_IN_INDEX;

SELECT TABLE_NAME, COLUMN_NAME, CONSTRAINT_NAME,
       REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
FROM information_schema.KEY_COLUMN_USAGE
WHERE TABLE_SCHEMA = 'luna_lunareafurniture'
  AND TABLE_NAME IN ('barang','konstanta','stok','pemesanan')
  AND REFERENCED_TABLE_NAME IS NOT NULL
ORDER BY TABLE_NAME, CONSTRAINT_NAME, ORDINAL_POSITION;

SELECT id, nama, varian, stok
FROM `luna_lunareafurniture`.`barang`
WHERE id = 'B20240116151233';

-- Nilai konstanta tidak ditampilkan agar konfigurasi lain tetap privat.
SELECT label, COUNT(*) AS row_count
FROM `luna_lunareafurniture`.`konstanta`
WHERE label LIKE 'luna\_product\_identity\_%'
GROUP BY label
HAVING COUNT(*) > 1;
