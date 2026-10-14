<?= $this->extend('layout/template'); ?>
<?= $this->section('content'); ?>

<div class="admin-content container-fluid py-4">
    <?php if (!empty($msg)) { ?>
        <div class="alert alert-success"><?= esc($msg); ?></div>
    <?php } ?>

    <div class="row g-3">
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <p class="text-uppercase text-secondary fw-bold mb-1" style="font-size: 12px;">Metode Pengiriman</p>
                    <h4 class="fw-bold mb-3">Pengaturan Pengiriman</h4>
                    <form action="/shippingadmin" method="post" class="d-flex flex-column gap-3">
                        <input type="hidden" name="action" value="settings">

                        <div class="border rounded p-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" name="internal_active" id="internal_active" value="1" <?= !empty($internal['is_active']) ? 'checked' : ''; ?>>
                                <label class="form-check-label fw-bold" for="internal_active">Aktifkan Jasa Kirim Toko</label>
                            </div>
                            <p class="text-secondary mb-0 small">Ongkir selalu Rp0 dan hanya tampil untuk kecamatan yang aktif.</p>
                        </div>

                        <div class="border rounded p-3">
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" role="switch" name="rajaongkir_active" id="rajaongkir_active" value="1" <?= !empty($rajaongkir['is_active']) ? 'checked' : ''; ?>>
                                <label class="form-check-label fw-bold" for="rajaongkir_active">Aktifkan RajaOngkir</label>
                            </div>
                            <label class="form-label">Origin ID RajaOngkir</label>
                            <input type="text" class="form-control mb-2" name="rajaongkir_origin_id" value="<?= esc($rajaSettings['origin_id'] ?? ''); ?>" placeholder="Isi setelah ID origin Semarang dikonfirmasi">
                            <label class="form-label">Label Origin</label>
                            <input type="text" class="form-control mb-2" name="rajaongkir_origin_label" value="<?= esc($rajaSettings['origin_label'] ?? 'Semarang, Jawa Tengah'); ?>">
                            <label class="form-label">Kurir Aktif</label>
                            <input type="text" class="form-control" name="rajaongkir_couriers" value="<?= esc(implode(',', $rajaSettings['couriers'] ?? ['jne', 'jnt', 'sicepat', 'tiki', 'pos', 'sentral'])); ?>">
                            <p class="text-secondary small mt-2 mb-0">Pisahkan dengan koma. Sentral Cargo akan tampil jika dikembalikan oleh RajaOngkir.</p>
                        </div>

                        <button class="btn btn-primary1" type="submit">Simpan Pengaturan</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body">
                    <p class="text-uppercase text-secondary fw-bold mb-1" style="font-size: 12px;">Coverage Gratis</p>
                    <h4 class="fw-bold mb-3">Tambah Kecamatan Jasa Kirim Toko</h4>
                    <form action="/shippingadmin" method="post" class="row g-2">
                        <input type="hidden" name="action" value="add_coverage">
                        <div class="col-md-4">
                            <label class="form-label">Provinsi</label>
                            <select class="form-select" name="provinsi_id" id="ship-prov" required>
                                <option value="">Pilih provinsi</option>
                                <?php foreach ($provinsi as $p) { ?>
                                    <option value="<?= esc($p['id']); ?>"><?= esc($p['label']); ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Kota/Kabupaten</label>
                            <select class="form-select" name="kabupaten_id" id="ship-kab" required>
                                <option value="">Pilih kota</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Kecamatan</label>
                            <select class="form-select" name="kecamatan_id" id="ship-kec" required>
                                <option value="">Pilih kecamatan</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <button class="btn btn-outline-success" type="submit">Tambah Coverage</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">Daftar Kecamatan Dilayani</h5>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th>Area</th>
                                    <th>Status</th>
                                    <th class="text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($coverages)) { ?>
                                    <tr><td colspan="3" class="text-secondary">Belum ada kecamatan yang diaktifkan.</td></tr>
                                <?php } ?>
                                <?php foreach ($coverages as $coverage) { ?>
                                    <tr>
                                        <td>
                                            <strong><?= esc($coverage['kecamatan_label']); ?></strong><br>
                                            <span class="text-secondary small"><?= esc($coverage['kabupaten_label']); ?>, <?= esc($coverage['provinsi_label']); ?></span>
                                        </td>
                                        <td>
                                            <span class="badge <?= !empty($coverage['is_active']) ? 'text-bg-success' : 'text-bg-secondary'; ?>">
                                                <?= !empty($coverage['is_active']) ? 'Aktif' : 'Nonaktif'; ?>
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <?php if (!empty($coverage['is_active'])) { ?>
                                                <form action="/shippingadmin/coverage/<?= (int)$coverage['id']; ?>/deactivate" method="post" class="d-inline">
                                                    <button class="btn btn-sm btn-outline-danger" type="submit">Nonaktifkan</button>
                                                </form>
                                            <?php } ?>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const shipProv = document.getElementById('ship-prov');
const shipKab = document.getElementById('ship-kab');
const shipKec = document.getElementById('ship-kec');

shipProv?.addEventListener('change', async function() {
    shipKab.innerHTML = '<option value="">Loading...</option>';
    shipKec.innerHTML = '<option value="">Pilih kecamatan</option>';
    const res = await fetch(`/getkota/${this.value}`);
    const list = await res.json();
    shipKab.innerHTML = '<option value="">Pilih kota</option>';
    (Array.isArray(list) ? list : []).forEach((item) => {
        shipKab.innerHTML += `<option value="${item.id}">${item.label}</option>`;
    });
});

shipKab?.addEventListener('change', async function() {
    shipKec.innerHTML = '<option value="">Loading...</option>';
    const res = await fetch(`/getkec/${this.value}`);
    const list = await res.json();
    shipKec.innerHTML = '<option value="">Pilih kecamatan</option>';
    (Array.isArray(list) ? list : []).forEach((item) => {
        shipKec.innerHTML += `<option value="${item.id}">${item.label}</option>`;
    });
});
</script>

<?= $this->endSection(); ?>
