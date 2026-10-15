<?php
$notif = [
    'wishlist' => session()->getFlashdata('notif-wishlist'),
    'cart' => session()->getFlashdata('notif-cart'),
];
$showMobileBottomNav = in_array($title, ['Beranda', 'Semua Produk', 'Artikel']);
$isAdminNav = session()->get('role') == '1';
if ($isAdminNav) {
    $currentPath = trim(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH), '/');
    $adminNav = [
        ['label' => 'Order', 'icon' => 'receipt_long', 'url' => '/listcustomer', 'match' => ['listcustomer', 'order']],
        ['label' => 'Invoice', 'icon' => 'description', 'url' => '/invoiceadmin', 'match' => ['invoiceadmin', 'addinvoiceadmin']],
        ['label' => 'Produk', 'icon' => 'inventory_2', 'url' => '/listproduct', 'match' => ['listproduct', 'addproduct', 'editproduct', 'findproductadmin']],
        ['label' => 'Flash Sale', 'icon' => 'bolt', 'url' => '/flashsaleadmin', 'match' => ['flashsaleadmin']],
        ['label' => 'Gambar Kategori', 'icon' => 'image_search', 'url' => '/categoryimagesadmin', 'match' => ['categoryimagesadmin']],
        ['label' => 'Pengiriman', 'icon' => 'local_shipping', 'url' => '/shippingadmin', 'match' => ['shippingadmin']],
        ['label' => 'Artikel', 'icon' => 'article', 'url' => '/article', 'match' => ['article', 'addarticle', 'editarticle']],
        ['label' => 'Banner', 'icon' => 'image', 'url' => '/listbanner', 'match' => ['listbanner', 'addbanner', 'editbanner']],
        ['label' => 'Traffic', 'icon' => 'bar_chart', 'url' => '/trafficadmin', 'match' => ['trafficadmin']],
        ['label' => 'Warna', 'icon' => 'palette', 'url' => '/settingwarna', 'match' => ['settingwarna']],
        ['label' => 'Voucher', 'icon' => 'confirmation_number', 'url' => '/listvoucher', 'match' => ['listvoucher', 'addvoucher', 'editvoucher']],
        ['label' => 'Rating', 'icon' => 'star_rate', 'url' => '/manageratingterjual', 'match' => ['manageratingterjual']],
        ['label' => 'Stok', 'icon' => 'unarchive', 'url' => '/stokadmin/all/1', 'match' => ['stokadmin']],
        ['label' => 'Redeem', 'icon' => 'redeem', 'url' => '/listredeem', 'match' => ['listredeem']],
    ];
?>
<input type="checkbox" id="admin-menu-toggle" class="d-none">
<aside class="admin-sidebar">
    <a href="/listcustomer" class="admin-brand">
        <img src="<?= base_url('/img/Logo Lunarea Bg Terang ukuran kecil.webp'); ?>" alt="Lunarea">
        <span>Admin</span>
    </a>
    <nav class="admin-nav" aria-label="Navigasi admin">
        <?php foreach ($adminNav as $item) {
            $active = false;
            foreach ($item['match'] as $pattern) {
                if ($currentPath === $pattern || strpos($currentPath, $pattern . '/') === 0) {
                    $active = true;
                    break;
                }
            }
        ?>
            <a href="<?= $item['url']; ?>" class="admin-nav-item <?= $active ? 'active' : ''; ?>">
                <i class="material-icons"><?= $item['icon']; ?></i>
                <span><?= $item['label']; ?></span>
            </a>
        <?php } ?>
    </nav>
    <div class="admin-sidebar-footer">
        <a href="/" class="admin-nav-item">
            <i class="material-icons">storefront</i>
            <span>Lihat Toko</span>
        </a>
        <a href="/account" class="admin-nav-item">
            <i class="material-icons">person_outline</i>
            <span>Akun</span>
        </a>
        <a href="/keluar" class="admin-nav-item danger">
            <i class="material-icons">logout</i>
            <span>Keluar</span>
        </a>
    </div>
</aside>
<label for="admin-menu-toggle" class="admin-menu-scrim"></label>
<header class="admin-topbar">
    <div class="admin-topbar-left">
        <label for="admin-menu-toggle" class="admin-icon-btn" aria-label="Buka menu admin">
            <i class="material-icons">menu</i>
        </label>
        <div>
            <p class="admin-eyebrow m-0">Lunarea Admin</p>
            <h1 class="admin-page-title m-0"><?= esc($title); ?></h1>
        </div>
    </div>
    <div class="admin-topbar-actions">
        <a href="/addproduct" class="btn btn-primary1 admin-quick-action"><i class="material-icons">add</i><span>Produk</span></a>
        <a href="/addarticle" class="btn btn-light admin-icon-btn" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Tambah Artikel"><i class="material-icons">article</i></a>
        <a href="/addbanner" class="btn btn-light admin-icon-btn" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Tambah Banner"><i class="material-icons">add_photo_alternate</i></a>
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
<div class="d-flex justify-content-center" style="position: fixed; top: 0; z-index: 15; left: 0; width: 100%;">
    <div class="notif">
        <div class="d-flex py-2 justify-content-center align-items-center">
            <i class="material-icons fw-bold" style="color: var(--hijau);">check</i>
        </div>
        <p class="m-0 fw-bold"><?= $notif['wishlist'] ? $notif['wishlist'] : ''; ?><?= $notif['cart'] ? $notif['cart'] : ''; ?></p>
    </div>
</div>
<nav class="navbar-hp buyer-mobile-header hide-ke-show-block py-2" id="navbar-hp">
    <div class="container mobile-navbar-main">
        <a class="mobile-navbar-brand" href="/" aria-label="Lunarea Furniture">
            <span class="mobile-navbar-wordmark">Lunarea</span>
        </a>
        <div class="mobile-navbar-actions">
            <button type="button" id="buyer-search-toggle" class="btn" aria-label="Buka pencarian" aria-controls="buyer-mobile-search" aria-expanded="false">
                <svg class="buyer-action-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" aria-hidden="true" focusable="false"><circle cx="10.5" cy="10.5" r="6.5"/><path d="m16 16 5 5"/></svg>
            </button>
            <?php if (session()->get('isLogin')) { ?>
                <?php if (session()->get('role') == 0) { ?>
                    <a href="/wishlist" class="btn position-relative" aria-label="Wishlist">
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
                        <a href="/keluar" class="btn" aria-label="Keluar"><i class="material-icons">exit_to_app</i></a>
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
                            <a href="/listproduct">List Products</a>
                            <a href="/flashsaleadmin">Flash Sale</a>
                            <a href="/shippingadmin">Pengiriman</a>
                            <a href="/article">Artikel</a>
                            <a href="/manageratingterjual">Rating &amp; Terjual</a>
                            <a href="/stokadmin/all/1" class="mb-2">Mutasi Stok</a>
                            <a href="/keluar" class="btn btn-outline-danger">Keluar</a>
                        </div>
                    </div>
                <?php } ?>
            <?php } else { ?>
                <a href="/login" class="btn" aria-label="Login"><svg class="buyer-action-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><circle cx="12" cy="8" r="4"/><path d="M4 21v-2a8 8 0 0 1 16 0v2"/></svg></a>
            <?php } ?>
        </div>
    </div>
    <div class="container mobile-search-container" id="buyer-mobile-search" hidden>
        <form class="d-flex w-100 search-box" role="search">
            <div class="input-group">
                <input required type="text" class="form-control search-input" placeholder="Cari produk impianmu" aria-label="Cari produk">
                <button class="btn btn-dark" type="submit" aria-label="Cari produk"><i class="material-icons" aria-hidden="true">search</i></button>
            </div>
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
        ['url' => '/wishlist', 'label' => 'Wishlist', 'icon' => 'favorite_border'],
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
            <a class="text-danger" href="/keluar"><i class="material-icons" aria-hidden="true">logout</i><span>Keluar</span></a>
        </nav>
    </div>
</div>
<?php } ?>

<?php if ($showMobileBottomNav) { ?>
    <nav class="mobile-bottom-nav hide-ke-show-flex" aria-label="Navigasi utama mobile">
        <a class="mobile-bottom-nav__item <?= $title == 'Beranda' ? "active " : ""; ?>" href="/">
            <svg class="buyer-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-5v-7H9v7H4a1 1 0 0 1-1-1Z"/></svg>
            <span>Beranda</span>
        </a>
        <a class="mobile-bottom-nav__item <?= $title == 'Semua Produk' ? "active " : ""; ?>" href="/all">
            <svg class="buyer-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg>
            <span>Produk</span>
        </a>
        <a class="mobile-bottom-nav__item <?= $title == 'Artikel' ? "active " : ""; ?>" href="/article">
            <svg class="buyer-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><rect x="4" y="3" width="16" height="18" rx="2"/><path d="M8 7h8M8 11h8M8 15h5"/></svg>
            <span>Artikel</span>
        </a>
    </nav>
    <div class="mobile-bottom-nav-spacer hide-ke-show-block"></div>
<?php } ?>
<div class="mobile-navbar-spacer hide-ke-show-block"></div>

<nav class="navbar buyer-desktop-header navbar-expand-lg show-ke-hide mb-2">
    <div class="container">
        <a class="navbar-brand" href="/" style="font-weight: bold;">
            <img src="<?= base_url('/img/Logo Lunarea Bg Terang ukuran kecil.webp'); ?>" height="17em" alt="Logo Lunarea">
        </a>
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
                <form class="d-flex search-box" role="search">
                    <div class="input-group">
                        <input required type="text" class="form-control search-input" placeholder="Search" aria-label="Cari produk">
                        <button class="btn btn-light" type="submit" aria-label="Cari produk"><i class="material-icons" aria-hidden="true">search</i></button>
                    </div>
                </form>
            <?php } ?>
            <?php if (session()->get('isLogin')) { ?>
                <?php if (session()->get('role') == 0) { ?>
                    <a href="/wishlist" class="btn position-relative" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Wishlist">
                        <i class="material-icons">favorite_border</i>
                        <?php if ($title != 'Wishlist') { ?>
                            <span style="top: 2px; right: 5px;" class="notif-wishlist d-none position-absolute p-1 bg-danger rounded-circle"></span>
                        <?php } ?>
                    </a>
                    <a href="/cart" class="btn position-relative" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Keranjang">
                        <i class="material-icons">shopping_cart</i>
                        <?php if ($title != 'Keranjang') { ?>
                            <span style="top: 2px; right: 5px;" class="notif-cart d-none position-absolute p-1 bg-danger rounded-circle"></span>
                        <?php } ?>
                    </a>
                    <?php if (session()->get('email') == 'tamu') { ?>
                        <a href="/keluar" class="btn" style="padding-right: 0" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Logout"><i class="material-icons">exit_to_app</i></a>
                    <?php } else { ?>
                        <a href="/account" class="btn" style="padding-right: 0" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Akun"><i class="material-icons">person_outline</i></a>
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
                    <a href="/account" class="btn" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Akun"><i class="material-icons">person_outline</i></a>
                <?php } ?>
            <?php } else { ?>
                <a href="/login" class="btn" style="padding-right: 0" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Login"><i class="material-icons">person_outline</i></a>
            <?php } ?>
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
