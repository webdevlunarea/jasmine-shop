<?= $this->extend('layout/template'); ?>
<?= $this->section('content'); ?>
<?php
$activeAccountMenu = 'point';
$balance = (int)($poin ?? 0);
$tierLabel = strtolower($tier['label'] ?? 'bronze');
$tierName = $tierMeta['label'] ?? ucwords($tierLabel);
$tierMin = (int)($tierMeta['min'] ?? 0);
$tierNext = $tierMeta['next'] ?? null;
$tierSpend = array_reduce(($tier['data'] ?? []), static fn($carry, $row) => $carry + (int)($row['nominal'] ?? 0), 0);
$progress = $tierNext ? max(0, min(100, (($tierSpend - $tierMin) / max(1, ((int)$tierNext - $tierMin))) * 100)) : 100;
$nextExpiry = $pointSummary['next_expiry'] ?? null;
$expiredCount = count($pointSummary['expired'] ?? []);
$formatTanggal = static function ($date) {
    if (!$date) return 'Belum ada';
    $bulan = ["Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Agu", "Sep", "Okt", "Nov", "Des"];
    $parts = explode('-', $date);
    if (count($parts) !== 3) return $date;
    return $parts[2] . ' ' . $bulan[((int)$parts[1]) - 1] . ' ' . $parts[0];
};
?>
<div class="konten account-shell">
    <div class="container">
        <div class="account-layout luna-point-page">
            <?= $this->include('partials/account_nav'); ?>
            <main class="account-content-card">
                <section class="luna-point-hero">
                    <div>
                        <p class="luna-eyebrow mb-1">Luna Rewards</p>
                        <h3 class="mb-2">Luna Point</h3>
                        <p class="text-secondary mb-0">Gunakan poin untuk mengurangi total pembayaran saat checkout.</p>
                    </div>
                    <div class="luna-point-balance">
                        <span>Saldo aktif</span>
                        <strong><?= number_format($balance, 0, ',', '.'); ?></strong>
                        <small>1 poin = Rp1</small>
                    </div>
                </section>

                <?php if ($expiredCount > 0) { ?>
                    <div class="luna-point-notice mt-3">
                        <i class="material-icons" aria-hidden="true">info</i>
                        <span><?= $expiredCount; ?> saldo poin yang sudah melewati masa berlaku sudah dirapikan otomatis.</span>
                    </div>
                <?php } ?>

                <section class="luna-point-grid mt-3">
                    <div class="luna-point-panel">
                        <div class="d-flex justify-content-between gap-2 align-items-start mb-3">
                            <div>
                                <p class="text-secondary mb-1">Status member</p>
                                <h4 class="mb-0"><?= esc($tierName); ?> User</h4>
                            </div>
                            <span class="luna-tier-badge luna-tier-<?= esc($tierLabel); ?>"><?= esc($tierName); ?></span>
                        </div>
                        <div class="luna-tier-track" aria-label="Progress tier">
                            <span style="width: <?= $progress; ?>%;"></span>
                        </div>
                        <div class="d-flex justify-content-between mt-2 luna-tier-caption">
                            <span><?= number_format($tierMin, 0, ',', '.'); ?></span>
                            <span><?= $tierNext ? number_format((int)$tierNext, 0, ',', '.') : 'Tier tertinggi'; ?></span>
                        </div>
                        <p class="text-secondary small mt-3 mb-0">
                            <?= $tierNext ? 'Total belanja tier saat ini: Rp ' . number_format($tierSpend, 0, ',', '.') . '.' : 'Kamu sudah berada di tier tertinggi Luna Rewards.'; ?>
                        </p>
                    </div>

                    <div class="luna-point-panel">
                        <p class="text-secondary mb-1">Poin terdekat kedaluwarsa</p>
                        <h4 class="mb-1"><?= esc($formatTanggal($nextExpiry)); ?></h4>
                        <p class="text-secondary small mb-3">Poin dipakai otomatis dari masa berlaku terdekat lebih dulu.</p>
                        <a href="/point/history" class="btn btn-primary1 w-100 d-flex justify-content-center align-items-center gap-1">
                            Lihat riwayat <i class="material-icons" aria-hidden="true">chevron_right</i>
                        </a>
                    </div>
                </section>

                <hr class="my-4">

                <section>
                    <div class="d-flex justify-content-between align-items-end gap-2 mb-3">
                        <div>
                            <p class="luna-eyebrow mb-1">Benefit</p>
                            <h5 class="jdl-section mb-0">Bonus sesuai tier</h5>
                        </div>
                    </div>
                    <div class="luna-benefit-list">
                        <?php foreach (($bonus[$tier['label']] ?? []) as $b) { ?>
                            <div class="luna-benefit-item <?= isset($b['ket_nonaktif']) ? 'is-locked' : ''; ?>">
                                <div class="luna-benefit-icon"><?= $b['nominal'] ? esc($b['nominal']) : '<i class="material-icons">redeem</i>'; ?></div>
                                <div>
                                    <h6 class="mb-1"><?= esc($b['nama']); ?></h6>
                                    <p class="mb-0 text-secondary"><?= esc($b['keterangan']); ?></p>
                                    <?php if (isset($b['ket_nonaktif'])) { ?>
                                        <small class="text-danger d-block mt-1">*<?= esc($b['ket_nonaktif']); ?></small>
                                    <?php } ?>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                </section>
            </main>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>
