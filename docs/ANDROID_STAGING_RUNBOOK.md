# Lunarea Android API — Staging Readiness Runbook

Dokumen ini mempersiapkan verifikasi deployment tanpa mengaktifkan checkout aplikasi di produksi. **Belum ada migration, credential, atau konfigurasi yang dijalankan/diubah oleh runbook ini.**

## Status awal

- `LUNAREA_ANDROID_API_ENABLED`: default source `false`.
- `LUNAREA_ANDROID_CHECKOUT_ENABLED`: default source `false`.
- Jangan mengaktifkan checkout native sebelum uji staging dan rekonsiliasi Midtrans lulus.
- Database lokal yang incomplete bukan representasi schema produksi. Gunakan salinan staging yang dibuat dari backup terbaru dan batasi akses data pelanggan.
- `php spark migrate:status` hanya untuk membaca status migration. Jangan menjalankan `php spark migrate` di produksi sebagai bagian dari persiapan ini.

## Konfigurasi yang dibutuhkan

Atur hanya pada secret manager atau `.env` staging. Jangan pernah memasukkan secret ke APK, Git, tiket, log, atau output CI.

| Nama | Kapan diperlukan | Kriteria |
|---|---|---|
| `LUNAREA_ANDROID_API_ENABLED` | Sebelum smoke test API staging | Mulai `false`; ubah ke `true` hanya di staging setelah schema dan mail siap |
| `LUNAREA_ANDROID_CHECKOUT_ENABLED` | Uji order native | Tetap `false` sampai semua tahap uji pembayaran staging lulus; tidak perlu dinyalakan untuk uji katalog/auth |
| `MIDTRANS_SANDBOX_KEY` | Order/polling sandbox | Key sandbox yang sesuai dengan akun dan order sandbox |
| `MIDTRANS_PRODUCTION_KEY` | Bukan untuk staging fungsional awal | Jangan digunakan untuk pengujian; dibutuhkan hanya pada deployment produksi yang disetujui |
| `FIREBASE_PROJECT_ID` | Push notification | ID project Firebase yang dipasangkan ke application ID Android staging |
| `FIREBASE_SERVICE_ACCOUNT_PATH` | Pengiriman FCM server-side | Path file service-account di luar document root; file dibaca hanya oleh user service |
| `encryption.key` | Simpan token FCM perangkat terenkripsi | Key CI4 kuat, stabil, dan konsisten selama ciphertext perangkat masih digunakan; simpan di secret manager |
| Konfigurasi SMTP CI4 (`SMTPHost`, `SMTPUser`, `SMTPPass`, dll.) | Registrasi, verifikasi, reset sandi, perubahan email | Akun staging yang dapat mengirim email uji dan alamat penerima yang dikendalikan tim |
| Konfigurasi `Config\Recaptcha` | Jika CAPTCHA aktif pada endpoint auth | Site key, secret key, hostname staging yang diizinkan; jangan melemahkan validasi CAPTCHA di produksi |

Nama key sandbox/production di atas berasal dari pemanggilan layanan yang diverifikasi. Nama properti SMTP dan CAPTCHA berasal dari konfigurasi CI4 project; sesuaikan sumber nilainya dengan cara konfigurasi existing sebelum deployment.

## Gate alamat IP dan reverse proxy

`Config\App::$proxyIPs` saat ini kosong. CI4 karena itu memakai `REMOTE_ADDR` dan tidak mempercayai `X-Forwarded-For`/header klien; ini mencegah header spoof dipakai sebagai IP sumber, tetapi rate limit dapat mengelompokkan pengguna berdasarkan IP proxy bila request aplikasi benar-benar melewati proxy.

Sebelum API diaktifkan pada staging:

1. Verifikasi jalur request aktual (client → CDN/load balancer → web server → PHP) dan nilai peer `REMOTE_ADDR` di staging. Jangan menyimpulkan hanya dari keberadaan Cloudflare atau LiteSpeed.
2. Jika ada reverse proxy, tentukan CIDR proxy dari konfigurasi resmi/aktual operator dan konfigurasi hanya proxy tersebut pada `Config\App::$proxyIPs`, dengan header IP klien yang memang dihapus lalu ditulis ulang oleh proxy tepercaya. Jangan mempercayai semua alamat, semua header, atau nilai dari klien langsung.
3. Uji request langsung dengan header IP palsu: hasil `getIPAddress()` harus tetap memakai peer langsung. Uji melalui proxy tepercaya: hasil harus menjadi IP client yang tervalidasi. Uji proxy yang tidak terdaftar: header harus diabaikan.
4. Uji rate limit auth, pemulihan sandi, dan quote shipping dari beberapa client; pastikan batas tidak bisa dilewati dengan mengganti header dan pengguna berbeda tidak tanpa sengaja berbagi bucket karena IP proxy.

**Status: belum terverifikasi untuk staging/produksi.** Jangan ubah konfigurasi proxy sampai topologi serta CIDR tepercaya dibuktikan; konfigurasi yang terlalu luas dapat memungkinkan pemalsuan IP dan melemahkan throttling.

## Schema dan migration staging

1. Buat backup database staging dan verifikasi file backup dapat dibaca. Gunakan salinan database yang mencerminkan schema produksi terbaru, bukan database lokal yang kosong/tidak lengkap.
2. Catat branch/commit aplikasi dan versi PHP/CI4 staging.
3. Ambil snapshot `SHOW CREATE TABLE` dan status migration sebelum perubahan. Bandingkan khusus tabel `user`, `pembeli`, `barang`, `pemesanan`, `provinsi`, `kabupaten`, `kecamatan`, `kelurahan`, voucher, serta tabel shipping/customer yang sudah ada.
4. Tinjau urutan migration yang relevan sebelum menjalankan apa pun:
   - `2026-10-14-000001_CreateShippingFoundation.php`
   - `2026-10-14-000002_CreateShippingQuotesAndShipments.php`
   - `2026-10-15-000001_CreateCustomerAddresses.php`
   - `2026-10-15-000002_AddShippingPricingSnapshot.php`
   - `2026-10-17-000002_CreateCustomerUsernames.php`
   - `2026-10-17-000003_CreateCustomerPasswordResets.php`
   - `2026-10-18-000001_CreateCustomerBlockedEmailDomains.php`
   - `2026-10-18-000002_AddUsernameChangeCooldown.php`
   - `2026-10-18-000003_CreateCustomerEmailChanges.php`
   - `2026-10-18-000004_CreateCustomerApiTokensAndDevices.php`
5. Migration `2026-10-17-000001_CreateFacebookIdentities.php` juga harus diperhitungkan bila belum tercatat sebagai diterapkan karena runner CI4 menjalankan migration tertunda berurutan; jangan melewatinya atau mengubah urutan secara manual.
6. Pastikan semua tabel operasional yang disentuh checkout memakai InnoDB. Periksa kolom/tipe/unique key yang diharapkan oleh migration dan layanan sebelum apply.
7. Jalankan migration hanya di staging setelah backup, review SQL DDL aktual, dan persetujuan deployment staging. Jalankan kembali `php spark migrate:status`, periksa schema, lalu uji migrasi/rollback sesuai data fixture staging. Jangan menguji rollback pada data pelanggan nyata.
8. Bila schema berbeda atau migration menolak tabel yang sudah ada, hentikan dan rekonsiliasi schema; jangan menghapus tabel/data untuk memaksa migration.

## Urutan smoke dan integration test

### Gate 0 — Staging aman

- DNS/HTTPS staging valid; database hanya salinan staging.
- API dan checkout flags OFF.
- Ambil baseline HTTP halaman checkout web dan uji pesanan web sandbox/manual transfer.
- Pastikan log staging tidak mencetak access token, refresh token, password, OTP, API key, atau token FCM.

### Gate 1 — API dasar (API flag ON di staging saja)

- `GET /api/v1/catalog/products?page=1&per_page=20` dan detail produk: status, paging, gambar, harga/stok/varian cocok database.
- Auth register → email verifikasi → login → access token invalid/expired → refresh rotation → replay refresh token ditolak/revoke family → logout.
- Reset sandi, ganti sandi, ganti email, batas OTP/cooldown, rate limit, CAPTCHA (bila enabled), dan re-login pasca perubahan kredensial.
- Bila CAPTCHA aktif, verifikasi token yang dihasilkan klien Android diterima Siteverify dengan hostname yang diizinkan untuk register, resend, dan reset; jika belum ada cara native yang kompatibel, tandai alur tersebut belum release-ready—jangan melemahkan validasi server.
- Profil, alamat lintas hierarchy, maksimum alamat, cart/wishlist multi-device stale-write `409`, regional lookup.
- API request tanpa bearer, token palsu, header identitas spoof, method salah, serta database schema belum siap menghasilkan JSON/status sesuai kontrak.
- Jalankan seluruh skenario pada bagian “Gate alamat IP dan reverse proxy”; catat peer IP dan hasil IP ter-normalisasi tanpa menyimpan header sensitif.

### Gate 2 — Pengiriman

- Validasi alamat yang tersimpan dan hierarchy kecamatan/kota/provinsi.
- Uji quote internal Rp0 hanya di kecamatan yang dikonfigurasi; uji wilayah di luar coverage.
- Uji RajaOngkir dengan credential/kuota sandbox atau akun uji provider, tarif real, timeout, invalid destination, cache key per cart/berat/tujuan/kurir, dan provider error.
- Pastikan checkout tidak memilih otomatis quote yang kedaluwarsa, tidak menerima nominal kiriman dari klien, serta tidak mengarang tarif saat provider gagal.
- Uji `GET /api/v1/orders/{id}/tracking` memakai bearer akun pemilik dan AWB staging yang valid; verifikasi kurir, status shipment, serta riwayat BinderByte. Pastikan order pengguna lain menghasilkan 404, nomor placeholder tidak memanggil provider, Jasa Kirim Toko memakai status internal, kegagalan BinderByte tetap mengembalikan snapshot tersimpan, dan batas request tidak bisa dilewati.

### Gate 3 — Checkout/payment sandbox (flag checkout ON hanya staging)

- Gunakan akun/test buyer dan `MIDTRANS_SANDBOX_KEY`; verifikasi item details sama dengan gross amount.
- Uji voucher, poin, preorder, proteksi kaca, admin fee, ongkir, varian stok, stok tidak cukup, cart/address berubah, quote expired/used, order pending yang sudah ada, serta request duplikat.
- Uji timeout ambigu setelah charge request: replay `Idempotency-Key` sama tidak membuat charge kedua; polling/reconciliation menemukan hasil final.
- Uji pembayaran sandbox pending, settlement, expire, deny, cancel, callback berulang/out-of-order, restore stok/poin/voucher sesuai alur web existing.
- Bandingkan nilai total, status, stok, voucher, poin, dan order lama dengan baseline; uji manual transfer web tidak berubah.

### Gate 4 — Push perangkat uji

- Build APK staging dengan Firebase project/application ID staging; kirim token lewat TLS setelah login.
- Uji registrasi, token rotation, pindah akun pada instalasi yang sama, unregister saat logout, encrypted-at-rest, dan dispatch command terjadwal.
- Uji notification order/payment/pengiriman saat app foreground/background/terminated; klik notifikasi hanya membuka detail order pemilik token.
- Uji credential hilang, FCM transient failure, invalid/unregistered token, retry/backoff, deduplikasi, serta penghentian dispatcher.

### Gate 5 — Release gate

- Pastikan seluruh checklist gate lulus dan catat bukti response, log yang sudah disanitasi, hasil payment sandbox, dan schema hash/version.
- Pertahankan rollback yang disepakati: matikan checkout flag lebih dahulu, lalu API flag bila perlu; jangan drop tabel berisi token/perangkat/order.
- Aktivasi produksi memerlukan persetujuan deployment terpisah, backup terverifikasi, pemeriksaan key/secret, monitoring, dan rencana rollback. Jangan menyalin konfigurasi sandbox ke produksi.

## Bukti lokal saat ini

- `vendor/bin/phpunit --no-coverage`: **714 tests, 3,234 assertions, 5 skipped** when opt-in MySQL tests are not enabled.
- Lima kasus skip pada suite default adalah seluruh test integrasi MySQL opt-in (dua pemeriksaan identity, satu migration API, dua native checkout). Semuanya memerlukan file koneksi untuk target loopback/port non-default dan schema khusus; test API serta checkout sudah dijalankan terpisah pada schema audit kosong. Test ini tidak menunjuk produksi.
- Uji migration API pada MySQL dapat dijalankan terpisah dengan `$env:LUNA_IDENTITY_STAGING_CONNECTION=(Resolve-Path 'REFERENSI/shipping-staging/run-20261016-160614/connection.json').Path; vendor/bin/phpunit --no-coverage --filter CustomerApiStorageMysqlStagingTest; Remove-Item Env:LUNA_IDENTITY_STAGING_CONNECTION`. Test menolak target selain loopback/non-default port/`shipping_audit_web`/user staging, skip bila salah satu tabel API sudah ada, lalu membuat dan me-rollback empat tabel kosong. Hasil lokal yang tercatat: **1 test, 13 assertions lulus**. Jangan mengubah test agar menerima production atau schema lain.
- Checkout native di MySQL staging dapat diuji dengan `$env:LUNA_ANDROID_CHECKOUT_STAGING_CONNECTION=(Resolve-Path 'REFERENSI/shipping-staging/run-20261016-160614/connection.json').Path; vendor/bin/phpunit --no-coverage --filter NativeCheckoutOrderMysqlStagingTest; Remove-Item Env:LUNA_ANDROID_CHECKOUT_STAGING_CONNECTION`. Sebelum menjalankan, schema `android_checkout_audit` harus kosong, di MySQL 8 loopback pada port non-default, dan user `shipping_audit` hanya diberi grant pada schema itu. Test membuat fixture InnoDB sintetis, menggunakan payment fake tanpa jaringan, memverifikasi replay/rollback, lalu menghapus fixture. Hasil lokal: **2 tests, 20 assertions lulus**. Jangan arahkan koneksi ini ke schema operasional atau produksi.
- `NativeCheckoutOrderServiceIntegrationTest`: 3 test/26 assertions dengan SQLite memory dan payment fake; tidak membuat charge Midtrans nyata.
- Sintaks seluruh `app/` diperiksa ulang (290 file); seluruhnya lulus `php -l`.
- Uji API address default memastikan alamat kedua tidak menggantikan alamat utama kecuali diminta secara eksplisit.
- Tes copy status notifikasi pengiriman memastikan pesan sesuai status aktual dan tidak membuat pesan untuk status yang tidak dikenal.
- Route list mengonfirmasi endpoint API v1; source defaults feature flags OFF.
- Ini bukan bukti MySQL staging, production credentials, SMTP delivery, live RajaOngkir/Midtrans/FCM, atau perilaku Android device.
