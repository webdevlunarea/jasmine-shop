(() => {
    'use strict';
    document.querySelectorAll('[data-cancel-order]').forEach(button => {
        button.addEventListener('click', () => {
            window.triggerToast('Batalkan pesanan ini? Pembatalan berlaku untuk pesanan yang belum dibayar.', button.dataset.cancelOrder);
        });
    });
    const root = document.getElementById('payment-deadline');
    const clock = document.getElementById('waktu');
    if (!root || !clock) return;
    const expiry = Number(root.dataset.expiryMs);
    const serverStart = Number(root.dataset.serverNow);
    const elapsedStart = performance.now();
    const message = document.getElementById('payment-deadline-message');
    // Server time avoids timezone parsing and a misconfigured customer device clock.
    if (!Number.isFinite(expiry) || expiry <= 0 || !Number.isFinite(serverStart)) {
        clock.textContent = '--:--:--';
        if (message) message.textContent = 'Batas waktu belum tersedia. Gunakan tombol Saya Sudah Bayar untuk memeriksa status.';
        return;
    }
    let stopped = false;
    const retryKey = 'payment-expiry-' + root.dataset.orderId;
    let checks = { count: 0, at: 0 };
    try { checks = JSON.parse(sessionStorage.getItem(retryKey)) || checks; } catch (_) { /* Storage may be disabled. */ }
    if (!Number.isFinite(checks.count) || !Number.isFinite(checks.at)) checks = { count: 0, at: 0 };
    document.addEventListener('submit', () => { stopped = true; });
    const tick = () => {
        const now = serverStart + performance.now() - elapsedStart;
        const remaining = Math.max(0, Math.ceil((expiry - now) / 1000));
        clock.textContent = [Math.floor(remaining / 3600), Math.floor(remaining % 3600 / 60), remaining % 60]
            .map(value => String(value).padStart(2, '0')).join(':');
        if (remaining > 0 || stopped || document.hidden) return;
        if (message) message.textContent = 'Batas waktu pembayaran telah tercapai. Status pembayaran sedang diperiksa.';
        if (checks.count >= 5) {
            if (message) message.textContent = 'Gunakan tombol Saya Sudah Bayar untuk memperbarui status. Jangan membayar ulang sebelum status dipastikan.';
            return;
        }
        if (now - checks.at < 30000) return;
        checks = { count: checks.count + 1, at: now };
        try { sessionStorage.setItem(retryKey, JSON.stringify(checks)); }
        catch (_) { stopped = true; return; } // Avoid a reload loop when storage is blocked.
        stopped = true;
        window.location.reload(); // Backend validates the provider; the browser never sets payment status.
    };
    tick();
    setInterval(tick, 1000);
})();
