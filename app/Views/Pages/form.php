<?= $this->extend('layout/template'); ?>
<?= $this->section('content'); ?>
<div class="konten">
    <div class="container">
        <nav style="--bs-breadcrumb-divider: '>'" aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/">Beranda</a></li>
                <li class="breadcrumb-item active" aria-current="page">
                    Kontak Kami
                </li>
            </ol>
        </nav>
        <h1 class="text-center">Kontak Kami</h1>
        <h5 class="mb-4 text-center" style="color: var(--hijau)">Tuliskan pesan Anda di formulir berikut</h5>
        <div class="d-flex flex-column align-items-center justify-content-center">
            <?php if ($val['msg']) { ?>
                <div class="alert alert-success" style="width: fit-content;" role="alert">
                    <?= $val['msg']; ?>
                </div>
            <?php } ?>
            <div class="py-3" style="max-width: 700px; width: 100%;">
                <form action="/actionform" method="post">
                    <div class="mb-2">
                        <label for="floatingInput" class="mb-2">Nama Lengkap</label>
                        <input type="text" class="form-control" placeholder="Nama Lengkap" name="nama" required>
                    </div>
                    <div class="mb-2">
                        <label for="floatingInput" class="mb-2">No. HP</label>
                        <input type="number" class="form-control" placeholder="Nomor Handphone" name="nohp" required>
                    </div>
                    <div class="mb-2">
                        <label for="floatingInput" class="mb-2">Alamat <p style="color: var(--hijau); display: inline;">(opsional)</p></label>
                        <input type="alamat" class="form-control" placeholder="Alamat Anda" name="alamat">
                    </div>
                    <div class="mb-2">
                        <label for="floatingInput" class="mb-2">Pesan <p style="color: var(--hijau); display: inline;">(opsional)</p></label>
                        <textarea class="form-control" placeholder="Tuliskan pesan Anda" name="pesan">Hai CS Jasmine, saya ingin menanyakan mengenai ...</textarea>
                    </div>
                    <div class="d-flex justify-content-center">
                        <button type="submit" disabled class="btn btn-primary1" id="btn-kirim">Kirim</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    const namaInputElm = document.querySelector('input[name="nama"]')
    const nohpInputElm = document.querySelector('input[name="nohp"]')
    const btnKirimElm = document.getElementById("btn-kirim");
    namaInputElm.addEventListener("input", (e) => {
        if (e.target.value != '' && nohpInputElm.value != '') btnKirimElm.disabled = false;
        else btnKirimElm.disabled = true;
    })
    nohpInputElm.addEventListener("input", (e) => {
        if (e.target.value != '' && namaInputElm.value != '') btnKirimElm.disabled = false;
        else btnKirimElm.disabled = true;
    })
</script>
<?= $this->endSection(); ?>