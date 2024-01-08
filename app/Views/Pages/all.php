<?= $this->extend('layout/template'); ?>
<?= $this->section('content'); ?>
<div class="konten">
    <div class="container">
        <h5 class="jdl-section">Kategori</h5>
        <h1 class="mb-3">Cari Berdasarkan Kategori</h1>
        <div class="container-kategori-scroll">
            <div class="container-kategori">
                <a class="kategori" href="/all/lemari-dewasa">
                    <img src="/img/logokategori/Lemari_Dewasa.png" alt="" width="50px">
                    <p>Lemari Dewasa</p>
                </a>
                <a class="kategori" href="/all/lemari-anak">
                    <img src="/img/logokategori/Meja_TV.png" alt="" width="50px">
                    <p>Lemari Anak</p>
                </a>
                <a class="kategori" href="/all/meja-rias">
                    <img src="/img/logokategori/Meja_Rias.png" alt="" width="50px">
                    <p>Meja Rias</p>
                </a>
                <a class="kategori" href="/all/meja-belajar">
                    <img src="/img/logokategori/Meja_Belajar.png" alt="" width="50px">
                    <p>Meja Belajar</p>
                </a>
                <a class="kategori" href="/all/meja-belajar-kaki-besi">
                    <img src="/img/logokategori/Meja_TV.png" alt="" width="50px">
                    <p>Meja Belajar Kaki Besi</p>
                </a>
                <a class="kategori" href="/all/meja-tv">
                    <img src="/img/logokategori/Meja_TV.png" alt="" width="50px">
                    <p>Meja TV</p>
                </a>
                <a class="kategori" href="/all/meja-tulis">
                    <img src="/img/logokategori/Meja_Tulis.png" alt="" width="50px">
                    <p>Meja Tulis</p>
                </a>
                <a class="kategori" href="/all/meja-komputer">
                    <img src="/img/logokategori/Meja_TV.png" alt="" width="50px">
                    <p>Meja Komputer</p>
                </a>
                <a class="kategori" href="/all/meja-komputer-kaki-besi">
                    <img src="/img/logokategori/Meja_TV.png" alt="" width="50px">
                    <p>Meja Komputer Kaki Besi</p>
                </a>
                <a class="kategori" href="/all/rak-sepatu">
                    <img src="/img/logokategori/Meja_TV.png" alt="" width="50px">
                    <p>Rak Sepatu</p>
                </a>
                <a class="kategori" href="/all/rak-besi">
                    <img src="/img/logokategori/Meja_TV.png" alt="" width="50px">
                    <p>Rak Besi</p>
                </a>
                <a class="kategori" href="/all/rak-serbaguna">
                    <span class="material-symbols-outlined">
                        two_pager
                    </span>
                    <p>Rak Serbaguna</p>
                </a>
                <a class="kategori" href="/all/kursi">
                    <span class="material-symbols-outlined">
                        chair_alt
                    </span>
                    <p>Kursi</p>
                </a>
            </div>
        </div>
    </div>
    <div class="container my-5">
        <?php if ($nama) { ?>
        <h5 class="mb-3">Anda mencari "<?= $nama ?>"</h5>
        <?php } else { ?>
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h5 class="jdl-section mb-3">
                <?= $kategori ? str_replace('-', ' ', ucfirst($kategori)) : "Semua Kategori"; ?>
            </h5>
            <?php if ($kategori) { ?>
            <a class="btn btn-primary1" href="/all">Semua Kategori</a>
            <?php } ?>
        </div>
        <?php } ?>

        <div class="card-group1 no-scroll mb-3">
            <?php foreach ($produk as $p) { ?>
            <a class="card1" href="/product/<?= $p['id']; ?>">
                <?php if ($p['diskon']) { ?>
                <p class="diskon">-<?= $p['diskon']; ?>%</p>
                <?php } ?>
                <img src="data:image/jpeg;base64,<?= base64_encode($p['gambar']); ?>" alt="">
                <div class="mt-3">
                    <h5 class="mb-0"><?= $p['nama']; ?></h5>
                    <p class="mb-0"><?= implode(" - ", json_decode($p['varian'], true)); ?></p>
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
                    <!-- <p>★★★☆☆ (<?= $p['rate']; ?>)</p> -->
                </div>
            </a>
            <?php } ?>
        </div>

        <?php if (count($semuaProduk) > count($produk)) { ?>
        <nav aria-label="Page navigation example">
            <ul class="pagination justify-content-center">
                <?php if ((int)$page > 1) { ?>
                <li class="page-item">
                    <a class="page-link text-dark"
                        href="/page/<?= $kategori ? ((int)$page - 1) . "/" . $kategori : ((int)$page - 1); ?>"
                        aria-label="Previous">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                </li>
                <?php }
                    $hitungGrupMax = ceil(count($semuaProduk) / 20);
                    for ($x = 1; $x <= $hitungGrupMax; $x++) {
                    ?>
                <li class="page-item"><a class="page-link text-dark"
                        href="/page/<?= $kategori ? $x . "/" . $kategori : $x; ?>"><?= $x; ?></a></li>
                <?php } ?>
                <?php if ((int)$page < $hitungGrupMax) { ?>
                <li class="page-item">
                    <a class="page-link text-dark"
                        href="/page/<?= $kategori ? ((int)$page + 1) . "/" . $kategori : ((int)$page + 1); ?>"
                        aria-label="Next">
                        <span aria-hidden="true">&raquo;</span>
                    </a>
                </li>
                <?php } ?>
            </ul>
        </nav>
        <?php } ?>
    </div>
</div>
<?= $this->endSection(); ?>