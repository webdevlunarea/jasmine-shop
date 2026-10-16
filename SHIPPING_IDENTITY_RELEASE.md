# Panduan rilis pengamanan identitas produk

## Status dan hasil pengujian

Restore salinan backup berhasil di MySQL Community 8.0.42 localhost terisolasi:
32 tabel website dan 52 tabel MyLuna. Kredensial staging hanya mempunyai akses dua database audit.
Tidak ada .env produksi yang dipakai, server aplikasi lengkap/scheduler tidak dijalankan,
HTTP integrasi dan notifikasi dimock/dimatikan. Instance audit ditutup normal setelah pengujian.

Khusus Windows: `sequelizemeta` diganti menjadi `shipping_legacy_sequelizemeta` hanya dalam
salinan restore agar tidak bertabrakan dengan `SequelizeMeta`. Kedua riwayat tetap dipertahankan;
dump asli tidak diedit. Ini bukan perubahan schema yang diusulkan untuk produksi Linux.

Tes transaksi MySQL meliputi import, retry, kegagalan insert/rollback, rename varian,
stok form lama, penolakan bersamaan, restock bersamaan, dan impor bersamaan diikuti retry.
Audit log/socket/HTTP dimock; pengujian ini bukan uji seluruh UI atau konfigurasi akses produksi.
Tes CI4 memeriksa hash seluruh nilai konstanta dan riwayat stok yang dipilih sebelum/sesudah ALTER,
penyimpanan metadata panjang, serta rollback data uji. File SQL persiapan juga dieksekusi persis
dengan nama target diganti menjadi database audit lokal.

Hasil akhir sesi: 368 tes PHP (termasuk staging), 109 tes backend (termasuk staging),
21 tes JavaScript website dan 10 tes frontend MyLuna lolos; type-check frontend/backend juga lolos.
Snapshot order L00000227 dari salinan backup lolos dry-run dengan mapping MT120 terverifikasi,
tanpa membuat sale baru atau mengubah stok master/varian tersebut. Ini bukan konfirmasi order
produksi sudah diteruskan, dan bukan pengesahan mapping produk lain secara otomatis.

## SQL yang diperlukan

Gunakan `PRODUCTION_SHIPPING_IDENTITY_PREPARE.sql` pada WEBSITE saja.
Perubahan terbatas pada:

1. luna_lunareafurniture.konstanta.value: VARCHAR(255) menjadi TEXT.
2. luna_lunareafurniture.stok.nama: VARCHAR(50) menjadi VARCHAR(100).

Tidak ada SQL wajib untuk wms_db pada patch ini. Jangan menjalankan migration existing MyLuna
hanya untuk mengatasi productId null; produk harus terpetakan, bukan dilonggarkan integritasnya.
SQL tidak mengaktifkan fitur, mengganti ID, mengubah nominal order, atau mengoreksi stok.

## Urutan produksi

1. Ambil backup terbaru mendekati waktu eksekusi; restore-test snapshot lokal bukan jaminan
   snapshot lama mencakup transaksi produksi terbaru. Simpan secara privat.
2. Jadwalkan periode sepi dan tinjau bagian PRECHECK SQL terlebih dahulu.
   Schema yang diharapkan: kedua kolom NOT NULL, collation utf8mb4_general_ci,
   tanpa default khusus atau indeks pada kolom yang diubah. Jika berbeda, hentikan eksekusi.
3. Jalankan dua ALTER pada database website dan periksa POSTCHECK. DDL tidak dapat dibatalkan
   dengan ROLLBACK. lock_wait_timeout hanya membatasi waktu menunggu lock, bukan durasi total ALTER.
   Jika salah satu gagal, jangan menyalakan fitur; periksa kondisi kedua kolom terlebih dahulu.
4. Periksa GitHub Actions remote yang masih antre/berjalan. Workflow lokal MyLuna sudah manual
   tanpa migrate/seed/reconcile. Deployment hanya melalui workflow_dispatch dengan input eksplisit.
5. Review diff dan deploy kode receiver MyLuna lebih dahulu, kemudian website secara terkontrol.
   Periksa hasil GitHub Actions setiap repository; push saja bukan bukti deployment berhasil.
6. Pastikan WEBSITE_ORDER_USER_ID merupakan operator integrasi yang dimaksud, bukan menebak pemilik order.
7. Gabungkan mapping yang diverifikasi ke LUNA_WEB_PRODUCT_MAP pada konfigurasi backend MyLuna.
   Jangan menimpa mapping lain. Pasangan MT120 terverifikasi:

       LUNA_WEB_PRODUCT_MAP='{"B20240116151233":"7ef42be0-9851-4d75-ab2b-13e9acd58990"}'

8. Sync katalog hanya produk yang ID website-nya telah diverifikasi, dimulai dry-run.
   Nama mirip bukan bukti mapping. Pembuatan produk otomatis tetap ditahan.
9. LUNA_PRODUCT_IDENTITY_ENABLED pada website tetap OFF sampai seluruh produk yang dijual terpetakan.
   SQL persiapan boleh selesai tanpa menyalakan flag ini. Jangan aktivasi massal berdasarkan kandidat nama saja.
10. Untuk order historis L00000227, gunakan tombol penerusan existing setelah versi kedua aplikasi,
    mapping, operator dan snapshot pembayaran dikonfirmasi. Periksa satu sale/items serta stok sekali,
    jangan insert pesanan langsung lewat SQL. SQL persiapan tidak meneruskan order tersebut.

## Rollback

Matikan aktivasi/penerusan baru bila perlu. Pertahankan pelebaran kolom dan data yang sudah disimpan.
Jangan otomatis menyusutkan kolom, menghapus mapping, atau mengoreksi stok massal.
Kode yang membaca snapshot UUID harus tetap tersedia untuk order baru; jangan kembali ke versi
yang menghapus/membuat ulang varian saat edit. Rekonsiliasi order spesifik secara terpisah.

## Batas kesiapan

Pengujian kode dan SQL pada salinan sudah dilakukan. Semua produk belum dipetakan secara otomatis,
patch tidak menjalankan perubahan schema/data produksi MyLuna, dan pemeriksaan UI/akses produksi
setelah rilis tetap diperlukan. Tidak ada klaim bebas seluruh bug atau tanpa risiko downtime.

## Pengamanan konfigurasi saat rilis

Preflight produksi menemukan satu entri mapping lama dengan nilai bukan UUID.
Dengan input eksplisit, entri tidak valid dipisahkan dari mapping aktif dan disimpan privat
di backend/.release-private, bersama backup lengkap .env sebelum perubahan.
Nilai valid yang bertentangan tetap menghentikan rilis, bukan ditimpa.
Hanya pasangan MT120 yang telah dikonfirmasi ditambahkan setelah SELECT master dan varian lulus.
Tidak ada nama produk yang digunakan untuk menebak UUID dan tidak ada perubahan stok oleh preflight.
Mapping seluruh katalog dan penerusan order berbayar tetap bukan aksi otomatis deployment.

## Bukti rilis backend

- Commit MyLuna: 0cf2043efae0fcaae52eb99adcb5e119f9dd609a.
- GitHub Actions: https://github.com/AdiityaAnugrah/myluna/actions/runs/38934023491 (success).
- Preflight: passed; mapping MT120 berubah; satu entri invalid diarsipkan; database_writes=false.
- WEBSITE_ORDER_USER_ID belum dikonfigurasi eksplisit. Fallback operator existing tidak diubah;
  tinjau operator integrasi sebelum mengeksekusi ulang order produksi.
- SQL pelebaran dua kolom website telah dikonfirmasi operator berhasil; jangan mengulang ALTER tanpa kebutuhan.
- Pemeriksaan ulang terakhir: 110 tes backend mock, 10 frontend, dan 21 JavaScript website lulus.
  Suite PHP: 368 tes, 2154 assertions, satu tes staging opt-in dilewati pada rerun;
  pengujian restore MySQL sungguhan sebelumnya tetap tercatat di atas.
- Tidak ada order produksi yang diimpor otomatis untuk pengujian rilis.
