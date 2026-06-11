<?= $this->extend('layout/template'); ?>
<?= $this->section('content'); ?>
<style>
.banner-form-preview {
    width: 100%;
    max-width: 720px;
    aspect-ratio: 16 / 5;
    object-fit: cover;
    border-radius: 8px;
    border: 1px solid #dee2e6;
    background-color: #f8f9fa;
}
</style>
<div class="konten">
    <div class="container">
        <form action="/actionaddbanner" method="post" enctype="multipart/form-data">
            <?= csrf_field(); ?>
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="jdl-section mb-0">Homepage</h5>
                    <h1 class="mb-0">Tambah Banner</h1>
                </div>
                <a href="/listbanner" class="btn btn-outline-dark">Kembali</a>
            </div>

            <?php if ($msg) { ?>
                <div class="alert alert-danger" role="alert">
                    <?= $msg; ?>
                </div>
            <?php } ?>

            <div class="mb-2">
                <label class="form-label">Judul</label>
                <input type="text" class="form-control" name="judul" value="<?= old('judul'); ?>" required>
            </div>
            <div class="mb-2">
                <label class="form-label m-0">Alt Text</label>
                <p class="text-secondary mb-1" style="font-size: small;">Dipakai untuk SEO dan aksesibilitas. Jika kosong akan memakai judul.</p>
                <input type="text" class="form-control" name="alt" value="<?= old('alt'); ?>">
            </div>
            <div class="mb-2">
                <label class="form-label m-0">Link Tujuan</label>
                <p class="text-secondary mb-1" style="font-size: small;">Contoh: /all, /voucher, atau URL lengkap. Kosongkan jika banner tidak bisa diklik.</p>
                <input type="text" class="form-control" name="link" value="<?= old('link'); ?>">
            </div>
            <div class="mb-2">
                <label class="form-label">Urutan</label>
                <input type="number" class="form-control" name="urutan" value="<?= old('urutan') !== null ? old('urutan') : 0; ?>">
            </div>
            <div class="mb-2">
                <label class="form-label">Gambar Banner</label>
                <input type="file" class="form-control" name="gambar" accept="image/png,image/jpeg,image/webp" required onchange="previewBanner(event)">
                <p class="text-secondary mt-1 mb-0" style="font-size: small;">Rekomendasi rasio gambar mengikuti banner lama, sekitar 16:5. Maksimal 4 MB.</p>
            </div>
            <img id="banner-preview" class="banner-form-preview d-none mb-3" alt="Preview banner">
            <div class="mb-3 d-flex gap-1 align-items-center">
                <input type="checkbox" name="active" id="active" checked>
                <label for="active">Aktifkan banner</label>
            </div>
            <button type="submit" class="btn btn-dark">Simpan</button>
        </form>
    </div>
</div>
<script>
function previewBanner(event) {
    const preview = document.getElementById('banner-preview');
    const file = event.target.files[0];
    if (!file) return;
    preview.src = URL.createObjectURL(file);
    preview.classList.remove('d-none');
}
</script>
<?= $this->endSection(); ?>
