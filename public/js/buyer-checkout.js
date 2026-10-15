(() => {
    'use strict';
    if (!document.querySelector('.checkout-page-shell')) return;
    const dialogs = [...document.querySelectorAll('#modal-metode-bayar, #modal-redeem')];
    let active = null;
    let trigger = null;
    const visible = element => !element.disabled && element.getClientRects().length > 0;
    const controls = dialog => [...dialog.querySelectorAll('a[href], button, input, select, textarea, [tabindex="0"]')].filter(visible);
    const sync = () => {
        const open = dialogs.find(dialog => !dialog.classList.contains('d-none')) || null;
        document.documentElement.classList.toggle('checkout-dialog-open', !!open);
        if (open === active) return;
        if (open) {
            trigger = document.activeElement;
            active = open;
            (open.querySelector('.checkout-dialog-close') || controls(open)[0])?.focus({ preventScroll: true });
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
            if (active.id === 'modal-metode-bayar') closeMetode();
            else closeRedeem();
        }
        if (event.key !== 'Tab') return;
        const items = controls(active), first = items[0], last = items[items.length - 1];
        if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last?.focus(); }
        else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first?.focus(); }
    });
    document.querySelectorAll('.checkout-page-shell .tombol-pilih-kurir').forEach(control => {
        control.setAttribute('role', 'button');
        control.tabIndex = 0;
        control.setAttribute('aria-expanded', 'false');
        const update = () => control.setAttribute('aria-expanded', String(control.classList.contains('show')));
        new MutationObserver(update).observe(control, { attributes: true, attributeFilter: ['class'] });
        control.addEventListener('keydown', event => {
            if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); control.click(); }
        });
    });
    const paymentLabels = { bca: 'BCA', bni: 'BNI', bri: 'BRI', mandiri: 'Mandiri', permata: 'Permata', cimb: 'CIMB Niaga', qris: 'QRIS', gopay: 'GoPay', shopeepay: 'ShopeePay', dana: 'DANA', card: 'Kartu kredit', rekening: 'Transfer bank BRI' };
    document.querySelectorAll('#modal-metode-bayar input[name="pembayaran"]').forEach(input => {
        input.setAttribute('aria-label', paymentLabels[input.value] || input.value);
        const label = document.querySelector(`label[for="${input.id}"]`);
        if (label) {
            const name = document.createElement('span');
            name.className = 'payment-method-name';
            name.textContent = paymentLabels[input.value] || input.value;
            label.append(name);
        }
    });
    sync();
})();
