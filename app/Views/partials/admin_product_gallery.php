<section class="admin-form-section admin-media-gallery mt-4" id="product-gallery" data-product-gallery>
    <div class="admin-media-heading">
        <div><p class="admin-section-kicker">Foto produk</p><h2>Kelola galeri</h2><p>Pilih foto, periksa preview, lalu simpan. Foto 1 menjadi gambar utama produk.</p></div>
        <button type="button" class="btn btn-outline-dark" data-add-photo><i class="material-icons" aria-hidden="true">add_photo_alternate</i>Tambah foto</button>
    </div>
    <p class="admin-media-help">WebP atau AVIF saja · Maksimal 4 MB/foto · Total file baru 12 MB sekali simpan · Maksimal 50 foto. Gambar lama tetap tersimpan jika tidak diganti.</p>
    <div id="foto-varian" class="admin-media-grid">
        <?php for ($photo = 1; $photo <= $imageSlotCount; $photo++) {
            $src = \App\Services\CatalogImageUpload::slotUrl((string)($produk['id'] ?? ''), $photo, $gambar['gambar'.$photo] ?? null);
        ?>
        <article class="admin-image-field" data-photo-slot="<?= $photo; ?>" data-max-bytes="4194304">
            <div class="admin-media-picture"><img src="<?= esc($src, 'attr'); ?>" data-image-preview alt="Preview foto <?= $photo; ?>" loading="lazy"></div>
            <div class="admin-media-heading"><strong data-photo-title>Foto <?= $photo; ?><?= $photo === 1 ? ' · Utama' : ''; ?></strong><span class="admin-media-state" data-image-state><?= $src === '/img/nopic.jpg' ? 'Kosong' : 'Tersimpan'; ?></span></div>
            <label class="form-label" for="product-photo-<?= $photo; ?>">Pilih / ganti foto</label>
            <input class="form-control" type="file" id="product-photo-<?= $photo; ?>" name="gambar<?= $photo; ?>" accept="<?= \App\Services\CatalogImageUpload::ACCEPT; ?>" data-image-input aria-describedby="photo-feedback-<?= $photo; ?>">
            <p class="admin-media-feedback" id="photo-feedback-<?= $photo; ?>" data-image-feedback role="status" aria-live="polite">Belum ada file baru dipilih.</p>
            <button type="button" class="btn btn-light" data-reset-image hidden>Batalkan pilihan baru</button>
        </article>
        <?php } ?>
    </div>
    <p class="admin-media-feedback" data-gallery-feedback role="status" aria-live="polite"></p>
</section>
