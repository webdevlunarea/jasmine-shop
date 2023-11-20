<nav class="navbar navbar-expand-lg bg-body-tertiary">
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
                <?php if (!session()->get('isLogin')) { ?>
                    <li class="nav-item">
                        <a class="nav-link <?= $title == 'Daftar' ? "active " : ""; ?>" href="/login">Masuk</a>
                    </li>
                <?php } ?>
            </ul>
            <form class="d-flex" role="search">
                <div class="input-group">
                    <input type="text" class="form-control" placeholder="Search" aria-label="Search" aria-describedby="button-addon2">
                    <button class="btn btn-dark" type="submit" id="button-addon2"><i class="material-icons">search</i></button>
                </div>
            </form>
            <?php if (session()->get('isLogin')) { ?>
                <?php if (session()->get('role') == 0 ) { ?>
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