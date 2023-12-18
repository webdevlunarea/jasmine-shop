<?= $this->extend('layout/template'); ?>
<?= $this->section('content'); ?>
<div class="konten">
    <div class="container">
        <h5 class="jdl-section">Kategori</h5>
        <h1 class="mb-3">Cari Berdasarkan Kategori</h1>
        <!-- <div class="d-flex justify-content-between mb-3">
            <div class="d-flex justify-content-end gap-1">
                <button class="btn btn-icon scroll-kategori show-ke-hide"><i
                        class="material-icons">arrow_back</i></button>
                <button class="btn btn-icon scroll-kategori show-ke-hide"><i
                        class="material-icons">arrow_forward</i></button>
            </div>
        </div> -->
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
        <?php if ($nama) { ?>
            <h5 class="mb-3">Anda mencari "<?= $nama ?>"</h5>
        <?php } else { ?>
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h5 class="jdl-section mb-3"><?= $kategori ? ucfirst($kategori) : "Semua Kategori"; ?></h5>
                <?php if ($kategori) { ?>
                    <a class="btn btn-danger" href="/all">Semua Kategori</a>
                <?php } ?>
            </div>
        <?php } ?>

        <div class="card-group1 no-scroll">
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
                        <!-- <p>★★★☆☆ (<?= $p['rate']; ?>)</p> -->
                    </div>
                </a>
            <?php } ?>
        </div>

        <nav aria-label="Page navigation example">
            <ul class="pagination justify-content-center">
                <li class="page-item">
                    <a class="page-link text-dark" href="#" aria-label="Previous">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                </li>
                <li class="page-item"><a class="page-link text-dark" href="#">1</a></li>
                <li class="page-item"><a class="page-link text-dark" href="#">2</a></li>
                <li class="page-item"><a class="page-link text-dark" href="#">3</a></li>
                <li class="page-item">
                    <a class="page-link text-dark" href="#" aria-label="Next">
                        <span aria-hidden="true">&raquo;</span>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
    <span class="garis my-5 container"></span>
    <div class="container baris-ke-kolom">
        <div class="limapuluh-ke-seratus">
            <h3>Metode Pembayaran</h3>
            <img class="logo-pembayaran" src="/img/logo/bca.webp" alt="">
            <img class="logo-pembayaran" src="/img/logo/bni.webp" alt="">
            <img class="logo-pembayaran" src="/img/logo/bri.webp" alt="">
            <img class="logo-pembayaran" src="/img/logo/mandiri.webp" alt="">
            <img class="logo-pembayaran" src="/img/logo/permata.webp" alt="">
            <img class="logo-pembayaran" src="/img/logo/cbbank.webp" alt="">
        </div>
        <div class="limapuluh-ke-seratus">
            <h3>Metode Pengiriman</h3>
            <img class="logo-pembayaran" src="/img/logo/jnt.webp" alt="">
            <img class="logo-pembayaran" src="/img/logo/jne.webp" alt="">
        </div>
    </div>
    <span class="garis  container my-5"></span>
</div>
<?= $this->endSection(); ?>