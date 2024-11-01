<?= $this->extend('layout/template'); ?>
<?= $this->section('content'); ?>
<?php if ($msg_active) { ?>
    <div id="modal-voucher" style="position: fixed; top: 0; left: 0; width: 100vw; height: 100svh; z-index: 99;"
        class="d-flex justify-content-center align-items-center">
        <div style="border-radius: 10px; overflow: hidden; background-color: white; width: 80%; max-width: 500px; box-shadow: 0 0 20px rgba(0, 0, 0, 0.3);"
            class="p-5">
            <h1 class="teks-sedang mb-3">Klaim voucher diskon 5% Anda sekarang juga</h1>
            <p class="text-secondary">*S&K diskon ini hanya berlaku 1 bulan sejak menjadi member kami</p>
            <a href="/all" class="btn btn-primary1 w-100 text-center mb-2">Beli Produk</a>
            <button class="btn-teks-aja mx-auto" onclick="closeModalVoucher()">Nanti</button>
        </div>
    </div>
    <script>
        function closeModalVoucher() {
            document.getElementById('modal-voucher').classList.add('d-none')
            document.getElementById('modal-voucher').classList.remove('d-flex')
        }
    </script>
<?php } ?>
<div class="konten pb-0">
    <div class="container">
        <div id="carouselExampleIndicators" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-indicators">
                <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
                <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="1" aria-label="Slide 2"></button>
                <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="2" aria-label="Slide 3"></button>
                <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="3" aria-label="Slide 4"></button>
            </div>
            <div class="carousel-inner" style="border-radius: 0.5em;">
                <div class=" carousel-item active">
                    <img src="img/banner1.webp?v=123" class="show-ke-hide" style="width: 100%; height: auto;" alt="Lunarea Furniture">
                    <img src="img/banner1 hp.webp?v=123" class="hide-ke-show-block" style="width: 100%; height: auto;" alt="Lunarea Furniture">
                </div>
                <div class="carousel-item">
                    <img loading="lazy" src="img/banner2.webp?v=123" class="show-ke-hide" style="width: 100%; height: auto;" alt="Lunarea Furniture Gratis Ongkir">
                    <img loading="lazy" src="img/banner2 hp.webp?v=123" class="hide-ke-show-block" style="width: 100%; height: auto;" alt="Lunarea Furniture Gratis Ongkir">
                </div>
                <div class="carousel-item">
                    <img loading="lazy" src="img/banner3.webp?v=1.0" class="show-ke-hide" style="width: 100%; height: auto;" alt="Lunarea Special Sale Discount 5%">
                    <img loading="lazy" src="img/banner3 hp.webp?v=1.0" class="hide-ke-show-block" style="width: 100%; height: auto;" alt="Lunarea Special Sale Discount 5%">
                </div>
                <div class="carousel-item">
                    <img loading="lazy" src="img/banner4.webp?v=123" class="show-ke-hide" style="width: 100%; height: auto;" alt="Lunarea Minimalist Furniture">
                    <img loading="lazy" src="img/banner4 hp.webp?v=123" class="hide-ke-show-block" style="width: 100%; height: auto;" alt="Lunarea Minimalist Furniture">
                </div>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
        </div>
    </div>
    <div class="container my-3">
        <h5 class="jdl-section">Kategori</h5>
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="mb-1">Kategori Produk</h1>
            <div class="gap-2 show-flex-ke-hide">
                <div class="btn-primary1 btn-geser-kategori" id="kiri" style="padding: 0.5em; border-radius: 1.5em; cursor: default;"><i class="material-icons">chevron_left</i></div>
                <div class="btn-primary1 btn-geser-kategori" id="kanan" style="padding: 0.5em; border-radius: 1.5em; cursor: default;"><i class="material-icons">chevron_right</i></div>
            </div>
        </div>
        <div class="container-kategori-scroll">
            <div class="container-kategori">
                <a class="kategori" href="/all/lemari-dewasa">
                    <img src="/img/logokategori/Lemari_Dewasa.webp" alt="" width="50px">
                    <p>Lemari Dewasa</p>
                </a>
                <a class="kategori" href="/all/lemari-anak">
                    <img src="/img/logokategori/Lemari_Anak.webp" alt="" width="50px">
                    <p>Lemari Anak</p>
                </a>
                <!-- <a class="kategori" href="/all/lemari-serbaguna">
                    <img src="/img/logokategori/Lemari_Hias.webp" alt="" width="50px">
                    <p>Lemari Serbaguna</p>
                </a> -->
                <a class="kategori" href="/all/meja-rias">
                    <img src="/img/logokategori/Meja_Rias.webp" alt="" width="50px">
                    <p>Meja Rias</p>
                </a>
                <a class="kategori" href="/all/meja-belajar">
                    <img src="/img/logokategori/Meja_Belajar.webp" alt="" width="50px">
                    <p>Meja Belajar</p>
                </a>
                <a class="kategori" href="/all/meja-tv">
                    <img src="/img/logokategori/Meja_TV.webp" alt="" width="50px">
                    <p>Meja TV</p>
                </a>
                <a class="kategori" href="/all/meja-tulis">
                    <img src="/img/logokategori/Meja_Tulis.webp" alt="" width="50px">
                    <p>Meja Tulis</p>
                </a>
                <a class="kategori" href="/all/meja-komputer">
                    <img src="/img/logokategori/Meja_Komputer.webp" alt="" width="50px">
                    <p>Meja Komputer</p>
                </a>
                <a class="kategori" href="/all/rak-sepatu">
                    <img src="/img/logokategori/Rak_Sepatu.webp" alt="" width="50px">
                    <p>Rak Sepatu</p>
                </a>
                <a class="kategori" href="/all/rak-besi">
                    <img src="/img/logokategori/Rak_Besi.webp" alt="" width="50px">
                    <p>Rak Besi</p>
                </a>
                <a class="kategori" href="/all/rak-serbaguna">
                    <img src="/img/logokategori/Rak_Serbaguna.webp" alt="" width="50px">
                    <p>Rak Serbaguna</p>
                </a>
            </div>
        </div>
    </div>
    <div class="container my-3">
        <?php
        $window_width = "<script type='text/javascript'>document.write(window.innerWidth);</script>";
        ?>
        <h5 class="jdl-section">Rekomendasi</h5>
        <h1 class="mb-1">Produk Terpopuler</h1>
        <div class="card-group1 no-scroll">
            <?php foreach ($produkBaru as $p) { ?>
                <a class="card1" href="/product/<?= $p['path']; ?>">
                    <?php if ($p['diskon']) { ?>
                        <p class="diskon">-<?= number_format((float)$p['diskon'], 2, '.', ''); ?>%</p>
                    <?php } ?>
                    <div style="position: relative; width: 100%; aspect-ratio: 1 / 1;">
                        <img class="img-card1-wm" src="<?= base_url('img/WM Black 300.webp'); ?>" alt="Watermark Lunarea">
                        <img class="img-card1" src="data:image/webp;base64,<?= base64_encode($p['gambar']); ?>" alt="<?= $p['nama']; ?>">
                    </div>
                    <div>
                        <h5 class="mb-0"><?= $p['nama']; ?></h5>
                        <?php foreach (json_decode($p['varian'], true) as $v) { ?>
                            <p class="mb-0 varian"><?= $v ?></p>
                        <?php } ?>
                        <!-- <p class="mb-0 varian"><?= implode(" - ", json_decode($p['varian'], true)); ?></p> -->
                        <?php if ($p['diskon']) { ?>
                            <span class="d-flex gap-1 align-items-center">
                                <p class="mb-0 diskon-coret" style="text-decoration: line-through; color: grey; width:fit-content;">
                                    Rp
                                    <?= number_format($p['harga'], 0, ",", "."); ?></p>
                                <p class="diskon-bwh">-<?= (int)$p['diskon']; ?>%</p>
                            </span>
                            <p class="mb-0 harga">Rp
                                <?php
                                $persen = (100 - $p['diskon']) / 100;
                                $hasil = $persen * $p['harga'];
                                echo number_format($hasil, 0, ",", ".");
                                ?></p>
                        <?php } else { ?>
                            <p class="mb-0 harga">Rp <?= number_format($p['harga'], 0, ",", "."); ?></p>
                        <?php } ?>
                        <!-- <p>★★★☆☆ (<?= $p['rate']; ?>)</p> -->
                    </div>
                </a>
            <?php } ?>
        </div>
        <div class="mx-auto mt-2" style="width: fit-content;">
            <a href="/all" class="btn mx-auto btn-primary1" style="width: fit-content;">Lihat Semua Produk</a>
        </div>
    </div>
    <div style="background-color: #c6f1c9; color: #2d7e33;" class="py-5 mt-5">
        <div class="container baris-ke-kolom gap-5">
            <div style="flex: 1" class="d-flex justify-content-start gap-4">
                <img src="../img/logo ongkir.webp" alt="" style="width: 70px; height: 40px">
                <div>
                    <p class="fw-bold mb-1">Gratis Ongkir 100%</p>
                    <p>Pengiriman di pulau Jawa, Madura, dan Bali tanpa minimum pembelian</p>
                </div>
            </div>
            <div style="flex: 1" class="d-flex justify-content-start gap-4">
                <img src="../img/logo diskon.webp" alt="" style="width: 70px; height: 70px">
                <div>
                    <p class="fw-bold mb-1">Spesial offer discount 5%</p>
                    <!-- <p>Untuk setiap pembelian pertama yang telah terdaftar sebagai member di akun Lunarea Furniture</p> -->
                    <p>Hanya untuk member Lunarea di setiap pembelian pertama</p>
                </div>
            </div>
            <div style="flex: 1" class="d-flex justify-content-start gap-4">
                <img src="../img/logo location.webp" alt="" style="width: 70px; height: 60px">
                <div>
                    <p class="fw-bold mb-1">Pengiriman ke seluruh Indonesia</p>
                    <!-- <p>Menggunakan jasa ekspedisi terpercaya untuk mengantarkan produk sampai ke alamat tujuan</p> -->
                    <p>Dengan jasa ekspedisi terpercaya yang mengantarkan ke alamat tujuan</p>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    const containeKategoriScrollElm = document.querySelector('.container-kategori-scroll');
    const btnGeserKategoriElm = document.querySelectorAll('.btn-geser-kategori');

    btnGeserKategoriElm.forEach(btn => {
        btn.addEventListener('click', () => {
            const direction = btn.id === 'kiri' ? -1 : 1;
            const scrollAmount = containeKategoriScrollElm.clientWidth * direction;
            containeKategoriScrollElm.scrollBy({
                left: scrollAmount,
                behavior: "smooth"
            });
        })
    })
</script>
<?= $this->endSection(); ?>