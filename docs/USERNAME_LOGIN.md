# Username pelanggan

## Perilaku

- Login menerima **email atau username + sandi akun yang sama**. reCAPTCHA, CSRF, verifikasi akun, dan redirect checkout tetap berjalan.
- Username opsional: akun lama, Google, dan Facebook tidak diberi username otomatis. Pelanggan aktif mengaturnya melalui **Akun Saya → Username untuk masuk**.
- Format: 3–30 karakter ASCII, diawali huruf, sisanya huruf/angka/underscore. Disimpan lowercase; `Pembeli_01` sama dengan `pembeli_01`.
- Nama khusus seperti `admin`, `administrator`, `root`, `system`, `support`, `lunarea`, `google`, `facebook`, dan `tamu` tidak tersedia.
- Satu username hanya dimiliki satu akun, dan satu akun hanya mempunyai satu username. Primary key dan unique index pada database melindungi klaim bersamaan, bukan hanya pemeriksaan form.
- Username boleh diganti. Username lama kemudian tersedia kembali; form memberi keterangan sebelum penggantian. Username bukan nama profil publik dan bukan kredensial tanpa sandi.
- Username hanya alias untuk email akun asli. Email, hash sandi, Google/Facebook ID, transaksi, keranjang, voucher, dan poin tidak dipindah atau dibuat ulang.
- Pengguna Google/Facebook yang belum memiliki sandi perlu mengaturnya di Edit profil terlebih dahulu sebelum memakai login username/sandi. Menyimpan username tidak membuat sandi baru.
- Admin tetap memakai login email. Pengaturan username hanya untuk pelanggan aktif; pemilik selalu dari sesi, bukan email yang dikirim form.

## Deployment

Tidak ada perubahan `.env` dan tidak ada ALTER pada tabel `user`. Tambahkan tabel pemetaan `customer_usernames` menggunakan **salah satu** prosedur berikut, setelah backup:

1. Jalur migrasi CI4: tinjau `php spark migrate:status`, pastikan seluruh migrasi App pending siap diterapkan, kemudian jalankan `php spark migrate -n App`. Migrasi baru: `2026-10-17-000002_CreateCustomerUsernames.php`.
2. Jalur phpMyAdmin: pilih database website, lalu **Import** berkas [customer-usernames.sql](customer-usernames.sql). SQL memakai `IF NOT EXISTS` dan tidak mengubah data pelanggan. Sesuaikan nama tabel jika memakai DBPrefix.

SQL manual tidak mencatat history migrasi CI4. Jangan menjalankan migrasi yang sama sesudah import manual sebelum history/schema diselaraskan. Ini juga berlaku jika tabel Facebook dibuat manual sebelumnya; jangan memaksa semua migrasi pending di production.

MySQL memakai InnoDB dengan collation `utf8mb4_unicode_ci` agar primary key username juga case-insensitive. File SQL/migrasi harus digunakan lengkap supaya kedua constraint tersedia. Bila tabel sudah ada, `IF NOT EXISTS` tidak memperbaiki schema yang salah; tinjau `SHOW COLUMNS` dan `SHOW INDEX` sebelum aktivasi.

Sebelum schema tersedia, login email dan sosial tetap berfungsi, lookup username tidak membuat sesi, dan halaman akun menampilkan status username belum tersedia. Import SQL tidak dilakukan otomatis oleh push/deploy.

## Pengujian

Tes memakai SQLite in-memory, tidak memakai database production. Cakupan: normalisasi, batas format, nama khusus, nama ganda, constraint database, perubahan alias, akun nonaktif/admin, pemilik dari sesi, penolakan CSRF, sandi salah, email canonical pada sesi, dan preservasi data belanja.

Smoke test setelah deploy/schema siap:

1. Masuk sebagai pelanggan melalui email atau sosial; simpan `pembeli_01` di Akun Saya.
2. Keluar lalu masuk dengan `PEMBELI_01` dan sandi benar. Pastikan email akun, keranjang, dan riwayat transaksi sama.
3. Coba akun pelanggan kedua mengklaim `pembeli_01`: tampilkan pesan sudah dipakai.
4. Ganti username pertama; login username lama tidak lagi mengarah ke pemilik lama.
5. Pastikan login email/Google/Facebook, akun belum aktif, dan alur checkout tetap normal.

Hasil verifikasi lokal: 519 tes PHPUnit, 2581 assertion, tanpa kegagalan; 2 tes MySQL staging opt-in dilewati. Pemeriksaan sintaks PHP, sintaks JavaScript tes browser, dan git diff --check bersih. Tampilan diperiksa melalui tes DOM; pengujian visual browser dan smoke test production tetap dilakukan setelah deployment. Masukkan tabel customer_usernames dalam prosedur penghapusan akun pelanggan agar alias tidak tertinggal.
