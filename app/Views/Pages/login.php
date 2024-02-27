<?= $this->extend('layout/template'); ?>
<?= $this->section('content'); ?>
<div class="konten d-flex justify-content-center align-items-center">
    <div class="container">
        <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/">Beranda</a></li>
                <li class="breadcrumb-item active" aria-current="page">Masuk</li>
            </ol>
        </nav>
        <div class="d-flex gap-5 align-items-center">
            <div class="show-ke-hide" style="flex: 1;">
                <img style="width: 100%; border-radius: 10px; aspect-ratio: 1/1; object-fit: cover;" src="/img/Login.webp" alt="nopic.jpg">
            </div>
            <div style="flex: 1;">
                <?php if ($val['msg']) { ?>
                    <div class="alert alert-success" role="alert">
                        <?= $val['msg']; ?>
                    </div>
                <?php } ?>
                <h3>Masuk</h3>
                <p>Masukan data Anda dibawah ini</p>
                <form action="/masuk" method="post">
                    <?= csrf_field(); ?>
                    <div class="form-floating mb-1">
                        <input type="email" class="form-control <?= ($val['val_email']) ? "is-invalid" : ""; ?>" placeholder="name@example.com" name="email" value="<?= $val['isiEmail']; ?>">
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
                    <input class="btn btn-primary1" type="submit" value="Masuk">
                </form>
                <p class="mt-3">Belum punya akun? <a href="/signup" style="color: var(--hijau);" class="link-offset-2 link-underline-opacity-25 link-underline-opacity-100-hover">Daftar
                        disini</a></p>
                <span class="garis mb-3"></span>
                <a class="btn btn-primary1" href="/logintamu">Masuk sebagai tamu</a>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>