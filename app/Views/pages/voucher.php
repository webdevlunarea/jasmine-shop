<?= $this->extend('layout/template'); ?>
<?= $this->section('content'); ?>
<style>
    .input-redeem {
        margin-top: 0px;
        max-height: 0px;
        transition: 0.3s;
        overflow-y: hidden;
    }

    .input-redeem.show {
        margin-top: 8px;
        max-height: 100px;
        transition: 0.3s;
    }
</style>
<div id="modal-redeem" class="d-none justify-content-center align-items-center" style="z-index: 12; background-color: rgba(0, 0, 0, 0.5); position:fixed; left: 0; top: 0; height: 100svh; width: 100vw;">
    <div style="background-color: white;" class="p-4 rounded-3">
        <h5 class="m-0">Redeem Voucher</h5>
        <p class="m-0 text-secondary mb-1">Masukan code redeem Kamu</p>
        <form action="/voucher/redeem" method="post">
            <input type="text" name="code" class="form-control mb-2 w-100">
            <div class="d-flex justify-content-end gap-1">
                <button onclick="closeRedeem()" type="button" class="btn btn-outline-dark">Batal</button>
                <button type="submit" class="btn btn-primary1">Ok</button>
            </div>
        </form>
    </div>
</div>
<div class="konten">
    <div class="container">
        <div class="baris-ke-kolom-reverse">
            <div style="width: 30%;" class="show-ke-hide">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item"><a class="list" href="/account">Profileku</a></li>
                    <?php if (session()->get('role') == '0') { ?>
                        <li class="list-group-item"><a class="list" href="/transaction">Transaksi</a></li>
                        <li class="list-group-item"><a class="list" href="/point">Luna poin</a></li>
                        <li class="list-group-item"><b>Voucher</b></li>
                    <?php } ?>
                    <li class="list-group-item"><a class="btn btn-outline-danger" href="/keluar">Keluar</a></li>
                </ul>
            </div>
            <!-- <div class="hide-ke-show-flex w-100 justify-content-center border-top pt-3 mt-2">
                <a class="btn btn-outline-danger" style="width: fit-content;" href="/keluar">Keluar</a>
            </div> -->
            <div class="w-100">
                <div class="p-2">
                    <div>
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3 class="m-0">Klaim Voucher Kamu</h3>
                                <p class="m-0">Berikut beberapa voucher yang sedang aktif</p>
                            </div>
                            <div>
                                <button onclick="openRedeem()" class="btn btn-primary1">Redeem voucher</button>
                            </div>
                        </div>
                        <?php if ($msg) { ?>
                            <div class="alert alert-primary mt-2" role="alert">
                                <?= $msg; ?>
                            </div>
                        <?php } ?>
                    </div>
                    <hr>
                    <div class="d-flex flex-column gap-1">
                        <?php if (count($voucherClaimed) > 0 || count($voucher) > 0) { ?>
                            <?php foreach ($voucherClaimed as $v) { ?>
                                <div class="list-voucher-customer aktif">
                                    <div class="baris-ke-kolom justify-content-between">
                                        <div>
                                            <p class="m-0" style="color: var(--hijau);"><?= ucwords($v['jenis']); ?></p>
                                            <h4 class="m-0"><?= ucwords($v['nama']); ?></h4>
                                            <p class="m-0 text-secondary"><?= $v['keterangan']; ?></p>
                                        </div>
                                        <div class="d-flex gap-1 align-items-end justify-content-end flex-column">
                                            <p class="m-0" style="text-align:end; text-wrap:nowrap;"><b>Kadaluarsa sampai</b></p>
                                            <p class="m-0 text-secondary" style="text-align:end;"><?= $v['kadaluarsa']; ?></p>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                            <?php foreach ($voucher as $ind_v => $v) { ?>
                                <form action="/voucher/claim" method="post">
                                    <div class="list-voucher-customer">
                                        <div class="baris-ke-kolom justify-content-between">
                                            <div>
                                                <p class="m-0" style="color: var(--hijau);"><?= ucwords($v['jenis']); ?></p>
                                                <h4 class="m-0"><?= ucwords($v['nama']); ?></h4>
                                                <p class="m-0 text-secondary"><?= $v['keterangan']; ?></p>
                                            </div>
                                            <div class="d-flex gap-1 align-items-center">
                                                <input style="display: none;" type="text" name="id_voucher" value="<?= $v['id']; ?>">
                                                <input style="display: none;" type="text" name="ind_user" value="<?= $v['index']; ?>">
                                                <button id="btn-redeem<?= $ind_v ?>" type="<?= isset($v['code'][$v['index']]['code']) ? 'button' : 'submit'; ?>" class="btn btn-primary1">Klaim</button>
                                            </div>
                                        </div>
                                        <?php if (isset($v['code'][$v['index']]['code'])) { ?>
                                            <div class="d-flex gap-1 align-items-center input-redeem">
                                                <input name="code" required type="text" placeholder="Tulis code kamu" class="form-control">
                                                <button type="submit" style="text-wrap: nowrap;" class="d-flex btn btn-primary1"><i class="material-icons">check</i></button>
                                            </div>
                                            <script>
                                                const btnRedeem<?= $ind_v ?> = document.getElementById('btn-redeem<?= $ind_v ?>');
                                                btnRedeem<?= $ind_v ?>.addEventListener('click', (e) => {
                                                    e.target.classList.add('d-none');
                                                    const inputRedeemElm = e.target.parentNode.parentNode.nextSibling.nextSibling;
                                                    inputRedeemElm.classList.add('show')
                                                })
                                            </script>
                                        <?php } ?>
                                    </div>
                                </form>
                            <?php } ?>
                        <?php } else { ?>
                            <p class="text-center text-secondary">Tidak ada voucher</p>
                        <?php } ?>
                    </div>
                </div>
            </div>
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