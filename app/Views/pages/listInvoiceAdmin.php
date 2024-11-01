<?= $this->extend('layout/template'); ?>
<?= $this->section('content'); ?>
<div class="konten">
    <div class="container">
        <div class="d-flex justify-content-between">
            <h3 class="">List Invoice</h3>
            <a href="/addinvoiceadmin" class="btn btn-primary1">Tambah Invoice</a>
        </div>
        <hr>
        <div class="d-flex border-bottom py-2 fw-bold">
            <div style="flex: 3;">ID</div>
            <div style="flex: 3;">Tanggal</div>
            <div style="flex: 3;">Nama</div>
            <div style="flex: 1;"></div>
        </div>
        <?php foreach ($seluruhInvoice as $invoice) { ?>
            <div class="d-flex py-2">
                <div style="flex: 3;"><?= $invoice['id']; ?></div>
                <div style="flex: 3;"><?= $invoice['tanggal']; ?></div>
                <div style="flex: 3;"><?= $invoice['nama']; ?></div>
                <div style="flex: 1;"><a href="/invoiceadmin/<?= $invoice['id']; ?>" class="btn btn-light">Detail</a></div>
            </div>
        <?php } ?>
    </div>
</div>
<?= $this->endSection(); ?>