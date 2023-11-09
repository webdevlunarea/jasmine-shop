<?= $this->extend("layout/template"); ?>
<?= $this->section("content"); ?>
<div class="konten">
    <div class="container">
        <div class="row">
            <div class="col">
                <img src="data:image/jpeg;base64,<?= base64_encode($produk['gambar']); ?>" alt="" class="img-produk">
            </div>
            <div class="col">
                <h3><?= $produk['nama']; ?></h3>
                <?php if ($produk['diskon']) { ?>
                    <p class="mb-0 harga d-inline">Rp
                        <?php
                        $persen = (100 - $produk['diskon']) / 100;
                        $hasil = $persen * $produk['harga'];
                        echo number_format($hasil, 0, ",", ".");
                        ?></p>
                    <p class="mb-0 d-inline" style="text-decoration: line-through; font-size: small; color: grey;">Rp <?= number_format($produk['harga'], 0, ",", "."); ?></p>
                <?php } else { ?>
                    <p class="mb-0 harga">Rp <?= number_format($produk['harga'], 0, ",", "."); ?></p>
                <?php } ?>
                <p>★★★☆☆ (<?= $produk['rate']; ?>)</p>
                <p>Stok : <?= $produk['stok']; ?></p>
                <p><?= $produk['deskripsi']; ?></p>
                <?php if (session()->get('isLogin')) { ?>
                    <a class="btn btn-danger" href="/addcart/<?= $produk['id']; ?>">Beli Sekarang</a>
                    <?php if (in_array($produk['id'], session()->get('wishlist'))) { ?>
                        <a class="btn btn-outline-dark" href="/delwishlist/<?= $produk['id']; ?>"><i class="material-icons">favorite</i></a>
                    <?php } else { ?>
                        <a class="btn btn-outline-dark" href="/addwishlist/<?= $produk['id']; ?>"><i class="material-icons">favorite_border</i></a>
                    <?php } ?>
                <?php } else { ?>
                    <a class="btn btn-danger" href="/login">Masuk untuk membeli</a>
                <?php } ?>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>