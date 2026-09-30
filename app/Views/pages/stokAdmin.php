<?= $this->extend('layout/template'); ?>
<?= $this->section('content'); ?>
<div id="modal-add" class="d-none justify-content-center align-items-center"
    style="position: fixed; top: 0; left: 0; width: 100vw; height: 100svh; background-color: rgba(0,0,0,0.3); z-index: 1000">
    <div class="bg-light p-4 rounded">
        <h5 class="mb-2">Tambah Mutasi</h5>
        <form action="" method="post" id="form-mutasi">
            <div class="mb-2">
                <label>Jenis</label>
                <select name="jenis" class="form-select">
                    <option value="keluar">Pengeluaran</option>
                    <?php if ($emailtambah) { ?>
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
.stok-hero {
    background: linear-gradient(135deg, #f3fff7 0%, #ffffff 52%, #eef8f2 100%);
    border: 1px solid #e3efe8;
    border-radius: 24px;
    padding: 1.2rem;
    box-shadow: 0 16px 40px rgba(26, 83, 54, 0.08);
}

.stok-badge {
    display: inline-flex;
    align-items: center;
    gap: .35rem;
    background: #e9f8ee;
    color: #207247;
    border: 1px solid #caead5;
    border-radius: 999px;
    padding: .35rem .7rem;
    font-size: .78rem;
    font-weight: 700;
}

.stok-search-wrap {
    position: relative;
    max-width: 620px;
}

.stok-search-wrap .form-select {
    min-height: 46px;
    border-radius: 14px;
    border-color: #d9e7de;
    box-shadow: 0 6px 20px rgba(20, 75, 48, 0.05);
}

.stok-sync-card {
    border: 1px solid #d7eadf;
    background: #f6fffa;
    border-radius: 18px;
    padding: 1rem;
}

.stok-varian-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));
    gap: .85rem;
}

.stok-varian-card {
    border: 1px solid #e5eee8;
    background: #fff;
    border-radius: 18px;
    padding: .95rem;
    text-align: center;
    box-shadow: 0 10px 28px rgba(26, 83, 54, 0.06);
}

.stok-varian-card p {
    min-height: 2.4em;
}

.stok-table-card {
    border: 1px solid #e5eee8;
    border-radius: 20px;
    overflow: hidden;
    background: #fff;
    box-shadow: 0 16px 36px rgba(15, 23, 42, 0.06);
}

.stok-table-head,
.stok-table-row {
    display: grid;
    grid-template-columns: 1.05fr 1.1fr .7fr .55fr .55fr 1.25fr .65fr .55fr;
    gap: .75rem;
    align-items: center;
    min-width: 980px;
}

.stok-table-head {
    background: #f8faf9;
    color: #475569;
    font-size: .78rem;
    font-weight: 800;
    letter-spacing: .02em;
    text-transform: uppercase;
    padding: .85rem 1rem;
    border-bottom: 1px solid #e5eee8;
}

.stok-table-row {
    padding: .9rem 1rem;
    border-bottom: 1px solid #eef3ef;
}

.stok-table-row:last-child {
    border-bottom: 0;
}

.stok-table-row:hover {
    background: #fbfefc;
}

.stok-qty {
    display: inline-flex;
    min-width: 54px;
    justify-content: center;
    border-radius: 999px;
    padding: .28rem .55rem;
    font-weight: 800;
    background: #f1f5f9;
}

.stok-action-lock {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 34px;
    border-radius: 999px;
    padding: .25rem .6rem;
    background: #f1f5f9;
    color: #64748b;
    font-size: .75rem;
    font-weight: 700;
}

.stok-pagination-wrap {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: .55rem;
    margin-top: 1rem;
}

.stok-pagination-wrap .pagination {
    flex-wrap: wrap;
    gap: .35rem;
}

.stok-pagination-wrap .page-link {
    min-width: 40px;
    min-height: 40px;
    border-radius: 12px !important;
    border: 1px solid #dbe7df;
    color: #1f3b2e;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    box-shadow: 0 6px 14px rgba(15, 23, 42, 0.04);
}

.stok-pagination-wrap .page-item.active .page-link {
    background: var(--hijau);
    border-color: var(--hijau);
    color: #fff;
}

.stok-pagination-wrap .page-item.disabled .page-link {
    background: #f8fafc;
    color: #94a3b8;
    box-shadow: none;
}

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

@media (max-width: 767px) {
    .stok-hero {
        padding: 1rem;
        border-radius: 18px;
    }

    .stok-hero h3 {
        font-size: 1.25rem;
    }

    .stok-table-card {
        border-radius: 16px;
    }
}
</style>
<div class="konten">
    <div class="container">
        <div class="stok-hero mb-4">
            <div class="d-flex flex-column flex-lg-row gap-3 justify-content-between align-items-lg-center">
                <div style="flex: 1">
                    <div class="stok-badge mb-2">
                        <i class="material-icons" style="font-size: 16px;">verified</i>
                        Monitoring Stok MyLuna
                    </div>
                    <h3 class="mb-1 fw-bold">Mutasi <?= $idProduk == 'all' ? 'Semua Produk' : explode(' - ', $produk['nama'])[1]; ?>
                    </h3>
                    <p class="text-secondary mb-3">Cari produk dan pantau riwayat sinkronisasi stok website. Perubahan stok dilakukan dari Luna Sistem.</p>
                    <div class="stok-search-wrap">
                        <input placeholder="Cari produk berdasarkan nama..." type="text" class="form-select w-100" oninput="handleInput(event)">
                    </div>
                    <div style="position: relative;" class="w-100">
                    <div id="container-cari-barang" class="d-none flex-column gap-1 border rounded w-100 bg-light"
                        style="overflow: auto; max-height: 40svh; position: absolute; z-index: 3"></div>
                    </div>
                </div>
                <div class="d-flex gap-2 flex-wrap justify-content-lg-end">
                    <button onclick="sinkronisasi(event)" class="btn btn-outline-dark px-3 py-2 rounded-pill">
                        <i class="material-icons align-middle" style="font-size: 18px;">sync</i>
                        Info Sync
                    </button>
                <?php if (empty($stockManagedByLuna)) { ?>
                    <button onclick="openTambal()" class="btn btn-primary1 px-3 py-2 rounded-pill">Tambah</button>
                <?php } ?>
                </div>
            </div>
        </div>
        <?php if (!empty($stockManagedByLuna)) { ?>
        <div class="stok-sync-card mb-4" role="alert">
            <div class="d-flex flex-column flex-md-row gap-2 justify-content-between align-items-md-center">
                <div class="d-flex gap-3 align-items-start">
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:42px;height:42px;background:#ddf7e7;color:#207247;flex:0 0 42px;">
                        <i class="material-icons">lock</i>
                    </div>
                    <div>
                    <strong>Stok website dikunci dari admin website.</strong><br>
                    <span class="small">Sumber stok, harga, dan varian sekarang wajib dari <b>Luna Sistem / MyLuna</b>. Halaman ini hanya untuk monitoring riwayat mutasi sinkronisasi.</span>
                    </div>
                </div>
                <span class="badge bg-success align-self-start align-self-md-center rounded-pill px-3 py-2">Source of truth: MyLuna</span>
            </div>
        </div>
        <?php } ?>
        <?php if ($idProduk != 'all') { ?>
        <div class="stok-varian-grid mb-4">
            <?php foreach ($stokVarian as $s) { ?>
            <div class="stok-varian-card">
                <p class="mb-1 text-secondary"><?= $s['nama']; ?></p>
                <h3 class="mb-0 fw-bold"><?= $s['stok']; ?></h3>
                <small class="text-secondary">stok website</small>
            </div>
            <?php } ?>
        </div>
        <?php } ?>
        <?php if ($msg) { ?>
        <div class="alert alert-danger" role="alert">
            <?= $msg; ?>
        </div>
        <?php } ?>
        <div class="stok-table-card mb-3">
            <div style="overflow-x: auto;">
                <div class="stok-table-head">
                    <div>Tanggal</div>
                    <div>Nama</div>
                    <div>Varian</div>
                    <div>Jumlah</div>
                    <div>PJ</div>
                    <div>Keterangan</div>
                    <div>Stok Akhir</div>
                    <div>Action</div>
                </div>
                <div class="d-flex flex-column gap-2">
                    <?php if (count($stok) > 0) { ?>
                    <?php foreach ($stok as $ind_s => $s) { ?>
                    <div class="stok-table-row">
                        <div class="m-0 small text-secondary"><?= $s['tanggal']; ?></div>
                        <div class="m-0 fw-semibold"><?= $s['nama']; ?></div>
                        <div class="m-0"><span class="badge bg-light text-dark border rounded-pill"><?= $s['varian']; ?></span></div>
                        <div class="m-0" style="color: <?= $s['jumlah'] < 0 ? '#dc2626' : '#16834a'; ?>">
                            <span class="stok-qty"><?= $s['jumlah'] < 0 ? '' : '+'; ?><?= $s['jumlah']; ?></span></div>
                        <?php if ($s['nama_admin']) { ?>
                        <div class="m-0"><?= explode(' ', $s['nama_admin'])[0]; ?>
                            <?= count(explode(' ', $s['nama_admin'])) > 1 ? substr(explode(' ', $s['nama_admin'])[1], 0, 1) : ''; ?>
                        </div>
                        <?php } else { ?>
                        <div class="m-0 text-sm text-secondary"><i>Butuh konfirm</i></div>
                        <?php } ?>
                        <div class="m-0 small"><?= $s['keterangan']; ?></div>
                        <div class="m-0 fw-bold"><?= $s['stok_akhir']; ?></div>
                        <div class="m-0 d-flex justify-content-center align-items-center">
                            <?php if (!$s['nama_admin'] && empty($stockManagedByLuna)) { ?>
                            <button type="button" onclick="openKofirm(<?= $ind_s; ?>)" class="btn btn-primary1 p-2"><i
                                    class="material-icons" style="font-size: 12px;">border_color</i></button>
                            <?php } elseif (!$s['nama_admin']) { ?>
                            <span class="stok-action-lock">Dikunci</span>
                            <?php } ?>
                        </div>
                    </div>
                    <?php } ?>
                    <?php } else { ?>
                    <div class="py-5 text-center text-sm text-secondary"><i>Belum ada data mutasi</i></div>
                    <?php } ?>
                </div>
            </div>
        </div>
        <?php if ($countAllStok > 20) { ?>
        <?php
            $currentPage = max(1, (int)$pag);
            $totalPages = max(1, (int)ceil($countAllStok / 20));
            $currentPage = min($currentPage, $totalPages);
            $basePageUrl = '/stokadmin/' . ($idProduk == 'all' ? 'all' : $produk['id']);
            $pages = [1, $totalPages];
            for ($x = $currentPage - 2; $x <= $currentPage + 2; $x++) {
                if ($x >= 1 && $x <= $totalPages) $pages[] = $x;
            }
            $pages = array_values(array_unique($pages));
            sort($pages);
            $lastPrintedPage = 0;
        ?>
        <nav class="stok-pagination-wrap" aria-label="Navigasi halaman mutasi stok">
            <div class="small text-secondary">
                Halaman <strong><?= $currentPage; ?></strong> dari <strong><?= $totalPages; ?></strong> · <?= number_format($countAllStok, 0, ',', '.'); ?> mutasi
            </div>
            <ul class="pagination justify-content-center mb-0">
                <li class="page-item <?= $currentPage <= 1 ? 'disabled' : ''; ?>">
                    <a class="page-link" href="<?= $currentPage <= 1 ? '#' : $basePageUrl . '/' . ($currentPage - 1); ?>" aria-label="Halaman sebelumnya">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                </li>
                <?php foreach ($pages as $pageNumber) { ?>
                    <?php if ($lastPrintedPage && $pageNumber > $lastPrintedPage + 1) { ?>
                    <li class="page-item disabled"><span class="page-link">...</span></li>
                    <?php } ?>
                    <li class="page-item <?= $pageNumber === $currentPage ? 'active' : ''; ?>">
                        <a class="page-link" href="<?= $basePageUrl . '/' . $pageNumber; ?>" aria-label="Halaman <?= $pageNumber; ?>"><?= $pageNumber; ?></a>
                    </li>
                    <?php $lastPrintedPage = $pageNumber; ?>
                <?php } ?>
                <li class="page-item <?= $currentPage >= $totalPages ? 'disabled' : ''; ?>">
                    <a class="page-link" href="<?= $currentPage >= $totalPages ? '#' : $basePageUrl . '/' . ($currentPage + 1); ?>" aria-label="Halaman berikutnya">
                        <span aria-hidden="true">&raquo;</span>
                    </a>
                </li>
            </ul>
        </nav>
        <?php } ?>
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
console.log(produkAll)
const modalAddElm = document.getElementById('modal-add');
const containerCariBarangElm = document.getElementById('container-cari-barang')

function openTambal() {
    window.alert('Stok website sudah wajib diperbarui dari Luna Sistem. Silakan ubah stok dari MyLuna, lalu jalankan sync produk ke website.');
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
    containerCariBarangElm.innerHTML += `<a href="/stokadmin/all/1" class="item-list-produk rounded">All</a>`
    produkFilter.forEach(p => {
        containerCariBarangElm.innerHTML +=
            `<a href="/stokadmin/${p.id}/1" class="item-list-produk rounded">${p.nama}</a>`
    });
}

function sinkronisasi(e) {
    e.target.innerHTML = 'Loading'
    async function fetchBenerin() {
        try {
            const res = await fetch('/benerinstokluna');
            const resJson = await res.json();
            e.target.innerHTML = 'Info Sync'
            window.alert(resJson.message || 'Stok website mengikuti Luna Sistem. Jalankan sync dari MyLuna untuk memperbarui data.')
        } catch (error) {
            e.target.innerHTML = 'Info Sync'
            console.log(error)
        }
    }
    fetchBenerin()
}
</script>
<?= $this->endSection(); ?>
