<?= $this->extend('layout/template'); ?>
<?= $this->section('content'); ?>
<div class="konten auth-page">
    <div class="container">
        <nav aria-label="breadcrumb" class="auth-breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/">Beranda</a></li>
                <li class="breadcrumb-item active" aria-current="page">Masuk</li>
            </ol>
        </nav>
        <div class="auth-shell">
            <aside class="auth-visual show-ke-hide">
                <img src="/img/Login.webp" alt="Lunarea Furniture">
                <div class="auth-visual__overlay">
                    <span>Member Lunarea</span>
                    <h2>Belanja furniture lebih mudah dan terpantau.</h2>
                    <p>Cek transaksi, simpan wishlist, dan lanjutkan checkout dengan akun Anda.</p>
                </div>
            </aside>
            <section class="auth-panel">
                <a href="/" class="auth-logo"><img src="<?= base_url('/img/Logo Lunarea Bg Terang ukuran kecil.webp'); ?>" alt="Lunarea"></a>
                <?php if ($val['msg']) { ?>
                    <div class="alert alert-success auth-alert" role="alert"><?= $val['msg']; ?></div>
                <?php } ?>
                <div class="auth-heading">
                    <p class="auth-eyebrow">Selamat datang</p>
                    <h1>Masuk ke akun</h1>
                    <p>Gunakan email dan sandi untuk melanjutkan belanja di Lunarea Furniture.</p>
                </div>
                <form action="/masuk" method="post" class="auth-form">
                    <?= csrf_field(); ?>
                    <div class="form-floating auth-field">
                        <input id="login-email" type="email" class="form-control <?= ($val['val_email']) ? "is-invalid" : ""; ?>" placeholder="name@example.com" name="email" value="<?= $val['isiEmail']; ?>" autocomplete="email" required>
                        <label for="login-email">Email</label>
                        <div class="invalid-feedback"><?= $val['val_email']; ?></div>
                    </div>
                    <div class="form-floating auth-field">
                        <input id="login-password" type="password" class="form-control <?= ($val['val_sandi']) ? "is-invalid" : ""; ?>" placeholder="Password" name="sandi" autocomplete="current-password" required>
                        <label for="login-password">Sandi</label>
                        <button class="auth-password-toggle" type="button" data-target="login-password" aria-label="Tampilkan sandi"><i class="material-icons">visibility</i></button>
                        <div class="invalid-feedback"><?= $val['val_sandi']; ?></div>
                    </div>
                    <input class="btn btn-primary1 auth-submit" disabled type="submit" value="Masuk">
                </form>
                <div class="auth-switch">Belum punya akun? <a href="/signup">Daftar sekarang</a></div>
                <div class="auth-divider"><span>atau</span></div>
                <form action="/logintamu" method="post">
                    <button type="submit" id="btn-masuk-tamu" class="btn btn-outline-dark auth-guest">Masuk sebagai tamu</button>
                </form>
            </section>
        </div>
    </div>
</div>
<script>
    const emailInputElm = document.querySelector('input[name="email"]');
    const passInputElm = document.querySelector('input[name="sandi"]');
    const btnMasukElm = document.querySelector('.auth-submit');

    function syncLoginButton() {
        btnMasukElm.disabled = !(emailInputElm.value.trim() && passInputElm.value.trim());
    }

    emailInputElm.addEventListener('input', syncLoginButton);
    passInputElm.addEventListener('input', syncLoginButton);
    syncLoginButton();

    document.querySelectorAll('.auth-password-toggle').forEach((button) => {
        button.addEventListener('click', () => {
            const input = document.getElementById(button.dataset.target);
            const show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            button.querySelector('.material-icons').textContent = show ? 'visibility_off' : 'visibility';
        });
    });
</script>
<?= $this->endSection(); ?>
