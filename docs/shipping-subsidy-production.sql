-- Backup, pilih database website yang benar, dan periksa kolom terlebih dahulu.
-- Untuk database tanpa prefix; sesuaikan order_shipments jika memakai DBPrefix.
-- Gunakan salah satu: SQL manual ini ATAU migration CI4 terkait, bukan keduanya.
-- Jika kolom sudah ada, berhenti dan periksa schema/riwayat migration.
-- SQL manual tidak mengisi tabel riwayat migration CI4.
ALTER TABLE `order_shipments`
    ADD COLUMN `shipping_pricing` TEXT NULL;
