<?= $this->extend('layout/template'); ?>
<?= $this->section('content'); ?>
<div class="konten">
    <div class="container">
        <nav style="--bs-breadcrumb-divider: '>'" aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/">Beranda</a></li>
                <li class="breadcrumb-item active" aria-current="page">
                    Kontak Kami
                </li>
            </ol>
        </nav>
        <h1 class="text-center">Kontak Kami</h1>
        <h5 class="mb-4 text-center" style="color: var(--hijau)">Tuliskan pesan Anda di formulir berikut</h5>
        <div class="d-flex flex-column align-items-center justify-content-center">
            <?php if ($val['msg']) { ?>
            <div class="alert alert-success" style="width: fit-content;" role="alert">
                <?= $val['msg']; ?>
            </div>
            <?php } ?>
            <div class="py-3" style="max-width: 700px; width: 100%;">
                <form action="/actionform" method="post">
                    <div class="form-floating mb-1">
                        <input type="text" class="form-control" placeholder="Nama Lengkap" name="nama" required>
                        <label for="floatingInput">Nama Lengkap</label>
                    </div>
                    <div class="form-floating mb-1">
                        <input type="number" class="form-control" placeholder="Nomor Handphone" name="nohp" required>
                        <label for="floatingInput">No. HP</label>
                    </div>
                    <div class="form-floating mb-1">
                        <input type="alamat" class="form-control" placeholder="Alamat" name="alamat">
                        <label for="floatingInput">Alamat</label>
                    </div>
                    <div class="form-floating mb-1">
                        <textarea class="form-control" placeholder="Alamat" name="pesan" required></textarea>
                        <label for="floatingInput">Pesan</label>
                    </div>
                    <div class="d-flex justify-content-center">
                        <button type="submit" class="btn btn-primary1">Kirim</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>