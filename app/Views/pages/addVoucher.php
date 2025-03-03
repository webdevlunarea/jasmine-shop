<?= $this->extend("layout/template"); ?>
<?= $this->section("content"); ?>
<div class="konten">
    <div class="container">
        <form action="/actionaddvoucher" method="post" enctype="multipart/form-data">
            <?= csrf_field(); ?>
            <h3 class="">Buat Voucher</h3>
            <?php if ($msg) { ?>
                <div class="alert alert-danger" role="alert">
                    <?= $msg; ?>
                </div>
            <?php } ?>
            <div class="mb-2">
                <label class="form-label">Nama</label>
                <input type="text" class="form-control" name="nama" required>
            </div>
            <div class="d-flex mb-2 gap-1">
                <div style="flex: 1">
                    <label class="form-label">Nominal</label>
                    <input type="number" class="form-control" name="nominal" required>
                </div>
                <div style="flex: 1">
                    <label class="form-label">Satuan</label>
                    <select name="satuan" class="form-select">
                        <option value="persen" selected>Persen</option>
                        <option value="rupiah">Rupiah</option>
                    </select>
                </div>
            </div>
            <div class="mb-2">
                <label class="form-label">Jenis</label>
                <select name="jenis" class="form-select">
                    <option value="member" selected>Member</option>
                    <option value="cashback">Cashback</option>
                    <option value="potongan">Potongan</option>
                </select>
            </div>
            <div class="mb-2">
                <label class="form-label m-0">Kuota Customer</label>
                <p class="text-secondary mb-1" style="font-size: small;">Jika tak terbatas isi dengan "-1"</p>
                <input type="number" class="form-control" value="-1" name="kuota">
            </div>
            <div class="d-flex gap-1 mb-2">
                <div style="flex: 1;">
                    <label class="form-label m-0">Durasi Voucher</label>
                    <p class="text-secondary mb-1" style="font-size: small;">Masa aktif voucher setelah di klaim</p>
                    <select name="durasi" class="form-select">
                        <option value="null" selected>Tak hingga</option>
                        <option value="+3 days">3 Hari</option>
                        <option value="+1 month">1 Bulan</option>
                        <option value="+1 year">1 Tahun</option>
                    </select>
                </div>
                <div style="flex: 1;">
                    <label class="form-label m-0">Durasi Poin</label>
                    <p class="text-secondary mb-1" style="font-size: small;">Masa aktif poin yang di dapat dari voucher cashback</p>
                    <select name="durasi-poin" class="form-select">
                        <option value="null" selected>Tak hingga</option>
                        <option value="+3 days">3 Hari</option>
                        <option value="+1 month">1 Bulan</option>
                        <option value="+1 year">1 Tahun</option>
                    </select>
                </div>
            </div>
            <div class="mb-2">
                <label class="form-label">Poster</label>
                <input type="file" class="form-control" name="poster">
            </div>
            <div class="mb-2">
                <label class="form-label">Keterangan</label>
                <textarea class="form-control" name="keterangan"></textarea>
            </div>
            <div class="mb-2">
                <label class="form-label m-0">Email Customer</label>
                <p class="text-secondary mb-1" style="font-size: small;">List email yang dapat mengklaim voucher</p>
                <textarea class="form-control mb-1" name="email" placeholder="Pisahkan dengan tanda ampersand (&)"></textarea>
                <div class="d-flex gap-1">
                    <input type="checkbox" onchange="handleChangeAllUser(event)" name="set-all-user-voucher" id="checkbox1">
                    <label for="checkbox1">Tambahkan akses klaim ke seluruh customer</label>
                </div>
                <div class="d-none gap-1">
                    <input type="checkbox" name="set-redeem" id="checkbox3">
                    <label for="checkbox3">Berikan code redeem</label>
                </div>
            </div>
            <div class="mb-2">
                <label class="form-label m-0">Syarat & Ketentuan</label>
                <p class="text-secondary mb-1" style="font-size: small;">Jika tidak ada biarkan kosong</p>
                <textarea class="form-control" name="syarat-ketentuan" placeholder="Ditulis dalam bentuk HTML"></textarea>
            </div>
            <hr>
            <h5 class="m-0">Penjadwalan</h5>
            <p class="text-secondary mb-1" style="font-size: small;">*kosongin jika tanpa penjadwalan</p>
            <div class="baris-ke-kolom gap-1">
                <div style="flex: 1">
                    <p class="m-0">Dari</p>
                    <input name="jadwal1" type="date" class="form-control" min="<?= date('Y-m-d', strtotime('+7 hours')); ?>">
                </div>
                <div style="flex: 1">
                    <p class="m-0">Sampai</p>
                    <input name="jadwal2" type="date" class="form-control" min="<?= date('Y-m-d', strtotime('+7 hours')); ?>">
                </div>
            </div>
            <hr>
            <h5 class="m-0">Broadcast Email Customer</h5>
            <p class="text-secondary mb-1" style="font-size: small;">Memberikan informasi ke customer mengenai voucher ini</p>
            <div class="d-flex gap-1 mb-2">
                <input onchange="handleChangeBroadcast(event)" checked type="checkbox" name="broadcast" id="broadcast-checkbox">
                <label for="broadcast-checkbox">Broadcast ke customer</label>
            </div>
            <div id="form-broadcast">
                <div class="mb-2">
                    <label class="form-label m-0">Poster Email</label>
                    <input type="file" class="form-control" name="poster-email">
                </div>
                <div class="mb-2">
                    <label class="form-label">Isi Email</label>
                    <textarea class="form-control" name="isi-email" placeholder="Ditulis dalam bentuk HTML"></textarea>
                </div>
            </div>
            <hr>
            <div class="mb-2">
                <p class="m-0">Lain-lain</p>
                <div class="d-flex gap-1">
                    <input type="checkbox" name="auto-claimed" id="checkbox2">
                    <label for="checkbox2">Auto klaim ketika customer registrasi</label>
                </div>
            </div>
            <hr>
            <input type="number" name="hitungProduk" class="d-none">
            <button type="submit" class="btn btn-dark">Simpan</button>
        </form>
    </div>
</div>
<script>
    const keteranganElm = document.querySelector('textarea[name="email"]');
    const formBroadcastElm = document.getElementById('form-broadcast')

    function handleChangeAllUser(e) {
        const valuenya = e.target.checked;
        if (valuenya) keteranganElm.classList.add('d-none');
        else keteranganElm.classList.remove('d-none');
    }

    function handleChangeBroadcast(e) {
        const valuenya = e.target.checked;
        if (valuenya) formBroadcastElm.classList.remove('d-none');
        else formBroadcastElm.classList.add('d-none');
    }
</script>
<?= $this->endSection(); ?>