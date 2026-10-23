# Suspend akun pelanggan

Fitur admin untuk menangguhkan akun yang perlu ditinjau karena dugaan penyalahgunaan. Suspend tidak menghapus akun, pesanan, atau pembayaran.

## Aktivasi produksi

1. Backup database produksi.
2. Untuk instalasi baru, jalankan `docs/sql/customer_account_suspensions.sql`. Jika tabel suspend versi sebelumnya sudah dibuat, jalankan `docs/sql/customer_account_suspensions_upgrade.sql` sekali **sebelum deploy kode baru**.
3. Deploy kode aplikasi.
4. Buka `/customersuspensionadmin` menggunakan akun admin.
5. Uji terlebih dahulu pada akun uji: suspend, pastikan sesi web dan aplikasi lama ditolak, lalu cabut suspend dan masuk ulang.

Jangan mengaktifkan fitur sebelum kedua tabel SQL berhasil dibuat. Jika tabel belum ada, layar admin memberi tahu dan status suspend tidak diberlakukan.

## Dampak suspend

- Login web (email/password, Google, Facebook) dan Android ditolak.
- Admin dapat memilih masa 24 jam, 7 hari, 30 hari, atau tanpa batas waktu, serta mengisi pesan yang terlihat oleh pelanggan. Waktu menggunakan UTC di penyimpanan.
- Sesi browser, token API, dan tantangan login lama dicabut/ditolak.
- Perangkat push dinonaktifkan dan notifikasi antrean yang belum terkirim dibatalkan.
- Permintaan API pada sesi aktif Android menerima `403 ACCOUNT_SUSPENDED`; aplikasi menghapus token lokal dan kembali ke layar akun dengan pesan yang dapat dipahami.
- Pesanan dan pembayaran yang sudah tercatat tetap mengikuti status dan proses normal; suspend tidak membatalkan transaksi.
- Saat masa berlaku habis, akun otomatis tidak lagi dianggap suspend. Sesi yang sebelumnya dicabut tidak dipulihkan; pengguna harus login lagi. Push tetap nonaktif sampai pengguna mengaktifkannya kembali.

## Pemulihan

Gunakan tombol **Cabut suspend** dari halaman admin. Pengguna perlu login kembali dan mengaktifkan notifikasi lagi pada perangkatnya. Riwayat tindakan disimpan dalam `customer_account_suspension_events`.

Untuk merekam event kedaluwarsa dan membersihkan baris yang sudah berakhir, jadwalkan command berikut tiap 5 menit sebagai pengguna proses website:

```cron
*/5 * * * * cd /mine/lunareafurniture.com/jasmine-shop && /usr/local/lsws/lsphp83/bin/php spark customers:suspensions-expire >> writable/logs/customer-suspensions-expire.log 2>&1
```

Pengecekan masa berlaku tetap berjalan tanpa cron; cron hanya merapikan state lama dan menulis event `expired` ke riwayat.

Alasan suspend dicatat untuk audit internal. Masukkan ringkasan faktual atau referensi tiket, tanpa kredensial atau data sensitif yang tidak diperlukan.
