<?= $this->extend('layout/template'); ?>
<?= $this->section('content'); ?>
<div class="konten d-flex justify-content-center align-items-center">
    <div>
        <h1 class="text-center">Verifikasi</h1>
        <?php if ($val['msg']) { ?>
            <div class="container">
                <div class="alert alert-success text-center" style="max-width: 400px;" role="alert">
                    <?= $val['msg']; ?>
                </div>
            </div>
        <?php } ?>
        <p class="text-center">Cek email & masukkan kode OTP</p>
        <form action="/verify" method="post" class="d-flex flex-column align-items-stretch container" data-confirm-submit data-confirm-message="Kirim kode verifikasi untuk memeriksa akun Anda?">
            <?= csrf_field(); ?>
            <div class="d-flex gap-1 mb-2" style="max-width: 400px">
                <input oninput="handleChange('otp2', '', event)" style="flex: 1;" maxlength="1" type="text" autofocus class="text-center form-control py-3 fw-bold" name="otp1">
                <input oninput="handleChange('otp3', 'otp1', event)" style="flex: 1;" maxlength="1" type="text" class="text-center form-control py-3 fw-bold" name="otp2">
                <input oninput="handleChange('otp4', 'otp2', event)" style="flex: 1;" maxlength="1" type="text" class="text-center form-control py-3 fw-bold" name="otp3">
                <input oninput="handleChange('otp5', 'otp3', event)" style="flex: 1;" maxlength="1" type="text" class="text-center form-control py-3 fw-bold" name="otp4">
                <input oninput="handleChange('otp6', 'otp4', event)" style="flex: 1;" maxlength="1" type="text" class="text-center form-control py-3 fw-bold" name="otp5">
                <input style="flex: 1;" maxlength="1" type="text" class="text-center form-control py-3 fw-bold" name="otp6">
            </div>
            <input class="btn btn-primary1" type="submit" value="Verifikasi">
        </form>
        <div class="container mt-3 d-flex flex-column align-items">
            <p class="m-0">Kode kedaluwarsa dalam <b class="m-0" id="waktu" role="timer" aria-live="off"><?= esc($waktu); ?></b></p>
            <p class="m-0 text-secondary" id="verify-expired-message" role="status" <?= empty($expired) ? 'hidden' : ''; ?>>Kode sudah kedaluwarsa. Kirim ulang hanya jika Anda ingin menerima kode baru.</p>
            <div class="d-flex gap-1">
                <p class="m-0">Belum mendapatkan kode OTP?</p>
                <form action="/kirimotp" method="post" class="d-inline" data-confirm-submit data-confirm-message="Kirim ulang kode verifikasi ke email akun Anda?" data-resend-available-at="<?= (int) ($resendAvailableAt ?? 0); ?>">
                    <?= csrf_field(); ?>
                    <button type="submit" data-verification-resend <?= !empty($resendWaitSeconds) ? 'disabled' : ''; ?> style="color: var(--hijau); border: none; background: none;" class="link-offset-2 link-underline-opacity-25 link-underline-opacity-100-hover">
                        <?= !empty($resendWaitSeconds) ? 'Kirim ulang dalam ' . gmdate('i:s', (int) $resendWaitSeconds) : 'Kirim ulang'; ?>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    const expiryTimeElm = document.getElementById("waktu");
    const de = new Date('<?= $waktuExp; ?>');
    const expireTime = de.getTime();
    const expiredMessage = document.getElementById('verify-expired-message');
    const resendForm = document.querySelector('[data-resend-available-at]');
    const resendButton = resendForm?.querySelector('[data-verification-resend]');
    const resendAvailableAt = Number(resendForm?.dataset.resendAvailableAt || 0) * 1000;
    let countdown;
    let resendCountdown;
    const updateExpiryCountdown = () => {
        const currTime = new Date().getTime();
        let dselisih = expireTime - currTime;
        if (dselisih <= 0) {
            expiryTimeElm.textContent = '00:00';
            expiredMessage.hidden = false;
            if (countdown) clearInterval(countdown);
            return;
        }

        const minutes = String(Math.floor(dselisih / 60000)).padStart(2, '0');
        dselisih %= 60000;
        const seconds = String(Math.floor(dselisih / 1000)).padStart(2, '0');
        expiryTimeElm.textContent = `${minutes}:${seconds}`;
    };
    if (expireTime <= Date.now()) {
        expiryTimeElm.textContent = '00:00';
        expiredMessage.hidden = false;
    } else {
        updateExpiryCountdown();
        countdown = setInterval(updateExpiryCountdown, 1000);
    }
    const updateResendAvailability = () => {
        if (!resendButton) return;
        const secondsLeft = Math.max(0, Math.ceil((resendAvailableAt - Date.now()) / 1000));
        resendButton.disabled = secondsLeft > 0;
        resendButton.textContent = secondsLeft > 0
            ? `Kirim ulang dalam ${String(Math.floor(secondsLeft / 60)).padStart(2, '0')}:${String(secondsLeft % 60).padStart(2, '0')}`
            : 'Kirim ulang';
        if (secondsLeft <= 0 && resendCountdown) clearInterval(resendCountdown);
    };
    updateResendAvailability();
    if (resendButton?.disabled) resendCountdown = setInterval(updateResendAvailability, 1000);
</script>
<script>
    function handleChange(next, prev, e) {
        const inputNextElm = document.querySelector('input[name="' + next + '"]')
        if (e.target.value) {
            inputNextElm.value = ''
            inputNextElm.focus()
        }
    }
</script>
<?= $this->endSection(); ?>
