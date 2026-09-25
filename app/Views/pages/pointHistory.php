<?= $this->extend('layout/template'); ?>
<?= $this->section('content'); ?>
<div class="konten account-shell">
    <div class="container">
        <?php $activeAccountMenu = 'point'; ?>
        <div class="account-layout">
            <?= $this->include('partials/account_nav'); ?>
            <main class="account-content-card">
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
            </main>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>
