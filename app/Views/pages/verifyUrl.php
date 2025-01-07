<?= $this->extend('layout/template'); ?>
<?= $this->section('content'); ?>
<div class="konten d-flex justify-content-center align-items-center">
    <div class="container" style="max-width: 600px;">
        <div class="d-flex justify-content-center mb-3">
            <div style="color: var(--<?= $status == 'success' ? 'hijau' : 'merah' ?>); border: 5px solid var(--<?= $status == 'success' ? 'hijau' : 'merah' ?>); width:fit-content; padding: 1em; border-radius: 3em;">
                <i class="material-icons" style="font-size: 50px;"><?= $datanya['icon']; ?></i>
            </div>
        </div>
        <h1 style="color: var(--<?= $status == 'success' ? 'hijau' : 'merah' ?>);" class="text-center m-0"><?= $datanya['judul1']; ?></h1>
        <h1 class="text-center"><?= $datanya['judul2']; ?></h1>
        <hr>
        <p class="text-center"><?= $datanya['isi']; ?></p>
        <div class="d-flex gap-1 justify-content-center">
            <?php if ($status == 'success') { ?>
                <a href="/voucher" class="btn btn-primary1">Lihat promo</a>
                <a href="/all" class="btn btn-outline-dark">Mulai belanja</a>
            <?php } else if ($status == 'fail') { ?>
                <form action="/kirimotp" method="post">
                    <button type="submit" class="btn btn-outline-dark">Kirim ulang kode OTP</button>
                </form>
            <?php } ?>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>