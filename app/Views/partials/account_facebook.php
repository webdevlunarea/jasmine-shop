<?php if (($facebookLinkState ?? 'disabled') !== 'disabled') : ?>
<section class="account-card account-facebook-link mb-3" aria-labelledby="facebook-link-title">
    <h2 id="facebook-link-title" class="h5">Metode masuk Facebook</h2>
    <?php if ($facebookLinkState === 'linked') : ?>
        <p class="mb-0" role="status">Facebook sudah tertaut. Login Google dengan email terverifikasi yang sama dan login email dengan sandi akun tetap memakai data belanja yang sama.</p>
    <?php elseif ($facebookLinkState === 'available') : ?>
        <p id="facebook-link-help">Tautkan Facebook dengan email yang sama agar login berikutnya tetap memakai transaksi, keranjang, dan poin akun ini. Penautan tidak mengubah sandi atau data belanja Anda.</p>
        <form method="post" action="<?= esc(site_url('auth/facebook/link'), 'attr'); ?>">
            <?= csrf_field(); ?>
            <button type="submit" class="btn btn-outline-dark" aria-describedby="facebook-link-help">Tautkan Facebook</button>
        </form>
    <?php else : ?>
        <p class="mb-0" role="status">Penautan Facebook sedang belum tersedia. Silakan hubungi admin; metode masuk sebelumnya tetap dapat dipakai.</p>
    <?php endif; ?>
</section>
<?php endif; ?>
