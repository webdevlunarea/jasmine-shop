<?= $this->extend("layout/template"); ?>
<?= $this->section("content"); ?>
<div class="konten">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 text-center">
                <h1 class="display-5 mt-5">Pembayaran Sedang Diproses</h1>
                <p class="lead">Pembayaran Anda sedang dalam proses verifikasi. Harap tunggu konfirmasi lebih lanjut.
                </p>
                <i class="bi bi-hourglass-split text-warning display-1 mt-4 mb-4"></i>
                <div class="mb-3">
                    <a href="<?= base_url(); ?>" class="btn btn-primary btn-lg me-3">Kembali ke Halaman Utama</a>
                    <a href="https://wa.me/6281234567890?text=Halo%20,%20saya%20mengalami%20masalah%20dengan%20pembayaran%20saya.%20Bisakah%20Anda%20bantu%20saya?"
                        class="btn btn-success btn-lg">Butuh Bantuan?</a>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>