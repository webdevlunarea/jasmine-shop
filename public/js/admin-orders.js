(() => {
    'use strict';
    const dataElement = document.getElementById('admin-orders-data');
    if (!dataElement) return;
    let config;
    try { config = JSON.parse(dataElement.textContent); } catch { return; }
    const orders = Array.isArray(config.orders) ? config.orders : [];
    let csrfHash = config.csrfHash;
    const search = document.getElementById('orders-search');
    const searchFeedback = document.getElementById('orders-search-feedback');
    const initialCaption = searchFeedback?.textContent || '';
    const rows = Array.from(document.querySelectorAll('.list-customer[data-order-index]'));
    const noMatch = document.querySelector('.orders-search-empty');
    const normalize = value => String(value || '').trim().toLocaleLowerCase('id-ID');
    function filterOrders() {
        const query = normalize(search?.value);
        let visible = 0;
        rows.forEach(row => {
            const order = orders[Number(row.dataset.orderIndex)] || {};
            const matches = !query || [order.id_midtrans, order.nama_pen, order.email_cus].some(value => normalize(value).includes(query));
            row.hidden = !matches;
            if (matches) visible++;
        });
        if (noMatch) noMatch.hidden = !query || visible > 0;
        if (searchFeedback) searchFeedback.textContent = query ? `${visible} pesanan ditemukan di halaman ini` : initialCaption;
    }
    search?.addEventListener('input', filterOrders);
    document.querySelector('[data-clear-order-search]')?.addEventListener('click', () => { search.value = ''; filterOrders(); search.focus(); });
    document.getElementById('orders-status')?.addEventListener('change', event => {
        window.location.href = '/listcustomer/1/' + encodeURIComponent(event.target.value);
    });
    document.querySelectorAll('[data-order-toggle]').forEach(button => {
        button.addEventListener('click', () => {
            const open = button.getAttribute('aria-expanded') !== 'true';
            document.querySelectorAll('[data-order-toggle]').forEach(other => {
                const detail = document.getElementById(other.getAttribute('aria-controls'));
                const expanded = other === button && open;
                other.setAttribute('aria-expanded', String(expanded));
                if (detail) detail.hidden = !expanded;
            });
        });
    });
    document.querySelectorAll('.order-sync-form').forEach(form => {
        form.addEventListener('submit', event => {
            if (form.dataset.sending === 'true') { event.preventDefault(); return; }
            if (!form.reportValidity()) { event.preventDefault(); return; }
            form.dataset.sending = 'true';
            const button = form.querySelector('button[type="submit"]');
            button.disabled = true;
            button.textContent = 'Menghubungkan ke MyLuna…';
        });
    });
    // Browser back/forward cache must not leave a valid review form permanently disabled.
    window.addEventListener('pageshow', event => {
        if (event.persisted) window.location.reload();
    });
    document.querySelectorAll('[data-complete-index]').forEach(button => button.addEventListener('click', () => {
        const order = orders[Number(button.dataset.completeIndex)];
        if (order?.status === 'Dikirim' && typeof window.triggerToast === 'function') {
            window.triggerToast(`Pesanan ${order.id_midtrans} sudah diterima pembeli?`, '/orderdone/' + encodeURIComponent(order.id_midtrans));
        }
    }));

    const modalElement = document.getElementById('container-edit-resi');
    const proofElement = document.getElementById('container-bukti-bayar');
    const feedback = document.getElementById('shipping-save-feedback');
    const saveButton = document.getElementById('shipping-save-button');
    const awb = document.getElementById('shipping-awb');
    const courier = document.getElementById('shipping-courier');
    const stage = document.getElementById('internal-shipping-status');
    let activeOrder = null;
    let shippingOpener = null;
    let proofOpener = null;
    let saving = false;
    let saved = false;
    function modal(element) { return window.bootstrap?.Modal.getOrCreateInstance(element); }
    function setFeedback(message, error = false) {
        if (!feedback) return;
        feedback.textContent = message;
        feedback.dataset.error = String(error);
    }
    function openShipping(index) {
        if (saving) return false;
        const order = orders[Number(index)];
        if (!order || !modalElement) return false;
        activeOrder = order;
        shippingOpener = document.activeElement;
        saved = false;
        const internal = order.shipment?.shipping_method_code === 'internal_delivery';
        document.getElementById('shipping-editor-title').textContent = internal ? 'Jasa Kirim Toko' : 'Pengiriman ekspedisi';
        document.getElementById('shipping-editor-order').textContent = order.id_midtrans;
        document.getElementById('external-shipping-courier').hidden = internal;
        document.getElementById('awb-label').textContent = internal ? 'Nomor resi internal toko (wajib)' : 'Nomor resi ekspedisi (wajib)';
        const stages = ['Disiapkan', 'Dalam Pengiriman', 'Gagal Kirim', 'Terkirim'];
        stage.value = stages.includes(order.shipment?.shipping_status) ? order.shipment.shipping_status : (order.status === 'Proses' ? 'Disiapkan' : (order.status === 'Selesai' ? 'Terkirim' : 'Dalam Pengiriman'));
        awb.value = order.shipment?.tracking_number || (/menunggu|^(kosong|none|null|n\/a|-)$/i.test(String(order.resi || '')) ? '' : order.resi || '');
        courier.value = order.kurir || '';
        courier.readOnly = Boolean(order.shipment) && !internal;
        setFeedback('');
        if (!window.bootstrap?.Modal) { setFeedback('Dialog belum siap. Muat ulang halaman.', true); return false; }
        modal(modalElement).show();
        return true;
    }
    async function saveShipping(retryOnly = false) {
        if (saving || !activeOrder) return;
        if (!retryOnly && !awb.value.trim()) { setFeedback('Isi nomor resi pengiriman terlebih dahulu.', true); awb.focus(); return; }
        saving = true;
        saveButton.disabled = true;
        stage.disabled = true;
        awb.disabled = true;
        courier.disabled = true;
        saveButton.textContent = retryOnly ? 'Menyinkronkan…' : 'Menyimpan…';
        setFeedback(retryOnly ? 'Memeriksa sinkronisasi ke MyLuna…' : 'Menyimpan pengiriman…');
        try {
            const response = await fetch('/editresi', {
                method: 'POST', credentials: 'same-origin',
                headers: {'Content-Type':'application/json', 'X-Requested-With':'XMLHttpRequest'},
                body: JSON.stringify({idMid:activeOrder.id_midtrans, resi:awb.value, kurir:courier.value, shipping_status:stage.value, retry_sync:retryOnly === true, [config.csrfName]:csrfHash}),
            });
            const result = await response.json();
            if (result.csrf_hash) {
                csrfHash = result.csrf_hash;
                document.querySelectorAll('input[type="hidden"]').forEach(input => { if (input.name === config.csrfName) input.value = csrfHash; });
            }
            if (!response.ok || !result.success) throw new Error(result.message || 'Pengiriman belum tersimpan. Muat ulang lalu coba lagi.');
            saved = true;
            setFeedback(result.message + ' Tutup dialog untuk memperbarui daftar.');
        } catch (error) {
            setFeedback(error.message || 'Koneksi terganggu. Periksa pesanan sebelum mencoba lagi.', true);
        } finally {
            saving = false;
            saveButton.disabled = false;
            stage.disabled = false;
            awb.disabled = false;
            courier.disabled = false;
            saveButton.textContent = 'Simpan pengiriman';
        }
    }
    document.querySelectorAll('[data-shipping-index]').forEach(button => button.addEventListener('click', () => openShipping(button.dataset.shippingIndex)));
    document.querySelectorAll('[data-shipping-retry]').forEach(button => button.addEventListener('click', () => {
        if (openShipping(button.dataset.shippingRetry)) void saveShipping(true);
    }));
    document.getElementById('shipping-editor-form')?.addEventListener('submit', event => { event.preventDefault(); void saveShipping(); });
    modalElement?.addEventListener('hide.bs.modal', event => { if (saving) event.preventDefault(); });
    modalElement?.addEventListener('hidden.bs.modal', () => { if (saved) window.location.reload(); else shippingOpener?.focus(); });
    document.querySelectorAll('[data-proof-index]').forEach(button => button.addEventListener('click', () => {
        const order = orders[Number(button.dataset.proofIndex)];
        if (!order?.bukti_bayar || !window.bootstrap?.Modal) return;
        proofOpener = document.activeElement;
        document.getElementById('payment-proof-order').textContent = order.id_midtrans;
        const image = document.getElementById('payment-proof-image');
        image.src = '/imgbuktibayar/' + encodeURIComponent(order.id_midtrans);
        image.alt = 'Bukti pembayaran pesanan ' + order.id_midtrans;
        ['accept', 'reject'].forEach(action => {
            const form = document.getElementById('payment-proof-' + action);
            const eligible = order.status === 'Menunggu Pembayaran Rekening';
            form.hidden = !eligible;
            form.action = eligible ? '/payorder/' + encodeURIComponent(order.id_midtrans) + '/confirm/' + (action === 'accept' ? 'accept' : 'cancel') : '';
        });
        modal(proofElement).show();
    }));
    document.querySelectorAll('#container-bukti-bayar form').forEach(form => form.addEventListener('submit', event => {
        if (form.dataset.sending === 'true') { event.preventDefault(); return; }
        form.dataset.sending = 'true';
        proofElement.querySelectorAll('button[type="submit"]').forEach(button => { button.disabled = true; });
    }));
    proofElement?.addEventListener('hidden.bs.modal', () => { document.getElementById('payment-proof-image').removeAttribute('src'); proofOpener?.focus(); });

    // A missing/malformed websocket must not break order review or shipping controls.
    try {
        const url = new URL(config.wsUrl || '');
        const usable = ['ws:', 'wss:'].includes(url.protocol) && !(window.location.protocol === 'https:' && url.protocol !== 'wss:');
        if (usable) {
            const socket = new WebSocket(url.href);
            socket.addEventListener('message', event => {
                try { if (JSON.parse(event.data)?.jenis === 'order') document.getElementById('btn-reload')?.classList.remove('d-none'); } catch { /* Ignore unrelated invalid messages. */ }
            });
            window.addEventListener('pagehide', () => socket.close(), {once:true});
        }
    } catch { /* Manual reload and all normal actions remain available. */ }
})();
