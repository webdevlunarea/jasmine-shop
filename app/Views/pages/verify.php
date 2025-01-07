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
        <form action="/verify" method="post" class="d-flex flex-column align-items-stretch container">
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
            <p class="m-0">Kode kadaluarsa dalam <b class="m-0" id="waktu"><?= $waktu; ?></b> detik</p>
            <div class="d-flex gap-1">
                <p class="m-0">Belum mendapatkan kode OTP?</p>
                <form action="/kirimotp" method="post" class="d-inline">
                    <button type="submit" style="color: var(--hijau); border: none; background: none;" class="link-offset-2 link-underline-opacity-25 link-underline-opacity-100-hover">Kirim ulang</button>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    const expiryTimeElm = document.getElementById("waktu");
    const de = new Date('<?= $waktuExp; ?>');
    const expireTime = de.getTime();
    const dc = new Date('<?= $waktuCurr; ?>');

    let reloadYok = false;
    setInterval(() => {
        const currTime = new Date().getTime();
        let dselisih = expireTime - currTime;
        if (dselisih <= 0 && !reloadYok) {
            reloadYok = true;
            setTimeout(() => {
                window.location.reload()
            }, 3000);
        }

        if (!reloadYok) {
            const hours = String(Math.floor(dselisih / (1000 * 60 * 60))).padStart(2, '0');
            dselisih %= (1000 * 60 * 60);
            const minutes = String(Math.floor(dselisih / (1000 * 60))).padStart(2, '0');
            dselisih %= (1000 * 60);
            const seconds = String(Math.floor(dselisih / 1000)).padStart(2, '0');

            expiryTimeElm.innerHTML = `${minutes}:${seconds}`;
            if (Number(hours) < 0 && Number(minutes) < 0 && Number(seconds) < 0) {
                window.location.reload();
            }
        }
    }, 1000);
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