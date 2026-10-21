<?= $this->extend('layout/template'); ?>
<?= $this->section('content'); ?>
<main class="konten admin-module-page">
    <div class="container" style="max-width: 960px;">
        <?= view('partials/admin_page_heading', [
            'adminHeadingTitle' => 'Referral Android', 'adminHeadingGroup' => 'Pengaturan',
            'adminHeadingDescription' => 'Atur program Ajak Teman dan reward Luna Poin untuk pembelian melalui aplikasi Android.',
        ], ['saveData' => false]); ?>
        <?php if (!empty($msg)) { ?><div class="alert alert-info rounded-3" role="status"><?= esc($msg); ?></div><?php } ?>
        <?php if (empty($schemaReady)) { ?>
            <div class="alert alert-warning rounded-3">Tabel program referral belum tersedia. Buat tabel menggunakan SQL yang disertakan sebelum menyimpan pengaturan.</div>
        <?php } ?>
        <?php if (!empty($stats)) { ?>
            <div class="row g-3 mb-4">
                <div class="col-12 col-md-4"><div class="border rounded-4 p-3 bg-white"><div class="text-secondary small">Akun teratribusi</div><strong class="fs-4"><?= number_format((int)$stats['invited'], 0, ',', '.'); ?></strong></div></div>
                <div class="col-12 col-md-4"><div class="border rounded-4 p-3 bg-white"><div class="text-secondary small">Reward berhasil</div><strong class="fs-4"><?= number_format((int)$stats['rewarded'], 0, ',', '.'); ?></strong></div></div>
                <div class="col-12 col-md-4"><div class="border rounded-4 p-3 bg-white"><div class="text-secondary small">Total poin referral</div><strong class="fs-4"><?= number_format((int)$stats['points'], 0, ',', '.'); ?></strong></div></div>
            </div>
        <?php } ?>
        <section class="card border-0 shadow-sm rounded-4 p-3 p-md-4">
            <h2 class="h5 fw-bold mb-1">Aturan program</h2>
            <p class="text-secondary mb-4">Satu reward diberikan kepada pengundang untuk pesanan pertama teman yang memenuhi syarat. Teman yang diundang belum menerima bonus poin pada aturan awal.</p>
            <form method="post" action="/androidreferraladmin" class="row g-3">
                <?= csrf_field(); ?>
                <div class="col-12">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" id="referral-enabled" name="enabled" value="1" <?= (int)($settings['enabled'] ?? 0) === 1 ? 'checked' : ''; ?>>
                        <label class="form-check-label fw-semibold" for="referral-enabled">Aktifkan referral khusus pesanan Android</label>
                    </div>
                    <div class="form-text">Aplikasi dapat menampilkan dan menautkan kode saat program aktif. Website tidak memperoleh reward referral.</div>
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label fw-semibold" for="referral-points">Poin untuk pengundang</label>
                    <input class="form-control" id="referral-points" name="referrer_reward_points" type="number" min="1" max="1000000" required value="<?= (int)$settings['referrer_reward_points']; ?>">
                    <div class="form-text">Ketentuan saat ini: 1 Luna Poin bernilai Rp1.</div>
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label fw-semibold" for="referral-minimum">Minimum subtotal produk (Rp)</label>
                    <input class="form-control" id="referral-minimum" name="minimum_subtotal" type="number" min="0" max="1000000000" step="1000" required value="<?= (int)$settings['minimum_subtotal']; ?>">
                    <div class="form-text">Dihitung setelah voucher/potongan produk; tidak termasuk ongkir dan proteksi kaca.</div>
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label fw-semibold" for="referral-trigger">Reward diberikan saat status</label>
                    <select class="form-select" id="referral-trigger" name="reward_trigger_status" required>
                        <option value="Selesai" <?= ($settings['reward_trigger_status'] ?? '') === 'Selesai' ? 'selected' : ''; ?>>Selesai — disarankan</option>
                    </select>
                    <div class="form-text">Status pembayaran/pengiriman harus diperbarui backend/admin; checkout pending tidak dihitung.</div>
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label fw-semibold" for="referral-expiry">Masa berlaku poin referral (hari)</label>
                    <input class="form-control" id="referral-expiry" name="point_expiry_days" type="number" min="30" max="3650" step="1" required value="<?= (int)$settings['point_expiry_days']; ?>">
                    <div class="form-text">Rentang 30–3.650 hari.</div>
                </div>
                <div class="col-12"><div class="alert alert-light border rounded-3 mb-0">Referral hanya dapat ditautkan sebelum pesanan pertama. Self-referral, sandbox, pesanan website, pesanan di bawah minimum, dan reward ganda tidak memenuhi syarat.</div></div>
                <div class="col-12 d-flex justify-content-end"><button class="btn btn-primary1 px-4" type="submit" <?= empty($schemaReady) ? 'disabled' : ''; ?>>Simpan pengaturan</button></div>
            </form>
        </section>
    </div>
</main>
<?= $this->endSection(); ?>
