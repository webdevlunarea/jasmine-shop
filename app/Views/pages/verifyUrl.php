<?= $this->extend('layout/template'); ?>
<?= $this->section('content'); ?>
<div class="konten d-flex justify-content-center align-items-center">
    <div class="container" style="max-width: 600px;">
        <h1 style="color: var(--hijau);" class="text-center m-0"><?= $datanya['judul1']; ?></h1>
        <h1 class="text-center"><?= $datanya['judul2']; ?></h1>
        <hr>
        <p class="text-center"><?= $datanya['isi']; ?></p>
        <div class="d-flex gap-1 justify-content-center">
            <?php if ($status == 'success') { ?>
                <a href="/account" class="btn btn-primary1">Lengkapi biodata</a>
                <a href="/all" class="btn btn-outline-dark">Lihat produk</a>
            <?php } else if ($status == 'fail') { ?>
                <form action="/kirimotp" method="post">
                    <button type="submit" class="btn btn-primary1">Kirim lagi</button>
                </form>
            <?php } ?>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>