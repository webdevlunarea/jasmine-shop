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
<div class="konten admin-module-page admin-module-edit-banner">
    <div class="container">
        <form action="/actioneditbanner/<?= $banner['id']; ?>" method="post" enctype="multipart/form-data">
            <?= csrf_field(); ?>
            <?= view('partials/admin_page_heading', ['adminHeadingTitle'=>'Edit banner', 'adminHeadingGroup'=>'Promosi & konten', 'adminHeadingDescription'=>'Gunakan gambar yang jelas dan periksa tampilannya di desktop maupun mobile.', 'adminHeadingActions'=>[['label'=>'Kembali', 'href'=>'/listbanner', 'icon'=>'arrow_back', 'primary'=>false, 'external'=>false]]], ['saveData'=>false]); ?>

        <?php if ($msg) { ?>
                <div class="alert alert-danger" role="alert">
                    <?= esc($msg); ?>
                </div>
            <?php } ?>

            <div class="mb-2">
                <label class="form-label">Judul</label>
                <input type="text" class="form-control" name="judul" value="<?= esc($banner['judul']); ?>" required>
            </div>
            <div class="mb-2">
                <label class="form-label m-0">Alt Text</label>
                <p class="text-secondary mb-1" style="font-size: small;">Dipakai untuk SEO dan aksesibilitas. Jika kosong akan memakai judul.</p>
                <input type="text" class="form-control" name="alt" value="<?= esc($banner['alt']); ?>">
            </div>
            <div class="mb-2">
                <label class="form-label m-0">Link Tujuan</label>
                <p class="text-secondary mb-1" style="font-size: small;">Contoh: /all, /voucher, atau URL lengkap. Kosongkan jika banner tidak bisa diklik.</p>
                <input type="text" class="form-control" name="link" value="<?= esc($banner['link']); ?>">
            </div>
            <div class="mb-2">
                <label class="form-label">Urutan</label>
                <input type="number" class="form-control" name="urutan" value="<?= (int)$banner['urutan']; ?>">
            </div>
            <div class="mb-3">
                <label class="form-label" for="banner-visibility">Tampilkan di</label>
                <?php $visibility = $banner['visibility'] ?? 'both'; ?>
                <select class="form-select" name="visibility" id="banner-visibility">
                    <option value="both" <?= $visibility === 'both' ? 'selected' : ''; ?>>Website dan Android</option>
                    <option value="website" <?= $visibility === 'website' ? 'selected' : ''; ?>>Website saja</option>
                    <option value="android" <?= $visibility === 'android' ? 'selected' : ''; ?>>Android saja</option>
                </select>
                <p class="text-secondary mb-0 mt-1" style="font-size:small">Mengatur kanal tampil tanpa menggandakan gambar banner.</p>
            </div>
            <div class="mb-2 admin-image-field" data-max-bytes="4194304">
                <label class="form-label" for="banner-image">Gambar banner</label>
                <img id="banner-preview" data-image-preview class="banner-form-preview d-block mb-2" src="/imgbanner/<?= $banner['id']; ?>?v=<?= strtotime($banner['updated_at'] ?? '') ?: $banner['id']; ?>" alt="<?= esc($banner['alt'] ?: $banner['judul']); ?>">
                <input type="file" class="form-control" id="banner-image" name="gambar" accept=".webp,.avif,image/webp,image/avif" data-image-input aria-describedby="banner-image-feedback">
                <p class="text-secondary mt-1 mb-0" style="font-size: small;">WebP atau AVIF · Maksimal 4 MB dan 4096 px per sisi. Kosongkan untuk mempertahankan gambar lama.</p>
                <p id="banner-image-feedback" class="admin-media-feedback" data-image-feedback role="status" aria-live="polite">Gambar tersimpan tetap dipakai jika tidak diganti.</p>
                <button type="button" class="btn btn-light" data-reset-image hidden>Batalkan pilihan baru</button>
            </div>
            <div class="mb-3 d-flex gap-1 align-items-center">
                <input type="checkbox" name="active" id="active" <?= $banner['active'] ? 'checked' : ''; ?>>
                <label for="active">Aktifkan banner</label>
            </div>
            <button type="submit" class="btn btn-dark">Simpan</button>
        </form>
    </div>
</div>

<?= $this->endSection(); ?>
