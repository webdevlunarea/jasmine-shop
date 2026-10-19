# Lunarea Android API v1 — kontrak integrasi Kotlin

Dokumen ini mengikuti route dan controller CI4 yang tersedia saat ini. API ini belum diaktifkan untuk produksi dan belum diverifikasi pada skema MySQL staging.

## Dasar komunikasi

- Base URL: `https://lunareafurniture.com/api/v1`
- Kirim dan terima JSON UTF-8, kecuali unggah bukti transfer yang memakai `multipart/form-data`.
- Respons berhasil: `{ "success": true, "data": {}, "meta": {} }`.
- Respons gagal: `{ "success": false, "error": { "code": "...", "message": "..." } }`.
- Endpoint terlindungi memakai `Authorization: Bearer <access_token>`; token akses berlaku 15 menit. Gunakan refresh token rotasi sekali pakai untuk memperoleh pasangan token baru.
- Semua API versi v1 dikendalikan `LUNAREA_ANDROID_API_ENABLED`, default **OFF**. Checkout native memiliki gate tambahan `LUNAREA_ANDROID_CHECKOUT_ENABLED`, default **OFF**.
- Jangan menyimpan secret Midtrans, RajaOngkir, Firebase, atau credential server di aplikasi Android.

## Autentikasi dan akun

| Method | Path | Auth | Keterangan |
|---|---|---|---|
| POST | `/auth/register` | Tidak | Buat akun menunggu verifikasi email; tidak membuat sesi |
| POST | `/auth/verify-email` | Tidak | Verifikasi OTP; login dilakukan terpisah |
| POST | `/auth/verification/resend` | Tidak | Kirim ulang OTP secara generik setelah cooldown |
| POST | `/auth/login` | Tidak | Login pelanggan terverifikasi; memperoleh access/refresh token |
| POST | `/auth/google` | Tidak | Verifikasi Google ID token di backend; pelanggan Google terverifikasi memperoleh token API Lunarea |
| POST | `/auth/refresh` | Refresh token di body | Rotasi refresh token; refresh token lama tidak boleh digunakan kembali |
| POST | `/auth/logout` | Bearer | Cabut seluruh keluarga token sesi saat ini |
| GET | `/me` | Bearer | Baca profil pengguna |
| PATCH | `/me` | Bearer | Perbarui nama dan nomor ponsel |
| POST | `/me/photo` | Bearer, multipart | Unggah foto profil JPG/PNG/WebP maksimal 2 MiB |

`GET /me` mengembalikan `data.customer.photo_url`. Unggah foto memeriksa format MIME, ukuran, dan dimensi, lalu menyimpan blob profil serta referensi foto pelanggan dalam satu transaksi.
| POST | `/auth/password/change` | Bearer | Ganti sandi dengan sandi lama; seluruh sesi dicabut dan login ulang diperlukan |
| POST | `/auth/email/change/request` | Bearer | Minta perubahan email dengan sandi saat ini; kode dikirim ke email baru |
| POST | `/auth/email/change/confirm` | Bearer | Konfirmasi kode dan sandi saat ini; ubah identitas akun dan wajib login ulang |
| POST | `/auth/password-reset/request` | Tidak | Minta OTP pemulihan dengan respons anti-enumerasi |
| POST | `/auth/password-reset/complete` | Tidak | Reset sandi dengan OTP; login ulang diperlukan |
| GET | `/notifications` | Bearer | Ambil maksimal 50 pemberitahuan akun dan jumlah yang belum dibaca |
| POST | `/notifications/read` | Bearer | Tandai pemberitahuan terkirim milik akun sebagai telah dibaca; body `{}` menandai semua |

Contoh pendaftaran:

```json
{
  "name": "Nama Pembeli",
  "email": "pembeli@example.com",
  "phone": "081234567890",
  "password": "SandiAman123",
  "password_confirmation": "SandiAman123",
  "consent": true,
  "captcha_token": "token-recaptcha-bila-diaktifkan"
}
```

Permintaan berhasil mengembalikan HTTP 202 dengan `verification_required`, `verification_email_sent`, dan instruksi berikutnya. OTP tidak pernah dikembalikan oleh API.

**Catatan CAPTCHA native:** bila `Config\Recaptcha::$enabled` aktif, pendaftaran, kirim ulang verifikasi, dan permintaan reset sandi mensyaratkan `captcha_token` yang lolos Google Siteverify serta cocok dengan hostname yang diizinkan server. Konfigurasi yang ada adalah reCAPTCHA web berbasis hostname; dokumen ini tidak menetapkan bahwa token dari Android native otomatis kompatibel. Sebelum mengaktifkan CAPTCHA bersamaan dengan alur registrasi/reset di APK, verifikasi cara token native yang didukung penyedia dan hasil `hostname`-nya pada staging. Jangan menonaktifkan CAPTCHA produksi hanya untuk melewati kendala klien; jika tidak ada integrasi native yang valid, UI harus menjelaskan bahwa tindakan belum tersedia dan backend tetap menolak permintaan.

Contoh verifikasi: `{"email":"pembeli@example.com","code":"123456"}`. OTP berumur 10 menit. Respons sukses menyatakan `verified: true` dan `login_required: true`.

Contoh login: `{"identifier":"pembeli@example.com","password":"SandiAman123","device_name":"Android"}`. Token ada di `data.tokens`: `access_token`, `refresh_token`, `token_type`, `expires_in`, dan `refresh_expires_in`.

Login Google native mengirim `{"id_token":"<Google ID token>","device_name":"Lunarea Android"}` ke `/auth/google`. Server wajib memiliki `GOOGLE_CLIENT_ID` berisi **Web OAuth Client ID** yang sama dengan `GOOGLE_WEB_CLIENT_ID` di aplikasi. ID token diverifikasi server-side; email biasa dari aplikasi tidak dipercaya. Client Secret tidak dikirim ke APK. Akun admin, token tidak valid, dan email Google yang belum terverifikasi ditolak. Akun pelanggan Google baru dibuat dengan profil Lunarea dan voucher auto-claim yang aktif; akun pelanggan dengan email terverifikasi yang sama ditautkan sesuai alur Google website existing.

Contoh refresh: `{"refresh_token":"..."}`. Simpan token baru secara atomik; token refresh lama sudah tidak berlaku. Replay refresh lama mencabut keluarga sesi.

Reset sandi memakai body request `{"identity":"email-atau-username","captcha_token":"..."}` lalu penyelesaian `{"identity":"...","code":"123456","password":"...","password_confirmation":"..."}`. Permintaan reset mengembalikan HTTP 202 generik untuk akun dikenal maupun tidak dikenal.

Ganti sandi memakai `{"current_password":"...","password":"...","password_confirmation":"..."}`. Respons menyatakan `login_required: true`; hapus token lokal dan lakukan login kembali.

Perubahan email memakai `POST /auth/email/change/request` dengan `{"current_password":"...","new_email":"baru@example.com"}`. Kode verifikasi 6 digit dikirim ke alamat baru dan berlaku 10 menit; permintaan kode memiliki jeda minimal 1 menit dan email hanya bisa diganti setiap 6 bulan. Konfirmasi melalui `POST /auth/email/change/confirm` dengan `{"current_password":"...","verification_code":"123456"}`. Konfirmasi juga meminta sandi saat ini. Sukses mengembalikan email baru dan `login_required: true`; hapus access/refresh token lokal dan login kembali dengan email baru. Transaksi juga mencabut token API yang masih aktif dan merotasi versi sesi password.

## Katalog dan wilayah

| Method | Path | Query | Isi respons utama |
|---|---|---|---|
| GET | `/catalog/products` | `page` (default 1, maksimum 10.000), `per_page` (default 20, maks 40), opsional `q`, `category`, `sort` (`default`, `popular`, `newest`) | `data.products`; metadata paging di `meta`; urutan `popular` berdasarkan angka terjual yang ditampilkan, `newest` mengikuti ID produk aktif terbaru per slug; parameter tidak valid menghasilkan HTTP 422 |
| GET | `/catalog/products/{id}` | — | `data.product`, termasuk deskripsi dan ketersediaan layanan kaca |
| GET | `/catalog/categories` | — | `data.categories` |
| GET | `/regions/provinces` | — | `data.regions` |
| GET | `/regions/cities?province_id={id}` | Province wajib | Kota/kabupaten dalam provinsi |
| GET | `/regions/districts?city_id={id}` | City wajib | Kecamatan dalam kota/kabupaten |
| GET | `/regions/villages?district_id={id}` | District wajib | Kelurahan dan kode pos |

Harga dan stok dikirim sebagai integer IDR/unit. Produk membawa `variants` dengan `name` serta `stock`, `sold_count` (angka terjual yang ditampilkan), dan URL gambar dari server. Produk tanpa varian menampilkan satu opsi `Default`. Pada data lama ketika jumlah opsi varian melebihi jumlah nilai stok, nilai stok pertama adalah stok bersama dan tidak dijumlahkan berulang. Jangan menganggap URL gambar sebagai berkas lokal.

## Profil, alamat, keranjang, dan favorit

| Method | Path | Body/parameter |
|---|---|---|
| GET | `/addresses` | — |
| POST | `/addresses` | `label`, `recipient_name`, `recipient_phone`, `address_detail`, `province_id`, `city_id`, `district_id`, `village_id`, opsional `is_default` |
| PUT | `/addresses` | Body yang sama dengan `id` alamat untuk edit |
| DELETE | `/addresses/{id}` | — |
| POST | `/addresses/{id}/default` | — |
| GET | `/cart` | — |
| POST | `/cart/items` | `product_id`, `variant`, `quantity` |
| PATCH | `/cart/items` | `product_id`, `variant`, `quantity` |
| DELETE | `/cart/items` | `product_id`, `variant` di JSON body |
| GET | `/wishlist` | — |
| POST | `/wishlist/items` | `product_id`, opsional `variant` |
| DELETE | `/wishlist/items` | `product_id`, opsional `variant` di JSON body |

Alamat hanya menerima kombinasi ID wilayah yang cocok hierarkinya. Maksimum 20 alamat per akun. Keranjang memvalidasi produk, varian, dan stok di backend; harga dari aplikasi bukan sumber kebenaran checkout.

Jika dua perangkat mengubah keranjang bersamaan, mutasi yang memakai snapshot lama ditolak dengan `409 CART_CHANGED_RETRY`. Muat ulang `GET /cart` sebelum pelanggan mencoba kembali; server tidak menimpa perubahan yang lebih baru.

Perubahan daftar favorit juga memakai perlindungan snapshot. Jika server mengembalikan `409 WISHLIST_CHANGED_RETRY`, muat ulang `GET /wishlist` sebelum pelanggan mencoba lagi.

## Pengiriman dan checkout

| Method | Path | Auth | Body |
|---|---|---|---|
| POST | `/shipping/quotes` | Bearer | `{"address_id": 123}`; kuotasi dihitung dari keranjang dan alamat tersimpan saat ini |
| GET | `/vouchers/available` | Bearer | Daftar klaim voucher aktif milik akun; harga/potongan tetap dihitung dan divalidasi server |
| POST | `/checkout/preview` | Bearer | `address_id`, `shipping_quote_token`, `payment_method`, opsional `voucher_claim_id`, `use_points`, `include_glass` |
| POST | `/checkout/orders` | Bearer | Body pilihan checkout yang sama, opsional `card_token`; header wajib `Idempotency-Key` unik minimal 16 karakter |

Preview menghitung ulang item, harga, voucher, poin, proteksi kaca, ongkir, biaya admin, dan grand total dari sisi server. Quote memiliki masa berlaku; perubahan alamat, keranjang, harga, atau quote harus memicu preview baru.

Alur klien yang disarankan:

1. Ambil quote dengan `POST /shipping/quotes`. Pilih salah satu opsi dari `data.quotes`; gunakan hanya `token` yang diterbitkan server, bukan nominal atau tarif yang dihitung aplikasi.
2. Minta `POST /checkout/preview` dengan `address_id`, `shipping_quote_token`, `payment_method`, dan opsi diskon/layanan. Tampilkan nilai `data.checkout.total` serta ringkasan server.
3. Ketika pengguna mengonfirmasi pembayaran, buat satu `Idempotency-Key` acak (minimal 16 karakter), simpan secara lokal, lalu kirim `POST /checkout/orders` dengan input yang sama. Jangan mengirim `total` atau `shipping_cost` dari klien sebagai sumber nilai.
4. Pada HTTP 201, baca `data.checkout.order`: `id`, `status`, `amount`, dan `payment` (termasuk `type`, `transaction_status`, `expiry_time`, VA/kode bayar, atau `actions` jika disediakan provider).
5. Pada HTTP 202 atau timeout, anggap hasil pembayaran masih dipastikan. Pertahankan key yang sama, jangan membuat order/key baru, lalu periksa `GET /orders/{id}` dan `GET /orders/{id}/payment-status`. Status 202 dapat membawa `data.checkout.order_id`; replay key yang selesai mengembalikan order yang sama.

Status order yang tersimpan dan `payment.transaction_status` dari provider adalah dua nilai terpisah. UI harus mengutamakan status order server, menampilkan instruksi pembayaran hanya jika provider mengembalikannya, dan tidak menandai order lunas berdasarkan respons lokal aplikasi. Metode `rekening` statis legacy tidak didukung untuk pembuatan order native; endpoint bukti transfer tetap dapat dipakai untuk order rekening milik pelanggan yang sudah ada.

Metode checkout yang dibangun oleh payload Midtrans: `bca`, `bri`, `bni`, `cimb`, `permata`, `mandiri`, `qris`, `gopay`, `shopeepay`, dan `card`. Pembayaran kartu membutuhkan `card_token`. Jangan kirim nominal ongkir atau total buatan klien sebagai nilai otoritatif. Pembuatan pesanan tetap tidak aktif sampai kedua feature flag dinyalakan setelah pengujian staging.

Replay dengan idempotency key yang sama tidak menagih ulang. HTTP 202 berarti status provider masih perlu dipastikan: jangan membuat key baru untuk order yang sama sebelum memeriksa riwayat dan status pesanan. HTTP 503 `CHECKOUT_UNAVAILABLE` menyertakan `error.request_id`; tampilkan tindakan untuk membuka Pesanan Saya dan gunakan ID tersebut untuk mencocokkan log server. Jangan membuat key baru atau mengulang pembayaran hanya karena pesan gangguan muncul.

## Pesanan, pembayaran, bukti transfer, dan tracking

| Method | Path | Keterangan |
|---|---|---|
| GET | `/orders?page=1&per_page=10` | Daftar milik akun, maks 30 per halaman dan `page` maksimum 10.000; parameter pagination tidak valid menghasilkan HTTP 422 |
| GET | `/orders/{id}` | Detail hanya jika order dimiliki akun; termasuk item, status pembayaran, instruksi pembayaran yang aman, dan tautan pembayaran HTTPS |
| POST | `/orders/{id}/cancel` | Meminta pembatalan pesanan unpaid; status Midtrans diverifikasi dan efek stok/voucher/poin memakai rekonsiliasi pembayaran website |
| GET | `/orders/{id}/payment-status` | Poll status, dibatasi per akun dan per order; sumber Midtrans hanya untuk transaksi yang memenuhi syarat |
| POST | `/orders/{id}/payment-proof` | Unggah gambar transfer melalui multipart field `proof`, maks 5 MB, JPEG/PNG/WebP |
| GET | `/orders/{id}/tracking` | Kurir, nomor resi, status pengiriman dan waktu pembaruan yang tersimpan |

Bukti transfer diterima hanya untuk order milik pengguna yang berstatus menunggu pembayaran rekening, belum memiliki bukti, dan belum melewati expiry. Endpoint pembatalan order **belum tersedia** di API v1.

Pembatalan memakai `POST /orders/{id}/cancel` tanpa body dan hanya berlaku untuk pesanan milik pengguna yang masih menunggu pembayaran. Transfer manual yang bukti bayarnya sedang diperiksa ditolak; tanpa bukti, sistem memproses pembatalan melalui rekonsiliasi yang sama dengan website. Untuk Midtrans, status provider diverifikasi lebih dulu, pembatalan hanya diminta jika status tetap `pending`, lalu status provider dibaca ulang sebelum API mengonfirmasi berhasil. Sukses mengembalikan status pesanan terbaru; status yang berubah atau bukti yang ditinjau menghasilkan HTTP 409, terlalu banyak permintaan menghasilkan HTTP 429, dan status provider yang tidak dapat diverifikasi menghasilkan HTTP 503. Klien harus memuat ulang detail pesanan setelah respons konflik/gangguan dan tidak boleh menganggap pembatalan berhasil tanpa respons sukses.

Respons tracking memisahkan `status` (status pengiriman tersimpan) dari `order_status` (status pesanan/pembayaran). Jika data shipment atau resi aktual belum tersedia, `status`, `courier`, dan `tracking_number` dapat bernilai `null`; placeholder legacy seperti “Menunggu pengiriman” tidak dianggap sebagai nomor resi. Untuk nomor resi/kurir eksternal yang dikenali, `live_tracking` berisi ringkasan, detail, dan riwayat BinderByte terbaru. Untuk Jasa Kirim Toko, status internal menjadi sumber status dan `live_tracking` bernilai `null`. Hasil live bersifat best-effort; bila provider tidak tersedia, snapshot tersimpan tetap dikembalikan dan `live_tracking.success` bernilai `false`.

Polling `payment-status` bersifat baca-saja dan bukan pengganti webhook/reconciliation backend. Status/manual transfer ditampilkan dari data tersimpan. Detail nomor VA dan data pembayaran yang dikirim dibatasi pada data pelanggan.

## Luna Poin

| Method | Path | Auth | Keterangan |
|---|---|---|---|
| GET | `/points?page=1` | Bearer | Saldo aktif, masa berlaku terdekat, tier/benefit, dan riwayat poin terpaginasikan (30 per halaman) |

Saldo dan tier dibaca dari data akun Lunarea; endpoint hanya-baca dan tidak mengubah atau menghapus batch poin kedaluwarsa. Pemakaian poin saat checkout dikirim sebagai `use_points` pada `POST /checkout/preview` dan `POST /checkout/orders`; backend menghitung saldo/potongan, menyimpan penggunaan pada pesanan, dan mengelola pengembalian poin sesuai alur status pesanan. Nilai poin dari aplikasi bukan sumber kebenaran.

## Push notification

| Method | Path | Body |
|---|---|---|
| POST | `/push/devices` | `{"device_token":"FCM token","platform":"android","device_name":"opsional"}` |
| DELETE | `/push/devices` | `{"device_token":"FCM token"}` |

Token perangkat disimpan terenkripsi, sedangkan fingerprint SHA-256 dipakai untuk pencarian. Android harus mendaftarkan token saat login/token FCM diperbarui dan menghapusnya saat logout. Delivery memerlukan Firebase service account di server dan command dispatcher terjadwal; kredensial tidak boleh ada di APK.

## Penanganan error untuk klien

- `401 UNAUTHENTICATED` / `INVALID_REFRESH_TOKEN`: hapus sesi lokal dan minta login lagi.
- `403 EMAIL_NOT_VERIFIED`: arahkan pengguna ke verifikasi email.
- `409 CART_CHANGED_RETRY`, `WISHLIST_CHANGED_RETRY`, atau konflik quote/bukti/order: muat ulang data server, jangan ulangi mutasi identik tanpa pemeriksaan.
- `422 VALIDATION_ERROR` atau `CHECKOUT_VALIDATION_FAILED`: tampilkan pesan validasi; data server tetap otoritatif.
- `429 RATE_LIMITED`: hormati retry/backoff; jangan membuat polling rapat.
- `503 API_NOT_ENABLED`: API master flag masih OFF. `503` lain berarti dependensi/integrasi belum tersedia; jangan ubah status lokal seolah operasi berhasil.
- `503 CHECKOUT_NOT_ENABLED`: pembuatan pesanan dari aplikasi belum diaktifkan; ringkasan checkout tetap dapat dimuat, tetapi jangan menawarkan aksi pembayaran aplikasi.
- `503 SHIPPING_UNAVAILABLE`: tidak ada tarif yang dapat dihitung karena konfigurasi/provider/lokasi bermasalah. Jangan mengganti ongkir dengan Rp0; pengguna harus mencoba kembali atau memilih metode internal yang memang eligible.
- Timeout saat POST order: gunakan kembali `Idempotency-Key` yang sama dan cek order sebelum mencoba tindakan baru.

Respons `POST /shipping/quotes` juga mengembalikan `external_status` yang aman untuk diagnostik UI (`available`, `no_rates`, `disabled`, `missing_api_key`, `invalid_weight`, `location_unavailable`, `provider_error`, atau `not_checked`). Field ini tidak berisi API key maupun respons mentah provider. Quote internal yang valid tetap dikembalikan meskipun provider eksternal gagal.

Akun uji yang secara eksplisit terdaftar pada `PaymentSafety::testAccountEmails()` dapat membuat pesanan sandbox saat checkout aplikasi umum masih OFF. Pesanan tetap dicatat sebagai pesanan uji pada basis data Lunarea, tidak memesan stok, memakai `MIDTRANS_SANDBOX_KEY`, dan tidak boleh diteruskan ke MyLuna produksi. Seluruh akun lain tetap memerlukan `LUNAREA_ANDROID_CHECKOUT_ENABLED=true`.

## Catatan kesiapan

Route dan payload di atas diturunkan dari controller saat ini. Suite PHPUnit lokal lulus, tetapi database lokal tidak memiliki seluruh tabel operasional dan seluruh migration berstatus belum diterapkan. Kontrak perlu diuji ulang terhadap skema MySQL staging dan response nyata Midtrans/Firebase sebelum klien Android diimplementasikan atau API diaktifkan di produksi.
