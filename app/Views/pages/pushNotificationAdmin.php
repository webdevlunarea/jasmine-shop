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

        <section class="card border-0 shadow-sm rounded-4 p-3 p-md-4 mt-4">
            <div class="d-flex align-items-start gap-3 mb-3">
                <span class="d-inline-flex align-items-center justify-content-center rounded-circle flex-shrink-0" style="width:48px;height:48px;background:#f5f3ee;color:#2f2d20;">
                    <i class="material-icons" aria-hidden="true">campaign</i>
                </span>
                <div>
                    <h2 class="h5 fw-bold mb-1">Broadcast untuk pengguna Android</h2>
                    <p class="text-secondary mb-0">Sampaikan kabar voucher, Flash Sale, atau acara Lunarea kepada pelanggan yang mengaktifkan notifikasi.</p>
                </div>
            </div>

            <?php if (!empty($broadcastMsg)) { ?>
                <div class="alert alert-info rounded-3" role="status"><?= esc($broadcastMsg); ?></div>
            <?php } ?>

            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 rounded-3 p-3 mb-3" style="background:#faf9f6;">
                <span class="text-secondary">Akun dengan perangkat Android aktif</span>
                <strong class="fs-5" style="color:#2f2d20;"><?= number_format((int) ($broadcastRecipientCount ?? 0), 0, ',', '.'); ?></strong>
            </div>

            <div class="alert alert-light border rounded-3" role="note">
                Pesan hanya masuk antrean untuk akun dengan perangkat Android aktif. Tidak ada pesan yang dikirim ke perangkat yang belum terdaftar. Worker notifikasi perlu dijalankan terjadwal agar antrean dikirim bertahap.
            </div>

            <form method="post" action="/notificationtestadmin/broadcast" enctype="multipart/form-data" data-confirm-submit data-confirm-message="Jadwalkan broadcast ke <?= (int) ($broadcastRecipientCount ?? 0); ?> akun Android aktif? Pastikan isi pesan dan promonya sudah benar.">
                <?= csrf_field(); ?>
                <div class="row g-3">
                    <div class="col-12 col-md-5">
                        <label class="form-label fw-semibold" for="broadcast-kind">Jenis informasi</label>
                        <select class="form-select" id="broadcast-kind" name="kind" required>
                            <option value="voucher">Voucher</option>
                            <option value="flash_sale">Flash Sale</option>
                            <option value="event">Acara / event</option>
                            <option value="general">Informasi lainnya</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold" for="broadcast-title">Judul notifikasi</label>
                        <input class="form-control" id="broadcast-title" name="title" maxlength="100" required placeholder="Contoh: Voucher spesial untuk Anda">
                        <div class="form-text">Maksimal 100 karakter.</div>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold" for="broadcast-body">Isi pesan</label>
                        <textarea class="form-control" id="broadcast-body" name="body" rows="3" maxlength="240" required placeholder="Tulis informasi singkat, jelas, dan sesuai promo yang sedang berlangsung."></textarea>
                        <div class="form-text">Maksimal 240 karakter. Hindari mencantumkan data pribadi atau informasi yang belum berlaku.</div>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold" for="broadcast-image-size">Ukuran gambar notifikasi</label>
                        <select class="form-select" id="broadcast-image-size" name="image_size" required>
                            <option value="800x400" selected>800 × 400 px — Banner kecil, 2:1</option>
                            <option value="1024x512">1024 × 512 px — Lebih tajam, 2:1</option>
                        </select>
                        <div class="form-text">Pilih ukuran yang sama dengan dimensi file gambar yang akan diunggah.</div>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold" for="broadcast-image">Gambar notifikasi <span class="text-secondary fw-normal">(opsional)</span></label>
                        <input class="form-control" id="broadcast-image" name="image" type="file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                        <div class="form-text">Boleh dikosongkan untuk mengirim notifikasi tanpa gambar. Jika diisi: JPG, PNG, atau WebP, kurang dari 512 KB, dan dimensinya harus sama dengan pilihan di atas.</div>
                        <div id="broadcast-image-error" class="small text-danger mt-1" role="alert" hidden></div>
                        <div class="mt-3 p-3 rounded-3" style="max-width:400px;background:#f5f3ee;">
                            <div class="small fw-semibold text-secondary mb-2">Pratinjau notifikasi Android · tampilan diperluas</div>
                            <div class="border rounded-3 p-3 bg-white" style="box-shadow:0 1px 4px rgba(30,35,30,.08);">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <span class="d-inline-flex align-items-center justify-content-center rounded-circle flex-shrink-0" style="width:25px;height:25px;background:#03ac0e;color:#fff;">
                                        <i class="material-icons" aria-hidden="true" style="font-size:16px;">notifications</i>
                                    </span>
                                    <span class="small text-secondary">Lunarea · sekarang</span>
                                </div>
                                <div id="broadcast-preview-title" class="fw-semibold text-dark text-break">Judul notifikasi</div>
                                <div id="broadcast-preview-body" class="small text-secondary text-break mt-1">Isi pesan akan terlihat seperti ini.</div>
                                <div id="broadcast-preview-image-frame" class="mt-2 rounded-2 overflow-hidden" style="display:none;width:100%;max-width:340px;aspect-ratio:2/1;background:#f5f3ee;">
                                    <img id="broadcast-image-preview" alt="Pratinjau gambar pada notifikasi Android" style="display:block;width:100%;height:100%;object-fit:cover;">
                                </div>
                            </div>
                            <div id="broadcast-image-meta" class="small text-secondary mt-2" aria-live="polite">Gambar yang dipilih akan dibatasi agar tampil proporsional dan tidak memenuhi layar.</div>
                        </div>
                    </div>
                    <div class="col-12 d-flex justify-content-end">
                        <button id="broadcast-submit" class="btn btn-primary1 px-4" type="submit" <?= (int) ($broadcastRecipientCount ?? 0) < 1 ? 'disabled' : ''; ?>>
                            <i class="material-icons align-middle me-1" aria-hidden="true">send</i>Jadwalkan broadcast
                        </button>
                    </div>
                </div>
            </form>
        </section>
    </div>
</main>
<script>
(() => {
    const input = document.getElementById('broadcast-image');
    const error = document.getElementById('broadcast-image-error');
    const preview = document.getElementById('broadcast-image-preview');
    const previewFrame = document.getElementById('broadcast-preview-image-frame');
    const previewTitle = document.getElementById('broadcast-preview-title');
    const previewBody = document.getElementById('broadcast-preview-body');
    const imageMeta = document.getElementById('broadcast-image-meta');
    const imageSizeSelect = document.getElementById('broadcast-image-size');
    const titleInput = document.getElementById('broadcast-title');
    const bodyInput = document.getElementById('broadcast-body');
    const submit = document.getElementById('broadcast-submit');
    if (!input || !error || !preview || !previewFrame || !submit) return;
    let previewUrl = '';
    const updateCopyPreview = () => {
        if (previewTitle) previewTitle.textContent = titleInput?.value.trim() || 'Judul notifikasi';
        if (previewBody) previewBody.textContent = bodyInput?.value.trim() || 'Isi pesan akan terlihat seperti ini.';
    };
    titleInput?.addEventListener('input', updateCopyPreview);
    bodyInput?.addEventListener('input', updateCopyPreview);
    input.addEventListener('change', () => {
        const file = input.files && input.files[0];
        error.hidden = true;
        error.textContent = '';
        previewFrame.style.display = 'none';
        if (previewUrl) URL.revokeObjectURL(previewUrl);
        previewUrl = '';
        if (!file) {
            if (imageMeta) imageMeta.textContent = 'Gambar yang dipilih akan dibatasi agar tampil proporsional dan tidak memenuhi layar.';
            submit.disabled = <?= (int) ($broadcastRecipientCount ?? 0) < 1 ? 'true' : 'false'; ?>;
            return;
        }
        const allowed = ['image/jpeg', 'image/png', 'image/webp'];
        if (file.size >= 512 * 1024 || !allowed.includes(file.type)) {
            error.textContent = file.size >= 512 * 1024
                ? 'Ukuran gambar harus kurang dari 512 KB.'
                : 'Pilih gambar JPG, PNG, atau WebP.';
            error.hidden = false;
            input.value = '';
            submit.disabled = true;
            return;
        }
        submit.disabled = <?= (int) ($broadcastRecipientCount ?? 0) < 1 ? 'true' : 'false'; ?>;
        previewUrl = URL.createObjectURL(file);
        previewFrame.style.display = 'block';
        preview.onload = () => {
            const selectedSize = imageSizeSelect?.value || '800x400';
            const [targetWidth, targetHeight] = selectedSize.split('x').map(Number);
            const wrongDimensions = preview.naturalWidth !== targetWidth || preview.naturalHeight !== targetHeight;
            if (imageMeta) {
                const sizeKb = (file.size / 1024).toFixed(0);
                imageMeta.textContent = `${sizeKb} KB · ${preview.naturalWidth} × ${preview.naturalHeight} px · pratinjau dibatasi 340 × 170 px.`;
            }
            if (wrongDimensions) {
                error.textContent = `Dimensi file harus tepat ${targetWidth} × ${targetHeight} px sesuai ukuran yang dipilih.`;
                error.hidden = false;
                submit.disabled = true;
            }
        };
        preview.src = previewUrl;
    });
    imageSizeSelect?.addEventListener('change', () => {
        const file = input.files && input.files[0];
        if (!file || !preview.naturalWidth) return;
        const [targetWidth, targetHeight] = (imageSizeSelect.value || '800x400').split('x').map(Number);
        const wrongDimensions = preview.naturalWidth !== targetWidth || preview.naturalHeight !== targetHeight;
        error.textContent = wrongDimensions ? `Dimensi file harus tepat ${targetWidth} × ${targetHeight} px sesuai ukuran yang dipilih.` : '';
        error.hidden = !wrongDimensions;
        submit.disabled = wrongDimensions || <?= (int) ($broadcastRecipientCount ?? 0) < 1 ? 'true' : 'false'; ?>;
    });
    updateCopyPreview();
})();
</script>
<?= $this->endSection(); ?>
