<?= $this->extend("layout/template"); ?>
<?= $this->section("content"); ?>
<div class="konten d-flex align-items-center">
    <div class="container">
        <div class="justify-content-center">
            <div class="text-center">
                <h1 class="display-5">Pembayaran Berhasil</h1>
                <p class="lead">Terima kasih telah melakukan pembayaran.</p>
                <i class="bi bi-check-circle text-success display-1 mt-4 mb-4"></i>
                <div class="mb-3">
                    <a href="<?= base_url(); ?>" class="btn btn-primary1 btn-lg me-3 mb-2">Kembali ke Halaman Utama</a>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>