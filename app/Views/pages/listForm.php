<?= $this->extend('layout/template'); ?>
<?= $this->section('content'); ?>
<div class="konten admin-module-page admin-module-list-form"><div class="container">
    <?= view('partials/admin_page_heading', ['adminHeadingTitle'=>'Pesan pelanggan', 'adminHeadingGroup'=>'Administrasi', 'adminHeadingDescription'=>'Tinjau kontak dan pesan yang masuk melalui formulir website.'], ['saveData'=>false]); ?>
    <div class="table-responsive" tabindex="0" role="region" aria-label="Pesan formulir pelanggan">
        <table class="table admin-data-table align-middle"><thead><tr><th scope="col">No.</th><th scope="col">Nama</th><th scope="col">Telepon</th><th scope="col">Alamat</th><th scope="col">Pesan</th></tr></thead><tbody>
        <?php if (!$form) { ?><tr><td colspan="5" class="admin-empty-state">Belum ada pesan dari pelanggan.</td></tr><?php } ?>
        <?php foreach ($form as $index=>$f) { ?><tr><td><?= $index+1; ?></td><td><?= esc($f['nama']); ?></td><td><?= esc($f['nohp']); ?></td><td><?= nl2br(esc($f['alamat'])); ?></td><td><?= nl2br(esc($f['pesan'])); ?></td></tr><?php } ?>
        </tbody></table>
    </div>
</div></div>
<?= $this->endSection(); ?>