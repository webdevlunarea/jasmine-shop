<?php
$activeAccountMenu = $activeAccountMenu ?? '';
$accountMenu = [
    ['key' => 'account', 'label' => 'Profilku', 'url' => '/account', 'icon' => 'person_outline'],
    ['key' => 'notifications', 'label' => 'Pemberitahuan', 'url' => '/notifications', 'icon' => 'notifications_none'],
    ['key' => 'transaction', 'label' => 'Transaksi', 'url' => '/transaction', 'icon' => 'receipt_long'],
    ['key' => 'point', 'label' => 'Luna Poin', 'url' => '/point', 'icon' => 'stars'],
    ['key' => 'voucher', 'label' => 'Voucher', 'url' => '/voucher', 'icon' => 'local_offer'],
    ['key' => 'wishlist', 'label' => 'Daftar Favorit', 'url' => '/wishlist', 'icon' => 'favorite_border'],
    ['key' => 'cart', 'label' => 'Keranjang', 'url' => '/cart', 'icon' => 'shopping_cart'],
];
$accountIconPaths = [
    'account' => 'M20 21v-2a7 7 0 0 0-14 0v2M16 7a4 4 0 1 1-8 0 4 4 0 0 1 8 0Z',
    'notifications' => 'M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4',
    'transaction' => 'M6 3h12v18l-3-2-3 2-3-2-3 2V3ZM9 8h6M9 12h6',
    'point' => 'm12 3 2.8 5.7 6.2.9-4.5 4.4 1.1 6.2-5.6-3-5.6 3 1.1-6.2L3 9.6l6.2-.9L12 3Z',
    'voucher' => 'M3 5h18v5a2 2 0 0 0 0 4v5H3v-5a2 2 0 0 0 0-4V5ZM15 5v3M15 11v2M15 16v3',
    'wishlist' => 'M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8L12 21l8.8-8.6a5.5 5.5 0 0 0 0-7.8Z',
    'cart' => 'M3 3h2l3 12h10l3-9H6M10 20h.01M18 20h.01',
];
?>
<nav class="account-mobile-tabs" aria-label="Menu akun mobile">
    <?php foreach ($accountMenu as $item) { ?>
        <a class="<?= $activeAccountMenu === $item['key'] ? 'active' : ''; ?>" href="<?= esc($item['url']); ?>" <?= $activeAccountMenu === $item['key'] ? 'aria-current="page"' : ''; ?>>
            <svg class="account-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="<?= esc($accountIconPaths[$item['key']], 'attr'); ?>"/></svg><span><?= esc($item['label']); ?></span>
        </a>
    <?php } ?>
</nav>
<aside class="account-side-card">
    <div class="account-side-title">Akun Saya</div>
    <nav class="account-nav-list" aria-label="Menu akun">
        <?php foreach ($accountMenu as $item) { ?>
            <a class="account-nav-item <?= $activeAccountMenu === $item['key'] ? 'active' : ''; ?>" href="<?= esc($item['url']); ?>" <?= $activeAccountMenu === $item['key'] ? 'aria-current="page"' : ''; ?>>
                <svg class="account-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="<?= esc($accountIconPaths[$item['key']], 'attr'); ?>"/></svg><span><?= esc($item['label']); ?></span>
            </a>
        <?php } ?>
    </nav>
    <div class="account-nav-logout">
        <a data-buyer-logout class="account-nav-item text-danger" href="/keluar" data-clear-notification-preference>
            <i class="material-icons">logout</i><span>Keluar</span>
        </a>
    </div>
</aside>
