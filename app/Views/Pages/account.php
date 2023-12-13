<?= $this->extend('layout/template'); ?>
<?= $this->section('content'); ?>
<div class="konten">
    <div class="container">
        <div class="baris-ke-kolom-reverse">
            <div style="width: 30%;" class="show-ke-hide">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">Profileku</li>
                    <li class="list-group-item">Pilih Pembayaran</li>
                    <li class="list-group-item">Pesananku</li>
                    <li class="list-group-item">Wishlist</li>
                    <li class="list-group-item"><a class="btn btn-outline-danger" href="/keluar">Keluar</a></li>
                </ul>
            </div>
            <div class="hide-ke-show-flex w-100 justify-content-center border-top pt-3 mt-2">
                <a class="btn btn-outline-danger" style="width: fit-content;" href="/keluar">Keluar</a>
            </div>
            <div>
                <div class="p-2">
                    <h3>Edit Profilemu</h3>
                    <p>Email : <?= session()->get('email') ?> </p>
                    <form class="row g-3" action="/account" method="post">
                        <div class="col-12">
                            <label for="inputPassword4" class="form-label">Sandi</label>
                            <input name="sandi" type="password" class="form-control" id="inputPassword4">
                        </div>
                        <?php if (session()->get('alamat')) { ?>
                            <div class="col-12">
                                <label for="inputAddress" class="form-label">Alamat</label>
                                <input name="alamat" type="text" class="form-control" id="inputAddress" placeholder="alamat" value="<?= session()->get("alamat") ?>" required>
                            </div>
                        <?php } ?>
                        <div class="col-12">
                            <button type="submit" class="btn btn-danger">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>