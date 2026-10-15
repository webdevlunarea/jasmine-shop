(() => {
    'use strict';
    const page = document.querySelector('.buyer-order-page');
    if (!page) return;
    const feedback = document.createElement('p');
    feedback.className = 'order-copy-feedback';
    feedback.setAttribute('role', 'status');
    page.querySelector('.order-heading').append(feedback);
    page.querySelectorAll('[data-order-copy]').forEach(button => {
        button.addEventListener('click', async () => {
            try {
                await navigator.clipboard.writeText(button.dataset.orderCopy);
                button.textContent = 'Tersalin';
                feedback.textContent = 'Rincian pembayaran berhasil disalin.';
            } catch {
                feedback.textContent = 'Silakan salin rincian pembayaran secara manual.';
            }
        });
    });
    const dialogs = [...document.querySelectorAll('#modal-upload, #modal-qr')];
    let active = null, trigger = null;
    const controls = dialog => [...dialog.querySelectorAll('button,a[href],input,label[tabindex]')].filter(e => !e.disabled && e.getClientRects().length && getComputedStyle(e).visibility !== 'hidden');
    const sync = () => {
        const next = dialogs.find(dialog => !dialog.classList.contains('d-none')) || null;
        document.documentElement.classList.toggle('order-dialog-open', !!next);
        if (next === active) return;
        if (next) {
            trigger = document.activeElement;
            active = next;
            (next.querySelector('.order-dialog-close') || controls(next)[0])?.focus({ preventScroll: true });
        } else {
            active = null;
            if (trigger?.isConnected) trigger.focus({ preventScroll: true });
            trigger = null;
        }
    };
    const observer = new MutationObserver(sync);
    dialogs.forEach(dialog => observer.observe(dialog, { attributes: true, attributeFilter: ['class'] }));
    document.addEventListener('keydown', event => {
        if (!active) return;
        if (event.key === 'Escape') {
            event.preventDefault();
            if (active.id === 'modal-upload') closeUpload(); else closeQR();
        }
        if (event.key !== 'Tab') return;
        const items = controls(active), first = items[0], last = items[items.length - 1];
        if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last?.focus(); }
        else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first?.focus(); }
    });
    sync();
})();
