<?= $this->extend('layout/template'); ?>
<?= $this->section('content'); ?>
<div class="konten d-flex justify-content-center align-items-center">
    <div>
        <h1 class="text-center">Verifikasi</h1>
        <?php if ($val['msg']) { ?>
            <div class="alert alert-success text-center" role="alert">
                <?= $val['msg']; ?>
            </div>
        <?php } ?>
        <p class="text-center">Masukkan kode OTP dibawah ini!</p>
        <form action="/verify" method="post">
            <?= csrf_field(); ?>
            <div class="form mb-1">
                <input type="number" style="letter-spacing: 2em" autofocus class="text-center form-control <?= ($val['val_verify']) ? "is-invalid" : ""; ?>" name="otp">
                <div class="invalid-feedback">
                    <?= $val['val_verify']; ?>
                </div>
            </div>
            <input class="btn btn-primary1 w-100" type="submit" value="Verifikasi">
        </form>
        <p class="mt-3">OTP belum terkirim? <a href="/kirimotp" style="color: var(--hijau);" class="link-offset-2 link-underline-opacity-25 link-underline-opacity-100-hover">Klik disini</a> untuk mengirim ulang</p>
    </div>
</div>
<?= $this->endSection(); ?>