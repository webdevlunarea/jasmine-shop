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
                    <h2>Simpan wishlist dan pantau pesanan dalam satu akun.</h2>
                    <p>Daftar sekali, lalu checkout produk favorit jadi lebih cepat.</p>
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
                    <p>Isi data di bawah untuk mulai belanja dan mendapatkan akses transaksi member.</p>
                </div>
                <form action="/daftar" method="post" class="auth-form">
                    <?= csrf_field(); ?>
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
                <div class="auth-switch">Sudah punya akun? <a href="/login">Masuk</a></div>
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
