<?php if (!empty($addressBookAvailable)) : ?>
<?php
$bookRows = $addressBook ?? [];
$selectedId = session('checkout_address_owner') === session('email') ? (int)session('checkout_address_id') : 0;
$selectedEntry = null;
foreach ($bookRows as $entry) {
    if ((int)$entry['id'] === $selectedId) $selectedEntry = $entry;
}
?>
<div class="mb-3" id="checkout-address-book">
    <label for="saved-address-choice" class="form-label small fw-bold">Alamat tersimpan</label>
    <select id="saved-address-choice" class="form-select mb-2">
        <option value="">Alamat baru / alamat sebelumnya</option>
        <?php foreach ($bookRows as $entry) : ?>
        <option value="<?= (int)$entry['id']; ?>" <?= (int)$entry['id'] === $selectedId ? 'selected' : ''; ?>><?= esc($entry['label'] . (!empty($entry['is_default']) ? ' · Utama' : '') . ' — ' . $entry['recipient_name']); ?></option>
        <?php endforeach; ?>
    </select>
    <div class="d-flex flex-wrap gap-2 mb-2">
        <button type="button" class="btn btn-sm btn-outline-success" id="address-add">+ Tambah alamat</button>
        <button type="button" class="btn btn-sm btn-outline-secondary" id="address-edit" <?= !$selectedEntry ? 'disabled' : ''; ?>>Edit alamat</button>
        <button type="button" class="btn btn-sm btn-link text-secondary px-1" id="address-default" <?= !$selectedEntry || !empty($selectedEntry['is_default']) ? 'disabled' : ''; ?>>Jadikan utama</button>
        <button type="button" class="btn btn-sm btn-link text-danger px-1" id="address-delete" <?= !$selectedEntry ? 'disabled' : ''; ?>>Hapus</button>
    </div>
    <div id="address-save-panel" <?= $selectedEntry ? 'hidden' : ''; ?>>
        <label for="address-label" class="form-label small">Label alamat</label>
        <input id="address-label" class="form-control mb-2" maxlength="60" placeholder="Contoh: Rumah atau Kantor" value="<?= esc($selectedEntry['label'] ?? 'Rumah'); ?>">
        <label class="d-flex gap-2 align-items-center small mb-2"><input id="address-is-default" type="checkbox" <?= !$bookRows || !empty($selectedEntry['is_default']) ? 'checked' : ''; ?>> Jadikan alamat utama</label>
        <button type="button" class="btn btn-sm btn-outline-success" id="address-save">Simpan alamat penerima</button>
        <p class="small text-secondary mt-2 mb-0">Lengkapi nama, nomor HP, dan alamat di bawah sebelum menyimpan. Ongkir perlu dipilih ulang setelah mengganti alamat.</p>
    </div>
    <p id="address-book-feedback" role="status" class="small text-danger mt-2 mb-0"></p>
</div>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const choice = document.getElementById('saved-address-choice');
    const panel = document.getElementById('address-save-panel');
    const feedback = document.getElementById('address-book-feedback');
    let editingId = <?= $selectedId; ?>;
    let submitting = false;
    function postAddress(action, id = editingId) {
        if (submitting) return;
        const data = { address_action: action, address_id: String(id || ''),
            <?= json_encode(csrf_token()); ?>: <?= json_encode(csrf_hash()); ?> };
        if (action === 'save') {
            for (const name of ['nama', 'nohp', 'provinsi', 'kota', 'kecamatan', 'kodepos', 'alamat_add']) {
                const field = document.querySelector('#form-checkout [name="' + name + '"]');
                if (!field || !field.value.trim()) {
                    feedback.textContent = 'Lengkapi nama penerima, nomor HP, dan seluruh wilayah alamat terlebih dahulu.';
                    field?.focus();
                    return;
                }
                data[name] = field.value;
            }
            data.address_label = document.getElementById('address-label').value.trim();
            data.address_default = document.getElementById('address-is-default').checked ? '1' : '0';
            if (!data.address_label) { feedback.textContent = 'Isi label alamat, misalnya Rumah atau Kantor.'; return; }
        }
        submitting = true;
        if (typeof resetShippingSelection === 'function') resetShippingSelection();
        const form = document.createElement('form');
        form.method = 'post';
        form.action = '/account/addresses/manage';
        for (const [name, value] of Object.entries(data)) {
            const input = document.createElement('input');
            input.type = 'hidden'; input.name = name; input.value = value; form.appendChild(input);
        }
        document.body.appendChild(form);
        form.submit();
    }
    choice.addEventListener('change', () => {
        if (choice.value) postAddress('select', choice.value);
        else document.getElementById('address-add').click();
    });
    document.getElementById('address-add').addEventListener('click', () => {
        editingId = 0; choice.value = ''; panel.hidden = false; feedback.textContent = '';
        document.getElementById('address-label').value = '';
        document.getElementById('address-is-default').checked = <?= !$bookRows ? 'true' : 'false'; ?>;
        for (const name of ['nama', 'nohp', 'alamat_add', 'alamat']) {
            const field = document.querySelector('#form-checkout [name="' + name + '"]');
            if (field) field.value = '';
        }
        for (const name of ['provinsi', 'kota', 'kecamatan', 'kodepos']) {
            const field = document.querySelector('#form-checkout [name="' + name + '"]');
            if (field) field.value = '';
        }
        prov_kab_kec_desa = ['', '', '', '']; isiAlamat = ['', '', '', '', '', ''];
        for (const id of ['address-edit', 'address-default', 'address-delete']) document.getElementById(id).disabled = true;
        resetShippingSelection(); refreshCheckoutButtonState();
        document.getElementById('address-label').focus();
    });
    document.getElementById('address-edit').addEventListener('click', () => { panel.hidden = false; document.getElementById('address-label').focus(); });
    document.getElementById('address-save').addEventListener('click', () => postAddress('save'));
    document.getElementById('address-default').addEventListener('click', () => postAddress('default'));
    document.getElementById('address-delete').addEventListener('click', () => {
        if (!editingId || submitting) return;
        const addressId = editingId;
        triggerToast('Alamat ini akan dihapus dari daftar alamat tersimpan. Alamat pada pesanan sebelumnya tetap tersimpan.', null, () => postAddress('delete', addressId));
        document.querySelector('.confirm-dialog__content h5').textContent = 'Hapus alamat tersimpan?';
        document.querySelector('.confirm-dialog__ok').textContent = 'Hapus Alamat';
    });
});
</script>
<?php endif; ?>
