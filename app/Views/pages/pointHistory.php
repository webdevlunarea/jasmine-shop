<?= $this->extend('layout/template'); ?>
<?= $this->section('content'); ?>
<?php
$activeAccountMenu = 'point';
$formatTanggal = static function ($date) use ($bulan) {
    $parts = explode('-', (string)$date);
    if (count($parts) !== 3) return $date;
    return $parts[2] . ' ' . $bulan[((int)$parts[1]) - 1] . ' ' . $parts[0];
};
$getType = static function ($row) {
    $nominal = (int)($row['nominal'] ?? 0);
    $label = strtolower((string)($row['label'] ?? ''));
    if ($nominal < 0 || strpos($label, 'pembelian') !== false || strpos($label, 'kedaluwarsa') !== false) return 'minus';
    return 'plus';
};
?>
<div class="konten account-shell">
    <div class="container">
        <div class="account-layout luna-point-page">
            <?= $this->include('partials/account_nav'); ?>
            <main class="account-content-card">
                <div class="d-flex justify-content-between align-items-start gap-2 flex-wrap mb-3">
                    <div>
                        <p class="luna-eyebrow mb-1">Mutasi Poin</p>
                        <h3 class="m-0">Riwayat Luna Point</h3>
                        <p class="text-secondary mb-0">Semua poin masuk, poin dipakai, dan poin kedaluwarsa tercatat di sini.</p>
                    </div>
                    <a href="/point" class="btn btn-outline-success btn-sm d-flex align-items-center gap-1">
                        <i class="material-icons" aria-hidden="true">keyboard_arrow_left</i> Kembali
                    </a>
                </div>

                <div class="luna-history-list">
                    <?php if (count($history) > 0) { ?>
                        <?php foreach ($history as $h) { ?>
                            <?php
                            $type = $getType($h);
                            $nominal = abs((int)($h['nominal'] ?? 0));
                            ?>
                            <article class="luna-history-item <?= $type === 'minus' ? 'is-minus' : 'is-plus'; ?>">
                                <div class="luna-history-icon">
                                    <i class="material-icons" aria-hidden="true"><?= $type === 'minus' ? 'remove' : 'add'; ?></i>
                                </div>
                                <div class="luna-history-main">
                                    <h6 class="mb-1"><?= esc(ucwords((string)$h['label'])); ?></h6>
                                    <p class="mb-0 text-secondary"><?= esc(ucfirst((string)$h['keterangan'])); ?></p>
                                    <small class="text-secondary"><?= esc($formatTanggal($h['tanggal'])); ?></small>
                                </div>
                                <div class="luna-history-amount">
                                    <?= $type === 'minus' ? '-' : '+'; ?><?= number_format($nominal, 0, ',', '.'); ?> pts
                                </div>
                            </article>
                        <?php } ?>
                    <?php } else { ?>
                        <div class="luna-empty-state">
                            <i class="material-icons" aria-hidden="true">stars</i>
                            <h5>Belum ada riwayat poin</h5>
                            <p class="text-secondary mb-0">Riwayat akan muncul setelah kamu mendapatkan atau memakai Luna Point.</p>
                        </div>
                    <?php } ?>
                </div>
            </main>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>
