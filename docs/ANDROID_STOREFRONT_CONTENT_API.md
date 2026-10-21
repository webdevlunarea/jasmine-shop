# Android Storefront Content API

API publik untuk menyiapkan data storefront website/admin agar kelak bisa ditampilkan di Android. Semua endpoint read-only, memakai envelope `success/data/meta/error` API v1, dan tidak menerima perubahan stok, harga, atau status transaksi dari klien.

## Endpoint

| Method | Route | Respons utama |
|---|---|---|
| GET | `/api/v1/home/banners` | Banner aktif dari admin, berurutan, dengan URL gambar berversi |
| GET | `/api/v1/home/flash-sale` | Pengaturan promo kilat, waktu berakhir WIB dari server, dan produk yang memenuhi syarat admin |
| GET | `/api/v1/catalog/categories` | Kategori aktif beserta slug dan gambar yang ditetapkan admin |
| GET | `/api/v1/catalog/products` | Katalog, termasuk harga, diskon, stok, varian; filter/pagination/sort mengikuti kontrak API v1 |
| GET | `/api/v1/catalog/products/{id}` | Detail produk, galeri, deskripsi, ulasan, dan ketersediaan layanan produk |
| GET | `/api/v1/vouchers/promotions` | Promosi publik yang aktif |
| GET | `/api/v1/articles?page=1&per_page=10` | Daftar artikel ringkas dengan pagination; query opsional `category` dan `q` |
| GET | `/api/v1/articles/{slug}` | Detail artikel berbentuk teks, metadata dan URL gambar |

Data personal/pesanan tetap memakai endpoint terautentikasi yang sudah tercantum di `ANDROID_API_V1_CONTRACT.md`.

## Perilaku pembaruan

- Konten dibaca langsung dari database website saat endpoint dipanggil; tidak ada salinan katalog atau kampanye yang perlu disinkronkan manual.
- Perubahan admin terlihat pada pemanggilan API berikutnya. Banner gambar memiliki revision query agar file baru tidak tertahan cache URL gambar lama.
- API ini bukan push real-time. FCM tetap untuk pemberitahuan; Android perlu memanggil ulang endpoint ketika membuka/menyegarkan layar.
- Status Flash Sale, batas waktu, produk eligible, harga, dan stok berasal dari server; aplikasi tidak boleh menghitung ulang kebenaran transaksi.
- Gambar dan isi artikel tidak dikirim sebagai blob dalam daftar. Respons menyediakan URL gambar; detail artikel mengirim teks yang sudah diubah dari HTML.

## Contoh bentuk data

`GET /api/v1/home/banners`:

```json
{"success":true,"data":{"banners":[{"id":"12","title":"Promo","alt":"Promo furnitur","image_url":"https://lunareafurniture.com/imgbanner/12?v=...","link":"/produk","order":1}]},"meta":{}}
```

`GET /api/v1/home/flash-sale` mengembalikan `data.flash_sale` dengan `enabled`, `title`, `subtitle`, `kicker`, `end_time`, `ends_at`, `remaining_seconds`, dan `products`.

Artikel menggunakan `data.articles` untuk daftar dan `data.article` untuk detail. Kategori tetap kompatibel dengan properti `name` lama dan menambahkan `slug` serta `image_url`.

## Verifikasi

1. `php spark routes` harus menampilkan keempat route storefront tambahan.
2. `php -l` pada controller terkait harus lulus.
3. Pada server setelah deploy, uji GET tiap endpoint; pastikan `success=true`, daftar banner hanya berisi yang aktif, dan nonaktifkan banner di admin lalu panggil ulang untuk melihat banner hilang.
4. Ubah gambar banner dari admin; pastikan `image_url` mendapat revision berbeda.
5. Ubah pengaturan Flash Sale atau kategori/artikel dari admin; panggilan berikutnya harus mencerminkan nilainya.
