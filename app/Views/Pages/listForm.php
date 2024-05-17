<?= $this->extend('layout/template'); ?>
<?= $this->section('content'); ?>
<div class="konten">
    <div class="container">
        <h1>Report Pengisian Form</h1>
        <div class="container-list-form" style="overflow-x: auto;">
            <div style="width: max-content;">
                <div class="d-flex py-2 border-bottom">
                    <p class="m-0" style="flex: 1; color: gray;">No</p>
                    <p class="m-0" style="flex: 4; color: gray;">Nama</p>
                    <p class="m-0" style="flex: 4; color: gray;">Nohp</p>
                    <p class="m-0" style="flex: 4; color: gray;">Alamat</p>
                    <p class="m-0" style="flex: 4; color: gray;">Pesan</p>
                </div>
                <?php foreach ($form as $ind_f => $f) { ?>
                    <div class="d-flex py-1">
                        <p class="m-0" style="flex: 1;"><?= $ind_f + 1 ?></p>
                        <p class="m-0" style="flex: 4;"><?= $f['nama']; ?></p>
                        <p class="m-0" style="flex: 4;"><?= $f['nohp']; ?></p>
                        <p class="m-0" style="flex: 4;"><?= $f['alamat']; ?></p>
                        <p class="m-0" style="flex: 4; text-overflow: ellipsis; overflow: hidden; width: 100px; white-space: nowrap;"><?= $f['pesan']; ?></p>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>