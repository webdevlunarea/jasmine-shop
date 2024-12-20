<?= $this->extend('layout/template'); ?>
<?= $this->section('content'); ?>
<div class="konten">
    <div class="container">
        <div class="baris-ke-kolom-reverse">
            <div style="width: 30%;" class="show-ke-hide">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item"><b>Profileku</b></li>
                    <?php if (session()->get('role') == '0') { ?>
                        <li class="list-group-item"><a class="list" href="/transaction">Transaksi</a></li>
                        <li class="list-group-item"><a class="list" href="/point">Luna poin</a></li>
                        <li class="list-group-item"><a class="list" href="/voucher">Voucher</a></li>
                    <?php } ?>
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
                    <?php if ($msg) { ?>
                        <div class="alert alert-success" role="alert">
                            <?= $msg; ?>
                        </div>
                    <?php } ?>
                    <form class="row g-3" action="/account" method="post">
                        <div class="col-12">
                            <label for="inputPassword4" class="form-label">Sandi</label>
                            <input name="sandi" type="password" class="form-control" id="inputPassword4">
                        </div>
                        <?php if (session()->get('role') == '0') { ?>
                            <div class="col-12">
                                <label for="inputAddress" class="form-label">Nama Lengkap</label>
                                <input name="nama" type="text" class="form-control" placeholder="Nama Lengkap" value="<?= $nama ?>" required>
                            </div>
                            <div class="col-12">
                                <label for="inputAddress" class="form-label">Nomor Handphone</label>
                                <input name="nohp" type="number" class="form-control" placeholder="No HP" value="<?= $nohp ?>" required>
                            </div>
                            <div class="col-12">
                                <label for="inputAddress" class="form-label">Tanggal Lahir</label>
                                <input name="tgl_lahir" type="date" class="form-control" value="<?= $tgl_lahir ?>" required>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary1">Simpan</button>
                            </div>
                        <?php } ?>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>