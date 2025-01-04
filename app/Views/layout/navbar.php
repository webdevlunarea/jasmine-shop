<?php
$notif = [
    'wishlist' => session()->getFlashdata('notif-wishlist'),
    'cart' => session()->getFlashdata('notif-cart'),
];
?>
<style>
    .menu-hp-navbar {
        position: fixed;
        left: 0;
        top: 101px;
        background-color: rgba(0, 0, 0, 0.2);
        width: 100vw;
        height: 0svh;
        overflow: hidden;
        transition: 0.3s;
    }

    #akun-icon:checked~.menu-hp-navbar {
        height: 100svh;
        transition: 0.3s;
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
<nav class="navbar-hp hide-ke-show-block py-2" id="navbar-hp">
    <div class="container d-flex">
        <form class="d-flex flex-grow-1 search-box" role="search">
            <div class="input-group">
                <input required type="text" class="form-control search-input" placeholder="Search" aria-label="Search" aria-describedby="button-addon2">
                <button class="btn btn-dark" type="submit"><i id="btn-search" class="material-icons">search</i></button>
            </div>
        </form>
        <?php if (session()->get('isLogin')) { ?>
            <?php if (session()->get('role') == 0) { ?>
                <a href="/wishlist" class="btn position-relative">
                    <i class="material-icons">favorite_border</i>
                    <?php if ($title != 'Wishlist') { ?>
                        <span style="top: 2px; right: 5px;" class="notif-wishlist d-none position-absolute p-1 bg-danger rounded-circle"></span>
                    <?php } ?>
                </a>
                <a href="/cart" class="btn position-relative">
                    <i class="material-icons">shopping_cart</i>
                    <?php if ($title != 'Keranjang') { ?>
                        <span style="top: 2px; right: 5px;" class="notif-cart d-none position-absolute p-1 bg-danger rounded-circle"></span>
                    <?php } ?>
                </a>
                <?php if (session()->get('email') == 'tamu') { ?>
                    <a href="/keluar" class="btn" style="padding-right: 0"><i class="material-icons">exit_to_app</i></a>
                <?php } else { ?>
                    <input type="checkbox" id="akun-icon" class="d-none">
                    <label for="akun-icon" class="btn" style="padding-right: 0"><i class="material-icons">person_outline</i></label>
                    <div class="menu-hp-navbar">
                        <div class="container py-3" style="background-color: white;">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item"><a class="list <?= $title == 'Akun Saya' ? "fw-bold" : ""; ?>" href="/account">Profileku</a></li>
                                <?php if (session()->get('role') == '0') { ?>
                                    <li class="list-group-item"><a class="list <?= $title == 'Transaksi Pembayaran' ? "fw-bold" : ""; ?>" href="/transaction">Transaksi</a></li>
                                    <li class="list-group-item"><a class="list <?= $title == 'Luna Reward' ? "fw-bold" : ""; ?>" href="/point">Luna poin</a></li>
                                    <li class="list-group-item"><a class="list <?= $title == 'Voucher' ? "fw-bold" : ""; ?>" href="/voucher">Voucher</a></li>
                                <?php } ?>
                                <li class="list-group-item"><a class="btn btn-outline-danger" href="/keluar">Keluar</a></li>
                            </ul>
                        </div>
                    </div>
                    <script>
                        const menuHpNavbarElm = document.querySelector('.menu-hp-navbar');
                        const akunIconElm = document.getElementById('akun-icon')
                        menuHpNavbarElm.addEventListener('click', (e) => {
                            console.log('ini backgroundnnya')
                            akunIconElm.checked = false
                        })
                        menuHpNavbarElm.children[0].addEventListener('click', (e) => {
                            e.stopPropagation();
                            console.log('ini anaknya')
                        })
                    </script>
                <?php } ?>
            <?php } else { ?>
                <!-- <a href="/listform" class="btn"><i class="material-icons">insert_comment</i></a>
                <a href="/addarticle" class="btn"><i class="material-icons">import_contacts</i></a> -->
                <a href="/invoiceadmin" class="btn"><i class="material-icons">description</i></a>
                <a href="/listvoucher" class="btn"><i class="material-icons">confirmation_number</i></a>
                <a href="/listcustomer" class="btn"><i class="material-icons">people</i></a>
                <a href="/listproduct" class="btn"><i class="material-icons">view_list</i></a>
                <a href="/account" class="btn" style="padding-right: 0"><i class="material-icons">person_outline</i></a>
            <?php } ?>
        <?php } else { ?>
            <a href="/login" class="btn" style="padding-right: 0"><i class="material-icons">person_outline</i></a>
        <?php } ?>
    </div>
    <div class="container pt-1">
        <div class="d-flex mt-1" style="gap: 1px; background-color: rgba(255, 255, 255, 0.5)">
            <a style="font-size: smaller; background-color: var(--hijau)" class="nav-link flex-grow-1 text-center <?= $title == 'Beranda' ? "active " : ""; ?>" href="/">Beranda</a>
            <a style="font-size: smaller; background-color: var(--hijau) " class="nav-link flex-grow-1 text-center <?= $title == 'Artikel' ? "active " : ""; ?>" href="/article">Artikel</a>
            <a style="font-size: smaller; background-color: var(--hijau)" class="nav-link flex-grow-1 text-center <?= $title == 'Semua Produk' ? "active " : ""; ?>" href="/all">Produk</a>
            <!-- <?php if (session()->get('isLogin')) { ?>
                <a style="font-size: smaller; background-color: var(--hijau)" class="nav-link flex-grow-1 text-center <?= $title == 'Transaksi Pembayaran' ? "active " : ""; ?>" href="/transaction">Transaksi</a>
            <?php } ?> -->
        </div>
    </div>
</nav>
<div style="height: 107px" class="hide-ke-show-block"></div>

<nav class="navbar navbar-expand-lg show-ke-hide mb-2">
    <div class="container">
        <a class="navbar-brand" href="/" style="font-weight: bold;">
            <img src="../img/Logo Lunarea Bg Terang ukuran kecil.webp" height="17em" alt="Logo Lunarea">
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
                        <input required type="text" class="form-control search-input" placeholder="Search" aria-label="Search" aria-describedby="button-addon2">
                        <button class="btn btn-light" type="submit"><i id="btn-search" class="material-icons">search</i></button>
                    </div>
                </form>
            <?php } ?>
            <?php if (session()->get('isLogin')) { ?>
                <?php if (session()->get('role') == 0) { ?>
                    <a href="/wishlist" class="btn position-relative">
                        <i class="material-icons">favorite_border</i>
                        <?php if ($title != 'Wishlist') { ?>
                            <span style="top: 2px; right: 5px;" class="notif-wishlist d-none position-absolute p-1 bg-danger rounded-circle"></span>
                        <?php } ?>
                    </a>
                    <a href="/cart" class="btn position-relative">
                        <i class="material-icons">shopping_cart</i>
                        <?php if ($title != 'Keranjang') { ?>
                            <span style="top: 2px; right: 5px;" class="notif-cart d-none position-absolute p-1 bg-danger rounded-circle"></span>
                        <?php } ?>
                    </a>
                    <?php if (session()->get('email') == 'tamu') { ?>
                        <a href="/keluar" class="btn" style="padding-right: 0"><i class="material-icons">exit_to_app</i></a>
                    <?php } else { ?>
                        <a href="/account" class="btn" style="padding-right: 0"><i class="material-icons">person_outline</i></a>
                    <?php } ?>
                <?php } else { ?>
                    <!-- <a href="/listform" class="btn"><i class="material-icons">insert_comment</i></a>
                    <a href="/addarticle" class="btn"><i class="material-icons">import_contacts</i></a> -->
                    <a href="/invoiceadmin" class="btn"><i class="material-icons">description</i></a>
                    <a href="/listvoucher" class="btn"><i class="material-icons">confirmation_number</i></a>
                    <a href="/listcustomer" class="btn"><i class="material-icons">people</i></a>
                    <a href="/listproduct" class="btn"><i class="material-icons">view_list</i></a>
                    <a href="/account" class="btn"><i class="material-icons">person_outline</i></a>
                <?php } ?>
            <?php } else { ?>
                <a href="/login" class="btn" style="padding-right: 0"><i class="material-icons">person_outline</i></a>
            <?php } ?>
        </div>
    </div>
</nav>
<div style="height: 107px" class="show-ke-hide"></div>
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