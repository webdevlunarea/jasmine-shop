# reCAPTCHA login dan pendaftaran Lunarea

Jenis: **reCAPTCHA v2 checkbox**, kompatibel dengan endpoint SiteVerify. Bukan key v3 atau key Enterprise assessment-only.

## Aktivasi server
1. Daftarkan domain `lunareafurniture.com` (dan `www.lunareafurniture.com` bila dipakai) di pengelolaan reCAPTCHA Google.
2. Dapatkan site key dan secret yang kompatibel dengan v2/SiteVerify. Secret hanya disimpan di server; jangan commit atau kirim melalui chat.
3. Tambahkan pada environment server secara terkontrol (contoh, bukan credential nyata):

```ini
recaptcha.enabled = true
recaptcha.siteKey = "ISI_SITE_KEY_V2"
recaptcha.secretKey = "ISI_SECRET_KEY_V2"
recaptcha.allowedHostnames = "lunareafurniture.com,www.lunareafurniture.com"
```

Default OFF agar deployment tanpa key tidak mengunci semua pelanggan. OFF berarti perlindungan CAPTCHA belum aktif. Saat ON, konfigurasi kosong, timeout, token kosong/palsu/kedaluwarsa, hostname asing, dan error provider menolak form sebelum akses akun/insert/email OTP. Tidak ada pengecualian untuk akun uji coba atau admin.

Tidak perlu SQL, migration, package, perubahan Midtrans, MyLuna, atau Ilena. Tidak mengubah .env otomatis.

## Cakupan
- POST /masuk dan /daftar: CAPTCHA server-side sebelum validasi dan efek bisnis, plus CSRF khusus kedua route. Tidak mengaktifkan CSRF global/AJAX lain.
- Google OAuth tetap menggunakan alur existing; CAPTCHA ini bukan perlindungan Google OAuth, OTP resend, form kontak atau login tamu.
- Token dikirim langsung ke SiteVerify melalui backend TLS dengan timeout. Google memberlakukan token sekali pakai, berlaku dua menit. Tidak mencatat token, sandi, atau secret di log.
- Widget compact pada form existing; button menunggu token, reset ketika expired/error. Validasi frontend bukan otoritas.
- CAPTCHA mengurangi bot, bukan jaminan bebas spam. Rate limit, pembatasan OTP, dan monitoring tetap diperlukan.
- Periksa kuota/billing aktual akun Google. Tidak mengasumsikan penggunaan tanpa batas; error kuota menolak verifikasi.

## Uji staging sebelum ON produksi
- Form normal dan Google OAuth tetap berfungsi saat OFF.
- Saat ON: selesaikan checkbox; login valid berhasil, sandi salah kembali ke form dan perlu token baru.
- Daftar valid mengirim satu OTP dan membuat akun; tanpa token/tampered/replay ditolak tanpa akun/OTP baru.
- Host asing, timeout, key kosong/salah, token expired dan kuota error ditolak. Uji HTTP dimock, bukan request Google berulang.
- CSRF hilang/salah ditolak hanya pada /masuk dan /daftar; Google callback dan Midtrans callback tetap tidak terkena filter baru.
- Mobile 320/360/390px: widget, pesan, tombol tetap terbaca dan tidak meluber. Coba keyboard dan pemblokiran script.
- Jangan memakai key pengujian publik Google di produksi.

## Rollback
Set `recaptcha.enabled = false` melalui prosedur konfigurasi server untuk mematikan CAPTCHA secara eksplisit. CSRF kedua route tetap aktif. Tidak ada perubahan database untuk di-rollback.

## Temuan tambahan
Audit menemukan cabang login legacy yang melewati verifikasi sandi. CAPTCHA tidak mengatasi kelemahan itu; perlu perbaikan autentikasi terpisah sebelum menyatakan login aman secara menyeluruh.

Referensi: https://developers.google.com/recaptcha/docs/display dan https://docs.cloud.google.com/recaptcha/docs/verify
