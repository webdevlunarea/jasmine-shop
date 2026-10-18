# Lunarea Android — Kotlin

Aplikasi pelanggan Android native menggunakan Kotlin, Jetpack Compose, Retrofit, Firebase Cloud Messaging, dan tema Lunarea (`#2F2D20`, `#C7A78A`, `#FEFEFF`). Package: `com.lunareafurniture.lunarea`.

Folder `/android/` kini menjadi bagian source project dan dapat dikembangkan lewat Git. File lokal Android Studio, cache Gradle, dan hasil build tetap dikecualikan.

## Yang sudah dirangkai

- Beranda/katalog produk dengan filter kategori dan muat produk berikutnya, pencarian, detail produk, pilihan varian, tambah ke keranjang, dan favorit.
- Keranjang tersinkron ke API akun; ubah jumlah dan hapus item.
- Checkout native: alamat, pilihan ongkir dari quote server, opsi internal yang dikembalikan server, voucher klaim milik akun, Luna Point, layanan tambahan, pratinjau total server, serta idempotency key untuk mencegah pesanan ganda.
- Akun: login native, registrasi native, verifikasi email/kirim ulang kode, pemulihan kata sandi dengan kode OTP, profil, perubahan kata sandi/email, alamat tersimpan (tambah, edit, hapus dengan konfirmasi, jadikan utama), favorit, riwayat pesanan berpaginasi, status pembayaran, pelacakan, pembatalan sesuai status server dengan konfirmasi, dan unggah bukti transfer.
- Notifikasi pesanan FCM bersifat opt-in; token perangkat didaftarkan setelah persetujuan dan dilepas ketika opt-out/logout.
- Antarmuka berbahasa Indonesia dan tidak menyalin footer website ke aplikasi.
- Splash screen native Android 12+ dengan fallback layar pembuka bermerek untuk Android 8–11.
- Dialog konfirmasi berikon sesuai tema serta pengantar izin notifikasi yang menjelaskan manfaat dan memberi pilihan sebelum dialog sistem Android.

## Batasan yang masih harus ditutup sebelum uji HP

- Registrasi, verifikasi email, dan pemulihan kata sandi kini memakai API native. API CAPTCHA website memakai validasi hostname web; jika server menjawab `CAPTCHA_REQUIRED`, konfigurasi proteksi untuk klien Android harus diselesaikan sebelum fitur autentikasi native diaktifkan. Jangan mengakali CAPTCHA atau mengirim token palsu.
- Voucher dipilih dari klaim akun melalui endpoint terautentikasi; nominal diskon dan kelayakan tetap dihitung ulang oleh backend pada preview dan pembuatan order.
- Semua alur checkout/pembayaran tetap perlu diuji dengan respons runtime staging, termasuk instruksi metode pembayaran dan status pending.
- Pembayaran kartu tidak ditampilkan karena membutuhkan tokenisasi SDK pembayaran; jangan mengumpulkan data kartu langsung di aplikasi.
- Fitur admin website tidak termasuk aplikasi pelanggan. Halaman legal, bantuan, dan kebijakan dapat tetap dibuka melalui website saat dibutuhkan.
- API Android dan checkout produksi masih feature-flagged OFF. Jangan mengaktifkan flag produksi sebagai bagian dari pekerjaan Android ini.

## Keamanan dan konfigurasi

- API root berada di `app/build.gradle.kts`; tidak ada secret Midtrans, RajaOngkir, atau Firebase server di APK.
- Firebase client config (`app/google-services.json`) adalah konfigurasi aplikasi klien; jangan pernah memasukkan Firebase Admin SDK service-account key ke aplikasi atau repository.
- Token akses/refresh ditangani oleh `SecureTokenStore` dan `SessionAuthenticator`.
- Checkout hanya memakai total/quote yang divalidasi backend; nominal klien bukan sumber kebenaran.

## Langkah verifikasi berikutnya

1. Buka folder `android` di Android Studio dan jalankan Gradle Sync.
2. Selesaikan pemeriksaan compile unit/debug dan perbaiki hasilnya sebelum menjalankan di emulator/perangkat.
3. Gunakan staging dengan database representatif untuk menguji login, katalog, keranjang, alamat, ongkir, checkout, pembayaran, order, tracking, dan FCM.
4. Uji izin notifikasi Android 13+, token refresh, opt-out, dan logout.
5. Uji di perangkat fisik setelah checklist staging lulus.
6. Sebelum distribusi: siapkan signing key, ikon/splash final, deep link yang diperlukan, privacy/data-safety disclosure, versioning, dan checklist Play Console.

Dokumen kontrak endpoint: `../docs/ANDROID_API_V1_CONTRACT.md`. Kontrak tersebut perlu tetap dicocokkan dengan respons runtime staging; keberadaan endpoint Retrofit saja bukan bukti integrasi end-to-end berhasil.
