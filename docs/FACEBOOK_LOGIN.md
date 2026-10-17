# Facebook Login CI4

## Audit alur yang sudah ada

- `Pages::actionLogin`: kredensial POST, CSRF, reCAPTCHA, pemeriksaan sandi/aktivasi, sesi pembeli/admin, voucher, dan redirect checkout.
- Google: `/auth/google` dan callback memakai helper sesi, pembeli, voucher, dan halaman `hapuslocalstorage`.
- Facebook memakai helper sesi/voucher yang sama melalui `FacebookLoginTrait`, tetapi identitasnya dipetakan berdasarkan Facebook ID, bukan pencocokan email otomatis.
- Catatan terpisah: Google yang lama menautkan email dan mengaktifkan akun yang ditemukan; helper redirect lama juga lebih longgar. Perubahan Facebook tidak memperluas perilaku itu; Facebook memakai pemeriksaan redirect sendiri.

## Aktivasi

1. Backup database dan tinjau status migrasi. Migrasi baru `2026-10-17-000001_CreateFacebookIdentities.php` hanya membuat tabel pemetaan, dengan Facebook ID sebagai primary key dan email unik. Tabel `user`/`pembeli` harus sudah tersedia dan mendukung transaksi (InnoDB di MySQL). Kolom `user.auth_provider` yang dipakai integrasi Google harus juga menerima nilai `facebook`; periksa tipe/enum pada schema deployment.
2. Jalankan migrasi yang telah ditinjau, melalui prosedur deployment proyek. `php spark migrate` juga menjalankan **seluruh** migrasi pending; periksa `php spark migrate:status` terlebih dahulu. Implementasi ini tidak menjalankan migrasi pada database aplikasi secara otomatis.
3. Di aplikasi Meta, siapkan Facebook Login untuk web. Daftarkan domain dan **Valid OAuth Redirect URI** yang persis sama dengan `facebook.redirectUri`, termasuk HTTPS, path, dan subdirektori jika ada. Callback proyek: `/auth/facebook/callback`.
4. Salin entri `docs/facebook.env.example` ke `.env`. Isi App ID dan App Secret secara lokal. Jangan kirim rahasia melalui chat, commit, tangkapan layar, URL, atau berkas contoh. `.env` sudah diabaikan Git. Gunakan HTTPS untuk deployment; localhost HTTP hanya untuk pengembangan jika konfigurasi Meta mengizinkannya.
5. Setelah tabel dan aplikasi siap, set `facebook.enabled = true`. Tombol muncul di login dan signup hanya saat konfigurasi lengkap. Untuk development mode Meta, uji dengan akun yang memiliki peran di aplikasi. Lengkapi izin, pengaturan publikasi, kebijakan privasi, dan mekanisme penghapusan data sesuai persyaratan dashboard sebelum dibuka untuk publik.

### Nilai `.env`

Contoh production untuk domain Lunarea (sesuaikan host jika deployment memakai domain lain atau `www`):

```dotenv
CI_ENVIRONMENT = production
app.baseURL = 'https://lunareafurniture.com/'

facebook.enabled = true
facebook.appId = 'ISI_APP_ID_META'
facebook.appSecret = 'ISI_APP_SECRET_HANYA_DI_SERVER'
facebook.redirectUri = 'https://lunareafurniture.com/auth/facebook/callback'
facebook.graphVersion = 'v25.0'

cookie.secure = true
cookie.httponly = true
cookie.samesite = 'Lax'
```

- `appId`: App ID numerik aplikasi Meta; `appSecret`: App Secret aplikasi yang sama. Placeholder di atas harus diganti secara lokal, bukan di berkas Git.
- `redirectUri`: harus persis sama dengan URI yang didaftarkan di Meta, dan menggunakan host yang sama dengan `app.baseURL` agar cookie sesi/state tetap tersedia. HTTPS wajib untuk host publik; HTTP hanya diterima untuk localhost/loopback. Jangan tambahkan query, fragment, atau trailing slash pada callback.
- `graphVersion`: versi Graph API yang dipin oleh integrasi; sesuaikan dengan versi yang tersedia pada aplikasi Meta jika diperlukan.
- `enabled`: tetap `false` sampai migrasi dan pengaturan Meta selesai. Nilai App ID placeholder, secret kosong, atau callback salah membuat tombol tetap tersembunyi.
- Cookie `Lax` memungkinkan sesi kembali pada navigasi GET dari Facebook. `Strict` berpotensi membuat state sesi hilang pada callback. `cookie.secure = true` hanya untuk HTTPS; jangan pakai saat menguji melalui localhost HTTP.
- Untuk `php spark serve`: contoh base URL `http://localhost:8080/` dan callback `http://localhost:8080/auth/facebook/callback`. Untuk XAMPP subdirektori: sertakan subdirektori pada kedua URL, misalnya `http://localhost/jasmine-shop/public/` dan `http://localhost/jasmine-shop/public/auth/facebook/callback`. Gunakan HTTPS development/tunnel jika Meta menolak URI lokal tersebut.
- Ubah nilai kunci yang sudah ada, jangan membuat duplikat. Jangan ubah konfigurasi database/Google yang sudah berfungsi. Setelah perubahan `.env`, restart proses PHP/worker persisten bila diperlukan oleh OPcache atau mekanisme cache deployment. Versi CI4 proyek ini tidak menyediakan perintah `config:clear`; jangan gunakan `config:check Facebook` untuk membagikan diagnostik karena pemeriksaan itu dapat mencetak nilai konfigurasi rahasia.

### Checklist deploy setelah push

Workflow GitHub saat ini hanya melakukan `git pull`; migrasi database dan pengisian `.env` bukan bagian dari workflow tersebut. Di server, setelah backup dan peninjauan migrasi pending:

```sh
php spark migrate:status
# Jalankan hanya setelah seluruh migrasi App yang pending sudah ditinjau:
php spark migrate -n App
```

Jangan gunakan `migrate:refresh`/rollback pada database pelanggan. Pastikan document root mengarah ke `public/`, PHP memiliki ekstensi curl/mbstring, sertifikat CA HTTPS valid, dan direktori session CI4 bisa ditulis. Jangan mematikan verifikasi sertifikat untuk mengatasi error TLS. Jika ada migrasi lama yang pending dan belum siap, tunda aktivasi Facebook sampai urutan migrasi ditinjau.

Smoke test manual setelah konfigurasi: buka login, klik Facebook, setujui akses email dengan akun uji baru, pastikan sesi pelanggan dan redirect checkout benar; ulangi login, batal, lalu email bentrok. Jangan mengunggah tangkapan layar bagian App Secret atau URL callback yang berisi kode. Pengujian ini memerlukan akun/aplikasi Meta milik operator dan tidak dilakukan oleh tes mock.

## Kontrak akun dan keamanan

- GET `/auth/facebook?redirect=/checkout` memulai authorization-code flow, scope `public_profile,email`.
- Callback memvalidasi state acak sekali pakai dengan masa berlaku 10 menit, lalu menukar kode melalui POST server ke server. Profil memakai Bearer token dan `appsecret_proof`, dengan verifikasi TLS dan timeout.
- Semua redirect login/callback Facebook membawa `Cache-Control: no-store, private` dan `Referrer-Policy: no-referrer` untuk membatasi caching dan kebocoran query autentikasi.
- Token, kode, App Secret, dan respons mentah provider tidak disimpan dalam sesi/tabel maupun dicetak ke log oleh integrasi ini. Konfigurasi web server/APM perlu meredaksi query callback (`code`, `state`) dan menonaktifkan perekaman body token exchange. Production: gunakan mode production CI4 tanpa debug toolbar/profiler untuk endpoint autentikasi.
- Login pertama membuat akun pelanggan role `0`, pembeli, dan pemetaan dalam satu transaksi. Sandi acak di-hash. Foto memakai avatar lokal; profil/provider tidak disisipkan sebagai HTML.
- Email wajib untuk pendaftaran pertama. Tanpa email, tampilkan arahan untuk mengizinkan akses email atau menggunakan pendaftaran email. Email provider tidak dianggap bukti untuk menautkan akun lokal lama.
- Email yang sudah terdaftar: gunakan metode login akun sebelumnya. Penautan akun lama secara eksplisit belum termasuk fitur ini. Facebook tidak mengaktifkan ulang akun nonaktif atau masuk ke akun admin.
- Login berikutnya mencari berdasarkan Facebook ID walaupun email provider berubah/tidak dikembalikan. Sesi diregenerasi dan voucher mengikuti helper proyek, lalu diarahkan melalui `hapuslocalstorage` ke target lokal yang tervalidasi.
- `facebook_identities` menyimpan identitas akun: masukkan tabel ini dalam prosedur penghapusan data pelanggan. Rollback migrasi dihentikan jika tabel sudah berisi pemetaan.

## Verifikasi

```powershell
php vendor/bin/phpunit --filter 'Facebook|PasswordLoginSecurityTest|BuyerAuthViewTest' --no-coverage
```

Tes otomatis menggunakan mock provider dan database SQLite in-memory, bukan kredensial Meta atau database aplikasi. Uji langsung setelah konfigurasi: sukses/ulang login, batal, state salah/kedaluwarsa/replay, izin email ditolak, benturan email, akun nonaktif, checkout redirect, serta login email/Google tetap normal. Login langsung ke Meta memerlukan konfigurasi aplikasi dan browser pengguna; hasil tes mock bukan bukti end-to-end Meta.

Hasil verifikasi implementasi (17 Oktober 2026):

- Tes terfokus Facebook + regresi login: **73 tes, 282 assertion, tanpa kegagalan**. Mencakup konfigurasi `.env`, callback/state, transaksi akun, tombol, dan bootstrap HTTP/rute CI4 asli dengan provider dummy, serta redirect XAMPP subdirektori.
- Seluruh PHPUnit: **456 tes, 2401 assertion, tanpa kegagalan, 2 dilewati**. Dua tes MySQL staging bersifat opt-in dan tidak dijalankan tanpa koneksi staging terisolasi. Sintaks PHP seluruh berkas integrasi dan `git diff --check` bersih.
- Tiga tes tampilan lama diperbarui agar sesuai copy promo berbahasa Indonesia dan class skeleton gambar saat ini; assertion struktur, aksesibilitas, harga, serta tautan tetap dipertahankan. Tidak ada perubahan UI beranda/katalog.
- Tombol diperiksa melalui DOM (tautan, redirect, label, ikon, kondisi aktivasi). Verifikasi visual browser belum selesai karena koneksi runtime browser gagal; tidak ada klaim pengujian langsung Meta.

Referensi resmi: [Manual login flow](https://developers.facebook.com/docs/facebook-login/guides/advanced/manual-flow/), [Securing Graph API requests](https://developers.facebook.com/docs/graph-api/securing-requests/), [Graph API changelog](https://developers.facebook.com/docs/graph-api/changelog/). Versi API dipin dan bisa diatur lewat lingkungan; periksa ketersediaan versi pada dashboard sebelum deployment. Akses dokumentasi Meta saat pengerjaan mendapat HTTP 429, sehingga validasi langsung terhadap dashboard tetap menjadi langkah aktivasi.
