<?= $this->extend('layout/template'); ?>
<?= $this->section('content'); ?>
<div class="konten">
    <div class="container">
        <div class="row">
            <div class="col">
                <img src="img/nopic.jpg" alt="nopic.jpg">
            </div>
            <div class="col">
                <?php if ($val['msg']) { ?>
                    <div class="alert alert-success" role="alert">
                        <?= $val['msg']; ?>
                    </div>
                <?php } ?>
                <h1>Masuk</h1>
                <p>Masukan informasimu dibawah</p>
                <form action="/masuk" method="post">
                    <?= csrf_field(); ?>
                    <div class="form-floating mb-1">
                        <input type="email" class="form-control <?= ($val['val_email']) ? "is-invalid" : ""; ?>" placeholder="name@example.com" name="email" value="<?= old('email'); ?>">
                        <label for="floatingInput">Email</label>
                        <div class="invalid-feedback">
                            <?= $val['val_email']; ?>
                        </div>
                    </div>
                    <div class="form-floating mb-3">
                        <input type="password" class="form-control <?= ($val['val_sandi']) ? "is-invalid" : ""; ?>" placeholder="Password" name="sandi">
                        <label for="floatingPassword">Sandi</label>
                        <div class="invalid-feedback">
                            <?= $val['val_sandi']; ?>
                        </div>
                    </div>
                    <input class="btn btn-danger" type="submit" value="Masuk">
                </form>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>