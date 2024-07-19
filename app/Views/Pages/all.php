<?= $this->extend('layout/template'); ?>
<?= $this->section('content'); ?>
<div class="konten">
    <div class="container">
        <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/">Beranda</a></li>
                <li class="breadcrumb-item" aria-current="page">Produk</li>
                <?php if ($nama) { ?>
                    <li class="breadcrumb-item active"><?= $nama ?></li>
                <?php } else { ?>
                    <?php if ($kategori) { ?>
                        <li class="breadcrumb-item active"><?= str_replace('-', ' ', ucfirst($kategori)) ?></li>
                    <?php } else { ?>
                        <li class="breadcrumb-item active">All</li>
                    <?php } ?>
                <?php } ?>
            </ol>
        </nav>
    </div>
    <div class="container my-3">
        <h5 class="jdl-section">Kategori</h5>
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="mb-1">Kategori Produk</h1>
            <div class="gap-2 show-flex-ke-hide">
                <div class="btn-primary1 btn-geser-kategori" id="kiri" style="padding: 0.5em; border-radius: 1.5em; cursor: default;"><i class="material-icons">chevron_left</i></div>
                <div class="btn-primary1 btn-geser-kategori" id="kanan" style="padding: 0.5em; border-radius: 1.5em; cursor: default;"><i class="material-icons">chevron_right</i></div>
            </div>
        </div>
        <div class="container-kategori-scroll">
            <div class="container-kategori">
                <a class="kategori" href="/all/lemari-dewasa">
                    <img src="/img/logokategori/Lemari_Dewasa.webp" alt="lemnari dewasa" width="50px">
                    <p>Lemari Dewasa</p>
                </a>
                <a class="kategori" href="/all/lemari-anak">
                    <img src="/img/logokategori/Lemari_Anak.webp" alt="lemari anak" width="50px">
                    <p>Lemari Anak</p>
                </a>
                <!--<a class="kategori" href="/all/lemari-hias">-->
                <!--    <img src="/img/logokategori/Lemari_Hias.webp" alt="lemari hias" width="50px">-->
                <!--    <p>Lemari Hias</p>-->
                <!--</a>-->
                <a class="kategori" href="/all/meja-rias">
                    <img src="/img/logokategori/Meja_Rias.webp" alt="" width="50px">
                    <p>Meja Rias</p>
                </a>
                <a class="kategori" href="/all/meja-belajar">
                    <img src="/img/logokategori/Meja_Belajar.webp" alt="meja belajar" width="50px">
                    <p>Meja Belajar</p>
                </a>
                <a class="kategori" href="/all/meja-tv">
                    <img src="/img/logokategori/Meja_TV.webp" alt="meja tv" width="50px">
                    <p>Meja TV</p>
                </a>
                <a class="kategori" href="/all/meja-tulis">
                    <img src="/img/logokategori/Meja_Tulis.webp" alt="meja tulis" width="50px">
                    <p>Meja Tulis</p>
                </a>
                <a class="kategori" href="/all/meja-komputer">
                    <img src="/img/logokategori/Meja_Komputer.webp" alt="meja kompuiter" width="50px">
                    <p>Meja Komputer</p>
                </a>
                <a class="kategori" href="/all/rak-sepatu">
                    <img src="/img/logokategori/Rak_Sepatu.webp" alt="rak sepatu" width="50px">
                    <p>Rak Sepatu</p>
                </a>
                <a class="kategori" href="/all/rak-besi">
                    <img src="/img/logokategori/Rak_Besi.webp" alt="rak besi" width="50px">
                    <p>Rak Besi</p>
                </a>
                <a class="kategori" href="/all/rak-serbaguna">
                    <img src="/img/logokategori/Rak_Serbaguna.webp" alt="rak serbaguna" width="50px">
                    <p>Rak Serbaguna</p>
                </a>
                <a class="kategori" href="/all/kursi">
                    <img src="/img/logokategori/Kursi.webp" alt="kursi" width="50px">
                    <p>Kursi</p>
                </a>
            </div>
        </div>
    </div>
    <div class="container my-3">
        <?php if ($nama) { ?>
            <h5 class="mb-3">Anda mencari "<?= $nama ?>"</h5>
        <?php } else { ?>
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="jdl-section">
                    <?= $kategori ? str_replace('-', ' ', ucfirst($kategori)) : "Semua Kategori"; ?>
                </h5>
                <?php if ($kategori) { ?>
                    <a class="btn btn-primary1" href="/all">Semua Kategori</a>
                <?php } ?>
            </div>
        <?php } ?>

        <?php if (count($produk) <= 0) { ?>
            <p class="m-0 text-center">Barang tidak ditemukan</p>
        <?php } else { ?>
            <div class="card-group1 no-scroll mb-3">
                <?php foreach ($produk as $p) { ?>
                    <a class="card1" href="/product/<?= urlencode($p['nama']); ?>">
                        <?php if ($p['diskon']) { ?>
                            <p class="diskon">-<?= number_format((float)$p['diskon'], 2, '.', ''); ?>%</p>
                        <?php } ?>
                        <div style="position: relative; width: 100%; aspect-ratio: 1 / 1;">
                            <img class="img-card1-wm" src="<?= base_url('img/WM Black 300.webp'); ?>" alt="">
                            <img class="img-card1" src="data:image/webp;base64,<?= base64_encode($p['gambar']); ?>" alt="">
                        </div>
                        <div>
                            <h5 class="mb-0"><?= $p['nama']; ?></h5>
                            <div class="container-varian">
                                <?php foreach (json_decode($p['varian'], true) as $v) { ?>
                                    <p class="mb-0 varian"><?= $v ?></p>
                                <?php } ?>
                            </div>
                            <?php if ($p['diskon']) { ?>
                                <span class="d-flex gap-1 align-items-center">
                                    <p class="mb-0 diskon-coret" style="text-decoration: line-through; color: grey; width:fit-content;">
                                        Rp
                                        <?= number_format($p['harga'], 0, ",", "."); ?></p>
                                    <p class="diskon-bwh">-<?= (int)$p['diskon']; ?>%</p>
                                </span>
                                <p class="mb-0 harga">Rp
                                    <?php
                                    $persen = (100 - $p['diskon']) / 100;
                                    $hasil = $persen * $p['harga'];
                                    echo number_format($hasil, 0, ",", ".");
                                    ?></p>
                            <?php } else { ?>
                                <p class="mb-0 harga">Rp <?= number_format($p['harga'], 0, ",", "."); ?></p>
                            <?php } ?>
                            <!-- <p>★★★☆☆ (<?= $p['rate']; ?>)</p> -->
                        </div>
                    </a>
                <?php } ?>
            </div>
        <?php } ?>

        <?php if (count($semuaProduk) > count($produk)) { ?>
            <nav aria-label="Page navigation example">
                <?php if ($nama) { ?>
                    <ul class="pagination justify-content-center">
                        <?php if ((int)$page > 1) { ?>
                            <li class="page-item">
                                <a class="page-link text-dark" href="/find/<?= str_replace(" ", "-", $nama); ?>/<?= (int)$page - 1; ?>" aria-label="Previous">
                                    <span aria-hidden="true">&laquo;</span>
                                </a>
                            </li>
                        <?php }
                        $hitungGrupMax = ceil(count($semuaProduk) / 20);
                        for ($x = 1; $x <= $hitungGrupMax; $x++) {
                        ?>
                            <li class="page-item"><a class="page-link <?= $x == $page ? "aktif" : "" ?>" href="/find/<?= str_replace(" ", "-", $nama); ?>/<?= $x; ?>"><?= $x; ?></a></li>
                        <?php } ?>
                        <?php if ((int)$page < $hitungGrupMax) { ?>
                            <li class="page-item">
                                <a class="page-link text-dark" href="/find/<?= str_replace(" ", "-", $nama); ?>/<?= (int)$page + 1; ?>" aria-label="Next">
                                    <span aria-hidden="true">&raquo;</span>
                                </a>
                            </li>
                        <?php } ?>
                    </ul>
                <?php } else { ?>
                    <ul class="pagination justify-content-center">
                        <?php if ((int)$page > 1) { ?>
                            <li class="page-item">
                                <a class="page-link text-dark" href="/page/<?= $kategori ? ((int)$page - 1) . "/" . $kategori : ((int)$page - 1); ?>" aria-label="Previous">
                                    <span aria-hidden="true">&laquo;</span>
                                </a>
                            </li>
                        <?php }
                        $hitungGrupMax = ceil(count($semuaProduk) / 20);
                        for ($x = 1; $x <= $hitungGrupMax; $x++) {
                        ?>
                            <li class="page-item"><a class="page-link <?= $x == $page ? "aktif" : "" ?>" href="/page/<?= $kategori ? $x . "/" . $kategori : $x; ?>"><?= $x; ?></a></li>
                        <?php } ?>
                        <?php if ((int)$page < $hitungGrupMax) { ?>
                            <li class="page-item">
                                <a class="page-link text-dark" href="/page/<?= $kategori ? ((int)$page + 1) . "/" . $kategori : ((int)$page + 1); ?>" aria-label="Next">
                                    <span aria-hidden="true">&raquo;</span>
                                </a>
                            </li>
                        <?php } ?>
                    </ul>
                <?php } ?>
            </nav>
        <?php } ?>
    </div>
    <?php switch ($kategori) {
        case 'rak-sepatu': ?>
            <hr class="my-5">
            <div class="container">
                <div class="my-3 container-meta">
                    <div class="overlay-meta"></div>
                    <h4 class="m--0">Apa itu Rak Sepatu?</h4>
                    <p class="text-justify">Rak sepatu menjadi salah satu perabotan rumah tangga yang harus ada di rumah. Terlebih jika memiliki banyak koleksi sneaker yang cukup banyak dan berharga. Pada umumnya, lemari rak sepatu dibuat dari bahan kayu, besi atau logam, plastik, hingga bahan lainnya. Ukuran dan desainnya pun beragam, bergantung pada kebutuhan dan penempatannya. Semisal saja, untuk penempatan di area yang rentan dengan debu akan lebih baik jika memilih rak sepatu tertutup. </p>
                    <p class="text-justify">Sedangkan untuk menyiasati space ruang terbatas, maka rak sepatu minimalis terbuka atau rak sepatu minimalis tertutup jadi opsi yang pas. Furniture minimalis pada dasarnya memegang prinsip kegunaan yang praktis dan menjawab kebutuhan. Terlebih bertambahnya koleksi sepatu yang dibarengi dengan kebutuhan, merupakan hal yang tidak bisa dihindari. Tak ada salahnya jika memutuskan untuk beli rak sepatu tertutup baru untuk mengganti atau menambah space penyimpanan sneakers jadi lebih tertata rapi dan awet di dalamnya.</p>
                    <h4 class="m--0">Tips Merawat dengan Mudah</h4>
                    <p class="text-justify">Melakukan perawatan terhadap furniture lemari rak sepatu sangat mudah. Namun perlu diperhatikan juga material bahan yang digunakan, apakah itu dari kayu, logam, atau plastik. Untuk lebih jelasnya, berikut ini beberapa perawatan mudah yang bisa dilakukan secara mandiri berdasarkan pada material bahannya:</p>
                    <h6 class="sub-1">Rak sepatu dari kayu</h6>
                    <p class="sub-1 text-justify mb-0">Bahan dari kayu sepertinya masih jadi pilihan populer diantara yang lain. Selain dari harganya yang terbilang terjangkau, kayu rak sepatu kayu memiliki warna yang menarik dan perawatan yang terbilang mudah. Cukup dengan melakukan langkah-langkah di bawah ini untuk memastikan rak sepatu dari kayu lebih awet dan tahan lama:</p>
                    <ol class="sub-2">
                        <li>Hindari dari tempat lembab</li>
                        <li>Bersihkan secara rutin dan berkala</li>
                        <li>Susun berdasarkan pada jenisnya</li>
                        <li>Bersihkan terlebih dahulu alas kaki yang telah digunakan sebelum disimpan</li>
                        <li>Jika terlihat ada jamur, bersihkan dengan segera dan tempatkan sementara di tempat yang terkena sinar matahari</li>
                    </ol>
                    <h6 class="sub-1">Rak sepatu dari logam</h6>
                    <p class="sub-1 text-justify mb-0">Merawat rak sepatu dari logam atau besi perlu memperhatikan beberapa hal khusus yang bertujuan mempertahankan keawetan dan ketahanan dari rak itu sendiri. Lebih jelasnya berikut ini adalah langkah-langkah perawatan yang bisa dilakukan:</p>
                    <ol class="sub-2">
                        <li>Melakukan pembersihan secara rutin</li>
                        <li>Menggunakan pelindung anti karat</li>
                        <li>Meminimalisir kontak dengan air</li>
                        <li>Memperhatikan beban yang ditopang rak sepatu dari logam ini</li>
                    </ol>
                    <h6 class="sub-1">Rak sepatu dari plastik</h6>
                    <p class="sub-1 text-justify mb-0">Sebenarnya perawatan untuk rak sepatu dari plastik memiliki beberapa kesamaan dari kedua bahan diatas. Namun, karena bahan plastik inilah yang membedakan dan harus jadi perhatian dalam melakukan perawatan.</p>
                    <ol class="sub-2">
                        <li>Hindari dari paparan sinar matahari langsung</li>
                        <li>Hindari bahan kimia yang bersifat keras</li>
                        <li>Jaga beban agar tidak terlalu berat</li>
                        <li>Jauhkan dari suhu yang ekstrim</li>
                    </ol>
                    <h4 class="m--0">Rekomendasi Tempat Beli Rak Sepatu Terpercaya</h4>
                    <p class="text-justify">Setelah mengetahui berbagai material dan cara perawatannya, apakah Kamu mulai tertarik untuk membeli lemari sepatu dengan berbagai macam desain dan harga terjangkau? Tenang saja, Kamu berada di tempat yang tepat! Sekarang semua cukup mudah, Kamu bisa mendapatkannya dengan aman dan nyaman dalam genggaman tangan saja hanya di Website Lunarea Furniture. Jangan lupa juga untuk perhatikan pembelian Kamu dan pastikan Kamu mendapatkan harga terbaik dan diskon menarik serta gratis ongkir untuk Pulau Jawa, Madura dan Bali!</p>
                </div>
            </div>
            <div class="d-flex justify-content-center mb-5">
                <button class="btn btn-outline-dark" onclick="openMeta(event)">Lihat selengkapnya</button>
            </div>
    <?php break;
    } ?>
</div>
<script>
    const containeKategoriScrollElm = document.querySelector('.container-kategori-scroll');
    const btnGeserKategoriElm = document.querySelectorAll('.btn-geser-kategori');

    btnGeserKategoriElm.forEach(btn => {
        btn.addEventListener('click', () => {
            const direction = btn.id === 'kiri' ? -1 : 1;
            const scrollAmount = containeKategoriScrollElm.clientWidth * direction;
            containeKategoriScrollElm.scrollBy({
                left: scrollAmount,
                behavior: "smooth"
            });
        })
    })

    let bukaMeta = false;

    function openMeta(e) {
        const containerMeta = document.querySelector('.container-meta');
        if (bukaMeta) {
            containerMeta.classList.remove('show')
            bukaMeta = false;
            e.target.innerHTML = 'Lihat selengkapnya'
        } else {
            containerMeta.classList.add('show')
            bukaMeta = true;
            e.target.innerHTML = 'Lebih sedikit'
        }
    }
</script>
<?= $this->endSection(); ?>