(() => {
    'use strict';
    const section = document.querySelector('[data-product-size-fit]');
    const button = section?.querySelector('[data-size-fit-toggle]');
    const panel = section?.querySelector('[data-size-fit-root]');
    if (!section || !button || !panel) return;

    let loaded = false;
    let loading = false;
    button.addEventListener('click', async () => {
        const open = button.getAttribute('aria-expanded') === 'true';
        if (open) {
            button.setAttribute('aria-expanded', 'false');
            button.querySelector('span:first-child').textContent = 'Coba simulasi';
            panel.hidden = true;
            return;
        }

        button.setAttribute('aria-expanded', 'true');
        button.querySelector('span:first-child').textContent = 'Tutup simulasi';
        panel.hidden = false;
        if (loaded || loading) return;
        loading = true;
        panel.setAttribute('aria-busy', 'true');
        try {
            const module = await import(panel.dataset.sizeFitModule);
            module.mountSizeFit(panel);
            loaded = true;
        } catch (_) {
            panel.textContent = 'Simulasi ukuran belum dapat dimuat. Coba buka kembali halaman produk.';
            panel.setAttribute('role', 'status');
        } finally {
            loading = false;
            panel.removeAttribute('aria-busy');
        }
    });
})();
