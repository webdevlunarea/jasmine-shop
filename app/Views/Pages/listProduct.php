<?= $this->extend('layout/template'); ?>
<?= $this->section('content'); ?>
<div class="konten">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h5 class="jdl-section">List Produk</h5>
            <a href="/addproduct" class="btn btn-danger d-flex gap-2" style="width: fit-content;"><i class="material-icons">add</i>
                <p class="mb-0">Tambah Produk</p>
            </a>
        </div>
        <div class="card-group1 no-scroll">
            <?php foreach ($produk as $p) { ?>
                <div class="card1">
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
                            <p class="mb-0 d-inline" style="text-decoration: line-through; font-size: small; color: grey;">Rp <?= number_format($p['harga'], 0, ",", "."); ?></p>
                        <?php } else { ?>
                            <p class="mb-0 harga">Rp <?= number_format($p['harga'], 0, ",", "."); ?></p>
                        <?php } ?>
                        <p>★★★☆☆ (<?= $p['rate']; ?>)</p>
                    </div>
                    <div class="d-flex gap-2 justify-content-center">
                        <a class="btn btn-light d-flex" href="/product/<?= $p['id']; ?>"><i class="material-icons">visibility</i></a>
                        <a class="btn btn-light d-flex" href="/editproduct/<?= $p['id']; ?>"><i class="material-icons">edit</i></a>
                        <button class="btn btn-light d-flex" onclick="triggerToast('Produk <?= $p['nama']; ?> akan dihapus?','/delproduct/<?= $p['id']; ?>')"><i class="material-icons">delete_forever</i></button>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>