<?= $this->extend('layout/template'); ?>
<?= $this->section('content'); ?>
<div class="konten artikel">
    <div class="container">
        <form action="/actionsearcharticle" method="post" class="mb-2">
            <!-- <div class="d-flex mb-2 align-items-center">
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
                        <a class="nav-kategori-artikel" href="/article/category/edukasi">EDUKASI</a>
                        <a class="nav-kategori-artikel" href="/article/category/tips-trik">TIPS & TRIK</a>
                        <a class="nav-kategori-artikel" href="/article/category/rekomendasi">REKOMENDASI</a>
                        <a class="nav-kategori-artikel" href="/article/category/plus-minus">PLUS MINUS</a>
                    </div>
                </div>
            </div> -->
            <div class="d-flex gap-1 align-items-center w-100">
                <select onchange="changeKategori(event)" class="form-select" style="width: fit-content" name="kategori" id="">
                    <option <?= isset($category) ? '' : 'selected'; ?> value="semua">Semua Kategori</option>
                    <option <?= isset($category) ? ($category == 'edukasi' ? 'selected' : '') : ''; ?> value="edukasi">Edukasi</option>
                    <option <?= isset($category) ? ($category == 'tips-trik' ? 'selected' : '') : ''; ?> value="tips-trik">Tips & Trik</option>
                    <option <?= isset($category) ? ($category == 'rekomendasi' ? 'selected' : '') : ''; ?> value="rekomendasi">Rekomendasi</option>
                    <option <?= isset($category) ? ($category == 'plus-minus' ? 'selected' : '') : ''; ?> value="plus-minus">Plus Minus</option>
                </select>
                <script>
                    function changeKategori(e) {
                        console.log(e.target.value)
                        window.location.replace('/article/category/' + e.target.value)
                    }
                </script>
                <div class="container-search-artikel">
                    <input type="text" placeholder="Cari artikel" class="form-control" name="cari" value="<?= isset($find) ? $find : ''; ?>">
                    <button type="submit" class="btn btn-light"><i class="material-icons">search</i></button>
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
        if (count($artikel) > 5) { ?>
            <div class="mb-4">
                <div class="d-flex justify-content-between mb-3 align-items-center">
                    <h5 class="jdl-section">Artikel Baru</h5>
                    <?php if (session()->get('role') == 1) { ?>
                        <a href="/addarticle" class="btn btn-primary1">Buat Artikel Baru</a>
                    <?php } ?>
                </div>
                <div class="show-flex-ke-hide gap-4">
                    <div style="width: calc(100% / 3);">
                        <div class="card-artikel-besar" style="height: 320px;" onclick="pergiKeArtikel(`<?= $artikel[0]['path']; ?>`)">
                            <img class="rounded" src="<?= $artikel[0]['header']; ?>" alt="<?= $artikel[0]['judul']; ?>">
                            <p class="m-0 judul"><?= $artikel[0]['judul']; ?></p>
                            <div class="container-isi">
                                <div class="overlay-isi"></div>
                                <p class="m-0 isi"><?= $artikel[0]['isi']; ?></p>
                            </div>
                            <a class="readmore">Baca selengkapnya</a>
                            <!-- <p class="m-0 fw-bold" style="font-size: smaller;"><?= $artikel[0]['penulis']; ?></p> -->
                            <!-- <p class="m-0" style="font-size: smaller; color: #888;"><?= $artikel[0]['waktu']; ?></p> -->
                        </div>
                    </div>
                    <div style="width: calc(100% / 3);" class="d-flex flex-column gap-4">
                        <div class="card-artikel-kecil" style="height: 150px;" onclick="pergiKeArtikel(`<?= $artikel[1]['path']; ?>`)">
                            <img class="rounded" src="<?= $artikel[1]['header']; ?>" alt="<?= $artikel[1]['judul']; ?>">
                            <div style="position: relative;" class="d-flex flex-column">
                                <p class="m-0 judul"><?= $artikel[1]['judul']; ?></p>
                                <div class="container-isi">
                                    <div class="overlay-isi"></div>
                                    <p class="m-0 isi"><?= $artikel[1]['isi']; ?></p>
                                </div>
                                <a class="readmore">Baca selengkapnya</a>
                                <!-- <p class="m-0 fw-bold" style="font-size: smaller;"><?= $artikel[1]['penulis']; ?></p> -->
                                <!-- <p class="m-0" style="font-size: smaller; color: #888;"><?= $artikel[1]['waktu']; ?></p> -->
                            </div>
                        </div>
                        <div class="card-artikel-kecil" style="height: 150px;" onclick="pergiKeArtikel(`<?= $artikel[2]['path']; ?>`)">
                            <img class="rounded" src="<?= $artikel[2]['header']; ?>" alt="<?= $artikel[2]['judul']; ?>">
                            <div style="position: relative;" class="d-flex flex-column">
                                <p class="m-0 judul"><?= $artikel[2]['judul']; ?></p>
                                <div class="container-isi">
                                    <div class="overlay-isi"></div>
                                    <p class="m-0 isi"><?= $artikel[2]['isi']; ?></p>
                                </div>
                                <a class="readmore">Baca selengkapnya</a>
                                <!-- <p class="m-0 fw-bold" style="font-size: smaller;"><?= $artikel[2]['penulis']; ?></p> -->
                                <!-- <p class="m-0" style="font-size: smaller; color: #888;"><?= $artikel[2]['waktu']; ?></p> -->
                            </div>
                        </div>
                    </div>
                    <div style="width: calc(100% / 3);" class="d-flex flex-column gap-4">
                        <div class="card-artikel-kecil" style="height: 150px;" onclick="pergiKeArtikel(`<?= $artikel[3]['path']; ?>`)">
                            <img class="rounded" src="<?= $artikel[3]['header']; ?>" alt="<?= $artikel[3]['judul']; ?>">
                            <div style="position: relative;" class="d-flex flex-column">
                                <p class="m-0 judul"><?= $artikel[3]['judul']; ?></p>
                                <div class="container-isi">
                                    <div class="overlay-isi"></div>
                                    <p class="m-0 isi"><?= $artikel[3]['isi']; ?></p>
                                </div>
                                <a class="readmore">Baca selengkapnya</a>
                                <!-- <p class="m-0 fw-bold" style="font-size: smaller;"><?= $artikel[3]['penulis']; ?></p> -->
                                <!-- <p class="m-0" style="font-size: smaller; color: #888;"><?= $artikel[3]['waktu']; ?></p> -->
                            </div>
                        </div>
                        <div class="card-artikel-kecil" style="height: 150px;" onclick="pergiKeArtikel(`<?= $artikel[4]['path']; ?>`)">
                            <img class="rounded" src="<?= $artikel[4]['header']; ?>" alt="<?= $artikel[4]['judul']; ?>">
                            <div style="position: relative;" class="d-flex flex-column">
                                <p class="m-0 judul"><?= $artikel[4]['judul']; ?></p>
                                <div class="container-isi">
                                    <div class="overlay-isi"></div>
                                    <p class="m-0 isi"><?= $artikel[4]['isi']; ?></p>
                                </div>
                                <a class="readmore">Baca selengkapnya</a>
                                <!-- <p class="m-0 fw-bold" style="font-size: smaller;"><?= $artikel[4]['penulis']; ?></p> -->
                                <!-- <p class="m-0" style="font-size: smaller; color: #888;"><?= $artikel[4]['waktu']; ?></p> -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php
            $indexAwal = 4;
        }
        ?>
        <div class="show-flex-ke-hide">
            <div style="display:grid; grid-template-columns: repeat(3, 1fr);" class="gap-4">
                <?php foreach ($artikel as $ind_a => $a) {
                    if ($ind_a > $indexAwal) { ?>
                        <div class="card-artikel-besar" onclick="pergiKeArtikel(`<?= $a['path']; ?>`)">
                            <img class="rounded" src="<?= $a['header']; ?>" alt="<?= $a['judul']; ?>">
                            <p class="judul"><?= $a['judul']; ?></p>
                            <div class="container-isi">
                                <div class="overlay-isi"></div>
                                <p class="m-0 isi"><?= $a['isi']; ?></p>
                                <!-- <p class="m-0 isi">Lorem ipsum dolor sit, amet consectetur adipisicing elit. Nam odio accusamus optio alias voluptatibus, mollitia maxime sed? Nostrum corporis, quisquam libero quos eaque veritatis? Ipsam odio officia aliquid, veritatis vel nemo vero aliquam dignissimos enim vitae ipsum distinctio id quidem voluptatum ex, earum quis illum illo nihil quasi non. Nesciunt tenetur quaerat nostrum dignissimos, ipsa eum dolorum a, fugiat voluptate fuga officiis inventore. Molestias, adipisci id ut vel animi saepe sapiente labore, dolor eveniet nulla soluta tempore hic! Laboriosam laudantium modi dolorem sit similique, illo numquam nisi voluptas adipisci dolor blanditiis alias nobis exercitationem possimus, tenetur perferendis fugit voluptates dolores!</p> -->
                            </div>
                            <a class="readmore">Baca selengkapnya</a>
                            <!-- <p class="m-0 fw-bold" style="font-size: smaller;"><?= $a['penulis']; ?></p> -->
                            <!-- <p class="m-0" style="font-size: smaller; color: #888;"><?= $a['waktu']; ?></p> -->
                        </div>
                <?php }
                } ?>
            </div>
        </div>
        <div class="hide-ke-show-flex flex-column gap-2">
            <div class="d-flex flex-column gap-3" style="min-height: 100px;">
                <?php foreach ($artikel as $ind_a => $a) { ?>
                    <div class="card-artikel-kecil" onclick="pergiKeArtikel(`<?= $a['path']; ?>`)">
                        <img class="rounded" src="<?= $a['header']; ?>" alt="<?= $a['judul']; ?>">
                        <!-- <div class="img">
                        </div> -->
                        <div style="position: relative;" class="d-flex flex-column">
                            <p class="m-0 judul"><?= $a['judul']; ?></p>
                            <div class="container-isi">
                                <div class="overlay-isi"></div>
                                <p class="m-0 isi"><?= $a['isi']; ?></p>
                                <!-- <p class="m-0 isi">Lorem ipsum dolor sit, amet consectetur adipisicing elit. Nam odio accusamus optio alias voluptatibus, mollitia maxime sed? Nostrum corporis, quisquam libero quos eaque veritatis? Ipsam odio officia aliquid, veritatis vel nemo vero aliquam dignissimos enim vitae ipsum distinctio id quidem voluptatum ex, earum quis illum illo nihil quasi non. Nesciunt tenetur quaerat nostrum dignissimos, ipsa eum dolorum a, fugiat voluptate fuga officiis inventore. Molestias, adipisci id ut vel animi saepe sapiente labore, dolor eveniet nulla soluta tempore hic! Laboriosam laudantium modi dolorem sit similique, illo numquam nisi voluptas adipisci dolor blanditiis alias nobis exercitationem possimus, tenetur perferendis fugit voluptates dolores!</p> -->
                            </div>
                            <a class="readmore">Baca selengkapnya</a>
                            <!-- <p class="m-0 fw-bold" style="font-size: smaller;"><?= $a['penulis']; ?></p> -->
                            <!-- <p class="m-0" style="font-size: smaller; color: #888;"><?= $a['waktu']; ?></p> -->
                        </div>
                    </div>
                <?php } ?>
            </div>
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