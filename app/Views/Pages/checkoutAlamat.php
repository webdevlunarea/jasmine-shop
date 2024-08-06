<div class="form-alamat d-flex mb-1 gap-1">
    <div class="form-floating w-50">
        <select class="form-select" aria-label="Default select example" name="provinsi">
            <option value="">-- Pilih provinsi --</option>
            <?php foreach ($provinsi as $p) { ?>
                <option value="<?= $p['province_id']; ?>-<?= $p['province']; ?>" <?= $user['alamat'] ? ($p['province_id'] == $user['alamat']['prov_id'] ? 'selected' : '') : ''; ?>><?= $p['province']; ?>
                </option>
            <?php } ?>
        </select>
        <label for="floatingProvinsi">Provinsi</label>
    </div>
    <div class="form-floating w-50">
        <select class="form-select" aria-label="Default select example" name="kota">
            <option value="">-- Pilih kota --</option>
            <?php foreach ($kabupaten as $p) { ?>
                <option value="<?= $p['city_id']; ?>-<?= explode("/", $p['city_name'])[0]; ?>" <?= $user['alamat'] ? ($p['city_id'] == $user['alamat']['kab_id'] ? 'selected' : '') : ''; ?>><?= $p['city_name']; ?>
                </option>
            <?php } ?>
        </select>
        <label for="floatingProvinsi">Kabupaten/Kota</label>
    </div>
</div>
<div class="form-alamat d-flex mb-1 gap-1">
    <div class="form-floating w-50">
        <select class="form-select" aria-label="Default select example" name="kecamatan">
            <option selected value="">-- Pilih kecamatan --</option>
            <?php foreach ($kecamatan as $p) { ?>
                <option value="<?= $p['subdistrict_id']; ?>-<?= explode("/", $p['subdistrict_name'])[0]; ?>" <?= $user['alamat'] ? ($p['subdistrict_id'] == $user['alamat']['kec_id'] ? 'selected' : '') : ''; ?>><?= $p['subdistrict_name']; ?>
                </option>
            <?php } ?>
        </select>
        <label for="floatingProvinsi">Kecamatan</label>
    </div>
    <div class="form-floating w-50">
        <select class="form-select" aria-label="Default select example" name="kodepos">
            <option value="">-- Pilih Desa --</option>
            <?php foreach ($desa as $p) { ?>
                <option value="<?= explode("/", ucwords(strtolower($p['DesaKelurahan'])))[0]; ?>-<?= $p['KodePos']; ?>" <?= $user['alamat'] ? (explode("/", ucwords(strtolower($p['DesaKelurahan'])))[0] == $user['alamat']['desa'] ? 'selected' : '') : ''; ?>><?= ucwords(strtolower($p['DesaKelurahan'])); ?>
                </option>
            <?php } ?>
        </select>
        <label for="floatingProvinsi">Desa/Kelurahan</label>
    </div>
</div>