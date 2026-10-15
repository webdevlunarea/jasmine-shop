<?= $this->extend('layout/template'); ?>
<?= $this->section('content'); ?>
<main class="konten about-page about-page--editorial">
    <div class="container">
        <section class="about-hero" aria-labelledby="about-title">
            <div class="about-hero__content">
                <p class="about-eyebrow">Tentang Lunarea</p>
                <h1 id="about-title">Furniture keluarga Indonesia sejak 1989.</h1>
                <p class="about-lead">Lunarea Furniture hadir sebagai produsen sekaligus distributor perabot rumah yang berpengalaman, menghadirkan produk fungsional, stylish, dan mudah dijangkau untuk berbagai kebutuhan rumah.</p>
                <div class="about-actions">
                    <a href="/all" class="btn btn-primary1">Lihat Produk</a>
                    <a href="/form" class="about-contact-link">Hubungi Kami <span aria-hidden="true">&rarr;</span></a>
                </div>
            </div>
            <figure class="about-hero__visual">
                <img src="/img/Login.webp" alt="Rak penyimpanan dalam penataan ruang rumah" width="1000" height="1000" decoding="async">
            </figure>
        </section>

        <dl class="about-stats" aria-label="Sekilas Lunarea">
            <div><dt>Mulai hadir</dt><dd>1989</dd></div>
            <div><dt>Tahun pengalaman</dt><dd>30+</dd></div>
            <div><dt>Distributor &amp; toko</dt><dd>100+</dd></div>
        </dl>

        <section class="about-story" aria-labelledby="about-story-title">
            <div>
                <p class="about-eyebrow">Sejarah perusahaan</p>
                <h2 id="about-story-title">Mengenal Lunarea Furniture.</h2>
            </div>
            <div class="about-story__text">
                <p>Lunarea Furniture hadir sejak tahun 1989 sebagai produsen sekaligus distributor aneka perabotan rumah yang berpengalaman. Selama lebih dari tiga puluh tahun, Lunarea konsisten memproduksi perabotan mulai dari meja, kursi, lemari, dan rak dengan kualitas terbaik.</p>
                <p>Produk kami dibuat dari material pilihan dan dikerjakan oleh tim yang berpengalaman. Saat ini, Lunarea telah memiliki ratusan distributor dan toko ternama yang tersebar di berbagai kota di Indonesia.</p>
                <p>Untuk semakin memudahkan aktivitas belanja konsumen, Lunarea juga hadir di berbagai platform marketplace ternama di Indonesia.</p>
            </div>
        </section>

        <section class="about-values" aria-labelledby="about-values-title">
            <h2 id="about-values-title">Yang kami utamakan.</h2>
            <div class="about-values-list">
                <article class="about-value">
                    <span class="about-value-number" aria-hidden="true">01</span>
                    <h3>Kualitas terjaga</h3>
                    <p>Produk dibuat dengan standar material dan proses produksi yang konsisten.</p>
                </article>
                <article class="about-value">
                    <span class="about-value-number" aria-hidden="true">02</span>
                    <h3>Desain fungsional</h3>
                    <p>Furniture dirancang untuk kebutuhan rumah modern yang praktis dan nyaman.</p>
                </article>
                <article class="about-value">
                    <span class="about-value-number" aria-hidden="true">03</span>
                    <h3>Mudah dijangkau</h3>
                    <p>Tersedia melalui toko, distributor, dan marketplace agar lebih mudah ditemukan.</p>
                </article>
            </div>
        </section>

        <blockquote class="about-quote">
            <p>“Lunarea furniture, always your choice, always your furniture.”</p>
        </blockquote>
    </div>
</main>
<?= $this->endSection(); ?>
