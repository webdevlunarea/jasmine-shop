<?= $this->extend('layout/template'); ?>
<?= $this->section('content'); ?>
<style>
    .icon-btn-change-pp {
        background-color: whitesmoke;
        color: var(--hijau);
        padding: 10px;
        position: absolute;
        transform: translate(70px, 70px);
        border-radius: 100px;
        cursor: pointer;
    }

    .icon-btn-change-pp:hover {
        background-color: var(--hijau);
        color: white;
    }
</style>
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
                    <li class="list-group-item"><a class="btn btn-outline-danger" href="/hapuslocalstorage/<?= base64_encode('/keluar'); ?>">Keluar</a></li>
                </ul>
            </div>
            <div class="hide-ke-show-flex w-100 justify-content-center border-top pt-3 mt-2">
                <a class="btn btn-outline-danger" style="width: fit-content;" href="/hapuslocalstorage/<?= base64_encode('/keluar'); ?>">Keluar</a>
            </div>
            <div>
                <div class="p-2">
                    <h3>Edit Profilemu</h3>
                    <p>Email : <?= session()->get('email') ?></p>
                    <?php if ($msg) { ?>
                        <div class="alert alert-success" role="alert">
                            <?= $msg; ?>
                        </div>
                    <?php } ?>
                    <?php if (session()->get('active')) { ?>
                        <form class="row g-3" action="/account" method="post" enctype="multipart/form-data">
                            <div class="mb-1 d-flex justify-content-center align-items-center">
                                <label for="input-file" class="icon-btn-change-pp"><i class="material-icons">edit</i></label>
                                <input class="d-none" name="foto" id="input-file" type="file">
                                <img src="<?= $foto; ?>" alt="" id="prev-file" style="width: 200px;  height: 200px; object-fit:cover; border-radius: 400px;">
                            </div>
                            <div class=" mb-1">
                                <label for="inputPassword4" class="form-label">Sandi</label>
                                <input name="sandi" type="password" class="form-control" id="inputPassword4">
                            </div>
                            <?php if (session()->get('role') == '0') { ?>
                                <div class="mb-1">
                                    <label for="inputAddress" class="form-label">Nama Lengkap</label>
                                    <input name="nama" type="text" class="form-control" placeholder="Nama Lengkap" value="<?= $nama ?>" required>
                                </div>
                                <div class="mb-1">
                                    <label for="inputAddress" class="form-label">Nomor Handphone</label>
                                    <input name="nohp" type="number" class="form-control" placeholder="No HP" value="<?= $nohp ?>" required>
                                </div>
                                <div class="mb-1">
                                    <label for="inputAddress" class="form-label">Tanggal Lahir</label>
                                    <input name="tgl_lahir" type="date" class="form-control" value="<?= $tgl_lahir ?>" required>
                                </div>
                                <div class="mb-1">
                                    <button type="submit" class="btn btn-primary1">Simpan</button>
                                </div>
                            <?php } ?>
                        </form>
                    <?php } else { ?>
                        <p class="mb-1 text-secondary">Biodata dapat diubah ketika akun telah diaktifkan</p>
                        <a href="/verify" class="btn btn-primary1">Aktivasi akun</a>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    const inputFileElm = document.getElementById('input-file');
    const prevFileElm = document.getElementById('prev-file');
    inputFileElm.addEventListener('change', (e) => {
        const file = e.target.files[0];
        const blobFile = new Blob([file], {
            type: file.type
        });
        var blobUrl = URL.createObjectURL(blobFile);
        prevFileElm.src = blobUrl;
    })
</script>
<?= $this->endSection(); ?>