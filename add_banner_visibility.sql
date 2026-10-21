-- Jalankan sekali di database produksi Lunarea yang berisi tabel `banner`.
-- Banner lama otomatis memakai `both`, sehingga tetap terlihat di website dan Android.
ALTER TABLE `banner`
    ADD COLUMN `visibility` VARCHAR(10) NOT NULL DEFAULT 'both' AFTER `active`;

-- Verifikasi hasil perubahan.
SELECT `visibility`, COUNT(*) AS `jumlah_banner`
FROM `banner`
GROUP BY `visibility`;
