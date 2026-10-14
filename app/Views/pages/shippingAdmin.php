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
$originId = trim((string)($rajaSettings['origin_id'] ?? ''));
$originLabel = trim((string)($rajaSettings['origin_label'] ?? '')) ?: 'Kota Semarang, Jawa Tengah';
?>

<div class="konten">
    <div class="container">
        <div class="d-flex justify-content-between align-items-start gap-3 mb-3 flex-wrap">
            <div>
                <p class="admin-form-eyebrow mb-1">Website</p>
                <h5 class="jdl-section mb-1">Pengiriman</h5>
                <p class="text-muted mb-0">Atur Jasa Kirim Toko, coverage kecamatan, dan RajaOngkir untuk checkout website.</p>
            </div>
            <a href="/" target="_blank" class="btn btn-outline-dark d-flex align-items-center gap-2">
                <i class="material-icons">open_in_new</i>
                <span>Lihat Website</span>
            </a>
        </div>

        <?php if (!empty($msg)) { ?>
            <div class="alert alert-info py-2"><?= esc($msg); ?></div>
        <?php } ?>

        <div class="shipping-admin-summary mb-3">
            <div class="shipping-summary-item">
                <span class="summary-icon"><i class="material-icons">storefront</i></span>
                <div>
                    <small>Jasa Kirim Toko</small>
                    <strong><?= $internalActive ? 'Aktif' : 'Nonaktif'; ?></strong>
                </div>
            </div>
            <div class="shipping-summary-item">
                <span class="summary-icon api"><i class="material-icons">api</i></span>
                <div>
                    <small>RajaOngkir</small>
                    <strong><?= $rajaActive ? 'Aktif' : 'Nonaktif'; ?></strong>
                </div>
            </div>
            <div class="shipping-summary-item">
                <span class="summary-icon area"><i class="material-icons">map</i></span>
                <div>
                    <small>Coverage Aktif</small>
                    <strong><?= $activeCoverage; ?>/<?= $totalCoverage; ?> Kecamatan</strong>
                </div>
            </div>
            <div class="shipping-summary-item">
                <span class="summary-icon origin"><i class="material-icons">my_location</i></span>
                <div>
                    <small>Origin</small>
                    <strong><?= esc($originLabel); ?> · ID <?= esc($originId); ?></strong>
                </div>
            </div>
        </div>

        <div class="shipping-admin-grid">
            <section class="shipping-admin-panel">
                <div class="shipping-panel-head">
                    <div>
                        <h5 class="fw-bold mb-1">Pengaturan Utama</h5>
                        <p class="text-secondary mb-0">Aktifkan metode dan isi origin pengiriman.</p>
                    </div>
                </div>

                <form action="/shippingadmin" method="post" class="shipping-form-stack">
                    <input type="hidden" name="action" value="settings">

                    <label class="shipping-admin-toggle">
                        <input type="checkbox" name="internal_active" value="1" <?= $internalActive ? 'checked' : ''; ?>>
                        <span>
                            <strong>Aktifkan Jasa Kirim Toko</strong>
                            <small>Ongkir Rp0 dan hanya tampil untuk kecamatan yang aktif.</small>
                        </span>
                    </label>

                    <label class="shipping-admin-toggle">
                        <input type="checkbox" name="rajaongkir_active" value="1" <?= $rajaActive ? 'checked' : ''; ?>>
                        <span>
                            <strong>Aktifkan RajaOngkir</strong>
                            <small>Untuk menampilkan pilihan ongkir ekspedisi dari API.</small>
                        </span>
                    </label>

                    <div class="origin-box">
                        <div class="d-flex justify-content-between align-items-start gap-2 mb-2 flex-wrap">
                            <div>
                                <strong>Origin Semarang</strong>
                                <p class="text-secondary small mb-0">Gunakan ID dari RajaOngkir Search Domestic Destination. Jika kosong, sistem mencari dari label origin.</p>
                            </div>
                            <span class="badge text-bg-success"><?= $originId !== '' ? 'ID ' . esc($originId) : 'Auto Search'; ?></span>
                        </div>
                        <div class="row g-2">
                            <div class="col-md-5">
                                <label class="form-label fw-bold">Origin ID RajaOngkir</label>
                                <input type="text" class="form-control" name="rajaongkir_origin_id" value="<?= esc($originId); ?>" placeholder="Opsional, ID valid dari RajaOngkir">
                                <small class="text-secondary">Jangan isi dengan ID kota/provinsi lama. Kosongkan jika belum yakin.</small>
                            </div>
                            <div class="col-md-7">
                                <label class="form-label fw-bold">Label Origin</label>
                                <input type="text" class="form-control" name="rajaongkir_origin_label" value="<?= esc($originLabel); ?>" placeholder="Kota Semarang, Jawa Tengah">
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="form-label fw-bold">Kurir Aktif</label>
                        <input type="text" class="form-control" name="rajaongkir_couriers" value="<?= esc(implode(',', $couriers)); ?>" placeholder="jne,jnt,sicepat,tiki,pos,sentral">
                        <small class="text-secondary">Pisahkan dengan koma. Sentral Cargo tampil jika tersedia dari RajaOngkir.</small>
                        <div class="shipping-courier-chips mt-2">
                            <?php foreach ($couriers as $courier) { ?>
                                <span><?= esc(strtoupper(trim((string)$courier))); ?></span>
                            <?php } ?>
                        </div>
                    </div>

                    <button class="btn btn-primary1 d-flex align-items-center justify-content-center gap-2" type="submit">
                        <i class="material-icons">save</i>
                        <span>Simpan Pengaturan</span>
                    </button>
                </form>
            </section>

            <section class="shipping-admin-panel">
                <div class="shipping-panel-head">
                    <div>
                        <h5 class="fw-bold mb-1">Coverage Jasa Kirim Toko</h5>
                        <p class="text-secondary mb-0">Tambahkan kecamatan yang berhak mendapat pengiriman toko gratis.</p>
                    </div>
                    <span class="shipping-count-badge"><?= $activeCoverage; ?> aktif</span>
                </div>

                <form action="/shippingadmin" method="post" class="shipping-coverage-form mb-3">
                    <input type="hidden" name="action" value="add_coverage">
                    <div>
                        <label class="form-label fw-bold">Provinsi</label>
                        <select class="form-select" name="provinsi_id" id="ship-prov" required>
                            <option value="">Pilih provinsi</option>
                            <?php foreach ($provinsi as $p) { ?>
                                <option value="<?= esc($p['id']); ?>"><?= esc($p['label']); ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div>
                        <label class="form-label fw-bold">Kota/Kabupaten</label>
                        <select class="form-select" name="kabupaten_id" id="ship-kab" required disabled>
                            <option value="">Pilih kota</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label fw-bold">Kecamatan</label>
                        <select class="form-select" name="kecamatan_id" id="ship-kec" required disabled>
                            <option value="">Pilih kecamatan</option>
                        </select>
                    </div>
                    <button class="btn btn-outline-success d-flex align-items-center justify-content-center gap-2" type="submit">
                        <i class="material-icons">add_location_alt</i>
                        <span>Tambah Coverage</span>
                    </button>
                </form>

                <div class="shipping-coverage-list">
                    <?php if (empty($coverages)) { ?>
                        <div class="shipping-empty-state">
                            <i class="material-icons">map</i>
                            <strong>Belum ada kecamatan</strong>
                            <p class="text-secondary mb-0">Tambahkan coverage agar Jasa Kirim Toko bisa muncul di checkout.</p>
                        </div>
                    <?php } ?>

                    <?php foreach ($coverages as $coverage) { ?>
                        <div class="shipping-coverage-row <?= !empty($coverage['is_active']) ? '' : 'is-inactive'; ?>">
                            <div class="d-flex align-items-start gap-2">
                                <span class="coverage-pin"><i class="material-icons">place</i></span>
                                <div>
                                    <strong><?= esc($coverage['kecamatan_label']); ?></strong>
                                    <small><?= esc($coverage['kabupaten_label']); ?>, <?= esc($coverage['provinsi_label']); ?></small>
                                </div>
                            </div>
                            <div class="coverage-action">
                                <span class="badge <?= !empty($coverage['is_active']) ? 'text-bg-success' : 'text-bg-secondary'; ?>">
                                    <?= !empty($coverage['is_active']) ? 'Aktif' : 'Nonaktif'; ?>
                                </span>
                                <?php if (!empty($coverage['is_active'])) { ?>
                                    <form action="/shippingadmin/coverage/<?= (int)$coverage['id']; ?>/deactivate" method="post">
                                        <button class="btn btn-sm btn-outline-danger" type="submit">Nonaktifkan</button>
                                    </form>
                                <?php } ?>
                            </div>
                        </div>
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

function setSelect(select, label, disabled = true) {
    select.disabled = disabled;
    select.innerHTML = `<option value="">${label}</option>`;
}

shipProv?.addEventListener('change', async function() {
    setSelect(shipKab, 'Loading kota...');
    setSelect(shipKec, 'Pilih kecamatan');
    if (!this.value) {
        setSelect(shipKab, 'Pilih kota');
        return;
    }
    try {
        const res = await fetch(`/getkota/${this.value}`);
        const list = await res.json();
        setSelect(shipKab, 'Pilih kota', false);
        (Array.isArray(list) ? list : []).forEach((item) => {
            shipKab.innerHTML += `<option value="${escapeHtml(item.id)}">${escapeHtml(item.label)}</option>`;
        });
    } catch (error) {
        setSelect(shipKab, 'Gagal memuat kota');
    }
});

shipKab?.addEventListener('change', async function() {
    setSelect(shipKec, 'Loading kecamatan...');
    if (!this.value) {
        setSelect(shipKec, 'Pilih kecamatan');
        return;
    }
    try {
        const res = await fetch(`/getkec/${this.value}`);
        const list = await res.json();
        setSelect(shipKec, 'Pilih kecamatan', false);
        (Array.isArray(list) ? list : []).forEach((item) => {
            shipKec.innerHTML += `<option value="${escapeHtml(item.id)}">${escapeHtml(item.label)}</option>`;
        });
    } catch (error) {
        setSelect(shipKec, 'Gagal memuat kecamatan');
    }
});
</script>

<style>
    .shipping-admin-summary {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px;
    }

    .shipping-summary-item,
    .shipping-admin-panel {
        background: #fff;
        border: 1px solid rgba(36, 59, 107, .10);
        box-shadow: 0 14px 32px rgba(36, 59, 107, .06);
    }

    .shipping-summary-item {
        display: flex;
        align-items: center;
        gap: 12px;
        border-radius: 18px;
        padding: 14px;
    }

    .summary-icon,
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

    .summary-icon.api {
        background: #eef4ff;
        color: #315ca8;
    }

    .summary-icon.area {
        background: #fff6e5;
        color: #9a5b00;
    }

    .summary-icon.origin {
        background: #f3efff;
        color: #6741a3;
    }

    .shipping-summary-item small,
    .shipping-coverage-row small {
        color: #66737f;
        display: block;
    }

    .shipping-summary-item strong {
        display: block;
        color: #20303d;
        font-weight: 900;
        line-height: 1.2;
    }

    .shipping-admin-grid {
        display: grid;
        grid-template-columns: minmax(0, .9fr) minmax(0, 1.1fr);
        gap: 18px;
        align-items: start;
    }

    .shipping-admin-panel {
        border-radius: 22px;
        padding: 18px;
    }

    .shipping-panel-head {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        align-items: flex-start;
        margin-bottom: 16px;
    }

    .shipping-count-badge {
        padding: 6px 10px;
        border-radius: 999px;
        background: var(--hijau);
        color: #fff;
        font-weight: 800;
        white-space: nowrap;
    }

    .shipping-form-stack,
    .shipping-coverage-form,
    .shipping-coverage-list {
        display: grid;
        gap: 14px;
    }

    .shipping-admin-toggle {
        display: flex;
        gap: 12px;
        align-items: center;
        padding: 14px;
        border-radius: 18px;
        background: var(--hijaumuda);
        cursor: pointer;
    }

    .shipping-admin-toggle input {
        width: 22px;
        height: 22px;
        accent-color: var(--hijau);
        flex: 0 0 auto;
    }

    .shipping-admin-toggle span {
        display: grid;
        gap: 2px;
    }

    .shipping-admin-toggle small {
        color: #66737f;
    }

    .origin-box {
        padding: 14px;
        border-radius: 18px;
        border: 1px solid var(--hijaumuda2);
        background: linear-gradient(135deg, var(--hijaumuda), #fff);
    }

    .shipping-courier-chips {
        display: flex;
        flex-wrap: wrap;
        gap: 7px;
    }

    .shipping-courier-chips span {
        padding: 5px 9px;
        border-radius: 999px;
        background: var(--hijaumuda);
        color: var(--hijau);
        font-size: .78rem;
        font-weight: 800;
    }

    .shipping-coverage-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        padding: 13px;
        border: 1px solid rgba(36, 59, 107, .10);
        border-radius: 18px;
        background: #fff;
    }

    .shipping-coverage-row.is-inactive {
        opacity: .7;
        background: #f8f9fa;
    }

    .coverage-action {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 8px;
        flex-wrap: wrap;
    }

    .shipping-empty-state {
        display: grid;
        place-items: center;
        gap: 5px;
        text-align: center;
        border: 1px dashed var(--hijaumuda2);
        background: var(--hijaumuda);
        border-radius: 18px;
        padding: 28px 14px;
    }

    .shipping-empty-state .material-icons {
        color: var(--hijau);
        font-size: 34px;
    }

    @media (max-width: 1199.98px) {
        .shipping-admin-summary {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .shipping-admin-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 575.98px) {
        .shipping-admin-summary {
            grid-template-columns: 1fr;
        }

        .shipping-panel-head,
        .shipping-coverage-row,
        .coverage-action {
            align-items: stretch;
            flex-direction: column;
        }

        .coverage-action form,
        .coverage-action .btn {
            width: 100%;
        }
    }
</style>

<?= $this->endSection(); ?>
