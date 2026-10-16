# Invoice pembeli — perbaikan 16 Oktober 2026

## Alur baru

- `GET /invoice/{id_midtrans}` mengembalikan PDF A4 dengan
  `Content-Disposition: attachment`, bukan HTML yang otomatis memanggil cetak.
- `GET /invoice/{id_midtrans}?preview=1` menampilkan pratinjau responsif beserta
  tombol Unduh PDF dan Cetak. Tidak otomatis membuka dialog cetak.
- Route tetap memakai `harusLogin`; controller juga memverifikasi pemilik pesanan
  atau role admin `1`. Pesanan hilang dan milik akun lain sama-sama menghasilkan 404.
- Respons invoice private/no-store dan tidak mengirim metadata rahasia pembayaran.
- PDF memakai template lokal dan Dompdf; akses remote, PHP, serta JavaScript pada
  renderer dimatikan. Input pelanggan selalu di-escape. Tidak ada fetch logo/font/CDN.

## Nominal dan kompatibilitas

Invoice memakai snapshot produk, penerima, gross_amount, voucher/poin tersimpan,
serta biaya kirim `order_shipments` bila tersedia. Harga produk aktif tidak dibaca.
Total invoice tidak mengubah tagihan Midtrans, saldo poin, stok, atau status order.
Cashback ditampilkan sebagai informasi, bukan potongan tagihan.

Order lama tidak selalu menyimpan rincian biaya admin/proteksi/preorder secara
terpisah. Selisih terhadap gross_amount ditampilkan sebagai biaya/potongan dan
penyesuaian lainnya, bukan mengarang biaya admin Rp5.000. Bila gross_amount tidak
tersedia, total ditulis Belum tercatat. Jangan menganggap selisih tersebut seluruhnya
ongkir. Email invoice existing dan invoice admin manual tidak dirombak pada patch ini.

## Deployment — wajib sebelum menguji di produksi

Tidak ada SQL atau migration. Dependency baru dikunci dalam composer.lock:
Dompdf 3.1.6 dan empat dependency pendukung. Package existing tidak diperbarui.

Workflow GitHub Actions existing hanya menjalankan git pull, **belum composer install**.
Jangan menganggap push otomatis memasang dependency. Operator perlu memastikan
vendor dari lock baru terpasang dalam deployment:

```sh
composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader --no-scripts
composer check-platform-reqs --no-dev
```

Jalankan dari root project di lingkungan staging/production yang benar. Pastikan
PHP dan ekstensi memenuhi composer.lock, terutama PHP >= 8.1 untuk dependency
parser terpilih, ext-dom, ext-mbstring, dan direktori writable/cache dapat ditulis
oleh PHP worker. Jangan memakai chmod 777. Tidak perlu mengganti .env.

Jika dependency belum terpasang atau renderer gagal, endpoint mengembalikan 503
dengan pesan umum; rincian exception tidak dibocorkan. Tidak ada PDF pelanggan
yang disimpan permanen atau diunggah ke layanan pihak ketiga.

Composer audit mendeteksi advisori pada dependency existing (CI4, Guzzle,
guzzlehttp/psr7, PHPUnit); upgrade framework/dependency tersebut perlu audit dan
regresi terpisah, bukan dimasukkan diam-diam ke perbaikan invoice.

## Verifikasi

- Unit: pemilik/admin/akun lain/logout, snapshot total dan selisih, pending/manual,
  data legacy hilang/rusak, escaping HTML, PDF A4 biner dan multipage.
- Controller terisolasi: PDF attachment, no-store, preview HTML, 404 order hilang
  dan akun lain sebelum membaca pengiriman. Tidak memakai DB produksi.
- PDF fixture sintetis dirender dengan PyMuPDF untuk memeriksa A4 dan halaman akhir.
- Pratinjau diperiksa pada desktop, mobile 390px dan 320px; tanpa overflow horizontal.
- Sebelum produksi: uji download dengan akun pembeli uji dan admin, validasi nama
  file, angka sesuai order, PDF pending tidak berlabel Lunas. Jangan membuat atau
  menyinkronkan order produksi hanya untuk menguji invoice.

## Rollback

Revert commit invoice yang disetujui, jangan reset perubahan lain. Pulihkan pasangan
composer.json/composer.lock dari commit sebelumnya, kemudian jalankan composer
install pada deployment yang benar. Tidak ada rollback database. Pastikan file
controller, layanan invoice, template, dan CSS dideploy sebagai satu versi.

Referensi renderer: [Dompdf resmi](https://github.com/dompdf/dompdf),
[konfigurasi keamanan](https://github.com/dompdf/dompdf/blob/master/src/Options.php).
