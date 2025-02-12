<?= $this->extend('layout/template'); ?>
<?= $this->section('content'); ?>
<div id="modal-add" class="d-none justify-content-center align-items-center" style="position: fixed; top: 0; left: 0; width: 100vw; height: 100svh; background-color: rgba(0,0,0,0.3); z-index: 1000">
    <div class="bg-light p-4 rounded">
        <h5 class="mb-2">Tambah Mutasi</h5>
        <form action="/stokadmin/<?= $url; ?>" method="post">
            <div class="mb-2">
                <label>Jenis</label>
                <select name="jenis" class="form-select">
                    <option value="keluar">Pengeluaran</option>
                    <?php if (session()->get('email') == 'galih8.4.2001@gmail.com') { ?>
                        <option value="masuk">Pemasukan</option>
                    <?php } ?>
                </select>
            </div>
            <div class="baris-ke-kolom mb-2 gap-1">
                <div style="flex: 2;">
                    <label>Varian</label>
                    <select name="varian" class="form-select">
                        <?php foreach ($produk['varian'] as $v) { ?>
                            <option value="<?= $v; ?>"><?= $v; ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div style="flex: 1;">
                    <label>Jumlah</label>
                    <input required type="number" class="form-control" name="jumlah">
                </div>
            </div>
            <div class="mb-3">
                <label>Keterangan</label>
                <input type="text" name="keterangan" required class="form-control">
            </div>
            <input type="text" value="<?= $produk['id']; ?>" name="id_barang" class="d-none">
            <input type="text" value="<?= $produk['nama']; ?>" name="nama" class="d-none">
            <div class="d-flex gap-1">
                <button onclick="closeModal()" style="flex: 1" type="button" class="btn btn-outline-dark">Batal</button>
                <button style="flex: 1" type="submit" class="btn btn-primary1">Tambahkan</button>
            </div>
        </form>
    </div>
</div>
<div class="konten">
    <div class="container">
        <div class="d-flex gap-5 justify-content-between align-items-center">
            <div>
                <h3 class="mb-1">Mutasi Produk</h3>
                <select class="form-select" onchange="handleChangeProduk(event)">
                    <?php foreach ($produkAll as $p) { ?>
                        <option value="<?= $p['id']; ?>" <?= $p['id'] == $produk['id'] ? 'selected' : ''; ?>><?= $p['nama']; ?></option>
                    <?php } ?>
                </select>
            </div>
            <button onclick="openTambal()" class="btn btn-primary1">Tambah</button>
        </div>
        <hr>
        <div class="d-flex">
            <?php foreach ($stokVarian as $s) { ?>
                <div style="flex: 1;" class="d-flex flex-column justify-content-center align-items-center">
                    <p class="mb-1 text-secondary"><?= $s['nama']; ?></p>
                    <h3><?= $s['stok']; ?></h3>
                </div>
            <?php } ?>
        </div>
        <hr>
        <?php if ($msg) { ?>
            <div class="alert alert-danger" role="alert">
                <?= $msg; ?>
            </div>
        <?php } ?>
        <div class="d-flex w-100 fw-bold gap-2 mb-2">
            <div style="flex: 1" class="m-0">Tanggal</div>
            <div style="flex: 1" class="m-0">Nama</div>
            <div style="flex: 0.7" class="m-0">Varian</div>
            <div style="flex: 0.4" class="m-0">Jumlah</div>
            <div style="flex: 0.5" class="m-0">PJ</div>
            <div style="flex: 1" class="m-0">Keterangan</div>
            <div style="flex: 0.4" class="m-0">Stok Akhir</div>
        </div>
        <div class="d-flex flex-column gap-2">
            <?php if (count($stok) > 0) { ?>
                <?php foreach ($stok as $s) { ?>
                    <div class="d-flex w-100 gap-2">
                        <div style="flex: 1" class="m-0"><?= $s['tanggal']; ?></div>
                        <div style="flex: 1" class="m-0"><?= $s['nama']; ?></div>
                        <div style="flex: 0.7" class="m-0"><?= $s['varian']; ?></div>
                        <div style="flex: 0.4; color: <?= $s['jumlah'] < 0 ? 'red' : 'var(--hijau)'; ?>" class="m-0"><?= $s['jumlah'] < 0 ? '' : '+'; ?><?= $s['jumlah']; ?></div>
                        <div style="flex: 0.5" class="m-0"><?= explode(' ', $s['nama_admin'])[0]; ?> <?= count(explode(' ', $s['nama_admin'])) > 1 ? substr(explode(' ', $s['nama_admin'])[1], 0, 1) : ''; ?></div>
                        <div style="flex: 1" class="m-0"><?= $s['keterangan']; ?></div>
                        <div style="flex: 0.4" class="m-0"><?= $s['stok_akhir']; ?></div>
                    </div>
                <?php } ?>
            <?php } else { ?>
                <p class="text-center text-sm text-secondary"><i>Belum ada data mutasi</i></p>
            <?php } ?>
        </div>
    </div>
</div>
<script>
    const modalAddElm = document.getElementById('modal-add');

    function openTambal() {
        modalAddElm.classList.remove('d-none')
        modalAddElm.classList.add('d-flex')
    }

    function closeModal() {
        modalAddElm.classList.add('d-none')
        modalAddElm.classList.remove('d-flex')
    }

    function handleChangeProduk(e) {
        console.log(e.target.value)
        window.location.replace(`/stokadmin/${e.target.value}/1`)
    }
</script>
<?= $this->endSection(); ?>