# Perbaikan alur order dan pengiriman admin

## Perilaku
- `/editresi`, konfirmasi pembayaran manual, dan penyelesaian pesanan memerlukan session admin + POST + CSRF. Tidak mengaktifkan CSRF global.
- Penyimpanan pengiriman mengunci order dan memperbarui `pemesanan` serta `order_shipments` dalam satu transaksi.
- Hanya order Proses/Dikirim yang dapat dikirim; Selesai tidak boleh mundur. Penyelesaian ulang bersifat idempoten.
- Data penerima/email/item diambil dari database, bukan `data` yang dikirim browser.
- Ekspedisi membutuhkan kurir/resi valid. Kurir dan tarif checkout terstruktur tidak diganti diam-diam.
- Jasa Kirim Toko: Disiapkan → Dalam Pengiriman → Terkirim; Gagal Kirim tersedia sesudah dikirim. Tidak membuat resi palsu.
- Ongkir ditampilkan terpisah; sisa di luar rincian dan ongkir diberi label biaya/penyesuaian, bukan diklaim seluruhnya biaya admin.
- Konfirmasi transfer manual kini memanggil impor order MyLuna. Compare-and-set status mencegah pengulangan efek reward oleh dua request konfirmasi yang bersaing. Ini tidak mengubah rumus reward maupun menggantikan kebutuhan audit transaksi pembayaran secara menyeluruh.

## MyLuna
- URL/token/flag tetap menggunakan `LUNA_SYSTEM_WEB_ORDER_*` existing.
- Pembaruan pengiriman mengirim `event_type: shipping_update` ke endpoint impor order existing.
- Backend MyLuna memeriksa token, mengunci Sale WEBSITE yang sudah tersedia, dan memperbarui blok catatan `[Pengiriman Website]` saja.
- Tidak membuat Sale/SaleItem/StockMovement, tidak mengubah stok, nilai pembayaran, atau status approval/pelunasan MyLuna.
- Order yang belum diimpor ditolak 404, bukan dibuat melalui event pengiriman.
- Respons wajib mengakui `data.shipping_updated: true`. Receiver lama yang hanya mengembalikan `skipped` tidak dianggap sukses.
- API gagal: data pengiriman website tetap tersimpan, admin menerima pesan dan bisa memakai tombol Sinkron ulang pengiriman ke MyLuna. Retry hanya metadata pengiriman, bukan pengulangan impor stok.
- Belum ada outbox/worker retry otomatis atau penanda sinkronisasi durable per order; jangan menganggap retry otomatis sudah tersedia.
- Ketika impor order pembayaran gagal sebelum Sale terbentuk, lakukan rekonsiliasi melalui jalur impor order existing dengan review operator. Tombol retry pengiriman tidak membuat Sale yang hilang.

## Deployment
1. Backup dan uji staging dengan database MySQL/MariaDB serta dua aplikasi.
2. Deploy backend MyLuna dahulu: `webOrderIntegration.controller.ts` dan `utils/websiteShipping.ts`, build melalui prosedur rilis existing.
3. Deploy website setelah receiver baru tersedia. Tidak memerlukan API key baru atau SQL tambahan untuk patch ini; tabel shipping foundation existing tetap diperlukan untuk order shipping v2.
4. Uji order nyata di staging: pembayaran → approval MyLuna → pengiriman → resi → selesai. Pastikan stok hanya berubah saat impor penjualan pertama.
5. Periksa flag integrasi, URL, token, hasil acknowledgment, dan kegagalan jaringan. Tidak ada pemanggilan API produksi/deploy/migration dalam pengerjaan patch ini.

## Pengujian
- PHP: `php vendor/bin/phpunit --no-coverage --no-logging --do-not-cache-result tests/unit/OrderShippingManagerTest.php` (SQLite in-memory eksplisit).
- MyLuna: Jest `websiteShipping.test.ts` dan `websiteShippingIntegration.test.ts` (model/DB dimock), lalu `tsc --noEmit`.
- Browser/staging wajib: token CSRF, akses non-admin, simpan ganda, error timeout, dialog Batal/Hapus, internal tanpa resi, gagal kirim dan pengiriman ulang, legacy tanpa shipment, nominal ongkir dan Midtrans tetap sama.

## Rollback
- Tidak ada migration baru. Pertahankan data shipping serta catatan MyLuna.
- Jika website dirollback, receiver MyLuna baru tetap menerima payload impor lama.
- Jika MyLuna dirollback lebih dahulu, website akan menampilkan sinkronisasi belum diakui; retry setelah receiver dipulihkan. Order website tetap tersimpan.
