<?= $this->extend("layout/template"); ?>
<?= $this->section("content"); ?>
<div class="konten">
    <div class="container">
        <div class="baris-ke-kolom">
            <div class="img-produk limapuluh-ke-seratus">
                <img src="data:image/jpeg;base64,<?= base64_encode($gambar['gambar1']); ?>" alt="" class="img-produk-prev">
                <div>
                    <?php foreach ($gambar as $key => $value) {
                        if ($value && $key != 'id') { ?>
                            <div class="img-produk-select <?= $key == 'gambar1' ? "selected" : "" ?>"><img src="data:image/jpeg;base64,<?= base64_encode($value); ?>" alt=""></div>
                    <?php }
                    } ?>
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
                        <input type="radio" value="<?= $key ?>" class="btn-check" name="btnradio" autocomplete="off" id="btnradio<?= $key ?>" <?= $key == 0 ? "checked" : "" ?>>
                        <label class="btn btn-outline-danger" for="btnradio<?= $key ?>"><?= $value ?></label>
                    <?php } ?>
                </div>
                <h5>Dimensi</h5>
                <p><?= $dimensi[0] . " cm x " . $dimensi[1] . " cm x " . $dimensi[2] . " cm"; ?></p>
                <h5>Deskripsi</h5>
                <p><?= $produk['deskripsi']; ?></p>
                <?php if (session()->get('isLogin')) { ?>
                    <?php if (session()->get('role') == 0) { ?>
                        <?php if (session()->get('active') == '1') { ?>
                            <a class="btn btn-danger" href="/addcart/<?= $produk['id']; ?>" id="btn-beli-product">Beli Sekarang</a>
                            <?php if (in_array($produk['id'], session()->get('wishlist'))) { ?>
                                <a class="btn btn-outline-dark" href="/delwishlist/<?= $produk['id']; ?>"><i class="material-icons">favorite</i></a>
                            <?php } else { ?>
                                <a class="btn btn-outline-dark" href="/addwishlist/<?= $produk['id']; ?>"><i class="material-icons">favorite_border</i></a>
                            <?php } ?>
                        <?php } else { ?>
                            <a class="btn btn-danger" href="/verify">Verifikasi Email</a>
                        <?php } ?>
                    <?php } else { ?>
                        <a class="btn btn-danger" href="/editproduct/<?= $produk['id']; ?>">Edit produk</a>
                    <?php } ?>
                <?php } else { ?>
                    <a class="btn btn-danger" href="/login">Masuk untuk membeli</a>
                <?php } ?>

                <div class="mt-2">
                    <p class="mb-1">
                        Produk ini juga tersedia di
                    </p>
                    <div>
                        <a href="#" title="Tokopedia" target="blank"><img src="/img/logo/tokopedia.png" class="marketplace">
                        </a>
                        <a href="#" title="Shopee" target="blank" style=""><img src="/img/logo/shopee.png" class="marketplace">
                        </a>
                    </div>
                </div>


            </div>

        </div>
    </div>

    <div class="container my-5">
        <h5 class="jdl-section">Produk serupa</h5>
        <div class="card-group1 no-scroll">
            <?php foreach ($produksekategori as $p) { ?>
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
    </div>
</div>
<script>
    const elmVarianSelect = document.querySelectorAll(".btn-check")
    const imgProdukSelect = document.querySelectorAll(".img-produk-select")
    const imgProdukPrev = document.querySelector(".img-produk-prev")
    const elmVarian = document.getElementById('varian-group')
    const elmBtnBeli = document.getElementById('btn-beli-product')
    const jmlVarian = "<?= $produk['jml_varian'] ?>";
    const idProduk = "<?= $produk['id'] ?>";

    if (imgProdukSelect.length > 0) {
        imgProdukSelect.forEach((element, index) => {
            element.addEventListener("click", () => {
                imgProdukSelect.forEach(e => e.classList.remove("selected"));
                element.classList.add("selected");
                imgProdukPrev.src = element.childNodes[0].src;
                const hitungBagi4 = Math.floor(index / Number(jmlVarian));
                elmVarianSelect.forEach(e => e.checked = false);
                elmVarianSelect[hitungBagi4].checked = true
                setUrlElmBeli()
            })
        });
    }
    elmVarian.addEventListener("change", (e) => {
        imgProdukSelect.forEach(e => e.classList.remove("selected"));
        imgProdukSelect[Number(e.target.value) * Number(jmlVarian)].classList.add("selected");
        imgProdukPrev.src = imgProdukSelect[Number(e.target.value) * Number(jmlVarian)].childNodes[0].src
        setUrlElmBeli()
    });

    function setUrlElmBeli() {
        let elmSelected;
        elmVarianSelect.forEach((e) => {
            if (e.checked) elmSelected = e.value
        })
        const varians = "<?php
                            foreach ($varian as $i => $v) {
                                echo $v;
                                if ($i < (count($varian) - 1)) echo ",";
                            }
                            ?>";
        const varianArray = varians.split(",")
        const indexGambar = Number(elmSelected) * Number(jmlVarian)
        elmBtnBeli.href = "/addcart/" + idProduk + "/" + varianArray[Number(elmSelected)] + "/" + indexGambar;
    }
    setUrlElmBeli()
</script>
<?= $this->endSection(); ?>