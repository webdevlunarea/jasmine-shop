(() => {
    'use strict';
    const source = document.getElementById('mapping-website-data');
    if (!source) return;
    const website = JSON.parse(source.textContent);
    const form = document.getElementById('mapping-form');
    const fields = document.getElementById('mapping-fields');
    const masterSelect = document.getElementById('master-product');
    const search = document.getElementById('master-search');
    const status = document.getElementById('catalogue-status');
    const reload = document.getElementById('reload-catalogue');
    const variants = document.getElementById('variant-mapping');
    const detail = document.getElementById('master-detail');
    const feedback = document.getElementById('mapping-validation');
    const submit = document.getElementById('save-mapping');
    let products = [], loading = false;
    const saved = website.identity;
    const option = (label, value) => new Option(label, value);
    const eligible = p => p.active && p.website_ids.every(id => id === website.id)
        && (p.local_website_ids || []).every(id => id === website.id) && (!saved || p.id === saved.product_id);

    function validate() {
        const product = products.find(p => p.id === masterSelect.value);
        const selections = [...variants.querySelectorAll('select')].map(select => select.value);
        const noVariants = product && !product.variants.length;
        const compatible = noVariants ? (!website.variants.length || (website.variants.length === 1 && website.variants[0] === 'Default')) : website.variants.length > 0;
        const valid = !!product && compatible && selections.every(Boolean) && new Set(selections).size === selections.length;
        submit.disabled = !valid || loading;
        feedback.textContent = !product ? 'Pilih produk terlebih dahulu.' : !compatible ? 'Struktur varian berbeda. Periksa katalog sebelum melanjutkan.' : !valid ? 'Lengkapi setiap varian dengan pilihan yang berbeda.' : 'Pilihan lengkap. Periksa sekali lagi sebelum menyimpan.';
    }

    function renderMaster() {
        variants.replaceChildren(); detail.replaceChildren(); detail.hidden = true;
        const product = products.find(p => p.id === masterSelect.value);
        document.getElementById('master-fingerprint').value = product ? product.fingerprint : '';
        if (!product) { validate(); return; }
        detail.hidden = false;
        for (const text of [product.name, 'SKU: ' + product.sku, 'ID: ' + product.id, 'Stok master MyLuna: ' + product.stock]) {
            const line = document.createElement('div'); line.textContent = text; detail.append(line);
        }
        if (product.variants.length) website.variants.forEach((name, index) => {
            const row = document.createElement('div'); row.className = 'mapping-variant-row';
            const label = document.createElement('label'); label.htmlFor = 'variant-pair-' + index; label.textContent = 'Varian website: ' + name;
            const select = document.createElement('select'); select.className = 'form-select'; select.name = 'variant_ids[' + index + ']'; select.id = label.htmlFor; select.required = true;
            select.add(option('Pilih varian MyLuna', ''));
            const lockedId = saved?.variants?.[name];
            for (const variant of product.variants) if (!lockedId || variant.id === lockedId) select.add(option(variant.value + ' · stok ' + variant.stock, variant.id));
            if (lockedId) select.value = lockedId;
            select.addEventListener('change', validate); row.append(label, select); variants.append(row);
        });
        validate();
    }

    function filterProducts() {
        const selected = masterSelect.value || saved?.product_id || '';
        const term = search.value.trim().toLocaleLowerCase('id');
        masterSelect.replaceChildren(option('Pilih produk MyLuna', ''));
        for (const p of products) if (eligible(p) && (p.id === selected || (p.name + ' ' + p.sku + ' ' + p.id).toLocaleLowerCase('id').includes(term))) masterSelect.add(option(p.name + ' · ' + p.sku + ' · stok ' + p.stock, p.id));
        masterSelect.value = selected; renderMaster();
    }

    async function load() {
        if (loading) return;
        loading = true; fields.disabled = true; submit.disabled = true; reload.disabled = true; status.textContent = 'Memuat katalog MyLuna…';
        const controller = new AbortController(); const timer = setTimeout(() => controller.abort(), 20000);
        try {
            const response = await fetch('/lunamappingadmin/catalogue', { credentials: 'same-origin', cache: 'no-store', signal: controller.signal, headers: { Accept: 'application/json' } });
            const body = await response.json();
            if (!response.ok || !body.success || !Array.isArray(body.products)) throw new Error('catalogue');
            products = body.products; fields.disabled = false;
            status.textContent = products.length + ' produk master dimuat. ' + (saved ? 'Pasangan lama dikunci.' : 'Tidak ada pasangan yang dipilih otomatis.');
            filterProducts();
        } catch (_) { status.textContent = 'Katalog belum berhasil dimuat. Periksa koneksi lalu tekan Muat ulang katalog.'; }
        finally { clearTimeout(timer); loading = false; reload.disabled = false; if (!fields.disabled) validate(); }
    }
    search.addEventListener('input', filterProducts);
    masterSelect.addEventListener('change', renderMaster);
    reload.addEventListener('click', load);
    form.addEventListener('submit', event => {
        validate();
        if (fields.disabled || submit.disabled || !form.reportValidity()) { event.preventDefault(); return; }
        submit.disabled = true; submit.textContent = 'Memeriksa dan menyimpan…'; reload.disabled = true;
    });
    load();
})();
