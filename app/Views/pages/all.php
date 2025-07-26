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
    <style>
    .container-kategori-sticky {
        position: sticky;
        top: 82px;
        background-color: white;
        z-index: 5;
    }

    @media (max-width: 600px) {
        .container-kategori-sticky {
            top: 100px;

        }
    }
    </style>
    <div class="container py-3 container-kategori-sticky">
        <h5 class="jdl-section">Kategori</h5>
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="mb-1">Kategori Produk</h1>
            <div class="gap-2 show-flex-ke-hide">
                <div class="btn-primary1 btn-geser-kategori" id="kiri"
                    style="padding: 0.5em; border-radius: 1.5em; cursor: default;"><i
                        class="material-icons">chevron_left</i></div>
                <div class="btn-primary1 btn-geser-kategori" id="kanan"
                    style="padding: 0.5em; border-radius: 1.5em; cursor: default;"><i
                        class="material-icons">chevron_right</i></div>
            </div>
        </div>
        <div class="container-kategori-scroll">
            <div class="container-kategori">
                <a class="kategori" href="/all/diskon">
                    <img src="/img/logokategori/Diskon15.webp" alt="Diskon" width="50px">
                    <p>Big Sale up to 15%</p>
                </a>
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
                <?= $kategori ? ($kategori == 'meja-tv' ? 'Meja TV' : ucwords(str_replace('-', ' ', $kategori))) : "Semua Kategori"; ?>
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
            <a class="card1" href="/product/<?= $p['path']; ?>">
                <?php if ($p['diskon']) { ?>
                <p class="diskon my-3">-<?= (int)$p['diskon']; ?>%</p>
                <?php } ?>
                <div style="position: relative; width: 100%; aspect-ratio: 1 / 1;">
                    <img class="img-card1-wm" src="<?= base_url('img/WM Black 300.webp'); ?>" alt="Watermark Lunarea">
                    <img class="img-card1" src="data:image/webp;base64,<?= base64_encode($p['gambar']); ?>"
                        alt="<?= $p['nama']; ?>">
                </div>
                <div>
                    <h5 class="mb-0"><?= $p['nama']; ?></h5>
                    <div class="container-varian my-2 pb-1">
                        <div>
                            <?php foreach (json_decode($p['varian'], true) as $v) { ?>
                            <p class="mb-0 varian"><?= $v ?></p>
                            <?php } ?>
                        </div>
                    </div>
                    <?php if ($p['diskon']) { ?>
                    <span class="d-flex gap-1 align-items-center">
                        <p class="mb-0 diskon-coret"
                            style="text-decoration: line-through; color: grey; width:fit-content;">
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
                    <a class="page-link text-dark"
                        href="/find/<?= str_replace(" ", "-", $nama); ?>/<?= (int)$page - 1; ?>" aria-label="Previous">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                </li>
                <?php }
                        $hitungGrupMax = ceil(count($semuaProduk) / 20);
                        for ($x = 1; $x <= $hitungGrupMax; $x++) {
                        ?>
                <li class="page-item"><a class="page-link <?= $x == $page ? "aktif" : "" ?>"
                        href="/find/<?= str_replace(" ", "-", $nama); ?>/<?= $x; ?>"><?= $x; ?></a></li>
                <?php } ?>
                <?php if ((int)$page < $hitungGrupMax) { ?>
                <li class="page-item">
                    <a class="page-link text-dark"
                        href="/find/<?= str_replace(" ", "-", $nama); ?>/<?= (int)$page + 1; ?>" aria-label="Next">
                        <span aria-hidden="true">&raquo;</span>
                    </a>
                </li>
                <?php } ?>
            </ul>
            <?php } else { ?>
            <ul class="pagination justify-content-center">
                <?php if ((int)$page > 1) { ?>
                <li class="page-item">
                    <a class="page-link text-dark"
                        href="/page/<?= $kategori ? ((int)$page - 1) . "/" . $kategori : ((int)$page - 1); ?>"
                        aria-label="Previous">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                </li>
                <?php }
                        $hitungGrupMax = ceil(count($semuaProduk) / 20);
                        for ($x = 1; $x <= $hitungGrupMax; $x++) {
                        ?>
                <li class="page-item"><a class="page-link <?= $x == $page ? "aktif" : "" ?>"
                        href="/page/<?= $kategori ? $x . "/" . $kategori : $x; ?>"><?= $x; ?></a></li>
                <?php } ?>
                <?php if ((int)$page < $hitungGrupMax) { ?>
                <li class="page-item">
                    <a class="page-link text-dark"
                        href="/page/<?= $kategori ? ((int)$page + 1) . "/" . $kategori : ((int)$page + 1); ?>"
                        aria-label="Next">
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
            <h5>Apa itu Rak Sepatu?</h5>
            <p class="text-justify">Furniture yang satu ini menjadi perabotan rumah tangga yang penting dan harus ada di
                rumah. Terlebih jika memiliki banyak koleksi sneaker yang cukup banyak dan berharga. Pada umumnya,
                lemari rak sepatu dibuat dari berbagai jenis kayu, besi atau logam, plastik, dan bahan lainnya. Ukuran
                dan desainnya pun beragam, bergantung pada kebutuhan dan penempatannya. Semisal saja, untuk penempatan
                di area yang rentan dengan debu akan lebih baik jika memilih rak sepatu tertutup.</p>
            <p class="text-justify">Untuk menyiasati <i>space</i> ruang terbatas, maka lemari sepatu minimalis terbuka
                atau rak sepatu minimalis tertutup jadi opsi yang pas. Furniture minimalis pada dasarnya memegang
                prinsip kegunaan yang praktis dan menjawab kebutuhan. Terlebih bertambahnya koleksi sepatu yang
                dibarengi dengan kebutuhan, merupakan hal yang tidak bisa dihindari. Tak ada salahnya jika memutuskan
                untuk beli lemari sepatu minimalis baru untuk mengganti atau menambah space penyimpanan sneakers jadi
                lebih tertata rapi dan awet di dalamnya.</p>

            <h5>Ini Jenis Lemari Rak Sepatu</h5>
            <p class="text-justify mb-1">Ada berbagai jenis yang bisa Kamu pilih. Pada dasarnya, pemilihan ini
                berdasarkan pada kebutuhan dan preferensi masing-masing.</p>
            <ol>
                <li>Rak sepatu terbuka</li>
                <li>Rak sepatu tertutup kaca</li>
                <li>Rak sepatu dengan kabinet</li>
                <li>Rak sepatu kayu olahan</li>
                <li>Rak sepatu minimalis</li>
                <li>Rak sepatu minimalis tertutup</li>
            </ol>

            <h5>Tips Merawat Rak Sepatu Kayu Olahan dengan Mudah</h5>
            <p class="text-justify mb-1">Melakukan perawatan terhadap furniture lemari rak sepatu sangat mudah. Kalau
                dilihat dari jenis di atas, maka material kayu olahan seperti partikel <i>board</i> jadi bahan yang
                paling sering dan populer digunakan. Selain dari harganya yang terbilang terjangkau, lemari sepatu kayu
                olahan pada dasarnya memiliki warna yang menarik dan perawatan yang terbilang mudah. Cukup dengan
                melakukan langkah-langkah di bawah ini untuk memastikannya lebih awet dan tahan lama:</p>
            <ol>
                <li>Hindari dari tempat lembab</li>
                <li>Bersihkan secara rutin dan berkala</li>
                <li>Susun berdasarkan pada jenisnya, dan usahakan sepatu atau alas kaki yang berat berada di susunan
                    terbawah serta semakin ringan beratnya di tingkat keatas</li>
                <li>Bersihkan terlebih dahulu alas kaki yang telah digunakan sebelum disimpan</li>
                <li>Jika terlihat ada jamur, langsung bersihkan dengan segera dan tempatkan sementara di tempat yang
                    terkena sinar matahari</li>
            </ol>

            <h5>Rekomendasi Tempat Beli Rak Sepatu Terpercaya</h5>
            <p class="text-justify">Setelah mengetahui berbagai jenis dan cara perawatannya, apakah Kamu mulai tertarik
                untuk beli rak sepatu dengan harga terjangkau? Atau malah sudah menetapkan hati beli rak sepatu tertutup
                tapi masih belum menemukan toko yang <i>trusted</i>?</p>
            <p class="text-justify">Tenang saja, Kamu berada di tempat yang tepat! Sekarang semua cukup mudah. Kamu bisa
                mendapatkan lemari sepatu kayu olahan dengan cepat dan aman dalam genggaman tangan saja melalui,
                <i>Website</i> Lunarea Furniture.
            </p>
            <p class="text-justify">Jangan lupa juga untuk perhatikan pembelian dan pastikan Kamu mendapatkan lemari
                sepatu minimalis dengan harga terbaik dan diskon menarik serta <b>gratis ongkir</b> untuk Pulau Jawa,
                Madura dan Bali lho!</p>
        </div>
    </div>
    <div class="d-flex justify-content-center mb-5">
        <button class="btn btn-outline-dark" onclick="openMeta(event)">Lihat selengkapnya</button>
    </div>
    <?php break;
        case 'lemari-dewasa': ?>
    <hr class="my-5">
    <div class="container">
        <div class="my-3 container-meta">
            <div class="overlay-meta"></div>
            <h5>Kenapa Harus Punya Lemari Pakaian?</h5>
            <p class="text-justify">Sebagai salah satu furniture penting yang ada di rumah, lemari memegang peran
                penting untuk memastikan barang di dalamnya tertata rapi. Lemari bisa ditempatkan pada bagian rumah di
                mana saja tergantung dari jenis penyimpanannya. Sesuai dengan namanya, lemari pakaian biasanya di kamar
                tidur sebagai tempat penyimpanan pakaian. Tapi tidak menutup kemungkinan juga lemari digunakan untuk
                menyimpan keperluan lain dan bisa ditempatkan secara fleksibel di ruangan lainnya.</p>
            <p class="text-justify">Kalau ruangan kamu tergolong terbatas dan menginginkan kesan luas, lemari pakaian
                minimalis jadi opsi terbaik untuk dipilih. Modelnya yang simpel dan tidak banyak aksen menjadikan lemari
                baju minimalis menyamarkan kesan sesak dalam ruangan. Desain lemari kayu minimalis olahan yang seperti
                inilah yang sedang banyak jadi furniture populer yang dengan mudah ditemukan di berbagai toko
                <i>offline</i> dan <i>online</i>. Lemari pakaian minimalis modern minimalis terbaru hadir dengan
                berbagai jenis mulai dari lemari dengan 2 pintu, lemari kayu 3 pintu, hingga 4 pintu.
            </p>

            <h5>Jenis Lemari Pakaian</h5>
            <p class="text-justify">Banyaknya minat masyarakat terhadap lemari pakaian minimalis, membuat para pengrajin
                terus menerus melakukan inovasi lemari pakaian minimalis modern terbaru baik dari segi desain dan
                jenisnya. Nah, apa saja ya kira-kira? sudahkah Kamu memiliki salah satu atau beberapa jenis lemari
                pakaian ini?</p>
            <div class="sub-1">
                <p class="fw-bold mb-1">1. Lemari Pakaian 2 Pintu</p>
                <p class="text-justify">Lemari pakaian 2 pintu adalah lemari pakaian yang dilengkapi dengan 2 pintu yang
                    biasanya memuat <i>space</i> penyimpanan ruang gantung sebagai penyimpanan pakaian panjang, rak
                    untuk menyimpan pakaian lipat, dan laci untuk menyimpan barang berharga.desain sederhana dari lemari
                    ini jadi pilihan yang cocok jika Kamu membutuhkan penyimpanan lengkap dengan ukuran yang tidak
                    terlalu besar.</p>
                <p class="fw-bold mb-1">2. Lemari Pakaian 3 Pintu</p>
                <p class="text-justify">Hampir sama yang diatas, lemari pakaian 3 pintu dilengkapi dengan <i>space</i>
                    ruang berupa area gantungan, rak, dan laci. Hanya saja yang membedakannya adalah ukuran serta jumlah
                    penyimpanan yang tentunya lebih komprehensif. Untuk menunjang kualitas akan lebih baik jika
                    memastikan yang Kamu pilih adalah lemari kayu 3 pintu. Material kayu sudah dikenal dengan
                    keawetannya dan keindahan khas yang tercipta dari tekstur kayu dengan dipertegas dengan
                    finishingnya.</p>
            </div>

            <h5>Model Pintu Lemari Pakaian</h5>
            <p class="text-justify">Sedangkan jika dilihat dari model lemari kayu minimalis olahan, kamu mungkin sudah
                tidak asing lagi dengan lemari pintu <i>swing</i> atau engsel dan lemari <i>sliding</i> alias lemari
                geser Lemari pakaian pintu swing mempunyai lebih mudah diakses karena pintunya bisa dibuka lebar, cocok
                untuk ruangan yang lega. Namun, pintu lemari jenis ini membutuhkan ruang tambahan di depan lemari untuk
                membuka pintu, yang akan jadi masalah jika ditempatkan pada ruang sempit. Selain itu, engselnya juga
                bisa lebih cepat aus.</p>
            <p class="text-justify">Berbanding terbalik dengan lemari pintu <i>sliding</i> sangat efisien dalam
                penggunaan ruang, ideal untuk ruang terbatas dengan desain modern. Pintu lemari <i>sliding</i>
                memberikan tampilan minimalis dan aman dari benturan, tetapi akses ke isi lemari lebih terbatas.
                Mekanisme <i>sliding</i> memerlukan perawatan rutin, dan pemasangannya lebih kompleks serta umumnya
                lebih mahal dibandingkan dengan pintu <i>swing</i>.</p>

            <h5>Segini Harga Lemari Pakaian Minimalis Modern</h5>
            <p class="text-justify">Walaupun diincar banyak orang, tapi tidak perlu khawatir karena lemari baju
                minimalis yang jadi idaman Kamu ini dijual dengan harga yang kompetitif lho! Maka dari itu, cukup dengan
                sesuaikan kebutuhan dan <i>budget</i> yang kamu miliki untuk mendapatkannya. Harga lemari baju juga bisa
                naik atau turun bergantung pada material bahan yang digunakan. Ada banyak sekali toko baik itu berbentuk
                <i>offline</i> atau <i>online</i> yang jual lemari pakaian dan menawarkan berbagai desain, keunggulan,
                dan harga lemari baju yang beragam. Maka dari itu, sebelum memutuskan membeli lemari baju, baiknya
                lakukan riset sederhana seperti bertanya pada orang-orang terpercaya, atau ngepoin marketplace sampai
                website toko <i>furniture online</i>. Kalau kamu bingung, website Lunarea Furniture bisa dengan mudah
                Kamu akses dengan mudah untuk tahu desain, material, dan harga lemari pakaian minimalis modern yang
                cocok buat Kamu.
            </p>
            <p class="text-justify">Kabar gembiranya, Lunarea Furniture yang jual lemari pakaian plus lagi ngadain
                <b>gratis ongkir</b> untuk pengiriman di pulau Jawa, Madura dan Bali nih! Kapan lagi? Yuk sekarang aja,
                sebelum kehabisan promonya!
            </p>
        </div>
    </div>
    <div class="d-flex justify-content-center mb-5">
        <button class="btn btn-outline-dark" onclick="openMeta(event)">Lihat selengkapnya</button>
    </div>
    <?php break;
        case 'meja-komputer': ?>
    <hr class="my-5">
    <div class="container">
        <div class="my-3 container-meta">
            <div class="overlay-meta"></div>
            <h5>Tips Memilih Meja Komputer</h5>
            <p class="text-justify">Komputer merupakan salah satu perangkat elektronik yang di zaman serba digital ini
                banyak dimiliki oleh orang-orang di rumah untuk membuat aktivitas kerja di meja kerja komputer jadi
                nyaman, efisien dan lebih efektif. Apalagi kebutuhan komputer meja di masa sekarang yang bisa dikatakan
                sebagai kebutuhan primer manusia modern. Maka tak heran jika membeli meja kayu komputer sangat penting
                dan tidak boleh sembarangan. Ada beberapa hal yang perlu diperhatikan yaitu sebagai berikut ini:</p>
            <ul class="mb-2">
                <li>Pilih meja PC yang dilengkapi dengan rak atau laci untuk <i>keyboard</i> dan CPU, dan <i>sliding</i>
                    laci untuk tempat <i>keyword</i>.</li>
                <li>Pilih meja yang cocok dengan preferensi pribadimu.</li>
                <li>Cek fitur yang ada pada meja komputer. Sebaiknya Kamu memilih meja yang dilengkapi dengan fitur
                    tambahan seperti tempat meletakkan <i>scanner</i>, <i>printer</i>, buku, dokumen, alat tulis, dan
                    lain sebagainya berdasarkan kebutuhan.</li>
            </ul>

            <h5>Jenis-jenis Meja Komputer</h5>
            <p class="text-justify">Seiring dengan banyaknya orang yang beraktivitas di depan komputer meja, maka akan
                sebanding juga dengan permintaan pasar.</p>
            <ul>
                <li>Meja komputer minimalis</li>
                <li>Meja PC</li>
                <li>Meja komputer dan <i>printer</i></li>
            </ul>

            <h5>Rekomendasi Material Berkualitas</h5>
            <p class="text-justify">Rekomendasi kali ini diperuntukan untuk kaum mendang-mending yang butuh komputer
                meja berkualitas dengan harga bersahabat. Pilihan tepat jatuh ke material kayu olahan yang sangat bisa
                diandalkan untuk membuat furniture yang mudah dibentuk dan punya banyak pilihan finishing. dengan
                banyaknya pilihan finishing inilah yang membuat banyak dijumpai meja komputer minimalis dengan warna
                hitam, putih, abu-abu atau meja komputer kayu yang sebenarnya hanya finishingnya saja.</p>

            <h5>Harga Meja Komputer</h5>
            <p class="text-justify">Nah, saatnya membahas hal <i>budgeting</i> agar tidak banyak memakan anggaran tak
                terduga. Maka dari itu, agar tidak kejadian seperti ini, Kamu bisa buat perencanaan biaya dikeluarkan
                untuk membeli meja PC idamanmu. Tenang saja, kalau sedang mencari meja kayu komputer harga yang cukup
                terjangkau, maka Kamu berada di tempat yang tepat! Lunarea Furniture menjual berbagai furniture seperti
                meja komputer minimalis, meja komputer dan <i>printer</i>, meja kerja komputer, dan meja lainnya yang
                Anda butuhkan.</p>
            <p class="text-justify">Tak perlu khawatir soal harga meja komputer, karena Lunarea menjual meja komputer
                kayu olahan furniture dengan harga bersahabat namun dengan kualitas produk yang berkualitas.</p>
            <p class="text-justify">Dapatkan <b>gratis ongkir sebesar 100%</b> untuk pengiriman di pulau Jawa, Madura,
                dan Bali! Untuk Kamu yang berada diluar ketiga pulau tersebut jangan bersedih hati, karena ada potongan
                ongkir yang menguntungkan buat Kamu!</p>
            <p class="text-justify">Buruan dapatkan produknya sekarang dan lengkapi furniture rumah Kamu bersama Lunarea
                Furniture.</p>
        </div>
    </div>
    <div class="d-flex justify-content-center mb-5">
        <button class="btn btn-outline-dark" onclick="openMeta(event)">Lihat selengkapnya</button>
    </div>
    <?php break;
        case 'meja-tulis': ?>
    <hr class="my-5">
    <div class="container">
        <div class="my-3 container-meta">
            <div class="overlay-meta"></div>
            <h5>Kenapa Harus Beli Meja Kerja?</h5>
            <p class="text-justify">Meja tulis merupakan salah satu furniture wajib yang ada di rumah atau kantor. Sama
                seperti namanya, meja jenis ini dibuat secara khusus untuk meningkatkan produktivitas dan kenyamanan.
                Faktor inilah yang diharapkan dapat membuat seseorang bisa menyelesaikan pekerjaannya lebih efektif dan
                efisien. Desain meja kerja kantor ini juga ditentukan oleh kebutuhan. Keberagaman desain ini merupakan
                jawaban atas beragamnya kebutuhan setiap orang masing-masing.</p>

            <h5>Material Pembuatan Meja Kerja Kantor</h5>
            <p class="text-justify">Bahan material untuk pembuatan meja tulis sekarang-sekarang ini didominasi oleh
                bahan dari kayu olahan. Dengan keunggulannya yaitu tekstur yang mudah dibentuk, punya banyak pilihan
                finishing, dan harganya yang lebih terjangkau. Hal inilah yang membuat Kamu akan lebih mudah menjumpai
                meja kantor berbahan <i>particle board</i> di pasaran. Karenanya semakin banyak permintaan pasar, akan
                membuat jumlah produksi dari meja kerja kayu olahan yang banyak pula.</p>

            <h5>Jenis-jenis Meja kerja Kantor</h5>
            <p class="text-justify mb-1">Setelah tahu material pembuatannya, sekarang ini kita bakal ngulik desain meja
                kantor yang bisa jadi pilihan Kamu.</p>
            <ul>
                <li>Meja kerja minimalis</li>
                <li style="list-style-type: none;">Jenis meja minimalis yang difungsikan untuk menyelesaikan pekerjaan
                    ini biasanya didesain <i>simple</i> dengan sedikit aksen dan finishing warna netral seperti abu-abu,
                    putih, hitam, hingga coklat. Meja kantor minimalis dengan desain sederhana yang fungsional,
                    merupakan pilihan yang cocok untuk berbagai keperluan di lingkungan kerja.</li>
                <li>Meja tulis</li>
                <li style="list-style-type: none;">Meja yang dirancang khusus untuk digunakan karyawan saat melakukan
                    aktivitas kerja ini biasanya dilengkapi dengan laci, atau lubang kecil sebagai tempat menata kabel.
                </li>
            </ul>

            <h5>Rekomendasi Meja Kerja Kayu Olahan Terjangkau</h5>
            <p class="text-justify">Nah jika desain dan jenisnya sudah kita ketahui, sekarang saatnya Kamu memutuskan
                harus beli meja kerja bukan? Jika masih ragu dan belum menentukan meja mana yang akan dibeli, mimin ada
                rekomendasi nih! Coba kunjungi Lunarea Furniture dan dapatkan produk meja kerja minimalis idamanmu di
                sini! Menariknya, harga meja kantor di website Lunarea Furniture hanya ratusan ribu aja lho! Nggak
                sampai disitu saja, tapi ada <b>gratis pengiriman ke pulau Jawa, Madura dan Bali!</b></p>
            <p class="text-justify">Buat yang diluar ketiga pulau tadi jangan khawatir, Lunarea bakal kasih kamu
                <b>subsidi ongkir</b> yang nggak tanggung-tanggung agar kamu dapatin harga meja kantor terbaik buat
                Kamu.
            </p>
            <p class="text-justify">Tunggu apa lagi, <i>check out</i> meja kantor minimalis sekarang juga dan dapatkan
                keuntungannya segera!</p>
        </div>
    </div>
    <div class="d-flex justify-content-center mb-5">
        <button class="btn btn-outline-dark" onclick="openMeta(event)">Lihat selengkapnya</button>
    </div>
    <?php break;
        case 'meja-tv': ?>
    <hr class="my-5">
    <div class="container">
        <div class="my-3 container-meta">
            <div class="overlay-meta"></div>
            <h5>Ini Alasannya Perlu Meja TV di Rumah!</h5>
            <p class="text-justify">Televisi sudah jadi barang elektronik yang dirasa wajib ada di setiap rumah.
                Kegunaannya sebagai media mencari hiburan baik itu saat sendiri atau bersama orang-orang tersayang
                menjadikan barang elektronik selalu eksis hingga sekarang. Maka dari itu, untuk melengkapi momen
                menonton TV jadi lebih asyik, diperlukan <i>space</i> khusus berupa Meja TV tentunya. Tak hanya itu
                saja, furniture meja yang satu ini juga punya fungsi lain sebagai tempat penyimpanan peralatan pendukung
                nonton TV seperti DVD <i>player</i>, PS <i>player</i>, WiFi, dan masih banyak lagi. Karena saking
                multifungsinya, furniture meja tempat TV ini punya sebutan lain di masyarakat seperti rak TV,
                <i>buffet</i> TV, credenza TV, dan sebutan lain yang sebenarnya jika dilihat dari fungsi tetap sama.
            </p>

            <h5>Desain Meja TV Minimalis</h5>
            <p class="text-justify">Ngomong desain, ada berbagai jenis model yang bisa Kamu pilih lho! Terlebih jika
                pilihanmu tertuju dengan model meja tv minimalis terbaru yang cantik dan kalem dengan warna-warna
                netralnya. Meja TV minimalis kayu olahan dari <i>particle board</i> yang <i>eye catching</i> ini lebih
                mudah dibentuk. Terlebih jika menggunakan <i>paper</i> sebagai finishing akhirnya, yang memanjakan mata
                dengan warna-warna cantik dari rak TV minimalis. Serba serbi minimalis memang jadi primadona bagi banyak
                orang, yang mungkin kamu salah satunya. Selain desain yang cantik, kebutuhan furniture yang pas dengan
                <i>space</i> terbatas jadi alasan mengapa meja TV minimalis kayu olahan jadi pilihan terbaik saat ini
                buat orang-orang.
            </p>

            <h5>Tips Jitu Anti Gagal Beli Rak TV</h5>
            <p class="text-justify mb-1">Semakin kesini, Kamu mungkin akan makin bimbang dengan berbagai model variatif
                meja tv minimalis terbaru ini bisa saja buat kamu mudah goyah menentukan harus beli meja TV mana yah?
                Nah sekarang saatnya keluarkan jurus jitu membeli rak TV yang anti gagal!</p>
            <div class="d-flex">
                <div style="width: 20px;">1.</div>
                <div style="flex: 1">
                    <p class="text-justify m-0">Tentukan kebutuhan seperti apa yang harus Kamu penuhi</p>
                </div>
            </div>
            <div class="d-flex">
                <div style="width: 20px;"></div>
                <div style="flex: 1">
                    <p class="text-justify">Seperti apa yah kira-kira <i>buffet</i> TV yang cocok sesuai kebutuhanmu?
                        Mulai dari ukuran <i>space</i> ruangan, selain TV adakah yang akan disimpan di area itu? Misal
                        saja pilihan itu jatuh pada meja TV minimalis kayu dari <i>particle board</i> atau lain
                        sebagainya. Buat secara rinci kebutuhan kamu ya!</p>
                </div>
            </div>
            <div class="d-flex">
                <div style="width: 20px;">2.</div>
                <div style="flex: 1">
                    <p class="text-justify m-0">Tentukan <i>Budget</i></p>
                </div>
            </div>
            <div class="d-flex">
                <div style="width: 20px;"></div>
                <div style="flex: 1">
                    <p class="text-justify">Hal yang berkaitan dengan keuangan biasanya bakal jadi sesuatu sensitif yang
                        salah gerak sekali saja bisa membuyarkan segalanya. Nah, setelah mendetailkan kebutuhan kamu
                        perlu membuat estimasi <i>budget</i> yang akan dikeluarkan untuk membeli furniture meja yang
                        satu ini. Ingat ya, Harga rak TV minimalis modern sangat beragam yang dengan mudah bisa
                        ditemukan dari meja TV minimalis modern murah hingga yang termahal tentunya.</p>
                </div>
            </div>
            <div class="d-flex">
                <div style="width: 20px;">3.</div>
                <div style="flex: 1">
                    <p class="text-justify m-0">Pilih desain yang Sesuai</p>
                </div>
            </div>
            <div class="d-flex">
                <div style="width: 20px;"></div>
                <div style="flex: 1">
                    <p class="text-justify">Jika 2 poin diatas sudah terpenuhi, maka yang selanjutnya dilakukan adalah
                        menentukan desain yang selaras. Selaras disini artinya sesuai dengan kebutuhan dan budget yang
                        sudah disiapkan. Akan lebih baik jika pemilihanmu jatuh pada desain meja TV minimalis modern
                        yang sedang tren sekarang ini. Tapi perlu dipertimbangkan juga kamu memilih material pilihan
                        yang bagus.</p>
                </div>
            </div>

            <h5>Ragu Beli Rak TV Minimalis Modern? Beli di Lunarea Aja!</h5>
            <p class="text-justify">Bagi kamu yang masih ragu beli furniture <i>online</i>, sekarang Kamu harus
                menghempas keraguan itu jauh-jauh. Ada Lunarea Furniture yang dijamin aman dan terpercaya mengantarkan
                meja TV minimalis modern pilihan kamu sampai di tujuan! </p>
            <p class="text-justify">Tak sampai itu saja, di sini Kamu juga bisa membeli meja TV minimalis modern murah
                namun dengan kualitas yang terbaik di kelasnya. Plus-nya lagi, ada kabar gembira buat pengiriman ke
                Pulau Jawa, Madura, dan Bali karena 100% biaya ongkir akan ditanggung oleh Lunarea Furniture Lho! Kapan
                lagi bisa dapat harga rak TV minimalis modern murah ditambah plus ongkir kalau bukan di sini.</p>
        </div>
    </div>
    <div class="d-flex justify-content-center mb-5">
        <button class="btn btn-outline-dark" onclick="openMeta(event)">Lihat selengkapnya</button>
    </div>
    <?php break;
        case 'meja-rias': ?>
    <hr class="my-5">
    <div class="container">
        <div class="my-3 container-meta">
            <div class="overlay-meta"></div>
            <h5>Alasan Kenapa Meja Rias Harus Ada Di Kamar</h5>
            <p class="text-justify">Salah satu perabotan rumah tangga yang penting walaupun bukan termasuk yang utama
                adalah meja rias. Sama seperti namanya, furniture satu ini punya fungsi yang vital terlebih buat
                perempuan. Dengan meja rias atau tolet inilah yang membuatmu tidak perlu <i>worry</i> lagi bakalan nggak
                <i>stunning</i> setiap harinya. Karena diperuntukkan secara spesial buat perempuan, tentunya kamu juga
                bisa dengan mudah menemukan berbagai jenis desain yang cantik dan menarik.
            </p>

            <h5>Dari Material Bahan Apa Meja Rias Dibuat?</h5>
            <p class="text-justify">Furniture ini kebanyakan terbuat dari bahan <i>particle board</i>. Material yang
                melimpah dan mudah dibentuk serta harga yang lebih kompetitif dari kayu solid atau bahan lain menjadi
                alasan utama mengapa bahan ini sangat populer belakangan ini. Selain itu, dengan menggunakan kayu
                olahan, juga membuat para pengrajin lebih inovatif dan kreatif mewarnai dengan berbagai jenis dan warna
                finishing untuk memproduksi meja rias minimalis modern yang jadi tren terkini. Katakanlah meja rias kayu
                modern yang didominasi warna-warna netral seperti coklat, putih, sonoma, dan lain sebagainya.</p>
            <p class="text-justify">Dengan keberagaman ini, Kamu sebagai konsumen tentu akan merasa termanjakan bukan?
                Masih belum puas dengan itu saja, jika meja rias kayu olahan minimalis masih tidak cukup di <i>space</i>
                ruang yang Kamu miliki, maka opsi selanjutnya dengan menambahkan pilihan pada meja rias minimalis kecil,
                atau meja rias simple yang kemungkinan besar sesuai dengan kebutuhan Kamu.</p>
            <p class="text-justify">Material kayu olahan menjawab kebutuhan pengrajin sebagai produsen dan Kamu sebagai
                konsumen. Maka dari itu, jangan heran ya kenapa setelah <i>search</i> sana sini partikel <i>board</i>
                selalu nampang jadi salah satu opsinya.</p>

            <h5>Berapa Estimasi Harga Meja Rias Kayu Olahan?</h5>
            <p class="text-justify">Barangkali sudah bukan rahasia umum yah kalau harga meja rias kayu modern dengan
                material <i>particle board</i> bisa dikatakan lebih miring dibanding material lain seperti kayu,
                <i>stainless</i>, dan lain sebagainya. Inilah yang membuatnya banyak diburu apalagi buat kaum mendang
                mending. Dengan kualitas yang mumpuni, <i>look</i> yang <i>eye catching</i> dan harga meja rias yang
                menawan, siapa sih yang nggak kelimpungan buat dapetin meja rias multifungsi kayak gini? Nah, tapi
                jangan asal pilih juga ya! Pastikan Kamu beli meja rias di tempat yang terpercaya agar tak menyesal
                kemudian. Salah satunya yang bakalan nggak bikin kecewa bisa Kamu dapetin lewat websitenya Lunarea
                Furniture.
            </p>
            <p class="text-justify">Pastiin kamu sudah cek dulu baru <i>check out</i> produk idaman Kamu ya! Jangan
                kelamaan <i>checking</i> sebelum kehabisan stok, mumpung ada <b>gratis ongkir</b> pengiriman di Pulau
                Jawa, Madura, dan Bali.</p>
            <p class="text-justify">Atau jika masih bimbang mau meja rias minimalis, meja rias multifungsi, meja rias
                simple, meja rias minimalis modern, meja rias minimalis kecil atau meja-meja lainnya, Kamu bisa hubungi
                <i>Customer Service</i> Lunarea yang bakal menjawab semua pertanyaanmu dengan sepenuh hati!
            </p>
        </div>
    </div>
    <div class="d-flex justify-content-center mb-5">
        <button class="btn btn-outline-dark" onclick="openMeta(event)">Lihat selengkapnya</button>
    </div>
    <?php break;
        case 'meja-belajar': ?>
    <hr class="my-5">
    <div class="container">
        <div class="my-3 container-meta">
            <div class="overlay-meta"></div>
            <h5>Apa Itu Meja Belajar?</h5>
            <p class="text-justify">Meja belajar merupakan salah satu furniture penting yang diupayakan dapat
                menciptakan lingkungan belajar yang nyaman dan produktif di rumah. Ada beberapa hal yang perlu jadi
                pertimbangan saat memilih meja belajar mana yang akan dibeli, sebut saja meja belajar minimalis, meja
                belajar <i>aesthetic</i>, meja belajar multifungsi, meja belajar simpel, dan masih banyak lagi ragamnya.
                Nah, pada dasarnya yang perlu dipilih adalah sesuai dengan kebutuhan dan mengesampingkan keinginan. Tapi
                kalau bisa keduanya, kenapa tidak? meja belajar minimalis unik mungkin bisa jadi pilihan yang pas
                untukmu yang tidak suka sesuatu monoton.</p>

            <h5>Material Bahan Pembuatannya</h5>
            <p class="text-justify">Furniture meja belajar biasanya terbuat dari material kayu atau kayu olahan seperti
                <i>particle board</i>. Tipe bahan yang mudah dibentuk, mudah ditemukan, dan harga yang lebih terjangkau
                menjadi alasan utama mengapa <i>particle board</i> sering jadi bahan pembuatan furniture. Selain itu,
                beberapa model meja juga dibuat dengan kombinasi besi pada rangka yang menjadikannya jadi lebih kuat dan
                kokoh Lho! Hal inilah yang menjadi dasar penetapan harga meja belajar kayu olahan dari bahan ini lebih
                terjangkau di pasaran dibanding dengan material lainnya.
            </p>

            <h5>Desain & Jenisnya</h5>
            <p class="text-justify mb-1">Sudah diulas sebelumnya, jika keberagaman desain ini tak luput dari beragam
                kebutuhan. Selain itu, dengan banyaknya pilihan desain ini akan memungkinkan buat Kamu lebih leluasa
                memilih sebelum membeli. Berikut ini referensinya:</p>
            <ul>
                <li>Meja belajar minimalis modern</li>
                <li style="list-style-type: none;">Segala produk yang mengklaim sebagai minimalis biasanya hadir dengan
                    warna-warna netral seperti hitam, putih, coklat, atau abu-abu dan minim aksen. Desain meja belajar
                    minimalis modern seperti ini cocok buat Kamu yang memiliki space terbatas dengan banyak kebutuhan.
                </li>
                <li>Meja belajar <i>aesthetic</i></li>
                <li style="list-style-type: none;">Kalau Kamu tipe orang yang suka dengan hal-hal yang berbau dengan
                    unsur seni, maka tak ada salahnya untuk mengaplikasikannya di ruangan belajar. Salah satunya dengan
                    menghadirkan meja belajar <i>aesthetic</i> yang mampu menjadi <i>moodbooster</i> saat sedang
                    belajar.</li>
                <li>Meja belajar simpel</li>
                <li style="list-style-type: none;">Agak berbeda dengan poin diatas, meja yang satu ini biasanya
                    diperuntukan buat orang yang males ribet dan lebih suka yang praktis serta minim aksesoris. Kalau
                    kamu salah satu orangnya, maka meja belajar simpel solusi tepatnya!</li>
                <li>Meja belajar minimalis unik</li>
                <li style="list-style-type: none;">Mungkin yang satu ini akan mirip dengan versi <i>aesthetic</i>, iya!
                    Meja belajar minimalis unik adalah jawaban tepat kalau kamu menyukai segala sesuatu yang berkaitan
                    dengan seni. Disini kamu bisa memadukan aksesoris lain untuk melengkapi tampilan jadi lebih <i>eye
                        catching</i> lagi dengan sentuhan seni yang kamu miliki!</li>
            </ul>
            <p class="text-justify">Nah kalau sudah dijabarkan begini, saatnya Kamu memutuskan meja belajar mana yang
                akan dibeli! Jika masih ragu dan bingung, kamu dapat cek produknya di website Lunarea Furniture dan
                temukan kebutuhan kamu di sini! Mau cari harga meja belajar kayu dari yang termurah pun ada. Plusnya
                lagi kamu bisa dapetin <b>gratis ongkir</b> yang beneran gratis sampai ke alamat tujuan yang berada di
                wilayah pulau Jawa, Madura, dan Bali!</p>
        </div>
    </div>
    <div class="d-flex justify-content-center mb-5">
        <button class="btn btn-outline-dark" onclick="openMeta(event)">Lihat selengkapnya</button>
    </div>
    <?php break;
        case 'rak-besi': ?>
    <hr class="my-5">
    <div class="container">
        <div class="my-3 container-meta">
            <div class="overlay-meta"></div>
            <h5>Keuntungan Punya Rak Besi Serbaguna</h5>
            <p class="text-justify">Rak besi serbaguna merupakan salah satu solusi penyimpanan dengan kegunaan yang
                beragam. Material besi yang digunakan membuat jenis furniture ini mampu menahan beban berat lebih baik
                jika dibandingkan dengan material jenis lainnya. Selain itu, rak besi juga lebih mudah dipasang dan
                disesuaikan sesuai dengan kebutuhan. Material besi yang kokoh ini membuat umur penggunaan rak besi lebih
                lama sehingga cocok digunakan sebagai investasi jangka panjang. Rak besi hadir dengan berbagai model
                dengan ukuran dan desain berbeda yang memungkinkan bagi Kamu untuk memilih rak besi susun sesuai dengan
                kebutuhan dan preferensi masing-masing.</p>

            <h5>Fungsi Rak Besi Serbaguna</h5>
            <p class="text-justify">Rak susun besi merupakan solusi optimal untuk memenuhi kebutuhan area penyimpanan di
                berbagai tempat seperti kantor, rumah, gudang, dan tempat lainnya. Material besi memang sudah dikenal
                sebagai salah satu material kokoh dan kuat yang mampu menahan beban berat dengan stabilitas tinggi.
                Selain itu, perawatan material dari besi juga tergolong lebih mudah. Pada dasarnya rak ini sangat mudah
                dibersihkan dan tahan terhadap korosi jika dibarengi dengan finishing yang pas. Sedangkan fungsi utama
                dari rak besi susun adalah sebagai tempat penyimpanan barang-barang lebih terorganisir, lebih efisien,
                dan memaksimalkan ruang yang ada. Tak hanya itu saja, rak susun besi juga hadir dengan desain modern dan
                minimalis yang <i>longlasting</i> dan tidak ketinggalan jaman dikemudian hari, seperti rak besi
                minimalis, dan rak besi bertingkat.</p>

            <h5>Tips Memilih Rak Besi Susun</h5>
            <p class="text-justify mb-1">Untuk menghindari hal-hal yang bisa membuatmu bete, inilah beberapa tips
                efektif yang bisa dilakukan sebelum membeli rak yang diinginkan:</p>
            <ul>
                <li>Pertimbangkan ketahan besi terhadap korosi dan karat</li>
                <li style="list-style-type: none;">Beberapa jenis rak besi memiliki kemungkinan untuk lebih tahan dari
                    korosi dan karat. Maka dari itu, pastikan rak yang Kamu pilih terbuat dari bahan berkualitas agar
                    tidak mudah terkena korosi dan karat</li>
                <li>Perkirakan kapasitas beban rak</li>
                <li style="list-style-type: none;">Rencanakan apa saja yang bakal Kamu simpan di rak ini, sebelum pada
                    akhirnya memutuskan untuk membeli rak. Dengan merencanakan dengan matang, Kamu bisa selektif untuk
                    memilih rak besi bertingkat yang sesuai dengan keperluan.</li>
                <li>Perhatikan ukuran dan desain</li>
                <li style="list-style-type: none;">Sesuaikan kebutuhan dengan desain yang Kamu beli. Jika tidak memiliki
                    banyak <i>space</i>, maka rak besi minimalis bisa jadi opsi terbaik yang membuat ruangan tidak
                    terlihat lebih sesak tanpa mengurangi kebutuhan penyimpanan dari rak itu sendiri. Selain itu,
                    pertimbangankan juga sistem pemasangan. Pilih rak yang mudah dirakit dan dilengkapi dengan instruksi
                    yang jelas.</li>
            </ul>

            <h5>Segini Harga Rak Besi Murah & Berkualitas</h5>
            <p class="text-justify">Nah, Kalau Kamu masih ragu berapa harga yang perlu disiapkan maka Kamu berada di
                tempat yang tepat! Karena di Lunarea Furniture, kami menjual berbagai furniture rumah tangga yang ramah
                di kantong. Bahkan jika kebutuhan rak yang Kamu perlukan tergolong banyak, harga rak besi 4 susun hanya
                mematok harga ratusan ribu saja lho!</p>
            <p class="text-justify">Temukan harga rak besi murah hingga harga rak besi 4 susun di Lunarea Furniture dan
                dapatkan <b>gratis ongkir hingga 100%</b> sekarang juga!</p>
        </div>
    </div>
    <div class="d-flex justify-content-center mb-5">
        <button class="btn btn-outline-dark" onclick="openMeta(event)">Lihat selengkapnya</button>
    </div>
    <?php break;
        case 'rak-serbaguna': ?>
    <hr class="my-5">
    <div class="container">
        <div class="my-3 container-meta">
            <div class="overlay-meta"></div>
            <h5>Apa Itu Rak Serbaguna?</h5>
            <p class="text-justify">Dari sekian banyaknya peralatan rumah tangga, rak susun serbaguna jadi furniture
                yang harus ada untuk membuat barang-barang tersimpan dengan lebih terorganisir. Nah, karena fungsi utama
                inilah, Kamu akan menemukan berbagai model rak modern dan minimalis dengan spesifikasi yang berbeda pula
                tergantung dari kebutuhan masing-masing.</p>

            <h5>Material Pembuatan Rak Serbaguna</h5>
            <p class="text-justify">Seperti yang sedikit ulasan di atas, rak susun serbaguna memiliki berbagai kegunaan.
                Hal inilah yang menjadi dasar dari jenis material apa yang akan digunakan untuk membuat sebuah rak
                penyimpanan serbaguna. Umumnya, rak serbaguna bisa terbuat dari material kayu, kayu olahan, dan besi.
                Tak menutup kemungkinan juga, rak terbuat dari paduan material seperti kayu olahan dan besi yang tidak
                hanya menambahan keindahan produk tetapi juga mempengaruhi ketahanan rak kayu serbaguna minimalis dalam
                menahan beban. Salah satu kayu olahan yang sering digunakan adalah <i>particle board</i>. Dengan
                keunggulannya yaitu mudah dibentuk, material yang melimpah serta harga yang tergolong terjangkau,
                menjadikan <i>particle board</i> material unggulan dalam pembuatan furniture salah satunya rak serbaguna
                kayu.</p>

            <h5>Barang yang Bisa disimpan dalam Rak Serbaguna Kayu Olahan</h5>
            <p class="text-justify mb-1">Tak jarang banyaknya barang-barang membuat rumah jadi terlihat berantakan dan
                tidak rapi. Maka dari itu, Kamu perlu tempat penyimpanan berupa kabinet khusus seperti rak bertingkat
                serbaguna yang akan membantu merapikan barang lebih baik. Lalu apa saja barang yang bisa Kamu simpan di
                tempat ini? Berikut barang-barangnya:</p>
            <ol class="mb-1">
                <li>Pakaian lipat</li>
                <li>Aksesoris rumah tangga</li>
                <li>Aksesoris atau mainan anak, dll</li>
            </ol>
            <p class="text-justify mb-2">Pada dasarnya semua barang yang tidak ingin Kamu pajang bisa disimpan dalam rak
                kayu serba guna ini. Tak terbatas itu saja, di atas rak serbaguna kayu olahan ini kamu juga bisa
                meletakkan beberapa aksesoris lain untuk memberikan kesan lebih cantik dalam interior ruang itu sendiri.
            </p>

            <h5>Tips Merawat Rak Penyimpanan Serbaguna</h5>
            <p class="text-justify mb-1">Pada dasarnya rak <i>portable</i> serbaguna adalah salah satu perabot rumah
                tangga yang difungsikan untuk menyimpan dan menata barang-barang jadi lebih rapi. Akan tetapi, agar rak
                kayu serba guna alias rak <i>portable</i> serbaguna bisa tetap awet dan tahan hingga waktu yang lama,
                diperlukan perawatan yang tepat. Berikut ini adalah kiat-kiat merawat rak kayu serbaguna yang benar:</p>
            <ol class="mb-2">
                <li>Lakukan pembersihan rutin</li>
                <li>Hindari terkena paparan sinar matahari secara langsung</li>
                <li>Simpan barang dengan beban paling berat di urutan terbawah dan makin ringan ke susunan tingkat atas
                </li>
                <li>Hindari meletakkan rak di tempat yang lembab</li>
                <li>Berikan tambahan alas pelindung</li>
            </ol>

            <h5>Rekomendasi Beli Rak Susun Serbaguna Murah</h5>
            <p class="text-justify">Sekarang saatnya memberikan rekomendasi tempat beli rak murah yang pastinya aman dan
                terpercaya. <i>Range</i> harga rak serba guna tergantung dari jenis material dan desainnya
                masing-masing. Karena inilah, penting sekali untuk menentukan berapa <i>budget</i> yang Kamu siapkan dan
                untuk penyimpanan apa rak ini akan digunakan. Misal, Kamu menginginkan serba-serbi produk minimalis,
                maka rak kayu serbaguna minimalis jadi pilihan yang cocok. Jika sudah, Kamu bisa memilih rak mana yang
                akan Kamu beli. Kalau ingin lebih mudah, Kamu bisa mengunjungi Lunarea Furniture dan dapatkan berbagai
                kemudahan yang pastinya nggak bikin kantong jebol!</p>
            <p class="text-justify">Walaupun harga rak serba guna terjangkau, tapi urusan kualitas boleh diadu! Mumpung
                lagi <b>banyak promo</b> plus <b>100% gratis ongkir</b> untuk wilayah Jawa, Madura dan Bali.</p>
            <p class="text-justify">Tunggu apalagi, buruan cek produk dan <i>check out</i> sekarang juga untuk dapatkan
                rak bertingkat serbaguna incaran mu!</p>
        </div>
    </div>
    <div class="d-flex justify-content-center mb-5">
        <button class="btn btn-outline-dark" onclick="openMeta(event)">Lihat selengkapnya</button>
    </div>
    <?php break;
        case 'kursi': ?>
    <hr class="my-5">
    <div class="container">
        <div class="my-3 container-meta">
            <div class="overlay-meta"></div>
            <h5>Apa Itu Kursi <i>Stainless</i>?</h5>
            <p class="text-justify">Kursi susun <i>stainless</i> merupakan salah satu pilihan favorit bagi banyak orang.
                Tidak hanya digunakan di kafe dan restoran, tetapi juga semakin bisa pula untuk di rumah-rumah pribadi.
                Desain yang sederhana membuat kursi ini cocok dalam berbagai kebutuhan yang membutuhkan banyak kursi di
                sebuah pertemuan besar. Maka dari itu, tak jarang pulang kursi kondangan ini bisa ditemui di acara
                pernikahan, rapat, pesta, dan lain sebagainya.</p>

            <h5>Bahan Pembuatan Kursi Susun <i>Stainless</i></h5>
            <h6>Kain</h6>
            <p class="text-justify">Untuk memberikan kenyamanan pada pengguna, dudukan dan sandaran kursi besi
                <i>stainless</i> dilapisi dengan busa yang kemudian dilapisi kain. Kain yang digunakan biasanya adalah
                kain poliester, yang dikenal karena kekuatan, elastisitas, dan kemampuannya untuk mempertahankan warna
                serta bentuk dalam jangka waktu lama. Poliester juga mudah dibersihkan dan dirawat, menjadikannya
                pilihan praktis untuk furniture yang sering digunakan. Kain ini tersedia dalam berbagai tekstur dan
                warna, memungkinkan desainer untuk menciptakan kursi yang tidak hanya nyaman tetapi juga estetis dan
                sesuai dengan berbagai tema dekorasi.
            </p>
            <h6>Busa</h6>
            <p class="text-justify">Busa poliuretan (PU <i>Foam</i>) adalah pilihan umum untuk bantalan dudukan dan
                sandaran kursi khususnya untuk kursi besi. Busa ini dipilih karena elastisitasnya yang baik, kemampuan
                untuk kembali ke bentuk semula setelah digunakan, serta daya tahannya yang tinggi. Busa poliuretan
                memberikan dukungan yang nyaman dan dapat menahan tekanan dalam jangka waktu lama, membuat kursi tumpuk
                <i>stainless</i> cocok untuk penggunaan jangka panjang. Penggunaan busa juga membantu mendistribusikan
                berat tubuh secara merata, mengurangi titik-titik tekanan, dan meningkatkan kenyamanan saat duduk.
            </p>
            <p class="text-justify">Gabungan antara kerangka <i>stainless steel</i> yang kokoh dan pelapis kain yang
                nyaman serta estetis menjadikan kursi susun <i>stainless</i> pilihan yang ideal untuk berbagai
                kebutuhan. <i>Stainless steel</i> memberikan struktur yang kuat dan tahan lama, sementara kain poliester
                dan busa poliuretan memastikan kenyamanan dan estetika. Kombinasi ini tidak hanya menghasilkan kursi
                yang fungsional tetapi juga menambah nilai estetika pada ruangan. Dengan pemilihan bahan yang tepat,
                kursi tumpuk <i>stainless</i> dapat digunakan di berbagai lingkungan, baik itu di rumah, kantor,
                restoran, atau kafe, dan tetap menawarkan kenyamanan serta keindahan yang tak tertandingi.</p>

            <h5>Fungsi Kursi <i>Stainless</i></h5>
            <p class="text-justify mb-1">Kursi besi merupakan salah satu kursi legendaris yang mudah dijumpai untuk
                banyak kesempatan. Berikut ini adalah fungsi dari kursi ini:</p>
            <ul class="mb-2">
                <li>Sebagai kursi pesta</li>
                <li>Sebagai kursi kantor</li>
                <li>Sebagai kursi kerja</li>
                <li>Sebagai kursi kondangan</li>
                <li>Sebagai kursi hajatan</li>
            </ul>

            <h5>Rekomendasi Tempat Beli Kursi Stainless</h5>
            <p class="text-justify">Karena banyaknya pilihan penjual kursi ini, mungkin saja bisa membuatmu dilema
                manakah toko terbaik dan terpercaya diantara banyaknya pilihan tersebut. Nah, Kamu sekarang berada di
                tempat yang tepat! Lunarea Furniture saat ini menjual jenis kursi tumpuk ini. Tersedia dalam 5 warna
                yaitu hitam, hijau, biru, merah, dan abu-abu. Sesuaikan warna dengan kebutuhan Kamu ya! Kalau Kamu
                membutuhkan untuk kursi hajatan atau kursi pesta maka warna yang cocok adalah warna hitam atau merah
                bisa Kamu pilih. Atau untuk kursi rumah makan, Kamu bisa pilih warna hijau, biru atau merah. Sedangkan
                untuk kursi kantor atau kursi kerja, Kamu bisa pilih warna hitam atau abu-abu yang tidak mencolok.</p>
            <p class="text-justify">Pilih produk kebutuhanmu dan <i>check out</i> segera untuk dapatkan <b>full gratis
                    ongkir</b> untuk pengiriman di pulau Jawa, Madura, dan Bali!</p>
        </div>
    </div>
    <div class="d-flex justify-content-center mb-5">
        <button class="btn btn-outline-dark" onclick="openMeta(event)">Lihat selengkapnya</button>
    </div>
    <?php break;
        case 'lemari-anak': ?>
    <hr class="my-5">
    <div class="container">
        <div class="my-3 container-meta">
            <div class="overlay-meta"></div>
            <h5>Kenapa Harus Punya Lemari Kecil Buat Si Buah Hati?</h5>
            <p class="text-justify"><i>Moms</i>, udah punya lemari buat buah hati belum? Nah kalau belum, yuk simak dulu
                alasannya kenapa Moms harus punya lemari kecil baju yang dikhususkan buat menyimpan koleksi pakaian si
                buah hati. Seperti lemari pakaian pada umumnya, lemari pakaian kecil ini juga memiliki fungsi yang sama,
                hanya saja ukurannya yang lebih kecil menyesuaikan kebutuhan si kecil. Memisahkan pakaian anak di lemari
                pakaian kecil juga membuat <i>Moms</i> lebih mudah saat mencari dan menata baju-baju si buah hati loh!
                Apalagi kalau si kesayangan lagi aktif-aktifnya. Pasti akan repot kalau baju-baju si kecil bercampur
                dengan baju-baju yang bukan miliknya bukan? Dengan lemari kecil baju anak ini juga secara tidak langsung
                membuat anak bertanggung jawab dengan pakaiannya sendiri. Inilah saat yang tepat untuk membuat anak
                merasa memiliki dan merawat barang-barang miliknya dengan baik yang disimpan dengan baik pula di lemari
                baju kecil miliknya.</p>

            <h5>Tips Memilih Lemari Baju Untuk Si Kecil</h5>
            <p class="text-justify">Memilih lemari baju kecil untuk menyimpan pakaian anak terkadang cukup
                <i>tricky</i>. Terlebih jika mencari desain dan tipe lemari yang bisa mendukung perkembangan si buah
                hati. Lemari minimalis kecil bisa jadi solusi yang tepat untuk penyimpanan pakaian anak. Dilengkapi
                dengan banyak rak susun dan rak gantung dengan tinggi dan ukuran lemari kecil kayu olahan yang mudah
                dijangkau anak sehingga anak tak perlu repot minta bantuan orang dewasa untuk mengambil pakaiannya.
            </p>
            <p class="text-justify">Nah, bagi <i>Moms</i> yang lagi cari-cari lemari kayu kecil minimalis anak, berikut
                ini tips lengkap memilihnya:</p>
            <ul>
                <li>Pilih lemari yang dilengkapi dengan laci</li>
                <li style="list-style-type: none;">Pemilihan lemari minimalis kecil ini membuat anak belajar melatih
                    keterampilan mengatur barang sejak dini loh <i>Moms</i>! Dengan adanya laci, akan mendorong anak
                    untuk terlibat mengatur pakaian sesuka hati dan kebutuhannya.</li>
                <li>Pastikan ada <i>space</i> gantungan dan rak</li>
                <li style="list-style-type: none;">Selain laci, hal yang perlu diperhatikan lagi adalah <i>space</i>
                    untuk gantungan baju dan ambalan rak susun untuk pakaian lipat. Sama seperti laci, dengan 2 item ini
                    diharapkan mampu melatih anak untuk mandiri dan bertanggung jawab atas pakaiannya sendiri.</li>
                <li>Perhatikan lapisan finishing lemari</li>
                <li style="list-style-type: none;">Ada banyak sekali pilihan lapisan finishing lemari baju minimalis
                    kecil yang digunakan oleh para pengrajin furniture. Misal saja untuk lemari kayu kecil, pilihan
                    lapisan finishing yang umum digunakan adalah cat kayu atau plitur. Atau jika pilihan lemari jatuh
                    pada material <i>particle board</i>, maka finishing <i>paper</i> bisa jadi pilihan populer dengan
                    berbagai pilihan warna menariknya yang mampu memikat anak-anak.</li>
                <li>Pertimbangkan ulasan pelanggan</li>
                <li style="list-style-type: none;">Hal lain yang perlu menjadi perhatian adalah bagaimana kepuasan
                    pelanggan yang telah membeli produk lemarianak tersebut. Jika dirasa memuaskan, maka <i>Moms</i>
                    bisa segera memutuskan untuk membelinya. Tapi jika <i>Moms</i> ragu setelah membaca ulasan,
                    sebaiknya <i>Moms move on</i> dari produk lemari di toko tersebut dan beralih ke toko yang lebih
                    <i>trusted</i>.
                </li>
            </ul>

            <h5>Rekomendasi Lemari Minimalis Kecil</h5>
            <p class="text-justify">Nah setelah mengetahui tips memilih lemari kecil kayu olahan, sekarang <i>Moms</i>
                sudah bisa memutuskan kira-kira lemari mana yah yang pas buat si buah hati. Misal saja, jika si kecil
                membutuhkan banyak <i>space</i> penyimpanan, maka <i>Moms</i> bisa membeli lemari kecil 2 pintu yang
                punya penyimpanan lebih <i>completed</i>. Walaupun mungkin akan memakan biaya yang lebih besar, tapi
                harga lemari kayu 2 pintu kecil ini akan membuat <i>Moms</i> lebih hemat lagi jika dihitung dengan
                lamanya kegunaan lemari ini.</p>
            <p class="text-justify">Tapi saat ini <i>Moms</i> ada di tempat yang tepat! Karena Lunarea Furniture nggak
                setengah-setengah memberikan keuntungan buat pelanggannya. Harga lemari baju minimalis kecil bisa kamu
                dapatkan cuma-cuma dengan produk yang sempurna buat si buah hati. Yang pastinya <i>Moms</i> nggak bakal
                kecewa deh!</p>
            <p class="text-justify"><i>Check out</i> produk lemarianak sekarang juga dan dapatkan <b>100% gratis
                    ongkir</b> untuk pengiriman di wilayah Jawa, Madura dan Bali!</p>
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