<?= $this->extend("layout/template"); ?>
<?= $this->section("content"); ?>
<div class="konten">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 text-center">
                <p><?= $ceking; ?></p>
                <p><?= $keranjang; ?></p>
                <h1 class="display-5 mt-5">Pembayaran Berhasil</h1>
                <p class="lead">Terima kasih telah melakukan pembayaran.</p>
                <i class="bi bi-check-circle text-success display-1 mt-4 mb-4"></i>
                <div class="mb-3">
                    <a href="<?= base_url(); ?>" class="btn btn-primary btn-lg me-3">Kembali ke Halaman Utama</a>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>