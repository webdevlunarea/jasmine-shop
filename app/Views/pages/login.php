<?= $this->extend('layout/template'); ?>
<?= $this->section('content'); ?>
<div class="konten auth-page">
    <div class="container">
        <div class="auth-shell">
            <section class="auth-panel">
                <?php if ($val['msg']) { ?>
                    <div class="alert alert-success auth-alert" role="alert"><?= $val['msg']; ?></div>
                <?php } ?>
                <div class="auth-heading">
                    <h1>Masuk ke akun</h1>
                    <p>Selamat datang kembali di Lunarea.</p>
                </div>
                <a class="auth-google-btn" href="/auth/google<?= !empty($redirect) ? '?redirect=' . rawurlencode($redirect) : ''; ?>" aria-label="Masuk dengan Google">
                    <svg class="auth-google-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                        <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                        <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                        <path fill="#FBBC05" d="M5.84 14.1c-.22-.66-.35-1.36-.35-2.1s.13-1.44.35-2.1V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l3.66-2.84z"/>
                        <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06L5.84 9.9C6.71 7.3 9.14 5.38 12 5.38z"/>
                    </svg>
                    <span>Masuk dengan Google</span>
                </a>
                <?= view('partials/auth_facebook', ['redirect' => $redirect ?? ''], ['saveData' => false]); ?>
                <div class="auth-divider"><span>atau masuk dengan email</span></div>
                <form action="/masuk" method="post" class="auth-form">
                    <?= csrf_field(); ?>
                    <?php if (!empty($redirect)) { ?>
                        <input type="hidden" name="redirect" value="<?= esc($redirect); ?>">
                    <?php } ?>
                    <div class="auth-field">
                        <label for="login-email">Email</label>
                        <input id="login-email" type="email" class="form-control <?= ($val['val_email']) ? "is-invalid" : ""; ?>" placeholder="name@example.com" name="email" value="<?= esc($val['isiEmail'] ?? ''); ?>" autocomplete="email" required>
                        <div class="invalid-feedback"><?= $val['val_email']; ?></div>
                    </div>
                    <div class="auth-field">
                        <label for="login-password">Sandi</label>
                        <input id="login-password" type="password" class="form-control <?= ($val['val_sandi']) ? "is-invalid" : ""; ?>" placeholder="Masukkan sandi" name="sandi" autocomplete="current-password" required>
                        <button class="auth-password-toggle" type="button" data-target="login-password" aria-label="Tampilkan sandi" aria-pressed="false"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/><path class="auth-eye-slash d-none" d="m3 3 18 18"/></svg></button>
                        <div class="invalid-feedback"><?= $val['val_sandi']; ?></div>
                    </div>
                    <?= view('partials/auth_recaptcha', [], ['saveData' => false]); ?>
                    <input class="btn btn-primary1 auth-submit" disabled type="submit" value="Masuk">
                </form>
                <div class="auth-switch">Belum punya akun? <a href="/signup<?= !empty($redirect) ? '?redirect=' . rawurlencode($redirect) : ''; ?>">Daftar sekarang</a></div>
            </section>
        </div>
    </div>
</div>
<script>
    const emailInputElm = document.querySelector('input[name="email"]');
    const passInputElm = document.querySelector('input[name="sandi"]');
    const btnMasukElm = document.querySelector('.auth-submit');

    document.addEventListener('lunarea:captcha', syncLoginButton);

    function syncLoginButton() {
        btnMasukElm.disabled = !((!document.querySelector('.auth-captcha') || window.lunareaCaptchaReady === true) && emailInputElm.value.trim() && passInputElm.value.trim());
    }

    emailInputElm.addEventListener('input', syncLoginButton);
    passInputElm.addEventListener('input', syncLoginButton);
    syncLoginButton();

    document.querySelectorAll('.auth-password-toggle').forEach((button) => {
        button.addEventListener('click', () => {
            const input = document.getElementById(button.dataset.target);
            const show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            button.setAttribute('aria-pressed', String(show));
            button.setAttribute('aria-label', show ? 'Sembunyikan sandi' : 'Tampilkan sandi');
            button.querySelector('.auth-eye-slash').classList.toggle('d-none', !show);
        });
    });
</script>
<?= $this->endSection(); ?>
