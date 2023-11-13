<?= $this->extend('layout/template'); ?>
<?= $this->section('content'); ?>
<div class="konten">
    <div class="container">
        <h5 class="jdl-section">List Produk</h5>
        <div>
            <?php foreach ($produk as $p) { ?>
                <div class="d-flex gap-3">
                    <a class="card2" href="/product/<?= $p['id']; ?>">
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
                        <div>
                            <a class="btn btn-danger"><i class="material-icons">visibility</i></a>
                            <a class="btn btn-danger"><i class="material-icons">edit</i></a>
                            <a class="btn btn-danger"><i class="material-icons">delete_forever</i></a>
                        </div>
                    </a>
                </div>
            <?php } ?>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>