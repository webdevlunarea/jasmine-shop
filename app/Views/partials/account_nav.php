<?php
$activeAccountMenu = $activeAccountMenu ?? '';
$accountMenu = [
    ['key' => 'account', 'label' => 'Profilku', 'url' => '/account', 'icon' => 'person_outline'],
    ['key' => 'transaction', 'label' => 'Transaksi', 'url' => '/transaction', 'icon' => 'receipt_long'],
    ['key' => 'point', 'label' => 'Luna Poin', 'url' => '/point', 'icon' => 'stars'],
    ['key' => 'voucher', 'label' => 'Voucher', 'url' => '/voucher', 'icon' => 'local_offer'],
    ['key' => 'wishlist', 'label' => 'Wishlist', 'url' => '/wishlist', 'icon' => 'favorite_border'],
    ['key' => 'cart', 'label' => 'Keranjang', 'url' => '/cart', 'icon' => 'shopping_cart'],
];
?>
<style>
    .account-shell {
        display: block;
        position: relative;
        clear: both;
        min-height: auto;
        padding: 1rem 0 clamp(46px, 6vw, 86px);
        overflow: visible;
    }
    .account-shell + footer.footer-transparent { clear: both; position: relative; z-index: 1; margin-top: 0; }
    .account-layout { display: grid; grid-template-columns: 260px minmax(0, 1fr); gap: 18px; align-items: start; }
    .account-side-card, .account-content-card { background: #fff; border: 1px solid rgba(17,24,39,.08); border-radius: 20px; box-shadow: 0 10px 28px rgba(17,24,39,.06); }
    .account-side-card { padding: 12px; position: sticky; top: 86px; }
    .account-content-card { padding: 18px; min-width: 0; overflow-x: auto; }
    .account-content-card .accordion-button { min-height: 48px; }
    .account-content-card .card-group1 { width: 100%; }
    .account-side-title { padding: 10px 12px; font-weight: 800; color: #111827; }
    .account-nav-list { display: grid; gap: 6px; }
    .account-nav-item { display: flex; align-items: center; gap: 10px; min-height: 44px; padding: 10px 12px; border-radius: 14px; color: #374151; text-decoration: none; font-weight: 650; }
    .account-nav-item:hover, .account-nav-item.active { background: var(--hijaumuda); color: var(--hijau); }
    .account-nav-item .material-icons { font-size: 21px; }
    .account-nav-logout { margin-top: 8px; border-top: 1px solid rgba(17,24,39,.08); padding-top: 10px; }
    .account-mobile-tabs { display: none; gap: 8px; overflow-x: auto; padding: 2px 0 12px; margin-bottom: 4px; scrollbar-width: none; }
    .account-mobile-tabs::-webkit-scrollbar { display: none; }
    .account-mobile-tabs a { flex: 0 0 auto; display: inline-flex; align-items: center; gap: 6px; min-height: 42px; padding: 9px 12px; border: 1px solid rgba(17,24,39,.09); border-radius: 999px; background: #fff; color: #374151; text-decoration: none; font-weight: 700; }
    .account-mobile-tabs a.active { background: var(--hijau); color: #fff; border-color: var(--hijau); }
    .account-mobile-tabs .material-icons { font-size: 19px; }
    @media (max-width: 991.98px) {
        .account-layout { grid-template-columns: 1fr; gap: 12px; }
        .account-side-card { display: none; }
        .account-mobile-tabs { display: flex; }
        .account-content-card { border-radius: 16px; padding: 14px; }
    }
    @media (max-width: 575.98px) {
        .account-shell { padding-top: .75rem; padding-bottom: 58px; }
        .account-shell + footer.footer-transparent { margin-top: 0; }
    }
</style>
<div class="account-mobile-tabs" aria-label="Menu akun">
    <?php foreach ($accountMenu as $item) { ?>
        <a class="<?= $activeAccountMenu === $item['key'] ? 'active' : ''; ?>" href="<?= esc($item['url']); ?>">
            <i class="material-icons"><?= esc($item['icon']); ?></i><span><?= esc($item['label']); ?></span>
        </a>
    <?php } ?>
</div>
<aside class="account-side-card">
    <div class="account-side-title">Akun Saya</div>
    <nav class="account-nav-list" aria-label="Menu akun">
        <?php foreach ($accountMenu as $item) { ?>
            <a class="account-nav-item <?= $activeAccountMenu === $item['key'] ? 'active' : ''; ?>" href="<?= esc($item['url']); ?>">
                <i class="material-icons"><?= esc($item['icon']); ?></i><span><?= esc($item['label']); ?></span>
            </a>
        <?php } ?>
    </nav>
    <div class="account-nav-logout">
        <a class="account-nav-item text-danger" href="/hapuslocalstorage/<?= base64_encode('/keluar'); ?>">
            <i class="material-icons">logout</i><span>Keluar</span>
        </a>
    </div>
</aside>
