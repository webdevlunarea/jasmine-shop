<?= $this->extend('layout/template'); ?>
<?= $this->section('content'); ?>
<div class="konten">
    <div class="container">
        <h5 class="jdl-section">Kategori</h5>
        <div class="d-flex justify-content-between mb-3">
            <h1 class="mb-0">Cari Berdasarkan Kategori</h1>
            <div>
                <button class="btn btn-icon scroll-kategori"><i class="material-icons">arrow_back</i></button>
                <button class="btn btn-icon scroll-kategori"><i class="material-icons">arrow_forward</i></button>
            </div>
        </div>
        <div class="container-kategori">
            <a class="kategori" href="/all/elektronik">
                <i class="material-icons">phone_android</i>
                <p>Elektronik</p>
            </a>
            <a class="kategori" href="/all/mebel">
                <i class="material-icons">event_seat</i>
                <p>Mebel</p>
            </a>
            <a class="kategori" href="/all/tanaman">
                <i class="material-icons">spa</i>
                <p>Tanaman</p>
            </a>
            <a class="kategori" href="/all/fashion">
                <i class="material-icons">loyalty</i>
                <p>Fashion</p>
            </a>
            <a class="kategori" href="/all/kesehatan">
                <i class="material-icons">healing</i>
                <p>Kesehatan</p>
            </a>
            <a class="kategori" href="/all/pangan">
                <i class="material-icons">local_dining</i>
                <p>Pangan</p>
            </a>
            <a class="kategori" href="/all/olahraga">
                <i class="material-icons">fitness_center</i>
                <p>Olahraga</p>
            </a>
            <a class="kategori" href="/all/bangunan">
                <i class="material-icons">format_paint</i>
                <p>Bangunan</p>
            </a>
            <a class="kategori" href="/all/peliharaan">
                <i class="material-icons">pets</i>
                <p>Peliharaan</p>
            </a>
            <a class="kategori" href="/all/kendaraan">
                <i class="material-icons">motorcycle</i>
                <p>Kendaraan</p>
            </a>
        </div>
    </div>
    <div class="container my-5">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h5 class="jdl-section mb-3"><?= $kategori ? ucfirst($kategori) : "Semua Kategori"; ?></h5>
            <?php if ($kategori) { ?>
                <a class="btn btn-danger" href="/all">Semua Kategori</a>
            <?php } ?>
        </div>
        <div class="card-group1 no-scroll">
            <?php foreach ($produk as $p) { ?>
                <a class="card1" href="/product/<?= $p['id']; ?>">
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
                            <p class="mb-0 d-inline" style="text-decoration: line-through; font-size: small; color: grey;">Rp <?= number_format($p['harga'], 0, ",", "."); ?></p>
                        <?php } else { ?>
                            <p class="mb-0 harga">Rp <?= number_format($p['harga'], 0, ",", "."); ?></p>
                        <?php } ?>
                        <p>★★★☆☆ (<?= $p['rate']; ?>)</p>
                    </div>
                    <?php if ($p['diskon']) { ?>
                        <p class="diskon">-<?= $p['diskon']; ?>%</p>
                    <?php } ?>
                </a>
            <?php } ?>
        </div>
    </div>
    <div class="container">
        <div class="row text-center">
            <div class="col">
                <div style="background-color: black; color: white; border-radius: 2em; width:fit-content; padding: 0.8em 1em; margin-inline: auto; margin-bottom: 1em;"><i class="material-icons">local_shipping</i></div>
                <h5>ANTAR GRATIS DAN CEPAT</h5>
                <p>Gratis pengantaran untuk semua orderan yang lebih dari Rp 50.000</p>
            </div>
            <div class="col">
                <div style="background-color: black; color: white; border-radius: 2em; width:fit-content; padding: 0.8em 1em; margin-inline: auto; margin-bottom: 1em;"><i class="material-icons">headset_mic</i></div>
                <h5>24/7 CUSTOMER SERVICE</h5>
                <p>Friendly 24/7 layanan pelanggan</p>
            </div>
            <div class="col">
                <div style="background-color: black; color: white; border-radius: 2em; width:fit-content; padding: 0.8em 1em; margin-inline: auto; margin-bottom: 1em;"><i class="material-icons">monetization_on</i></div>
                <h5>GARANSI UANG KEMBALI</h5>
                <p>Garansi yang kami berikan selama 30 hari</p>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>