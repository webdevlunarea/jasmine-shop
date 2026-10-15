<?= $this->extend("layout/template"); ?>
<?= $this->section("content"); ?>
<?php
$hasSearched = trim((string)($keyword ?? '')) !== '';
$summary = $tracking['summary'] ?? [];
$detail = $tracking['detail'] ?? [];
$history = $tracking['history'] ?? [];
$shipment = $shipment ?? null;
$shipmentCourier = $shipment
    ? ((($shipment['shipping_method_code'] ?? '') === 'internal_delivery') ? 'Jasa Kirim Toko' : trim(implode(' - ', array_filter([
        $shipment['courier_name'] ?? $shipment['courier_code'] ?? '',
        $shipment['service_name'] ?? $shipment['service_code'] ?? '',
    ]))))
    : '';
$shipmentCourier = $shipmentCourier ?: ($order['kurir'] ?? '-');
$shipmentAwb = trim((string)($shipment['tracking_number'] ?? $order['resi'] ?? ''));
$receiver = '';
if (!empty($order['nama_pen'])) {
    $parts = preg_split('/\s+/', trim($order['nama_pen']));
    $receiver = $parts[0] . (count($parts) > 1 ? ' ' . mb_substr(end($parts), 0, 1) . '.' : '');
}
?>
<main class="konten buyer-help-page tracking-page">
    <div class="container buyer-help-container"><?= view('partials/buyer_help_nav', ['activeHelp' => 'tracking'], ['saveData' => false]); ?></div>
    <section class="tracking-hero">
        <div class="container">
            <div class="tracking-hero-card">
                <div class="tracking-hero-copy">
                    <p class="buyer-help-eyebrow">Bantuan pengiriman</p>
                    <h1>Lacak pengiriman</h1>
                    <p>Cek posisi paket Lunarea memakai nomor invoice atau nomor resi. Bisa dipakai member maupun non-member.</p>
                </div>
                <form action="/lacak-pengiriman" method="post" class="tracking-form">
                    <label class="form-label" for="tracking-keyword">Nomor invoice / nomor resi</label>
                    <div class="input-group mb-2">
                        <input id="tracking-keyword" aria-describedby="tracking-hint" type="text" class="form-control" name="keyword" value="<?= esc($keyword ?? ''); ?>" placeholder="Contoh: ORDER-ID / JNE CM123456789" required>
                        <button class="btn btn-primary1" type="submit">Lacak</button>
                    </div>
                    <p class="tracking-hint" id="tracking-hint">Untuk nomor resi, pilih kurir atau tulis nama kurir sebelum resi, misalnya JNE NOMOR_RESI.</p>
                    <label class="form-label" for="tracking-courier">Kurir <span class="text-secondary">(opsional)</span></label>
                    <select id="tracking-courier" class="form-select" name="courier">
                        <option value="">Deteksi otomatis / dari data pesanan</option>
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
            <div class="alert alert-warning tracking-alert" role="status">
                <strong>Pengiriman belum ditemukan.</strong><br>
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
                    <strong><?= esc($shipmentCourier ?: '-'); ?></strong>
                </div>
                <div>
                    <p class="text-secondary mb-1">Resi</p>
                    <strong><?= esc($shipmentAwb !== '' ? $shipmentAwb : (($shipment['shipping_method_code'] ?? '') === 'internal_delivery' ? 'Internal' : '-')); ?></strong>
                </div>
            </section>
        <?php } ?>

        <?php if (!empty($tracking['success'])) { ?>
            <section class="tracking-summary">
                <div>
                    <p class="tracking-status-label">Status terakhir</p>
                    <h3><?= esc($summary['status'] ?? 'Dalam proses'); ?></h3>
                    <p class="mb-0 text-secondary"><?= esc($summary['desc'] ?? 'Data diperbarui dari layanan pengiriman.'); ?></p>
                </div>
                <div class="tracking-summary-grid">
                    <div>
                        <span>No. Resi</span>
                        <strong><?= esc($summary['awb'] ?? ($order['resi'] ?? $keyword)); ?></strong>
                    </div>
                    <div>
                        <span>Ekspedisi</span>
                        <strong><?= esc($summary['courier'] ?? $shipmentCourier ?? '-'); ?></strong>
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
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true" focusable="false"><path d="M4 12h16m-6-6 6 6-6 6"/></svg>
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
                        <p class="text-secondary mb-0">Riwayat akan muncul setelah pengiriman terdeteksi.</p>
                    </div>

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
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true" focusable="false"><circle cx="10.5" cy="10.5" r="6.5"/><path d="m16 16 5 5"/></svg>
                <h5 class="fw-bold">Masukkan nomor invoice atau resi</h5>
                <p class="text-secondary mb-0">Kalau memakai nomor invoice, kurir dan resi akan dicari otomatis dari data pesanan.</p>
            </section>
        <?php } ?>
    </div>
</main>

<?= $this->endSection(); ?>
