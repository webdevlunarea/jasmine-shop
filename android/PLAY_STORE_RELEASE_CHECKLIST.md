# Lunarea Android — Checklist Rilis Google Play

Status audit lokal: 20 Oktober 2026. Dokumen ini memisahkan hal yang sudah terbukti dari pekerjaan yang masih harus dilakukan di Play Console atau backend.

## Terverifikasi di project lokal

- `applicationId`: `com.lunareafurniture.lunarea`.
- `compileSdk` dan `targetSdk`: 36; memenuhi target API Android 16 untuk pengiriman baru pada tanggal audit.
- `minSdk`: 26.
- `versionCode`: 2; pastikan lebih tinggi dari versi tertinggi yang pernah diunggah ke Play Console sebelum rilis.
- Task `bundleRelease` pernah berhasil menghasilkan `app/build/outputs/bundle/release/app-release.aab`, tetapi pemeriksaan rinci `jarsigner` menyatakan bundle tersebut **unsigned**. Jangan unggah artefak itu.
- `debugSymbolLevel = FULL` sudah dikonfigurasi; pemeriksaan lokal belum menemukan arsip simbol native terpisah untuk diunggah.
- Konfigurasi Gradle kini membaca kredensial signing lokal dari `android/keystore.properties` dan menolak task bundle/assemble/package release jika belum lengkap.
- Aplikasi sudah meminta izin notifikasi dan lokasi; lokasi dipakai melalui alur pilihan lokasi/alamat. Pastikan deklarasi Data safety sesuai penggunaan SDK dan praktik backend yang sebenarnya.
- Layar akun kini menautkan Kebijakan Privasi dan Syarat & Ketentuan ke rute website Lunarea.
- `.gitignore` sekarang mengecualikan berkas client-secret OAuth, signing material lokal, dan AAB hasil build.

## Penghambat sebelum produksi

1. **Penghapusan akun belum tersedia.** Pemeriksaan source Android dan route backend tidak menemukan alur self-service penghapusan akun beserta data terkait. Google Play mensyaratkan opsi yang mudah ditemukan di aplikasi dan jalur web publik untuk meminta penghapusan. Tentukan proses retensi data pesanan yang diwajibkan, lalu implementasikan permintaan dan pemenuhan penghapusan sebelum menyatakan aplikasi siap.
2. **Kebijakan Privasi perlu verifikasi publik.** Rute ada di source dan tautan sudah ditambahkan di aplikasi, tetapi ketersediaan URL publik dan kecocokan isi dengan data aplikasi belum dikonfirmasi melalui HTTP. Data Safety harus konsisten dengan seluruh SDK, backend, dan kebijakan tersebut.
3. **Sertifikat rilis harus dicocokkan.** Di Play Console, aktifkan Play App Signing dan cocokkan upload-key fingerprint. Setelah bundle diunggah, tambahkan fingerprint App signing key Play ke OAuth Android/Firebase/API yang memvalidasi aplikasi. Login Google produksi bisa gagal jika hanya SHA debug yang terdaftar.
4. **Native debug symbols belum ditemukan sebagai keluaran terpisah.** Konfigurasi FULL ada, tetapi cari/hasilkan file simbol dari build release dan unggah di App Bundle Explorer jika Play Console masih menampilkan peringatan.

## Checklist Play Console

- Buat listing: nama aplikasi, deskripsi, ikon 512×512, feature graphic 1024×500, screenshot perangkat, kategori, email kontak, dan URL kebijakan privasi.
- Isi App content: Data safety, target audience/content rating, ads declaration, app access/sign-in instructions, dan data deletion.
- Siapkan akun demo reviewer yang tetap aktif, dapat dipakai berulang, tidak memerlukan OTP yang tidak tersedia untuk reviewer, serta berikan instruksi login Google bila alur itu harus diuji.
- Jika akun developer adalah **personal** dan dibuat setelah 13 November 2023: selesaikan closed test dengan sedikitnya 12 tester yang ikut terus selama 14 hari, lalu ajukan production access.
- Unggah AAB release, periksa Play App Signing, warnings, device catalog, dan hasil Pre-launch report.
- Uji login (email dan Google), registrasi/verifikasi, pencarian, favorit, keranjang, alamat/lokasi, checkout, pembayaran sandbox, pembatalan, pesanan/tracking, notifikasi, dan penghapusan akun di internal/closed testing sebelum produksi.
- Periksa bahwa `versionCode` belum pernah digunakan. Naikkan setiap rilis berikutnya.
- Jangan unggah file client secret OAuth atau keystore ke repository/AAB. Simpan upload key dan kata sandinya pada penyimpanan terenkripsi yang dicadangkan.

## Verifikasi versi dan build berikutnya

Jalankan dari folder `android`:

```powershell
./gradlew.bat :app:bundleRelease
```

Artefak: `android/app/build/outputs/bundle/release/app-release.aab`.

Salin `android/keystore.properties.example` menjadi `android/keystore.properties`, lalu isi lokasi keystore upload, password keystore, alias, dan password key secara lokal. Jangan kirim password lewat chat atau commit berkas tersebut. AAB yang sudah ada di folder output sebelum kredensial ini diatur dibuat tanpa signing release yang dikonfigurasi dan **jangan diunggah**; guard Gradle kini menolak release sampai konfigurasi lengkap. Jalankan ulang `bundleRelease` setelah konfigurasi, lalu verifikasi sertifikat upload key terhadap Play Console.
