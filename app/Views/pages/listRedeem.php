<?= $this->extend('layout/template'); ?>
<?= $this->section('content'); ?>
<div id="modal-redeem" class="d-none justify-content-center align-items-center" style="background-color:rgba(0,0,0,.5);position:fixed;inset:0;z-index:1050;padding:1rem;">
    <div style="background:var(--bs-body-bg,#fff);width:min(100%,460px);" class="p-4 rounded-3 shadow">
        <h5 class="mb-3" id="redeem-modal-title">Tambah kode redeem</h5>
        <form action="/redeemcode" method="post" id="redeem-form">
            <?= csrf_field(); ?>
            <div class="mb-3"><label for="redeem-voucher" class="form-label">Voucher</label>
                <select id="redeem-voucher" name="voucher" class="form-select" required>
                    <?php foreach ($voucher as $v) { ?><option value="<?= esc($v['id'], 'attr'); ?>"><?= esc($v['nama']); ?></option><?php } ?>
                </select>
            </div>
            <div class="mb-3"><label for="redeem-code" class="form-label">Kode</label><input id="redeem-code" name="code" type="text" class="form-control text-uppercase" maxlength="80" required autocomplete="off"></div>
            <div class="d-flex justify-content-end gap-2"><button onclick="closeRedeem()" type="button" class="btn btn-outline-secondary">Batal</button><button type="submit" class="btn btn-primary1" id="redeem-submit">Simpan kode</button></div>
        </form>
    </div>
</div>
<main class="konten admin-module-page admin-module-list-redeem">
    <div class="container">
        <div class="admin-module-heading d-flex justify-content-between align-items-center"><div><h3>Kode redeem</h3><p class="text-secondary mb-0">Kelola kode voucher yang belum pernah diklaim.</p></div><button type="button" onclick="openRedeem()" class="btn btn-primary1">Tambah kode</button></div>
        <?php if (!empty($msg)) { ?><div class="alert alert-info mt-3" role="status"><?= esc($msg); ?></div><?php } ?>
        <div class="table-responsive mt-3" tabindex="0" role="region" aria-label="Daftar kode redeem">
            <table class="table admin-data-table align-middle">
                <thead><tr><th scope="col">No.</th><th scope="col">Voucher</th><th scope="col">Kode</th><th scope="col">Penggunaan</th><th scope="col" class="text-end">Tindakan</th></tr></thead>
                <tbody>
                <?php if (!$redeem) { ?><tr><td colspan="5" class="admin-empty-state">Belum ada kode redeem. Tambahkan kode untuk voucher yang tersedia.</td></tr><?php } ?>
                <?php foreach ($redeem as $index => $r) { $isUsed = is_array($r['email_user'] ?? null) && count($r['email_user']) > 0; ?>
                    <tr>
                        <td><?= (int)$index + 1; ?></td><td><?= esc($r['nama'] ?? 'Voucher'); ?></td><td class="fw-semibold"><code><?= esc($r['code'] ?? ''); ?></code></td>
                        <td><?= $isUsed ? 'Sudah diklaim · ' . count($r['email_user']) . ' akun' : 'Belum diklaim'; ?></td>
                        <td class="text-end text-nowrap">
                            <button type="button" class="btn btn-light" <?= $isUsed ? 'disabled title="Kode yang telah diklaim tidak dapat diubah"' : ''; ?> aria-label="Edit kode redeem" onclick="openRedeemEdit(<?= (int)$r['id']; ?>, <?= esc(json_encode((string)$r['id_voucher'], JSON_HEX_APOS | JSON_HEX_QUOT), 'attr'); ?>, <?= esc(json_encode((string)$r['code'], JSON_HEX_APOS | JSON_HEX_QUOT), 'attr'); ?>)"><i class="material-icons" aria-hidden="true">edit</i></button>
                            <form class="d-inline" action="/redeemcode/delete/<?= (int)$r['id']; ?>" method="post" data-confirm-submit data-confirm-message="Hapus kode redeem ini? Kode yang pernah diklaim akan dipertahankan.">
                                <?= csrf_field(); ?><button type="submit" class="btn btn-outline-danger" <?= $isUsed ? 'disabled title="Kode yang telah diklaim tidak dapat dihapus"' : ''; ?> aria-label="Hapus kode redeem"><i class="material-icons" aria-hidden="true">delete</i></button>
                            </form>
                        </td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
<script>
    const modalRedeemElm = document.getElementById('modal-redeem');
    const redeemFormElm = document.getElementById('redeem-form');
    function openRedeem() {
        redeemFormElm.action = '/redeemcode';
        document.getElementById('redeem-modal-title').textContent = 'Tambah kode redeem';
        document.getElementById('redeem-submit').textContent = 'Simpan kode';
        document.getElementById('redeem-code').value = '';
        modalRedeemElm.classList.remove('d-none'); modalRedeemElm.classList.add('d-flex');
        document.getElementById('redeem-code').focus();
    }
    function openRedeemEdit(id, voucher, code) {
        redeemFormElm.action = '/redeemcode/edit/' + encodeURIComponent(id);
        document.getElementById('redeem-modal-title').textContent = 'Edit kode redeem';
        document.getElementById('redeem-submit').textContent = 'Simpan perubahan';
        document.getElementById('redeem-voucher').value = voucher;
        document.getElementById('redeem-code').value = code;
        modalRedeemElm.classList.remove('d-none'); modalRedeemElm.classList.add('d-flex');
        document.getElementById('redeem-code').focus();
    }
    function closeRedeem() { modalRedeemElm.classList.add('d-none'); modalRedeemElm.classList.remove('d-flex'); }
    modalRedeemElm.addEventListener('click', event => { if (event.target === modalRedeemElm) closeRedeem(); });
    document.addEventListener('keydown', event => { if (event.key === 'Escape' && !modalRedeemElm.classList.contains('d-none')) closeRedeem(); });
</script>
<?= $this->endSection(); ?>
