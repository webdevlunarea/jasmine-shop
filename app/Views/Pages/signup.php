<?= $this->extend('layout/template'); ?>
<?= $this->section('content'); ?>
<div class="konten d-flex justify-content-center align-items-center">
    <div class="container">
        <div class="d-flex gap-5">
            <div class="show-ke-hide">
                <img src="img/nopic.jpg" alt="nopic.jpg">
            </div>
            <div style="flex: 1;">
                <h1>Buat Akun</h1>
                <p>Masukan informasimu dibawah</p>
                <form action="/daftar" method="post">
                    <?= csrf_field(); ?>
                    <div class="form-floating mb-1">
                        <input type="email" class="form-control <?= ($val['val_email']) ? "is-invalid" : ""; ?>" placeholder="name@example.com" name="email" value="<?= old('email'); ?>">
                        <label for="floatingInput">Email</label>
                        <div class="invalid-feedback">
                            <?= $val['val_email']; ?>
                        </div>
                    </div>
                    <div class="form-floating mb-1">
                        <input type="password" class="form-control <?= ($val['val_sandi']) ? "is-invalid" : ""; ?>" placeholder="Password" name="sandi" value="<?= old('sandi'); ?>">
                        <label for="floatingPassword">Sandi</label>
                        <div class="invalid-feedback">
                            <?= $val['val_sandi']; ?>
                        </div>
                    </div>
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control <?= ($val['val_alamat']) ? "is-invalid" : ""; ?>" placeholder="Alamat" name="alamat" value="<?= old('alamat'); ?>">
                        <label for="floatingPassword">Alamat</label>
                        <div class="invalid-feedback">
                            <?= $val['val_alamat']; ?>
                        </div>
                    </div>
                    <input class="btn btn-primary" type="submit" value="Buat">
                </form>
                <p class="mt-3">Sudah punya akun? <a href="/login" style="color: var(--hijau);" class="link-offset-2 link-underline-opacity-25 link-underline-opacity-100-hover">Masuk</a></p>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>