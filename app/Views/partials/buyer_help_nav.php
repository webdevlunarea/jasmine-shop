<?php
$helpLinks = [
    'tracking' => ['/lacak-pengiriman', 'Lacak pengiriman'],
    'faq' => ['/faq', 'FAQ'],
    'terms' => ['/syarat-dan-ketentuan', 'Syarat & ketentuan'],
    'privacy' => ['/kebijakan-privasi', 'Kebijakan privasi'],
];
?>
<nav class="buyer-help-nav" aria-label="Bantuan dan informasi">
    <?php foreach ($helpLinks as $key => [$url, $label]) { ?>
        <a href="<?= esc($url, 'attr'); ?>" <?= ($activeHelp ?? '') === $key ? 'aria-current="page"' : ''; ?>><?= esc($label); ?></a>
    <?php } ?>
</nav>
