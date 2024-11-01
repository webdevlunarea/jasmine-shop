<?= $this->extend('layout/template'); ?>
<?= $this->section('content'); ?>
<div class="konten">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center">
            <h1>List Voucher</h1>
            <a href="/addvoucher" class="btn btn-primary1">Tambah Voucher</a>
        </div>
        <div class="d-flex py-2 border-bottom">
            <p class="m-0" style="flex: 1; color: gray;">No</p>
            <p class="m-0" style="flex: 3; color: gray;">Nama</p>
            <p class="m-0" style="flex: 3; color: gray;">Nominal</p>
            <p class="m-0" style="flex: 3; color: gray;">Satuan</p>
            <p class="m-0" style="flex: 3; color: gray;">Berakhir</p>
            <p class="m-0" style="flex: 3; color: gray;">Jenis</p>
            <p class="m-0" style="flex: 3; color: gray;">Active</p>
        </div>
        <?php foreach ($voucher as $ind_v => $v) { ?>
            <div class="d-flex py-1">
                <p class="m-0" style="flex: 1;"><?= $ind_v + 1 ?></p>
                <p class="m-0" style="flex: 3;"><?= $v['nama'] ?></p>
                <p class="m-0" style="flex: 3;"><?= $v['nominal'] ?></p>
                <p class="m-0" style="flex: 3;"><?= $v['satuan'] ?></p>
                <p class="m-0" style="flex: 3;"><?= $v['berakhir'] != '0000-00-00' ? date('d/m/Y', strtotime($v['berakhir'])) : 'Tidak ada' ?></p>
                <p class="m-0" style="flex: 3;"><?= $v['jenis'] ?></p>
                <div class="m-0" style="flex: 3;">
                    <div class="bg-light border border-dark rounded-5 p-1 d-flex justify-content-<?= $v['active'] ? 'end' : 'start' ?>" style="width: 60px; height: 100%; cursor:pointer;" onclick="triggerToast('Voucher <?= $v['nama']; ?> akan di<?= $v['active'] ? 'non aktifkan' : 'aktifkan'; ?>?', '/activevoucher/<?= $v['id']; ?>')">
                        <div class="bg-<?= $v['active'] ? 'success' : 'danger' ?> rounded-2" style="width: 30px; height: 90%"></div>
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>
</div>
<?= $this->endSection(); ?>