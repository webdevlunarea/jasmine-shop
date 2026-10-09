<?= $this->extend('layout/template'); ?>
<?= $this->section('content'); ?>
<?php
$selectedIds = array_flip((array)($settings['product_ids'] ?? []));
$previewProducts = $previewProducts ?? [];
$isManualMode = ($settings['mode'] ?? 'auto') === 'manual';
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
                        <input type="number" class="form-control" name="limit" id="flash-limit-input" min="4" max="24" value="<?= (int)($settings['limit'] ?? 12); ?>" <?= $isManualMode ? 'readonly' : ''; ?>>
                        <small class="text-secondary" id="flash-limit-help"><?= $isManualMode ? 'Mode manual: jumlah tampil mengikuti jumlah produk yang dicentang.' : 'Mode otomatis: minimal 4, maksimal 24 produk.'; ?></small>
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
                    <p class="mb-0">Homepage mengikuti setting di admin ini. Harga, diskon, stok, dan status aktif tetap dari data produk/sistem.</p>
                    <div class="flash-admin-sync mt-3">
                        <div>
                            <small>Status</small>
                            <strong><?= !empty($settings['enabled']) ? 'Aktif' : 'Nonaktif'; ?></strong>
                        </div>
                        <div>
                            <small>Mode</small>
                            <strong><?= $isManualMode ? 'Manual' : 'Otomatis'; ?></strong>
                        </div>
                        <div>
                            <small>Tampil</small>
                            <strong><?= count($previewProducts); ?> produk</strong>
                        </div>
                    </div>
                    <div class="flash-text-preview mt-3">
                        <small>Teks yang sedang aktif di homepage</small>
                        <strong><?= esc($settings['kicker'] ?? 'Promo kilat'); ?></strong>
                        <h4><?= esc($settings['title'] ?? 'Flash Sale Lunarea'); ?></h4>
                        <p><?= esc($settings['subtitle'] ?? 'Harga spesial untuk produk pilihan. Buruan sebelum waktu habis.'); ?></p>
                    </div>
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

                <div class="flash-selected-tools mb-3">
                    <div>
                        <strong><span id="flash-selected-count-top">0</span> produk dipilih</strong>
                        <small>Untuk mode manual, jumlah produk homepage akan sama dengan checklist ini.</small>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-danger" id="flash-clear-selected">
                        Bersihkan pilihan
                    </button>
                </div>
                <div class="flash-selected-summary mb-3" id="flash-selected-summary"></div>
                <div class="flash-hidden-selected-alert mb-3" id="flash-hidden-selected-alert"></div>

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
                        <label class="flash-product-option" data-search="<?= esc(strtolower($p['nama'] . ' ' . $id)); ?>" data-id="<?= esc($id); ?>" data-name="<?= esc($p['nama']); ?>">
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

                <div class="flash-home-preview mt-3">
                    <div class="flash-home-preview__head">
                        <div>
                            <strong>Produk yang tampil di homepage sekarang</strong>
                            <small><?= $isManualMode ? 'Sesuai pilihan manual dan urutan checklist.' : 'Otomatis dari diskon terbesar.'; ?></small>
                        </div>
                        <span><?= count($previewProducts); ?> item</span>
                    </div>
                    <?php if (empty($previewProducts)) { ?>
                        <p class="text-secondary mb-0 small">Belum ada produk yang akan tampil. Untuk mode manual, pilih minimal satu produk diskon. Untuk mode otomatis, pastikan produk aktif memiliki diskon.</p>
                    <?php } else { ?>
                        <div class="flash-home-preview__items">
                            <?php foreach (array_slice($previewProducts, 0, 8) as $preview) { ?>
                                <a href="/product/<?= esc($preview['path']); ?>" target="_blank" class="flash-home-preview__item">
                                    <img src="data:image/webp;base64,<?= base64_encode($preview['gambar']); ?>" alt="<?= esc($preview['nama']); ?>">
                                    <span><?= esc($preview['nama']); ?></span>
                                </a>
                            <?php } ?>
                        </div>
                    <?php } ?>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-3 gap-2 flex-wrap">
                    <small class="text-secondary"><span id="flash-selected-count">0</span> produk dipilih. Pastikan mode produk = Manual kalau ingin memakai pilihan ini.</small>
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

    .flash-admin-sync {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 10px;
    }

    .flash-admin-sync div {
        display: grid;
        gap: 2px;
        padding: 10px;
        border-radius: 14px;
        background: rgba(255, 255, 255, .82);
        border: 1px solid rgba(36, 59, 107, .08);
    }

    .flash-admin-sync small {
        color: #66737f;
        font-weight: 700;
    }

    .flash-admin-sync strong {
        color: var(--hijau);
        font-size: .95rem;
    }

    .flash-text-preview {
        display: grid;
        gap: 4px;
        padding: 12px;
        border-radius: 16px;
        background: #fff;
        border: 1px solid rgba(36, 59, 107, .08);
    }

    .flash-text-preview small {
        color: #66737f;
        font-weight: 700;
    }

    .flash-text-preview strong {
        width: fit-content;
        border-radius: 999px;
        padding: 5px 9px;
        background: var(--hijau);
        color: #fff;
        font-size: .72rem;
        text-transform: uppercase;
        letter-spacing: .08em;
    }

    .flash-text-preview h4 {
        margin: 4px 0 0;
        color: #14212b;
        font-weight: 900;
    }

    .flash-text-preview p {
        margin: 0;
        color: #66737f;
    }

    .flash-product-list {
        display: grid;
        gap: 10px;
        max-height: 620px;
        overflow: auto;
        padding-right: 4px;
    }

    .flash-selected-tools {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 12px;
        border-radius: 16px;
        background: var(--hijaumuda);
        border: 1px solid var(--hijaumuda2);
    }

    .flash-selected-tools div {
        display: grid;
        gap: 2px;
    }

    .flash-selected-tools strong {
        color: var(--hijau);
    }

    .flash-selected-tools small {
        color: #66737f;
    }

    .flash-selected-summary {
        display: none;
        gap: 8px;
        flex-wrap: wrap;
        padding: 12px;
        border-radius: 16px;
        background: #fff;
        border: 1px dashed rgba(36, 59, 107, .18);
    }

    .flash-selected-summary.is-active {
        display: flex;
    }

    .flash-selected-chip {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        max-width: 100%;
        padding: 6px 8px 6px 6px;
        border-radius: 999px;
        background: var(--hijaumuda);
        color: #14212b;
        font-size: .8rem;
        font-weight: 800;
    }

    .flash-selected-chip img {
        width: 28px;
        height: 28px;
        border-radius: 999px;
        object-fit: cover;
        background: #fff;
    }

    .flash-selected-chip span {
        max-width: 180px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .flash-selected-chip button {
        width: 22px;
        height: 22px;
        border: 0;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(36, 59, 107, .10);
        color: var(--hijau);
        line-height: 1;
    }

    .flash-hidden-selected-alert {
        display: none;
        padding: 10px 12px;
        border-radius: 14px;
        background: #fff7e6;
        color: #8a5b00;
        border: 1px solid rgba(217, 140, 154, .35);
        font-size: .86rem;
        font-weight: 700;
    }

    .flash-hidden-selected-alert.is-active {
        display: block;
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

    .flash-home-preview {
        padding: 14px;
        border-radius: 18px;
        border: 1px solid rgba(36, 59, 107, .10);
        background: linear-gradient(180deg, #fff, #fbfdfb);
    }

    .flash-home-preview__head {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 10px;
        margin-bottom: 12px;
    }

    .flash-home-preview__head div {
        display: grid;
        gap: 2px;
    }

    .flash-home-preview__head strong {
        color: #14212b;
    }

    .flash-home-preview__head small {
        color: #66737f;
    }

    .flash-home-preview__head span {
        border-radius: 999px;
        background: var(--hijaumuda);
        color: var(--hijau);
        padding: 5px 10px;
        font-weight: 800;
        white-space: nowrap;
    }

    .flash-home-preview__items {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 10px;
    }

    .flash-home-preview__item {
        display: grid;
        gap: 7px;
        color: #14212b;
        text-decoration: none;
        font-size: .78rem;
        font-weight: 700;
        line-height: 1.25;
    }

    .flash-home-preview__item:hover {
        color: var(--hijau);
    }

    .flash-home-preview__item img {
        width: 100%;
        aspect-ratio: 1 / 1;
        object-fit: cover;
        border-radius: 12px;
        background: var(--hijaumuda);
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

        .flash-selected-tools {
            align-items: stretch;
            flex-direction: column;
        }

        .flash-admin-sync,
        .flash-home-preview__items {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
</style>

<script>
    const flashProductSearch = document.getElementById('flash-product-search');
    const flashSelectedCount = document.getElementById('flash-selected-count');
    const flashSelectedCountTop = document.getElementById('flash-selected-count-top');
    const flashProductOptions = document.querySelectorAll('.flash-product-option');
    const flashModeSelect = document.getElementById('flash-mode-select');
    const flashLimitInput = document.getElementById('flash-limit-input');
    const flashLimitHelp = document.getElementById('flash-limit-help');
    const flashClearSelected = document.getElementById('flash-clear-selected');
    const flashSelectedSummary = document.getElementById('flash-selected-summary');
    const flashHiddenSelectedAlert = document.getElementById('flash-hidden-selected-alert');

    function setManualModeFromChecklist() {
        if (flashModeSelect && flashModeSelect.value !== 'manual') {
            flashModeSelect.value = 'manual';
        }
        syncFlashLimitMode();
    }

    function renderSelectedSummary() {
        if (!flashSelectedSummary) return;

        const selectedItems = Array.from(flashProductOptions).filter((item) => item.querySelector('input')?.checked);
        flashSelectedSummary.innerHTML = '';
        flashSelectedSummary.classList.toggle('is-active', selectedItems.length > 0);

        selectedItems.forEach((item) => {
            const chip = document.createElement('span');
            chip.className = 'flash-selected-chip';

            const img = item.querySelector('img')?.cloneNode();
            if (img) chip.appendChild(img);

            const text = document.createElement('span');
            text.textContent = item.dataset.name || item.dataset.id || 'Produk';
            chip.appendChild(text);

            const remove = document.createElement('button');
            remove.type = 'button';
            remove.innerHTML = '&times;';
            remove.setAttribute('aria-label', 'Hapus ' + text.textContent);
            remove.addEventListener('click', () => {
                const input = item.querySelector('input');
                if (input) input.checked = false;
                updateFlashSelectedCount();
            });
            chip.appendChild(remove);

            flashSelectedSummary.appendChild(chip);
        });
    }

    function renderHiddenSelectedWarning() {
        if (!flashHiddenSelectedAlert) return;
        const keyword = flashProductSearch?.value.trim().toLowerCase() || '';
        const hiddenChecked = Array.from(flashProductOptions).filter((item) => {
            const checked = item.querySelector('input')?.checked;
            const visibleBySearch = keyword === '' || item.dataset.search.includes(keyword);
            return checked && !visibleBySearch;
        }).length;

        flashHiddenSelectedAlert.classList.toggle('is-active', hiddenChecked > 0);
        flashHiddenSelectedAlert.textContent = hiddenChecked > 0
            ? `${hiddenChecked} produk terpilih sedang tersembunyi karena pencarian. Cek ringkasan produk terpilih di atas sebelum simpan.`
            : '';
    }

    function updateFlashSelectedCount() {
        const checkedItems = document.querySelectorAll('.flash-product-option input:checked');
        const checked = checkedItems.length;
        if (flashSelectedCount) flashSelectedCount.textContent = checked;
        if (flashSelectedCountTop) flashSelectedCountTop.textContent = checked;

        if (flashModeSelect?.value === 'manual' && flashLimitInput) {
            flashLimitInput.value = Math.min(24, checked);
        }

        flashProductOptions.forEach((item) => {
            const isChecked = item.querySelector('input')?.checked;
            item.style.order = isChecked ? '-1' : '0';
        });

        renderSelectedSummary();
        renderHiddenSelectedWarning();
    }

    function syncFlashLimitMode() {
        const isManual = flashModeSelect?.value === 'manual';
        if (flashLimitInput) flashLimitInput.readOnly = !!isManual;
        if (flashLimitHelp) {
            flashLimitHelp.textContent = isManual
                ? 'Mode manual: jumlah tampil mengikuti jumlah produk yang dicentang.'
                : 'Mode otomatis: minimal 4, maksimal 24 produk.';
        }
        updateFlashSelectedCount();
    }

    flashProductSearch?.addEventListener('input', () => {
        const keyword = flashProductSearch.value.trim().toLowerCase();
        flashProductOptions.forEach((item) => {
            item.style.display = item.dataset.search.includes(keyword) ? 'grid' : 'none';
        });
        renderHiddenSelectedWarning();
    });

    flashProductOptions.forEach((item) => {
        item.querySelector('input')?.addEventListener('change', () => {
            setManualModeFromChecklist();
        });
    });

    flashModeSelect?.addEventListener('change', syncFlashLimitMode);
    flashClearSelected?.addEventListener('click', () => {
        flashProductOptions.forEach((item) => {
            const input = item.querySelector('input');
            if (input) input.checked = false;
        });
        updateFlashSelectedCount();
    });

    syncFlashLimitMode();
</script>
<?= $this->endSection(); ?>
