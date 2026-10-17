<?php $settings = $usernameSettings ?? ['available' => false, 'username' => '']; ?>
<section id="account-username" class="account-card mb-3" aria-labelledby="account-username-title">
    <h2 id="account-username-title" class="h5">Username untuk masuk</h2>
    <?php if ($message = session()->getFlashdata('username_message')) : ?>
        <p role="status"><?= esc($message); ?></p>
    <?php endif; ?>
    <?php if ($error = session()->getFlashdata('username_error')) : ?>
        <p class="text-danger" role="alert"><?= esc($error); ?></p>
    <?php endif; ?>
    <?php if ($settings['available']) : ?>
        <p id="account-username-help">Username unik, 3–30 karakter: diawali huruf, diikuti huruf, angka, atau garis bawah. Huruf besar/kecil dianggap sama. Username tetap memerlukan sandi akun; pengguna Google/Facebook dapat mengatur sandi melalui Edit profil.</p>
        <form method="post" action="<?= esc(site_url('account/username'), 'attr'); ?>">
            <?= csrf_field(); ?>
            <label for="account-username-input" class="form-label">Username<?= $settings['username'] === '' ? ' baru' : ''; ?></label>
            <input id="account-username-input" class="form-control mb-2" type="text" name="username" value="<?= esc($settings['username'], 'attr'); ?>" minlength="3" maxlength="30" pattern="[A-Za-z][A-Za-z0-9_]{2,29}" autocomplete="username" autocapitalize="none" spellcheck="false" aria-describedby="account-username-help" required>
            <button class="btn btn-outline-dark" type="submit"><?= $settings['username'] === '' ? 'Simpan username' : 'Ubah username'; ?></button>
        </form>
        <?php if ($settings['username'] !== '') : ?><p class="small text-secondary mt-2 mb-0">Jika username diganti, username lama tersedia kembali untuk orang lain. Akun dan data belanja Anda tetap sama.</p><?php endif; ?>
    <?php else : ?>
        <p class="mb-0" role="status">Pengaturan username belum tersedia. Login melalui email, Google, atau Facebook tetap dapat dipakai. Hubungi admin untuk mengaktifkan fitur ini.</p>
    <?php endif; ?>
</section>
