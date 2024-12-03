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
                <a href="/wishlist" class="btn"><i class="material-icons">favorite_border</i></a>
                <a href="/cart" class="btn"><i class="material-icons">shopping_cart</i></a>
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
            <?php if (session()->get('isLogin')) { ?>
                <a style="font-size: smaller; background-color: var(--hijau)" class="nav-link flex-grow-1 text-center <?= $title == 'Transaksi Pembayaran' ? "active " : ""; ?>" href="/transaction">Transaksi</a>
            <?php } ?>
        </div>
    </div>
</nav>
<div style="height: 107px" class="hide-ke-show-block"></div>

<nav class="navbar navbar-expand-lg show-ke-hide mb-2">
    <div class="container">
        <a class="navbar-brand" href="/" style="font-weight: bold;">
            <img src="../img/Logo Jasmine.webp" height="30em" alt="Logo Lunarea">
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
            <?php if (str_contains($title, 'artikel')) { ?>
                <form class="d-flex search-box" role="search">
                    <div class="input-group">
                        <input required type="text" class="form-control search-input" placeholder="Search" aria-label="Search" aria-describedby="button-addon2">
                        <button class="btn btn-light" type="submit"><i id="btn-search" class="material-icons">search</i></button>
                    </div>
                </form>
            <?php } ?>
            <?php if (session()->get('isLogin')) { ?>
                <?php if (session()->get('role') == 0) { ?>
                    <a href="/wishlist" class="btn"><i class="material-icons">favorite_border</i></a>
                    <a href="/cart" class="btn"><i class="material-icons">shopping_cart</i></a>
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