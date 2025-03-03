<?= $this->extend('layout/template'); ?>
<?= $this->section('content'); ?>
<div id="modal-add" class="d-none justify-content-center align-items-center" style="position: fixed; top: 0; left: 0; width: 100vw; height: 100svh; background-color: rgba(0,0,0,0.3); z-index: 1000">
    <div class="bg-light p-4 rounded">
        <h5 class="mb-2">Tambah Mutasi</h5>
        <form action="" method="post" id="form-mutasi">
            <div class="mb-2">
                <label>Jenis</label>
                <select name="jenis" class="form-select">
                    <option value="keluar">Pengeluaran</option>
                    <?php if (session()->get('email') == 'galih8.4.2001@gmail.com') { ?>
                        <option value="masuk">Pemasukan</option>
                    <?php } ?>
                </select>
            </div>
            <div class="mb-2">
                <label>Warna</label>
                <select name="varian" class="form-select">
                    <?php foreach ($produk['varian'] as $v) { ?>
                        <option value="<?= $v; ?>"><?= $v; ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="mb-2">
                <label>Kuantitas</label>
                <p class="text-secondary mb-1" style="font-size: 12px;">Penambahan / pengurangannya (bukan total)</p>
                <input required type="number" class="form-control" name="jumlah">
            </div>
            <div class="mb-3">
                <label>Keterangan</label>
                <input type="text" name="keterangan" required class="form-control">
            </div>
            <input type="text" value="<?= $produk['id']; ?>" name="id_barang" class="d-none">
            <input type="text" value="<?= $produk['nama']; ?>" name="nama" class="d-none">
            <div class="d-flex gap-1">
                <button onclick="closeModal()" style="flex: 1" type="button" class="btn btn-outline-dark">Batal</button>
                <button style="flex: 1" type="submit" class="btn btn-primary1" id="btn-form">Tambahkan</button>
            </div>
        </form>
    </div>
</div>
<style>
    .item-list-produk {
        text-decoration: none;
        color: black;
        transition: 0.1s;
        padding: 0.3em 1em;
        cursor: pointer;
    }

    .item-list-produk:hover {
        background-color: var(--hijau);
        color: white;
        transition: 0.1s;
    }
</style>
<div class="konten">
    <div class="container">
        <div class="d-flex gap-3 justify-content-between align-items-center">
            <div style="flex: 1">
                <h3 class="mb-1">Mutasi <?= explode(' - ', $produk['nama'])[1]; ?></h3>
                <input placeholder="Cari produk" type="text" class="form-select w-100" oninput="handleInput(event)">
                <div style="position: relative;" class="w-100">
                    <div id="container-cari-barang" class="d-none flex-column gap-1 border rounded w-100 bg-light" style="overflow: auto; max-height: 40svh; position: absolute; z-index: 3"></div>
                </div>
            </div>
            <div>
                <button onclick="sinkronisasi(event)" class="btn btn-outline-dark">Sinkron</button>
                <button onclick="openTambal()" class="btn btn-primary1">Tambah</button>
            </div>
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
        <div style="overflow-x: auto;">
            <div style="min-width: 900px;">
                <div class="d-flex w-100 fw-bold gap-2 mb-2">
                    <div style="flex: 1;" class="m-0">Tanggal</div>
                    <div style="flex: 1;" class="m-0">Nama</div>
                    <div style="flex: 0.7;" class="m-0">Varian</div>
                    <div style="flex: 0.4;" class="m-0">Jumlah</div>
                    <div style="flex: 0.5;" class="m-0">PJ</div>
                    <div style="flex: 1;" class="m-0">Keterangan</div>
                    <div style="flex: 0.6;" class="m-0">Stok Akhir</div>
                    <div style="flex: 0.4;" class="m-0">Action</div>
                </div>
                <div class="d-flex flex-column gap-2">
                    <?php if (count($stok) > 0) { ?>
                        <?php foreach ($stok as $ind_s => $s) { ?>
                            <div class="d-flex w-100 gap-2">
                                <div style="flex: 1;" class="m-0"><?= $s['tanggal']; ?></div>
                                <div style="flex: 1;" class="m-0"><?= $s['nama']; ?></div>
                                <div style="flex: 0.7;" class="m-0"><?= $s['varian']; ?></div>
                                <div style="flex: 0.4; color: <?= $s['jumlah'] < 0 ? 'red' : 'var(--hijau)'; ?>" class="m-0"><?= $s['jumlah'] < 0 ? '' : '+'; ?><?= $s['jumlah']; ?></div>
                                <?php if ($s['nama_admin']) { ?>
                                    <div style="flex: 0.5;" class="m-0"><?= explode(' ', $s['nama_admin'])[0]; ?> <?= count(explode(' ', $s['nama_admin'])) > 1 ? substr(explode(' ', $s['nama_admin'])[1], 0, 1) : ''; ?></div>
                                <?php } else { ?>
                                    <div style="flex: 0.5;" class="m-0 text-sm text-secondary"><i>Butuh konfirm</i></div>
                                <?php } ?>
                                <div style="flex: 1;" class="m-0"><?= $s['keterangan']; ?></div>
                                <div style="flex: 0.6;" class="m-0"><?= $s['stok_akhir']; ?></div>
                                <div style="flex: 0.4;" class="m-0 d-flex justify-content-center align-items-center">
                                    <?php if (!$s['nama_admin']) { ?>
                                        <button type="button" onclick="openKofirm(<?= $ind_s; ?>)" class="btn btn-primary1 p-2"><i class="material-icons" style="font-size: 12px;">border_color</i></button>
                                    <?php } ?>
                                </div>
                            </div>
                        <?php } ?>
                    <?php } else { ?>
                        <p class="text-center text-sm text-secondary"><i>Belum ada data mutasi</i></p>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    const btnFormElm = document.getElementById('btn-form');
    const url = "<?= $url; ?>";
    const formElm = document.getElementById('form-mutasi');
    const selectVarianELm = document.querySelector('select[name="varian"]');
    const inputJumlahELm = document.querySelector('input[name="jumlah"]');
    const inputKeteranganELm = document.querySelector('input[name="keterangan"]');
    const stok = JSON.parse('<?= $stokJson; ?>');
    const produkAll = JSON.parse('<?= $produkAllJson; ?>');
    const modalAddElm = document.getElementById('modal-add');
    const containerCariBarangElm = document.getElementById('container-cari-barang')

    function openTambal() {
        formElm.action = `/stokadmin/${url}`
        btnFormElm.innerHTML = 'Tambahkan'
        modalAddElm.classList.remove('d-none')
        modalAddElm.classList.add('d-flex')
    }

    function openKofirm(index) {
        const data = stok[index]
        formElm.action = `/stokadminacc/${data.id}/${url}`
        console.log(data)
        btnFormElm.innerHTML = 'Konfirm'
        selectVarianELm.value = data.varian;
        inputJumlahELm.value = Math.abs(Number(data.jumlah));
        inputKeteranganELm.value = data.keterangan;
        modalAddElm.classList.remove('d-none')
        modalAddElm.classList.add('d-flex')
    }

    function closeModal() {
        formElm.action = ''
        inputJumlahELm.value = '';
        inputKeteranganELm.value = '';
        modalAddElm.classList.add('d-none')
        modalAddElm.classList.remove('d-flex')
    }

    function handleChangeProduk(e) {
        window.location.replace(`/stokadmin/${e.target.value}/1`)
    }

    function handleInput(e) {
        const inputType = e.target.value.toLowerCase();
        containerCariBarangElm.innerHTML = '';
        if (inputType == '') {
            containerCariBarangElm.classList.remove('d-flex')
            containerCariBarangElm.classList.add('d-none')
            return;
        }
        containerCariBarangElm.classList.add('d-flex')
        containerCariBarangElm.classList.remove('d-none')
        const produkFilter = produkAll.filter((p) => {
            return p.nama.toLowerCase().includes(inputType)
        })
        produkFilter.forEach(p => {
            containerCariBarangElm.innerHTML += `<a href="/stokadmin/${p.id}/1" class="item-list-produk rounded">${p.nama}</a>`
        });
    }

    function sinkronisasi(e) {
        e.target.innerHTML = 'Loading'
        async function fetchBenerin() {
            try {
                const res = await fetch('/benerinstokluna');
                const resJson = await res.json();
                e.target.innerHTML = 'Sinkron'
                window.alert('Sinkronisasi stok berhasil dilakukan')
            } catch (error) {
                console.log(error)
            }
        }
        fetchBenerin()
    }
</script>
<?= $this->endSection(); ?>