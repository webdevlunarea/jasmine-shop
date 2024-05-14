<?= $this->extend('layout/template'); ?>
<?= $this->section('content'); ?>
<div class="container-melayang d-flex flex-column gap-2">
    <a class="btn-circle" href="/form"><i class="material-icons">insert_comment</i></a>
    <a class="btn-circle hijau" href="https://wa.me/+628112938160"><svg viewBox="0 0 32 32" class="whatsapp-ico">
            <path
                d=" M19.11 17.205c-.372 0-1.088 1.39-1.518 1.39a.63.63 0 0 1-.315-.1c-.802-.402-1.504-.817-2.163-1.447-.545-.516-1.146-1.29-1.46-1.963a.426.426 0 0 1-.073-.215c0-.33.99-.945.99-1.49 0-.143-.73-2.09-.832-2.335-.143-.372-.214-.487-.6-.487-.187 0-.36-.043-.53-.043-.302 0-.53.115-.746.315-.688.645-1.032 1.318-1.06 2.264v.114c-.015.99.472 1.977 1.017 2.78 1.23 1.82 2.506 3.41 4.554 4.34.616.287 2.035.888 2.722.888.817 0 2.15-.515 2.478-1.318.13-.33.244-.73.244-1.088 0-.058 0-.144-.03-.215-.1-.172-2.434-1.39-2.678-1.39zm-2.908 7.593c-1.747 0-3.48-.53-4.942-1.49L7.793 24.41l1.132-3.337a8.955 8.955 0 0 1-1.72-5.272c0-4.955 4.04-8.995 8.997-8.995S25.2 10.845 25.2 15.8c0 4.958-4.04 8.998-8.998 8.998zm0-19.798c-5.96 0-10.8 4.842-10.8 10.8 0 1.964.53 3.898 1.546 5.574L5 27.176l5.974-1.92a10.807 10.807 0 0 0 16.03-9.455c0-5.958-4.842-10.8-10.802-10.8z"
                fill-rule="evenodd"></path>
        </svg></a>
</div>
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
            <div class="carousel-inner" style="border-radius: 0.5em;">
                <div class=" carousel-item active">
                    <img src="img/banner1.webp?v=123" class="d-block" style="max-width: 100%; height: auto;"
                        alt="banner1">
                </div>
                <div class="carousel-item">
                    <img src="img/banner2.webp?v=123" class="d-block" style="max-width: 100%; height: auto;"
                        alt="banner2">
                </div>
                <div class="carousel-item">
                    <img src="img/banner3.webp?v=123" class="d-block" style="max-width: 100%; height: auto;"
                        alt="banner3">
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
    <div class="container my-3">
        <h5 class="jdl-section">Kategori</h5>
        <h1 class="mb-1">Kategori Produk</h1>
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
        <h5 class="jdl-section">Hari ini</h5>
        <h1 class="mb-1">Produk Baru</h1>
        <div class="card-group1 no-scroll">
            <?php foreach ($produkBaru as $p) { ?>
            <a class="card1" href="/product/<?= $p['id']; ?>">
                <?php if ($p['diskon']) { ?>
                <p class="diskon">-<?= number_format((float)$p['diskon'], 2, '.', ''); ?>%</p>
                <?php } ?>
                <img src="data:image/webp;base64,<?= base64_encode($p['gambar']); ?>" alt="">
                <div>
                    <h5 class="mb-0"><?= $p['nama']; ?></h5>
                    <?php foreach (json_decode($p['varian'], true) as $v) { ?>
                    <p class="mb-0 varian"><?= $v ?></p>
                    <?php } ?>
                    <!-- <p class="mb-0 varian"><?= implode(" - ", json_decode($p['varian'], true)); ?></p> -->
                    <?php if ($p['diskon']) { ?>
                    <span class="d-flex gap-1 align-items-center">
                        <p class="mb-0 diskon-coret"
                            style="text-decoration: line-through; color: grey; width:fit-content;">
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
    <!-- <div class="container my-3">
        <h5 class="jdl-section">Bulan Ini</h5>
        <div class="d-flex justify-content-between mb-3">
            <h1 class="mb-0">Produk Terbaik</h1>
            <button class="btn btn-primary1">Lihat Semua</button>
        </div>
        <div class="card-group1 no-scroll">
            <a class="card1"><img src="img/nopic.jpg" alt="">
                <div class="mt-3">
                    <h5 class="mb-0">JOYSTICK H-92 New!</h5>
                    <p class="mb-0 harga">Rp 50.000</p>
                </div>
            </a>
            <a class="card1"><img src="img/nopic.jpg" alt="">
                <div class="mt-3">
                    <h5 class="mb-0">JOYSTICK H-92 New!</h5>
                    <p class="mb-0 harga">Rp 50.000</p>
                </div>
            </a>
            <a class="card1"><img src="img/nopic.jpg" alt="">
                <div class="mt-3">
                    <h5 class="mb-0">JOYSTICK H-92 New!</h5>
                    <p class="mb-0 harga">Rp 50.000</p>
                </div>
            </a>
            <a class="card1"><img src="img/nopic.jpg" alt="">
                <div class="mt-3">
                    <h5 class="mb-0">JOYSTICK H-92 New!</h5>
                    <p class="mb-0 harga">Rp 50.000</p>
                </div>
            </a>
            <a class="card1"><img src="img/nopic.jpg" alt="">
                <div class="mt-3">
                    <h5 class="mb-0">JOYSTICK H-92 New!</h5>
                    <p class="mb-0 harga">Rp 50.000</p>
                </div>
            </a>
            <a class="card1"><img src="img/nopic.jpg" alt="">
                <div class="mt-3">
                    <h5 class="mb-0">JOYSTICK H-92 New!</h5>
                    <p class="mb-0 harga">Rp 50.000</p>
                </div>
            </a>
            <a class="card1"><img src="img/nopic.jpg" alt="">
                <div class="mt-3">
                    <h5 class="mb-0">JOYSTICK H-92 New!</h5>
                    <p class="mb-0 harga">Rp 50.000</p>
                </div>
            </a>
            <a class="card1"><img src="img/nopic.jpg" alt="">
                <div class="mt-3">
                    <h5 class="mb-0">JOYSTICK H-92 New!</h5>
                    <p class="mb-0 harga">Rp 50.000</p>
                </div>
            </a>
            <a class="card1"><img src="img/nopic.jpg" alt="">
                <div class="mt-3">
                    <h5 class="mb-0">JOYSTICK H-92 New!</h5>
                    <p class="mb-0 harga">Rp 50.000</p>
                </div>
            </a>
            <a class="card1"><img src="img/nopic.jpg" alt="">
                <div class="mt-3">
                    <h5 class="mb-0">JOYSTICK H-92 New!</h5>
                    <p class="mb-0 harga">Rp 50.000</p>
                </div>
            </a>
            <a class="card1"><img src="img/nopic.jpg" alt="">
                <div class="mt-3">
                    <h5 class="mb-0">JOYSTICK H-92 New!</h5>
                    <p class="mb-0 harga">Rp 50.000</p>
                </div>
            </a>
            <a class="card1"><img src="img/nopic.jpg" alt="">
                <div class="mt-3">
                    <h5 class="mb-0">JOYSTICK H-92 New!</h5>
                    <p class="mb-0 harga">Rp 50.000</p>
                </div>
            </a>
            <a class="card1"><img src="img/nopic.jpg" alt="">
                <div class="mt-3">
                    <h5 class="mb-0">JOYSTICK H-92 New!</h5>
                    <p class="mb-0 harga">Rp 50.000</p>
                </div>
            </a>
            <a class="card1"><img src="img/nopic.jpg" alt="">
                <div class="mt-3">
                    <h5 class="mb-0">JOYSTICK H-92 New!</h5>
                    <p class="mb-0 harga">Rp 50.000</p>
                </div>
            </a>
            <a class="card1"><img src="img/nopic.jpg" alt="">
                <div class="mt-3">
                    <h5 class="mb-0">JOYSTICK H-92 New!</h5>
                    <p class="mb-0 harga">Rp 50.000</p>
                </div>
            </a>
            <a class="card1"><img src="img/nopic.jpg" alt="">
                <div class="mt-3">
                    <h5 class="mb-0">JOYSTICK H-92 New!</h5>
                    <p class="mb-0 harga">Rp 50.000</p>
                </div>
            </a>
        </div>
    </div> -->
</div>
<?= $this->endSection(); ?>