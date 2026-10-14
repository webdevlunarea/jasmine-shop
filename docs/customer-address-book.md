# Buku alamat checkout

## Perubahan
- Pilihan alamat tersimpan di Informasi Penerima, tambah/edit/hapus, label, penerima dan alamat utama.
- Maksimal 20 alamat per akun; ownership diperiksa server, tidak menerima email pemilik dari browser.
- Endpoint POST `/account/addresses/manage`, autentikasi pelanggan aktif dan verifikasi CSRF khusus endpoint.
- Wilayah disusun ulang dari tabel lokal beserta relasinya; teks wilayah dari browser bukan sumber kebenaran.
- Pilihan alamat memuat ulang checkout dan mengosongkan token ongkir. Quote tetap terikat akun/keranjang/wilayah melalui validasi existing.
- Session alamat tetap menggunakan format lama, snapshot order dan rumus Midtrans tidak diubah.
- Saat penyimpanan pertama, alamat lama yang berbeda dipertahankan sebagai `Alamat sebelumnya`.
- Tanpa tabel baru, buku alamat disembunyikan dan checkout existing tetap berjalan.

## Aktivasi
1. Backup database dan verifikasi staging memakai MySQL/MariaDB serta schema tabel `pembeli` dan wilayah.
2. Review migration `2026-10-15-000001_CreateCustomerAddresses.php` dan daftar migration pending. Jangan menjalankan seluruh migration pending tanpa review.
3. Jalankan migration yang telah direview di staging, bukan langsung production. Tidak ada migration aplikasi yang dijalankan selama pengembangan ini.
4. Uji matriks di bawah, lalu deployment dan migration production dilakukan terpisah setelah backup/review.
5. Tidak perlu API key baru atau perubahan `.env`.

### Jika menggunakan phpMyAdmin
- SQL tersedia pada `docs/customer-address-book-production.sql`.
- Pilih database website, backup, periksa prefix tabel dan pastikan `customer_addresses` belum ada.
- Jalankan SQL hanya sekali. SQL ini hanya membuat tabel baru, tanpa mengubah tabel existing.
- Pilih SQL manual ATAU migration CI4. SQL manual tidak mengisi riwayat migration CI4; migration tersebut sengaja menolak jika tabel sudah ada. Sebelum memakai runner migration di kemudian hari, lakukan rekonsiliasi riwayat/schema melalui review terpisah, jangan menjalankan keduanya atau menghapus tabel untuk melewati pemeriksaan.
- Schema produksi dan deployment belum diverifikasi dari lingkungan pengembangan ini.

## Pengujian wajib staging
- Akun A menambah Rumah dan Kantor; akun B tidak melihat/mengedit/menghapus alamat A meski ID dimanipulasi.
- Simpan nama/alamat berisi tanda petik dan karakter HTML: tampil sebagai teks, JavaScript tetap berjalan.
- Wilayah tidak lengkap, kecamatan milik kota lain, kelurahan/kode pos tidak sesuai: ditolak tanpa perubahan.
- Alamat pertama menjadi utama; perubahan alamat utama konsisten, termasuk request serentak akun yang sama.
- Tambah alamat baru mempertahankan alamat lama; edit alamat terpilih memperbarui penerima.
- Hapus alamat utama/terpilih/terakhir; order lama tetap berisi snapshot sebelumnya.
- Ganti alamat gratis ke luar coverage: token lama kosong, cek ongkir ulang dan validasi backend tetap berlaku.
- POST tanpa CSRF, tanpa login, akun nonaktif, role admin, atau method GET ditolak.
- Simpan berulang setelah redirect: cookie CSRF tetap valid.
- Desktop/mobile: tombol wrap, tidak ada nested form, tidak memicu pembayaran saat menyimpan alamat.
- Checkout transfer manual dan Midtrans, voucher/poin/proteksi kaca, pesanan pending tetap sama.

## Test otomatis
`php vendor/bin/phpunit --no-coverage --no-logging --do-not-cache-result tests/unit/CustomerAddressModelTest.php`

Test hanya memakai SQLite `:memory:` eksplisit: ownership pembacaan, urutan utama, recipient/timestamp, rollback kosong dan proteksi rollback berisi data. Bukan pengganti uji end-to-end MySQL/browser.

## Rollback
- Rollback aplikasi menonaktifkan UI baru tanpa menghapus tabel/data. Checkout lama membaca `pembeli.alamat` seperti sebelumnya.
- Migration `down()` menolak menghapus tabel berisi alamat. Backup/export dan persetujuan terpisah diperlukan sebelum penghapusan data.
- Jangan menghapus atau mengubah tabel order, pembeli, atau wilayah.
