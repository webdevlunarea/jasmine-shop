<?= $this->extend('layout/template'); ?>
<?= $this->section('content'); ?>
<div class="konten">
    <div class="container">
        <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/">Beranda</a></li>
                <li class="breadcrumb-item" aria-current="page">Produk</li>
                <?php if ($nama) { ?>
                    <li class="breadcrumb-item active"><?= $nama ?></li>
                <?php } else { ?>
                    <?php if ($kategori) { ?>
                        <li class="breadcrumb-item active"><?= str_replace('-', ' ', ucfirst($kategori)) ?></li>
                    <?php } else { ?>
                        <li class="breadcrumb-item active">All</li>
                    <?php } ?>
                <?php } ?>
            </ol>
        </nav>
        <h5 class="jdl-section">Kategori</h5>
        <h1 class="mb-3">Kategori Produk</h1>
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
                    <img src="/img/logokategori/Meja_Belajar.webp" alt="meja-belajar" width="50px">
                    <p>Meja Belajar</p>
                </a>
                <a class="kategori" href="/all/meja-tv">
                    <img src="/img/logokategori/Meja_TV.webp" alt="meja-tv" width="50px">
                    <p>Meja TV</p>
                </a>
                <a class="kategori" href="/all/meja-tulis">
                    <img src="/img/logokategori/Meja_Tulis.webp" alt="meja-tulis" width="50px">
                    <p>Meja Tulis</p>
                </a>
                <a class="kategori" href="/all/meja-komputer">
                    <img src="/img/logokategori/Meja_Komputer.webp" alt="meja-komputer" width="50px">
                    <p>Meja Komputer</p>
                </a>
                <a class="kategori" href="/all/rak-sepatu">
                    <img src="/img/logokategori/Rak_Sepatu.webp" alt="rak-sepatu" width="50px">
                    <p>Rak Sepatu</p>
                </a>
                <a class="kategori" href="/all/rak-besi">
                    <img src="/img/logokategori/Rak_Besi.webp" alt="rak-besi" width="50px">
                    <p>Rak Besi</p>
                </a>
                <a class="kategori" href="/all/rak-serbaguna">
                    <img src="/img/logokategori/Rak_Serbaguna.webp" alt="rak-serbaguna" width="50px">
                    <p>Rak Serbaguna</p>
                </a>
                <a class="kategori" href="/all/kursi">
                    <img src="/img/logokategori/Kursi.webp" alt="kursi" width="50px">
                    <p>Kursi</p>
                </a>
            </div>
        </div>
    </div>
    <div class="container my-3">
        <?php if ($nama) { ?>
            <h5 class="mb-3">Anda mencari "<?= $nama ?>"</h5>
        <?php } else { ?>
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="jdl-section">
                    <?= $kategori ? str_replace('-', ' ', ucfirst($kategori)) : "Semua Kategori"; ?>
                </h5>
                <?php if ($kategori) { ?>
                    <a class="btn btn-primary1" href="/all">Semua Kategori</a>
                <?php } ?>
            </div>
        <?php } ?>

        <?php if (count($produk) <= 0) { ?>
            <p class="m-0 text-center">Barang tidak ditemukan</p>
        <?php } else { ?>
            <div class="card-group1 no-scroll mb-3">
                <?php foreach ($produk as $p) { ?>
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
        <?php } ?>

        <?php if (count($semuaProduk) > count($produk)) { ?>
            <nav aria-label="Page navigation example">
                <?php if ($nama) { ?>
                    <ul class="pagination justify-content-center">
                        <?php if ((int)$page > 1) { ?>
                            <li class="page-item">
                                <a class="page-link text-dark" href="/find/<?= str_replace(" ", "-", $nama); ?>/<?= (int)$page - 1; ?>" aria-label="Previous">
                                    <span aria-hidden="true">&laquo;</span>
                                </a>
                            </li>
                        <?php }
                        $hitungGrupMax = ceil(count($semuaProduk) / 20);
                        for ($x = 1; $x <= $hitungGrupMax; $x++) {
                        ?>
                            <li class="page-item"><a class="page-link <?= $x == $page ? "aktif" : "" ?>" href="/find/<?= str_replace(" ", "-", $nama); ?>/<?= $x; ?>"><?= $x; ?></a></li>
                        <?php } ?>
                        <?php if ((int)$page < $hitungGrupMax) { ?>
                            <li class="page-item">
                                <a class="page-link text-dark" href="/find/<?= str_replace(" ", "-", $nama); ?>/<?= (int)$page + 1; ?>" aria-label="Next">
                                    <span aria-hidden="true">&raquo;</span>
                                </a>
                            </li>
                        <?php } ?>
                    </ul>
                <?php } else { ?>
                    <ul class="pagination justify-content-center">
                        <?php if ((int)$page > 1) { ?>
                            <li class="page-item">
                                <a class="page-link text-dark" href="/page/<?= $kategori ? ((int)$page - 1) . "/" . $kategori : ((int)$page - 1); ?>" aria-label="Previous">
                                    <span aria-hidden="true">&laquo;</span>
                                </a>
                            </li>
                        <?php }
                        $hitungGrupMax = ceil(count($semuaProduk) / 20);
                        for ($x = 1; $x <= $hitungGrupMax; $x++) {
                        ?>
                            <li class="page-item"><a class="page-link <?= $x == $page ? "aktif" : "" ?>" href="/page/<?= $kategori ? $x . "/" . $kategori : $x; ?>"><?= $x; ?></a></li>
                        <?php } ?>
                        <?php if ((int)$page < $hitungGrupMax) { ?>
                            <li class="page-item">
                                <a class="page-link text-dark" href="/page/<?= $kategori ? ((int)$page + 1) . "/" . $kategori : ((int)$page + 1); ?>" aria-label="Next">
                                    <span aria-hidden="true">&raquo;</span>
                                </a>
                            </li>
                        <?php } ?>
                    </ul>
                <?php } ?>
            </nav>
        <?php } ?>
    </div>
</div>
<?= $this->endSection(); ?>