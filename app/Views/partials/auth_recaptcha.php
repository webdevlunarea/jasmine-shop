<?php $captchaConfig = $captchaConfig ?? config(\Config\Recaptcha::class); ?>
<?php if ($captchaConfig->enabled): ?>
<div class="auth-captcha" role="group" aria-label="Verifikasi keamanan" data-state="loading">
    <div class="auth-captcha-slot" data-sitekey="<?= esc($captchaConfig->siteKey, 'attr'); ?>"></div>
    <p class="auth-captcha-status" role="status" aria-live="polite" aria-atomic="true">Memuat verifikasi…</p>
    <noscript><p>Aktifkan JavaScript untuk menjalankan verifikasi keamanan.</p></noscript>
</div>
<script>
    window.lunareaCaptchaReady = false;
    window.lunareaCaptchaUpdate = function (ready, message, state) {
        const group = document.querySelector('.auth-captcha');
        window.lunareaCaptchaReady = ready;
        group.dataset.state = state || (ready ? 'verified' : 'idle');
        group.querySelector('.auth-captcha-status').textContent = message;
        document.dispatchEvent(new Event('lunarea:captcha'));
    };
    window.lunareaCaptchaSuccess = function () { window.lunareaCaptchaUpdate(true, 'Verifikasi selesai.'); };
    window.lunareaCaptchaExpired = function () { window.lunareaCaptchaUpdate(false, 'Verifikasi kedaluwarsa. Silakan ulangi.', 'error'); };
    window.lunareaCaptchaError = function () { window.lunareaCaptchaUpdate(false, 'Periksa koneksi, lalu muat ulang verifikasi.', 'error'); };
    window.lunareaCaptchaLoaded = function () {
        const slot = document.querySelector('.auth-captcha-slot');
        if (!slot || !slot.dataset.sitekey) { window.lunareaCaptchaError(); return; }
        let widgetId = null;
        let currentSize = '';
        function renderCaptcha() {
            // Official Google sizes only: never scale, crop, stretch or hide iframe branding.
            const size = slot.clientWidth >= 304 ? 'normal' : 'compact';
            if (currentSize === size) return;
            if (widgetId !== null) window.grecaptcha.reset(widgetId);
            window.lunareaCaptchaUpdate(false, '');
            currentSize = size;
            slot.dataset.size = size;
            const widget = document.createElement('div');
            widget.className = 'g-recaptcha';
            slot.replaceChildren(widget);
            try {
                widgetId = window.grecaptcha.render(widget, {
                    sitekey: slot.dataset.sitekey, size: size, theme: 'light',
                    callback: window.lunareaCaptchaSuccess,
                    'expired-callback': window.lunareaCaptchaExpired,
                    'error-callback': window.lunareaCaptchaError
                });
            } catch (error) {
                currentSize = '';
                window.lunareaCaptchaError();
            }
        }
        renderCaptcha();
        // Re-render only when crossing a size boundary, not on every resize/input.
        if (window.ResizeObserver) {
            const observer = new ResizeObserver(renderCaptcha);
            observer.observe(slot);
        } else {
            window.addEventListener('resize', renderCaptcha);
        }
    };
</script>
<?php if (trim($captchaConfig->siteKey) !== ''): ?>
<script src="https://www.google.com/recaptcha/api.js?onload=lunareaCaptchaLoaded&amp;render=explicit&amp;hl=id" async defer onerror="lunareaCaptchaError()"></script>
<?php else: ?>
<script>window.lunareaCaptchaUpdate(false, 'Verifikasi sedang tidak tersedia. Silakan coba lagi nanti.', 'error');</script>
<?php endif; ?>
<?php endif; ?>
