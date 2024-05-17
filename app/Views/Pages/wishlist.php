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
                    <a class="card1" href="/product/<?= urlencode($p['nama']); ?>">
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
        <?php } else { ?>
            <h5 class="text-center">Oops, keranjangmu masih kosong!</h5>
        <?php } ?>
    </div>
</div>
<?= $this->endSection(); ?>