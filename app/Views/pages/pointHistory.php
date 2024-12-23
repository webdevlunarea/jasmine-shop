<?= $this->extend('layout/template'); ?>
<?= $this->section('content'); ?>
<div class="konten">
    <div class="container">
        <div class="baris-ke-kolom-reverse">
            <div style="width: 30%;" class="show-ke-hide">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item"><a class="list" href="/account">Profileku</a></li>
                    <?php if (session()->get('role') == '0') { ?>
                        <li class="list-group-item"><a class="list" href="/transaction">Transaksi</a></li>
                        <li class="list-group-item"><b>Luna Poin</b></li>
                        <li class="list-group-item"><a class="list" href="/voucher">Voucher</a></li>
                    <?php } ?>
                    <li class="list-group-item"><a class="btn btn-outline-danger" href="/keluar">Keluar</a></li>
                </ul>
            </div>
            <!-- <div class="hide-ke-show-flex w-100 justify-content-center border-top pt-3 mt-2">
                <a class="btn btn-outline-danger" style="width: fit-content;" href="/keluar">Keluar</a>
            </div> -->
            <div class="w-100">
                <div class="p-2">
                    <h3 class="m-0">Luna Points History</h3>
                    <a href="/point" style="color: var(--hijau);" class="d-flex mb-3 align-items-center"><i class="material-icons">keyboard_arrow_left</i> POINT REWARDS</a>
                    <hr>
                    <div class="d-flex flex-column gap-1">
                        <?php if (count($history) > 0) { ?>
                            <?php foreach ($history as $ind_h => $h) { ?>
                                <div class="d-flex py-2 gap-5">
                                    <div style="flex: 1;">
                                        <h5 class="m-0"><?= ucwords($h['label']); ?></h5>
                                        <p class="m-0 text-secondary"><?= ucfirst($h['keterangan']); ?></p>
                                    </div>
                                    <div class="d-flex flex-column align-items-end">
                                        <div class="d-flex gap-1" <?= $h['nominal'] < 0 ? '' : 'style="color: var(--hijau);"'; ?>>
                                            <h5 class="m-0"><?= $h['nominal'] < 0 ? '- Rp ' . number_format($h['nominal'] * (-1), 0, ",", ".") : 'Rp ' . number_format($h['nominal'], 0, ",", "."); ?></h5>
                                            <p class="text-seconday m-0" style="font-size: small;">Pts</p>
                                        </div>
                                        <p class="m-0 text-secondary" style="font-size: small;"><?= explode('-', $h['tanggal'])[2] . " " . $bulan[(int)explode('-', $h['tanggal'])[1] - 1] . " " . explode('-', $h['tanggal'])[0]; ?></p>
                                    </div>
                                </div>
                            <?php } ?>
                        <?php } else { ?>
                            <p class="text-center">Belum ada riwayat poin</p>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>