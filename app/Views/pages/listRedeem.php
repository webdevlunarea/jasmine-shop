<?= $this->extend('layout/template'); ?>
<?= $this->section('content'); ?>
<div id="modal-redeem" class="d-none justify-content-center align-items-center" style="background-color: rgba(0, 0, 0, 0.5); position: fixed; left:0; top:0; width:100vw; height:100svh;">
    <div style="background-color: white;" class="p-4 rounded-3">
        <h5 class="mb-2">Tambah Code</h5>
        <form action="/redeemcode" method="post">
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
<div class="konten">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center">
            <h3 class="">List Redeem Code</h3>
            <button onclick="openRedeem()" class="btn btn-primary1">Tambah Code</button>
        </div>
        <hr>
        <div class="d-flex border-bottom py-2 fw-bold">
            <div style="flex: 1;">No</div>
            <div style="flex: 3;">Nama Voucher</div>
            <div style="flex: 3;">Code</div>
            <div style="flex: 3;">Action</div>
        </div>
        <?php
        $no = 1;
        if (count($redeem) > 0) {
            foreach ($redeem as $r) { ?>
                <div class="d-flex py-2">
                    <div style="flex: 1;"><?= $no; ?></div>
                    <div style="flex: 3;"><?= $r['nama']; ?></div>
                    <div style="flex: 3;"><?= $r['code']; ?></div>
                    <div style="flex: 3;">
                        <button class="btn btn-outline-dark"><i class="material-icons">edit</i></button>
                        <button class="btn btn-outline-danger"><i class="material-icons">delete</i></button>
                    </div>
                </div>
            <?php
                $no++;
            }
        } else { ?>
            <div class="d-flex py-2 justify-content-center">
                <p>Tidak ada redeem code</p>
            </div>
        <?php } ?>
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