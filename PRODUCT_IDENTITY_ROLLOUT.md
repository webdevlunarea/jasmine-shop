# Pemetaan produk website ke MyLuna

Status: kode lokal, belum push/deployment. Tidak ada migration atau perubahan database produksi pada sesi ini.

## Prinsip

- barang.id, wishlist, cart, gambar, rating, invoice, dan order historis tetap memakai ID website existing.
- Metadata tambahan disimpan pada konstanta.label = luna_product_identity_<website_id>, value JSON version/product_id/variants.
- Sinkron katalog hanya menggunakan UUID terhubung atau ID eksplisit website/SKU yang tunggal.
  Nama mirip, slug, dan hardcoded name map tidak lagi mengizinkan overwrite produk.
- Nama varian MyLuna harus unik pada satu produk; setiap varian memiliki UUID.
- Foto pilihan dihubungkan kembali menggunakan UUID ketika nama varian berubah; file gambar tidak dipindah/dihapus.
- Sinkron katalog serial dengan advisory lock dan satu transaksi batch; konflik menggagalkan batch.
- Dry-run tidak menyimpan mapping, stok, atau katalog. Hasil skipped_not_found harus ditinjau, bukan dianggap berhasil terhubung.

## Aktivasi checkout

### Update hasil schema produksi dari operator

Tabel barang/konstanta/stok/pemesanan terverifikasi InnoDB melalui hasil information_schema.
Database website: luna_lunareafurniture. Database aktif pada console operator masih information_schema;
hasil metadata tetap relevan karena TABLE_SCHEMA disebut eksplisit.

Blokir kapasitas yang ditemukan: konstanta.value VARCHAR(255), konstanta.label VARCHAR(50),
barang.id VARCHAR(15), barang.stok VARCHAR(40), stok.nama VARCHAR(50), stok.varian VARCHAR(20).
Guard lokal sekarang menolak payload yang melebihi kapasitas sebelum commit; tidak melakukan pemotongan data.
Metadata MT 120 satu varian diuji muat, tetapi itu bukan bukti semua produk multi-varian muat.
Pembuatan produk otomatis diblokir karena kontrak kolom NOT NULL/default lengkap belum terverifikasi.
Jangan memakai UUID/SKU MyLuna panjang sebagai primary key barang.

Rencana schema-free sebelumnya belum mencakup kebutuhan katalog besar pada value 255 karakter.
Sebelum aktivasi menyeluruh, evaluasi penyimpanan terpisah atau pelebaran terkontrol kolom metadata WEBSITE;
jangan jalankan ALTER produksi sebelum backup/restore-test staging dan rancangan final disetujui.
Schema MyLuna tidak perlu dilonggarkan untuk productId null. Tidak ada DDL yang dijalankan.

Flag tambahan LUNA_PRODUCT_IDENTITY_ENABLED default OFF; tidak ada .env yang diubah.
Saat ON, checkout mengambil UUID dari metadata server sebelum pemakaian poin/pembayaran.
Mapping/varian yang belum tersedia menghentikan checkout dengan pesan pemeriksaan admin.
Browser tidak menjadi sumber UUID dan nominal pembayaran.
Order lama tidak diperkaya ulang dari katalog terbaru; payload snapshot historis tetap utuh.

## Urutan verifikasi

1. Backup dan restore-test staging untuk kedua aplikasi.
2. Verifikasi schema produksi secara read-only: konstanta.value cukup untuk JSON, label cukup panjang,
   tidak ada label duplikat, tabel barang/konstanta/stok memakai engine transactional, advisory lock MySQL/MariaDB tersedia.
3. Review mapping eksplisit website ke products.id MyLuna. Gunakan mapping konfigurasi backend MyLuna yang sama untuk payload katalog dan import.
4. Deploy receiver MyLuna kompatibel dahulu melalui proses terkontrol. Workflow lokal MyLuna telah diubah menjadi manual tanpa migration/seed/reconcile;
   perubahan belum dipush. Verifikasi workflow remote dan run yang sudah berjalan sebelum rollout.
5. Uji katalog dry-run staging dan tinjau seluruh skipped/conflict. Tidak ada auto-backfill berdasarkan nama.
6. Uji sinkron katalog staging, foto varian rename, rollback batch gagal, dan retry.
7. Aktifkan flag identitas hanya setelah seluruh produk yang dijual sudah terpetakan dan diuji checkout staging.
8. Tinjau paid order, retur, cancel, resync, sandbox, dan Midtrans gross_amount tidak berubah.
9. Deployment produksi memerlukan verifikasi backup/schema/mapping dan persetujuan terpisah.

Tidak ada SQL wajib baru untuk rancangan schema-free ini. Itu bukan pernyataan bahwa schema produksi sudah kompatibel;
jika engine/kolom metadata belum sesuai, hentikan deployment dan ajukan perubahan schema terpisah.
Jangan mengganti primary key, menghapus tabel, menjalankan migrate:refresh, atau mengubah pesanan lama.

## Rollback

Matikan flag checkout identity dan flag sinkron yang diperlukan, hentikan penerusan jika ada anomali.
Jangan hapus mapping atau snapshot yang sudah dipakai order. Pertahankan backend MyLuna yang memahami UUID
sampai seluruh order baru selesai; kode lama yang mengganti UUID varian tidak layak menjadi rollback.

## Perkembangan restore-test

Backup kedua aplikasi telah direstore ke MySQL 8.0.42 lokal terisolasi. Dua pelebaran kolom website
sudah diuji dengan pembandingan data, dan guard kapasitas membaca schema aktual.
Panduan terbaru serta urutan produksi: SHIPPING_IDENTITY_RELEASE.md.
SQL persiapan teruji: PRODUCTION_SHIPPING_IDENTITY_PREPARE.sql. Tidak ada SQL produksi yang dijalankan sesi ini.
