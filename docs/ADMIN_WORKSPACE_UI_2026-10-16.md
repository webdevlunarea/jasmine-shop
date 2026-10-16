# Penyegaran menu admin dan pesanan

## Cakupan
- Sidebar: kelompok Operasional, Katalog, Promosi & konten, Pengaturan; semua 14 tujuan navigasi dipertahankan.
- Topbar lebih ringkas, active state dan aria-current, drawer mobile memakai tombol native, Escape, focus containment, inert ketika tersembunyi dan body scroll lock.
- `/listcustomer`: ringkasan datar, filter status existing, pencarian lokal yang secara eksplisit hanya menelusuri halaman yang sedang terbuka, daftar pesanan ringkas, rincian dibuka dengan tombol terpisah.
- Detail mempertahankan item/varian, total tersimpan, adjustment, ongkir dan rincian subsidi internal, penerima, catatan, resi, dokumen, review MyLuna, retry dan konfirmasi diterima.
- Resi dan bukti transfer menggunakan Bootstrap modal existing (focus trap, backdrop, Escape). Form resi mendukung Enter dan menunggu respons sebelum ditutup. Fokus dikembalikan ke pemicu saat batal.
- Tidak ada perubahan controller, route, database, environment, dependency, formula pembayaran, stok, maupun kontrak integrasi MyLuna.

## Keamanan / kompatibilitas
- Semua teks pelanggan dan item di-escape. Data JavaScript menggunakan JSON_HEX_* untuk mencegah terminasi script oleh isi data.
- CSRF tetap dikirim saat update resi dan disegarkan pada semua hidden token setelah respons.
- Review paid-order tetap POST ke endpoint existing dengan checkbox wajib; tidak ada pengiriman order otomatis dari UI.
- Tindakan tidak ditempel pada keseluruhan baris: klik invoice, checkbox atau tombol tidak menutup/membuka rincian secara tidak sengaja.
- WebSocket kosong / payload invalid tidak menghentikan kontrol pesanan; tidak ada dump data pelanggan ke console.
- Asset admin hanya dimuat untuk role admin. CSS baru di-scope ke `.admin-body`; toko/pembeli tidak memakai stylesheet ini.

## Pengujian
- Unit view PHP: variasi status, total/dokumen/CSRF, escaping, empty state, pagination terakhir, menu dan active state.
- Node unit: accordion, pencarian lokal, review validation/duplicate submit, CSRF dan status payload resi, lock saat saving, error/retry, proof target dan mobile drawer/focus/Escape/resize.
- Preview sintetis dapat dibuat dengan `LUNAREA_UI_PREVIEW=1` saat menjalankan `AdminWorkspaceViewTest`. Preview disimpan di temp OS, tidak menyimpan data pelanggan produksi.
- Chrome headless terisolasi: desktop, rincian aktual dibuka via klik, modal pengiriman, viewport mobile 390px (clientWidth=scrollWidth=390), drawer open/Escape dan pencarian kosong. Tidak ada formulir pembayaran/order yang disubmit ke server.

## Rilis / rollback
Push mengikuti workflow Git existing; cek hasil deployment sebelum mengklaim tampilan produksi berubah. Tidak perlu SQL, Composer atau NPM install. Untuk rollback, revert commit UI dan deploy ulang; tidak mengubah data pesanan.
