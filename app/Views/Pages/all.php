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
                    <h5>Apa itu Rak Sepatu?</h5>
                    <p class="text-justify">Furniture yang satu ini menjadi perabotan rumah tangga yang penting dan harus ada di rumah. Terlebih jika memiliki banyak koleksi sneaker yang cukup banyak dan berharga. Pada umumnya, lemari rak sepatu dibuat dari bahan kayu, besi atau logam, plastik, hingga bahan lainnya. Ukuran dan desainnya pun beragam, bergantung pada kebutuhan dan penempatannya. Semisal saja, untuk penempatan di area yang rentan dengan debu akan lebih baik jika memilih rak sepatu tertutup.</p>
                    <p class="text-justify">Sedangkan untuk menyiasati <i>space</i> ruang terbatas, maka rak sepatu minimalis terbuka atau rak sepatu minimalis tertutup jadi opsi yang pas. Furniture minimalis pada dasarnya memegang prinsip kegunaan yang praktis dan menjawab kebutuhan. Terlebih bertambahnya koleksi sepatu yang dibarengi dengan kebutuhan, merupakan hal yang tidak bisa dihindari. Tak ada salahnya jika memutuskan untuk beli rak sepatu tertutup baru untuk mengganti atau menambah <i>space</i> penyimpanan sneakers jadi lebih tertata rapi dan awet di dalamnya.</p>

                    <h5>Ini Jenis Lemari Rak Sepatu</h5>
                    <p class="text-justify mb-1">Ada berbagai jenis yang bisa Kamu pilih. Pada dasarnya, pemilihan ini berdasarkan pada kebutuhan dan preferensi masing-masing.</p>
                    <ol>
                        <li>Rak sepatu terbuka</li>
                        <li>Rak sepatu tertutup kaca</li>
                        <li>Rak sepatu dengan kabinet</li>
                        <li>Rak sepatu dari kayu</li>
                        <li>Rak sepatu minimalis</li>
                        <li>Rak sepatu minimalis tertutup</li>
                    </ol>

                    <h5>Tips Merawat dengan Mudah</h5>
                    <p class="text-justify mb-1">Melakukan perawatan terhadap furniture lemari rak sepatu sangat mudah. Kalau dilihat dari jenis di atas, maka material kayu jadi bahan yang paling sering dan populer digunakan. Selain dari harganya yang terbilang terjangkau, kayu rak sepatu kayu memiliki warna yang menarik dan perawatan yang terbilang mudah. cukup dengan melakukan langkah-langkah di bawah ini untuk memastikannya lebih awet dan tahan lama:</p>
                    <ol>
                        <li>Hindari dari tempat lembab</li>
                        <li>Bersihkan secara rutin dan berkala</li>
                        <li>Susun berdasarkan pada jenisnya, dan usahakan sepatu atau alas kaki yang berat berada di susunan terbawah serta semakin ringan beratnya di tingkat keatas</li>
                        <li>Bersihkan terlebih dahulu alas kaki yang telah digunakan sebelum disimpan</li>
                        <li>Jika terlihat ada jamur, langsung bersihkan dengan segera dan tempatkan sementara di tempat yang terkena sinar matahari</li>
                    </ol>

                    <h5>Rekomendasi Tempat Beli Rak Sepatu Terpercaya</h5>
                    <p class="text-justify">Setelah mengetahui berbagai jenis dan cara perawatannya, apakah Kamu mulai tertarik untuk beli rak sepatu dengan harga terjangkau? Atau malah sudah menetapkan hati beli rak sepatu tertutup tapi masih belum menemukan toko yang <i>trusted</i>?</p>
                    <p class="text-justify">Tenang saja, Kamu berada di tempat yang tepat! Sekarang semua cukup mudah. Kamu bisa mendapatkannya dengan cepat dan aman dalam genggaman tangan saja melalui, Website Lunarea Furniture.</p>
                    <p class="text-justify">Jangan lupa juga untuk perhatikan pembelian dan pastikan Kamu mendapatkan harga terbaik dan diskon menarik serta <b>gratis ongkir</b> untuk Pulau Jawa, Madura dan Bali lho!</p>
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
                    <p class="text-justify">Sebagai salah satu furniture penting yang ada di rumah, lemari memegang peran penting untuk memastikan barang di dalamnya tertata rapi. Lemari bisa ditempatkan pada bagian rumah di mana saja tergantung dari jenis penyimpanannya. Sesuai dengan namanya, lemari pakaian biasanya di tempat tidur sebagai tempat penyimpanan pakaian. Tapi tidak menutup kemungkinan juga lemari digunakan untuk menyimpan keperluan lain dan bisa ditempatkan secara fleksibel di ruangan lainnya.</p>
                    <p class="text-justify">Kalau ruangan kamu tergolong terbatas dan menginginkan kesan luas, lemari pakaian minimalis jadi opsi terbaik untuk dipilih. Modelnya yang simpel dan tidak banyak aksen menjadikan lemari baju minimalis menyamarkan kesan sesak dalam ruangan. Desain yang seperti inilah yang sedang banyak jadi furniture populer yang dengan mudah ditemukan di berbagai toko <i>offline</i> dan <i>online</i>. Lemari pakaian minimalis modern minimalis terbaru hadir dengan berbagai jenis mulai dari lemari dengan 2 pintu, 3 pintu, hingga 4 pintu.</p>

                    <h5>Jenis Lemari Pakaian</h5>
                    <p class="text-justify">Banyaknya minat masyarakat terhadap lemari pakaian minimalis, membuat para pengrajin terus menerus melakukan inovasi lemari pakaian minimalis modern terbaru baik dari segi desain dan jenisnya. Nah, apa saja ya kira-kira? sudahkah Kamu memiliki salah satu atau beberapa jenis lemari pakaian ini?</p>
                    <div class="sub-1">
                        <p class="fw-bold mb-1">1. Lemari Pakaian 2 Pintu</p>
                        <p class="text-justify">Seperti yang sudah tertera pada namanya, lemari pakaian 2 pintu adalah lemari pakaian yang dilengkapi dengan 2 pintu yang biasanya memuat <i>space</i> penyimpanan ruang gantung sebagai penyimpanan pakaian panjang, rak untuk menyimpan pakaian lipat, dan laci untuk menyimpan barang berharga.desain sederhana dari lemari ini jadi pilihan yang cocok jika Kamu membutuhkan penyimpanan lengkap dengan ukuran yang tidak terlalu besar.</p>
                        <p class="fw-bold mb-1">2. Lemari Pakaian 3 Pintu</p>
                        <p class="text-justify">Hampir sama yang diatas, lemari pakaian 3 pintu dilengkapi dengan space ruang berupa area gantungan, rak, dan laci. Hanya saja yang membedakannya adalah ukuran serta jumlah penyimpanan yang tentunya lebih komprehensif. Untuk menunjang kualitas akan lebih baik jika memastikan yang Kamu pilih adalah lemari kayu 3 pintu. Material kayu sudah dikenal dengan keawetannya dan keindahan khas yang tercipta dari tekstur kayu dengan dipertegas dengan finishingnya.</p>
                    </div>

                    <h5>Segini Harga Lemari Pakaian Minimalis Modern</h5>
                    <p class="text-justify">Walaupun diincar banyak orang, tapi tidak perlu khawatir karena lemari baju minimalis yang jadi idaman Kamu ini dijual dengan harga yang kompetitif lho! Maka dari itu, cukup dengan sesuaikan kebutuhan dan budget yang kamu miliki untuk mendapatkannya. Harga lemari baju juga bisa naik atau turun bergantung pada material bahan yang digunakan. Ada banyak sekali toko baik itu berbentuk offline atau online yang jual lemari pakaian dan menawarkan berbagai desain, keunggulan, dan harga yang beragam. Maka dari itu, sebelum memutuskan membeli lemari baju, baiknya lakukan riset sederhana seperti bertanya pada orang-orang terpercaya, atau ngepoin marketplace sampai website toko furniture online. Kalau kamu bingung, website Lunarea Furniture bisa dengan mudah Kamu akses dengan mudah untuk tahu desain, material, dan harga lemari pakaian minimalis modern yang cocok buat Kamu.</p>
                    <p class="text-justify">Kabar gembiranya, Lunarea Furniture lagi ngadain gratis ongkir untuk pengiriman di pulau Jawa, Madura dan Bali nih! Kapan lagi?
                        Yuk sekarang aja, sebelum kehabisan promonya!
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
                    <p class="text-justify">Komputer merupakan salah satu perangkat elektronik yang di zaman serba digital ini banyak dimiliki oleh orang-orang di rumah untuk membuat aktivitas kerja di meja kerja komputer jadi nyaman, efisien dan lebih efektif. Apalagi kebutuhan komputer meja di masa sekarang yang bisa dikatakan sebagai kebutuhan primer manusia modern. Maka tak heran jika membeli meja komputer sangat penting dan tidak boleh sembarangan. Ada beberapa hal yang perlu diperhatikan yaitu sebagai berikut ini:</p>
                    <ul class="mb-2">
                        <li>Pilih meja PC yang dilengkapi dengan rak atau laci untuk keyboard dan CPU, dan sliding laci untuk tempat <i>keyword</i>.</li>
                        <li>Pilih meja yang cocok dengan preferensi pribadimu.</li>
                        <li>Cek fitur yang ada pada meja komputer. Sebaiknya Kamu memilih meja yang dilengkapi dengan fitur tambahan seperti tempat meletakkan <i>scanner</i>, <i>printer</i>, buku, dokumen, alat tulis, dan lain sebagainya berdasarkan kebutuhan.</li>
                    </ul>

                    <h5>Jenis-jenis Meja Komputer</h5>
                    <p class="text-justify">Seiring dengan banyaknya orang yang beraktivitas di depan komputer meja, maka akan sebanding juga dengan permintaan pasar.</p>
                    <ul>
                        <li>Meja komputer minimalis</li>
                        <li>Meja PC</li>
                        <li>Meja komputer & <i>printer</i></li>
                    </ul>

                    <h5>Rekomendasi Material Berkualitas</h5>
                    <p class="text-justify">Rekomendasi kali ini diperuntukan untuk kaum mendang-mending yang butuh komputer meja berkualitas dengan harga bersahabat. Pilihan tepat jatuh ke material kayu olahan atau plywood yang sangat bisa diandalkan untuk membuat furniture yang mudah dibentuk dan punya banyak pilihan finishing. dengan banyaknya pilihan finishing inilah yang membuat banyak dijumpai meja komputer minimalis dengan warna hitam, putih, abu-abu atau meja komputer kayu yang sebenarnya hanya finishingnya saja.</p>

                    <h5>Harga Meja Komputer</h5>
                    <p class="text-justify">Nah, saatnya membahas hal <i>budgeting</i> agar tidak banyak memakan anggaran tak terduga. Maka dari itu, agar tidak kejadian seperti ini, Kamu bisa buat perencanaan biaya dikeluarkan untuk membeli meja PC idamanmu. Tenang saja, kalau sedang mencari harga yang cukup terjangkau, maka kamu berada di tempat yang tepat! Lunarea Furniture menjual berbagai furniture seperti meja komputer minimalis, meja komputer & <i>printer</i>, meja kerja komputer, dan meja lainnya yang Anda butuhkan.</p>
                    <p class="text-justify">Tak perlu khawatir soal harga, karena Lunarea menjual furniture dengan harga bersahabat namun dengan kualitas produk yang berkualitas.</p>
                    <p class="text-justify">Dapatkan <b>gratis ongkir sebesar 100%</b> untuk pengiriman di pulau Jawa, Madura, dan Bali! Untuk Kamu yang berada diluar ketiga pulau tersebut jangan bersedih hati, karena ada potongan ongkir yang menguntungkan buat Kamu!</p>
                    <p class="text-justify">Buruan dapatkan produknya sekarang dan lengkapi furniture rumah Kamu bersama Lunarea Furniture.</p>
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
                    <p class="text-justify">Meja tulis merupakan salah satu furniture wajib yang ada di rumah atau kantor. Sama seperti namanya, meja jenis ini dibuat secara khusus untuk meningkatkan produktivitas dan kenyamanan. Faktor inilah yang diharapkan dapat membuat seseorang bisa menyelesaikan pekerjaannya lebih efektif dan efisien. Desain meja kerja kantor ini juga ditentukan oleh kebutuhan. Ada meja tulis dengan <i>top table</i> lurus tanpa ada ambalan tambahan, ada jenis meja kantor yang terkadang diberikan fitur tambahan seperti laci dan <i>space</i> kabinet tertutup di sisi kiri dan atau kanan bawah. Keberagaman desain ini merupakan jawaban atas beragamnya kebutuhan setiap orang masing-masing.</p>

                    <h5>Material Pembuatan Meja Kerja Kantor</h5>
                    <p class="text-justify">Bahan material untuk pembuatan meja tulis sekarang-sekarang ini didominasi oleh bahan dari kayu olahan atau plywood. Dengan keunggulannya yaitu tekstur yang mudah dibentuk, punya banyak pilihan finishing, dan harganya yang lebih terjangkau. Hal inilah yang membuat Kamu akan lebih mudah menjumpai meja kantor berbahan plywood di pasaran. Walaupun begitu, tidak menutup kemungkinan kalau meja kerja kayu masih banyak peminatnya.</p>

                    <h5>Jenis-jenis Meja kerja Kantor</h5>
                    <p class="text-justify mb-1">Setelah tahu material pembuatannya, sekarang ini kita bakal ngulik desain meja kerja yang bisa jadi pilihan Kamu.</p>
                    <ul>
                        <li>Meja kerja minimalis</li>
                        <li style="list-style-type: none;">Jenis meja minimalis yang difungsikan untuk menyelesaikan pekerjaan ini biasanya didesain simple dengan sedikit aksen dan finishing warna netral seperti abu-abu, putih, hitam, hingga coklat.</li>
                        <li>Meja resepsionis</li>
                        <li style="list-style-type: none;">Ketika masuk di sebuah perusahaan kamu akan menemukan jenis meja yang satu ini. Meja ini sendiri biasanya difungsikan untuk menerima dan memberikan informasi kepada tamu yang membutuhkan saat datang.</li>
                        <li>Meja tulis</li>
                        <li style="list-style-type: none;">Meja yang dirancang khusus untuk digunakan karyawan saat melakukan aktivitas kerja ini biasanya dilengkapi dengan laci, atau lubang kecil sebagai tempat menata kabel.</li>
                    </ul>

                    <h5>Rekomendasi Meja Kerja Terjangkau</h5>
                    <p class="text-justify">Nah jika desain dan jenisnya sudah kita ketahui, sekarang saatnya Kamu memutuskan harus beli meja kerja bukan? Jika masih ragu dan belum menentukan meja mana yang akan dibeli, mimin ada rekomendasi nih! Coba kunjungi Lunarea Furniture dan dapatkan produk meja kerja minimalis idamanmu di sini! Menariknya, harga meja kantor di website Lunarea Furniture hanya ratusan ribu aja lho! Nggak sampai disitu saja, tapi ada gratis pengiriman di pulau Jawa, Madura dan Bali! Buat yang diluar ketiga pulau tadi, jangan khawatir, Lunarea bakal kasih kamu subsidi ongkir yang nggak tanggung-tanggung nih!
                        Tunggu apa lagi, <i>check out</i> sekarang juga dan dapatkan keuntungannya segera!</p>
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
                    <p class="text-justify">Televisi sudah jadi barang elektronik yang dirasa wajib ada di setiap rumah. Kegunaannya sebagai media mencari hiburan baik itu saat sendiri atau bersama orang-orang tersayang menjadikan barang elektronik selalu eksis hingga sekarang. Maka dari itu, untuk melengkapi momen menonton TV jadi lebih asyik, diperlukan <i>space</i> khusus berupa Meja TV tentunya. Tak hanya itu saja, furniture meja yang satu ini juga punya fungsi lain sebagai tempat penyimpanan peralatan pendukung nonton TV seperti DVD <i>player</i>, PS <i>player</i>, WiFi, dan masih banyak lagi. Karena saking multifungsinya, furniture meja tempat TV ini punya sebutan lain di masyarakat seperti rak TV, <i>buffet</i> TV, credenza TV, dan sebutan lain yang sebenarnya jika dilihat dari fungsi tetap sama.</p>

                    <h5>Desain Meja TV Minimalis</h5>
                    <p class="text-justify">Ngomong desain, ada berbagai jenis model yang bisa Kamu pilih lho! Terlebih jika pilihanmu tertuju dengan model rak TV minimalis yang cantik dan kalem dengan warna-warna netralnya. meja TV minimalis yang <i>eye catching</i> ini biasanya terbuat dari material plywood yang mudah dibentuk. Terlebih jika dibalut dengan <i>paper</i> sebagai finishing akhirnya, yang memanjakan mata dengan warna-warna cantik dari rak TV minimalis. Serba serbi minimalis memang jadi primadona bagi banyak orang, yang mungkin kamu salah satunya. Selain desain yang cantik, kebutuhan furniture yang pas dengan <i>space</i> terbatas jadi alasan mengapa meja TV minimalis jadi pilihan terbaik saat ini buat orang-orang.</p>

                    <h5>Tips Jitu Anti Gagal Beli Rak TV</h5>
                    <p class="text-justify mb-1">Semakin kesini, Kamu mungkin akan makin bimbang dengan berbagai model variatif meja TV minimalis modern ini bisa saja buat kamu mudah goyah menentukan harus beli meja TV mana yah? Nah sekarang saatnya keluarkan jurus jitu membeli rak TV yang anti gagal!</p>
                    <div class="d-flex">
                        <div style="width: 20px;">1.</div>
                        <div style="flex: 1">
                            <p class="text-justify m-0">Tentukan kebutuhan seperti apa yang harus Kamu penuhi</p>
                        </div>
                    </div>
                    <div class="d-flex">
                        <div style="width: 20px;"></div>
                        <div style="flex: 1">
                            <p class="text-justify">Seperti apa yah kira-kira <i>buffet</i> TV yang cocok sesuai kebutuhanmu? Mulai dari ukuran <i>space</i> ruangan, selain TV adakah yang akan disimpan di area itu? dan lain sebagainya. Buat secara rinci kebutuhan kamu ya!</p>
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
                            <p class="text-justify">Hal yang berkaitan dengan keuangan biasanya bakal jadi sesuatu sensitif yang salah gerak sekali saja bisa membuyarkan segalanya. Nah, setelah mendetailkan kebutuhan kamu perlu membuat estimasi <i>budget</i> yang akan dikeluarkan untuk membeli furniture meja yang satu ini. Ingat ya, Harga rak TV minimalis modern sangat beragam yang dengan mudah bisa ditemukan dari meja TV minimalis modern termurah hingga termahal tentunya.</p>
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
                            <p class="text-justify">Jika 2 poin diatas sudah terpenuhi, maka yang selanjutnya dilakukan adalah menentukan desain yang selaras. Selaras disini artinya sesuai dengan kebutuhan dan budget yang sudah disiapkan. Akan lebih baik jika pemilihanmu jatuh pada desain meja TV minimalis modern yang sedang tren sekarang ini. Tapi perlu dipertimbangkan juga kamu memilih material pilihan yang bagus.</p>
                        </div>
                    </div>

                    <h5>Ragu Beli Rak TV Minimalis Modern? Beli di Lunarea Aja!</h5>
                    <p class="text-justify">Bagi kamu yang masih ragu beli furniture online, sekarang Kamu harus menghempas keraguan itu jauh-jauh. Ada Lunarea Furniture yang dijamin aman dan terpercaya mengantarkan meja TV minimalis modern pilihan kamu sampai di tujuan!</p>
                    <p class="text-justify">Tak sampai itu saja, di sini Kamu juga bisa membeli meja TV minimalis modern termurah namun dengan kualitas yang terbaik di kelasnya. Plus-nya lagi, ada kabar gembira buat pengiriman ke Pulau Jawa, Madura, dan Bali karena 100% biaya ongkir akan ditanggung oleh Lunarea Furniture Lho! Kapan lagi bisa dapat harga rak TV minimalis modern murah ditambah plus ongkir kalau bukan di sini.</p>
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
                    <p class="text-justify">Salah satu perabotan rumah tangga yang penting walaupun bukan termasuk yang utama adalah meja rias. Sama seperti namanya, furniture satu ini punya fungsi yang vital terlebih buat perempuan. Dengan meja rias atau tolet inilah yang membuatmu tidak perlu <i>worry</i> lagi bakalan nggak <i>stunning</i> setiap harinya. Karena diperuntukkan secara spesial buat perempuan, tentunya kamu juga bisa dengan mudah menemukan berbagai jenis desain yang cantik dan menarik.</p>

                    <h5>Dari Material Bahan Apa Meja Rias Dibuat?</h5>
                    <p class="text-justify">Furniture ini kebanyakan terbuat dari bahan plywood. Material yang melimpah dan mudah dibentuk serta harga yang lebih kompetitif dari kayu solid atau bahan lain menjadi alasan utama mengapa bahan ini sangat populer belakangan ini. Selain itu, dengan menggunakan plywood, juga membuat para pengrajin lebih inovatif dan kreatif mewarnai dengan berbagai jenis dan warna finishing untuk memproduksi meja rias minimalis modern yang jadi tren terkini. Katakanlah meja rias minimalis yang didominasi warna-warna netral seperti coklat, putih, sonoma, dan lain sebagainya.</p>
                    <p class="text-justify">Dengan keberagaman ini, Kamu sebagai konsumen tentu akan merasa termanjakan bukan? Masih belum puas dengan itu saja, jika minimalis masih tidak cukup di <i>space</i> ruang yang kamu miliki, maka opsi selanjutnya dengan menambahkan pilihan pada meja rias minimalis kecil, atau meja rias <i>simple</i> yang kemungkinan besar sesuai dengan kebutuhan Kamu.</p>
                    <p class="text-justify">Material plywood menjawab kebutuhan pengrajin sebagai produsen dan Kamu sebagai Konsumen. Maka dari itu, jangan heran ya kenapa setelah <i>search</i> sana sini plywood selalu nampang jadi salah satu opsinya.</p>

                    <h5>Berapa Estimasi Harga Meja Rias?</h5>
                    <p class="text-justify">Barangkali sudah bukan rahasia umum yah kalau harga meja rias dengan material plywood bisa dikatakan lebih miring dibanding material lain seperti kayu, <i>stainless</i>, dan lain sebagainya. Inilah yang membuatnya banyak diburu apalagi buat kaum mendang mending. Dengan kualitas yang mumpuni, <i>look</i> yang <i>eye catching</i> dan harga yang menawan, siapa sih yang nggak kelimpungan buat dapetin meja rias multifungsi kayak gini? Nah, tapi jangan asal pilih juga ya! Pastikan Kamu beli meja rias di tempat yang terpercaya agar tak menyesal kemudian. Salah satunya yang bakalan nggak bikin kecewa bisa Kamu dapetin lewat websitenya Lunarea Furniture.</p>
                    <p class="text-justify">Pastiin kamu sudah cek dulu baru <i>check out</i> produk idaman Kamu ya!
                        Jangan kelamaan <i>checking</i> sebelum kehabisan stok, mumpung ada gratis ongkir pengiriman di Pulau Jawa, Madura, dan Bali.</p>
                    <p class="text-justify">Atau jika masih bimbang mau meja rias minimalis, meja rias multifungsi, meja rias <i>simple</i>, meja rias minimalis modern, atau meja-meja lainnya, Kamu bisa hubungi <i>Customer Service</i> Lunarea yang bakal menjawab semua pertanyaanmu dengan sepenuh hati!</p>
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
                    <p class="text-justify">Meja belajar merupakan salah satu furniture penting yang diupayakan dapat menciptakan lingkungan belajar yang nyaman dan produktif di rumah. Ada beberapa hal yang perlu jadi pertimbangan saat memilih meja belajar mana yang akan dibeli, sebut saja meja belajar minimalis, meja belajar <i>aesthetic</i>, meja belajar multifungsi, meja belajar <i>simple</i>, dan masih banyak lagi ragamnya. Nah, pada dasarnya yang perlu dipilih adalah sesuai dengan kebutuhan dan mengesampingkan keinginan. Tapi kalau bisa keduanya, kenapa tidak? meja belajar minimalis unik mungkin bisa jadi pilihan yang pas untukmu yang tidak suka sesuatu monoton.</p>

                    <h5>Material Bahan Pembuatannya</h5>
                    <p class="text-justify">Furniture meja belajar biasanya terbuat dari material kayu atau kayu olahan seperti plywood atau <i>particle board</i>. Tipe bahan yang mudah dibentuk, mudah ditemukan, dan harga yang lebih terjangkau menjadi alasan utama mengapa <i>particle board</i> sering jadi bahan pembuatan furniture. Hal inilah yang menjadi dasar penetapan harga meja belajar dari bahan ini lebih terjangkau di pasaran dibanding dengan material lainnya.</p>

                    <h5>Desain & Jenisnya</h5>
                    <p class="text-justify mb-1">Sudah diulas sebelumnya, jika keberagaman desain ini tak luput dari beragam kebutuhan. Selain itu, dengan banyaknya pilihan desain ini akan memungkinkan buat Kamu lebih leluasa memilih sebelum membeli. Berikut ini referensinya:</p>
                    <ul>
                        <li>Meja belajar minimalis modern</li>
                        <li style="list-style-type: none;">Segala produk yang mengklaim sebagai minimalis biasanya hadir dengan warna-warna netral seperti hitam, putih, coklat, atau abu-abu dan minim aksen. Desain meja belajar minimalis modern seperti ini cocok buat Kamu yang memiliki <i>space</i> terbatas dengan banyak kebutuhan.</li>
                        <li>Meja belajar <i>aesthetic</i></li>
                        <li style="list-style-type: none;">Kalau Kamu tipe orang yang suka dengan hal-hal yang berbau dengan unsur seni, maka tak ada salahnya untuk mengaplikasikannya di ruangan belajar. Salah satunya dengan menghadirkan meja belajar <i>aesthetic</i> yang mampu menjadi <i>moodbooster</i> saat sedang belajar.</li>
                        <li>Meja belajar <i>simple</i></li>
                        <li style="list-style-type: none;">Agak berbeda dengan poin diatas, meja yang satu ini biasanya diperuntukan buat orang yang males ribet dan lebih suka yang praktis serta minim aksesoris. Kalau kamu salah satu orangnya, maka meja belajar <i>simple</i> solusi tepatnya!</li>
                        <li>Meja belajar minimalis unik</li>
                        <li style="list-style-type: none;">Mungkin yang satu ini akan mirip dengan versi <i>aesthetic</i>, iya! Meja belajar minimalis unik adalah jawaban tepat kalau kamu menyukai segala sesuatu yang berkaitan dengan seni. Disini kamu bisa memadukan aksesoris lain untuk melengkapi tampilan jadi lebih <i>eye catching</i> lagi dengan sentuhan seni yang kamu miliki!</li>
                    </ul>
                    <p class="text-justify">Nah kalau sudah dijabarkan begini, saatnya Kamu memutuskan meja belajar mana yang akan dibeli! Jika masih ragu dan bingung, kamu dapat cek produknya di website Lunarea Furniture dan temukan kebutuhan kamu di sini! Mau cari harga meja belajar dari yang termurah pun ada. Plusnya lagi kamu bisa dapetin gratis ongkir yang beneran gratis sampai ke alamat tujuan yang berada di wilayah pulau Jawa, Madura, dan Bali!</p>
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