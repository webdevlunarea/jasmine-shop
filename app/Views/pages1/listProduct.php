<?= $this->extend('layout/template'); ?>
<?= $this->section('content'); ?>
<div class="konten">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h5 class="jdl-section">List Produk</h5>
            <div class="d-flex gap-1">
                <a href="/listproducttable" class="btn btn-outline-dark d-flex gap-2" style="width: fit-content;">
                    <p class="mb-0">Eksport</p>
                </a>
                <a href="/addproduct" class="btn btn-primary1 d-flex gap-2" style="width: fit-content;"><i class="material-icons">add</i>
                    <p class="mb-0">Tambah Produk</p>
                </a>
            </div>
        </div>
        <form action="/findproductadmin" method="post">
            <div class="mb-2 d-flex gap-3 align-items-center">
                <p class="m-0" style="width: 150px">Cari barang</p>
                <input type="text" class="form-control" name="cari" value="<?= $cari ? str_replace('-', ' ', $cari) : ''; ?>">
            </div>
        </form>
        <div class="show-flex-ke-hide flex-column">
            <div class="w-100 gap-2 d-flex py-2 border-bottom mb-3 fw-bold">
                <div style="flex: 1" class="d-flex align-items-center">Gambar</div>
                <div style="flex: 4" class="d-flex flex-column justify-content-center">Nama dan ID</div>
                <div style="flex: 2" class="d-flex flex-column justify-content-center">Stok</div>
                <div style="flex: 1;" class="d-flex justify-content-center align-items-center">Active</div>
                <div style="flex: 2" class="d-flex gap-1 justify-content-end align-items-center">Action</div>
            </div>
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
                    <div style="flex: 1;" class="d-flex justify-content-center align-items-center">
                        <div class="bg-light border border-dark rounded-5 p-1 d-flex justify-content-<?= $p['active'] ? 'end' : 'start' ?>" style="width: 60px; height: 20px; cursor:pointer;" onclick="triggerToast('Produk <?= $p['nama']; ?> akan di<?= $p['active'] ? 'non aktifkan' : 'aktifkan'; ?>?', '/activeproduct/<?= $p['id']; ?>')">
                            <div class="bg-<?= $p['active'] ? 'success' : 'danger' ?> rounded-2" style="width: 30px; height: 90%"></div>
                        </div>
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
                        <div style="flex: 1;" class="d-flex justify-content-center align-items-center">
                            <div class="bg-light border border-dark rounded-5 p-1 d-flex justify-content-<?= $p['active'] ? 'end' : 'start' ?>" style="width: 60px; height: 20px; cursor:pointer;" onclick="triggerToast('Produk <?= $p['nama']; ?> akan di<?= $p['active'] ? 'non aktifkan' : 'aktifkan'; ?>?', '/activeproduct/<?= $p['id']; ?>')">
                                <div class="bg-<?= $p['active'] ? 'success' : 'danger' ?> rounded-2" style="width: 30px; height: 90%"></div>
                            </div>
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
                        <li class="page-item"><a class="page-link <?= $x == $page ? "aktif" : "" ?>" href="<?= $cari ? '/findproductadmin/' . $cari . '/' . $x : '/listproduct/' . $x; ?>"><?= $x; ?></a></li>
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