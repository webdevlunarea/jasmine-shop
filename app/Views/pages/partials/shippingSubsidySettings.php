<?php
try { $subsidy = \App\Services\Shipping\ShippingSubsidyPolicy::normalize($rajaSettings['subsidy'] ?? []); }
catch (\DomainException $e) { $subsidy = \App\Services\Shipping\ShippingSubsidyPolicy::normalize([]); }
$configuredCouriers = $rajaSettings['couriers'] ?? ['jne', 'jnt', 'sicepat', 'tiki', 'pos', 'sentral'];
if (!is_array($configuredCouriers)) {
    $configuredCouriers = preg_split('/[:,]/', (string)$configuredCouriers);
}
$configuredCouriers = array_values(array_filter(array_map(static fn($code) => strtolower(trim((string)$code)), $configuredCouriers)));
$subsidyCouriers = array_values(array_unique(array_merge($configuredCouriers, $subsidy['couriers'])));
$percentageDisplay = number_format($subsidy['percentage_bps'] / 100, 2, '.', '');
?>
<section class="shipping-admin-panel mt-3" id="shipping-subsidy" aria-labelledby="shipping-subsidy-title">
    <div class="shipping-panel-head">
        <div>
            <h5 class="fw-bold mb-1" id="shipping-subsidy-title">Subsidi Ongkir</h5>
            <p class="text-secondary mb-0">Toko menanggung sebagian tarif ekspedisi. Pembeli hanya melihat ongkir akhir, tanpa label subsidi.</p>
        </div>
        <span class="badge <?= $subsidy['enabled'] && !empty($subsidyStorageReady) ? 'text-bg-success' : 'text-bg-secondary'; ?>"><?= $subsidy['enabled'] && !empty($subsidyStorageReady) ? 'Diaktifkan' : 'Nonaktif'; ?></span>
    </div>
    <?php if (empty($subsidyStorageReady)) { ?>
        <div class="alert alert-warning small">Aktivasi memerlukan pembaruan kolom <code>shipping_pricing</code>. Sebelum itu, checkout tetap menggunakan tarif normal.</div>
    <?php } ?>
    <form action="/shippingadmin/subsidy" method="post" class="shipping-form-stack">
        <?= csrf_field(); ?>
        <label class="shipping-admin-toggle">
            <input type="checkbox" name="subsidy_enabled" value="1" <?= $subsidy['enabled'] ? 'checked' : ''; ?> <?= empty($subsidyStorageReady) ? 'disabled' : ''; ?>>
            <span><strong>Aktifkan subsidi ongkir</strong><small>Hanya untuk ekspedisi. Jasa Kirim Toko tetap Rp0.</small></span>
        </label>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label fw-bold" for="subsidy-percentage">Persentase subsidi (%)</label>
                <input id="subsidy-percentage" type="number" min="0" max="100" step="0.01" name="subsidy_percentage" value="<?= esc($percentageDisplay); ?>" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-bold" for="subsidy-max">Maksimal subsidi per pesanan (Rp)</label>
                <input id="subsidy-max" type="number" min="0" max="2000000000" step="1" name="subsidy_max_amount" value="<?= (int)$subsidy['max_amount']; ?>" class="form-control" required>
                <small class="text-secondary">Isi 0 untuk tanpa batas nominal. Persentase tetap berlaku.</small>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-bold" for="subsidy-start">Mulai (WIB, opsional)</label>
                <input id="subsidy-start" type="datetime-local" name="subsidy_starts_at" value="<?= esc($subsidy['starts_at'] ? str_replace(' ', 'T', substr($subsidy['starts_at'], 0, 16)) : ''); ?>" class="form-control">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-bold" for="subsidy-end">Selesai (WIB, opsional)</label>
                <input id="subsidy-end" type="datetime-local" name="subsidy_ends_at" value="<?= esc($subsidy['ends_at'] ? str_replace(' ', 'T', substr($subsidy['ends_at'], 0, 16)) : ''); ?>" class="form-control">
            </div>
        </div>
        <fieldset>
            <legend class="form-label fw-bold fs-6">Ekspedisi yang mendapat subsidi</legend>
            <div class="d-flex flex-wrap gap-3">
                <?php foreach ($subsidyCouriers as $courier) { ?>
                <label class="d-flex align-items-center gap-2"><input type="checkbox" name="subsidy_couriers[]" value="<?= esc($courier); ?>" <?= in_array($courier, $subsidy['couriers'], true) ? 'checked' : ''; ?>> <?= esc(strtoupper($courier)); ?></label>
                <?php } ?>
            </div>
            <small class="text-secondary">Kosongkan semua pilihan agar berlaku untuk seluruh ekspedisi yang tersedia.</small>
        </fieldset>
        <p class="small text-secondary mb-0">Contoh: tarif Rp100.000 dengan subsidi 10% menjadi Rp90.000. Pembulatan subsidi ke bawah dalam rupiah. Perubahan hanya berlaku pada penawaran baru; penawaran belum dibayar yang tidak sesuai diminta cek ulang ongkir.</p>
        <button type="submit" class="btn btn-primary1 align-self-start">Simpan Subsidi Ongkir</button>
    </form>
</section>
