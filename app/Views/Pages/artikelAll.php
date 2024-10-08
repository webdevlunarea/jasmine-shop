<?= $this->extend('layout/template'); ?>
<?= $this->section('content'); ?>
<div class="konten artikel">
    <div class="container">
        <form action="/actionsearcharticle" method="post">
            <div class="d-flex mb-2 align-items-center">
                <div class="container-search-artikel">
                    <input type="text" placeholder="Cari artikel" class="form-control" name="cari" value="<?= isset($find) ? $find : ''; ?>">
                    <button type="submit" class="btn btn-light"><i class="material-icons">search</i></button>
                    <button type="button" class="btn btn-light" id="btn-open-search-artikel"><i class="material-icons">search</i></button>
                </div>
                <script>
                    const containerSearchArtikelElm = document.querySelector('.container-search-artikel')
                    let openSearchArtikel = false
                    const btnOpenSearchArtikelElm = document.getElementById('btn-open-search-artikel')

                    btnOpenSearchArtikelElm.addEventListener('click', () => {
                        if (openSearchArtikel) {
                            btnOpenSearchArtikelElm.classList.add('btn-light')
                            btnOpenSearchArtikelElm.classList.remove('btn-primary1')
                            btnOpenSearchArtikelElm.innerHTML = '<i class="material-icons">search</i>'
                            containerSearchArtikelElm.classList.remove('show');
                        } else {
                            btnOpenSearchArtikelElm.classList.remove('btn-light')
                            btnOpenSearchArtikelElm.classList.add('btn-primary1')
                            btnOpenSearchArtikelElm.innerHTML = '<i class="material-icons">chevron_left</i>'
                            containerSearchArtikelElm.classList.add('show');
                        }
                        openSearchArtikel = !openSearchArtikel
                    })
                </script>
                <div class="container-nav-kategori-artikel">
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
            </div>
        </form>
        <?php if (!isset($find)) { ?>
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
        }
        $indexAwal = -1;
        if (count($artikel) > 6) { ?>
            <div class="mb-4">
                <div class="d-flex justify-content-between mb-3 align-items-center">
                    <h5 class="jdl-section">Artikel Baru</h5>
                    <?php if (session()->get('role') == 1) { ?>
                        <a href="/addarticle" class="btn btn-primary1">Buat Artikel Baru</a>
                    <?php } ?>
                </div>
                <div class="gap-4 show-flex-ke-hide container-card-artikel">
                    <div class="flex-grow-1">
                        <div class="card-artikel-besar h-100" onclick="pergiKeArtikel(`<?= $artikel[0]['path']; ?>`)">
                            <img class="rounded" src="<?= $artikel[0]['header']; ?>" alt="<?= $artikel[0]['judul']; ?>">
                            <p class="m-0 judul"><?= $artikel[0]['judul']; ?></p>
                            <div class="flex-grow-1">
                                <!-- <p class="m-0 isi"><?= $artikel[0]['isi'][0]['teks']; ?></p> -->
                            </div>
                            <!-- <p class="m-0 fw-bold" style="font-size: smaller;"><?= $artikel[0]['penulis']; ?></p> -->
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
                                    <!-- <p class="m-0 isi"><?= $artikel[1]['isi'][0]['teks']; ?></p> -->
                                </div>
                                <!-- <p class="m-0 fw-bold" style="font-size: smaller;"><?= $artikel[1]['penulis']; ?></p> -->
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
                                    <!-- <p class="m-0 isi"><?= $artikel[2]['isi'][0]['teks']; ?></p> -->
                                </div>
                                <!-- <p class="m-0 fw-bold" style="font-size: smaller;"><?= $artikel[2]['penulis']; ?></p> -->
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
                                    <!-- <p class="m-0 isi"><?= $artikel[3]['isi'][0]['teks']; ?></p> -->
                                </div>
                                <!-- <p class="m-0 fw-bold" style="font-size: smaller;"><?= $artikel[3]['penulis']; ?></p> -->
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
                                    <!-- <p class="m-0 isi"><?= $artikel[4]['isi'][0]['teks']; ?></p> -->
                                </div>
                                <!-- <p class="m-0 fw-bold" style="font-size: smaller;"><?= $artikel[4]['penulis']; ?></p> -->
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
                                    <!-- <p class="m-0 isi"><?= $artikel[5]['isi'][0]['teks']; ?></p> -->
                                </div>
                                <!-- <p class="m-0 fw-bold" style="font-size: smaller;"><?= $artikel[5]['penulis']; ?></p> -->
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
        <div class="show-flex-ke-hide">
            <div style="display:grid; grid-template-columns: repeat(3, 1fr);" class="gap-4">
                <?php foreach ($artikel as $ind_a => $a) {
                    if ($ind_a > $indexAwal) { ?>
                        <div class="card-artikel-besar" onclick="pergiKeArtikel(`<?= $a['path']; ?>`)">
                            <img class="rounded" src="<?= $a['header']; ?>" alt="<?= $a['judul']; ?>">
                            <p class="m-0 judul"><?= $a['judul']; ?></p>
                            <div class="flex-grow-1">
                                <!-- <p class="m-0 isi"><?= $a['isi'][0]['teks']; ?></p> -->
                            </div>
                            <!-- <p class="m-0 fw-bold" style="font-size: smaller;"><?= $a['penulis']; ?></p> -->
                            <p class="m-0" style="font-size: smaller; color: #888;"><?= $a['waktu']; ?></p>
                        </div>
                <?php }
                } ?>
            </div>
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
                                <p class="m-0 judul"><?= $a['judul']; ?></p>
                                <div class="flex-grow-1">
                                    <!-- <p class="m-0 isi"><?= $a['isi'][0]['teks']; ?></p> -->
                                </div>
                                <!-- <p class="m-0 fw-bold" style="font-size: smaller;"><?= $a['penulis']; ?></p> -->
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