<?= $this->extend('layout/template'); ?>
<?= $this->section('content'); ?>
<div class="konten auth-page auth-reset-page">
    <div class="container">
        <div class="auth-shell auth-reset-shell">
            <section class="auth-panel auth-reset-panel" aria-labelledby="reset-title">
                <div class="auth-reset-brand">
                    <span class="auth-reset-mark" aria-hidden="true">
                        <?php if ($step === 'confirm') : ?>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><rect x="3.5" y="5" width="17" height="14" rx="2"/><path d="m4.5 6 7.5 6 7.5-6"/></svg>
                        <?php else : ?>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="7" width="16" height="13" rx="2"/><path d="M8 7V5a4 4 0 0 1 8 0v2M12 12v3"/></svg>
                        <?php endif; ?>
                    </span>
                    <span>Pemulihan akun Lunarea</span>
                </div>
                <div class="auth-heading">
                    <h1 id="reset-title"><?= $step === 'reset' ? 'Buat sandi baru' : ($step === 'confirm' ? 'Konfirmasi email pemulihan' : 'Lupa sandi?'); ?></h1>
                    <p><?= $step === 'reset' ? 'Masukkan kode dari email, lalu tentukan sandi baru Anda.' : ($step === 'confirm' ? 'Pastikan alamat pemulihan ini benar sebelum kami mengirim kode verifikasi.' : 'Masukkan email atau username akun Anda untuk melihat alamat pemulihan.'); ?></p>
                </div>
                <?php if (!empty($message)) : ?><div class="auth-alert auth-reset-note" role="status"><?= esc($message); ?></div><?php endif; ?>
                <?php if (!empty($error)) : ?><div class="alert alert-danger auth-alert" role="alert"><?= esc($error); ?></div><?php endif; ?>
                <form class="auth-form" id="password-reset-form" method="post" action="<?= esc(site_url($step === 'request' ? 'lupa-sandi' : 'reset-sandi'), 'attr'); ?>">
                    <?= csrf_field(); ?>
                    <?php if ($step === 'request') : ?>
                        <div class="auth-field">
                            <label for="reset-identity">Email atau username</label>
                            <input id="reset-identity" class="form-control" type="text" name="identity" maxlength="254" autocomplete="username" required>
                        </div>
                        <?= view('partials/auth_recaptcha', [], ['saveData' => false]); ?>
                        <button type="submit" data-primary-submit class="btn btn-primary1 auth-submit">Lanjutkan</button>
                    <?php elseif ($step === 'confirm') : ?>
                        <div class="auth-reset-email-confirm" aria-live="polite" aria-label="Alamat email pemulihan">
                            <span class="auth-reset-email-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><rect x="3.5" y="5" width="17" height="14" rx="2"/><path d="m4.5 6 7.5 6 7.5-6"/></svg></span>
                            <div class="auth-reset-email-copy">
                                <span class="auth-reset-email-label">Email pemulihan</span>
                                <strong><?= esc($maskedEmail); ?></strong>
                                <small>Kode verifikasi akan dikirim ke alamat ini.</small>
                            </div>
                        </div>
                        <label class="auth-reset-confirm-check" for="confirm-reset-email">
                            <input id="confirm-reset-email" type="checkbox" name="confirm_email" value="1" required>
                            <span>Saya dapat membuka email tersebut.</span>
                        </label>
                        <?= view('partials/auth_recaptcha', [], ['saveData' => false]); ?>
                        <button type="submit" data-primary-submit class="btn btn-primary1 auth-submit">Kirim kode ke email ini</button>
                        <button type="submit" class="auth-reset-change" name="change_identity" value="1" formnovalidate>Gunakan email atau username lain</button>
                    <?php else : ?>
                        <div class="auth-field">
                            <label for="reset-otp">Kode verifikasi</label>
                            <input id="reset-otp" class="form-control auth-otp" type="text" name="otp" inputmode="numeric" pattern="[0-9]{6}" minlength="6" maxlength="6" autocomplete="one-time-code" aria-describedby="reset-code-help" required>
                            <p class="auth-reset-help" id="reset-code-help">6 digit angka · Berlaku 10 menit · Maksimal 5 percobaan</p>
                        </div>
                        <div class="auth-field">
                            <label for="reset-password">Sandi baru</label>
                            <input id="reset-password" class="form-control" type="password" name="sandi" minlength="8" maxlength="72" pattern="(?=.*[0-9]).{8,}" autocomplete="new-password" aria-describedby="reset-password-help" required>
                            <p class="auth-reset-help" id="reset-password-help">Minimal 8 karakter dan wajib mengandung angka.</p>
                        </div>
                        <div class="auth-field">
                            <label for="reset-confirmation">Konfirmasi sandi baru</label>
                            <input id="reset-confirmation" class="form-control" type="password" name="konfirmasi_sandi" minlength="8" maxlength="72" autocomplete="new-password" required>
                        </div>
                        <button type="button" class="auth-reset-toggle" id="reset-password-toggle" aria-pressed="false" aria-controls="reset-password reset-confirmation">Tampilkan sandi</button>
                        <button type="submit" class="btn btn-primary1 auth-submit">Simpan sandi baru</button>
                    <?php endif; ?>
                </form>
                <?php if ($step === 'reset') : ?>
                    <div class="auth-switch">Belum menerima kode? <a href="<?= esc(site_url('lupa-sandi'), 'attr'); ?>">Kirim ulang kode</a><p class="auth-reset-help">Tunggu 60 detik sebelum meminta ulang. Maksimal 3 permintaan per email dalam 15 menit.</p></div>
                <?php endif; ?>
                <div class="auth-switch"><a href="<?= esc(site_url('login'), 'attr'); ?>">Kembali ke halaman masuk</a></div>
            </section>
        </div>
    </div>
</div>
<script>
(() => {
    const form = document.getElementById('password-reset-form');
    const submit = form.querySelector('[data-primary-submit]') || form.querySelector('[type="submit"]');
    function syncCaptcha() {
        submit.disabled = !!form.querySelector('.auth-captcha') && window.lunareaCaptchaReady !== true;
    }
    document.addEventListener('lunarea:captcha', syncCaptcha);
    syncCaptcha();
    form.addEventListener('submit', event => {
        if (event.submitter && event.submitter.name === 'change_identity') return;
        submit.disabled = true;
        submit.textContent = 'Memproses…';
    });
    const toggle = document.getElementById('reset-password-toggle');
    if (toggle) toggle.addEventListener('click', () => {
        const show = toggle.getAttribute('aria-pressed') !== 'true';
        ['reset-password', 'reset-confirmation'].forEach(id => document.getElementById(id).type = show ? 'text' : 'password');
        toggle.setAttribute('aria-pressed', String(show));
        toggle.textContent = show ? 'Sembunyikan sandi' : 'Tampilkan sandi';
    });
})();
</script>
<?= $this->endSection(); ?>
