<?= $this->extend('layout/template'); ?>
<?= $this->section('content'); ?>
<main class="konten admin-module-page">
    <div class="container" style="max-width: 920px;">
        <?= view('partials/admin_page_heading', [
            'adminHeadingTitle' => 'Tes notifikasi',
            'adminHeadingGroup' => 'Pengaturan',
            'adminHeadingDescription' => 'Kirim satu notifikasi percobaan ke perangkat Android yang sudah terdaftar.',
        ], ['saveData' => false]); ?>

        <?php if (!empty($msg)) { ?>
            <div class="alert alert-info rounded-3" role="status"><?= esc($msg); ?></div>
        <?php } ?>

        <section class="card border-0 shadow-sm rounded-4 p-3 p-md-4">
            <div class="d-flex align-items-start gap-3 mb-4">
                <span class="d-inline-flex align-items-center justify-content-center rounded-circle flex-shrink-0" style="width:48px;height:48px;background:#edf7ee;color:#028a0b;">
                    <i class="material-icons" aria-hidden="true">notifications_active</i>
                </span>
                <div>
                    <h2 class="h5 fw-bold mb-1">Kirim notifikasi percobaan</h2>
                    <p class="text-secondary mb-0">Gunakan email akun pelanggan yang dipakai untuk masuk ke aplikasi Lunarea.</p>
                </div>
            </div>

            <div class="alert alert-warning rounded-3" role="note">
                Notifikasi ini benar-benar dikirim ke perangkat. Pastikan akun sudah masuk di aplikasi, izin notifikasi aktif, dan perangkat terdaftar. Maksimal lima perangkat Android aktif akan menerima tes.
            </div>

            <form method="post" action="/notificationtestadmin/send" onsubmit="return confirm('Kirim notifikasi tes ke perangkat Android untuk email ini?');">
                <?= csrf_field(); ?>
                <div class="row g-3 align-items-end">
                    <div class="col-12 col-md-8">
                        <label class="form-label fw-semibold" for="push-test-email">Email akun pelanggan</label>
                        <input class="form-control" id="push-test-email" name="email" type="email" maxlength="254" autocomplete="email" value="<?= esc($email ?? '', 'attr'); ?>" placeholder="nama@email.com" required>
                        <div class="form-text">Hanya perangkat aktif milik akun tersebut yang menerima notifikasi.</div>
                    </div>
                    <div class="col-12 col-md-4">
                        <button class="btn btn-primary1 w-100" type="submit">
                            <i class="material-icons align-middle me-1" aria-hidden="true">send</i>Kirim tes
                        </button>
                    </div>
                </div>
            </form>
        </section>
    </div>
</main>
<?= $this->endSection(); ?>
