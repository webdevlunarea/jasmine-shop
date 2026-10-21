<?php
$footerVariant = in_array($footerVariant ?? '', ['full', 'compact', 'minimal'], true) ? $footerVariant : 'full';
$footerPayment = !empty($footerPayment);
?>
<?php if ($footerVariant === 'full') { ?>
    <?= view('layout/footer', [], ['saveData' => false]); ?>
<?php } else { ?>
<footer class="buyer-footer buyer-footer--<?= esc($footerVariant); ?> d-print-none" aria-label="Informasi dan bantuan Lunarea">
    <div class="container">
        <?php if ($footerVariant === 'compact') { ?>
        <div class="buyer-footer__top">
            <div class="buyer-footer__identity">
                <p class="buyer-footer__brand">Lunarea</p>
                <p>Butuh bantuan? Tim kami siap membantu Anda.</p>
            </div>
            <nav class="buyer-footer__help" aria-label="Bantuan pelanggan">
                <a href="https://api.whatsapp.com/send?phone=<?= esc(LUNAREA_CS_WHATSAPP_E164, 'attr'); ?>" target="_blank" rel="noopener noreferrer">Layanan Pelanggan</a>
                <a href="https://api.whatsapp.com/send?phone=<?= esc(LUNAREA_RETURN_WHATSAPP_E164, 'attr'); ?>" target="_blank" rel="noopener noreferrer">Retur / Komplain</a>
                <a href="/lacak-pengiriman">Lacak Pengiriman</a>
            </nav>
        </div>
        <?php } elseif ($footerPayment) { ?>
        <p class="buyer-footer__payment-note">Periksa rincian pesanan sebelum menyelesaikan pembayaran.</p>
        <?php } ?>
        <div class="buyer-footer__bottom">
            <p class="buyer-footer__copyright">&copy; <?= date('Y'); ?> Lunarea Furniture. Hak cipta dilindungi.</p>
            <nav class="buyer-footer__legal" aria-label="Kebijakan dan bantuan">
                <?php if ($footerVariant === 'minimal') { ?>
                <a href="https://api.whatsapp.com/send?phone=<?= esc(LUNAREA_CS_WHATSAPP_E164, 'attr'); ?>" target="_blank" rel="noopener noreferrer"><?= $footerPayment ? 'Bantuan pembayaran' : 'Bantuan'; ?></a>
                <?php } else { ?>
                <a href="/faq">Pertanyaan Umum</a>
                <?php } ?>
                <a href="/kebijakan-privasi"<?= $footerPayment ? ' target="_blank" rel="noopener noreferrer"' : ''; ?>>Kebijakan Privasi</a>
                <button type="button" class="cookie-preferences-link" data-cookie-settings-open>Pengaturan cookie</button>
                <a href="/syarat-dan-ketentuan"<?= $footerPayment ? ' target="_blank" rel="noopener noreferrer"' : ''; ?>>Syarat &amp; Ketentuan</a>
            </nav>
        </div>
    </div>
</footer>
<?php } ?>
