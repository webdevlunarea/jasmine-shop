/* Native file inputs are kept mounted: preview updates must never discard FileList. */
(() => {
    'use strict';
    const allowed = ['image/webp', 'image/avif'];
    const fields = new Set();
    function variants() {
        document.querySelectorAll('.variant-image-select').forEach(select => {
            const slot = document.querySelector(`[data-photo-slot="${Number(select.value) + 1}"] [data-image-preview]`);
            const target = document.querySelector(`[data-variant-preview="${select.dataset.previewTarget}"]`);
            if (target && slot) target.src = slot.src;
        });
        const first = document.querySelector('[data-photo-slot="1"] [data-image-preview]');
        const main = document.getElementById('addProduct_PreviewUtama');
        if (first && main) main.src = first.src;
    }
    function init(field) {
        if (fields.has(field)) return;
        fields.add(field);
        const input = field.querySelector('[data-image-input]');
        const preview = field.querySelector('[data-image-preview]');
        const feedback = field.querySelector('[data-image-feedback]');
        const reset = field.querySelector('[data-reset-image]');
        const state = field.querySelector('[data-image-state]');
        if (!input || !preview) return;
        const original = preview.dataset.savedSource || preview.getAttribute('src') || '/img/nopic.jpg';
        let objectUrl = null;
        let generation = 0;
        let pending = false;
        const say = (text, error = false) => {
            if (feedback) { feedback.textContent = text; feedback.dataset.error = String(error); }
            input.setAttribute('aria-invalid', String(error));
            input.setCustomValidity(error ? text : '');
        };
        function restore(message = 'Pilihan baru dibatalkan. Gambar tersimpan tetap dipakai.', error = false) {
            generation++;
            pending = false;
            input.value = '';
            if (objectUrl) URL.revokeObjectURL(objectUrl);
            objectUrl = null;
            preview.src = original;
            if (reset) reset.hidden = !error;
            if (state) state.textContent = original.includes('/img/nopic.jpg') ? 'Kosong' : 'Tersimpan';
            say(message, error);
            variants();
        }
        reset?.addEventListener('click', () => restore());
        input.addEventListener('change', () => {
            const file = input.files?.[0];
            if (!file) { restore(); return; }
            if (!/\.(webp|avif)$/i.test(file.name) || (file.type && !allowed.includes(file.type))) {
                restore('Pilih file WebP atau AVIF. JPG, PNG, dan SVG tidak diterima untuk upload baru.', true); return;
            }
            const limit = Number(field.dataset.maxBytes || 4194304);
            if (!file.size || file.size > limit) { restore(`Ukuran foto maksimal ${limit / 1048576} MB.`, true); return; }
            generation++;
            const current = generation;
            if (objectUrl) URL.revokeObjectURL(objectUrl);
            objectUrl = URL.createObjectURL(file);
            pending = true;
            say('Memeriksa preview…');
            const decoded = new Image();
            decoded.onload = () => {
                if (current !== generation) return;
                if (decoded.naturalWidth > 4096 || decoded.naturalHeight > 4096 || decoded.naturalWidth * decoded.naturalHeight > 16000000) {
                    restore('Maksimal 4096 px per sisi dan 16 megapiksel. Perkecil foto lalu pilih ulang.', true); return;
                }
                pending = false;
                preview.src = objectUrl;
                preview.classList.remove('d-none');
                if (reset) reset.hidden = false;
                if (state) state.textContent = 'Belum disimpan';
                say(`${file.name} · ${Math.ceil(file.size / 1024)} KB · ${decoded.naturalWidth} × ${decoded.naturalHeight} px. Simpan untuk menerapkan.`);
                variants();
            };
            decoded.onerror = () => { if (current === generation) restore('Preview gagal dibaca. Gunakan file WebP/AVIF yang valid dan didukung browser.', true); };
            decoded.src = objectUrl;
        });
        input.form?.addEventListener('submit', event => {
            if (pending) { event.preventDefault(); say('Tunggu pemeriksaan preview selesai.', true); }
        });
    }
    document.querySelectorAll('.admin-image-field').forEach(init);
    document.querySelectorAll('.variant-image-select').forEach(select => select.addEventListener('change', variants));
    document.querySelectorAll('[data-product-gallery]').forEach(gallery => {
        const grid = gallery.querySelector('.admin-media-grid');
        const add = gallery.querySelector('[data-add-photo]');
        const feedback = gallery.querySelector('[data-gallery-feedback]');
        const update = () => { add.disabled = grid.children.length >= 50; };
        add.addEventListener('click', () => {
            if (grid.children.length >= 50) return;
            const number = grid.children.length + 1;
            const card = grid.firstElementChild.cloneNode(true);
            card.dataset.photoSlot = String(number);
            const image = card.querySelector('[data-image-preview]');
            image.src = image.dataset.savedSource = '/img/nopic.jpg';
            image.alt = `Preview foto ${number}`;
            const input = card.querySelector('[data-image-input]');
            input.value = ''; input.id = `product-photo-${number}`; input.name = `gambar${number}`;
            input.removeAttribute('aria-invalid'); input.setAttribute('aria-describedby', `photo-feedback-${number}`);
            card.querySelector('label').htmlFor = input.id;
            card.querySelector('[data-photo-title]').textContent = `Foto ${number}`;
            card.querySelector('[data-image-state]').textContent = 'Kosong';
            const note = card.querySelector('[data-image-feedback]');
            note.id = `photo-feedback-${number}`; note.textContent = 'Belum ada file baru dipilih.'; note.dataset.error = 'false';
            card.querySelector('[data-reset-image]').hidden = true;
            grid.appendChild(card);
            document.querySelectorAll('.variant-image-select').forEach(select => {
                const option = document.createElement('option'); option.value = String(number - 1); option.textContent = `Pakai Foto ${number}`; select.appendChild(option);
            });
            init(card); update(); input.focus();
        });
        gallery.closest('form')?.addEventListener('submit', event => {
            let total = 0;
            grid.querySelectorAll('[data-image-input]').forEach(input => { total += input.files?.[0]?.size || 0; });
            if (total > 12582912) { event.preventDefault(); feedback.textContent = 'Total file baru maksimal 12 MB. Batalkan sebagian pilihan lalu simpan bertahap.'; }
        });
        update();
    });
    variants();
})();
