<?= $this->extend('layout/template'); ?>
<?= $this->section('content'); ?>
<div class="konten">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <p class="mb-0">Wishlist (4)</p>
            <a class="btn btn-outline-dark" href="/wishlisttocart">Beli Semua</a>
        </div>
        <?php if (count($wishlist) > 0) { ?>
            <div class="card-group1 no-scroll">
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
                        </div>
                    </a>
                <?php } ?>
            </div>
        <?php } else { ?>
            <h5 class="text-center">Oops, keranjangmu masih kosong!</h5>
        <?php } ?>
    </div>
</div>
<?= $this->endSection(); ?>