<nav class="navbar-hp hide-ke-show-block py-2" id="navbar-hp">
    <div class="container d-flex">
        <!-- <a class="navbar-brand" href="/" style="font-weight: bold;">
            <img src="../img/Logo Jasmine.png" height="30em">
        </a> -->
        <form class="d-flex flex-grow-1 search-box" role="search">
            <div class="input-group">
                <input required type="text" class="form-control search-input" placeholder="Search" aria-label="Search" aria-describedby="button-addon2">
                <button class="btn btn-dark" type="submit"><i class="material-icons">search</i></button>
            </div>
        </form>
        <?php if (session()->get('isLogin')) { ?>
            <?php if (session()->get('role') == 0) { ?>
                <a href="/wishlist" class="btn"><i class="material-icons">favorite_border</i></a>
                <a href="/cart" class="btn"><i class="material-icons">shopping_cart</i></a>
                <a href="/account" class="btn" style="padding-right: 0"><i class="material-icons">person_outline</i></a>
            <?php } else { ?>
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
            <a style="font-size: smaller; background-color: var(--hijau) " class="nav-link flex-grow-1 text-center <?= $title == 'Tentang' ? "active " : ""; ?>" href="/about">Tentang</a>
            <a style="font-size: smaller; background-color: var(--hijau)" class="nav-link flex-grow-1 text-center <?= $title == 'Semua Produk' ? "active " : ""; ?>" href="/all">Produk</a>
        </div>
    </div>
</nav>
<div style="height: 77px" class="hide-ke-show-block "></div>

<nav class="navbar navbar-expand-lg show-ke-hide mb-2">
    <div class="container">
        <a class="navbar-brand" href="/" style="font-weight: bold;">
            <img src="../img/Logo Jasmine.png" height="30em">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link <?= $title == 'Beranda' ? "active " : ""; ?>" href="/">Beranda</a>
                </li>
                <!-- <li class="nav-item">
                    <a class="nav-link <?= $title == 'Kontak' ? "active " : ""; ?>" href="/contact">Kontak</a>
                </li> -->
                <li class="nav-item">
                    <a class="nav-link <?= $title == 'Tentang' ? "active " : ""; ?>" href="/about">Tentang</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $title == 'Semua Produk' ? "active " : ""; ?>" href="/all">Produk</a>
                </li>
                <?php if (!session()->get('isLogin')) { ?>
                    <li class="nav-item">
                        <a class="nav-link <?= $title == 'Daftar' ? "active " : ""; ?>" href="/login">Masuk</a>
                    </li>
                <?php } ?>
            </ul>
            <form class="d-flex search-box" role="search">
                <div class="input-group">
                    <input required type="text" class="form-control search-input" placeholder="Search" aria-label="Search" aria-describedby="button-addon2">
                    <button class="btn btn-dark" type="submit"><i class="material-icons">search</i></button>
                </div>
            </form>
            <?php if (session()->get('isLogin')) { ?>
                <?php if (session()->get('role') == 0) { ?>
                    <a href="/wishlist" class="btn"><i class="material-icons">favorite_border</i></a>
                    <a href="/cart" class="btn"><i class="material-icons">shopping_cart</i></a>
                    <a href="/account" class="btn"><i class="material-icons">person_outline</i></a>
                <?php } else { ?>
                    <a href="/listproduct" class="btn"><i class="material-icons">view_list</i></a>
                    <a href="/account" class="btn"><i class="material-icons">person_outline</i></a>
                <?php } ?>
            <?php } ?>
        </div>
    </div>
</nav>