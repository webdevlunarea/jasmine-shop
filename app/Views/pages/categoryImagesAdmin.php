<?= $this->extend('layout/template'); ?>
<?= $this->section('content'); ?>
<div class="konten admin-module-page admin-module-category-images-admin">
    <div class="container">
        <?= view('partials/admin_page_heading', ['adminHeadingTitle'=>'Gambar kategori', 'adminHeadingGroup'=>'Katalog', 'adminHeadingDescription'=>'Gunakan WebP atau AVIF, rekomendasi 300 × 300 px. Periksa preview sebelum menyimpan.', 'adminHeadingActions'=>[['label'=>'Kembali ke produk', 'href'=>'/listproduct', 'icon'=>'arrow_back', 'primary'=>false, 'external'=>false]]], ['saveData'=>false]); ?>

        <?php if (!empty($msg)) { ?>
            <div class="alert alert-info py-2"><?= esc($msg); ?></div>
        <?php } ?>

        <form action="/categoryimagesadmin" method="post" enctype="multipart/form-data">
            <?= csrf_field(); ?>
            <div class="row g-3">
                <?php foreach ($categories as $key => $label) { ?>
                    <div class="col-12 col-md-6 col-lg-4">
                        <div class="admin-image-field h-100" data-max-bytes="2097152">
                            <div class="d-flex gap-3 align-items-center mb-3">
                                <div style="width:76px;height:76px;display:grid;place-items:center;background:transparent;border:1px dashed #d1d5db;border-radius:14px;">
                                    <img class="category-image-preview" data-image-preview src="<?= esc($images[$key] ?? ''); ?>" alt="<?= esc($label); ?>" style="width:68px;height:68px;object-fit:contain;background:transparent;">
                                </div>
                                <div>
                                    <p class="mb-1 fw-bold"><?= esc($label); ?></p>
                                    <small class="text-muted"><?= esc($key); ?></small>
                                </div>
                            </div>
                            <label class="form-label" for="category-<?= esc($key); ?>">Pilih / ganti gambar</label>
                            <input type="file" class="form-control category-image-input" id="category-<?= esc($key); ?>" data-image-input name="category_<?= esc($key); ?>" accept=".webp,.avif,image/webp,image/avif">
                            <small class="admin-media-feedback d-block" data-image-feedback role="status" aria-live="polite">WebP atau AVIF · Maksimal 2 MB · Saran 300 × 300 px.</small>
                            <button type="button" class="btn btn-light" data-reset-image hidden>Batalkan pilihan baru</button>
                        </div>
                    </div>
                <?php } ?>
            </div>
            <div class="d-flex justify-content-end mt-4">
                <button type="submit" class="btn btn-primary1">Simpan Gambar Kategori</button>
            </div>
        </form>
    </div>
</div>

<?= $this->endSection(); ?>
