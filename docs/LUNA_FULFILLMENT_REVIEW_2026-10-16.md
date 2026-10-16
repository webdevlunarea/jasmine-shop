# Review pesanan sebelum resi — 16 Oktober 2026

## Alur
1. Pembayaran terverifikasi tidak otomatis mengimpor ke MyLuna.
2. Admin website memeriksa rincian, mencentang konfirmasi review, lalu meneruskan.
3. MyLuna menerima order WEBSITE tanpa resi. Nomor pesanan tetap menjadi kunci deduplikasi.
4. TCP mencetak dokumen yang sama dan mengonfirmasi proses. Tanpa resi, tidak dicetak barcode resi palsu.
5. Setelah transaksi TCP commit, callback terautentikasi menyimpan konfirmasi proses di website.
6. Status order website tetap Proses. Admin menyimpan resi, lalu memilih Dalam Pengiriman ketika barang benar-benar dikirim.
7. Resi pertama setelah TCP memproses diterima MyLuna tanpa membuat sale/stock movement baru. Kurir/layanan dan rincian ongkir tetap dijaga.

## Penyimpanan dan keamanan
- Tidak ada SQL/migration/dependency baru.
- Website menyimpan metadata operasional `_app_luna_fulfillment` di JSON `pemesanan.data_mid` yang sudah ada, dengan row lock yang sama dengan pembayaran. Snapshot nominal/waktu pembayaran tetap utuh.
- Payment callback tidak menjadi sumber metadata fulfillment.
- Callback POST `/integrations/luna-order-status` memerlukan `X-Luna-Webhook-Token` yang cocok dengan `LUNA_SYSTEM_WEB_ORDER_TOKEN` (hash_equals), order produksi sudah dibayar, sale ID dan timestamp valid.
- Callback tidak menerima perubahan nominal, stok, pembayaran, resi atau status Dikirim.
- Replay idempotent; acknowledgement received yang terlambat tidak menurunkan processed.
- Order baru yang sudah diterima pusat menunggu konfirmasi proses sebelum dispatch. Pesanan legacy tanpa metadata tetap kompatibel.
- Admin submit tetap memakai autentikasi, role admin dan CSRF existing; konfirmasi review juga diperiksa backend.

## Rilis dua project
1. Backup database sesuai prosedur existing, meskipun tidak ada migration.
2. Rilis website (endpoint penerima callback) dan MyLuna backend/frontend sebagai satu perubahan terkoordinasi. Selama jeda versi, tunda penerusan order tanpa resi.
3. MyLuna membutuhkan build/restart backend dan build frontend sesuai workflow deployment existing. Git push saja bukan bukti deployment berhasil.
4. Token integrasi existing tetap digunakan; jangan ganti atau tampilkan nilainya.
5. URL opsional pada backend MyLuna: `LUNA_WEB_ORDER_STATUS_URL=https://lunareafurniture.com/integrations/luna-order-status`. Bila kosong, gunakan origin URL retur existing jika cocok; selebihnya domain produksi Lunarea. Untuk staging/custom host, tetapkan URL staging secara eksplisit agar tidak mengarah ke produksi.
6. Tidak perlu cron website baru. MyLuna retry setiap 60 detik, batch maksimal 25, tanpa overlap di satu proses. Lock dan idempotensi penerima melindungi callback duplikat dari beberapa worker. Penanda acknowledgement di notes MyLuna bertahan melewati restart; kandidat gagal dirotasi dengan cursor.
7. Sebagai pemulihan manual, admin website dapat mengirim ulang nomor order yang sama: respons membawa status proses MyLuna aktual tanpa membuat sale baru. Jangan mengubah nominal/waktu order agar bisa sinkron.

## Verifikasi staging / produksi terkendali
- Review tanpa centang ditolak; order pending pembayaran/sandbox tidak diteruskan.
- Order produksi paid tanpa resi diterima sekali; retry tidak mengulang stok.
- TCP dapat mencetak dan mengonfirmasi proses tanpa resi; cetak batal tidak otomatis memproses.
- Konfirmasi pusat tampil pada admin dan pembeli setelah refresh; tidak berubah menjadi Dikirim.
- Putus koneksi callback: proses TCP tetap tersimpan; retry / pemeriksaan ulang memulihkan status.
- Admin memasukkan resi pertama setelah processed, menyimpan persiapan, kemudian dispatch; order yang sama diperbarui.
- Resi tetap wajib saat menandai pengiriman termasuk Jasa Kirim Toko.
- Callback tanpa token/salah token, sandbox, unpaid, waktu tidak valid, dan konflik sale ID ditolak.
- Uji order legacy sudah beresi dan halaman invoice/tracking/pembayaran tetap terbaca.

## Rollback
Tunda penerusan dan pemrosesan order baru terlebih dahulu. Rollback kedua repository secara terkoordinasi dengan revert commit rilis dan deploy ulang; jangan menghapus order atau metadata. Versi lama mewajibkan resi sebelum proses, sehingga order tanpa resi yang sudah masuk perlu direkonsiliasi operator sebelum melanjutkan. Tidak ada DROP tabel atau pengembalian stok massal. Unit test lokal menggunakan SQLite in-memory/mock; konektivitas dua server produksi tetap harus diverifikasi setelah rilis.
