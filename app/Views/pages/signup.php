<?= $this->extend('layout/template'); ?>
<?= $this->section('content'); ?>
<div class="konten auth-page">
    <div class="container">
        <div class="auth-shell auth-shell--signup">
            <section class="auth-panel">
                <?php if ($val['msg']) { ?>
                    <div class="alert alert-success auth-alert" role="alert"><?= $val['msg']; ?></div>
                <?php } ?>
                <div class="auth-heading">
                    <h1>Buat akun</h1>
                    <p>Daftar untuk mulai belanja bersama Lunarea.</p>
                </div>
                <a class="auth-google-btn" href="/auth/google<?= !empty($redirect) ? '?redirect=' . rawurlencode($redirect) : ''; ?>" aria-label="Daftar dengan Google">
                    <svg class="auth-google-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                        <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                        <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                        <path fill="#FBBC05" d="M5.84 14.1c-.22-.66-.35-1.36-.35-2.1s.13-1.44.35-2.1V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l3.66-2.84z"/>
                        <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06L5.84 9.9C6.71 7.3 9.14 5.38 12 5.38z"/>
                    </svg>
                    <span>Daftar dengan Google</span>
                </a>
                <?= view('partials/auth_facebook', ['redirect' => $redirect ?? ''], ['saveData' => false]); ?>
                <div class="auth-divider"><span>atau daftar dengan email</span></div>
                <form action="/daftar" method="post" class="auth-form" data-confirm-submit data-confirm-message="Periksa kembali nama, email, nomor HP, dan data akun Anda sebelum membuat akun Lunarea.">
                    <?= csrf_field(); ?>
                    <?php if (!empty($redirect)) { ?>
                        <input type="hidden" name="redirect" value="<?= esc($redirect); ?>">
                    <?php } ?>
                    <div class="auth-field">
                        <label for="signup-name">Nama lengkap</label>
                        <input id="signup-name" type="text" class="form-control <?= ($val['val_nama']) ? "is-invalid" : ""; ?>" placeholder="Nama Lengkap" name="nama" value="<?= old('nama'); ?>" autocomplete="name" required>
                        <div class="invalid-feedback"><?= $val['val_nama']; ?></div>
                    </div>
                    <div class="auth-field">
                        <label for="signup-email">Email</label>
                        <input id="signup-email" type="email" class="form-control <?= ($val['val_email']) ? "is-invalid" : ""; ?>" placeholder="name@example.com" name="email" value="<?= old('email'); ?>" autocomplete="email" required>
                        <div class="invalid-feedback"><?= $val['val_email']; ?></div>
                    </div>
                    <div class="auth-field">
                        <label for="signup-password">Sandi</label>
                        <input id="signup-password" type="password" class="form-control <?= ($val['val_sandi']) ? "is-invalid" : ""; ?>" placeholder="Buat sandi" name="sandi" autocomplete="new-password" required>
                        <button class="auth-password-toggle" type="button" data-target="signup-password" aria-label="Tampilkan sandi" aria-pressed="false"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/><path class="auth-eye-slash d-none" d="m3 3 18 18"/></svg></button>
                        <div class="invalid-feedback"><?= $val['val_sandi']; ?></div>
                    </div>
                    <div class="auth-field">
                        <label for="signup-phone">No handphone</label>
                        <input id="signup-phone" type="tel" inputmode="tel" maxlength="24" class="form-control <?= ($val['val_nohp']) ? "is-invalid" : ""; ?>" placeholder="081234567890 atau +6281234567890" name="nohp" value="<?= old('nohp'); ?>" autocomplete="tel" aria-describedby="signup-phone-help signup-phone-error" required>
                        <small id="signup-phone-help" class="form-text text-secondary">Gunakan nomor seluler Indonesia yang aktif. Format +62 dan 08 diterima.</small>
                        <div id="signup-phone-error" class="invalid-feedback"><?= $val['val_nohp']; ?></div>
                    </div>
                    <label class="auth-consent" for="syarat">
                        <input type="checkbox" id="syarat" name="persetujuan_kebijakan" value="1" required>
                        <span>Saya menyetujui <a href="/syarat-dan-ketentuan" data-policy="terms" aria-haspopup="dialog">Syarat &amp; Ketentuan</a> serta <a href="/kebijakan-privasi" data-policy="privacy" aria-haspopup="dialog">Kebijakan Privasi</a>.</span>
                    </label>
                    <?php if (!empty($val['val_kebijakan'])) : ?><div class="signup-policy-validation" role="alert"><?= esc($val['val_kebijakan']); ?></div><?php endif; ?>
                    <p class="signup-policy-consent-help" id="signup-policy-consent-help" role="status">Buka kedua dokumen dan gulir sampai akhir sebelum kotak persetujuan dapat dicentang.</p>
                    <?= view('partials/auth_recaptcha', [], ['saveData' => false]); ?>
                    <input class="btn btn-primary1 auth-submit" disabled type="submit" value="Buat akun">
                </form>
                <div class="auth-switch">Sudah punya akun? <a href="/login<?= !empty($redirect) ? '?redirect=' . rawurlencode($redirect) : ''; ?>">Masuk</a></div>
            </section>
        </div>
    </div>
</div>
<div class="signup-policy-modal" id="signup-policy-modal" hidden>
    <section class="signup-policy-dialog" role="dialog" aria-modal="true" aria-labelledby="signup-policy-title" aria-describedby="signup-policy-instruction" tabindex="-1">
        <header class="signup-policy-header">
            <div>
                <span class="signup-policy-kicker">Informasi akun</span>
                <h2 id="signup-policy-title">Dokumen kebijakan</h2>
            </div>
            <button class="signup-policy-close" type="button" aria-label="Tutup dokumen">&times;</button>
        </header>
        <p class="signup-policy-instruction" id="signup-policy-instruction">Gulir hingga akhir dokumen untuk mengaktifkan tombol konfirmasi.</p>
        <div class="signup-policy-scroll" id="signup-policy-scroll" tabindex="0" aria-label="Isi dokumen kebijakan">
            <p class="signup-policy-loading" role="status">Memuat dokumen…</p>
        </div>
        <footer class="signup-policy-footer">
            <span class="signup-policy-progress" id="signup-policy-progress" role="status" aria-live="polite">Gulir ke bagian akhir untuk melanjutkan.</span>
            <button class="signup-policy-accept" id="signup-policy-accept" type="button" disabled>Saya sudah membaca</button>
        </footer>
    </section>
</div>
<script>
    const buttonSubmit = document.querySelector('.auth-submit');
    const checkboxElm = document.querySelector('#syarat');
    const inputEmailElm = document.querySelector('input[name="email"]');
    const inputNamaElm = document.querySelector('input[name="nama"]');
    const inputNohpElm = document.querySelector('input[name="nohp"]');
    const inputSandiElm = document.querySelector('input[name="sandi"]');
    const policyModal = document.getElementById('signup-policy-modal');
    const policyDialog = policyModal.querySelector('.signup-policy-dialog');
    const policyScroll = document.getElementById('signup-policy-scroll');
    const policyTitle = document.getElementById('signup-policy-title');
    const policyProgress = document.getElementById('signup-policy-progress');
    const policyAccept = document.getElementById('signup-policy-accept');
    const consentLabel = document.querySelector('.auth-consent');
    const consentCheckbox = document.getElementById('syarat');
    const consentHelp = document.getElementById('signup-policy-consent-help');
    const policyLinks = [...document.querySelectorAll('[data-policy]')];
    const policies = {
        terms: { title: 'Syarat & Ketentuan', url: '/syarat-dan-ketentuan', read: false },
        privacy: { title: 'Kebijakan Privasi', url: '/kebijakan-privasi', read: false },
    };
    let activePolicy = null;
    let lastPolicyTrigger = null;
    let policyLoading = false;
    let policyRequestId = 0;
    let policyHasScrolled = false;

    consentCheckbox.disabled = true;
    consentLabel.classList.add('auth-consent-locked');

    function updateSignupButton() {
        buttonSubmit.disabled = !((!document.querySelector('.auth-captcha') || window.lunareaCaptchaReady === true) && inputEmailElm.value.trim() && inputNamaElm.value.trim() && inputNohpElm.value.trim() && inputSandiElm.value.trim() && consentCheckbox.checked);
    }

    function policyReachedEnd() {
        return policyScroll.scrollTop + policyScroll.clientHeight >= policyScroll.scrollHeight - 8;
    }

    function updatePolicyProgress() {
        if (!activePolicy || policyLoading) return;
        if (policyHasScrolled && policyReachedEnd()) {
            policyAccept.disabled = false;
            policyProgress.textContent = 'Bagian akhir dokumen sudah dibaca.';
        } else {
            policyAccept.disabled = true;
            policyProgress.textContent = 'Gulir ke bagian akhir untuk mengaktifkan konfirmasi.';
        }
    }

    async function openPolicy(key, trigger) {
        const policy = policies[key];
        if (!policy) return;
        const requestId = ++policyRequestId;
        activePolicy = policy;
        lastPolicyTrigger = trigger;
        policyTitle.textContent = policy.title;
        policyModal.hidden = false;
        document.body.classList.add('signup-policy-open');
        policyDialog.focus();
        policyScroll.innerHTML = '<p class="signup-policy-loading" role="status">Memuat dokumen…</p>';
        policyScroll.scrollTop = 0;
        policyHasScrolled = false;
        policyAccept.disabled = true;
        policyLoading = true;
        policyProgress.textContent = 'Dokumen sedang dimuat.';
        try {
            const response = await fetch(policy.url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' });
            if (!response.ok) throw new Error('Request failed');
            const parsed = new DOMParser().parseFromString(await response.text(), 'text/html');
            if (requestId !== policyRequestId || policyModal.hidden) return;
            const article = parsed.querySelector('.buyer-help-content');
            if (!article) throw new Error('Document content missing');
            const safeContent = article.cloneNode(true);
            safeContent.querySelectorAll('script, iframe, object, embed, form').forEach(node => node.remove());
            safeContent.querySelectorAll('*').forEach(node => [...node.attributes].forEach(attribute => {
                if (/^on/i.test(attribute.name) || ((attribute.name === 'href' || attribute.name === 'src') && /^\s*javascript:/i.test(attribute.value))) node.removeAttribute(attribute.name);
            }));
            policyScroll.replaceChildren(safeContent);
            policyScroll.scrollTop = 0;
            policyLoading = false;
            policyProgress.textContent = 'Gulir ke bagian akhir untuk mengaktifkan konfirmasi.';
            updatePolicyProgress();
        } catch (error) {
            if (requestId !== policyRequestId || policyModal.hidden) return;
            policyLoading = false;
            policyScroll.innerHTML = '<p class="signup-policy-error">Dokumen gagal dimuat. Periksa koneksi, lalu muat ulang dokumen.</p>';
            const retry = document.createElement('button');
            retry.type = 'button';
            retry.className = 'signup-policy-retry';
            retry.textContent = 'Muat ulang dokumen';
            retry.addEventListener('click', () => openPolicy(key, trigger));
            policyScroll.append(retry);
            policyProgress.textContent = 'Dokumen perlu dimuat sebelum persetujuan dapat diberikan.';
        }
    }

    function closePolicy() {
        policyRequestId++;
        policyModal.hidden = true;
        document.body.classList.remove('signup-policy-open');
        activePolicy = null;
        policyLoading = false;
        if (lastPolicyTrigger) lastPolicyTrigger.focus();
    }

    policyLinks.forEach(link => link.addEventListener('click', event => {
        event.preventDefault();
        openPolicy(link.dataset.policy, link);
    }));
    policyScroll.addEventListener('scroll', () => {
        if (policyScroll.scrollTop > 0) policyHasScrolled = true;
        updatePolicyProgress();
    }, { passive: true });
    policyModal.querySelector('.signup-policy-close').addEventListener('click', closePolicy);
    policyModal.addEventListener('click', event => { if (event.target === policyModal) closePolicy(); });
    document.addEventListener('keydown', event => {
        if (policyModal.hidden) return;
        if (event.key === 'Escape') { closePolicy(); return; }
        if (event.key === 'Tab') {
            const focusable = [...policyDialog.querySelectorAll('button:not(:disabled), a[href], [tabindex="0"]')];
            const first = focusable[0], last = focusable[focusable.length - 1];
            if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last.focus(); }
            else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first.focus(); }
        }
    });
    policyAccept.addEventListener('click', () => {
        if (!activePolicy || policyLoading || !policyHasScrolled || !policyReachedEnd()) return;
        activePolicy.read = true;
        const link = policyLinks.find(item => item.dataset.policy === (activePolicy === policies.terms ? 'terms' : 'privacy'));
        if (link) link.setAttribute('aria-label', `${activePolicy.title}, sudah dibaca`);
        const allRead = policies.terms.read && policies.privacy.read;
        consentCheckbox.disabled = !allRead;
        consentLabel.classList.toggle('auth-consent-locked', !allRead);
        if (allRead) {
            consentLabel.querySelector('span').setAttribute('data-policy-read', 'true');
            consentHelp.textContent = 'Kedua dokumen sudah dibaca. Centang kotak untuk menyetujui.';
            consentHelp.classList.add('is-complete');
            policyProgress.textContent = 'Kedua dokumen sudah dibaca. Anda dapat menyetujui di formulir pendaftaran.';
        }
        closePolicy();
    });

    document.addEventListener('lunarea:captcha', syncSignupButton);

    function syncSignupButton() {
        updateSignupButton();
    }

    [checkboxElm, inputEmailElm, inputNamaElm, inputNohpElm, inputSandiElm].forEach((el) => {
        el.addEventListener('input', syncSignupButton);
        el.addEventListener('change', syncSignupButton);
    });
    syncSignupButton();

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
