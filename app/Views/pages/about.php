<?= $this->extend("layout/template"); ?>
<?= $this->section("content"); ?>
<div class="konten">
    <div class="container mb-3">
        <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/">Beranda</a></li>
                <li class="breadcrumb-item active" aria-current="page">tentang</li>
            </ol>
        </nav>
        <h1>
            Sejarah Perusahaan
        </h1>
        <h5 class="mb-5">Lunarea Furniture</h5>
        <div class="baris-ke-kolom-reverse align-items-center justify-content-between">
            <div style="height: fit-content; flex: 2;">
                <p>
                    Lunarea furniture hadir sejak tahun 1989 sebagai produsen sekaligus distributor aneka perabotan rumah yang berpengalaman. Selama lebih dari tiga puluhan tahun yang lalu hingga sekarang, Lunarea Furniture dengan konsisten memproduksi perabotan mulai dari meja, kursi, lemari dan rak dengan kualitas terbaik yang terbuat dari material terbaik yang dikerjakan secara professional oleh tim terbaik kami.
                </p>
                <p>
                    Saat ini kami telah memiliki ratusan distributor dan toko ternama, yang tersebar di seluruh penjuru kota di Indonesia.
                </p>
                <p>
                    Untuk semakin memudahkan aktivitas belanja konsumen, kami melakukan pengembangan dan hadir di berbagai platform marketplace ternama di Indonesia.
                </p>
                <p>
                    Temukan produk furniture kebutuhan Anda dengan harga terbaik dan desain stylish masa kini, di toko terdekat Anda bersama dengan Lunarea furniture.
                </p>
                <p class="fw-bold" style="color: var(--hijau)">
                    <i>Lunarea furniture, always your choice, always your furniture</i>
                </p>
            </div>
            <div class="show-ke-hide align-items-center justify-content-end" style="height: 350px; flex: 1;">
                <div style="
                        height: 100%;
                        width: 100%;
                        display: grid;
                        gap: 1em;
                        grid-template-areas: 'gmbr1 gmbr3' 'gmbr2 gmbr3' 'gmbr2 gmbr4';
                    ">
                    <div style="
                            grid-area: gmbr1;
                            border-radius: 1em;
                            overflow: hidden;
                        ">
                        <img style="height: 100%; width: 100%; object-fit: cover" src="/img/header/header_comp3.webp" />
                    </div>
                    <div style="
                            grid-area: gmbr2;
                            border-radius: 1em;
                            overflow: hidden;
                        ">
                        <img style="height: 100%; width: 100%; object-fit: cover" src="/img/header/header_comp1.webp" />
                    </div>
                    <div style="
                            grid-area: gmbr3;
                            border-radius: 1em;
                            overflow: hidden;
                        ">
                        <img style="height: 100%; width: 100%; object-fit: cover" src="/img/header/header_comp2.webp" />
                    </div>
                    <div style="
                            grid-area: gmbr4;
                            border-radius: 1em;
                            overflow: hidden;
                        ">
                        <img style="height: 100%; width: 100%; object-fit: cover" src="/img/header/header_comp4.webp" />
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>