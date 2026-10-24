<section id="account-security" class="account-card mb-3" aria-labelledby="account-security-title">
    <h2 id="account-security-title" class="h5 fw-bold">Keamanan akun</h2>
    <?php if ($message = session()->getFlashdata('customer_password_message')) { ?>
        <div class="alert alert-success py-2" role="status"><?= esc($message); ?></div>
    <?php } ?>
    <?php if ($error = session()->getFlashdata('customer_password_error')) { ?>
        <div class="alert alert-danger py-2" role="alert"><?= esc($error); ?></div>
    <?php } ?>
    <p class="text-secondary mb-3">Konfirmasi dengan sandi saat ini. Perangkat lain akan keluar setelah sandi diganti.</p>
    <form method="post" action="<?= esc(site_url('account/password/change'), 'attr'); ?>" class="row g-2" autocomplete="on">
        <?= csrf_field(); ?>
        <div class="col-md-4">
            <label class="form-label" for="customer-current-password">Sandi saat ini</label>
            <input class="form-control" id="customer-current-password" name="current_password" type="password" autocomplete="current-password" required maxlength="4096">
        </div>
        <div class="col-md-4">
            <label class="form-label" for="customer-new-password">Sandi baru</label>
            <input class="form-control" id="customer-new-password" name="password" type="password" autocomplete="new-password" minlength="8" maxlength="72" required>
        </div>
        <div class="col-md-4">
            <label class="form-label" for="customer-confirm-password">Ulangi sandi baru</label>
            <input class="form-control" id="customer-confirm-password" name="password_confirmation" type="password" autocomplete="new-password" minlength="8" maxlength="72" required>
        </div>
        <div class="col-12">
            <p class="form-text mb-2">Minimal 8 karakter dan harus mengandung angka.</p>
            <button type="submit" class="btn btn-outline-success">Perbarui sandi</button>
        </div>
    </form>
</section>
