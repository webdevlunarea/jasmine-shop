<?= $this->extend('layout/template'); ?>
<?= $this->section('content'); ?>
<div class="konten admin-module-page admin-module-list-invoice-admin">
    <div class="container">
        <?= view('partials/admin_page_heading', ['adminHeadingTitle'=>'Invoice', 'adminHeadingGroup'=>'Operasional', 'adminHeadingDescription'=>'Temukan dan periksa invoice yang dibuat oleh admin.', 'adminHeadingActions'=>[['label'=>'Buat invoice', 'href'=>'/addinvoiceadmin', 'icon'=>'add', 'primary'=>true]]], ['saveData'=>false]); ?>
        <div class="table-responsive" tabindex="0" role="region" aria-label="Daftar invoice">
            <table class="table admin-data-table align-middle"><caption class="visually-hidden">Nomor, tanggal, penerima dan tautan detail invoice.</caption>
                <thead><tr><th scope="col">Nomor invoice</th><th scope="col">Tanggal</th><th scope="col">Penerima</th><th scope="col" class="text-end">Tindakan</th></tr></thead>
                <tbody>
                <?php if (!$seluruhInvoice) { ?><tr><td colspan="4" class="admin-empty-state">Belum ada invoice. Gunakan tombol Buat invoice untuk memulai.</td></tr><?php } ?>
                <?php foreach ($seluruhInvoice as $invoice) { ?>
                    <tr><td class="fw-semibold"><?= esc($invoice['id']); ?></td><td><?= esc($invoice['tanggal']); ?></td><td><?= esc($invoice['nama']); ?></td><td class="text-end"><a href="/invoiceadmin/<?= rawurlencode((string)$invoice['id']); ?>" class="btn orders-secondary" aria-label="Detail invoice <?= esc((string)$invoice['id'], 'attr'); ?>">Lihat detail<i class="material-icons" aria-hidden="true">north_east</i></a></td></tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>