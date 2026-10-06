<?= $this->extend('layout/template'); ?>
<?= $this->section('content'); ?>
<?php
$selectedIds = array_flip((array)($settings['product_ids'] ?? []));
?>
<div class="konten">
    <div class="container">
        <div class="d-flex justify-content-between align-items-start gap-3 mb-3 flex-wrap">
            <div>
                <h5 class="jdl-section mb-1">Promo Website</h5>
                <h1 class="mb-1">Flash Sale</h1>
                <p class="text-secondary mb-0">Atur tampilan Flash Sale di homepage tanpa mengubah data utama produk dari Luna Sistem.</p>
            </div>
            <a href="/" target="_blank" class="btn btn-outline-dark d-flex align-items-center gap-2">
                <i class="material-icons">open_in_new</i>
                <span>Lihat Home</span>
            </a>
        </div>

        <?php if (!empty($msg)) { ?>
            <div class="alert alert-warning py-2"><?= esc($msg); ?></div>
        <?php } ?>

        <form action="/flashsaleadmin" method="post" class="flash-admin-grid">
            <section class="flash-admin-panel">
                <div class="flash-admin-panel__head">
                    <div>
                        <h5 class="fw-bold mb-1">Pengaturan utama</h5>
                        <p class="text-secondary mb-0">Kontrol status, teks, waktu selesai, dan mode produk.</p>
                    </div>
                </div>

                <label class="flash-admin-toggle mb-3">
                    <input type="checkbox" name="enabled" value="1" <?= !empty($settings['enabled']) ? 'checked' : ''; ?>>
                    <span>
                        <strong>Aktifkan Flash Sale</strong>
                        <small>Tampilkan section Flash Sale di homepage.</small>
                    </span>
                </label>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Label kecil</label>
                        <input type="text" class="form-control" name="kicker" maxlength="40" value="<?= esc($settings['kicker'] ?? 'Promo kilat'); ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Jam selesai</label>
                        <input type="time" class="form-control" name="end_time" value="<?= esc($settings['end_time'] ?? '23:59'); ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-bold">Judul</label>
                        <input type="text" class="form-control" name="title" maxlength="80" value="<?= esc($settings['title'] ?? 'Flash Sale Lunarea'); ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-bold">Subjudul</label>
                        <textarea class="form-control" name="subtitle" rows="2" maxlength="180"><?= esc($settings['subtitle'] ?? 'Harga spesial untuk produk pilihan. Buruan sebelum waktu habis.'); ?></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Jumlah tampil</label>
                        <input type="number" class="form-control" name="limit" min="4" max="24" value="<?= (int)($settings['limit'] ?? 12); ?>">
                        <small class="text-secondary">Minimal 4, maksimal 24 produk.</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Mode produk</label>
                        <select class="form-select" name="mode" id="flash-mode-select">
                            <option value="auto" <?= ($settings['mode'] ?? 'auto') === 'auto' ? 'selected' : ''; ?>>Otomatis dari diskon terbesar</option>
                            <option value="manual" <?= ($settings['mode'] ?? 'auto') === 'manual' ? 'selected' : ''; ?>>Manual pilih produk</option>
                        </select>
                        <small class="text-secondary">Produk tetap wajib punya diskon dari sistem.</small>
                    </div>
                </div>

                <div class="flash-admin-preview mt-4">
                    <span><i class="material-icons">bolt</i> Preview alur</span>
                    <p class="mb-0">Homepage akan menampilkan produk aktif yang masih punya diskon. Harga asli tetap dari sistem, website hanya mengatur promo display.</p>
                </div>
            </section>

            <section class="flash-admin-panel">
                <div class="flash-admin-panel__head">
                    <div>
                        <h5 class="fw-bold mb-1">Pilih produk manual</h5>
                        <p class="text-secondary mb-0">Dipakai kalau mode produk diset manual. Urutan mengikuti daftar yang dipilih.</p>
                    </div>
                </div>

                <div class="input-group mb-3">
                    <span class="input-group-text bg-white"><i class="material-icons">search</i></span>
                    <input type="search" class="form-control" id="flash-product-search" placeholder="Cari nama / ID produk">
                </div>

                <div class="flash-product-list" id="flash-product-list">
                    <?php if (empty($products)) { ?>
                        <div class="alert alert-info mb-0">Belum ada produk aktif yang memiliki diskon. Isi diskon dari sistem/produk terlebih dahulu.</div>
                    <?php } ?>

                    <?php foreach ($products as $p) {
                        $id = (string)$p['id'];
                        $discount = max(0, (int)($p['diskon'] ?? 0));
                        $price = (float)($p['harga'] ?? 0);
                        $salePrice = round(((100 - $discount) / 100) * $price);
                        $stockRaw = (string)($p['stok'] ?? '0');
                    ?>
                        <label class="flash-product-option" data-search="<?= esc(strtolower($p['nama'] . ' ' . $id)); ?>">
                            <input type="checkbox" name="product_ids[]" value="<?= esc($id); ?>" <?= isset($selectedIds[$id]) ? 'checked' : ''; ?>>
                            <img src="data:image/webp;base64,<?= base64_encode($p['gambar']); ?>" alt="<?= esc($p['nama']); ?>">
                            <span class="flash-product-option__body">
                                <strong><?= esc($p['nama']); ?></strong>
                                <small><?= esc($id); ?> · Diskon <?= $discount; ?>% · Stok <?= esc($stockRaw); ?></small>
                                <em>Rp <?= number_format($salePrice, 0, ",", "."); ?> <del>Rp <?= number_format($price, 0, ",", "."); ?></del></em>
                            </span>
                        </label>
                    <?php } ?>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-3 gap-2 flex-wrap">
                    <small class="text-secondary"><span id="flash-selected-count">0</span> produk dipilih.</small>
                    <button type="submit" class="btn btn-primary1 d-flex align-items-center gap-2">
                        <i class="material-icons">save</i>
                        <span>Simpan Flash Sale</span>
                    </button>
                </div>
            </section>
        </form>
    </div>
</div>

<style>
    .flash-admin-grid {
        display: grid;
        grid-template-columns: minmax(0, .95fr) minmax(0, 1.25fr);
        gap: 18px;
        align-items: start;
    }

    .flash-admin-panel {
        background: #fff;
        border: 1px solid rgba(36, 59, 107, .10);
        border-radius: 22px;
        padding: 18px;
        box-shadow: 0 14px 32px rgba(36, 59, 107, .06);
    }

    .flash-admin-panel__head {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 16px;
    }

    .flash-admin-toggle {
        display: flex;
        gap: 12px;
        align-items: center;
        padding: 14px;
        border-radius: 18px;
        background: var(--hijaumuda);
        cursor: pointer;
    }

    .flash-admin-toggle input {
        width: 22px;
        height: 22px;
        accent-color: var(--hijau);
        flex: 0 0 auto;
    }

    .flash-admin-toggle span {
        display: grid;
        gap: 2px;
    }

    .flash-admin-toggle small,
    .flash-admin-preview p {
        color: #66737f;
    }

    .flash-admin-preview {
        padding: 14px;
        border-radius: 18px;
        background: linear-gradient(135deg, var(--hijaumuda), #fff);
        border: 1px solid var(--hijaumuda2);
    }

    .flash-admin-preview span {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-bottom: 6px;
        color: var(--hijau);
        font-weight: 800;
    }

    .flash-product-list {
        display: grid;
        gap: 10px;
        max-height: 620px;
        overflow: auto;
        padding-right: 4px;
    }

    .flash-product-option {
        display: grid;
        grid-template-columns: auto 58px minmax(0, 1fr);
        align-items: center;
        gap: 12px;
        padding: 10px;
        border: 1px solid rgba(36, 59, 107, .10);
        border-radius: 16px;
        background: #fff;
        cursor: pointer;
        transition: .18s ease;
    }

    .flash-product-option:hover,
    .flash-product-option:has(input:checked) {
        border-color: var(--hijau);
        background: var(--hijaumuda);
    }

    .flash-product-option input {
        width: 20px;
        height: 20px;
        accent-color: var(--hijau);
    }

    .flash-product-option img {
        width: 58px;
        height: 58px;
        border-radius: 14px;
        object-fit: cover;
        background: var(--hijaumuda);
    }

    .flash-product-option__body {
        min-width: 0;
        display: grid;
        gap: 2px;
    }

    .flash-product-option__body strong {
        color: #14212b;
        line-height: 1.25;
    }

    .flash-product-option__body small {
        color: #66737f;
    }

    .flash-product-option__body em {
        color: var(--hijau);
        font-style: normal;
        font-weight: 800;
    }

    .flash-product-option__body del {
        color: #9ca3af;
        font-weight: 500;
        margin-left: 4px;
    }

    @media (max-width: 991.98px) {
        .flash-admin-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 575.98px) {
        .flash-admin-panel {
            border-radius: 18px;
            padding: 14px;
        }

        .flash-product-option {
            grid-template-columns: auto 50px minmax(0, 1fr);
        }

        .flash-product-option img {
            width: 50px;
            height: 50px;
        }
    }
</style>

<script>
    const flashProductSearch = document.getElementById('flash-product-search');
    const flashSelectedCount = document.getElementById('flash-selected-count');
    const flashProductOptions = document.querySelectorAll('.flash-product-option');

    function updateFlashSelectedCount() {
        const checked = document.querySelectorAll('.flash-product-option input:checked').length;
        if (flashSelectedCount) flashSelectedCount.textContent = checked;
    }

    flashProductSearch?.addEventListener('input', () => {
        const keyword = flashProductSearch.value.trim().toLowerCase();
        flashProductOptions.forEach((item) => {
            item.style.display = item.dataset.search.includes(keyword) ? 'grid' : 'none';
        });
    });

    flashProductOptions.forEach((item) => {
        item.querySelector('input')?.addEventListener('change', updateFlashSelectedCount);
    });

    updateFlashSelectedCount();
</script>
<?= $this->endSection(); ?>
