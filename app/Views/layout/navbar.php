<?php
$notif = [
    'wishlist' => session()->getFlashdata('notif-wishlist'),
    'cart' => session()->getFlashdata('notif-cart'),
];
$showMobileBottomNav = in_array($title, ['Beranda', 'Semua Produk', 'Artikel']);
$isAdminNav = session()->get('role') == '1';
if ($isAdminNav) {
    $adminRole = \App\Services\Auth\AdminPermissions::normalizeRole(session('admin_role') ?: 'ADMIN');
    $isDevAdmin = $adminRole === 'DEV';
    $currentPath = trim(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH), '/');
    $adminNav = [
        ['group'=>'Workspace', 'label' => 'Dashboard', 'icon' => 'dashboard', 'url' => '/admin/dashboard', 'permission'=>'admin.dashboard', 'match' => ['admin/dashboard']],
        ['group'=>'Operasional', 'label' => 'Pesanan', 'icon' => 'receipt_long', 'url' => '/listcustomer', 'permission'=>'orders.read', 'match' => ['listcustomer', 'order']],
        ['group'=>'Operasional', 'label' => 'Pengguna', 'icon' => 'group', 'url' => '/customerdirectoryadmin', 'permission'=>'customers.read', 'match' => ['customerdirectoryadmin']],
        ['group'=>'Operasional', 'label' => 'Invoice', 'icon' => 'description', 'url' => '/invoiceadmin', 'permission'=>'finance.read', 'match' => ['invoiceadmin', 'addinvoiceadmin']],
        ['group'=>'Operasional', 'label' => 'Pengiriman', 'icon' => 'local_shipping', 'url' => '/shippingadmin', 'permission'=>'shipping.read', 'match' => ['shippingadmin']],
        ['group'=>'Operasional', 'label' => 'Akun & email spam', 'icon' => 'manage_accounts', 'url' => '/customeradmin', 'permission'=>'customers.manage', 'match' => ['customeradmin']],
        ['group'=>'Operasional', 'label' => 'Suspend akun', 'icon' => 'person_off', 'url' => '/customersuspensionadmin', 'permission'=>'customers.manage', 'match' => ['customersuspensionadmin']],
        ['group'=>'Pengaturan', 'label' => 'Broadcast & notifikasi', 'icon' => 'notifications_active', 'url' => '/notificationtestadmin', 'permission'=>'settings.write', 'match' => ['notificationtestadmin']],
        ['group'=>'Pengaturan', 'label' => 'Program Afiliasi', 'icon' => 'group_add', 'url' => '/androidreferraladmin', 'permission'=>'settings.write', 'match' => ['androidreferraladmin']],
        ['group'=>'Pengaturan', 'label' => 'Traffic', 'icon' => 'bar_chart', 'url' => '/trafficadmin', 'permission'=>'settings.read', 'match' => ['trafficadmin']],
        ['group'=>'Pengaturan', 'label' => 'Tema warna', 'icon' => 'palette', 'url' => '/settingwarna', 'permission'=>'settings.write', 'match' => ['settingwarna']],
        ['group'=>'Katalog', 'label' => 'Produk', 'icon' => 'inventory_2', 'url' => '/listproduct', 'permission'=>'catalog.read', 'match' => ['listproduct', 'addproduct', 'editproduct', 'findproductadmin']],
        ['group'=>'Katalog', 'label' => 'Stok', 'icon' => 'unarchive', 'url' => '/stokadmin/all/1', 'permission'=>'catalog.read', 'match' => ['stokadmin']],
        ['group'=>'Katalog', 'label' => 'Pemetaan MyLuna', 'icon' => 'compare_arrows', 'url' => '/lunamappingadmin', 'permission'=>'catalog.read', 'match' => ['lunamappingadmin']],
        ['group'=>'Katalog', 'label' => 'Gambar kategori', 'icon' => 'image_search', 'url' => '/categoryimagesadmin', 'permission'=>'catalog.read', 'match' => ['categoryimagesadmin']],
        ['group'=>'Katalog', 'label' => 'Rating & terjual', 'icon' => 'star_rate', 'url' => '/manageratingterjual', 'permission'=>'content.read', 'match' => ['manageratingterjual']],
        ['group'=>'Promosi & konten', 'label' => 'Flash Sale', 'icon' => 'bolt', 'url' => '/flashsaleadmin', 'permission'=>'content.read', 'match' => ['flashsaleadmin']],
        ['group'=>'Promosi & konten', 'label' => 'Voucher', 'icon' => 'confirmation_number', 'url' => '/listvoucher', 'permission'=>'content.read', 'match' => ['listvoucher', 'addvoucher', 'editvoucher']],
        ['group'=>'Promosi & konten', 'label' => 'Redeem', 'icon' => 'redeem', 'url' => '/listredeem', 'permission'=>'content.read', 'match' => ['listredeem']],
        ['group'=>'Promosi & konten', 'label' => 'Artikel', 'icon' => 'article', 'url' => '/article', 'permission'=>'content.read', 'match' => ['article', 'addarticle', 'editarticle']],
        ['group'=>'Promosi & konten', 'label' => 'Banner', 'icon' => 'image', 'url' => '/listbanner', 'permission'=>'content.read', 'match' => ['listbanner', 'addbanner', 'editbanner']],
    ];
    $adminNav[] = ['group'=>'Pengaturan', 'label'=>'Peran & akses', 'icon'=>'admin_panel_settings', 'url'=>'/admin/roles', 'permission'=>'admin.roles.manage', 'match'=>['admin/roles']];
    if (!$isDevAdmin) {
        $simpleNavLabels = [
            'Dashboard' => 'Ringkasan', 'Pengguna' => 'Pelanggan', 'Invoice' => 'Tagihan',
            'Akun & email spam' => 'Akun & pendaftaran', 'Suspend akun' => 'Batasi akun',
            'Broadcast & notifikasi' => 'Notifikasi', 'Program Afiliasi' => 'Program berbagi',
            'Traffic' => 'Pengunjung', 'Tema warna' => 'Tampilan toko', 'Pemetaan MyLuna' => 'Cocokkan produk',
            'Gambar kategori' => 'Foto kategori', 'Rating & terjual' => 'Ulasan & penjualan',
            'Flash Sale' => 'Promo kilat', 'Redeem' => 'Kode hadiah',
        ];
        $simpleNavGroups = ['Workspace' => 'Utama', 'Operasional' => 'Toko', 'Katalog' => 'Produk', 'Promosi & konten' => 'Promosi'];
        foreach ($adminNav as &$adminNavItem) {
            $adminNavItem['label'] = $simpleNavLabels[$adminNavItem['label']] ?? $adminNavItem['label'];
            $adminNavItem['group'] = $simpleNavGroups[$adminNavItem['group']] ?? $adminNavItem['group'];
        }
        unset($adminNavItem);
    }
    $adminNav = array_values(array_filter($adminNav, static fn($item) => \App\Services\Auth\AdminPermissions::canAccess($adminRole, $item['permission'], (array) session('admin_permissions'))));
?>
<input type="checkbox" id="admin-menu-toggle" class="d-none">
<aside class="admin-sidebar" id="admin-navigation" aria-label="Menu administrator">
    <div class="admin-brand-row">
        <a href="/admin/dashboard" class="admin-brand" aria-label="Lunarea, dashboard admin">
            <span class="admin-brand-mark" aria-hidden="true">L.</span>
            <span class="admin-brand-copy"><strong>Lunarea</strong><small><?= $isDevAdmin ? 'Workspace admin' : 'Panel admin'; ?></small></span>
        </a>
        <button type="button" class="admin-icon-btn admin-drawer-close" data-admin-menu-close aria-label="Tutup menu admin"><i class="material-icons" aria-hidden="true">close</i></button>
    </div>
    <nav class="admin-nav" aria-label="Navigasi admin">
        <?php $lastAdminGroup = null; foreach ($adminNav as $item) {
            $active = false;
            foreach ($item['match'] as $pattern) {
                if ($currentPath === $pattern || strpos($currentPath, $pattern . '/') === 0) {
                    $active = true;
                    break;
                }
            }
            if ($lastAdminGroup !== $item['group']) { ?>
                <p class="admin-nav-heading"><?= esc($item['group']); ?></p>
            <?php $lastAdminGroup = $item['group']; } ?>
            <a href="<?= esc($item['url'], 'attr'); ?>" class="admin-nav-item <?= $active ? 'active' : ''; ?>" <?= $active ? 'aria-current="page"' : ''; ?>>
                <i class="material-icons" aria-hidden="true"><?= $item['icon']; ?></i>
                <span><?= esc($item['label']); ?></span>
            </a>
        <?php } ?>
    </nav>
    <div class="admin-sidebar-footer">
        <a href="/" class="admin-nav-item">
            <i class="material-icons" aria-hidden="true">storefront</i>
            <span>Lihat Toko</span>
        </a>
        <a href="/account" class="admin-nav-item">
            <i class="material-icons" aria-hidden="true">person_outline</i>
            <span>Akun</span>
        </a>
        <form method="post" action="/keluar" class="m-0">
            <button type="submit" class="admin-nav-item danger w-100 border-0 bg-transparent text-start">
                <i class="material-icons" aria-hidden="true">logout</i>
                <span>Keluar</span>
            </button>
        </form>
    </div>
</aside>
<button type="button" class="admin-menu-scrim" data-admin-menu-close aria-label="Tutup menu navigasi" tabindex="-1"></button>
<header class="admin-topbar">
    <div class="admin-topbar-left">
        <button type="button" class="admin-icon-btn admin-menu-trigger" data-admin-menu-open aria-controls="admin-navigation" aria-expanded="false" aria-label="Buka menu admin">
            <i class="material-icons" aria-hidden="true">menu</i>
        </button>
        <div>
            <?php $adminSectionLabel = $currentPath === 'listcustomer' || strpos($currentPath, 'listcustomer/') === 0 ? 'Pesanan' : 'Menu'; ?>
            <p class="admin-eyebrow m-0"><?= $isDevAdmin ? 'Workspace / ' : 'Panel / '; ?><?= $adminSectionLabel; ?></p>
            <?php $adminTitleLabels = ['Dashboard'=>'Ringkasan', 'List Customer'=>'Pesanan pelanggan', 'Invoice'=>'Tagihan', 'Program Afiliasi'=>'Program berbagi', 'Traffic'=>'Pengunjung']; ?>
            <h1 class="admin-page-title m-0"><?= esc($isDevAdmin ? ($title === 'List Customer' ? 'Pesanan pelanggan' : $title) : ($adminTitleLabels[$title] ?? $title)); ?></h1>
        </div>
    </div>
    <div class="admin-topbar-actions">
        <a href="/" class="admin-store-link"><i class="material-icons" aria-hidden="true">storefront</i><span>Lihat toko</span></a>
        <a href="/account" class="admin-icon-btn admin-profile-link" aria-label="Buka akun staf" title="Akun staf">
            <?php $staffAvatar = (string)session('foto'); if ($staffAvatar !== '' && $staffAvatar !== '/imguser/ZGVmYXVsdA==') { ?>
                <img class="admin-profile-avatar" src="<?= esc($staffAvatar, 'attr'); ?>" alt="">
            <?php } else { ?><i class="material-icons" aria-hidden="true">person_outline</i><?php } ?>
        </a>
    </div>
</header>
<?php return;
}
?>
<style>
    .menu-hp-navbar-admin {
        position: fixed;
        top: 70px;
        right: 5px;
        right: -50vw;
        z-index: 16;
        background-color: white;
        display: flex;
        flex-direction: column;
        align-items: end;
        width: 50vw;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
        transition: 0.2s;
    }

    .menu-hp-navbar-admin a:not(.btn) {
        color: black;
        display: block;
        padding-block: 0.5em;
        text-align: end;
    }

    #menu-icon-admin:checked~.menu-hp-navbar-admin {
        transition: 0.2s;
        right: 5px;
    }
</style>
<div class="buyer-notif-wrap d-flex justify-content-center" aria-live="polite" aria-atomic="true">
    <div class="notif" role="status">
        <span class="notif-icon" aria-hidden="true">
            <i class="material-icons">check</i>
        </span>
        <div class="notif-copy">
            <span class="notif-title">Berhasil</span>
            <p class="notif-message">
                <?= $notif['wishlist'] ? $notif['wishlist'] : ''; ?><?= $notif['cart'] ? $notif['cart'] : ''; ?>
            </p>
        </div>
    </div>
</div>
<nav class="navbar-hp buyer-mobile-header hide-ke-show-block py-2" id="navbar-hp">
    <div class="container mobile-navbar-main">
        <a class="navbar-brand buyer-desktop-brand mobile-navbar-brand" href="/" aria-label="Lunarea Furniture — Beranda">
            <img src="<?= base_url('/img/Logo Lunarea Bg Terang.png'); ?>" class="buyer-desktop-logo mobile-navbar-logo" width="180" height="19" alt="Lunarea Furniture" decoding="async">
        </a>
        <div class="mobile-navbar-actions">
            <button type="button" id="buyer-search-toggle" class="btn" aria-label="Buka pencarian" aria-controls="buyer-mobile-search" aria-expanded="false">
                <svg class="buyer-action-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" aria-hidden="true" focusable="false"><circle cx="10.5" cy="10.5" r="6.5"/><path d="m16 16 5 5"/></svg>
            </button>
            <?php if (session()->get('isLogin')) { ?>
                <?php if (session()->get('role') == 0) { ?>
                    <a href="/wishlist" class="btn position-relative" aria-label="Daftar Favorit">
                        <svg class="buyer-action-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8L12 21l8.8-8.6a5.5 5.5 0 0 0 0-7.8Z"/></svg>
                        <?php if ($title != 'Wishlist') { ?>
                            <span style="top: 2px; right: 5px;" class="notif-wishlist d-none position-absolute p-1 bg-danger rounded-circle"></span>
                        <?php } ?>
                    </a>
                    <a href="/cart" class="btn position-relative" aria-label="Keranjang">
                        <svg class="buyer-action-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M6 7h12l2 14H4L6 7Z"/><path d="M9 8V6a3 3 0 0 1 6 0v2"/></svg>
                        <?php if ($title != 'Keranjang') { ?>
                            <span style="top: 2px; right: 5px;" class="notif-cart d-none position-absolute p-1 bg-danger rounded-circle"></span>
                        <?php } ?>
                    </a>
                    <?php if (session()->get('email') == 'tamu') { ?>
                        <a href="/transaction" class="btn" aria-label="Transaksi"><i class="material-icons" aria-hidden="true">receipt_long</i></a>
                        <a data-buyer-logout href="/keluar" class="btn" aria-label="Keluar"><i class="material-icons">exit_to_app</i></a>
                    <?php } else { ?>
                        <button type="button" id="buyer-account-toggle" class="btn" aria-label="Menu akun" aria-controls="buyer-account-menu" aria-expanded="false"><svg class="buyer-action-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><circle cx="12" cy="8" r="4"/><path d="M4 21v-2a8 8 0 0 1 16 0v2"/></svg></button>
                    <?php } ?>
                <?php } else { ?>
                    <div class="mobile-admin-menu-wrap">
                        <input type="checkbox" id="menu-icon-admin" class="d-none">
                        <label for="menu-icon-admin" class="btn" aria-label="Menu admin"><i class="material-icons">menu</i></label>
                        <div class="menu-hp-navbar-admin p-3">
                            <a href="/invoiceadmin">Invoice Admin</a>
                            <a href="/listbanner">Banner</a>
                            <a href="/listvoucher">List Voucher</a>
                            <a href="/listcustomer">List Customer</a>
                            <a href="/customerdirectoryadmin">Direktori pengguna</a>
                            <a href="/customeradmin">Akun &amp; email spam</a>
                            <a href="/customersuspensionadmin">Suspend akun</a>
                            <a href="/notificationtestadmin">Tes notifikasi</a>
                            <a href="/listproduct">List Products</a>
                            <a href="/flashsaleadmin">Flash Sale</a>
                            <a href="/shippingadmin">Pengiriman</a>
                            <a href="/article">Artikel</a>
                            <a href="/manageratingterjual">Rating &amp; Terjual</a>
                            <a href="/stokadmin/all/1" class="mb-2">Mutasi Stok</a>
                            <a data-buyer-logout href="/keluar" class="btn btn-outline-danger">Keluar</a>
                        </div>
                    </div>
                <?php } ?>
            <?php } else { ?>
                <a href="/login" class="btn" aria-label="Masuk"><svg class="buyer-action-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><circle cx="12" cy="8" r="4"/><path d="M4 21v-2a8 8 0 0 1 16 0v2"/></svg></a>
            <?php } ?>
        </div>
    </div>
    <div class="container mobile-search-container" id="buyer-mobile-search" hidden>
        <form class="d-flex w-100 search-box" role="search" data-smart-search data-suggest-url="<?= esc(site_url('/search/suggest')); ?>">
            <div class="input-group">
                <input required type="search" class="form-control search-input" placeholder="Cari produk impianmu" aria-label="Cari produk" autocomplete="off" aria-autocomplete="list" aria-expanded="false" aria-controls="buyer-mobile-search-list">
                <button class="btn btn-dark" type="submit" aria-label="Cari produk"><i class="material-icons" aria-hidden="true">search</i></button>
            </div>
            <div class="smart-search-results" id="buyer-mobile-search-results" hidden><div class="smart-search-heading"><span data-smart-search-heading></span><button type="button" data-search-history-clear hidden>Hapus riwayat</button></div><div class="smart-search-list" id="buyer-mobile-search-list" role="listbox" aria-label="Saran produk"></div></div>
        </form>
    </div>
</nav>
<?php if (session()->get('isLogin') && session()->get('role') == 0 && session()->get('email') != 'tamu') {
    $buyerPath = trim(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH), '/');
    $buyerLinks = [
        ['url' => '/account', 'label' => 'Profilku', 'icon' => 'person_outline'],
        ['url' => '/transaction', 'label' => 'Transaksi', 'icon' => 'receipt_long'],
        ['url' => '/point', 'label' => 'Luna Poin', 'icon' => 'stars'],
        ['url' => '/voucher', 'label' => 'Voucher', 'icon' => 'confirmation_number'],
        ['url' => '/wishlist', 'label' => 'Daftar Favorit', 'icon' => 'favorite_border'],
        ['url' => '/cart', 'label' => 'Keranjang', 'icon' => 'shopping_cart'],
        ['url' => '/lacak-pengiriman', 'label' => 'Lacak Pengiriman', 'icon' => 'local_shipping'],
    ];
?>
<div id="buyer-account-menu" role="dialog" aria-modal="true" aria-labelledby="buyer-account-title" hidden>
    <div class="buyer-account-panel">
        <div class="buyer-account-panel__heading">
            <h5 id="buyer-account-title">Akun Saya</h5>
            <button type="button" class="buyer-account-close" data-buyer-menu-close aria-label="Tutup menu akun"><i class="material-icons" aria-hidden="true">close</i></button>
        </div>
        <nav class="buyer-account-links" aria-label="Menu pembeli">
            <?php foreach ($buyerLinks as $link) { ?>
                <a href="<?= esc($link['url']); ?>" <?= $buyerPath === trim($link['url'], '/') ? 'aria-current="page"' : ''; ?>>
                    <i class="material-icons" aria-hidden="true"><?= esc($link['icon']); ?></i><span><?= esc($link['label']); ?></span>
                </a>
            <?php } ?>
            <a data-buyer-logout class="text-danger" href="/keluar"><i class="material-icons" aria-hidden="true">logout</i><span>Keluar</span></a>
        </nav>
    </div>
</div>
<?php } ?>

<?php if ($showMobileBottomNav) { ?>
    <nav class="mobile-bottom-nav hide-ke-show-flex" aria-label="Navigasi utama mobile">
        <a class="mobile-bottom-nav__item <?= $title == 'Beranda' ? "active " : ""; ?>" href="/" <?= $title == 'Beranda' ? 'aria-current="page"' : ''; ?>>
            <span class="mobile-bottom-nav__icon"><svg class="buyer-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.65" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="m3.5 10 8.5-7 8.5 7"/><path d="M5.5 9v11.5h5v-6h3v6h5V9"/></svg></span>
            <span>Beranda</span>
        </a>
        <a class="mobile-bottom-nav__item <?= $title == 'Semua Produk' ? "active " : ""; ?>" href="/all" <?= $title == 'Semua Produk' ? 'aria-current="page"' : ''; ?>>
            <span class="mobile-bottom-nav__icon"><svg class="buyer-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.65" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M4.5 4.5h15v15h-15z"/><path d="M12 4.5v15M8 8h1M15 8h1M8 16h1M15 16h1"/></svg></span>
            <span>Produk</span>
        </a>
        <a class="mobile-bottom-nav__item <?= $title == 'Artikel' ? "active " : ""; ?>" href="/article" <?= $title == 'Artikel' ? 'aria-current="page"' : ''; ?>>
            <span class="mobile-bottom-nav__icon"><svg class="buyer-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.65" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M5 4.5h10a3 3 0 0 1 3 3v12H8a3 3 0 0 1-3-3z"/><path d="M18 19.5a3 3 0 0 1 3-3V7.5a3 3 0 0 0-3-3M8.5 9h6M8.5 12.5h6"/></svg></span>
            <span>Artikel</span>
        </a>
    </nav>
    <div class="mobile-bottom-nav-spacer hide-ke-show-block"></div>
<?php } ?>
<div class="mobile-navbar-spacer hide-ke-show-block"></div>

<nav class="navbar buyer-desktop-header navbar-expand-lg show-ke-hide mb-2">
    <div class="container">
        <div class="buyer-desktop-brand-cluster">
            <a class="navbar-brand buyer-desktop-brand" href="/" aria-label="Lunarea Furniture — Beranda">
                <img src="<?= base_url('/img/Logo Lunarea Bg Terang.png'); ?>" class="buyer-desktop-logo" width="180" height="19" alt="Lunarea Furniture" decoding="async">
            </a>
            <div class="buyer-brand-partnership" aria-label="Lunarea bagian dari Ilena">
                <span class="buyer-brand-partnership__divider" aria-hidden="true"></span>
                <span class="buyer-brand-partnership__stack">
                    <span class="buyer-brand-partnership__label">Part of</span>
                    <a class="buyer-brand-partnership__link" href="https://ilenafurniture.com" target="_blank" rel="noopener noreferrer" aria-label="Kunjungi website Ilena Furniture">
                        <img src="<?= base_url('/img/logoilena.png'); ?>" class="buyer-brand-partnership__logo" width="100" height="16" alt="Ilena Furniture" decoding="async">
                        <span class="buyer-brand-partnership__popover" aria-hidden="true">Kunjungi Ilena Furniture</span>
                    </a>
                </span>
            </div>
        </div>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link <?= $title == 'Beranda' ? "active " : ""; ?>" href="/">Beranda</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $title == 'Semua Produk' ? "active " : ""; ?>" href="/all">Produk</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $title == 'Artikel' ? "active " : ""; ?>" href="/article">Artikel</a>
                </li>
                <?php if (session()->get('isLogin') && session()->get('email') == 'tamu') { ?>
                    <a class="nav-link <?= $title == 'Transaksi Pembayaran' ? "active " : ""; ?>" href="/transaction">Transaksi</a>
                <?php } ?>
            </ul>
            <?php if (!str_contains(strtolower($title), 'artikel')) { ?>
                <form class="d-flex search-box" role="search" data-smart-search data-suggest-url="<?= esc(site_url('/search/suggest')); ?>">
                    <div class="input-group">
                        <input required type="search" class="form-control search-input" placeholder="Cari produk" aria-label="Cari produk" autocomplete="off" aria-autocomplete="list" aria-expanded="false" aria-controls="buyer-desktop-search-list">
                        <button class="btn btn-light" type="submit" aria-label="Cari produk"><svg class="buyer-action-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><circle cx="10.5" cy="10.5" r="6.5"/><path d="m16 16 5 5"/></svg></button>
                    </div>
                    <div class="smart-search-results" id="buyer-desktop-search-results" hidden><div class="smart-search-heading"><span data-smart-search-heading></span><button type="button" data-search-history-clear hidden>Hapus riwayat</button></div><div class="smart-search-list" id="buyer-desktop-search-list" role="listbox" aria-label="Saran produk"></div></div>
                </form>
            <?php } ?>
            <div class="buyer-desktop-actions">
            <?php if (session()->get('isLogin')) { ?>
                <?php if (session()->get('role') == 0) { ?>
                    <div class="buyer-desktop-favorite-wrap">
                        <a href="/wishlist" class="btn position-relative" aria-label="Daftar Favorit">
                            <svg class="buyer-action-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8L12 21l8.8-8.6a5.5 5.5 0 0 0 0-7.8Z"/></svg>
                            <?php if ($title != 'Wishlist') { ?>
                                <span style="top: 2px; right: 5px;" class="notif-wishlist d-none position-absolute p-1 bg-danger rounded-circle"></span>
                            <?php } ?>
                        </a>
                        <div class="buyer-action-popover buyer-favorite-popover">
                            <p class="buyer-action-popover__eyebrow">Produk favorit</p>
                            <p class="buyer-action-popover__copy">Simpan dan temukan lagi furnitur yang kamu sukai.</p>
                            <a href="/wishlist">Lihat produk favorit</a>
                        </div>
                    </div>
                    <div class="buyer-desktop-cart-wrap">
                        <a href="/cart" class="btn position-relative" aria-label="Keranjang">
                            <svg class="buyer-action-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M6 7h12l2 14H4L6 7Z"/><path d="M9 8V6a3 3 0 0 1 6 0v2"/></svg>
                            <?php if ($title != 'Keranjang') { ?>
                                <span style="top: 2px; right: 5px;" class="notif-cart d-none position-absolute p-1 bg-danger rounded-circle"></span>
                            <?php } ?>
                        </a>
                        <div class="buyer-action-popover buyer-cart-popover">
                            <p class="buyer-action-popover__eyebrow">Keranjang belanja</p>
                            <p class="buyer-action-popover__copy">Tinjau pilihan furnitur sebelum melanjutkan pesanan.</p>
                            <a href="/cart">Buka keranjang</a>
                        </div>
                    </div>
                    <?php if (session()->get('email') == 'tamu') { ?>
                        <a data-buyer-logout href="/keluar" class="btn" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Keluar" aria-label="Keluar"><svg class="buyer-action-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M9 4H4v16h5M10 12h11m-4-4 4 4-4 4"/></svg></a>
                    <?php } else { ?>
                        <div class="buyer-desktop-account-wrap">
                            <a href="/account" class="btn" aria-label="Akun saya"><svg class="buyer-action-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><circle cx="12" cy="8" r="4"/><path d="M4 21v-2a8 8 0 0 1 16 0v2"/></svg></a>
                            <div class="buyer-account-popover" aria-label="Pintasan akun">
                                <p class="buyer-account-popover__eyebrow">Akun Lunarea</p>
                                <p class="buyer-account-popover__copy">Akses cepat ke akun dan aktivitas belanjamu.</p>
                                <a href="/account">Profil saya</a>
                                <a href="/transaction">Pesanan saya</a>
                                <a href="/point">Luna Poin</a>
                                <a href="/wishlist">Produk favorit</a>
                            </div>
                        </div>
                    <?php } ?>
                <?php } else { ?>
                    <!-- <a href="/listform" class="btn"><i class="material-icons">insert_comment</i></a> -->
                    <a href="/invoiceadmin" class="btn" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Invoice"><i class="material-icons">description</i></a>
                    <a href="/listbanner" class="btn" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Banner"><i class="material-icons">image</i></a>
                    <a href="/listvoucher" class="btn" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Voucher"><i class="material-icons">confirmation_number</i></a>
                    <a href="/listcustomer" class="btn" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Customer"><i class="material-icons">people</i></a>
                    <a href="/listproduct" class="btn" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Product"><i class="material-icons">view_list</i></a>
                    <a href="/article" class="btn" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Artikel"><i class="material-icons">article</i></a>
                    <a href="/manageratingterjual" class="btn" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Rating & Terjual"><i class="material-icons">star_rate</i></a>
                    <a href="/stokadmin/all/1" class="btn" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Stok"><i class="material-icons">unarchive</i></a>
                    <a href="/account" class="btn" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Akun" aria-label="Akun"><svg class="buyer-action-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><circle cx="12" cy="8" r="4"/><path d="M4 21v-2a8 8 0 0 1 16 0v2"/></svg></a>
                <?php } ?>
            <?php } else { ?>
                <div class="buyer-desktop-login-wrap">
                    <a href="/login" class="btn" aria-label="Masuk"><svg class="buyer-action-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><circle cx="12" cy="8" r="4"/><path d="M4 21v-2a8 8 0 0 1 16 0v2"/></svg></a>
                    <div class="buyer-login-popover" id="buyer-login-popover">
                        <p class="buyer-login-popover__eyebrow">Selamat datang di Lunarea</p>
                        <p class="buyer-login-popover__copy">Masuk untuk menikmati pengalaman belanja yang lebih mudah.</p>
                        <a class="buyer-login-popover__primary" href="/login">Masuk</a>
                        <a class="buyer-login-popover__secondary" href="/signup">Buat akun</a>
                    </div>
                </div>
            <?php } ?>
            </div>
        </div>
    </div>
</nav>
<div class="buyer-desktop-spacer show-ke-hide"></div>
<?php if ($notif['wishlist'] || $notif['cart']) { ?>
    <script>
        const notifElm = document.querySelector('.notif');
        setTimeout(() => {
            notifElm.classList.add('show');
        }, 100);
        setTimeout(() => {
            notifElm.classList.remove('show');
        }, 2000);
    </script>
<?php } ?>
<?php if ($notif['cart']) { ?>
    <script>
        window.localStorage.setItem('notif-cart', true);
    </script>
<?php } ?>
<?php if ($notif['wishlist']) { ?>
    <script>
        window.localStorage.setItem('notif-wishlist', true);
    </script>
<?php } ?>
<script>
    const notifWishlist = document.querySelectorAll(".notif-wishlist");
    const notifCart = document.querySelectorAll(".notif-cart");
    const sessionWishlist = window.localStorage.getItem('notif-wishlist');
    const sessionCart = window.localStorage.getItem('notif-cart');
    if (sessionWishlist) {
        notifWishlist.forEach(element => {
            element.classList.remove('d-none')
        });
    }
    if (sessionCart) {
        notifCart.forEach(element => {
            element.classList.remove('d-none')
        });
    }
</script>
