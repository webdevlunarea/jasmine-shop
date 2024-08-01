<?= $this->extend('layout/template'); ?>
<?= $this->section('content'); ?>
<div class="konten">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h5 class="jdl-section">List Produk</h5>
            <a href="/addproduct" class="btn btn-primary1 d-flex gap-2" style="width: fit-content;"><i class="material-icons">add</i>
                <p class="mb-0">Tambah Produk</p>
            </a>
        </div>
        <form action="/findproductadmin" method="post">
            <div class="mb-2 d-flex gap-3 align-items-center">
                <p class="m-0" style="width: 150px">Cari barang</p>
                <input type="text" class="form-control" name="cari">
            </div>
        </form>
        <div class="show-flex-ke-hide flex-column">
            <?php foreach ($produk as $ind_p => $p) { ?>
                <div class="w-100 gap-2 d-flex">
                    <div style="flex: 1" class="d-flex align-items-center">
                        <img src="data:image/webp;base64,<?= base64_encode($p['gambar']); ?>" style="aspect-ratio: 1/1; width: 50px; border-radius: 10px">
                    </div>
                    <div style="flex: 4" class="d-flex flex-column justify-content-center">
                        <p class="m-0 fw-bold"><?= $p['nama']; ?></p>
                        <p class="m-0 text-secondary"><?= $p['id']; ?></p>
                    </div>
                    <div style="flex: 2" class="d-flex flex-column justify-content-center">
                        <?php foreach (json_decode($p['varian'], true) as $ind_v => $v) { ?>
                            <p class="m-0"><?= $v; ?> : <?= isset(explode(',', $p['stok'])[$ind_v]) ? explode(',', $p['stok'])[$ind_v] : $p['stok']; ?></p>
                        <?php } ?>
                    </div>
                    <div style="flex: 1" class="d-flex justify-content-center align-items-center gap-1">
                        <label for="checkbox_active<?= $ind_p; ?>">Active </label>
                        <input type="checkbox" id="checkbox_active<?= $ind_p; ?>">
                    </div>
                    <div style="flex: 2" class="d-flex gap-1 justify-content-end align-items-center">
                        <a class="btn btn-light d-flex" href="/product/<?= $p['path']; ?>"><i class="material-icons">visibility</i></a>
                        <a class="btn btn-light d-flex" href="/editproduct/<?= $p['id']; ?>"><i class="material-icons">edit</i></a>
                        <button class="btn btn-light d-flex" onclick="triggerToast('Produk <?= $p['nama']; ?> akan dihapus?','/delproduct/<?= $p['id']; ?>')"><i class="material-icons">delete_forever</i></button>
                    </div>
                </div>
                <hr>
            <?php } ?>
        </div>
        <div class="hide-ke-show-flex flex-column">
            <?php foreach ($produk as $ind_p => $p) { ?>
                <div class="w-100">
                    <div class="d-flex gap-3 mb-2">
                        <img src="data:image/webp;base64,<?= base64_encode($p['gambar']); ?>" style="aspect-ratio: 1/1; width: 50px; border-radius: 10px">
                        <div>
                            <p class="mt-2 mb-0 fw-bold"><?= $p['nama']; ?></p>
                            <p class="mt-0 mb-2 text-secondary"><?= $p['id']; ?></p>
                        </div>
                    </div>
                    <?php foreach (json_decode($p['varian'], true) as $ind_v => $v) { ?>
                        <div class="d-flex">
                            <p class="m-0 w-50"><?= $v; ?></p>
                            <p class="m-0 w-50 text-end"><?= isset(explode(',', $p['stok'])[$ind_v]) ? explode(',', $p['stok'])[$ind_v] : $p['stok']; ?></p>
                        </div>
                    <?php } ?>
                    <div class="d-flex mt-2">
                        <div style="flex: 1" class="d-flex align-items-center gap-2">
                            <label for="checkbox_active<?= $ind_p; ?>">
                                <p class="m-0">Active </p>
                            </label>
                            <input type="checkbox" id="checkbox_active<?= $ind_p; ?>">
                        </div>
                        <div style="flex: 2" class="d-flex gap-1 justify-content-end align-items-center">
                            <a class="btn btn-light d-flex" href="/product/<?= $p['path']; ?>"><i class="material-icons">visibility</i></a>
                            <a class="btn btn-light d-flex" href="/editproduct/<?= $p['id']; ?>"><i class="material-icons">edit</i></a>
                            <button class="btn btn-light d-flex" onclick="triggerToast('Produk <?= $p['nama']; ?> akan dihapus?','/delproduct/<?= $p['id']; ?>')"><i class="material-icons">delete_forever</i></button>
                        </div>
                    </div>
                </div>
                <hr>
            <?php } ?>
        </div>
        <div class="card-group1 no-scroll">
            <?php foreach ($produk as $p) { ?>
                <div class="card1">
                    <?php if ($p['diskon']) { ?>
                        <p class="diskon">-<?= $p['diskon']; ?>%</p>
                    <?php } ?>
                    <!-- <img src="data:image/webp;base64,<?= base64_encode($p['gambar']); ?>" alt=""> -->
                    <div style="position: relative; width: 100%; aspect-ratio: 1 / 1;">
                        <img class="img-card1-wm" src="<?= base_url('img/WM Black 300.webp'); ?>" alt="">
                        <img class="img-card1" src="data:image/webp;base64,<?= base64_encode($p['gambar']); ?>" alt="">
                    </div>
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
                        <a class="btn btn-light d-flex" href="/product/<?= urlencode($p['nama']); ?>"><i class="material-icons">visibility</i></a>
                        <a class="btn btn-light d-flex" href="/editproduct/<?= $p['id']; ?>"><i class="material-icons">edit</i></a>
                        <button class="btn btn-light d-flex" onclick="triggerToast('Produk <?= $p['nama']; ?> akan dihapus?','/delproduct/<?= $p['id']; ?>')"><i class="material-icons">delete_forever</i></button>
                    </div>
                </div>
            <?php } ?>
        </div>

        <?php if (count($semuaProduk) > count($produk)) { ?>
            <nav aria-label="Page navigation example">
                <ul class="pagination justify-content-center">
                    <?php if ((int)$page > 1) { ?>
                        <li class="page-item">
                            <a class="page-link text-dark" href="<?= $cari ? '/findproductadmin/' . $cari . '/' . ((int)$page - 1) : '/listproduct/' . ((int)$page - 1); ?>" aria-label="Previous">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
                    <?php }
                    $hitungGrupMax = ceil(count($semuaProduk) / 20);
                    for ($x = 1; $x <= $hitungGrupMax; $x++) {
                    ?>
                        <li class="page-item"><a class="page-link text-dark" href="<?= $cari ? '/findproductadmin/' . $cari . '/' . $x : '/listproduct/' . $x; ?>"><?= $x; ?></a></li>
                    <?php } ?>
                    <?php if ((int)$page < $hitungGrupMax) { ?>
                        <li class="page-item">
                            <a class="page-link text-dark" href="<?= $cari ? '/findproductadmin/' . $cari . '/' . ((int)$page + 1) : '/listproduct/' . ((int)$page + 1); ?>" aria-label="Next">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                    <?php } ?>
                </ul>
            </nav>
        <?php } ?>
    </div>
</div>
<?= $this->endSection(); ?>