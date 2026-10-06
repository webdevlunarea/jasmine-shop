<?= $this->extend('layout/template'); ?>
<?= $this->section('content'); ?>
<div class="konten auth-page">
    <div class="container">
        <nav aria-label="breadcrumb" class="auth-breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/">Beranda</a></li>
                <li class="breadcrumb-item active" aria-current="page">Daftar</li>
            </ol>
        </nav>
        <div class="auth-shell auth-shell--signup">
            <aside class="auth-visual show-ke-hide">
                <img src="/img/Login.webp" alt="Lunarea Furniture">
                <div class="auth-visual__overlay">
                    <span>Akun Lunarea</span>
                    <h2>Simpan wishlist, voucher, dan riwayat pesanan dalam satu akun.</h2>
                    <p>Daftar sekali, lalu checkout produk favorit jadi lebih cepat dan pesanan lebih mudah dipantau.</p>
                    <div class="auth-visual__points" aria-label="Keunggulan daftar akun Lunarea">
                        <div><i class="material-icons" aria-hidden="true">redeem</i><span>Benefit member</span></div>
                        <div><i class="material-icons" aria-hidden="true">favorite</i><span>Wishlist produk</span></div>
                        <div><i class="material-icons" aria-hidden="true">support_agent</i><span>Bantuan pesanan</span></div>
                    </div>
                </div>
            </aside>
            <section class="auth-panel">
                <a href="/" class="auth-logo"><img src="<?= base_url('/img/Logo Lunarea Bg Terang ukuran kecil.webp'); ?>" alt="Lunarea"></a>
                <?php if ($val['msg']) { ?>
                    <div class="alert alert-success auth-alert" role="alert"><?= $val['msg']; ?></div>
                <?php } ?>
                <div class="auth-heading">
                    <p class="auth-eyebrow">Akun baru</p>
                    <h1>Buat akun</h1>
                    <p>Buat akun untuk checkout lebih cepat, mendapatkan voucher, dan mengelola transaksi dengan mudah.</p>
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
                <div class="auth-divider"><span>atau daftar dengan email</span></div>
                <form action="/daftar" method="post" class="auth-form">
                    <?= csrf_field(); ?>
                    <?php if (!empty($redirect)) { ?>
                        <input type="hidden" name="redirect" value="<?= esc($redirect); ?>">
                    <?php } ?>
                    <div class="form-floating auth-field">
                        <input id="signup-name" type="text" class="form-control <?= ($val['val_nama']) ? "is-invalid" : ""; ?>" placeholder="Nama Lengkap" name="nama" value="<?= old('nama'); ?>" autocomplete="name" required>
                        <label for="signup-name">Nama lengkap</label>
                        <div class="invalid-feedback"><?= $val['val_nama']; ?></div>
                    </div>
                    <div class="form-floating auth-field">
                        <input id="signup-email" type="email" class="form-control <?= ($val['val_email']) ? "is-invalid" : ""; ?>" placeholder="name@example.com" name="email" value="<?= old('email'); ?>" autocomplete="email" required>
                        <label for="signup-email">Email</label>
                        <div class="invalid-feedback"><?= $val['val_email']; ?></div>
                    </div>
                    <div class="form-floating auth-field">
                        <input id="signup-password" type="password" class="form-control <?= ($val['val_sandi']) ? "is-invalid" : ""; ?>" placeholder="Password" name="sandi" value="<?= old('sandi'); ?>" autocomplete="new-password" required>
                        <label for="signup-password">Sandi</label>
                        <button class="auth-password-toggle" type="button" data-target="signup-password" aria-label="Tampilkan sandi"><i class="material-icons">visibility</i></button>
                        <div class="invalid-feedback"><?= $val['val_sandi']; ?></div>
                    </div>
                    <div class="form-floating auth-field">
                        <input id="signup-phone" type="tel" inputmode="numeric" class="form-control <?= ($val['val_nohp']) ? "is-invalid" : ""; ?>" placeholder="NoHP" name="nohp" value="<?= old('nohp'); ?>" autocomplete="tel" required>
                        <label for="signup-phone">No handphone</label>
                        <div class="invalid-feedback"><?= $val['val_nohp']; ?></div>
                    </div>
                    <label class="auth-consent" for="syarat">
                        <input type="checkbox" id="syarat" required>
                        <span>Saya menyetujui <a href="/syarat-dan-ketentuan">Syarat & Ketentuan</a> serta <a href="/kebijakan-privasi">Kebijakan Privasi</a>.</span>
                    </label>
                    <input class="btn btn-primary1 auth-submit" disabled type="submit" value="Buat Sekarang">
                </form>
                <div class="auth-trust-note">
                    <i class="material-icons" aria-hidden="true">lock</i>
                    <span>Login Google memakai autentikasi resmi Google. Satu email tetap jadi satu akun Lunarea.</span>
                </div>
                <div class="auth-switch">Sudah punya akun? <a href="/login<?= !empty($redirect) ? '?redirect=' . rawurlencode($redirect) : ''; ?>">Masuk</a></div>
            </section>
        </div>
    </div>
</div>
<script>
    const buttonSubmit = document.querySelector('.auth-submit');
    const checkboxElm = document.querySelector('#syarat');
    const inputEmailElm = document.querySelector('input[name="email"]');
    const inputNamaElm = document.querySelector('input[name="nama"]');
    const inputNohpElm = document.querySelector('input[name="nohp"]');
    const inputSandiElm = document.querySelector('input[name="sandi"]');

    function syncSignupButton() {
        buttonSubmit.disabled = !(inputEmailElm.value.trim() && inputNamaElm.value.trim() && inputNohpElm.value.trim() && inputSandiElm.value.trim() && checkboxElm.checked);
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
            button.querySelector('.material-icons').textContent = show ? 'visibility_off' : 'visibility';
        });
    });
</script>
<?= $this->endSection(); ?>
