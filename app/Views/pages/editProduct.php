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
$imageSlotCount = min(50, count($variantList) + max((int)$jmlVarianProduk, 1) - 1);
$variantImageMap = is_array($variantImageMap ?? null) ? $variantImageMap : [];
?>
<div class="konten">
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
                border: 2px solid #f59e0b;
                border-radius: 14px;
                padding: 12px;
                background: #fffbeb;
            }
            .website-promo-field .form-control {
                background: #fff !important;
                border: 1px solid #f59e0b !important;
                color: #111827 !important;
                cursor: text !important;
            }
            .variant-image-picker {
                display: grid;
                gap: 12px;
                padding: 12px;
                border: 1px solid #dbeafe;
                border-radius: 16px;
                background: #fff;
            }
            .variant-image-picker__top {
                display: grid;
                grid-template-columns: 92px minmax(0, 1fr);
                gap: 12px;
                align-items: center;
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
            .variant-image-picker__hint {
                display: block;
                margin-top: 6px;
                color: #64748b;
                font-size: 12px;
            }
            .variant-image-picker__choice-grid {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(74px, 1fr));
                gap: 8px;
            }
            .variant-image-choice {
                position: relative;
                display: block;
                cursor: pointer;
                border: 2px solid #e5e7eb;
                border-radius: 12px;
                padding: 6px;
                background: #fff;
                transition: border-color .18s ease, box-shadow .18s ease, transform .18s ease;
            }
            .variant-image-choice:hover {
                border-color: #93c5fd;
                box-shadow: 0 8px 18px rgba(37, 99, 235, .12);
                transform: translateY(-1px);
            }
            .variant-image-choice input {
                position: absolute;
                opacity: 0;
                pointer-events: none;
            }
            .variant-image-choice img {
                width: 100%;
                aspect-ratio: 1 / 1;
                object-fit: cover;
                border-radius: 9px;
                background: #f8fafc;
                display: block;
            }
            .variant-image-choice span {
                display: block;
                margin-top: 5px;
                text-align: center;
                font-size: 11px;
                font-weight: 700;
                color: #475569;
            }
            .variant-image-choice:has(input:checked) {
                border-color: #16a34a;
                box-shadow: 0 0 0 3px rgba(22, 163, 74, .14);
            }
            .variant-image-choice:has(input:checked)::after {
                content: "check";
                font-family: "Material Icons";
                position: absolute;
                right: 4px;
                top: 4px;
                width: 22px;
                height: 22px;
                display: grid;
                place-items: center;
                border-radius: 999px;
                background: #16a34a;
                color: #fff;
                font-size: 16px;
                line-height: 1;
            }
            @media (max-width: 575.98px) {
                .variant-image-picker__top {
                    grid-template-columns: 72px minmax(0, 1fr);
                }
                .variant-image-picker__preview {
                    width: 72px;
                    height: 72px;
                }
            }
        </style>
        <div class="admin-form-hero mb-4">
            <div>
                <p class="admin-form-eyebrow mb-1">Produk</p>
                <h1 class="mb-1">Edit Foto Produk</h1>
                <p class="text-muted mb-0">Data utama produk dikunci agar tetap satu jalur dari Luna Sistem. Di website hanya foto produk yang bisa diperbarui.</p>
            </div>
            <a class="btn btn-outline-dark" href="/listproduct">Kembali</a>
        </div>
        <?php if (session()->getFlashdata('msg')) { ?>
            <div class="alert alert-warning py-2"><?= session()->getFlashdata('msg'); ?></div>
        <?php } ?>
        <div class="luna-locked-panel mb-3">
            <i class="material-icons">lock</i>
            <div>
                <strong>Mode satu jalur aktif.</strong>
                <p class="mb-0 text-muted">Nama, harga normal, stok, varian, kategori, status aktif, berat, dimensi, deskripsi, dan link marketplace wajib diedit dari Luna Sistem. Admin website hanya bisa upload/mengganti foto dan mengatur diskon promo website.</p>
            </div>
        </div>
        <form method="post" action="/editproduct/<?= $produk['id']; ?>" enctype="multipart/form-data" class="admin-product-form">
            <?= csrf_field(); ?>
            <div class="admin-product-grid">
                <div class="admin-product-fields">
                    <section class="admin-form-section">
                        <div class="admin-form-section__head"><span>1</span><div><h5>Informasi utama</h5><p>Terkunci, mengikuti Luna Sistem.</p></div></div>
                        <div class="admin-field-grid">
                            <div class="admin-field admin-field--full">
                                <label class="form-label" for="nama">Nama produk</label>
                                <input id="nama" type="text" class="form-control" value="<?= esc($namaProduk); ?>" name="nama" required onchange="isiPencarian(event)" placeholder="Contoh: Lemari Pakaian 3 Pintu Luna">
                                <small>Tulis nama singkat, jelas, dan mudah dicari customer.</small>
                            </div>
                            <div class="admin-field">
                                <label class="form-label" for="harga">Harga</label>
                                <div class="input-group"><span class="input-group-text">Rp</span><input id="harga" type="number" class="form-control" value="<?= esc($hargaProduk); ?>" name="harga" required placeholder="1250000"></div>
                                <small>Masukkan angka tanpa titik/koma.</small>
                            </div>
                            <div class="admin-field website-promo-field">
                                <label class="form-label" for="diskon">Diskon</label>
                                <div class="input-group"><input id="diskon" type="number" class="form-control" value="<?= esc($diskonProduk); ?>" name="diskon" step="any" required onchange="isiPencarian(event)"><span class="input-group-text">%</span></div>
                                <small><b>Bisa diedit di website.</b> Harga normal tetap dari Luna Sistem, diskon ini hanya promo website dan dikirim ke Luna Sistem sebagai diskon transaksi.</small>
                            </div>
                            <div class="admin-field">
                                <label class="form-label" for="dimensi">Dimensi</label>
                                <input id="dimensi" value="<?= esc($dimensiProduk); ?>" type="text" class="form-control" name="dimensi" required placeholder="80 x 45 x 180">
                                <small>Format disarankan: P x L x T dalam cm.</small>
                            </div>
                            <div class="admin-field">
                                <label class="form-label" for="berat">Berat</label>
                                <div class="input-group"><input id="berat" value="<?= esc($beratProduk); ?>" type="number" class="form-control" name="berat" step="any" required><span class="input-group-text">kg</span></div>
                                <small>Berat untuk estimasi pengiriman.</small>
                            </div>
                            <div class="admin-field">
                                <label class="form-label" for="stok">Stok</label>
                                <input id="stok" type="text" class="form-control" value="<?= esc($stokProduk); ?>" name="stok" required placeholder="ready / 25 / pre-order 7 hari">
                                <small>Bisa angka stok atau status ready/pre-order.</small>
                            </div>
                            <div class="admin-field">
                                <label class="form-label" for="terjual_custom">Terjual custom</label>
                                <input id="terjual_custom" type="number" class="form-control" value="<?= esc($produk['terjual_custom'] ?? 0); ?>" name="terjual_custom" placeholder="Atur manual jumlah terjual">
                                <small>Angka manual untuk tampilan terjual.</small>
                                <input type="hidden" value="<?= esc($produk['terjual'] ?? 0); ?>" name="terjual">
                            </div>
                        </div>
                    </section>

                    <section class="admin-form-section">
                        <div class="admin-form-section__head"><span>2</span><div><h5>Kategori & varian</h5><p>Terkunci, mengikuti Luna Sistem. Slot foto otomatis mengikuti varian yang sudah tersimpan.</p></div></div>
                        <div class="admin-field-grid">
                            <div class="admin-field">
                                <label class="form-label" for="kategori">Kategori</label>
                                <input id="kategori" list="kategori-list" type="text" class="form-control" value="<?= esc($kategoriProduk); ?>" name="kategori" required onchange="isiPencarian(event)" placeholder="lemari-dewasa">
                                <small>Contoh: lemari-dewasa, meja-belajar, rak-serbaguna.</small>
                            </div>
                            <div class="admin-field">
                                <label class="form-label" for="subkategori">Sub kategori</label>
                                <input id="subkategori" type="text" class="form-control" value="<?= esc($subkategoriProduk); ?>" name="subkategori" required onchange="isiPencarian(event)" placeholder="lemari-3-pintu">
                                <small>Gunakan huruf kecil dan tanda minus agar URL rapi.</small>
                            </div>
                            <div class="admin-field">
                                <label class="form-label" for="varian">Varian warna/model</label>
                                <input id="varian" type="text" class="form-control" value="<?= esc($varianText); ?>" name="varian" required onchange="isiPencarian(event)" placeholder="Putih, Coklat, Oak">
                                <small>Pisahkan dengan koma. Contoh: Putih, Coklat, Oak.</small>
                            </div>
                            <div class="admin-field">
                                <label class="form-label" for="jml_varian">Jumlah foto per varian</label>
                                <input id="jml_varian" type="number" min="1" class="form-control" value="<?= esc($jmlVarianProduk); ?>" name="jml_varian" required>
                                <small>Jika tiap varian punya 2 foto, isi 2.</small>
                            </div>
                        </div>
                        <datalist id="kategori-list"><option value="lemari-dewasa"></option><option value="lemari-anak"></option><option value="meja-rias"></option><option value="meja-belajar"></option><option value="meja-tv"></option><option value="meja-tulis"></option><option value="meja-komputer"></option><option value="rak-sepatu"></option><option value="rak-besi"></option><option value="rak-serbaguna"></option><option value="kursi"></option></datalist>
                    </section>

                    <section class="admin-form-section">
                        <div class="admin-form-section__head"><span>3</span><div><h5>Link marketplace & video</h5><p>Terkunci, mengikuti Luna Sistem.</p></div></div>
                        <div class="admin-field-grid">
                            <div class="admin-field"><label class="form-label" for="shopee">Link Shopee</label><input id="shopee" type="url" class="form-control" value="<?= esc($produk['shopee'] ?? ''); ?>" name="shopee" placeholder="https://shopee.co.id/..."><small>Link tombol Shopee di halaman detail.</small></div>
                            <div class="admin-field"><label class="form-label" for="tokped">Link Tokopedia</label><input id="tokped" type="url" class="form-control" value="<?= esc($produk['tokped'] ?? ''); ?>" name="tokped" placeholder="https://tokopedia.com/..."><small>Link tombol Tokopedia.</small></div>
                            <div class="admin-field"><label class="form-label" for="tiktok">Link Tiktok Shop</label><input id="tiktok" type="url" class="form-control" value="<?= esc($produk['tiktok'] ?? ''); ?>" name="tiktok" placeholder="https://www.tiktok.com/..."><small>Link tombol Tiktok Shop.</small></div>
                            <div class="admin-field"><label class="form-label" for="youtube">Link Youtube</label><input id="youtube" type="url" class="form-control" value="<?= esc($produk['youtube'] ?? ''); ?>" name="youtube" placeholder="https://youtube.com/..."><small>Video review/perakitan produk.</small></div>
                        </div>
                    </section>

                    <section class="admin-form-section">
                        <div class="admin-form-section__head"><span>4</span><div><h5>Deskripsi & pencarian</h5><p>Terkunci, mengikuti Luna Sistem.</p></div></div>
                        <div class="admin-field-grid">
                            <div class="admin-field admin-field--full">
                                <label class="form-label" for="deskripsi">Deskripsi HTML</label>
                                <textarea id="deskripsi" class="form-control admin-description-input" name="deskripsi" required><?= $deskripsiProduk; ?></textarea>
                                <small>Tulis/edit seperti di artikel. Admin bisa bold, list, heading, dan link tanpa mengetik tag HTML manual.</small>
                            </div>
                            <div class="admin-field admin-field--full">
                                <label class="form-label" for="deskripsi_nonhtml">Deskripsi tanpa HTML</label>
                                <textarea id="deskripsi_nonhtml" class="form-control" name="deskripsi_nonhtml" required><?= esc($deskripsiNonhtmlProduk); ?></textarea>
                                <small>Otomatis bisa mengikuti deskripsi tanpa format HTML, tetap bisa diedit manual.</small>
                            </div>
                            <div class="admin-field admin-field--full">
                                <label class="form-label" for="pencarian">Keyword pencarian</label>
                                <div class="input-group">
                                    <input id="pencarian" type="text" class="form-control" value="<?= esc($pencarianProduk); ?>" name="pencarian" required placeholder="lemari pakaian minimalis modern putih murah">
                                    <button class="btn btn-outline-dark" type="button" id="generateSearchKeyword">Generate</button>
                                </div>
                                <small>Bisa otomatis dibuat dari nama, kategori, subkategori, varian, dan deskripsi.</small>
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
                    <section class="admin-form-section admin-image-section editable-photo-panel mt-3">
                        <div class="admin-form-section__head"><span>5</span><div><h5>Gambar produk <span class="badge bg-success">Bisa diedit</span></h5><p>Upload gambar baru hanya jika ingin mengganti. Slot mengikuti varian dari Luna Sistem.</p></div></div>
                        <div id="foto-varian" class="d-flex gap-2"></div>
                    </section>
                    <section class="admin-form-section mt-3">
                        <div class="admin-form-section__head"><span>6</span><div><h5>Relasi foto ke varian</h5><p>Pilih foto utama yang akan tampil ketika pembeli memilih varian tertentu.</p></div></div>
                        <div class="admin-field-grid">
                            <?php foreach ($variantList as $variantName) {
                                $defaultImageIndex = array_search($variantName, $variantList, true);
                                $defaultImageIndex = $defaultImageIndex === 0 ? 0 : ((int)$jmlVarianProduk + (int)$defaultImageIndex - 1);
                                $selectedImageIndex = isset($variantImageMap[$variantName]) ? (int)$variantImageMap[$variantName] : $defaultImageIndex;
                            ?>
                                <div class="admin-field">
                                    <label class="form-label">Varian: <?= esc($variantName); ?></label>
                                    <div class="variant-image-picker">
                                        <?php
                                            $previewKey = 'gambar' . ($selectedImageIndex + 1);
                                            $previewSrc = !empty($gambar[$previewKey]) ? ('data:image/webp;base64,' . base64_encode($gambar[$previewKey])) : '/img/nopic.jpg';
                                        ?>
                                        <div class="variant-image-picker__top">
                                            <img class="variant-image-picker__preview" src="<?= $previewSrc; ?>" alt="Preview foto untuk <?= esc($variantName); ?>" data-variant-preview="<?= md5($variantName); ?>">
                                            <div class="variant-image-picker__meta">
                                                <strong>Foto yang tampil untuk varian ini</strong>
                                                <span class="variant-image-picker__hint">Klik salah satu thumbnail di bawah. Yang centang hijau adalah foto aktif untuk varian <?= esc($variantName); ?>.</span>
                                            </div>
                                        </div>
                                        <div class="variant-image-picker__choice-grid" role="radiogroup" aria-label="Pilih foto untuk varian <?= esc($variantName); ?>">
                                            <?php for ($slot = 0; $slot < $imageSlotCount; $slot++) {
                                                $thumbKey = 'gambar' . ($slot + 1);
                                                $thumbSrc = !empty($gambar[$thumbKey]) ? ('data:image/webp;base64,' . base64_encode($gambar[$thumbKey])) : '/img/nopic.jpg';
                                            ?>
                                                <label class="variant-image-choice">
                                                    <input type="radio" class="variant-image-radio" name="variant_image_<?= md5($variantName); ?>" value="<?= $slot; ?>" data-preview-target="<?= md5($variantName); ?>" data-variant="<?= esc($variantName); ?>" data-image-index="<?= $slot; ?>" <?= $slot === $selectedImageIndex ? 'checked' : ''; ?>>
                                                    <img src="<?= $thumbSrc; ?>" alt="Foto <?= $slot + 1; ?> untuk opsi varian <?= esc($variantName); ?>" data-image-choice-preview="<?= $slot; ?>">
                                                    <span>Foto <?= $slot + 1; ?></span>
                                                </label>
                                            <?php } ?>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                    </section>
                    <div class="d-grid gap-2 mt-3">
                        <button class="btn btn-primary1" type="submit">Simpan Foto</button>
                        <a class="btn btn-outline-dark" href="/listproduct">Batal</a>
                    </div>
                </aside>
            </div>
        </form>
    </div>
</div>
<script>
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))
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
    const existingProductImages = <?= json_encode((function () use ($gambar, $imageSlotCount) {
        $items = [];
        for ($i = 1; $i <= $imageSlotCount; $i++) {
            $key = 'gambar' . $i;
            $items[$i - 1] = !empty($gambar[$key]) ? ('data:image/webp;base64,' . base64_encode($gambar[$key])) : '/img/nopic.jpg';
        }
        return $items;
    })(), JSON_UNESCAPED_SLASHES); ?>;
    const existingVariants = <?= json_encode($variantList, JSON_UNESCAPED_UNICODE); ?>;

    document.querySelectorAll('.admin-product-fields input, .admin-product-fields textarea').forEach((field) => {
        if (field.name === 'diskon') {
            field.required = true;
            field.removeAttribute('readonly');
            field.removeAttribute('aria-readonly');
            field.setAttribute('title', 'Diskon promo khusus website');
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
        if (label.querySelector('.readonly-badge') || label.getAttribute('for') === 'diskon') return;
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
    document.getElementById('generateSearchKeyword')?.setAttribute('disabled', 'disabled');

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
        document.getElementById('previewDescription').innerHTML = getDescriptionContent() || 'Preview deskripsi.';
    }

    document.querySelectorAll('.admin-product-form input, .admin-product-form textarea').forEach((field) => { field.addEventListener('input', updateAdminProductPreview); field.addEventListener('change', updateAdminProductPreview); });
    document.querySelector('[name="deskripsi_nonhtml"]').addEventListener('input', (e) => e.target.dataset.touched = 'true');
    document.getElementById('generateSearchKeyword')?.addEventListener('click', generateSearchKeyword);
    document.querySelector('.admin-product-form').addEventListener('submit', () => {
        if (window.tinymce) tinymce.triggerSave();
        syncPlainDescription(true);
        if (!document.querySelector('[name="pencarian"]').value.trim()) generateSearchKeyword();
    });
    if (window.tinymce) {
        tinymce.init({
            selector: '#deskripsi',
            height: 320,
            menubar: false,
            readonly: true,
            toolbar: false,
            setup: function(editor) {
                editor.on('input change keyup setcontent', function() {
                    syncPlainDescription();
                    updateAdminProductPreview();
                });
            }
        });
    }

    function syncImageInputs() {
        const varianArray = (elmVarian.value || '').split(',').map(v => v.trim()).filter(Boolean);
        varian = Math.max(varianArray.length, 1);
        jmlVarian = Math.max(Number(elmJmlvarian.value || 1), 1);
        hasilVarian = jmlVarian + varian - 1;
        inputElement(hasilVarian);
        updateAdminProductPreview();
    }

    function inputElement(hasilVarian) {
        elmFotoVarian.innerHTML = '';
        for (let i = 1; i <= hasilVarian; i++) {
            const cardVarian = document.createElement('div');
            cardVarian.classList.add('input-group-gambar');
            const cardAnkvarian = document.createElement('div');
            cardAnkvarian.classList.add('addProduct_Input');
            cardAnkvarian.setAttribute('id', 'addProduct_Input' + i);
            cardAnkvarian.setAttribute('data-bs-toggle', 'tooltip');
            cardAnkvarian.setAttribute('data-bs-placement', 'top');
            cardAnkvarian.setAttribute('data-bs-title', 'Upload gambar baru ke-' + i);
            const cardlabel = document.createElement('label');
            cardlabel.classList.add('input-gambar-label');
            cardlabel.setAttribute('for', 'addProduct_InputGambar' + i);
            const cardIlabel = document.createElement('i');
            cardIlabel.classList.add('material-icons');
            cardIlabel.innerHTML = 'add_photo_alternate';
            const cardinput = document.createElement('input');
            cardinput.classList.add('input-gambar');
            cardinput.setAttribute('type', 'file');
            cardinput.setAttribute('id', 'addProduct_InputGambar' + i);
            cardinput.setAttribute('name', 'gambar' + i);
            cardinput.setAttribute('accept', 'image/*');
            const cardImg = document.createElement('img');
            cardImg.src = existingProductImages[i - 1] || '/img/nopic.jpg';
            cardImg.setAttribute('id', 'addProduct_PreviewGambar' + i);
            cardImg.classList.add('addProduct_Preview');
            const slotCaption = document.createElement('small');
            slotCaption.className = 'd-block text-center text-muted mt-1';
            const usedBy = [...document.querySelectorAll('.variant-image-radio:checked')]
                .filter(input => Number(input.value) === i - 1)
                .map(input => input.dataset.variant)
                .join(', ');
            slotCaption.textContent = usedBy ? `Foto ${i}: ${usedBy}` : `Foto ${i}`;
            cardlabel.appendChild(cardIlabel); cardAnkvarian.appendChild(cardlabel); cardAnkvarian.appendChild(cardinput); cardVarian.appendChild(cardAnkvarian); cardVarian.appendChild(cardImg); cardVarian.appendChild(slotCaption); elmFotoVarian.appendChild(cardVarian);
        }
        const addProduct_inputGambar = document.querySelectorAll('.input-gambar');
        const addProduct_previewGambar = document.querySelectorAll('.addProduct_Preview');
        const addProduct_input = document.querySelectorAll('.addProduct_Input');
        const addProduct_previewUtama = document.getElementById('addProduct_PreviewUtama');
        addProduct_inputGambar.forEach((item, index) => { item.addEventListener('change', () => { const file = addProduct_inputGambar[index].files[0]; if (!file) return; const blobUrl = URL.createObjectURL(file); existingProductImages[index] = blobUrl; addProduct_previewGambar[index].src = blobUrl; addProduct_previewUtama.src = blobUrl; document.querySelectorAll(`[data-image-choice-preview="${index}"]`).forEach(img => img.src = blobUrl); addProduct_previewGambar[index].style.display = 'block'; addProduct_input[index].style.display = 'none'; refreshVariantImagePreviews(); inputElement(hasilVarian); }) })
    }

    function refreshVariantImagePreviews() {
        document.querySelectorAll('.variant-image-radio:checked').forEach((input) => {
            const target = input.dataset.previewTarget;
            const preview = document.querySelector(`[data-variant-preview="${target}"]`);
            if (!preview) return;
            const imageIndex = Number(input.value || 0);
            preview.src = existingProductImages[imageIndex] || '/img/nopic.jpg';
            preview.alt = `Preview ${input.dataset.variant || 'varian'} dari Foto ${imageIndex + 1}`;
        });
    }

    syncImageInputs();
    updateAdminProductPreview();
    document.querySelectorAll('.variant-image-radio').forEach((input) => {
        input.addEventListener('change', () => {
            refreshVariantImagePreviews();
            inputElement(hasilVarian);
        });
    });
    refreshVariantImagePreviews();
</script>
<?= $this->endSection(); ?>
