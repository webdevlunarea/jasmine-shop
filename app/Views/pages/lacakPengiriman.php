<?= $this->extend("layout/template"); ?>
<?= $this->section("content"); ?>
<?php
$hasSearched = trim((string)($keyword ?? '')) !== '';
$summary = $tracking['summary'] ?? [];
$detail = $tracking['detail'] ?? [];
$history = $tracking['history'] ?? [];
$receiver = '';
if (!empty($order['nama_pen'])) {
    $parts = preg_split('/\s+/', trim($order['nama_pen']));
    $receiver = $parts[0] . (count($parts) > 1 ? ' ' . mb_substr(end($parts), 0, 1) . '.' : '');
}
?>
<div class="konten tracking-page">
    <section class="tracking-hero">
        <div class="container">
            <div class="tracking-hero-card">
                <div class="tracking-hero-copy">
                    <span class="tracking-badge"><i class="material-icons">local_shipping</i> Real-time tracking</span>
                    <h1>Lacak Pengiriman</h1>
                    <p>Cek posisi paket Lunarea memakai nomor invoice atau nomor resi. Bisa dipakai member maupun non-member.</p>
                </div>
                <form action="/lacak-pengiriman" method="post" class="tracking-form">
                    <label class="form-label fw-bold">Nomor invoice / nomor resi</label>
                    <div class="input-group mb-2">
                        <input type="text" class="form-control" name="keyword" value="<?= esc($keyword ?? ''); ?>" placeholder="Contoh: ORDER-ID / JP1234567890" required>
                        <button class="btn btn-primary1" type="submit">Lacak</button>
                    </div>
                    <label class="form-label small text-secondary">Kurir opsional, dipakai kalau mencari langsung dengan nomor resi.</label>
                    <select class="form-select" name="courier">
                        <option value="">Deteksi dari data pesanan</option>
                        <?php foreach ($couriers as $code => $label) { ?>
                            <option value="<?= esc($code); ?>" <?= ($courier ?? '') === $code ? 'selected' : ''; ?>><?= esc($label); ?></option>
                        <?php } ?>
                    </select>
                </form>
            </div>
        </div>
    </section>

    <div class="container tracking-result-wrap">
        <?php if ($hasSearched && !empty($error)) { ?>
            <div class="alert alert-warning tracking-alert">
                <strong>Tracking belum berhasil.</strong><br>
                <?= esc($error); ?>
            </div>
        <?php } ?>

        <?php if ($hasSearched && !empty($order)) { ?>
            <section class="tracking-order-card">
                <div>
                    <p class="text-secondary mb-1">Pesanan ditemukan</p>
                    <h5 class="fw-bold mb-0"><?= esc($order['id_midtrans']); ?></h5>
                </div>
                <div>
                    <p class="text-secondary mb-1">Penerima</p>
                    <strong><?= esc($receiver ?: '-'); ?></strong>
                </div>
                <div>
                    <p class="text-secondary mb-1">Kurir</p>
                    <strong><?= esc($order['kurir'] ?: '-'); ?></strong>
                </div>
                <div>
                    <p class="text-secondary mb-1">Resi</p>
                    <strong><?= esc($order['resi'] ?: '-'); ?></strong>
                </div>
            </section>
        <?php } ?>

        <?php if (!empty($tracking['success'])) { ?>
            <section class="tracking-summary">
                <div>
                    <p class="tracking-status-label">Status terakhir</p>
                    <h3><?= esc($summary['status'] ?? 'Dalam proses'); ?></h3>
                    <p class="mb-0 text-secondary"><?= esc($summary['desc'] ?? 'Data diperbarui dari API kurir.'); ?></p>
                </div>
                <div class="tracking-summary-grid">
                    <div>
                        <span>No. Resi</span>
                        <strong><?= esc($summary['awb'] ?? ($order['resi'] ?? $keyword)); ?></strong>
                    </div>
                    <div>
                        <span>Ekspedisi</span>
                        <strong><?= esc($summary['courier'] ?? ($order['kurir'] ?? '-')); ?></strong>
                    </div>
                    <div>
                        <span>Layanan</span>
                        <strong><?= esc($summary['service'] ?? '-'); ?></strong>
                    </div>
                    <div>
                        <span>Update</span>
                        <strong><?= esc($summary['date'] ?? '-'); ?></strong>
                    </div>
                </div>
            </section>

            <?php if (!empty($detail['origin']) || !empty($detail['destination'])) { ?>
                <section class="tracking-route">
                    <div>
                        <span>Dari</span>
                        <strong><?= esc($detail['origin'] ?? '-'); ?></strong>
                    </div>
                    <i class="material-icons">arrow_forward</i>
                    <div>
                        <span>Tujuan</span>
                        <strong><?= esc($detail['destination'] ?? '-'); ?></strong>
                    </div>
                </section>
            <?php } ?>

            <section class="tracking-timeline-card">
                <div class="d-flex justify-content-between align-items-center mb-3 gap-2 flex-wrap">
                    <div>
                        <h5 class="fw-bold mb-1">Riwayat pengiriman</h5>
                        <p class="text-secondary mb-0">Data langsung dari provider tracking.</p>
                    </div>
                    <span class="tracking-provider">API <?= esc($tracking['provider'] ?? 'Tracking'); ?></span>
                </div>

                <?php if (empty($history)) { ?>
                    <div class="alert alert-info mb-0">Riwayat pengiriman belum tersedia dari kurir.</div>
                <?php } ?>

                <?php foreach ($history as $index => $item) { ?>
                    <div class="tracking-timeline-item <?= $index === 0 ? 'active' : ''; ?>">
                        <div class="tracking-dot"></div>
                        <div class="tracking-time">
                            <strong><?= esc(date('H:i', strtotime($item['date'] ?? 'now'))); ?></strong>
                            <span><?= esc(date('d M Y', strtotime($item['date'] ?? 'now'))); ?></span>
                        </div>
                        <div class="tracking-note">
                            <p class="mb-1"><?= esc($item['desc'] ?? '-'); ?></p>
                            <span><?= esc($item['location'] ?? ''); ?></span>
                        </div>
                    </div>
                <?php } ?>
            </section>
        <?php } elseif (!$hasSearched) { ?>
            <section class="tracking-empty">
                <i class="material-icons">search</i>
                <h5 class="fw-bold">Masukkan nomor invoice atau resi</h5>
                <p class="text-secondary mb-0">Kalau memakai nomor invoice, kurir dan resi akan dicari otomatis dari data pesanan.</p>
            </section>
        <?php } ?>
    </div>
</div>

<style>
    .tracking-page {
        background: linear-gradient(180deg, var(--hijaumuda) 0%, #fff 320px);
    }

    .tracking-hero {
        padding: 26px 0 18px;
    }

    .tracking-hero-card {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(320px, 480px);
        gap: 22px;
        align-items: center;
        border: 1px solid var(--hijaumuda2);
        border-radius: 28px;
        padding: clamp(18px, 3vw, 32px);
        background: radial-gradient(circle at 8% 0%, #fff 0%, transparent 35%), linear-gradient(135deg, #fff, var(--hijaumuda1));
        box-shadow: 0 18px 42px rgba(36, 59, 107, .10);
    }

    .tracking-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 11px;
        border-radius: 999px;
        background: var(--hijau);
        color: #fff;
        font-weight: 800;
        font-size: .78rem;
        text-transform: uppercase;
        letter-spacing: .06em;
    }

    .tracking-badge .material-icons {
        font-size: 16px;
    }

    .tracking-hero-copy h1 {
        margin: 12px 0 8px;
        color: var(--hijau);
        font-weight: 900;
        letter-spacing: -.04em;
        font-size: clamp(2rem, 5vw, 3.2rem);
    }

    .tracking-hero-copy p {
        max-width: 560px;
        color: #66737f;
        font-size: 1rem;
        line-height: 1.65;
    }

    .tracking-form {
        padding: 16px;
        border-radius: 22px;
        background: #fff;
        border: 1px solid rgba(36, 59, 107, .10);
    }

    .tracking-result-wrap {
        display: grid;
        gap: 16px;
        padding-bottom: 42px;
    }

    .tracking-alert,
    .tracking-order-card,
    .tracking-summary,
    .tracking-route,
    .tracking-timeline-card,
    .tracking-empty {
        border-radius: 22px;
        border: 1px solid rgba(36, 59, 107, .10);
        box-shadow: 0 12px 28px rgba(36, 59, 107, .06);
    }

    .tracking-order-card {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px;
        padding: 18px;
        background: #fff;
    }

    .tracking-summary {
        display: grid;
        grid-template-columns: minmax(0, .9fr) minmax(0, 1.1fr);
        gap: 18px;
        padding: 20px;
        background: #fff;
    }

    .tracking-status-label,
    .tracking-summary-grid span,
    .tracking-route span {
        color: #66737f;
        margin-bottom: 4px;
        display: block;
    }

    .tracking-summary h3 {
        color: var(--hijau);
        font-weight: 900;
        margin-bottom: 4px;
    }

    .tracking-summary-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
    }

    .tracking-summary-grid div,
    .tracking-route div {
        padding: 12px;
        border-radius: 16px;
        background: var(--hijaumuda);
    }

    .tracking-route {
        display: grid;
        grid-template-columns: 1fr auto 1fr;
        align-items: center;
        gap: 12px;
        padding: 16px;
        background: #fff;
    }

    .tracking-route .material-icons {
        color: var(--hijau);
    }

    .tracking-timeline-card {
        padding: 20px;
        background: #fff;
    }

    .tracking-provider {
        padding: 6px 10px;
        border-radius: 999px;
        background: var(--hijaumuda);
        color: var(--hijau);
        font-weight: 800;
        font-size: .8rem;
    }

    .tracking-timeline-item {
        position: relative;
        display: grid;
        grid-template-columns: 20px 100px minmax(0, 1fr);
        gap: 12px;
        padding: 0 0 18px;
    }

    .tracking-timeline-item::before {
        content: "";
        position: absolute;
        left: 9px;
        top: 20px;
        bottom: 0;
        width: 2px;
        background: var(--hijaumuda2);
    }

    .tracking-timeline-item:last-child::before {
        display: none;
    }

    .tracking-dot {
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background: var(--hijaumuda2);
        border: 4px solid #fff;
        box-shadow: 0 0 0 1px var(--hijaumuda2);
        z-index: 1;
    }

    .tracking-timeline-item.active .tracking-dot {
        background: var(--hijau);
        box-shadow: 0 0 0 4px var(--hijaumuda1);
    }

    .tracking-time strong {
        display: block;
        color: var(--hijau);
    }

    .tracking-time span,
    .tracking-note span {
        color: #66737f;
        font-size: .85rem;
    }

    .tracking-note {
        padding: 12px;
        border-radius: 16px;
        background: var(--hijaumuda);
    }

    .tracking-empty {
        text-align: center;
        padding: 34px 18px;
        background: #fff;
    }

    .tracking-empty .material-icons {
        color: var(--hijau);
        font-size: 40px;
    }

    @media (max-width: 991.98px) {
        .tracking-hero-card,
        .tracking-summary {
            grid-template-columns: 1fr;
        }

        .tracking-order-card {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 575.98px) {
        .tracking-hero-card {
            border-radius: 22px;
            padding: 16px;
        }

        .tracking-order-card,
        .tracking-summary-grid,
        .tracking-route {
            grid-template-columns: 1fr;
        }

        .tracking-route .material-icons {
            transform: rotate(90deg);
            margin: auto;
        }

        .tracking-timeline-item {
            grid-template-columns: 20px minmax(0, 1fr);
        }

        .tracking-time {
            grid-column: 2;
        }

        .tracking-note {
            grid-column: 2;
        }
    }
</style>
<?= $this->endSection(); ?>
