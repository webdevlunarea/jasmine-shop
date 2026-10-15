<?php $captchaConfig = $captchaConfig ?? config(\Config\Recaptcha::class); ?>
<?php if ($captchaConfig->enabled): ?>
<div class="auth-captcha">
    <?php if (trim($captchaConfig->siteKey) !== ''): ?>
        <div class="g-recaptcha" data-sitekey="<?= esc($captchaConfig->siteKey, 'attr'); ?>" data-size="compact"
             data-callback="lunareaCaptchaSuccess" data-expired-callback="lunareaCaptchaExpired" data-error-callback="lunareaCaptchaError"></div>
    <?php endif; ?>
    <p class="auth-captcha-status" aria-live="polite">Selesaikan verifikasi keamanan sebelum melanjutkan.</p>
    <noscript><p>Aktifkan JavaScript untuk menjalankan verifikasi keamanan.</p></noscript>
</div>
<script>
    window.lunareaCaptchaReady = false;
    window.lunareaCaptchaUpdate = function (ready, message) {
        window.lunareaCaptchaReady = ready;
        document.querySelector('.auth-captcha-status').textContent = message;
        document.dispatchEvent(new Event('lunarea:captcha'));
    };
    window.lunareaCaptchaSuccess = function () { window.lunareaCaptchaUpdate(true, 'Verifikasi keamanan selesai.'); };
    window.lunareaCaptchaExpired = function () { window.lunareaCaptchaUpdate(false, 'Verifikasi kedaluwarsa. Silakan ulangi.'); };
    window.lunareaCaptchaError = function () { window.lunareaCaptchaUpdate(false, 'Verifikasi sedang terganggu. Periksa koneksi lalu muat ulang halaman.'); };
</script>
<?php if (trim($captchaConfig->siteKey) !== ''): ?>
<script src="https://www.google.com/recaptcha/api.js?hl=id" async defer onerror="lunareaCaptchaError()"></script>
<?php endif; ?>
<?php endif; ?>
