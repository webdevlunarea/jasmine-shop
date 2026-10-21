<?= $this->extend('layout/template'); ?>
<?= $this->section('content'); ?>
<main class="konten admin-module-page">
    <div class="container" style="max-width: 960px;">
        <?= view('partials/admin_page_heading', [
            'adminHeadingTitle' => 'Program Afiliasi', 'adminHeadingGroup' => 'Pengaturan',
            'adminHeadingDescription' => 'Kelola tautan afiliasi produk dan komisi Luna Poin untuk pesanan website maupun Android.',
        ], ['saveData' => false]); ?>
        <?php if (!empty($msg)) { ?><div class="alert alert-info rounded-3" role="status"><?= esc($msg); ?></div><?php } ?>
        <?php if (empty($schemaReady)) { ?>
            <div class="alert alert-warning rounded-3">Tabel program referral belum tersedia. Buat tabel menggunakan SQL yang disertakan sebelum menyimpan pengaturan.</div>
        <?php } ?>
        <?php if (!empty($stats)) { ?>
            <div class="row g-3 mb-4">
                <div class="col-12 col-md-4"><div class="border rounded-4 p-3 bg-white"><div class="text-secondary small">Akun teratribusi</div><strong class="fs-4"><?= number_format((int)$stats['invited'], 0, ',', '.'); ?></strong></div></div>
                <div class="col-12 col-md-4"><div class="border rounded-4 p-3 bg-white"><div class="text-secondary small">Komisi pesanan selesai</div><strong class="fs-4"><?= number_format((int)$stats['rewarded'], 0, ',', '.'); ?></strong></div></div>
                <div class="col-12 col-md-4"><div class="border rounded-4 p-3 bg-white"><div class="text-secondary small">Total komisi (Luna Poin)</div><strong class="fs-4"><?= number_format((int)$stats['points'], 0, ',', '.'); ?></strong></div></div>
            </div>
        <?php } ?>
        <section class="card border-0 shadow-sm rounded-4 p-3 p-md-4">
            <h2 class="h5 fw-bold mb-1">Aturan komisi</h2>
            <p class="text-secondary mb-4">Komisi diberikan sebagai Luna Poin setelah pesanan produksi berstatus Selesai. Perhitungan memakai subtotal produk bersih; ongkir, biaya admin, dan proteksi kaca tidak dihitung.</p>
            <form method="post" action="/androidreferraladmin" class="row g-3">
                <?= csrf_field(); ?>
                <div class="col-12">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" id="referral-enabled" name="enabled" value="1" <?= (int)($settings['enabled'] ?? 0) === 1 ? 'checked' : ''; ?>>
                        <label class="form-check-label fw-semibold" for="referral-enabled">Aktifkan program afiliasi website & Android</label>
                    </div>
                    <div class="form-text">Kode pengguna dapat dibagikan dari detail produk. Tautan melacak pesanan yang selesai di website maupun aplikasi Android.</div>
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label fw-semibold" for="referral-percent">Komisi afiliasi (%)</label>
                    <input class="form-control" id="referral-percent" name="commission_percent" type="number" min="0.1" max="20" step="0.1" required value="<?= esc(number_format((float)($settings['commission_percent'] ?? 3), 2, '.', '')); ?>">
                    <div class="form-text">Default 3% dari subtotal produk bersih, dibayarkan sebagai Luna Poin (1 poin = Rp1).</div>
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label fw-semibold" for="referral-minimum">Minimum subtotal produk (Rp)</label>
                    <input class="form-control" id="referral-minimum" name="minimum_subtotal" type="number" min="0" max="1000000000" step="1000" required value="<?= (int)$settings['minimum_subtotal']; ?>">
                    <div class="form-text">Pesanan di bawah minimum ini tidak memperoleh komisi.</div>
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label fw-semibold" for="referral-trigger">Reward diberikan saat status</label>
                    <select class="form-select" id="referral-trigger" name="reward_trigger_status" required>
                        <option value="Selesai" <?= ($settings['reward_trigger_status'] ?? '') === 'Selesai' ? 'selected' : ''; ?>>Selesai — disarankan</option>
                    </select>
                    <div class="form-text">Komisi hanya diberikan setelah pesanan benar-benar selesai; pembayaran pending tidak dihitung.</div>
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label fw-semibold" for="referral-expiry">Masa berlaku komisi Luna Poin (hari)</label>
                    <input class="form-control" id="referral-expiry" name="point_expiry_days" type="number" min="30" max="3650" step="1" required value="<?= (int)$settings['point_expiry_days']; ?>">
                    <div class="form-text">Rentang 30–3.650 hari.</div>
                </div>
                <div class="col-12"><div class="alert alert-light border rounded-3 mb-0">Atribusi dikunci ke referral pertama sebelum pesanan pertama. Self-referral dan transaksi sandbox dikecualikan. Komisi dihitung per pesanan selesai dan hanya sekali per ID pesanan.</div></div>
                <div class="col-12 d-flex justify-content-end"><button class="btn btn-primary1 px-4" type="submit" <?= empty($schemaReady) ? 'disabled' : ''; ?>>Simpan pengaturan</button></div>
            </form>
        </section>
    </div>
</main>
<?= $this->endSection(); ?>
