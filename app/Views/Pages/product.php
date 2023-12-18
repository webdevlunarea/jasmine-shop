<?= $this->extend("layout/template"); ?>
<?= $this->section("content"); ?>
<div class="konten">
    <div class="container">
        <div class="baris-ke-kolom">
            <div class="img-produk limapuluh-ke-seratus">
                <img src="data:image/jpeg;base64,<?= base64_encode($gambar['gambar1']); ?>" alt=""
                    class="img-produk-prev">
                <div>
                    <?php foreach ($gambar as $key => $value) {
                        if($value && $key != 'id') {?>
                    <div class="img-produk-select <?= $key == 'gambar1' ? "selected" : "" ?>"><img
                            src="data:image/jpeg;base64,<?= base64_encode($value); ?>" alt=""></div>
                    <?php }} ?>
                </div>
            </div>
            <div class="limapuluh-ke-seratus">
                <h3><?= $produk['nama']; ?></h3>
                <?php if ($produk['diskon']) { ?>
                <p class="mb-0 harga d-inline">Rp
                    <?php
                        $persen = (100 - $produk['diskon']) / 100;
                        $hasil = $persen * $produk['harga'];
                        echo number_format($hasil, 0, ",", ".");
                        ?></p>
                <p class="mb-0 d-inline" style="text-decoration: line-through; font-size: small; color: grey;">Rp
                    <?= number_format($produk['harga'], 0, ",", "."); ?></p>
                <?php } else { ?>
                <p class="mb-0 harga">Rp <?= number_format($produk['harga'], 0, ",", "."); ?></p>
                <?php } ?>
                <p class="mb-0">★★★☆☆ (<?= $produk['rate']; ?>)</p>
                <p>Stok : <?= $produk['stok']; ?></p>
                <span class="garis mb-2"></span>
                <h5>Varian</h5>
                <div class="btn-group mb-3" id="varian-group" role="group" aria-label="Basic radio toggle button group">
                    <?php foreach ($varian as $key => $value) { ?>
                    <input type="radio" value="<?= $key ?>" class="btn-check" name="btnradio" autocomplete="off"
                        id="btnradio<?= $key ?>" <?= $key == 0 ? "checked" : "" ?>>
                    <label class="btn btn-outline-danger" for="btnradio<?= $key ?>"><?= $value ?></label>
                    <?php } ?>
                </div>
                <h5>Dimensi</h5>
                <p><?= $dimensi[0]." x ".$dimensi[1]." x ".$dimensi[2]; ?></p>
                <h5>Deskripsi</h5>
                <p><?= $produk['deskripsi']; ?></p>
                <?php if (session()->get('isLogin')) { ?>
                <?php if (session()->get('role') == 0) { ?>
                <a class="btn btn-danger" href="/addcart/<?= $produk['id']; ?>">Beli Sekarang</a>
                <?php if (in_array($produk['id'], session()->get('wishlist'))) { ?>
                <a class="btn btn-outline-dark" href="/delwishlist/<?= $produk['id']; ?>"><i
                        class="material-icons">favorite</i></a>
                <?php } else { ?>
                <a class="btn btn-outline-dark" href="/addwishlist/<?= $produk['id']; ?>"><i
                        class="material-icons">favorite_border</i></a>
                <?php } ?>
                <?php } else { ?>
                <a class="btn btn-danger" href="/editproduct/<?= $produk['id']; ?>">Edit produk</a>
                <?php } ?>
                <?php } else { ?>
                <a class="btn btn-danger" href="/login">Masuk untuk membeli</a>
                <?php } ?>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>