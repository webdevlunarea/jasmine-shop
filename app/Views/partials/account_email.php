<?php $settings = $emailChangeSettings ?? []; ?>
<section id="account-email" class="account-card mb-3" aria-labelledby="account-email-title">
    <h2 id="account-email-title" class="h5 fw-bold">Keamanan email akun</h2>
    <?php if ($message = session()->getFlashdata('email_change_message')) : ?>
        <div class="alert alert-success py-2" role="status"><?= esc($message); ?></div>
    <?php endif; ?>
    <?php if ($error = session()->getFlashdata('email_change_error')) : ?>
        <div class="alert alert-danger py-2" role="alert"><?= esc($error); ?></div>
    <?php endif; ?>
    <p class="text-secondary mb-3">Email digunakan untuk masuk dan mengakses pesanan. Perubahan memerlukan sandi saat ini, kode ke email baru, dan dibatasi sekali setiap 6 bulan.</p>
    <?php if (empty($settings['available'])) : ?>
        <p class="mb-0" role="status">Pengaturan email belum tersedia. Hubungi admin untuk mengaktifkan penyimpanan keamanan akun.</p>
    <?php elseif (!empty($settings['pending_email'])) : ?>
        <div class="rounded-4 p-3" style="background:#f7fbf8;border:1px solid rgba(45,194,107,.18)">
            <p class="fw-semibold mb-1">Konfirmasi alamat email baru</p>
            <p class="small text-secondary">Kode telah dikirim ke <strong><?= esc($settings['pending_email']); ?></strong> dan berlaku selama 10 menit.</p>
            <form method="post" action="<?= esc(site_url('account/email/confirm'), 'attr'); ?>" class="row g-2">
                <?= csrf_field(); ?>
                <div class="col-md-5"><label class="form-label" for="email-confirm-password">Sandi saat ini</label><input class="form-control" id="email-confirm-password" name="current_password" type="password" autocomplete="current-password" required></div>
                <div class="col-md-4"><label class="form-label" for="email-confirm-code">Kode verifikasi</label><input class="form-control" id="email-confirm-code" name="verification_code" inputmode="numeric" pattern="[0-9]{6}" minlength="6" maxlength="6" autocomplete="one-time-code" required></div>
                <div class="col-md-3 d-flex align-items-end"><button class="btn btn-primary1 w-100" type="submit">Verifikasi email</button></div>
            </form>
            <p class="small text-secondary mt-2 mb-0">Jika kode kedaluwarsa, ajukan kode baru dari bagian penggantian email.</p>
        </div>
    <?php elseif (!empty($settings['can_change'])) : ?>
        <form method="post" action="<?= esc(site_url('account/email/request'), 'attr'); ?>" class="row g-2">
            <?= csrf_field(); ?>
            <div class="col-md-5"><label class="form-label" for="email-new-address">Alamat email baru</label><input class="form-control" id="email-new-address" name="new_email" type="email" maxlength="254" autocomplete="email" required></div>
            <div class="col-md-5"><label class="form-label" for="email-current-password">Sandi saat ini</label><input class="form-control" id="email-current-password" name="current_password" type="password" autocomplete="current-password" required></div>
            <div class="col-md-2 d-flex align-items-end"><button class="btn btn-outline-dark w-100" type="submit">Kirim kode</button></div>
        </form>
    <?php else : ?>
        <p class="mb-1">Email dapat diganti kembali mulai <strong><?= esc($settings['next_change_label'] ?? ''); ?></strong>.</p>
        <p class="small text-secondary mb-0">Email yang digunakan saat ini: <?= esc(session('email')); ?></p>
    <?php endif; ?>
</section>
