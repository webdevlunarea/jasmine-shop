# Login Google Native Android

## Implementasi

- Android menggunakan AndroidX Credential Manager dan Google ID untuk memperoleh Google ID token; tidak memakai WebView.
- Aplikasi mengirim `id_token` melalui HTTPS ke `POST /api/v1/auth/google`.
- Backend memverifikasi tanda tangan, audience (`GOOGLE_CLIENT_ID`), issuer, dan masa berlaku token menggunakan Google API Client. Backend hanya menerima email terverifikasi dan akun pelanggan, lalu menerbitkan token sesi Lunarea yang sama seperti login sandi.
- Google Client Secret tidak digunakan oleh alur native ini dan tidak boleh dimasukkan ke APK. Rahasia OAuth web hanya untuk callback web.

## Konfigurasi server

Pastikan `.env` server memiliki kunci publik Web OAuth Client ID yang sama dengan `GOOGLE_WEB_CLIENT_ID` Android:

```ini
GOOGLE_CLIENT_ID = "<Web OAuth Client ID>"
LUNAREA_ANDROID_API_ENABLED = true
```

`LUNAREA_ANDROID_API_ENABLED` adalah feature flag API yang sudah melindungi seluruh endpoint Android. Jangan mengaktifkannya sebelum tabel sesi API Android yang diwajibkan tersedia di database produksi. Jangan menaruh `GOOGLE_CLIENT_SECRET` di Android atau mengirimkannya ke aplikasi.

## Konfigurasi Google/Firebase

- OAuth Android Client harus menggunakan package `com.lunareafurniture.lunarea` dan SHA-1 dari sertifikat yang benar.
- SHA-1 debug yang sudah diberikan hanya mengizinkan APK debug. Sebelum rilis, daftarkan SHA-1 sertifikat release/Play App Signing di Firebase dan Google Cloud, lalu unduh ulang `google-services.json` bila konfigurasi Firebase berubah.
- `google-services.json` dan signing keystore tidak boleh dikomit. File rahasia OAuth yang pernah diletakkan di `android/app` juga jangan dikomit; native login memakai Client ID publik, bukan client secret.

## Endpoint

Request:

```json
{"id_token":"<Google ID token>","device_name":"Lunarea Android"}
```

Sukses mengikuti envelope API v1 dengan `data.tokens.access_token`, `data.tokens.refresh_token`, masa berlaku, serta ringkasan pelanggan. Token mentah Google tidak disimpan atau dicatat ke log.

## Verifikasi

1. Pastikan server mengirim HTTP 200 untuk ID token valid dan token sesi Lunarea dapat dipakai ke endpoint `/api/v1/me`.
2. Token rusak/kedaluwarsa, audience salah, email belum terverifikasi, dan akun admin harus ditolak.
3. Uji pembatalan pemilih akun, akun baru, akun Lunarea yang cocok dengan email Google, logout, dan pemilihan Google account lain.
4. Uji debug dan release secara terpisah karena SHA-1 signing berbeda.
