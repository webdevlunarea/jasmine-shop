<?= $this->extend('layout/template'); ?>
<?= $this->section('content'); ?>
<?php
$internalActive = !empty($internal['is_active']);
$rajaActive = !empty($rajaongkir['is_active']);
$activeCoverage = 0;
foreach ($coverages as $coverage) {
    if (!empty($coverage['is_active'])) $activeCoverage++;
}
$totalCoverage = count($coverages);
$couriers = $rajaSettings['couriers'] ?? ['jne', 'jnt', 'sicepat', 'tiki', 'pos', 'sentral'];
$originConfigured = trim((string)($rajaSettings['origin_id'] ?? '')) !== '';
?>

<div class="admin-content shipping-admin-page">
    <div class="shipping-admin-shell">
        <?php if (!empty($msg)) { ?>
            <div class="alert shipping-alert-success">
                <i class="material-icons">check_circle</i>
                <span><?= esc($msg); ?></span>
            </div>
        <?php } ?>

        <section class="shipping-hero">
            <div>
                <span class="shipping-eyebrow"><i class="material-icons">local_shipping</i> Admin Pengiriman</span>
                <h1>Pengaturan Pengiriman</h1>
                <p>Atur Jasa Kirim Toko, coverage kecamatan, origin RajaOngkir, dan ekspedisi yang tampil di checkout.</p>
            </div>
            <div class="shipping-hero-status">
                <div class="status-dot <?= ($internalActive || $rajaActive) ? 'is-on' : ''; ?>"></div>
                <div>
                    <strong><?= ($internalActive || $rajaActive) ? 'Metode aktif' : 'Semua metode nonaktif'; ?></strong>
                    <span><?= $activeCoverage; ?> kecamatan aktif · <?= $originConfigured ? 'Origin siap' : 'Origin belum diisi'; ?></span>
                </div>
            </div>
        </section>

        <section class="shipping-stat-grid">
            <div class="shipping-stat-card">
                <span class="stat-icon internal"><i class="material-icons">storefront</i></span>
                <div>
                    <p>Jasa Kirim Toko</p>
                    <strong><?= $internalActive ? 'Aktif' : 'Nonaktif'; ?></strong>
                    <small>Ongkir Rp0 untuk coverage terpilih</small>
                </div>
            </div>
            <div class="shipping-stat-card">
                <span class="stat-icon api"><i class="material-icons">api</i></span>
                <div>
                    <p>RajaOngkir</p>
                    <strong><?= $rajaActive ? 'Aktif' : 'Nonaktif'; ?></strong>
                    <small><?= $originConfigured ? 'Origin ID sudah diisi' : 'Butuh Origin ID'; ?></small>
                </div>
            </div>
            <div class="shipping-stat-card">
                <span class="stat-icon coverage"><i class="material-icons">map</i></span>
                <div>
                    <p>Coverage Internal</p>
                    <strong><?= $activeCoverage; ?>/<?= $totalCoverage; ?></strong>
                    <small>Kecamatan aktif / total tersimpan</small>
                </div>
            </div>
        </section>

        <div class="shipping-layout">
            <section class="shipping-panel">
                <div class="panel-header">
                    <div>
                        <span class="panel-kicker">Metode & API</span>
                        <h2>Konfigurasi utama</h2>
                    </div>
                    <span class="panel-badge">Feature flag</span>
                </div>

                <form action="/shippingadmin" method="post" class="shipping-settings-form">
                    <input type="hidden" name="action" value="settings">

                    <div class="method-toggle-card <?= $internalActive ? 'is-active' : ''; ?>">
                        <div class="method-copy">
                            <span class="method-icon"><i class="material-icons">local_mall</i></span>
                            <div>
                                <h3>Jasa Kirim Toko</h3>
                                <p>Metode internal Rp0. Hanya muncul untuk kecamatan yang ada di daftar coverage aktif.</p>
                            </div>
                        </div>
                        <div class="form-check form-switch shipping-switch">
                            <input class="form-check-input" type="checkbox" role="switch" name="internal_active" id="internal_active" value="1" <?= $internalActive ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="internal_active"><?= $internalActive ? 'Aktif' : 'Nonaktif'; ?></label>
                        </div>
                    </div>

                    <div class="method-toggle-card <?= $rajaActive ? 'is-active' : ''; ?>">
                        <div class="method-copy">
                            <span class="method-icon api"><i class="material-icons">cloud_sync</i></span>
                            <div>
                                <h3>RajaOngkir Shipping Cost</h3>
                                <p>Digunakan untuk cek tarif ekspedisi. API key tetap di backend, tidak tampil ke browser.</p>
                            </div>
                        </div>
                        <div class="form-check form-switch shipping-switch">
                            <input class="form-check-input" type="checkbox" role="switch" name="rajaongkir_active" id="rajaongkir_active" value="1" <?= $rajaActive ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="rajaongkir_active"><?= $rajaActive ? 'Aktif' : 'Nonaktif'; ?></label>
                        </div>
                    </div>

                    <div class="settings-group">
                        <div class="form-floating">
                            <input type="text" class="form-control" id="rajaongkir_origin_id" name="rajaongkir_origin_id" value="<?= esc($rajaSettings['origin_id'] ?? ''); ?>" placeholder="Origin ID RajaOngkir">
                            <label for="rajaongkir_origin_id">Origin ID RajaOngkir</label>
                        </div>
                        <p class="field-help">Isi dengan ID origin Semarang setelah lokasi origin final sudah dipilih dari dashboard/API RajaOngkir.</p>

                        <div class="form-floating">
                            <input type="text" class="form-control" id="rajaongkir_origin_label" name="rajaongkir_origin_label" value="<?= esc($rajaSettings['origin_label'] ?? 'Semarang, Jawa Tengah'); ?>" placeholder="Label Origin">
                            <label for="rajaongkir_origin_label">Label Origin</label>
                        </div>

                        <label class="form-label fw-bold mb-1" for="rajaongkir_couriers">Kurir Aktif</label>
                        <input type="text" class="form-control courier-input" id="rajaongkir_couriers" name="rajaongkir_couriers" value="<?= esc(implode(',', $couriers)); ?>" placeholder="jne,jnt,sicepat,tiki,pos,sentral">
                        <div class="courier-chip-row">
                            <?php foreach ($couriers as $courier) { ?>
                                <span><?= esc(strtoupper(trim((string)$courier))); ?></span>
                            <?php } ?>
                        </div>
                        <p class="field-help">Pisahkan dengan koma. Sentral Cargo tampil hanya jika tersedia dari respons RajaOngkir dan akun mendukung.</p>
                    </div>

                    <button class="btn btn-primary1 shipping-save-btn" type="submit">
                        <i class="material-icons">save</i>
                        Simpan Pengaturan
                    </button>
                </form>
            </section>

            <section class="shipping-panel">
                <div class="panel-header">
                    <div>
                        <span class="panel-kicker">Coverage Gratis</span>
                        <h2>Jasa Kirim Toko</h2>
                    </div>
                    <span class="panel-badge success">Rp0</span>
                </div>

                <form action="/shippingadmin" method="post" class="coverage-form">
                    <input type="hidden" name="action" value="add_coverage">
                    <div>
                        <label class="form-label" for="ship-prov">Provinsi</label>
                        <select class="form-select" name="provinsi_id" id="ship-prov" required>
                            <option value="">Pilih provinsi</option>
                            <?php foreach ($provinsi as $p) { ?>
                                <option value="<?= esc($p['id']); ?>"><?= esc($p['label']); ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div>
                        <label class="form-label" for="ship-kab">Kota/Kabupaten</label>
                        <select class="form-select" name="kabupaten_id" id="ship-kab" required disabled>
                            <option value="">Pilih kota</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label" for="ship-kec">Kecamatan</label>
                        <select class="form-select" name="kecamatan_id" id="ship-kec" required disabled>
                            <option value="">Pilih kecamatan</option>
                        </select>
                    </div>
                    <button class="btn btn-outline-success coverage-submit" type="submit">
                        <i class="material-icons">add_location_alt</i>
                        Tambah Coverage
                    </button>
                </form>

                <div class="coverage-list-header">
                    <div>
                        <h3>Daftar Kecamatan Dilayani</h3>
                        <p>Coverage aktif akan membuat opsi Jasa Kirim Toko muncul di checkout.</p>
                    </div>
                    <span><?= $activeCoverage; ?> aktif</span>
                </div>

                <div class="coverage-list">
                    <?php if (empty($coverages)) { ?>
                        <div class="coverage-empty">
                            <i class="material-icons">map</i>
                            <strong>Belum ada coverage</strong>
                            <p>Tambahkan kecamatan pertama agar Jasa Kirim Toko bisa digunakan secara terkontrol.</p>
                        </div>
                    <?php } ?>

                    <?php foreach ($coverages as $coverage) { ?>
                        <article class="coverage-item <?= !empty($coverage['is_active']) ? 'is-active' : 'is-inactive'; ?>">
                            <div class="coverage-main">
                                <span class="coverage-pin"><i class="material-icons">place</i></span>
                                <div>
                                    <h4><?= esc($coverage['kecamatan_label']); ?></h4>
                                    <p><?= esc($coverage['kabupaten_label']); ?>, <?= esc($coverage['provinsi_label']); ?></p>
                                </div>
                            </div>
                            <div class="coverage-actions">
                                <span class="badge <?= !empty($coverage['is_active']) ? 'text-bg-success' : 'text-bg-secondary'; ?>">
                                    <?= !empty($coverage['is_active']) ? 'Aktif' : 'Nonaktif'; ?>
                                </span>
                                <?php if (!empty($coverage['is_active'])) { ?>
                                    <form action="/shippingadmin/coverage/<?= (int)$coverage['id']; ?>/deactivate" method="post">
                                        <button class="btn btn-sm btn-outline-danger" type="submit">
                                            <i class="material-icons">block</i>
                                            Nonaktifkan
                                        </button>
                                    </form>
                                <?php } ?>
                            </div>
                        </article>
                    <?php } ?>
                </div>
            </section>
        </div>
    </div>
</div>

<script>
const shipProv = document.getElementById('ship-prov');
const shipKab = document.getElementById('ship-kab');
const shipKec = document.getElementById('ship-kec');

function escapeHtml(value) {
    return String(value ?? '').replace(/[&<>"']/g, (char) => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    }[char]));
}

function setSelectLoading(select, label) {
    select.disabled = true;
    select.innerHTML = `<option value="">${label}</option>`;
}

shipProv?.addEventListener('change', async function() {
    setSelectLoading(shipKab, 'Loading kota...');
    setSelectLoading(shipKec, 'Pilih kecamatan');
    if (!this.value) {
        setSelectLoading(shipKab, 'Pilih kota');
        return;
    }
    try {
        const res = await fetch(`/getkota/${this.value}`);
        const list = await res.json();
        shipKab.innerHTML = '<option value="">Pilih kota</option>';
        (Array.isArray(list) ? list : []).forEach((item) => {
            shipKab.innerHTML += `<option value="${escapeHtml(item.id)}">${escapeHtml(item.label)}</option>`;
        });
        shipKab.disabled = false;
    } catch (error) {
        setSelectLoading(shipKab, 'Gagal memuat kota');
    }
});

shipKab?.addEventListener('change', async function() {
    setSelectLoading(shipKec, 'Loading kecamatan...');
    if (!this.value) {
        setSelectLoading(shipKec, 'Pilih kecamatan');
        return;
    }
    try {
        const res = await fetch(`/getkec/${this.value}`);
        const list = await res.json();
        shipKec.innerHTML = '<option value="">Pilih kecamatan</option>';
        (Array.isArray(list) ? list : []).forEach((item) => {
            shipKec.innerHTML += `<option value="${escapeHtml(item.id)}">${escapeHtml(item.label)}</option>`;
        });
        shipKec.disabled = false;
    } catch (error) {
        setSelectLoading(shipKec, 'Gagal memuat kecamatan');
    }
});
</script>

<style>
    .shipping-admin-page {
        background: #f7fbf8;
        min-height: calc(100vh - 70px);
        padding: clamp(16px, 3vw, 28px);
    }

    .shipping-admin-shell {
        max-width: 1280px;
        margin: 0 auto;
        display: grid;
        gap: 18px;
    }

    .shipping-alert-success {
        display: flex;
        align-items: center;
        gap: 10px;
        border: 1px solid rgba(24, 135, 84, .18);
        border-radius: 18px;
        background: #ecfff4;
        color: #12643c;
        font-weight: 700;
        margin: 0;
    }

    .shipping-alert-success .material-icons {
        color: var(--hijau);
    }

    .shipping-hero {
        display: flex;
        justify-content: space-between;
        align-items: stretch;
        gap: 18px;
        padding: clamp(18px, 3vw, 30px);
        border-radius: 28px;
        background:
            radial-gradient(circle at 12% 0%, rgba(255, 255, 255, .95), transparent 34%),
            linear-gradient(135deg, #ffffff 0%, var(--hijaumuda) 100%);
        border: 1px solid var(--hijaumuda2);
        box-shadow: 0 18px 45px rgba(36, 59, 107, .08);
    }

    .shipping-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 7px 11px;
        border-radius: 999px;
        background: var(--hijau);
        color: #fff;
        font-weight: 900;
        font-size: .78rem;
        letter-spacing: .05em;
        text-transform: uppercase;
    }

    .shipping-eyebrow .material-icons {
        font-size: 17px;
    }

    .shipping-hero h1 {
        margin: 13px 0 8px;
        color: var(--hijau);
        font-size: clamp(1.8rem, 3vw, 2.7rem);
        font-weight: 900;
        letter-spacing: -.04em;
    }

    .shipping-hero p {
        max-width: 720px;
        margin: 0;
        color: #61707e;
        line-height: 1.65;
    }

    .shipping-hero-status {
        min-width: 255px;
        display: flex;
        align-items: center;
        gap: 12px;
        border-radius: 22px;
        padding: 16px;
        background: #fff;
        border: 1px solid rgba(36, 59, 107, .08);
    }

    .shipping-hero-status strong,
    .shipping-hero-status span {
        display: block;
    }

    .shipping-hero-status strong {
        color: #20303d;
        font-weight: 900;
    }

    .shipping-hero-status span {
        color: #66737f;
        font-size: .9rem;
    }

    .status-dot {
        width: 14px;
        height: 14px;
        border-radius: 50%;
        background: #b7c0c8;
        box-shadow: 0 0 0 7px rgba(183, 192, 200, .18);
    }

    .status-dot.is-on {
        background: var(--hijau);
        box-shadow: 0 0 0 7px var(--hijaumuda1);
    }

    .shipping-stat-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 14px;
    }

    .shipping-stat-card,
    .shipping-panel {
        background: #fff;
        border: 1px solid rgba(36, 59, 107, .08);
        box-shadow: 0 12px 30px rgba(36, 59, 107, .06);
    }

    .shipping-stat-card {
        display: flex;
        align-items: center;
        gap: 14px;
        border-radius: 22px;
        padding: 16px;
    }

    .stat-icon {
        width: 48px;
        height: 48px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 16px;
        background: var(--hijaumuda);
        color: var(--hijau);
        flex: 0 0 48px;
    }

    .stat-icon.api {
        background: #eef4ff;
        color: #315ca8;
    }

    .stat-icon.coverage {
        background: #fff5df;
        color: #a15f00;
    }

    .shipping-stat-card p,
    .shipping-stat-card small {
        margin: 0;
        color: #66737f;
    }

    .shipping-stat-card strong {
        display: block;
        color: #20303d;
        font-size: 1.35rem;
        font-weight: 900;
        line-height: 1.15;
    }

    .shipping-layout {
        display: grid;
        grid-template-columns: minmax(320px, .85fr) minmax(0, 1.15fr);
        gap: 18px;
        align-items: start;
    }

    .shipping-panel {
        border-radius: 26px;
        padding: clamp(16px, 2vw, 22px);
    }

    .panel-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 12px;
        margin-bottom: 18px;
    }

    .panel-kicker {
        color: var(--hijau);
        font-size: .78rem;
        text-transform: uppercase;
        letter-spacing: .08em;
        font-weight: 900;
    }

    .panel-header h2,
    .coverage-list-header h3,
    .method-toggle-card h3,
    .coverage-item h4 {
        margin: 0;
        color: #20303d;
        font-weight: 900;
        letter-spacing: -.02em;
    }

    .panel-header h2 {
        font-size: 1.35rem;
    }

    .panel-badge {
        padding: 6px 10px;
        border-radius: 999px;
        background: #eef4ff;
        color: #315ca8;
        font-weight: 800;
        font-size: .82rem;
        white-space: nowrap;
    }

    .panel-badge.success {
        background: var(--hijaumuda);
        color: var(--hijau);
    }

    .shipping-settings-form,
    .settings-group,
    .coverage-form,
    .coverage-list {
        display: grid;
        gap: 14px;
    }

    .method-toggle-card {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 14px;
        padding: 15px;
        border: 1px solid rgba(36, 59, 107, .10);
        border-radius: 20px;
        background: #fbfcfd;
    }

    .method-toggle-card.is-active {
        border-color: var(--hijaumuda2);
        background: linear-gradient(135deg, #fff, var(--hijaumuda));
    }

    .method-copy {
        display: flex;
        gap: 12px;
        min-width: 0;
    }

    .method-icon {
        width: 42px;
        height: 42px;
        border-radius: 14px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: var(--hijau);
        color: #fff;
        flex: 0 0 42px;
    }

    .method-icon.api {
        background: #315ca8;
    }

    .method-toggle-card h3 {
        font-size: 1rem;
    }

    .method-toggle-card p,
    .field-help,
    .coverage-list-header p,
    .coverage-item p,
    .coverage-empty p {
        color: #66737f;
        margin: 0;
        line-height: 1.55;
    }

    .shipping-switch {
        min-width: 92px;
        text-align: right;
    }

    .shipping-switch .form-check-input {
        width: 3rem;
        height: 1.5rem;
        cursor: pointer;
    }

    .shipping-switch .form-check-label {
        display: block;
        margin-top: 6px;
        font-weight: 800;
        color: #20303d;
    }

    .form-control,
    .form-select {
        min-height: 46px;
        border-radius: 14px;
        border-color: rgba(36, 59, 107, .14);
    }

    .form-control:focus,
    .form-select:focus {
        border-color: var(--hijau);
        box-shadow: 0 0 0 .2rem rgba(36, 137, 74, .12);
    }

    .courier-chip-row {
        display: flex;
        flex-wrap: wrap;
        gap: 7px;
    }

    .courier-chip-row span {
        padding: 5px 9px;
        border-radius: 999px;
        background: var(--hijaumuda);
        color: var(--hijau);
        font-weight: 800;
        font-size: .78rem;
    }

    .shipping-save-btn,
    .coverage-submit {
        min-height: 46px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        font-weight: 800;
        border-radius: 14px;
    }

    .coverage-submit {
        width: 100%;
    }

    .coverage-list-header {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        align-items: center;
        margin-top: 8px;
        padding-top: 16px;
        border-top: 1px dashed var(--hijaumuda2);
    }

    .coverage-list-header h3 {
        font-size: 1.08rem;
    }

    .coverage-list-header span {
        padding: 6px 10px;
        border-radius: 999px;
        background: var(--hijau);
        color: #fff;
        font-weight: 800;
        white-space: nowrap;
    }

    .coverage-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        padding: 14px;
        border: 1px solid rgba(36, 59, 107, .09);
        border-radius: 18px;
        background: #fff;
    }

    .coverage-item.is-inactive {
        opacity: .72;
        background: #f7f8f9;
    }

    .coverage-main {
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 0;
    }

    .coverage-pin {
        width: 42px;
        height: 42px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 14px;
        background: var(--hijaumuda);
        color: var(--hijau);
        flex: 0 0 42px;
    }

    .coverage-item h4 {
        font-size: .98rem;
    }

    .coverage-actions {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
        justify-content: flex-end;
    }

    .coverage-actions .btn {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        border-radius: 999px;
        min-height: 34px;
    }

    .coverage-actions .material-icons {
        font-size: 15px;
    }

    .coverage-empty {
        display: grid;
        place-items: center;
        text-align: center;
        gap: 6px;
        padding: 28px 16px;
        border-radius: 18px;
        border: 1px dashed var(--hijaumuda2);
        background: var(--hijaumuda);
    }

    .coverage-empty .material-icons {
        color: var(--hijau);
        font-size: 34px;
    }

    @media (max-width: 1100px) {
        .shipping-layout {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 768px) {
        .shipping-admin-page {
            padding: 12px;
        }

        .shipping-hero,
        .method-toggle-card,
        .coverage-item,
        .coverage-list-header {
            flex-direction: column;
            align-items: stretch;
        }

        .shipping-hero-status {
            min-width: 0;
        }

        .shipping-stat-grid {
            grid-template-columns: 1fr;
        }

        .shipping-switch,
        .coverage-actions {
            text-align: left;
            justify-content: flex-start;
        }

        .shipping-switch {
            min-width: 0;
        }

        .coverage-actions .btn,
        .coverage-actions form {
            width: 100%;
        }

        .coverage-actions .btn {
            justify-content: center;
        }
    }
</style>

<?= $this->endSection(); ?>
