<?= $this->extend("layout/template"); ?>
<?= $this->section("content"); ?>
<div class="konten">
    <div class="container">
        <form action="/actionaddinvoiceadmin" method="post">
            <h3 class="">Buat Invoice</h3>
            <?php if ($msg) { ?>
                <div class="alert alert-danger" role="alert">
                    <?= $msg; ?>
                </div>
            <?php } ?>
            <div class="mb-1">
                <label class="form-label">Tanggal</label>
                <input type="date" class="form-control" name="tanggal">
            </div>
            <div class="mb-1">
                <label class="form-label">ID</label>
                <input type="text" class="form-control" name="id">
            </div>
            <hr>
            <h2>Penerima</h2>
            <div class="mb-1">
                <label class="form-label">Nama</label>
                <input type="text" class="form-control" name="nama">
            </div>
            <div class="mb-1">
                <label class="form-label">Alamat</label>
                <input type="text" class="form-control" name="alamat">
            </div>
            <hr>
            <h2>Produk</h2>
            <div id="container-produk-invoice">
                <!-- <div class="d-flex gap-1">
                <div class="mb-1" style="flex: 1;">
                    <label class="form-label">Nama produk</label>
                    <input type="text" class="form-control" name="produk-1-nama">
                </div>
                <div class="mb-1" style="flex: 1;">
                    <label class="form-label">Kuantitas</label>
                    <input type="number" class="form-control" name="produk-1-kuantitas">
                </div>
                <div class="mb-1" style="flex: 1;">
                    <label class="form-label">Harga satuan</label>
                    <input type="number" class="form-control" name="produk-1-harga">
                </div>
            </div> -->
            </div>
            <button type="button" class="btn btn-primary1" onclick="addProduct()">Tambah Produk</button>
            <hr>
            <input type="number" name="hitungProduk" class="d-none">
            <button type="submit" class="btn btn-dark">Simpan</button>
        </form>
    </div>
</div>
<script>
    const containerProdukInvoiceElm = document.getElementById('container-produk-invoice');
    const inputHitungProdukElm = document.querySelector('input[name="hitungProduk"]');
    let counterItem = 0;

    function addProduct() {
        counterItem++;
        const item = '<div class="d-flex gap-1"><div class="mb-1" style="flex: 1;"><label class="form-label">Nama produk</label><input type="text" class="form-control" name="produk-' +
            counterItem + '-nama"></div><div class="mb-1" style="flex: 1;"><label class="form-label">Kuantitas</label><input type="number" class="form-control" name="produk-' +
            counterItem + '-kuantitas"></div><div class="mb-1" style="flex: 1;"><label class="form-label">Harga satuan</label><input type="number" class="form-control" name="produk-' +
            counterItem + '-harga"></div></div>'
        containerProdukInvoiceElm.innerHTML += item
        inputHitungProdukElm.value = counterItem
    }
</script>
<?= $this->endSection(); ?>