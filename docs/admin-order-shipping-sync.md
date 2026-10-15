# Pesanan Lunarea -> MyLuna: wajib resi sebelum impor

## Aturan bisnis saat ini
1. Midtrans settlement / konfirmasi transfer manual hanya menandai website **Proses**. Tidak mengimpor ke MyLuna, termasuk callback berulang.
2. Admin website wajib memilih ekspedisi yang sesuai checkout dan mengisi resi. **Jasa Kirim Toko juga wajib resi internal toko**, diisi admin, bukan dihasilkan otomatis.
3. Menyimpan resi pada tahap **Disiapkan** menjaga status website Proses. Tidak mengirim email barang sudah dikirim dan tidak memanggil impor otomatis.
4. Setelah memeriksa data, admin menekan **Kirim pesanan ke MyLuna** pada detail `/listcustomer`.
5. MyLuna menolak impor baru tanpa resi, termasuk internal delivery. Impor pertama menjadi Sale WEBSITE / WAITING_APPROVAL (Menunggu Proses/Packing). Stok produk terpetakan dikurangi pada impor pertama sesuai perilaku existing, bukan saat resi disimpan.
6. TCP/pusat tetap perlu memproses/packing sesuai ekspedisi. Resi ditampilkan pada antrean dan catatan/cetakan pesanan. Label Cetak Resi existing MyLuna tidak berarti membuat AWB provider baru.
7. Admin memperbarui tahap Dalam Pengiriman hanya saat barang benar-benar dikirim, kemudian Terkirim saat diterima. Tidak ada callback otomatis dari packing MyLuna ke status pengiriman website pada scope ini.

## Keamanan dan snapshot
- POST `/syncorder/{id}` dan `/editresi`: login admin role 1, POST, CSRF server-side; tidak mengaktifkan CSRF global.
- Snapshot order dibaca ulang dari database. Item, harga, jumlah, gross amount, penerima dan catatan tidak berasal dari browser atau harga produk terkini.
- Tanggal penjualan berasal dari data_mid.transaction_time asli; WIB dikirim sebagai ISO +07:00. Waktu mentah/data_mid tetap ada. Jika waktu hilang/invalid, impor ditolak, bukan diganti jam sekarang.
- Audit penerimaan/createdAt MyLuna tetap waktu impor aktual, bukan dipalsukan sebagai waktu pembelian.
- Harga normal/diskon historis yang tidak pernah disimpan tidak dibuat-buat. Nominal biaya dan subsidi tetap dari snapshot shipping existing.
- Nomor pesanan yang sudah ada tidak dibuat ulang; SaleNumber unique tetap wajib tersedia pada schema existing. Tidak mengulang SaleItem/StockMovement atau menimpa tanggal/total/status operasional pesanan lama.
- Pesanan MyLuna yang pernah dibatalkan/ditolak, termasuk suffix CANCELLED/REJECTED dari hook, ditolak dan memerlukan rekonsiliasi operator.
- Sandbox ditahan sebelum operasional produksi. Website tidak mengulang penagihan Midtrans, poin/voucher atau pengurangan stok website saat sinkron ulang.
- Timeout dapat berarti penerima sudah commit tetapi respons hilang. Cari nomor pesanan dulu sebelum mencoba ulang. Jangan membuat pesanan/pembayaran pengganti.

## Pengiriman dan tracking
- Resi disimpan pada pemesanan.resi dan order_shipments.tracking_number jika tabel tersedia. Tarif, layanan dan subsidi checkout tidak ditimpa.
- Kurir API dan layanan dikirim terpisah dari nominal jika snapshot tersedia. Jasa Kirim Toko tetap internal_delivery, bukan kurir eksternal.
- Tracking internal menampilkan resi toko serta status tersimpan; tidak memanggil BinderByte untuk resi internal. Belum ada posisi GPS realtime.
- Event shipping_update hanya memperbarui catatan pengiriman pada Sale existing. Tidak membuat Sale atau memutasi stok. Jika pesanan belum ada di MyLuna, event ditolak: simpan resi dan impor melalui tombol pesanan terlebih dahulu.
- Pesanan lama tetap terbaca. Tidak ada backfill atau perubahan masal terhadap order lama. Pesanan lama tanpa resi harus direkonsiliasi sebelum impor baru; bukan diberi nomor palsu.

## Deployment dan pengujian
Tidak ada migration/SQL baru. Menggunakan kolom order dan shipment existing. Deploy Lunarea dan backend/frontend MyLuna bersama; build MyLuna sesuai prosedur existing. Push bukan bukti deployment berhasil.
Uji staging: paid tanpa resi tidak masuk; resi eksternal/internal kosong/placeholder ditolak; simpan resi tetap Proses; kirim valid masuk WAITING_APPROVAL; retry tidak mengubah stok lagi; sandbox, CSRF/role salah, timeout, tanggal WIB dan pembatalan terarsip diuji.
Pada produksi cari **Penjualan -> WEBSITE**, nomor Lunarea; antrean proses tersedia di `/sales/process` sesuai role. Filter bisa membatasi visibilitas.
Belum ada outbox/worker retry otomatis. Kegagalan spesifik L00000227 belum terkonfirmasi dari log produksi dan pesanan belum dikirim ulang oleh agen.


## Penguatan cetak dan koreksi resi
- Cetakan TCP menampilkan saleDate asli dalam WIB (Intl timeZone Asia/Jakarta), bukan createdAt impor. Website tanpa tanggal asli tidak diberi tanggal sekarang; ditandai belum tersedia.
- Nomor pesanan dan resi berbeda: barcode pesanan tetap berlabel nomor pesanan. Resi website dicetak terpisah, dengan barcode CODE128 bila panjang/karakternya sesuai. Resi panjang/non-ASCII tetap lengkap sebagai teks, bukan dipotong/diperkecil paksa.
- Menutup/membatalkan dialog cetak atau membuka PDF tidak otomatis memproses. Operator harus memilih **Sudah benar, proses pesanan**. **Belum/Batal** tidak mengubah status. Sistem tidak mengklaim bisa membuktikan cetak fisik.
- Konfirmasi website membawa snapshot blok pengiriman yang dicetak. Backend mengunci Sale dan membandingkannya dengan data terkini; perubahan resi saat operator mencetak menyebabkan penolakan konfirmasi lama dan meminta cetak ulang.
- Koreksi resi website tersimpan sebagai draft sampai admin menekan Kirim pesanan ke MyLuna. Hanya status MyLuna WAITING_APPROVAL/APPROVED yang dapat menerima resi/kurir/layanan baru, dengan revisi order_shipments.updated_at yang lebih baru. Revision sama/lebih lama ditolak, bukan menimpa data baru. Jika data legacy tidak memiliki revisi, perubahan perlu rekonsiliasi operator.
- Pesanan yang sudah PROCESSED/SETTLED/COMPLETED tidak menerima pergantian resi lewat sinkron ulang. Pesan penolakan harus ditindaklanjuti; draft resi website tidak boleh dianggap sudah dipakai TCP.
- Respons MyLuna wajib mengakui tracking_number yang benar-benar tersimpan. Lunarea tidak menyatakan sukses jika nomor order sama tetapi resinya berbeda atau receiver lama belum mendukung pengakuan resi.
- Deploy backend/frontend MyLuna dan Lunarea bersamaan. Client lama yang memproses order WEBSITE tanpa konfirmasi snapshot akan ditolak backend. Tidak ada perubahan schema/SQL. Tidak menambahkan status callback otomatis MyLuna -> website.
- Pengujian meliputi mock transport/impor, aturan resi/revisi/cetakan dan typecheck. Validasi printer/PDF fisik serta penerimaan deployment produksi tetap langkah terpisah; tidak dijalankan pada transaksi nyata oleh agen.
