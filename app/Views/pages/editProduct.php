<?= $this->extend('layout/template'); ?>
<?= $this->section('content'); ?>
<?php
$namaProduk = $produk['nama'] ?? '';
$hargaProduk = $produk['harga'] ?? 0;
$diskonProduk = $produk['diskon'] ?? 0;
$dimensiProduk = $produk['dimensi'] ?? '';
$beratProduk = $produk['berat'] ?? '';
$stokProduk = $produk['stok'] ?? '';
$kategoriProduk = $produk['kategori'] ?? '';
$subkategoriProduk = $produk['subkategori'] ?? '';
$varianText = $varian ?? '';
$jmlVarianProduk = $produk['jml_varian'] ?? 1;
$deskripsiProduk = $produk['deskripsi'] ?? '';
$deskripsiNonhtmlProduk = $produk['deskripsi_nonhtml'] ?? '';
$pencarianProduk = $produk['pencarian'] ?? '';
$variantList = json_decode($produk['varian'] ?? '[]', true);
if (!is_array($variantList) || count($variantList) < 1) $variantList = ['Default'];
$gambar = is_array($gambar ?? null) ? $gambar : [];
if (empty($gambar['gambar1']) && !empty($produk['gambar'])) $gambar['gambar1'] = $produk['gambar'];
$imageSlotCount = \App\Services\CatalogImageUpload::slotCount($produk, $gambar);
$variantImageMap = is_array($variantImageMap ?? null) ? $variantImageMap : [];
?>
<div class="konten admin-module-page admin-module-edit-product">
    <div class="container admin-product-editor">
        <style>
            .admin-product-fields .form-control[readonly],
            .admin-product-fields textarea[readonly] {
                background: #f8fafc !important;
                border: 1px dashed #cbd5e1;
                color: #64748b;
                cursor: not-allowed;
                box-shadow: none;
            }
            .admin-product-fields .admin-field {
                position: relative;
            }
            .readonly-badge {
                display: inline-flex;
                align-items: center;
                gap: 4px;
                margin-left: 8px;
                padding: 2px 8px;
                border-radius: 999px;
                background: #eef2ff;
                color: #4338ca;
                font-size: 11px;
                font-weight: 700;
                letter-spacing: .02em;
                vertical-align: middle;
            }
            .readonly-badge .material-icons {
                font-size: 13px;
                line-height: 1;
            }
            .luna-locked-panel {
                border: 1px solid #c7d2fe;
                background: linear-gradient(135deg, #eef2ff, #f8fafc);
                border-radius: 16px;
                padding: 14px 16px;
                display: flex;
                gap: 12px;
                align-items: flex-start;
            }
            .luna-locked-panel .material-icons {
                color: #4f46e5;
                background: #fff;
                border-radius: 12px;
                padding: 8px;
            }
            .editable-photo-panel {
                border: 2px solid #16a34a;
                box-shadow: 0 10px 30px rgba(22, 163, 74, .08);
            }
            .website-promo-field {
                border: 1px solid #e8ded3;
                border-radius: 14px;
                padding: 12px;
                background: #fbf8f5;
            }
            .website-promo-field .form-control {
                background: #fff !important;
                border: 1px solid #d9c8b8 !important;
                color: #111827 !important;
                cursor: text !important;
            }
            .luna-locked-panel {
                border-color: #e6ded6;
                background: linear-gradient(135deg, #f8f5f1, #fff);
            }
            .luna-locked-panel .material-icons {
                color: #2f2d20;
            }
            .admin-product-fields .admin-field:not(.website-promo-field) .form-control:not([readonly]) {
                background-color: #fff;
                border-color: #d9c8b8;
                cursor: text;
            }
            .variant-image-picker {
                display: grid;
                grid-template-columns: 1.1fr 92px minmax(180px, .9fr);
                gap: 14px;
                align-items: center;
                padding: 14px;
                border: 1px solid #e5e7eb;
                border-radius: 14px;
                background: #fff;
            }
            .variant-image-map-section {
                border: 1px solid #dbeafe;
                background: linear-gradient(180deg, #ffffff, #f8fbff);
            }
            .variant-image-picker__title strong {
                display: block;
                color: #0f172a;
            }
            .variant-image-picker__title span {
                display: block;
                color: #64748b;
                font-size: 12px;
                margin-top: 3px;
            }
            .variant-image-picker__preview {
                width: 92px;
                height: 92px;
                border-radius: 12px;
                object-fit: cover;
                background: #f8fafc;
                border: 1px solid #e2e8f0;
            }
            .variant-image-picker__meta {
                min-width: 0;
            }
            .variant-image-picker__meta .form-control {
                min-height: 44px;
            }
            .variant-image-select {
                min-height: 44px;
            }
            .variant-image-picker + .variant-image-picker {
                margin-top: 10px;
            }
            .admin-form-actions-sticky {
                max-width: 520px;
                margin-left: auto;
            }
            @media (max-width: 767.98px) {
                .variant-image-picker {
                    grid-template-columns: 72px minmax(0, 1fr);
                    grid-template-areas:
                        "preview title"
                        "select select";
                }
                .variant-image-picker__title { grid-area: title; }
                .variant-image-picker__preview { grid-area: preview; width: 72px; height: 72px; }
                .variant-image-picker__select { grid-area: select; }
                }
            }
        </style>
        <div class="admin-module-heading admin-form-hero mb-4">
            <div>
                <p class="admin-form-eyebrow mb-1">Produk</p>
                <h1 class="mb-1">Edit Produk</h1>
                <p class="text-muted mb-0">Kelola harga dan konten Lunarea tanpa mengubah identitas master maupun stok MyLuna.</p>
            </div>
            <a class="btn btn-outline-dark" href="/listproduct">Kembali</a>
        </div>
        <?php if ($mediaMessage = session()->getFlashdata('msg')) { ?>
            <div class="alert alert-info py-2" role="status"><?= esc($mediaMessage); ?></div>
        <?php } ?>
        <div class="luna-locked-panel mb-3">
            <i class="material-icons">sync_alt</i>
            <div>
                <strong>Data jual dan stok memiliki sumber berbeda.</strong>
                <p class="mb-0 text-muted">Harga dan konten yang diedit di sini berlaku untuk Lunarea. Stok tetap mengikuti MyLuna. Identitas, nama master, kategori, varian, berat, dimensi, dan status produk tetap terlindungi dari perubahan tidak sengaja.</p>
            </div>
        </div>
        <form method="post" action="/editproduct/<?= $produk['id']; ?>" enctype="multipart/form-data" class="admin-product-form">
            <?= csrf_field(); ?>
            <nav class="admin-module-section-nav" aria-label="Bagian edit produk">
                <a href="#product-gallery">Foto produk</a><a href="#product-variant-photos">Foto varian</a><a href="#product-master">Harga &amp; konten</a>
            </nav>
            <?= view('partials/admin_product_gallery', ['produk'=>$produk, 'gambar'=>$gambar, 'imageSlotCount'=>$imageSlotCount], ['saveData'=>false]); ?>
            <section class="admin-form-section variant-image-map-section mt-4" id="product-variant-photos">
                <div class="admin-form-section__head"><div><h5>Hubungkan foto dengan varian</h5><p>Setelah foto produk di atas benar, pilih foto utama untuk masing-masing varian di bawah ini.</p></div></div>
                <?php foreach ($variantList as $variantName) {
                    $defaultImageIndex = array_search($variantName, $variantList, true);
                    $defaultImageIndex = $defaultImageIndex === 0 ? 0 : ((int)$jmlVarianProduk + (int)$defaultImageIndex - 1);
                    $selectedImageIndex = isset($variantImageMap[$variantName]) ? (int)$variantImageMap[$variantName] : $defaultImageIndex;
                    $previewKey = 'gambar' . ($selectedImageIndex + 1);
                    $previewSrc = \App\Services\CatalogImageUpload::slotUrl((string)$produk['id'], $selectedImageIndex + 1, $gambar[$previewKey] ?? null);
                ?>
                    <div class="variant-image-picker">
                        <div class="variant-image-picker__title">
                            <strong><?= esc($variantName); ?></strong>
                            <span>Varian ini akan memakai foto yang dipilih di sebelah kanan.</span>
                        </div>
                        <img class="variant-image-picker__preview" src="<?= $previewSrc; ?>" alt="Preview foto untuk <?= esc($variantName); ?>" data-variant-preview="<?= md5($variantName); ?>">
                        <div class="variant-image-picker__select">
                            <select class="form-control variant-image-select" name="variant_image_<?= md5($variantName); ?>" data-preview-target="<?= md5($variantName); ?>" data-variant="<?= esc($variantName); ?>">
                                <?php for ($slot = 0; $slot < $imageSlotCount; $slot++) { ?>
                                    <option value="<?= $slot; ?>" <?= $slot === $selectedImageIndex ? 'selected' : ''; ?>>Pakai Foto <?= $slot + 1; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                <?php } ?>
            </section>
            <div class="admin-product-grid" id="product-master">
                <div class="admin-product-fields">
                    <section class="admin-form-section">
                        <div class="admin-form-section__head"><span>1</span><div><h5>Harga &amp; promo website</h5><p>Perubahan harga berlaku pada transaksi Lunarea; stok tetap mengikuti MyLuna.</p></div></div>
                        <div class="admin-field-grid">
                            <div class="admin-field admin-field--full">
                                <label class="form-label" for="nama">Nama produk</label>
                                <input id="nama" type="text" class="form-control" value="<?= esc($namaProduk); ?>" name="nama" readonly aria-readonly="true" required onchange="isiPencarian(event)" placeholder="Contoh: Lemari Pakaian 3 Pintu Luna">
                                <small>Tulis nama singkat, jelas, dan mudah dicari customer.</small>
                            </div>
                            <div class="admin-field">
                                <label class="form-label" for="harga">Harga jual Lunarea</label>
                                <div class="input-group"><span class="input-group-text">Rp</span><input id="harga" type="number" class="form-control" value="<?= esc($hargaProduk); ?>" name="harga" required placeholder="1250000"></div>
                                <small>Harga jual untuk Lunarea, masukkan angka rupiah tanpa titik/koma.</small>
                            </div>
                            <div class="admin-field website-promo-field">
                                <label class="form-label" for="diskon">Diskon</label>
                                <div class="input-group"><input id="diskon" type="number" class="form-control" value="<?= esc($diskonProduk); ?>" name="diskon" step="any" required onchange="isiPencarian(event)"><span class="input-group-text">%</span></div>
                                <small>Diskon promo khusus Lunarea. Nilai diskon dicatat bersama detail pesanan.</small>
                            </div>
                            <div class="admin-field">
                                <label class="form-label" for="dimensi">Dimensi</label>
                                <input id="dimensi" value="<?= esc($dimensiProduk); ?>" type="text" class="form-control" name="dimensi" readonly aria-readonly="true" required placeholder="80 x 45 x 180">
                                <small>Format disarankan: P x L x T dalam cm.</small>
                            </div>
                            <div class="admin-field">
                                <label class="form-label" for="berat">Berat</label>
                                <div class="input-group"><input id="berat" value="<?= esc($beratProduk); ?>" type="number" class="form-control" name="berat" readonly aria-readonly="true" step="any" required><span class="input-group-text">kg</span></div>
                                <small>Berat untuk estimasi pengiriman.</small>
                            </div>
                            <div class="admin-field">
                                <label class="form-label" for="stok">Stok</label>
                                <input id="stok" type="text" class="form-control" value="<?= esc($stokProduk); ?>" name="stok" readonly aria-readonly="true" required placeholder="ready / 25 / pre-order 7 hari">
                                <small>Bisa angka stok atau status ready/pre-order.</small>
                            </div>
                            <div class="admin-field">
                                <label class="form-label" for="terjual_custom">Terjual custom</label>
                                <input id="terjual_custom" type="number" class="form-control" value="<?= esc($produk['terjual_custom'] ?? 0); ?>" name="terjual_custom" readonly aria-readonly="true" placeholder="Atur manual jumlah terjual">
                                <small>Angka manual untuk tampilan terjual.</small>
                                <input type="hidden" value="<?= esc($produk['terjual'] ?? 0); ?>" name="terjual">
                            </div>
                        </div>
                    </section>

                    <section class="admin-form-section">
                        <div class="admin-form-section__head"><span>2</span><div><h5>Data master &amp; stok</h5><p>Dikelola melalui MyLuna agar pemetaan produk dan stok tetap konsisten.</p></div></div>
                        <div class="admin-field-grid">
                            <div class="admin-field">
                                <label class="form-label" for="kategori">Kategori</label>
                                <input id="kategori" list="kategori-list" type="text" class="form-control" value="<?= esc($kategoriProduk); ?>" name="kategori" readonly aria-readonly="true" required onchange="isiPencarian(event)" placeholder="lemari-dewasa">
                                <small>Contoh: lemari-dewasa, meja-belajar, rak-serbaguna.</small>
                            </div>
                            <div class="admin-field">
                                <label class="form-label" for="subkategori">Sub kategori</label>
                                <input id="subkategori" type="text" class="form-control" value="<?= esc($subkategoriProduk); ?>" name="subkategori" readonly aria-readonly="true" required onchange="isiPencarian(event)" placeholder="lemari-3-pintu">
                                <small>Gunakan huruf kecil dan tanda minus agar URL rapi.</small>
                            </div>
                            <div class="admin-field">
                                <label class="form-label" for="varian">Varian warna/model</label>
                                <input id="varian" type="text" class="form-control" value="<?= esc($varianText); ?>" name="varian" readonly aria-readonly="true" required onchange="isiPencarian(event)" placeholder="Putih, Coklat, Oak">
                                <small>Pisahkan dengan koma. Contoh: Putih, Coklat, Oak.</small>
                            </div>
                            <div class="admin-field">
                                <label class="form-label" for="jml_varian">Jumlah foto per varian</label>
                                <input id="jml_varian" type="number" min="1" class="form-control" value="<?= esc($jmlVarianProduk); ?>" name="jml_varian" readonly aria-readonly="true" required>
                                <small>Jika tiap varian punya 2 foto, isi 2.</small>
                            </div>
                        </div>
                        <datalist id="kategori-list"><option value="lemari-dewasa"></option><option value="lemari-anak"></option><option value="meja-rias"></option><option value="meja-belajar"></option><option value="meja-tv"></option><option value="meja-tulis"></option><option value="meja-komputer"></option><option value="rak-sepatu"></option><option value="rak-besi"></option><option value="rak-serbaguna"></option><option value="kursi"></option></datalist>
                    </section>

                    <section class="admin-form-section">
                        <div class="admin-form-section__head"><span>3</span><div><h5>Link marketplace &amp; video</h5><p>Link yang ditampilkan pada halaman produk Lunarea.</p></div></div>
                        <div class="admin-field-grid">
                            <div class="admin-field"><label class="form-label" for="shopee">Link Shopee</label><input id="shopee" type="url" class="form-control" value="<?= esc($produk['shopee'] ?? ''); ?>" name="shopee" placeholder="https://shopee.co.id/..."><small>Link tombol Shopee di halaman detail.</small></div>
                            <div class="admin-field"><label class="form-label" for="tokped">Link Tokopedia</label><input id="tokped" type="url" class="form-control" value="<?= esc($produk['tokped'] ?? ''); ?>" name="tokped" placeholder="https://tokopedia.com/..."><small>Link tombol Tokopedia.</small></div>
                            <div class="admin-field"><label class="form-label" for="tiktok">Link Tiktok Shop</label><input id="tiktok" type="url" class="form-control" value="<?= esc($produk['tiktok'] ?? ''); ?>" name="tiktok" placeholder="https://www.tiktok.com/..."><small>Link tombol Tiktok Shop.</small></div>
                            <div class="admin-field"><label class="form-label" for="youtube">Link Youtube</label><input id="youtube" type="url" class="form-control" value="<?= esc($produk['youtube'] ?? ''); ?>" name="youtube" placeholder="https://youtube.com/..."><small>Video review/perakitan produk.</small></div>
                        </div>
                    </section>

                    <section class="admin-form-section">
                        <div class="admin-form-section__head"><span>4</span><div><h5>Deskripsi &amp; pencarian</h5><p>Konten khusus halaman produk dan pencarian Lunarea.</p></div></div>
                        <div class="admin-field-grid">
                            <div class="admin-field admin-field--full">
                                <label class="form-label" for="deskripsi">Deskripsi produk</label>
                                <textarea id="deskripsi" class="form-control admin-description-input" name="deskripsi" placeholder="Ceritakan bahan, ukuran, fungsi, dan keunggulan produk dengan bahasa yang mudah dipahami."><?= esc($deskripsiProduk); ?></textarea>
                                <small>Ketik seperti biasa. Tekan Enter untuk membuat paragraf baru (dengan jarak), atau Shift+Enter untuk pindah baris. Pilih paragraf/judul dan gunakan tombol format di editor—tidak perlu menulis kode.</small>
                            </div>
                            <details class="admin-description-advanced admin-field--full">
                                <summary>Pengaturan lanjutan: teks deskripsi untuk pencarian</summary>
                                <div class="admin-field">
                                    <label class="form-label" for="deskripsi_nonhtml">Teks deskripsi tanpa format</label>
                                    <textarea id="deskripsi_nonhtml" class="form-control" name="deskripsi_nonhtml" placeholder="Versi teks biasa dari deskripsi produk."><?= esc($deskripsiNonhtmlProduk); ?></textarea>
                                    <small>Biasanya terisi otomatis dari deskripsi produk. Ubah hanya jika teks pencarian perlu disesuaikan.</small>
                                </div>
                            </details>
                            <div class="admin-field admin-field--full">
                                <label class="form-label" for="pencarian">Kata kunci pencarian</label>
                                <div class="input-group">
                                    <input id="pencarian" type="text" class="form-control" value="<?= esc($pencarianProduk); ?>" name="pencarian" placeholder="Contoh: lemari pakaian, minimalis, putih">
                                    <button class="btn btn-outline-dark" type="button" id="generateSearchKeyword">Buat otomatis</button>
                                </div>
                                <small>Opsional. Klik “Buat otomatis” untuk menyusun kata kunci dari nama, kategori, varian, dan deskripsi produk.</small>
                            </div>
                        </div>
                    </section>
                </div>

                <aside class="admin-product-preview">
                    <div class="admin-preview-card">
                        <p class="admin-form-eyebrow mb-2">Live preview</p>
                        <div class="add-gambar admin-preview-image mb-3"><img src="/img/nopic.jpg" id="addProduct_PreviewUtama" alt="Preview produk"></div>
                        <div class="admin-preview-meta"><span id="previewCategory">Kategori produk</span><span id="previewStock">Stok</span></div>
                        <h3 id="previewName">Nama produk</h3>
                        <p class="admin-preview-price" id="previewPrice">Rp0</p>
                        <p class="admin-preview-spec" id="previewSpec">Dimensi dan berat.</p>
                        <div class="admin-preview-variants" id="previewVariants">Varian: -</div>
                        <div class="admin-description-preview" id="previewDescription">Preview deskripsi.</div>
                    </div>
                </aside>
            </div>
            <div class="d-grid gap-2 mt-3 admin-form-actions-sticky">
                <button class="btn btn-primary1" type="submit">Simpan Perubahan</button>
                <a class="btn btn-outline-dark" href="/listproduct">Batal</a>
            </div>
        </form>
    </div>
</div>
<script>
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
    if (window.bootstrap?.Tooltip) [...tooltipTriggerList].forEach(el => new bootstrap.Tooltip(el));
    const elmFotoVarian = document.getElementById('foto-varian');
    const elmKategori = document.querySelector('input[name="kategori"]');
    const elmSubkategori = document.querySelector('input[name="subkategori"]');
    const elmPencarian = document.querySelector('input[name="pencarian"]');
    const elmVarian = document.querySelector('input[name="varian"]');
    const elmJmlvarian = document.querySelector('input[name="jml_varian"]');
    const ambilVarian = "<?= count(json_decode($produk['varian'], true)); ?>";
    const ambilJmlvarian = "<?= $produk['jml_varian'] ?>";
    let varian = Number(ambilVarian) || 1;
    let jmlVarian = Number(ambilJmlvarian) || 1;
    let hasilVarian = jmlVarian + varian - 1;
    const existingVariants = <?= json_encode($variantList, JSON_UNESCAPED_UNICODE); ?>;

    const websiteEditableFields = new Set(['harga', 'diskon', 'deskripsi', 'deskripsi_nonhtml', 'pencarian', 'shopee', 'tokped', 'tiktok', 'youtube']);
    document.querySelectorAll('.admin-product-fields input, .admin-product-fields textarea').forEach((field) => {
        if (websiteEditableFields.has(field.name)) {
            field.readOnly = false;
            field.removeAttribute('aria-readonly');
            field.removeAttribute('tabindex');
            field.classList.remove('bg-light');
            field.setAttribute('title', 'Dikelola khusus untuk Lunarea');
            if (['harga', 'diskon'].includes(field.name)) field.required = true;
            else field.required = false;
            return;
        }
        field.readOnly = true;
        field.required = false;
        field.setAttribute('aria-readonly', 'true');
        field.setAttribute('title', 'Readonly: ubah data ini dari Luna Sistem');
        field.classList.add('bg-light');
        field.setAttribute('tabindex', '-1');
    });
    document.querySelectorAll('.admin-product-fields label').forEach((label) => {
        if (label.querySelector('.readonly-badge') || websiteEditableFields.has(label.getAttribute('for'))) return;
        const badge = document.createElement('span');
        badge.className = 'readonly-badge';
        badge.innerHTML = '<i class="material-icons">lock</i> Readonly';
        label.appendChild(badge);
    });
    const diskonLabel = document.querySelector('label[for="diskon"]');
    if (diskonLabel && !diskonLabel.querySelector('.readonly-badge')) {
        const promoBadge = document.createElement('span');
        promoBadge.className = 'readonly-badge';
        promoBadge.style.background = '#fef3c7';
        promoBadge.style.color = '#92400e';
        promoBadge.innerHTML = '<i class="material-icons">local_offer</i> Promo Website';
        diskonLabel.appendChild(promoBadge);
    }
    function isiPencarian(e) {
        if (e.srcElement.value != '') {
            if (e.srcElement.name == 'nama') elmPencarian.value += `${e.srcElement.value} `;
            else if (e.srcElement.name == 'kategori') elmPencarian.value += `${e.srcElement.value} elegan ${e.srcElement.value} simpel ${e.srcElement.value} minimalis ${e.srcElement.value} estetik ${e.srcElement.value} modern `;
            else if (e.srcElement.name == 'subkategori') { const subkategorinya = e.srcElement.value.replace(/-/g, ' '); elmPencarian.value += `${subkategorinya} elegan ${subkategorinya} simpel ${subkategorinya} minimalis ${subkategorinya} estetik ${subkategorinya} modern `; }
            else if (e.srcElement.name == 'varian') { const arrVar = e.srcElement.value.split(','); const subkategorinya = elmSubkategori.value.replace(/-/g, ' '); arrVar.forEach((va) => { elmPencarian.value += `${elmKategori.value} ${va} ${subkategorinya} ${va} `; }) }
            else if (e.srcElement.name == 'diskon') { const subkategorinya = elmSubkategori.value.replace(/-/g, ' '); if (Number(e.srcElement.value) > 0) elmPencarian.value += `${elmKategori.value} promo ${elmKategori.value} diskon ${subkategorinya} promo ${subkategorinya} diskon `; }
        }
    }

    function stripHtml(html) {
        const tmp = document.createElement('div');
        tmp.innerHTML = html || '';
        return (tmp.textContent || tmp.innerText || '').replace(/\s+/g, ' ').trim();
    }

    function getDescriptionContent() {
        if (window.tinymce && tinymce.get('deskripsi')) return tinymce.get('deskripsi').getContent();
        return document.querySelector('[name="deskripsi"]')?.value || '';
    }

    function syncPlainDescription(force = false) {
        const plainField = document.querySelector('[name="deskripsi_nonhtml"]');
        if (!plainField) return;
        const plain = stripHtml(getDescriptionContent());
        if (force || !plainField.dataset.touched || plainField.value.trim() === '') plainField.value = plain;
    }

    function generateSearchKeyword() {
        const get = (name) => document.querySelector(`[name="${name}"]`)?.value?.trim() || '';
        const sub = get('subkategori').replace(/-/g, ' ');
        const kat = get('kategori').replace(/-/g, ' ');
        const variants = get('varian').split(',').map(v => v.trim()).filter(Boolean).join(' ');
        const descWords = stripHtml(getDescriptionContent()).split(' ').slice(0, 24).join(' ');
        const keywords = `${get('nama')} ${kat} ${sub} ${variants} ${kat} minimalis ${kat} modern ${sub} elegan ${sub} simpel ${descWords}`.replace(/\s+/g, ' ').trim();
        document.querySelector('[name="pencarian"]').value = keywords;
    }

    function formatRupiah(value) { return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(Number(value || 0)); }
    function updateAdminProductPreview() {
        const get = (name) => document.querySelector(`[name="${name}"]`)?.value?.trim() || '';
        const harga = Number(get('harga') || 0);
        const diskon = Number(get('diskon') || 0);
        const finalPrice = diskon > 0 ? harga - (harga * diskon / 100) : harga;
        document.getElementById('previewName').textContent = get('nama') || 'Nama produk';
        document.getElementById('previewCategory').textContent = get('kategori') || 'Kategori produk';
        document.getElementById('previewStock').textContent = get('stok') || 'Stok';
        document.getElementById('previewPrice').textContent = `${formatRupiah(finalPrice)}${diskon > 0 ? ' • Diskon ' + diskon + '%' : ''}`;
        document.getElementById('previewSpec').textContent = `${get('dimensi') || 'Dimensi'} cm • ${get('berat') || 'Berat'} kg`;
        document.getElementById('previewVariants').textContent = `Varian: ${get('varian') || '-'}`;
        document.getElementById('previewDescription').textContent = stripHtml(getDescriptionContent()) || 'Preview deskripsi.';
    }

    document.querySelectorAll('.admin-product-form input, .admin-product-form textarea').forEach((field) => { field.addEventListener('input', updateAdminProductPreview); field.addEventListener('change', updateAdminProductPreview); });
    document.querySelector('[name="deskripsi_nonhtml"]').addEventListener('input', (e) => e.target.dataset.touched = 'true');
    document.getElementById('generateSearchKeyword')?.addEventListener('click', generateSearchKeyword);
    document.querySelector('.admin-product-form').addEventListener('submit', () => {
        if (window.tinymce) tinymce.triggerSave();
        syncPlainDescription(false);
        if (!document.querySelector('[name="pencarian"]').value.trim()) generateSearchKeyword();
    });
    if (window.tinymce) {
        tinymce.init({
            selector: '#deskripsi',
            height: 460,
            min_height: 460,
            resize: false,
            menubar: false,
            readonly: false,
            plugins: 'lists link',
            toolbar: 'undo redo | blocks | bold italic underline | bullist numlist | link | removeformat',
            block_formats: 'Paragraf=p; Judul bagian=h2; Subjudul=h3; Judul kecil=h4',
            content_style: 'body { font-family: Arial, sans-serif; font-size: 14px; line-height: 1.7; } p { margin: 0 0 1em; } h2, h3, h4 { line-height: 1.35; margin: 1em 0 .45em; }',
            setup: function(editor) {
                editor.on('input change keyup setcontent', function() {
                    syncPlainDescription();
                    updateAdminProductPreview();
                });
            }
        });
    }

    updateAdminProductPreview();
</script>
<?= $this->endSection(); ?>
