<?= $this->extend('layout/template'); ?>
<?= $this->section('content'); ?>
<div class="konten artikel">
    <div class="container">
        <div class="container-nav-kategori-artikel mb-2">
            <div class="d-flex gap-5">
                <a class="nav-kategori-artikel" href="/article">SEMUA</a>
                <a class="nav-kategori-artikel" href="/article/category/lemari-dewasa">LEMARI DEWASA</a>
                <a class="nav-kategori-artikel" href="/article/category/lemari-anak">LEMARI ANAK</a>
                <a class="nav-kategori-artikel" href="/article/category/meja-rias">MEJA RIAS</a>
                <a class="nav-kategori-artikel" href="/article/category/meja-belajar">MEJA BELAJAR</a>
                <a class="nav-kategori-artikel" href="/article/category/meja-tv">MEJA TV</a>
                <a class="nav-kategori-artikel" href="/article/category/meja-tulis">MEJA TULIS</a>
                <a class="nav-kategori-artikel" href="/article/category/meja-komputer">MEJA KOMPUTER</a>
                <a class="nav-kategori-artikel" href="/article/category/rak-sepatu">RAK SEPATU</a>
                <a class="nav-kategori-artikel" href="/article/category/rak-besi">RAK BESI</a>
                <a class="nav-kategori-artikel" href="/article/category/rak-serbaguna">RAK SERBAGUNA</a>
                <a class="nav-kategori-artikel" href="/article/category/kursi">KURSI</a>
            </div>
        </div>
        <div class="mb-4">
            <div class="p-5 header show-flex-ke-hide" style="position: relative; margin-bottom: -50svh; flex-direction: column; justify-content: end; width: 40%;">
                <h1 class="text-light mb-1" style="font-size: 50px; line-height: 52px">Welcome to Lunarea's Article</h1>
                <p class="text-light mb-3">Perbarui informasi & referensi Anda seputar furniture dengan desain ala masyarakat urban</p>
                <div class="d-flex gap-2">
                    <a href="/all" class="btn btn-primary1">Pergi ke Toko</a>
                </div>
            </div>
            <div class="p-4 header hide-ke-show-flex" style="position: relative; margin-bottom: -30svh; flex-direction: column; justify-content: end; width: 80%;">
                <h2 class="text-light mb-1">Lunarea's Article</h2>
                <p class="text-light mb-3">Perbarui informasi & referensi Anda seputar furniture dengan desain ala masyarakat urban</p>
                <div class="d-flex gap-2">
                    <a href="/all" class="btn btn-primary1">Pergi ke Toko</a>
                </div>
            </div>
            <img class="d-block rounded header" src="https://images.unsplash.com/photo-1613575831056-0acd5da8f085?q=80&w=2070&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" alt="">
        </div>
        <?php
        $indexAwal = -1;
        if (count($artikel) > 6) { ?>
            <div class="mb-4">
                <h5 class="jdl-section mb-3">Artikel Baru</h5>
                <div class="gap-4 show-flex-ke-hide container-card-artikel">
                    <div class="flex-grow-1">
                        <div class="card-artikel-besar" onclick="pergiKeArtikel(`<?= $artikel[0]['path']; ?>`)">
                            <img class="rounded" src="<?= $artikel[0]['header']; ?>" alt="<?= $artikel[0]['judul']; ?>">
                            <p class="m-0 judul"><?= $artikel[0]['judul']; ?></p>
                            <div class="flex-grow-1">
                                <p class="m-0 isi"><?= $artikel[0]['isi'][0]['teks']; ?></p>
                            </div>
                            <p class="m-0 fw-bold" style="font-size: smaller;"><?= $artikel[0]['penulis']; ?></p>
                            <p class="m-0" style="font-size: smaller; color: #888;"><?= $artikel[0]['waktu']; ?></p>
                        </div>
                    </div>
                    <div class="d-flex flex-grow-1 flex-column gap-4">
                        <div class="card-artikel-kecil" onclick="pergiKeArtikel(`<?= $artikel[1]['path']; ?>`)">
                            <div class="img">
                                <img class="rounded" src="<?= $artikel[1]['header']; ?>" alt="<?= $artikel[1]['judul']; ?>">
                            </div>
                            <div class="flex-grow-1 d-flex flex-column">
                                <p class="m-0 judul"><?= $artikel[1]['judul']; ?></p>
                                <div class="flex-grow-1">
                                    <p class="m-0 isi"><?= $artikel[1]['isi'][0]['teks']; ?></p>
                                </div>
                                <p class="m-0 fw-bold" style="font-size: smaller;"><?= $artikel[1]['penulis']; ?></p>
                                <p class="m-0" style="font-size: smaller; color: #888;"><?= $artikel[1]['waktu']; ?></p>
                            </div>
                        </div>
                        <div class="card-artikel-kecil" onclick="pergiKeArtikel(`<?= $artikel[2]['path']; ?>`)">
                            <div class="img">
                                <img class="rounded" src="<?= $artikel[2]['header']; ?>" alt="<?= $artikel[2]['judul']; ?>">
                            </div>
                            <div class="flex-grow-1 d-flex flex-column">
                                <p class="m-0 judul"><?= $artikel[2]['judul']; ?></p>
                                <div class="flex-grow-1">
                                    <p class="m-0 isi"><?= $artikel[2]['isi'][0]['teks']; ?></p>
                                </div>
                                <p class="m-0 fw-bold" style="font-size: smaller;"><?= $artikel[2]['penulis']; ?></p>
                                <p class="m-0" style="font-size: smaller; color: #888;"><?= $artikel[2]['waktu']; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex flex-grow-1 flex-column gap-4">
                        <div class="card-artikel-kecil" onclick="pergiKeArtikel(`<?= $artikel[3]['path']; ?>`)">
                            <div class="img">
                                <img class="rounded" src="<?= $artikel[3]['header']; ?>" alt="<?= $artikel[3]['judul']; ?>">
                            </div>
                            <div class="flex-grow-1 d-flex flex-column">
                                <p class="m-0 judul"><?= $artikel[3]['judul']; ?></p>
                                <div class="flex-grow-1">
                                    <p class="m-0 isi"><?= $artikel[3]['isi'][0]['teks']; ?></p>
                                </div>
                                <p class="m-0 fw-bold" style="font-size: smaller;"><?= $artikel[3]['penulis']; ?></p>
                                <p class="m-0" style="font-size: smaller; color: #888;"><?= $artikel[3]['waktu']; ?></p>
                            </div>
                        </div>
                        <div class="card-artikel-kecil" onclick="pergiKeArtikel(`<?= $artikel[4]['path']; ?>`)">
                            <div class="img">
                                <img class="rounded" src="<?= $artikel[4]['header']; ?>" alt="<?= $artikel[4]['judul']; ?>">
                            </div>
                            <div class="flex-grow-1 d-flex flex-column">
                                <p class="m-0 judul"><?= $artikel[4]['judul']; ?></p>
                                <div class="flex-grow-1">
                                    <p class="m-0 isi"><?= $artikel[4]['isi'][0]['teks']; ?></p>
                                </div>
                                <p class="m-0 fw-bold" style="font-size: smaller;"><?= $artikel[4]['penulis']; ?></p>
                                <p class="m-0" style="font-size: smaller; color: #888;"><?= $artikel[4]['waktu']; ?></p>
                            </div>
                        </div>
                        <div class="card-artikel-kecil" onclick="pergiKeArtikel(`<?= $artikel[5]['path']; ?>`)">
                            <div class="img">
                                <img class="rounded" src="<?= $artikel[5]['header']; ?>" alt="<?= $artikel[5]['judul']; ?>">
                            </div>
                            <div class="flex-grow-1 d-flex flex-column">
                                <p class="m-0 judul"><?= $artikel[5]['judul']; ?></p>
                                <div class="flex-grow-1">
                                    <p class="m-0 isi"><?= $artikel[5]['isi'][0]['teks']; ?></p>
                                </div>
                                <p class="m-0 fw-bold" style="font-size: smaller;"><?= $artikel[5]['penulis']; ?></p>
                                <p class="m-0" style="font-size: smaller; color: #888;"><?= $artikel[5]['waktu']; ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php
            $indexAwal = 5;
        }
        ?>
        <div class="show-flex-ke-hide flex-column gap-2">
            <?php foreach ($artikel as $ind_a => $a) {
                if ($ind_a > $indexAwal) {
                    if (fmod($ind_a, 3) == 0) { ?>
                        <div class="gap-4 d-flex container-card-artikel">
                            <?php for ($i = $ind_a; $i < $ind_a + 3; $i++) {
                                if (isset($artikel[$i])) { ?>
                                    <div class="flex-grow-1" onclick="pergiKeArtikel(`<?= $artikel[$i]['path']; ?>`)">
                                        <div class="card-artikel-besar" .>
                                            <img class="rounded" src="<?= $artikel[$i]['header']; ?>" alt="<?= $artikel[$i]['judul']; ?>">
                                            <p class="m-0 judul"><?= $artikel[$i]['judul']; ?></p>
                                            <div class="flex-grow-1">
                                                <p class="m-0 isi"><?= $artikel[$i]['isi'][0]['teks']; ?></p>
                                            </div>
                                            <p class="m-0 fw-bold" style="font-size: smaller;"><?= $artikel[$i]['penulis']; ?></p>
                                            <p class="m-0" style="font-size: smaller; color: #888;"><?= $artikel[$i]['waktu']; ?></p>
                                        </div>
                                    </div>
                            <?php }
                            } ?>
                        </div>
            <?php }
                }
            } ?>
        </div>
        <div class="hide-ke-show-flex flex-column gap-2">
            <?php foreach ($artikel as $ind_a => $a) { ?>
                <div class="gap-4 d-flex container-card-artikel" style="height: 100px;">
                    <div class="d-flex flex-grow-1 flex-column gap-4">
                        <div class="card-artikel-kecil" onclick="pergiKeArtikel(`<?= $a['path']; ?>`)">
                            <div class="img">
                                <img class="rounded" src="<?= $a['header']; ?>" alt="<?= $a['judul']; ?>">
                            </div>
                            <div class="flex-grow-1 d-flex flex-column">
                                <p class="m-0 judul" style="line-height: 16px;"><?= $a['judul']; ?></p>
                                <div class="flex-grow-1">
                                    <p class="m-0 isi"><?= $a['isi'][0]['teks']; ?></p>
                                </div>
                                <p class="m-0 fw-bold" style="font-size: smaller;"><?= $a['penulis']; ?></p>
                                <p class="m-0" style="font-size: smaller; color: #888;"><?= $a['waktu']; ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>
</div>
<script>
    function pergiKeArtikel(judulArtikel) {
        console.log(judulArtikel)
        window.location.href = '/article/' + judulArtikel
    }
</script>
<?= $this->endSection(); ?>