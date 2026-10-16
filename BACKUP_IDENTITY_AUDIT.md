# Audit backup lokal — 16 Oktober 2026

Backup dibaca sebagai file, tanpa menjalankan SQL dan tanpa mengimpor data.
Keberadaan/checksum backup bukan bukti backup sudah berhasil di-restore.

## Bukti file

| File privat | Byte | SHA-256 |
|---|---:|---|
| REFERENSI/luna_lunareafurniture.sql | 232299442 | 4ed098d9926afe240a6f6dda92cd2a1572ec3565f5d2e61114bea91b901d3c27 |
| REFERENSI/wms_db.sql | 35912860 | 786cf158e224120b59d716d37471a78ff65c2f7b1998f30be5c6fa803a69188d |

Dump menyebut MySQL 8.0.42. Binary XAMPP lokal adalah MariaDB 10.4.32.
Dump MyLuna memakai collation utf8mb4_0900_ai_ci pada sebagian tabel.
Jangan mengganti collation massal pada dump asli atau mengimpor langsung ke database aktif.
Restore-test harus dilakukan pada MySQL 8 yang kompatibel, dengan datadir/database, port dan credential terisolasi.
Salinan staging harus mematikan email, integrasi HTTP, scheduler dan akses produksi sebelum aplikasi dijalankan.

## Hasil pembacaan katalog offline

- Website: 113 produk. MyLuna: 99 produk dan 176 varian.
- Tidak ditemukan SKU MyLuna yang sama persis dengan ID produk website. Ini bukan bukti semua mapping belum ada;
  hubungan eksplisit harus diaudit terpisah dan tidak dibentuk otomatis dari nama.
- 7 produk MyLuna menghasilkan JSON identitas lebih panjang dari 255 karakter, maksimum 472 byte dalam format saat ini.
- Tidak ditemukan duplikasi value varian di dalam satu produk MyLuna pada snapshot ini.
- 63 produk mempunyai stok induk berbeda dari jumlah stok variannya. Tidak disimpulkan sebagai kerusakan;
  kemungkinan beda aturan stok, snapshot historis, atau transaksi perlu ditelusuri. Tidak ada koreksi stok otomatis.
- 6 nama produk website melebihi batas stok.nama (50 karakter).
- Pasangan MT 120 dan UUID varian CHERRY HITAM yang diberikan operator ada dalam kedua backup.

Angka di atas menggambarkan backup, bukan pembacaan database produksi saat ini.
Audit parser offline bukan pengganti validasi engine, collation, constraint dan transaksi pada restore-test.

## Schema yang dikonfirmasi dari dump

- konstanta.id auto increment; label VARCHAR(50), value VARCHAR(255), keduanya NOT NULL, collation utf8mb4_general_ci.
- Tidak ada indeks pada konstanta.value yang terlihat dalam DDL dump.
- barang.id VARCHAR(15); stok list VARCHAR(40); beberapa kolom NOT NULL tanpa default.
- sale_items.productId NOT NULL; dua foreign key berbeda mengarah ke products.id.
  Jangan menghapus constraint duplikat saat perbaikan mapping ini.
- sales.notes TEXT; sale_return_items.saleItemId menunjuk item pesanan asli.

## Rancangan penyesuaian website — BELUM DIJALANKAN

Untuk memuat metadata seluruh katalog, opsi minimum adalah melebarkan konstanta.value pada database WEBSITE saja.
Ini tidak memerlukan penggantian ID atau pelebaran nullable productId MyLuna.

Rancangan DDL untuk diuji pada salinan staging, bukan instruksi eksekusi produksi:

```sql
ALTER TABLE `luna_lunareafurniture`.`konstanta`
  MODIFY COLUMN `value` TEXT
  CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL;
```

Default/collation/nullability dipertahankan sesuai dump. ALTER dapat mengambil metadata lock atau rebuild tabel;
jadwal, pemakaian tabel bersama fitur lain, dan durasi harus diuji. Jangan mengasumsikan operasi tanpa downtime.
Kode lokal membaca kapasitas kolom aktual: tetap menolak data >255 pada schema lama,
dan baru menerima data lebih besar jika schema yang dibaca memang mendukungnya.

Rollback: hentikan aktivasi/sinkron baru dan pertahankan kolom TEXT/data yang sudah dipakai.
Jangan otomatis menyusutkan kembali ke VARCHAR(255); data baru bisa terpotong.
Downgrade hanya setelah pemeriksaan seluruh nilai, rencana pemulihan, dan persetujuan terpisah.

## Perlindungan backup

Folder REFERENSI sudah ditambahkan ke .gitignore lokal. Tidak ada backup yang di-stage/commit/unggah.
.htaccess Require all denied ditambahkan sebagai defense-in-depth Apache; efektivitasnya bergantung konfigurasi server.
Simpan backup di lokasi privat di luar document root untuk perlindungan utama. File asli tidak dipindah atau diubah.

## Status

Restore dan tes transaksi kini sudah dijalankan di MySQL 8.0.42 lokal terisolasi; lihat SHIPPING_IDENTITY_RELEASE.md.
Kedua backup asli diperiksa ulang dan SHA-256 tetap sama. MyLuna pada staging Windows mempertahankan
dua tabel migration dengan mengganti nama salah satunya hanya pada salinan.
Belum ada DDL produksi atau push/deployment.
