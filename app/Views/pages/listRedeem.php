<?= $this->extend('layout/template'); ?>
<?= $this->section('content'); ?>
<div id="modal-redeem" class="d-none justify-content-center align-items-center" style="background-color: rgba(0, 0, 0, 0.5); position: fixed; left:0; top:0; width:100vw; height:100svh;">
    <div style="background-color: white;" class="p-4 rounded-3">
        <h5 class="mb-2">Tambah kode</h5>
        <form action="/redeemcode" method="post">
            <?= csrf_field(); ?>
            <div class="mb-1">
                <label>Voucher</label>
                <select name="voucher" class="form-select">
                    <?php foreach ($voucher as $v) { ?>
                        <option value="<?= $v['id']; ?>"><?= $v['nama']; ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="mb-3">
                <label>Code</label>
                <input name="code" type="text" class="form-control w-100">
            </div>
            <div class="d-flex justify-content-end gap-1">
                <button onclick="closeRedeem()" type="button" class="btn btn-outline-dark">Batal</button>
                <button type="submit" class="btn btn-primary1">Ok</button>
            </div>
        </form>
    </div>
</div>
<div class="konten admin-module-page admin-module-list-redeem">
    <div class="container">
        <div class="admin-module-heading d-flex justify-content-between align-items-center">
            <h3 class="">Kode redeem</h3>
            <button onclick="openRedeem()" class="btn btn-primary1">Tambah kode</button>
        </div>
        <div class="table-responsive" tabindex="0" role="region" aria-label="Daftar kode redeem">
            <table class="table admin-data-table align-middle">
                <thead><tr><th scope="col">No.</th><th scope="col">Voucher</th><th scope="col">Kode</th><th scope="col" class="text-end">Tindakan</th></tr></thead>
                <tbody>
                <?php if (!$redeem) { ?><tr><td colspan="4" class="admin-empty-state">Belum ada kode redeem. Tambahkan kode untuk voucher yang tersedia.</td></tr><?php } ?>
                <?php foreach ($redeem as $index=>$r) { ?>
                    <tr><td><?= $index+1; ?></td><td><?= esc($r['nama']); ?></td><td class="fw-semibold"><?= esc($r['code']); ?></td><td class="text-end"><button type="button" class="btn btn-light" disabled aria-label="Edit kode belum tersedia" title="Edit kode belum tersedia"><i class="material-icons" aria-hidden="true">edit</i></button> <button type="button" class="btn btn-outline-danger" disabled aria-label="Hapus kode belum tersedia" title="Hapus kode belum tersedia"><i class="material-icons" aria-hidden="true">delete</i></button></td></tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<script>
    const modalRedeemElm = document.getElementById('modal-redeem');

    function openRedeem() {
        modalRedeemElm.classList.remove('d-none')
        modalRedeemElm.classList.add('d-flex')
    }

    function closeRedeem() {
        modalRedeemElm.classList.add('d-none')
        modalRedeemElm.classList.remove('d-flex')
    }
</script>
<?= $this->endSection(); ?>
