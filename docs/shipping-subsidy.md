# Subsidi ongkir ekspedisi

## Admin
Menu `/shippingadmin` → Subsidi Ongkir: aktif/nonaktif (default OFF), persentase 0–100 dengan dua desimal, batas nominal per pesanan (0 = tanpa batas nominal), pilihan kurir (kosong = semua), dan periode opsional dalam WIB (Asia/Jakarta). Akhir periode bersifat eksklusif.

Subsidi dihitung per pilihan layanan untuk satu pesanan, bukan per produk. Subsidi rupiah dibulatkan ke bawah: `min(floor(tarif_asli * persentase_bps / 10000), batas_jika_ada)`. Ongkir pembeli = tarif asli − subsidi. Jasa Kirim Toko tidak mendapat subsidi.

## Penyimpanan dan pembayaran
- Konfigurasi menggunakan JSON `shipping_methods.settings.subsidy`. Penyimpanan pengaturan utama dan subsidi memakai merge dengan row lock agar tidak saling menimpa origin/kurir/subsidi.
- Quote menyimpan harga final pada `shipping_quotes.cost` serta snapshot tarif asli/subsidi/policy pada `raw_response.shipping_pricing`; respons pembeli memakai whitelist dan tidak mengirim snapshot atau raw provider response.
- Snapshot disalin ke satu kolom baru `order_shipments.shipping_pricing` ketika order dibuat. Nullable, tanpa backfill, tanpa mengubah nominal order lama.
- Cost yang dipakai pembayaran tetap dari quote server tervalidasi. Rumus produk, voucher, poin, proteksi, dan administrasi pembayaran tidak diganti. Midtrans menerima ongkir final pada item dan gross amount existing.
- Perubahan konfigurasi/periode/kelayakan menyebabkan quote belum dibayar yang tidak sesuai diminta cek ongkir ulang, bukan mengubah nominal pembayaran diam-diam. Tidak ada panggilan API tambahan saat validasi policy.
- Pengaturan baru tidak mengubah snapshot/nominal order yang sudah dibuat, termasuk order pending payment yang telah memiliki nominal gateway.
- Jika kolom baru belum tersedia, aktivasi ditolak dan quote tetap memakai tarif normal. Internal delivery tetap mengikuti coverage existing.
- CSRF khusus endpoint admin (termasuk semua form pengaturan pengiriman) dan pemeriksaan session admin; CSRF global tidak diaktifkan.

## Pembeli
Hanya melihat nama kurir/layanan dan ongkir akhir. Tidak ada badge subsidi, harga asli dicoret, atau rincian subsidi pada checkout, transaksi, maupun lacak pengiriman. Metadata akuntansi juga dilepas dari shipment map pelanggan.

## MyLuna
- Payload server menyertakan `shipping_pricing` berupa currency, original_cost, subsidy_amount, dan payable_cost.
- Backend menyimpan tiga nominal sebagai blok catatan internal `[Rincian Ongkir Website]` pada Sale, baik saat impor pembayaran maupun update pengiriman. Snapshot yang sudah tercatat tidak boleh diubah melalui replay webhook.
- Receiver memvalidasi nominal integer nonnegatif dan `asli = subsidi + dibayar pembeli`. Retry bersifat idempoten dan tidak mengulang stok.
- Website memerlukan acknowledgment `shipping_pricing_recorded` ketika subsidi lebih dari nol. Receiver lama tidak diklaim sudah mencatat subsidi.
- Ini catatan alokasi/biaya yang ditanggung toko, BUKAN bukti pembayaran kurir. Tidak membuat Expense, refund, pembayaran kurir, atau perubahan laba laporan otomatis. Posting pengeluaran harus mengikuti pencatatan pembayaran kurir existing agar biaya tidak dihitung ganda. Laporan akuntansi terstruktur dari metadata ini dapat dikembangkan sebagai tahap terpisah.

## Aktivasi produksi (belum dijalankan)
1. Backup dan review schema production/staging, termasuk prefix tabel serta migration pending.
2. Deploy receiver MyLuna yang mendukung rincian ongkir terlebih dahulu.
3. Tambahkan kolom via migration `2026-10-15-000002_AddShippingPricingSnapshot.php` ATAU SQL `docs/shipping-subsidy-production.sql` setelah review. Jangan keduanya. SQL manual perlu rekonsiliasi riwayat migration terpisah sebelum memakai runner CI4 di kemudian hari.
4. Deploy website, cek checkout lama saat OFF, lalu aktifkan subsidi hanya sesudah pengujian staging.
5. Tidak memerlukan API key baru atau paket tambahan. Jangan menjalankan seluruh migration pending tanpa review.

## Uji staging
- 100.000 dengan 10% → 90.000; cap 5.000 → 95.000; 100% → 0; kurir di luar pilihan tetap tarif normal.
- Internal Rp0 tetap Rp0; data berat, origin/destination dan tarif asli provider tidak diubah.
- Periode WIB, quote expired, policy diubah setelah quote, alamat/cart diubah, token milik akun lain.
- Periksa tab network: hanya cost final, tidak ada raw_response/shipping_pricing/subsidy_amount.
- Periksa total Midtrans/transfer, order baru, order lama, dan snapshot audit admin.
- Simpan pengaturan utama tidak mereset subsidi; simpan subsidi tidak mereset origin/kurir.
- Akses non-admin dan CSRF invalid ditolak; rollback migration berisi snapshot ditolak.
- Receiver MyLuna mati/lama: gagal diakui tanpa menghapus order; retry setelah receiver pulih tanpa duplikasi stok atau catatan.

## Rollback
Nonaktifkan subsidi untuk quote baru. Jangan mengubah snapshot atau ongkir order existing. Rollback aplikasi mempertahankan kolom nullable/data. Migration down menolak menghapus kolom yang sudah memiliki snapshot; backup dan persetujuan terpisah diperlukan sebelum penghapusan data.
