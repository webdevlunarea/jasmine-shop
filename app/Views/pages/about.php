<?= $this->extend("layout/template"); ?>
<?= $this->section("content"); ?>
<div class="konten about-page">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/">Beranda</a></li>
                <li class="breadcrumb-item active" aria-current="page">Tentang Lunarea</li>
            </ol>
        </nav>

        <section class="about-hero">
            <div class="about-hero__content">
                <p class="about-eyebrow">Tentang kami</p>
                <h1>Furniture keluarga Indonesia sejak 1989.</h1>
                <p class="about-lead">
                    Lunarea Furniture hadir sebagai produsen sekaligus distributor perabot rumah yang berpengalaman,
                    menghadirkan produk fungsional, stylish, dan mudah dijangkau untuk berbagai kebutuhan rumah.
                </p>
                <div class="about-actions">
                    <a href="/all" class="btn btn-primary1">Lihat Produk</a>
                    <a href="/form" class="btn btn-outline-dark">Hubungi Kami</a>
                </div>
            </div>
            <div class="about-hero__visual">
                <div class="about-gallery">
                    <img src="/img/header/header_comp3.webp" alt="Produk Lunarea Furniture">
                    <img src="/img/header/header_comp1.webp" alt="Furniture rumah Lunarea">
                    <img src="/img/header/header_comp2.webp" alt="Lemari dan rak Lunarea">
                    <img src="/img/header/header_comp4.webp" alt="Meja dan kursi Lunarea">
                </div>
            </div>
        </section>

        <section class="about-stats">
            <div>
                <strong>1989</strong>
                <span>Mulai hadir</span>
            </div>
            <div>
                <strong>30+</strong>
                <span>Tahun pengalaman</span>
            </div>
            <div>
                <strong>100+</strong>
                <span>Distributor & toko</span>
            </div>
        </section>

        <section class="about-story">
            <div>
                <p class="about-eyebrow">Sejarah perusahaan</p>
                <h2>Lunarea Furniture</h2>
            </div>
            <div class="about-story__text">
                <p>
                    Lunarea Furniture hadir sejak tahun 1989 sebagai produsen sekaligus distributor aneka perabotan
                    rumah yang berpengalaman. Selama lebih dari tiga puluh tahun, Lunarea konsisten memproduksi
                    perabotan mulai dari meja, kursi, lemari, dan rak dengan kualitas terbaik.
                </p>
                <p>
                    Produk kami dibuat dari material pilihan dan dikerjakan oleh tim yang berpengalaman. Saat ini,
                    Lunarea telah memiliki ratusan distributor dan toko ternama yang tersebar di berbagai kota di
                    Indonesia.
                </p>
                <p>
                    Untuk semakin memudahkan aktivitas belanja konsumen, Lunarea juga hadir di berbagai platform
                    marketplace ternama di Indonesia.
                </p>
            </div>
        </section>

        <section class="about-values">
            <div class="about-value">
                <i class="material-icons">verified</i>
                <h5>Kualitas terjaga</h5>
                <p>Produk dibuat dengan standar material dan proses produksi yang konsisten.</p>
            </div>
            <div class="about-value">
                <i class="material-icons">home_work</i>
                <h5>Desain fungsional</h5>
                <p>Furniture dirancang untuk kebutuhan rumah modern yang praktis dan nyaman.</p>
            </div>
            <div class="about-value">
                <i class="material-icons">local_shipping</i>
                <h5>Mudah dijangkau</h5>
                <p>Tersedia melalui toko, distributor, dan marketplace agar lebih mudah ditemukan.</p>
            </div>
        </section>

        <section class="about-quote">
            <p>“Lunarea furniture, always your choice, always your furniture.”</p>
        </section>
    </div>
</div>
<?= $this->endSection(); ?>
