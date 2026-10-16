# Kedaluwarsa dan pembatalan pembayaran — 16 Oktober 2026

## Temuan terverifikasi di kode

1. Countdown lama menahan sisa waktu di nol, kemudian memeriksa nilai negatif.
   Kondisi ini tidak pernah terjadi; reload pada cabang tersebut juga dikomentari.
2. Browser mem-parsing waktu tanpa timezone. Controller menghitung selisih dengan
   penambahan tujuh jam dan fungsi date yang dapat membungkus durasi 24 jam.
3. Pembatalan gateway mengirim POST ke Midtrans tetapi tidak menerapkan hasilnya
   ke order lokal. Keberhasilan tampilan bergantung pada callback yang datang belakangan.
4. Endpoint order/cancel/proof belum memverifikasi kepemilikan secara menyeluruh.
   Form konfirmasi existing **sudah POST**; masalahnya bukan tombol memakai GET.
5. Pembatalan transfer manual menulis status dan memulihkan resource tanpa transaksi
   bersama seperti callback. Jalur tersebut kini memakai transaksi status yang sama.
6. Pengembalian stok lama bergantung pada nama produk dan indeks varian yang bisa
   false. Pengembalian poin dapat mengkredit bucket nol lalu menambah lagi ke bucket
   aktif berikutnya. Kedua jalur terminal tersebut diperbaiki dan diuji terisolasi.

Ini hasil audit codebase. Belum ada pemeriksaan ID pesanan aktual, log webhook,
kredensial merchant, konfigurasi notifikasi Midtrans, atau schema/engine DB produksi.

## Perilaku baru

- Callback signed, pembacaan status, pembatalan, dan expiry manual memakai satu
  transaksi dengan row lock dan pembaruan bersyarat. Efek produksi dijalankan sekali.
- Provider host/key mengikuti lingkungan yang tersimpan pada pesanan, bukan akun
  yang sedang melihat. Routing legacy mengikuti aturan email existing.
- Pembatalan: autentikasi + kepemilikan/admin + POST + CSRF; cek status provider,
  panggil cancel hanya bila pending, lalu baca status lagi dan simpan hasil terverifikasi.
  Settlement yang bersamaan tidak ditimpa Dibatalkan. Tidak ada refund otomatis.
- Lookup status memverifikasi order ID, transaction ID, nominal, signature, serta
  transisi. HTTP sukses dapat berisi kode transaksi 407/expire; itu bukan error HTTP.
- Timeout, 404, key salah, nominal/signature tidak cocok: status tetap, bukan dipaksa
  Kadaluarsa atau Dibatalkan. Log tidak memuat key maupun body provider.
- Kedaluwarsa gateway mengikuti status provider, bukan jam browser. Rekonsiliasi
  dipanggil saat membuka order, order pending di cart/checkout, dan transaksi yang
  jatuh tempo. Lookup halaman dibatasi cache marker per lingkungan/order 20 detik.
- Countdown memakai epoch WIB dan waktu server; maksimal lima refresh bertahap,
  bukan loop reload. WebSocket invalid tidak mematikan countdown/tombol.
- Transfer manual tanpa bukti dapat kedaluwarsa dari batas snapshot lokal. Bukti
  yang sudah tersimpan menahan expiry/cancel pembeli sampai pemeriksaan admin.
- Upload bukti memverifikasi pemilik, POST/CSRF, MIME raster dan batas 5 MB, serta
  mengunci order yang sama dengan expiry. Bukti tidak disimpan ke order terminal.
- Stok dikembalikan menggunakan ID produk dan varian, dengan lock inventory.
  Data legacy tanpa ID memakai nama snapshot. Mapping hilang/ambigu menyebabkan
  rollback untuk pemeriksaan, bukan menambah varian yang salah.
- Poin dikembalikan tepat sebesar pakai_poin; bucket berikutnya tidak dikredit lagi
  setelah jumlah terpenuhi, dan tanggal kedaluwarsa asli tidak diperpanjang.
- Order sandbox tidak mengubah stok/voucher/poin produksi atau dikirim ke MyLuna.

## Rekonsiliasi tanpa pengunjung — belum diaktifkan pada server

Callback tetap jalur utama. Job CLI adalah cadangan bila callback terlambat/gagal.

```sh
php spark payments:reconcile --dry-run --limit 25
php spark payments:reconcile --limit 25
```

Dry run hanya menghitung kandidat batch: tanpa API, penulisan order, email, atau
perubahan cursor. Command kedua membaca status API dan dapat mengubah order serta
resource lokal sesuai hasil yang diverifikasi. **Jangan menjalankannya sebagai audit
read-only.** Command tidak memanggil cancel/expire provider secara massal.

Sebelum produksi: backup, uji staging, verifikasi kedua key merchant, akses HTTPS
Midtrans, dan engine tabel transactional/InnoDB. Periksa juga order legacy yang
telah terminal tetapi resource-nya pernah diproses parsial; patch ini bukan pemulihan
massal data historis tersebut. Bukti transfer lewat batas yang sudah terminal perlu
ditangani layanan pelanggan/admin; jangan menghidupkan order terminal otomatis.

Contoh cron operator, ganti path dan binary PHP sesuai hosting:

```cron
* * * * * flock -n /tmp/lunarea-payment-reconcile.lock sh -c 'cd /PATH/TO/jasmine-shop && php spark payments:reconcile --limit 25' >> /PATH/TO/jasmine-shop/writable/logs/payment-reconcile.log 2>&1
```

Pasang hanya satu scheduler aktif, sebagai user aplikasi. Gunakan log rotation.
Cursor keyset berpindah antar batch agar order yang masih pending/gagal sementara
tidak menghalangi semua order berikutnya. Sesuaikan limit/frekuensi setelah mengukur
jumlah pending, waktu API, dan beban server; ini bukan jaminan pembaruan tepat per detik.
Jadwal ini **belum dipasang**, dan tidak ada request cancel produksi yang dijalankan
selama pengembangan. Tanpa scheduler, fallback berlaku saat halaman dibuka.

## Pengujian dan rollback

- SQLite in-memory dan mock provider: sandbox/produksi, expire 407, timeout, amount
  mismatch, pembatalan ulang, settlement/cancel race, kepemilikan, proof/expiry,
  stok per varian dengan produk berganti nama, pengembalian poin/voucher sekali.
- Node: countdown nol, jam server, retry cap/backoff, storage terblokir, background
  tab, dan penghentian refresh saat form dikirim.
- PHP lint, unit/regresi pembeli, callback, shipping, serta invoice.
- Sebelum aktivasi production, uji order sandbox riil dengan key merchant yang benar;
  verifikasi callback endpoint dan hasil status. Mock tidak membuktikan akses merchant.
- Tidak ada SQL/migration/dependency baru pada patch ini.
- Rollback: nonaktifkan hanya scheduler baru lalu revert commit patch yang disetujui.
  Jangan reset repo atau mengembalikan snapshot seluruh DB. Status yang telah sah
  berubah tidak boleh dibalik massal; periksa per order bila ada insiden.

Referensi: [Get Status](https://docs.midtrans.com/reference/get-transaction-status),
[Cancel](https://docs.midtrans.com/reference/cancel-transaction),
[kode transaksi expire](https://docs.midtrans.com/reference/expire-api).
