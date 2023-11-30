<?= $this->extend('layout/template'); ?>
<?= $this->section('content'); ?>
<div class="konten">
    <div class="container">
        <div id="carouselExampleIndicators" class="carousel slide">
            <div class="carousel-indicators">
                <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="0" class="active"
                    aria-current="true" aria-label="Slide 1"></button>
                <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="1"
                    aria-label="Slide 2"></button>
                <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="2"
                    aria-label="Slide 3"></button>
            </div>
            <div class="carousel-inner">
                <div class="carousel-item active">
                    <img src="img/promo1.webp" class="d-block w-100" alt="...">
                </div>
                <div class="carousel-item">
                    <img src="img/promo2.webp" class="d-block w-100" alt="...">
                </div>
                <div class="carousel-item">
                    <img src="img/promo3.webp" class="d-block w-100" alt="...">
                </div>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleIndicators"
                data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleIndicators"
                data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
        </div>
    </div>
    <div class="container my-5">
        <h5 class="jdl-section">Hari ini besok</h5>
        <h1 class="mb-3">Produk Baru</h1>
        <div class="card-group1">
            <?php foreach ($produk as $p) { ?>
            <a class="card1" href="/product/<?= $p['id']; ?>">
                <?php if ($p['diskon']) { ?>
                <p class="diskon">-<?= $p['diskon']; ?>%</p>
                <?php } ?>
                <img src="data:image/jpeg;base64,<?= base64_encode($p['gambar']); ?>" alt="">
                <div class="mt-3">
                    <h5 class="mb-0"><?= $p['nama']; ?></h5>
                    <?php if ($p['diskon']) { ?>
                    <p class="mb-0 harga d-inline">Rp
                        <?php
                                $persen = (100 - $p['diskon']) / 100;
                                $hasil = $persen * $p['harga'];
                                echo number_format($hasil, 0, ",", ".");
                                ?></p>
                    <p class="mb-0 d-inline" style="text-decoration: line-through; font-size: small; color: grey;">Rp
                        <?= number_format($p['harga'], 0, ",", "."); ?></p>
                    <?php } else { ?>
                    <p class="mb-0 harga">Rp <?= number_format($p['harga'], 0, ",", "."); ?></p>
                    <?php } ?>
                    <p>★★★☆☆ (<?= $p['rate']; ?>)</p>
                </div>
            </a>
            <?php } ?>
        </div>
        <div class="mx-auto mt-2" style="width: fit-content;">
            <a href="/all" class="btn btn-danger mx-auto" style="width: fit-content;">View All Products</a>
        </div>
    </div>
    <div class="container my-5">
        <h5 class="jdl-section">Kategori</h5>
        <div class="d-flex justify-content-between mb-3">
            <h1 class="mb-0">Cari Berdasarkan Kategori</h1>
            <div class="d-flex gap-1 justify-content-end">
                <button class="btn btn-icon scroll-kategori show-ke-hide"><i
                        class="material-icons">arrow_back</i></button>
                <button class="btn btn-icon scroll-kategori show-ke-hide"><i
                        class="material-icons">arrow_forward</i></button>
            </div>
        </div>
        <div class="container-kategori">
            <a class="kategori" href="/all/meja">
                <span class="material-symbols-outlined">
                    desk
                </span>
                <p>Meja</p>
            </a>
            <a class="kategori" href="/all/kursi">
                <span class="material-symbols-outlined">
                    chair_alt
                </span>
                <p>Kursi</p>
            </a>
            <a class="kategori" href="/all/lemari">
                <span class="material-symbols-outlined">
                    kitchen
                </span>
                <p>Lemari</p>
            </a>
            <a class="kategori" href="/all/rak">
                <span class="material-symbols-outlined">
                    two_pager
                </span>
                <p>Rak</p>
            </a>
        </div>
    </div>
    <div class="container my-5">
        <h5 class="jdl-section">Bulan Ini</h5>
        <div class="d-flex justify-content-between mb-3">
            <h1 class="mb-0">Produk Terbaik</h1>
            <button class="btn btn-danger">Lihat Semua</button>
        </div>
        <div class="card-group1">
            <div class="card1">
                <img src="img/nopic.jpg" alt="">
                <div class="mt-3">
                    <h5 class="mb-0">JOYSTICK H-92 New!</h5>
                    <p class="mb-0 harga">Rp 50.000</p>
                    <p>★★★☆☆ (100)</p>
                </div>
            </div>
            <div class="card1">
                <img src="img/nopic.jpg" alt="">
                <div class="mt-3">
                    <h5 class="mb-0">JOYSTICK H-92 New!</h5>
                    <p class="mb-0 harga">Rp 50.000</p>
                    <p>★★★☆☆ (100)</p>
                </div>
            </div>
            <div class="card1">
                <img src="img/nopic.jpg" alt="">
                <div class="mt-3">
                    <h5 class="mb-0">JOYSTICK H-92 New!</h5>
                    <p class="mb-0 harga">Rp 50.000</p>
                    <p>★★★☆☆ (100)</p>
                </div>
            </div>
            <div class="card1">
                <img src="img/nopic.jpg" alt="">
                <div class="mt-3">
                    <h5 class="mb-0">JOYSTICK H-92 New!</h5>
                    <p class="mb-0 harga">Rp 50.000</p>
                    <p>★★★☆☆ (100)</p>
                </div>
            </div>
            <div class="card1">
                <img src="img/nopic.jpg" alt="">
                <div class="mt-3">
                    <h5 class="mb-0">JOYSTICK H-92 New!</h5>
                    <p class="mb-0 harga">Rp 50.000</p>
                    <p>★★★☆☆ (100)</p>
                </div>
            </div>
            <div class="card1">
                <img src="img/nopic.jpg" alt="">
                <div class="mt-3">
                    <h5 class="mb-0">JOYSTICK H-92 New!</h5>
                    <p class="mb-0 harga">Rp 50.000</p>
                    <p>★★★☆☆ (100)</p>
                </div>
            </div>
            <div class="card1">
                <img src="img/nopic.jpg" alt="">
                <div class="mt-3">
                    <h5 class="mb-0">JOYSTICK H-92 New!</h5>
                    <p class="mb-0 harga">Rp 50.000</p>
                    <p>★★★☆☆ (100)</p>
                </div>
            </div>
        </div>
    </div>
    <!-- <div class="container my-5">
        <h5 class="jdl-section">Terbaru</h5>
        <h1>Kedatangan Baru</h1>
        <div class="row">
            <div class="col">
                <div class="container-text-bwh-kiri" style="background-color: black; background-image: url('img/ps5 hitam.jpg'); background-size: cover ; background-repeat: no-repeat; height: 100%;">
                    <h3>PlayStation 5</h3>
                    <p>PS5 versi black and white<br>sudah tersedia</p>
                    <a href="" style="color: white;">Shop Nom</a>
                </div>
            </div>
            <div class="col">
                <div class="row pb-1">
                    <div class="container-text-bwh-kiri" style="background-color: black; height: 200px">
                        <h3>PlayStation 5</h3>
                        <p>PS5 versi black and white<br>sudah tersedia</p>
                        <a href="" style="color: white;">Shop Nom</a>
                    </div>
                </div>
                <div class="row pt-2">
                    <div class="col px-0 pe-1">
                        <div class="container-text-bwh-kiri" style="background-color: black; height: 200px; width: 100%;">
                            <h3>PlayStation 5</h3>
                            <p>PS5 versi black</p>
                            <a href="" style="color: white;">Shop Nom</a>
                        </div>
                    </div>
                    <div class="col px-0 ps-2">
                        <div class="container-text-bwh-kiri" style="background-color: black; height: 200px; width: 100%;">
                            <h3>PlayStation 5</h3>
                            <p>PS5 versi black</p>
                            <a href="" style="color: white;">Shop Nom</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div> -->
    <div class="container">
        <div class="row text-center">
            <div class="col">
                <div class="bundar mx-auto mb-3"><i class="material-icons">local_shipping</i></div>
                <h5>ANTAR GRATIS DAN CEPAT</h5>
                <p>Gratis pengantaran untuk semua orderan yang lebih dari Rp 50.000</p>
            </div>
            <div class="col">
                <div class="bundar mx-auto mb-3"><i class="material-icons">headset_mic</i></div>
                <h5>24/7 CUSTOMER SERVICE</h5>
                <p>Friendly 24/7 layanan pelanggan</p>
            </div>
            <div class="col">
                <div class="bundar mx-auto mb-3"><i class="material-icons">monetization_on</i></div>
                <h5>GARANSI UANG KEMBALI</h5>
                <p>Garansi yang kami berikan selama 30 hari</p>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>