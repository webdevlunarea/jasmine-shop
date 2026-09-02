<?= $this->extend('layout/template'); ?>
<?= $this->section('content'); ?>
<script src="https://cdn.tiny.cloud/1/<?= $tinymce; ?>/tinymce/7/tinymce.min.js" referrerpolicy="origin"></script>
<div class="konten">
    <div class="container admin-product-editor">
        <div class="admin-form-hero mb-4">
            <div>
                <p class="admin-form-eyebrow mb-1">Produk</p>
                <h1 class="mb-1">Tambah Produk</h1>
                <p class="text-muted mb-0">Isi data produk dari atas ke bawah. Preview di kanan akan berubah otomatis saat admin mengetik.</p>
            </div>
            <a class="btn btn-outline-dark" href="/listproduct">Kembali</a>
        </div>
        <form method="post" action="/addproduct" enctype="multipart/form-data" class="admin-product-form">
            <?= csrf_field(); ?>
            <div class="admin-product-grid">
                <div class="admin-product-fields">
                    <section class="admin-form-section">
                        <div class="admin-form-section__head">
                            <span>1</span>
                            <div>
                                <h5>Informasi utama</h5>
                                <p>Data yang pertama kali dilihat customer di halaman produk.</p>
                            </div>
                        </div>
                        <div class="admin-field-grid">
                            <div class="admin-field admin-field--full">
                                <label class="form-label" for="nama">Nama produk</label>
                                <input id="nama" type="text" class="form-control" name="nama" required placeholder="Contoh: Lemari Pakaian 3 Pintu Luna" data-preview="name">
                                <small>Tulis nama singkat, jelas, dan mudah dicari customer.</small>
                            </div>
                            <div class="admin-field">
                                <label class="form-label" for="harga">Harga</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input id="harga" type="number" class="form-control" name="harga" required placeholder="1250000" data-preview="price">
                                </div>
                                <small>Masukkan angka tanpa titik/koma.</small>
                            </div>
                            <div class="admin-field">
                                <label class="form-label" for="diskon">Diskon</label>
                                <div class="input-group">
                                    <input id="diskon" type="number" class="form-control" name="diskon" step="any" required value="0" placeholder="0" data-preview="discount">
                                    <span class="input-group-text">%</span>
                                </div>
                                <small>Isi 0 kalau produk tidak diskon.</small>
                            </div>
                            <div class="admin-field">
                                <label class="form-label" for="dimensi">Dimensi</label>
                                <input id="dimensi" type="text" class="form-control" name="dimensi" required placeholder="80 x 45 x 180" data-preview="dimension">
                                <small>Format disarankan: P x L x T dalam cm.</small>
                            </div>
                            <div class="admin-field">
                                <label class="form-label" for="berat">Berat</label>
                                <div class="input-group">
                                    <input id="berat" type="number" class="form-control" name="berat" step="any" required placeholder="25" data-preview="weight">
                                    <span class="input-group-text">kg</span>
                                </div>
                                <small>Berat untuk estimasi pengiriman.</small>
                            </div>
                            <div class="admin-field admin-field--full">
                                <label class="form-label" for="stok">Stok</label>
                                <input id="stok" type="text" class="form-control" name="stok" required placeholder="Contoh: ready / 25 / pre-order 7 hari" data-preview="stock">
                                <small>Bisa angka stok atau status seperti ready/pre-order.</small>
                            </div>
                        </div>
                    </section>

                    <section class="admin-form-section">
                        <div class="admin-form-section__head">
                            <span>2</span>
                            <div>
                                <h5>Kategori & varian</h5>
                                <p>Pastikan kategori sama dengan struktur produk di website.</p>
                            </div>
                        </div>
                        <div class="admin-field-grid">
                            <div class="admin-field">
                                <label class="form-label" for="kategori">Kategori</label>
                                <input id="kategori" list="kategori-list" type="text" class="form-control" name="kategori" required placeholder="lemari-dewasa" data-preview="category">
                                <small>Contoh: lemari-dewasa, meja-belajar, rak-serbaguna.</small>
                            </div>
                            <div class="admin-field">
                                <label class="form-label" for="subkategori">Sub kategori</label>
                                <input id="subkategori" type="text" class="form-control" name="subkategori" required placeholder="lemari-3-pintu" data-preview="subcategory">
                                <small>Gunakan huruf kecil dan tanda minus agar URL rapi.</small>
                            </div>
                            <div class="admin-field">
                                <label class="form-label" for="varian">Varian warna/model</label>
                                <input id="varian" type="text" class="form-control" name="varian" required placeholder="Putih, Coklat, Oak" data-preview="variant">
                                <small>Pisahkan dengan koma. Contoh: Putih, Coklat, Oak.</small>
                            </div>
                            <div class="admin-field">
                                <label class="form-label" for="jml_varian">Jumlah foto per varian</label>
                                <input id="jml_varian" type="number" min="1" class="form-control" name="jml_varian" required value="1">
                                <small>Jika tiap varian punya 2 foto, isi 2.</small>
                            </div>
                        </div>
                        <datalist id="kategori-list">
                            <option value="lemari-dewasa"></option>
                            <option value="lemari-anak"></option>
                            <option value="meja-rias"></option>
                            <option value="meja-belajar"></option>
                            <option value="meja-tv"></option>
                            <option value="meja-tulis"></option>
                            <option value="meja-komputer"></option>
                            <option value="rak-sepatu"></option>
                            <option value="rak-besi"></option>
                            <option value="rak-serbaguna"></option>
                            <option value="kursi"></option>
                        </datalist>
                    </section>

                    <section class="admin-form-section">
                        <div class="admin-form-section__head">
                            <span>3</span>
                            <div>
                                <h5>Link marketplace & video</h5>
                                <p>Opsional, isi jika produk tersedia di platform tersebut.</p>
                            </div>
                        </div>
                        <div class="admin-field-grid">
                            <div class="admin-field"><label class="form-label" for="shopee">Link Shopee</label><input id="shopee" type="url" class="form-control" name="shopee" placeholder="https://shopee.co.id/..."><small>Link tombol Shopee di halaman detail.</small></div>
                            <div class="admin-field"><label class="form-label" for="tokped">Link Tokopedia</label><input id="tokped" type="url" class="form-control" name="tokped" placeholder="https://tokopedia.com/..."><small>Link tombol Tokopedia.</small></div>
                            <div class="admin-field"><label class="form-label" for="tiktok">Link Tiktok Shop</label><input id="tiktok" type="url" class="form-control" name="tiktok" placeholder="https://www.tiktok.com/..."><small>Link tombol Tiktok Shop.</small></div>
                            <div class="admin-field"><label class="form-label" for="youtube">Link Youtube</label><input id="youtube" type="url" class="form-control" name="youtube" placeholder="https://youtube.com/..."><small>Video review/perakitan produk.</small></div>
                        </div>
                    </section>

                    <section class="admin-form-section">
                        <div class="admin-form-section__head">
                            <span>4</span>
                            <div>
                                <h5>Deskripsi & pencarian</h5>
                                <p>Deskripsi HTML akan tampil di detail produk, non-HTML untuk teks bersih.</p>
                            </div>
                        </div>
                        <div class="admin-field-grid">
                            <div class="admin-field admin-field--full">
                                <label class="form-label" for="deskripsi">Deskripsi HTML</label>
                                <textarea id="deskripsi" class="form-control admin-description-input" name="deskripsi" required placeholder="Tulis deskripsi produk seperti biasa, editor akan membuat format rapi otomatis." data-preview="description"></textarea>
                                <small>Tulis seperti di artikel. Admin bisa bold, list, heading, dan link tanpa mengetik tag HTML manual.</small>
                            </div>
                            <div class="admin-field admin-field--full">
                                <label class="form-label" for="deskripsi_nonhtml">Deskripsi tanpa HTML</label>
                                <textarea id="deskripsi_nonhtml" class="form-control" name="deskripsi_nonhtml" required placeholder="Akan otomatis terisi dari deskripsi. Bisa diedit manual jika perlu."></textarea>
                                <small>Otomatis dibuat dari deskripsi tanpa format HTML.</small>
                            </div>
                            <div class="admin-field admin-field--full">
                                <label class="form-label" for="pencarian">Keyword pencarian</label>
                                <div class="input-group">
                                    <input id="pencarian" type="text" class="form-control" name="pencarian" required placeholder="lemari pakaian minimalis modern putih murah">
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
                        <div class="add-gambar admin-preview-image mb-3">
                            <img src="/img/nopic.jpg" id="addProduct_PreviewUtama" alt="Preview produk">
                        </div>
                        <div class="admin-preview-meta">
                            <span id="previewCategory">Kategori produk</span>
                            <span id="previewStock">Stok</span>
                        </div>
                        <h3 id="previewName">Nama produk akan tampil di sini</h3>
                        <p class="admin-preview-price" id="previewPrice">Rp0</p>
                        <p class="admin-preview-spec" id="previewSpec">Dimensi dan berat akan tampil di sini.</p>
                        <div class="admin-preview-variants" id="previewVariants">Varian: -</div>
                        <div class="admin-description-preview" id="previewDescription">Preview deskripsi akan muncul di sini saat admin mengetik.</div>
                    </div>
                    <section class="admin-form-section admin-image-section mt-3">
                        <div class="admin-form-section__head">
                            <span>5</span>
                            <div>
                                <h5>Gambar produk</h5>
                                <p>Jumlah upload mengikuti varian dan jumlah foto per varian.</p>
                            </div>
                        </div>
                        <div class="d-flex gap-2" id="foto-varian"></div>
                    </section>
                    <button class="btn btn-primary1 w-100 mt-3" type="submit">Simpan Produk</button>
                </aside>
            </div>
        </form>
    </div>
</div>
<script>
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))
    const elmFotoVarian = document.getElementById('foto-varian');
    const elmVarian = document.querySelector('input[name="varian"]');
    const elmJmlvarian = document.querySelector('input[name="jml_varian"]');
    let varian = 1;
    let jmlVarian = 1;
    let hasilVarian = 1;

    function formatRupiah(value) {
        const number = Number(value || 0);
        return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(number);
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
        if (force || !plainField.dataset.touched || plainField.value.trim() === '') {
            plainField.value = plain;
        }
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

    function updateAdminProductPreview() {
        const get = (name) => document.querySelector(`[name="${name}"]`)?.value?.trim() || '';
        const harga = Number(get('harga') || 0);
        const diskon = Number(get('diskon') || 0);
        const finalPrice = diskon > 0 ? harga - (harga * diskon / 100) : harga;
        document.getElementById('previewName').textContent = get('nama') || 'Nama produk akan tampil di sini';
        document.getElementById('previewCategory').textContent = get('kategori') || 'Kategori produk';
        document.getElementById('previewStock').textContent = get('stok') || 'Stok';
        document.getElementById('previewPrice').textContent = `${formatRupiah(finalPrice)}${diskon > 0 ? ' • Diskon ' + diskon + '%' : ''}`;
        document.getElementById('previewSpec').textContent = `${get('dimensi') || 'Dimensi'} cm • ${get('berat') || 'Berat'} kg`;
        document.getElementById('previewVariants').textContent = `Varian: ${get('varian') || '-'}`;
        document.getElementById('previewDescription').innerHTML = getDescriptionContent() || 'Preview deskripsi akan muncul di sini saat admin mengetik.';
    }

    document.querySelectorAll('.admin-product-form input, .admin-product-form textarea').forEach((field) => {
        field.addEventListener('input', updateAdminProductPreview);
        field.addEventListener('change', updateAdminProductPreview);
    });
    document.querySelector('[name="deskripsi_nonhtml"]').addEventListener('input', (e) => e.target.dataset.touched = 'true');
    document.getElementById('generateSearchKeyword').addEventListener('click', generateSearchKeyword);
    document.querySelector('.admin-product-form').addEventListener('submit', () => {
        if (window.tinymce) tinymce.triggerSave();
        syncPlainDescription(true);
        if (!document.querySelector('[name="pencarian"]').value.trim()) generateSearchKeyword();
    });

    tinymce.init({
        selector: '#deskripsi',
        height: 320,
        menubar: false,
        plugins: ['link', 'lists', 'table', 'code'],
        toolbar: 'undo redo | blocks | bold italic underline | bullist numlist | link table | removeformat code',
        setup: function(editor) {
            editor.on('input change keyup setcontent', function() {
                syncPlainDescription();
                updateAdminProductPreview();
            });
        }
    });

    function syncImageInputs() {
        const varianArray = (elmVarian.value || '').split(',').map(v => v.trim()).filter(Boolean);
        varian = Math.max(varianArray.length, 1);
        jmlVarian = Math.max(Number(elmJmlvarian.value || 1), 1);
        hasilVarian = jmlVarian + varian - 1;
        inputElement(hasilVarian);
        updateAdminProductPreview();
    }

    elmVarian.addEventListener('input', syncImageInputs);
    elmJmlvarian.addEventListener('input', syncImageInputs);

    function inputElement(hasilVarian) {
        elmFotoVarian.innerHTML = "";
        for (let i = 1; i <= hasilVarian; i++) {
            const cardVarian = document.createElement('div');
            cardVarian.classList.add('input-group-gambar');
            const cardAnkvarian = document.createElement('div');
            cardAnkvarian.classList.add('addProduct_Input');
            cardAnkvarian.setAttribute('id', 'addProduct_Input' + i);
            cardAnkvarian.setAttribute('data-bs-toggle', 'tooltip');
            cardAnkvarian.setAttribute('data-bs-placement', 'top');
            cardAnkvarian.setAttribute('data-bs-title', 'Upload gambar ke-' + i);
            const cardlabel = document.createElement('label');
            cardlabel.classList.add('input-gambar-label');
            cardlabel.setAttribute('for', 'addProduct_InputGambar' + i);
            const cardIlabel = document.createElement('i');
            cardIlabel.classList.add('material-icons');
            cardIlabel.innerHTML = "add_photo_alternate";
            const cardinput = document.createElement('input');
            cardinput.classList.add('input-gambar');
            cardinput.setAttribute('type', 'file');
            cardinput.setAttribute('id', 'addProduct_InputGambar' + i);
            cardinput.setAttribute('name', 'gambar' + i);
            cardinput.setAttribute('accept', 'image/*');
            cardinput.setAttribute('required', '');
            const cardImg = document.createElement('img');
            cardImg.src = "/img/nopic.jpg";
            cardImg.setAttribute('id', 'addProduct_PreviewGambar' + i);
            cardImg.classList.add('addProduct_Preview');
            cardlabel.appendChild(cardIlabel);
            cardAnkvarian.appendChild(cardlabel);
            cardAnkvarian.appendChild(cardinput);
            cardVarian.appendChild(cardAnkvarian);
            cardVarian.appendChild(cardImg);
            elmFotoVarian.appendChild(cardVarian);
        }
        const addProduct_inputGambar = document.querySelectorAll(".input-gambar");
        const addProduct_previewGambar = document.querySelectorAll(".addProduct_Preview");
        const addProduct_input = document.querySelectorAll(".addProduct_Input");
        const addProduct_previewUtama = document.getElementById("addProduct_PreviewUtama");
        addProduct_inputGambar.forEach((item, index) => {
            item.addEventListener("change", () => {
                const file = addProduct_inputGambar[index].files[0];
                if (!file) return;
                const blobUrl = URL.createObjectURL(file);
                addProduct_previewGambar[index].src = blobUrl;
                addProduct_previewUtama.src = blobUrl;
                addProduct_previewGambar[index].style.display = "block";
                addProduct_input[index].style.display = 'none';
            })
        })
    }

    syncImageInputs();
    updateAdminProductPreview();
</script>
<?= $this->endSection(); ?>
