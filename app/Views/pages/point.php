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
                    <li class="list-group-item"><a class="btn btn-outline-danger" href="/hapuslocalstorage/<?= base64_encode('/keluar'); ?>">Keluar</a></li>
                </ul>
            </div>
            <!-- <div class="hide-ke-show-flex w-100 justify-content-center border-top pt-3 mt-2">
                <a class="btn btn-outline-danger" style="width: fit-content;" href="/hapuslocalstorage/<?= base64_encode('/keluar'); ?>">Keluar</a>
            </div> -->
            <div class="w-100">
                <div class="p-2">
                    <h3 class="mb-3">Luna Points Rewards</h3>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-secondary m-0">Kamu sebagai</p>
                            <h3 class="m-0"><?= strtoupper($tier['label']); ?> USER</h3>
                            <a href="/point/history" style="color: var(--hijau);" class="d-flex align-items-center">POINT HISTORY <i class="material-icons">keyboard_arrow_right</i></a>
                        </div>
                        <div class="baris-ke-kolom gap-1 align-items-end justify-content-end">
                            <h3 class="m-0"><?= number_format($poin, 0, ",", "."); ?></h3>
                            <h5 class="m-0" style="color: rgb(182, 182, 182);">Points</h5>
                        </div>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-center">
                        <div class="d-flex flex-column align-items-center">
                            <div class="d-flex justify-content-center w-100 mb-3">
                                <div class="tier-point <?= $poin >= 0 ? 'aktif' : ''; ?>"></div>
                            </div>
                            <div class="rounded p-3 mb-2" style="box-shadow: 0 3px 5px rgba(0,0,0,0.1);">
                                <div class="tier small bronze <?= ($poin >= 0 && $poin < 10000000) ? 'aktif' : ''; ?>">
                                    <i class="material-icons">star</i>
                                </div>
                            </div>
                            <p class="m-0 fw-bold">Bronze</p>
                            <p class="m-0 text-secondary" style="font-size: small;">0 PTS</p>
                        </div>
                        <div class="tier-split">
                            <div style="width: 70px; height: 12px; position: absolute;">
                                <div class="progres-tier">
                                    <div style="width: <?= $poin < 10000000 ? ($poin / 10000000) * 100 . '%' : '100%'; ?>;" class="bar"></div>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex flex-column align-items-center">
                            <div class="d-flex justify-content-center w-100 mb-3">
                                <div class="tier-point <?= $poin >= 10000000 ? 'aktif' : ''; ?>"></div>
                            </div>
                            <div class="rounded p-3 mb-2" style="box-shadow: 0 3px 5px rgba(0,0,0,0.1);">
                                <div class="tier small silver <?= ($poin >= 10000000 && $poin < 50000000) ? 'aktif' : ''; ?>">
                                    <i class="material-icons">star</i>
                                </div>
                            </div>
                            <p class="m-0 fw-bold">Silver</p>
                            <p class="m-0 text-secondary" style="font-size: small;">10JT PTS</p>
                        </div>
                        <div class="tier-split">
                            <div style="width: 70px; height: 12px; position: absolute;">
                                <div class="progres-tier">
                                    <div style="width: <?= $poin < 50000000 ? ($poin >= 10000000 ? (($poin - 10000000) / 40000000) * 100 . '%' : '0%') : '100%'; ?>;" class="bar"></div>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex flex-column align-items-center">
                            <div class="d-flex justify-content-center w-100 mb-3">
                                <div class="tier-point <?= $poin >= 50000000 ? 'aktif' : ''; ?>"></div>
                            </div>
                            <div class="rounded p-3 mb-2" style="box-shadow: 0 3px 5px rgba(0,0,0,0.1);">
                                <div class="tier small gold <?= ($poin >= 50000000 && $poin < 100000000) ? 'aktif' : ''; ?>">
                                    <i class="material-icons">star</i>
                                </div>
                            </div>
                            <p class="m-0 fw-bold">Gold</p>
                            <p class="m-0 text-secondary" style="font-size: small;">50JT PTS</p>
                        </div>
                        <div class="tier-split">
                            <div style="width: 70px; height: 12px; position: absolute;">
                                <div class="progres-tier">
                                    <div style="width: <?= $poin < 100000000 ? ($poin >= 50000000 ? (($poin - 50000000) / 50000000) * 100 . '%' : '0%') : '100%'; ?>;" class="bar"></div>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex flex-column align-items-center">
                            <div class="d-flex justify-content-center w-100 mb-3">
                                <div class="tier-point <?= $poin >= 100000000 ? 'aktif' : ''; ?>"></div>
                            </div>
                            <div class="rounded p-3 mb-2" style="box-shadow: 0 3px 5px rgba(0,0,0,0.1);">
                                <div class="tier small platinum <?= $poin >= 100000000 ? 'aktif' : ''; ?>">
                                    <i class="material-icons">star</i>
                                </div>
                            </div>
                            <p class="m-0 fw-bold">Platinum</p>
                            <p class="m-0 text-secondary" style="font-size: small;">100JT PTS</p>
                        </div>
                    </div>
                    <hr>
                    <h5 class="jdl-section mb-3">Klaim bonus Kamu</h5>
                    <div class="d-flex flex-column gap-1">
                        <?php foreach ($bonus[$tier['label']] as $ind_b => $b) { ?>
                            <div class="py-4 px-5 rounded-1" style="box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
                                <div class="d-flex gap-3" <?= isset($b['ket_nonaktif']) ? 'style="filter:saturate(0.3) opacity(0.2);"' : ''; ?>>
                                    <?php if ($b['nominal']) { ?>
                                        <div style="width: 70px;" class="show-ke-hide">
                                            <h1 class="m-0" style="color: var(--hijau);"><?= $b['nominal']; ?></h1>
                                        </div>
                                    <?php } ?>
                                    <div>
                                        <h5 class="m-0"><?= $b['nama']; ?></h5>
                                        <p class="m-0 text-secondary"><?= $b['keterangan']; ?></p>
                                    </div>
                                </div>
                                <?php if (isset($b['ket_nonaktif'])) { ?>
                                    <p class="mt-1 mb-0 text-danger">*<?= $b['ket_nonaktif']; ?></p>
                                <?php } ?>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>