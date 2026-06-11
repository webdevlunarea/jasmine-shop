<?= $this->extend('layout/template'); ?>
<?= $this->section('content'); ?>
<style>
.banner-preview {
    width: 180px;
    aspect-ratio: 16 / 5;
    object-fit: cover;
    border-radius: 8px;
    border: 1px solid #dee2e6;
    background-color: #f8f9fa;
}

.banner-row {
    border-bottom: 1px solid #dee2e6;
}

@media (max-width: 768px) {
    .banner-preview {
        width: 100%;
    }
}
</style>
<div class="konten">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h5 class="jdl-section mb-0">Homepage</h5>
                <h1 class="mb-0">List Banner</h1>
            </div>
            <a href="/addbanner" class="btn btn-primary1 d-flex gap-2 align-items-center" style="width: fit-content;">
                <i class="material-icons">add</i>
                <p class="mb-0">Tambah Banner</p>
            </a>
        </div>

        <?php if ($msg) { ?>
            <div class="alert alert-primary" role="alert">
                <?= $msg; ?>
            </div>
        <?php } ?>

        <?php if (count($banner) == 0) { ?>
            <div class="alert alert-light border" role="alert">
                Belum ada banner dari admin. Homepage masih memakai banner lama dari folder <b>public/img/benner</b>.
            </div>
        <?php } else { ?>
            <div class="show-flex-ke-hide flex-column">
                <div class="w-100 gap-3 d-flex py-2 border-bottom mb-2 fw-bold">
                    <div style="flex: 2">Preview</div>
                    <div style="flex: 3">Judul dan Link</div>
                    <div style="flex: 1" class="text-center">Urutan</div>
                    <div style="flex: 1" class="text-center">Active</div>
                    <div style="flex: 2" class="text-end">Action</div>
                </div>
                <?php foreach ($banner as $b) { ?>
                    <div class="w-100 gap-3 d-flex py-3 banner-row">
                        <div style="flex: 2" class="d-flex align-items-center">
                            <img class="banner-preview" src="/imgbanner/<?= $b['id']; ?>?v=<?= strtotime($b['updated_at'] ?? '') ?: $b['id']; ?>" alt="<?= esc($b['alt'] ?: $b['judul']); ?>">
                        </div>
                        <div style="flex: 3" class="d-flex flex-column justify-content-center">
                            <p class="m-0 fw-bold"><?= esc($b['judul']); ?></p>
                            <p class="m-0 text-secondary"><?= $b['link'] ? esc($b['link']) : 'Tanpa link'; ?></p>
                        </div>
                        <div style="flex: 1" class="d-flex align-items-center justify-content-center">
                            <p class="m-0"><?= (int)$b['urutan']; ?></p>
                        </div>
                        <div style="flex: 1" class="d-flex justify-content-center align-items-center">
                            <div class="bg-light border border-dark rounded-5 p-1 d-flex justify-content-<?= $b['active'] ? 'end' : 'start' ?>" style="width: 60px; height: 20px; cursor:pointer;" onclick="triggerToast('Banner <?= esc($b['judul']); ?> akan di<?= $b['active'] ? 'non aktifkan' : 'aktifkan'; ?>?', '/activebanner/<?= $b['id']; ?>')">
                                <div class="bg-<?= $b['active'] ? 'success' : 'danger' ?> rounded-2" style="width: 30px; height: 90%"></div>
                            </div>
                        </div>
                        <div style="flex: 2" class="d-flex gap-1 justify-content-end align-items-center">
                            <a class="btn btn-light d-flex" href="/editbanner/<?= $b['id']; ?>" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Edit banner"><i class="material-icons">edit</i></a>
                            <button class="btn btn-light d-flex" onclick="triggerToast('Banner <?= esc($b['judul']); ?> akan dihapus?', '/deletebanner/<?= $b['id']; ?>')" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Hapus banner"><i class="material-icons">delete_forever</i></button>
                        </div>
                    </div>
                <?php } ?>
            </div>

            <div class="hide-ke-show-flex flex-column gap-3">
                <?php foreach ($banner as $b) { ?>
                    <div class="border-bottom pb-3">
                        <img class="banner-preview mb-2" src="/imgbanner/<?= $b['id']; ?>?v=<?= strtotime($b['updated_at'] ?? '') ?: $b['id']; ?>" alt="<?= esc($b['alt'] ?: $b['judul']); ?>">
                        <p class="m-0 fw-bold"><?= esc($b['judul']); ?></p>
                        <p class="m-0 text-secondary"><?= $b['link'] ? esc($b['link']) : 'Tanpa link'; ?></p>
                        <p class="m-0 text-secondary">Urutan: <?= (int)$b['urutan']; ?></p>
                        <div class="d-flex mt-2 align-items-center justify-content-between">
                            <div class="bg-light border border-dark rounded-5 p-1 d-flex justify-content-<?= $b['active'] ? 'end' : 'start' ?>" style="width: 60px; height: 20px; cursor:pointer;" onclick="triggerToast('Banner <?= esc($b['judul']); ?> akan di<?= $b['active'] ? 'non aktifkan' : 'aktifkan'; ?>?', '/activebanner/<?= $b['id']; ?>')">
                                <div class="bg-<?= $b['active'] ? 'success' : 'danger' ?> rounded-2" style="width: 30px; height: 90%"></div>
                            </div>
                            <div class="d-flex gap-1">
                                <a class="btn btn-light d-flex" href="/editbanner/<?= $b['id']; ?>"><i class="material-icons">edit</i></a>
                                <button class="btn btn-light d-flex" onclick="triggerToast('Banner <?= esc($b['judul']); ?> akan dihapus?', '/deletebanner/<?= $b['id']; ?>')"><i class="material-icons">delete_forever</i></button>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
        <?php } ?>
    </div>
</div>
<?= $this->endSection(); ?>
