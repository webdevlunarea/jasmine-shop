<?= $this->extend('layout/template'); ?>
<?= $this->section('content'); ?>
<div class="konten">
    <div class="container">
        <div class="row">
            <div class="col">
                <img src="img/nopic.jpg" alt="nopic.jpg">
            </div>
            <div class="col">
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
                    <input class="btn btn-danger" type="submit" value="Buat">
                </form>
                <p>Sudah punya akun? <a href="/login" class="link-dark link-offset-2 link-underline-opacity-25 link-underline-opacity-100-hover">Masuk</a></p>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>