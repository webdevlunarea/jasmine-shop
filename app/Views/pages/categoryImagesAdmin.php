<?= $this->extend('layout/template'); ?>
<?= $this->section('content'); ?>
<div class="konten">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <p class="admin-form-eyebrow mb-1">Website</p>
                <h5 class="jdl-section mb-1">Gambar Kategori</h5>
                <p class="text-muted mb-0">Upload gambar kategori transparan. Rekomendasi ukuran 300x300 px, format PNG/WebP.</p>
            </div>
            <a href="/listproduct" class="btn btn-outline-dark">Kembali</a>
        </div>

        <?php if (!empty($msg)) { ?>
            <div class="alert alert-info py-2"><?= esc($msg); ?></div>
        <?php } ?>

        <form action="/categoryimagesadmin" method="post" enctype="multipart/form-data">
            <?= csrf_field(); ?>
            <div class="row g-3">
                <?php foreach ($categories as $key => $label) { ?>
                    <div class="col-12 col-md-6 col-lg-4">
                        <div class="border rounded-4 p-3 h-100 bg-white">
                            <div class="d-flex gap-3 align-items-center mb-3">
                                <div style="width:76px;height:76px;display:grid;place-items:center;background:transparent;border:1px dashed #d1d5db;border-radius:14px;">
                                    <img src="<?= esc($images[$key] ?? ''); ?>" alt="<?= esc($label); ?>" style="width:68px;height:68px;object-fit:contain;background:transparent;">
                                </div>
                                <div>
                                    <p class="mb-1 fw-bold"><?= esc($label); ?></p>
                                    <small class="text-muted"><?= esc($key); ?></small>
                                </div>
                            </div>
                            <label class="form-label">Ganti gambar</label>
                            <input type="file" class="form-control" name="category_<?= esc($key); ?>" accept="image/png,image/webp,image/jpeg">
                            <small class="text-muted">Saran: 300x300 px, background transparan, max 2MB.</small>
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
