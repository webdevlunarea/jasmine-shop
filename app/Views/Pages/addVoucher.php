<?= $this->extend("layout/template"); ?>
<?= $this->section("content"); ?>
<div class="konten">
    <div class="container">
        <form action="/actionaddvoucher" method="post">
            <h3 class="">Buat Voucher</h3>
            <?php if ($msg) { ?>
                <div class="alert alert-danger" role="alert">
                    <?= $msg; ?>
                </div>
            <?php } ?>
            <div class="mb-1">
                <label class="form-label">Nama</label>
                <input type="text" class="form-control" name="nama" required>
            </div>
            <div class="mb-1">
                <label class="form-label">Nominal</label>
                <input type="number" class="form-control" name="nominal" required>
            </div>
            <div class="mb-1">
                <label class="form-label">Satuan</label>
                <select name="satuan" class="form-select">
                    <option value="persen" selected>Persen</option>
                    <option value="rupiah">Rupiah</option>
                </select>
            </div>
            <div class="mb-1">
                <label class="form-label">berakhir</label>
                <input type="date" class="form-control" name="berakhir">
            </div>
            <div class="mb-1">
                <label class="form-label">Jenis</label>
                <input type="text" class="form-control" name="jenis" required>
            </div>
            <hr>
            <input type="number" name="hitungProduk" class="d-none">
            <button type="submit" class="btn btn-dark">Simpan</button>
        </form>
    </div>
</div>
<?= $this->endSection(); ?>