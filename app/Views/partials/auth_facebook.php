<?php if (config(\Config\Facebook::class)->ready()) : ?>
    <a class="auth-facebook-btn" href="<?= esc(site_url('auth/facebook') . (!empty($redirect) ? '?redirect=' . rawurlencode($redirect) : ''), 'attr'); ?>">
        <svg class="auth-facebook-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path fill="currentColor" d="M24 12a12 12 0 1 0-13.875 11.854v-8.385H7.078V12h3.047V9.356c0-3.007 1.792-4.669 4.533-4.669 1.313 0 2.686.235 2.686.235v2.953h-1.513c-1.491 0-1.956.925-1.956 1.874V12h3.328l-.532 3.469h-2.796v8.385A12.003 12.003 0 0 0 24 12Z"/></svg>
        <span>Masuk dengan Facebook</span>
    </a>
<?php endif; ?>
