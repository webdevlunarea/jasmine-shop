# Reset sandi pelanggan

## Deploy

1. Backup database produksi dan pilih database yang benar di phpMyAdmin.
2. Import `docs/password-reset.sql` melalui tab **Import**. Ini membuat tiga tabel baru tanpa mengubah data akun. Jika memakai database prefix, sesuaikan nama tabel dahulu.
3. Tabel `user` harus memakai InnoDB agar perubahan sandi, pemakaian kode dan pencabutan sesi atomik. Periksa engine sebelum deploy; rencanakan konversi terpisah bila diperlukan.
4. Alternatif untuk instalasi dengan riwayat migration yang sinkron: jalankan migration aplikasi melalui prosedur deploy proyek. Hindari menjalankan semua migration secara membabi buta pada produksi yang sebelumnya memakai SQL manual (termasuk tabel Facebook).
5. Isi konfigurasi SMTP di `.env` server menggunakan `docs/password-reset.env.example`. Pertahankan konfigurasi SMTP yang sudah bekerja. Gunakan port dan enkripsi sesuai penyedia; contoh mengikuti default proyek 465/ssl. `fromEmail` harus pengirim terverifikasi penyedia. Simpan password hanya di server, jangan commit atau kirim lewat chat.
6. Jalankan `php spark password-reset:check`. Pemeriksaan ini hanya membaca kesiapan konfigurasi, skema dan engine, bukan mengirim email atau memastikan delivery.
7. Buka `/lupa-sandi` melalui HTTPS. Uji menggunakan email pelanggan milik sendiri: terima kode, reset sandi, login ulang lewat email/username, dan pastikan sesi lama keluar pada permintaan berikutnya. Periksa Inbox/Spam, autentikasi domain SPF/DKIM/DMARC dan log penyedia bila email tertunda.

Fitur username sebelumnya memiliki SQL terpisah: `docs/customer-usernames.sql`. Import jika belum dilakukan. Tidak ada variabel Facebook baru untuk reset sandi.

## Perilaku

- Email reset berisi kode acak 6 digit (termasuk nol di depan), bukan tautan/token URL. Kode disimpan sebagai hash; tidak disimpan dalam session atau log aplikasi.
- Kode berlaku 10 menit, sekali pakai, maksimal 5 percobaan. Kirim ulang minimal 60 detik; maksimal 3 permintaan per email dan 10 per IP per 15 menit. Verifikasi dibatasi 30 per IP per 15 menit. Pembatas disimpan di database dan berlaku lintas sesi.
- Balasan permintaan sama untuk email terdaftar, tidak terdaftar, pembatasan, atau kegagalan SMTP. Waktu respons tidak dijamin identik karena pengiriman SMTP sinkron.
- Akun pelanggan role 0, termasuk akun Google/Facebook, dapat menetapkan sandi lewat email akun yang tersimpan. Tidak membuat akun baru, mengubah email, atau mengaktifkan akun nonaktif. Akun admin dikecualikan.
- Sandi baru minimal 8 karakter, mengandung angka, maksimal 72 byte. Kode OTP wajib angka; sandi boleh menggabungkan huruf, angka dan simbol. Konfirmasi wajib sama.
- Reset berhasil mencabut sesi akun lama pada permintaan berikutnya dan mengirim pemberitahuan perubahan. Pelanggan login kembali; tidak otomatis masuk. Kegagalan email pemberitahuan tidak membatalkan sandi yang sudah berhasil disimpan.
- Form dilindungi CSRF; permintaan kode juga memakai konfigurasi reCAPTCHA proyek. OTP/sandi tidak dipantulkan ke halaman atau flash session.

## Operasional

- Gunakan HTTPS dan `CI_ENVIRONMENT = production`. Jangan aktifkan SMTP debugger atau pencatatan body POST yang memuat kode/sandi di proxy/APM.
- Jangan hapus `customer_password_sessions` selama akun masih ada: penanda ini diperlukan untuk terus menolak sesi lama. Sertakan ketiga tabel dalam backup serta prosedur penghapusan akun.
- Jika diperlukan, jadwalkan pembersihan record kode kedaluwarsa dan bucket rate-limit yang lebih tua dari 24 jam; tidak ada penghapusan otomatis produksi dalam fitur ini. Jangan membersihkan penanda versi sesi sebagai cache biasa.
- Pengujian otomatis memakai alamat dan transport email tiruan; pengiriman SMTP nyata harus diverifikasi setelah konfigurasi server.
