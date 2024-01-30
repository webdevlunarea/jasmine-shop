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
        <h5 class="mb-5">Jasmine Furniture</h5>
        <div class="baris-ke-kolom-reverse align-items-center justify-content-between">
            <div class="limapuluh-ke-seratus" style="height: fit-content;">
                <p>
                    Jasmine furniture hadir sejak tahun 1989 sebagai produsen sekaligus distributor aneka perabotan rumah yang berpengalaman. Selama lebih dari tiga puluhan tahun yang lalu hingga sekarang, Jasmine Furniture dengan konsisten memproduksi perabotan mulai dari meja, kursi, lemari dan rak dengan kualitas terbaik yang terbuat dari material terbaik yang dikerjakan secara professional oleh tim terbaik kami.
                </p>
                <p>
                    Saat ini kami telah memiliki ratusan distributor dan toko ternama, yang tersebar di seluruh penjuru kota di Indonesia.
                </p>
                <p>
                    Untuk semakin memudahkan aktivitas belanja konsumen, kami melakukan pengembangan dan hadir di berbagai platform marketplace ternama di Indonesia.
                </p>
                <p>
                    Temukan produk furniture kebutuhan Anda dengan harga terbaik dan desain stylish masa kini, di toko terdekat Anda bersama dengan Jasmine furniture.
                </p>
                <p class="fw-bold" style="color: var(--hijau)">
                    Jasmine Furniture, Always your choice, always your furniture
                </p>
            </div>
            <div class="show-flex-ke-hide limapuluh-ke-seratus align-items-center justify-content-center" style="height: 400px; min-width: 300px;">
                <div style="
                        display: grid;
                        height: 100%;
                        gap: 1em;
                        grid-template-areas: 'gmbr1 gmbr3' 'gmbr2 gmbr3' 'gmbr2 gmbr4';
                    ">
                    <div class="bg-danger" style="
                            grid-area: gmbr1;
                            border-radius: 1em;
                            overflow: hidden;
                        ">
                        <img style="height: 100%; width: 100%; object-fit: cover" src="https://plus.unsplash.com/premium_photo-1678559033839-aaf50cb4c843?q=80&w=1856&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" />
                    </div>
                    <div class="bg-danger" style="
                            grid-area: gmbr2;
                            border-radius: 1em;
                            overflow: hidden;
                        ">
                        <img style="height: 100%; width: 100%; object-fit: cover" src="https://images.unsplash.com/photo-1524758631624-e2822e304c36?q=80&w=2070&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" />
                    </div>
                    <div class="bg-danger" style="
                            grid-area: gmbr3;
                            border-radius: 1em;
                            overflow: hidden;
                        ">
                        <img style="height: 100%; width: 100%; object-fit: cover" src="https://images.unsplash.com/photo-1618220179428-22790b461013?q=80&w=1854&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" />
                    </div>
                    <div class="bg-danger" style="
                            grid-area: gmbr4;
                            border-radius: 1em;
                            overflow: hidden;
                        ">
                        <img style="height: 100%; width: 100%; object-fit: cover" src="https://images.unsplash.com/photo-1586023492125-27b2c045efd7?q=80&w=1916&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" />
                    </div>
                </div>
            </div>
            <!-- <div class="d-flex flex-wrap justify-content-center gap-3 align-items-center">
                <div class="kotak-tentang">
                    <h1 class="mb-0">40+</h1>
                    <p class="mb-0">Tahun Pengalaman</p>
                </div>
                <div class="kotak-tentang">
                    <h1 class="mb-0">100%</h1>
                    <p class="mb-0">Produk bergaransi</p>
                </div>
                <div class="kotak-tentang">
                    <h1 class="mb-0">40+</h1>
                    <p class="mb-0">Tahun Pengalaman</p>
                </div>
                <div class="kotak-tentang">
                    <h1 class="mb-0">40+</h1>
                    <p class="mb-0">Tahun Pengalaman</p>
                </div>
            </div> -->
        </div>
    </div>

    <!-- <span class="garis container show-ke-hide mb-4"></span>

    <div class="baris-ke-kolom-reverse justify-content-center align-content-center container gap-3 pb-5">
        <div><img src="" alt="" style="width: 100%" /></div>
        <div class="d-flex flex-column justify-content-center align-content-center">
            <h2>Visi</h2>
            <p>
                Lorem ipsum dolor sit amet consectetur adipisicing elit. Quo nihil
                aliquid quae quis, ipsum sapiente quidem dolorum. Libero officiis
                porro molestiae sapiente eaque molestias deserunt, sed officia
                corrupti optio corporis?
            </p>
            <p>
                Lorem ipsum dolor sit amet consectetur adipisicing elit. Quo nihil
                aliquid quae quis, ipsum sapiente quidem dolorum. Libero officiis
                porro molestiae sapiente eaque molestias deserunt, sed officia
                corrupti optio corporis?
            </p>
            <h2>Misi</h2>
            <p>
                Lorem ipsum dolor sit amet consectetur adipisicing elit. Fuga porro
                aliquam eveniet eaque, dignissimos minima. Voluptatum, repellendus et.
                Sapiente beatae earum eum sed aspernatur optio soluta vel, nesciunt
                qui! Molestias.
            </p>
        </div>
    </div> -->
</div>
<?= $this->endSection(); ?>