<?= $this->extend("layout/template"); ?>
<?= $this->section("content"); ?>
<div class="konten d-flex align-items-center">
    <div class="container">
        <div class="justify-content-center">
            <div class="text-center">
                <h1 class="display-5">Pembayaran Gagal</h1>
                <p class="lead">Maaf, pembayaran Anda tidak berhasil diproses.</p>
                <i class="bi bi-x-circle text-danger display-1 mt-4 mb-4"></i>
                <div class="mb-3">
                    <a href="<?= base_url(); ?>" class="btn btn-primary btn-lg me-3 mb-2">Kembali ke Halaman Utama</a>
                    <a href="https://wa.me/6281234567890?text=Halo%20,%20saya%20mengalami%20masalah%20dengan%20pembayaran%20saya.%20Bisakah%20Anda%20bantu%20saya?" class="btn btn-dark btn-lg mb-2">Butuh Bantuan?</a>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>