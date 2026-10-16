# Halaman admin pemetaan MyLuna

## Status

Implementasi lokal; belum di-push atau di-deploy pada pekerjaan ini. Tidak ada SQL, perubahan konfigurasi, atau penulisan data produksi yang dijalankan.

Menu baru: **Katalog → Pemetaan MyLuna**, path `/lunamappingadmin`.

## Cara penggunaan setelah rilis

1. Login admin Lunarea dan pilih produk website.
2. Cari/pilih produk MyLuna berdasarkan model dan spesifikasi yang benar. Nama mirip bukan persetujuan otomatis.
3. Pilih UUID pasangan untuk setiap varian. Nama lokal boleh berbeda (misalnya PUTIH/WHITE), tetapi pilihan harus diperiksa manusia. Satu UUID varian tidak dipilih dua kali.
4. Isi catatan pemeriksaan dan centang konfirmasi, lalu simpan satu produk.
5. Ulangi untuk produk aktif yang belum dipetakan. Produk yang belum jelas dibiarkan belum terhubung.

Produk/varian lama yang sudah dipetakan tidak dialihkan ke ID lain dari halaman ini. Rekonsiliasi pasangan salah adalah pekerjaan terpisah, terutama jika sudah ada pesanan yang memakai referensinya.

## Batas perubahan

- MyLuna hanya menambah GET `/api/v1/integrations/lunarea-web-catalogue` dengan token integrasi backend. Query diproyeksikan ke ID/SKU/nama/status/ref website/UUID varian; tidak mengirim harga beli, stok, credential, atau data pelanggan.
- Website membaca katalog melalui backend HTTPS; token tidak diteruskan ke browser. Gagal koneksi/respons tidak lengkap memblokir penyimpanan.
- Saat menyimpan: pemeriksaan admin, metode POST, CSRF, konfirmasi/catatan, fresh master fingerprint, revision website, advisory lock katalog existing, transaksi dan row lock, kepemilikan varian, duplikasi produk, serta readback hasil penyimpanan.
- Koneksi MyLuna dikonfigurasi memakai URL/token integrasi existing. Endpoint katalog diturunkan sebagai sibling dari URL `lunarea-web-order`; bukan URL yang diberikan browser.
- Metadata ditulis ke `konstanta` memakai prefix `luna_product_identity_` existing. Memerlukan kapasitas `value` cukup; model memeriksa schema aktual dan menolak overflow. Schema produksi sebelumnya sudah diverifikasi TEXT. Tidak ada tabel baru/migration atau SQL tambahan untuk rilis ini.
- Catatan pemeriksaan terakhir menyimpan admin, UTC timestamp, alasan dan fingerprint master dalam metadata. Ini bukan tabel riwayat audit lengkap; log aplikasi mencatat kejadian penyimpanan tanpa token.
- ID produk/varian, stok, harga, foto, keranjang dan snapshot pesanan lama tidak ditulis. Tidak membuat/meneruskan order. L00000227 tidak disentuh.
- `LUNA_PRODUCT_IDENTITY_ENABLED` tidak diubah. Jika OFF, checkout tetap memakai perilaku sebelumnya: menyimpan mapping bukan berarti checkout otomatis menggunakannya. Aktivasi terpisah setelah cakupan produk dan pengujian pesanan disetujui.
- Pemeriksaan katalog live adalah verifikasi saat request, bukan transaksi atomik lintas dua database. Import order MyLuna tetap memvalidasi master/varian di dalam transaksi existing.
- Batas katalog saat ini 2.000 produk; jika terlampaui request ditolak, bukan dipotong diam-diam. Paginasi provider menjadi pekerjaan berikutnya bila diperlukan.

## Pengujian yang dijalankan

- Suite PHP: 388 tes, 2.224 assertions, 2 tes MySQL opt-in dilewati pada suite biasa.
- Uji MySQL 8 hasil restore localhost khusus metadata review: 1 tes, 10 assertions. Verifikasi readback, konflik satu master untuk dua website, produk/order/stok tidak berubah, rollback metadata, lalu server staging dihentikan normal.
- Regresi backend integrasi website berbasis mock: 12 suite / 134 tes lulus; `tsc --noEmit` lulus. Peringatan ts-jest TS151002 existing tetap ada.
- Browser Chrome headless dengan fixture sintetis localhost: dropdown tidak memilih otomatis, varian duplikat memblokir tombol, varian lengkap diterima, payload form benar, API gagal memblokir kontrol form, muat ulang pulih. Desktop 1365px dan mobile 390px diperiksa; tidak ada horizontal overflow pada fixture.
- Pemeriksaan visual menggunakan CSS/view asli tetapi fixture tanpa shell/login/database aplikasi penuh. Ini bukan bukti end-to-end produksi. Alat browser interaktif mengalami masalah koneksi; pemeriksaan fixture memakai browser headless lokal.
- Tidak melakukan login, pemetaan, forward order, atau request API produksi pada pengembangan halaman ini.

## Urutan rilis yang disarankan

1. Backup kode dan database melalui prosedur existing; jangan menjalankan cleanup/migration otomatis.
2. Review diff kedua repository dan deploy endpoint read-only MyLuna terlebih dahulu (build backend existing).
3. Pastikan request tanpa token menghasilkan 401; verifikasi respons ber-token melalui backend tepercaya tanpa mencetak token. Tidak ada POST order sebagai smoke test.
4. Deploy halaman admin website. Cek akses nonadmin, CSRF, menu, pencarian, kondisi koneksi gagal, dan kecocokan katalog.
5. Admin memilih satu pasangan yang sudah diverifikasi. Periksa metadata dan produk/stok/order sebelum/sesudah dengan SELECT. Jangan memakai L00000227 sebagai tes penerusan.
6. Review mapping bertahap. Checkout/flag tetap unchanged sampai pengujian staging alur pesanan dan cakupan produk disetujui tersendiri.

## Rollback

Nonaktifkan/hilangkan akses menu dan routes halaman baru melalui rollback kode terarah. Biarkan metadata yang sudah dikonfirmasi; jangan hapus tabel/metadata atau membalik stok massal. Endpoint katalog read-only boleh tetap tersedia untuk kompatibilitas. Tidak ada SQL downgrade. Jika ditemukan pasangan salah, hentikan penggunaan pasangan tersebut dan review dampak pesanan sebelum koreksi terpisah.
