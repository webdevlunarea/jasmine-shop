# Optimasi performa Lunarea — 15 Oktober 2026

## Bukti awal

Pengukuran GET publik satu kali dari lingkungan pengembangan, memakai curl
`--compressed`, tanpa login. Ini bukan rata-rata dan bukan pengukuran browser/LCP.

| Halaman produksi | HTTP | TTFB | Total transfer | Ukuran transfer curl |
| --- | --- | --- | --- | --- |
| `/` | 200 | 0,509 detik | 1,053 detik | 5.198.619 byte |
| `/all` | 200 | 1,751 detik | 11,132 detik | 2.405.693 byte |

Header publik menunjukkan Cloudflare dan LiteSpeed. CSS sudah mendapat cache
publik tujuh hari dari hosting, tetapi template memakai `time()` pada URL CSS
utama sehingga URL berubah setiap render. Konfigurasi origin server belum diaudit
melalui SSH. Hasil HEAD `/` tidak dipakai untuk menilai kesehatan halaman karena
GET aktual menghasilkan 200.

## Perubahan lokal

- `template.php`: versi CSS mengikuti waktu perubahan file, bukan setiap request.
- Kartu beranda, flash sale, katalog, rekomendasi produk/artikel: gambar tidak lagi
  disalin sebagai base64 ke HTML. URL `/imgpic/{id}` mendapat versi hash konten,
  lazy loading, dan decoding async. Harga, varian, watermark, dan tautan dipertahankan.
- `PublicCatalogImage`: MIME sesuai gambar, cache publik terbatas 300 detik,
  ETag/If-None-Match dan respons 304; gambar hilang/rusak menghasilkan 404 no-store.
- Endpoint produk/banner hanya mengambil kolom gambar, bukan seluruh row.
- Banner pertama diprioritaskan; banner berikutnya lazy. Versi banner fallback
  mengikuti filemtime agar tidak berubah setiap hari tanpa perubahan gambar.

Tidak ada konversi/penimpaan gambar asli, migration, perubahan pembayaran,
deployment, restart layanan, atau pengubahan konfigurasi server. Galeri utama dan
wishlist varian sengaja tetap mengikuti pemetaan foto existing.

## Batas dan tindak lanjut

Ukuran HTML berkurang; total byte seluruh gambar dan kecepatan produksi setelah
deploy **belum diukur**. Request gambar pertama masih mengambil blob dari DB.
Query katalog juga masih mengambil blob untuk membentuk versi konten. Cache
browser mengurangi unduhan berulang, bukan menghilangkan seluruh pekerjaan DB.

Jangan menerapkan cache publik pada akun, checkout, pembayaran, bukti pembayaran,
API autentikasi, atau halaman admin. Jangan mengaktifkan aturan CDN "cache everything"
untuk seluruh situs. Header Set-Cookie/session dapat membuat CDN melewati cache
gambar; evaluasi lewat staging dan jangan menghapus cookie secara global.

Prioritas audit berikutnya:

1. Verifikasi profil SSH yang benar: RAM/CPU, disk/inode, I/O, PHP workers,
   OPcache, versi PHP, LiteSpeed, compression, DB connections/slow queries,
   dan jumlah worker PM2 MyLuna. Read-only sebelum memutuskan tuning.
2. Periksa query kategori berulang di `KonstantaModel::getCategoryImages()`;
   banner metadata beranda masih mengambil blob. Optimasi query diuji terpisah.
3. Evaluasi thumbnail WebP/AVIF berukuran kartu dengan penyimpanan turunan dan
   fallback; jangan mengonversi seluruh database tanpa backup dan uji kualitas.
4. Verifikasi apakah SVG/PNG besar di `public/img` benar-benar dipakai sebelum
   mengoptimalkan atau menghapus. Ukuran file besar saja bukan bukti bottleneck.
5. MyLuna: periksa header aset `_next/static` aktual sebelum mengubah aturan
   no-store global; uploads dapat berisi dokumen pribadi, bukan semuanya aset publik.

## Validasi dan rollout

- Jalankan tes unit/regresi lokal (katalog, auth, pembayaran, shipping, order sync),
  PHP lint, dan `git diff --check`.
- Staging: pastikan semua foto sesuai ID, MIME benar, foto yang diubah mendapat URL
  baru, ETag cocok menghasilkan 304, versi lama mendapat konten baru setelah cache
  habis, gambar hilang 404, watermark/varian tetap benar.
- Bandingkan cold/warm browser load pada mobile dan desktop: HTML transferred,
  jumlah request gambar sebelum scroll, LCP, TTFB, total transfer, error server,
  DB/CPU. Ulang beberapa kali dengan kondisi jaringan sama.
- Regresi login, checkout, callback dan order sync; pastikan data pribadi tidak
  mendapat header public. Jangan membuat pesanan produksi untuk tes performa.
- Rollback melalui revert commit optimasi yang disetujui, bukan reset semua
  perubahan. Tidak membutuhkan rollback database. Cache gambar publik maksimal
  lima menit; batalkan aturan CDN terpisah bila sebelumnya ditambahkan operator.

Referensi perilaku browser:
[HTTP caching](https://developer.mozilla.org/en-US/docs/Web/HTTP/Guides/Caching),
[elemen img](https://developer.mozilla.org/en-US/docs/Web/HTML/Reference/Elements/img).
