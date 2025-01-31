<?= $this->extend('layout/template'); ?>
<?= $this->section('content'); ?>
<?php if (!session()->get('submitEmail') && session()->get('role') != 1) { ?>/
<div id="submit-email" style="z-index: 3; position: fixed; top: 0; left: 0; width: 100vw; height: 100svh; background-color: rgba(0,0,0,0.5);" class="d-none justify-content-center align-items-center">
    <div class="ps-4 pe-4 pb-4 pt-2 rounded" style="background-color: #eff8f2;">
        <div class="d-flex justify-content-end">
            <button class="btn btn-light" onclick="closeSubmitEmail()">x</button>
        </div>
        <h4 class="text-center">Jangan pergi dulu!</h4>
        <p class="text-secondary text-center mb-3">Dapatkan informasi terbaru dengan memasukkan email Anda</p>
        <form action="/submitemail/<?= urlencode($artikel['judul']); ?>" method="post">
            <div class="form-floating mb-3">
                <input type="email" class="form-control" placeholder="name@example.com" name="email" required>
                <label for="floatingInput">Email</label>
            </div>
            <div class="mb-3">
                <input type="checkbox" required id="syaratsubmitemail">
                <label for="syaratsubmitemail">Anda menyetujui seluruh <a style="color: var(--hijau);" class="link-offset-2 link-underline-opacity-25 link-underline-opacity-100-hover" href="/kebijakan-privasi">kebijakan privasi</a> Kami.</label>
            </div>
            <button type="submit" class="btn btn-primary1 w-100">OK</button>
        </form>
    </div>
</div>
<script>
    const submitEmailElm = document.getElementById('submit-email');
    let opened = false;
    document.body.onscroll = (e) => {
        const scrollingElm = e.target.scrollingElement;
        const hasil = Math.round(
            (scrollingElm.scrollTop /
                (scrollingElm.scrollHeight -
                    scrollingElm.clientHeight)) *
            100
        );
        if (hasil > 60 && !opened) {
            submitEmailElm.classList.remove('d-none')
            submitEmailElm.classList.add('d-flex')
            opened = true
        }
    };

    function closeSubmitEmail() {
        submitEmailElm.classList.add('d-none')
        submitEmailElm.classList.remove('d-flex')
    }
</script>
<?php } ?>
<?php if (session()->get('role') == 1) { ?>
    <div id="edit-komen" style="z-index: 3; position: fixed; top: 0; left: 0; width: 100vw; height: 100svh; background-color: rgba(0,0,0,0.5);" class="d-none justify-content-center align-items-center">
        <div class="p-4 bg-light">
            <form action="/editkomen" method="post" id="form-edit">
                <h5>Edit Komen</h5>
                <div class="form-floating mb-1">
                    <input type="text" class="form-control" placeholder="name@example.com" name="nama_edit" required>
                    <label for="floatingInput">Nama</label>
                </div>
                <div class="form-floating mb-2">
                    <input type="text" class="form-control" placeholder="name@example.com" name="isi_edit" required>
                    <label for="floatingInput">Isi</label>
                </div>
                <div class="d-flex gap-1">
                    <button type="submit" class="btn btn-primary1">Ubah</button>
                    <button type="button" class="btn btn-light" onclick="closeEditKomen()">Batal</button>
                </div>
            </form>
        </div>
    </div>
<?php } ?>
<div class="konten artikel">
    <div class="container mb-5">
        <img src="<?= $artikel['header']; ?>" alt="<?= $artikel['judul'] ?>" class="header">
        <div class="p-5 mx-5 show-ke-hide" style="background-color: white; box-shadow: 5px 5px 20px rgba(0,0,0,0.1); position: relative; margin-top: -10svh">
            <div class="d-flex justify-content-between">
                <div class="d-flex gap-1 mb-3">
                    <?php foreach ($artikel['kategori'] as $k) { ?>
                        <h5 class="badge rounded-pill text-bg-secondary"><?= ucwords($k); ?></h5>
                    <?php } ?>
                </div>
                <div class="d-flex gap-2 align-items-center">
                    <div class="d-flex gap-1 align-items-center">
                        <form action="/addlikearticle/<?= $artikel['id'] ?>" method="post">
                            <button type="submit" class="btn-sm" style="background: none; border: none;"><i class="material-icons text-secondary">thumb_up</i></button>
                        </form>
                        <?php if ($artikel['suka'] > 0) { ?>
                            <p class="m-0"><?= $artikel['suka']; ?></p>
                        <?php } ?>
                    </div>
                    <div class="d-flex gap-1 align-items-center">
                        <form action="/addsharearticle/<?= $artikel['id'] ?>" method="post">
                            <button type="submit" class="btn-sm" style="background: none; border: none;"><i class="material-icons text-secondary">share</i></button>
                        </form>
                        <?php if ($artikel['bagikan'] > 0) { ?>
                            <p class="m-0"><?= $artikel['bagikan']; ?></p>
                        <?php } ?>
                    </div>
                    <?php if (session()->get('role') == 1) { ?>
                        <a href="/editarticle/<?= $artikel['id'] ?>" class="btn btn-primary1">Edit</a>
                    <?php } ?>
                </div>
            </div>
            <h1 class="judul"><?= $artikel['judul'] ?></h1>
            <div class="d-flex justify-content-between">
                <p class="m-0 text-secondary">Ditulis oleh <?= $artikel['penulis']; ?></p>
                <p class="m-0 text-secondary">Terakhir diubah pada <?= $artikel['waktu']; ?></p>
            </div>
            <span class="garis my-3"></span>
            <?php foreach ($artikel['isi'] as $isi) {
                if ($isi['tag'] == 'h2') { ?>
                    <h2 class="<?= $isi['style']; ?>"><?= $isi['teks']; ?></h2>
                <?php } else if ($isi['tag'] == 'h4') { ?>
                    <h4 class="<?= $isi['style']; ?>"><?= $isi['teks']; ?></h4>
                <?php } else if ($isi['tag'] == 'p') { ?>
                    <p class="<?= $isi['style']; ?>"><?= $isi['teks']; ?></p>
                <?php } else if ($isi['tag'] == 'a') { ?>
                    <a href="<?= $isi['link']; ?>" class="<?= $isi['style']; ?>"><?= $isi['teks']; ?></a>
                <?php } else if ($isi['tag'] == 'img') { ?>
                    <a class="d-flex flex-column align-items-center <?= $isi['style']; ?>" href="<?= $isi['link']; ?>">
                        <img src="<?= $isi['src']; ?>" style="height: 500px; max-width: 100%" class="mb-1">
                        <p class="m-0 text-sm text-secondary"><?= $isi['sumber']; ?></p>
                    </a>
                <?php } else if ($isi['tag'] == 'space') { ?>
                    <div class="w-100" style="height: 1em"></div>
                <?php }
                ?>
            <?php } ?>
            <span class="garis my-3"></span>
            <div class="d-flex justify-content-between align-items-start" style="gap: 6em;">
                <div style="flex: 1;">
                    <?php if ($prevArtikel) { ?>
                        <a href="/article/<?= $prevArtikel['path']; ?>" class="d-flex gap-2 align-items-center" style="color: var(--hijau);">
                            <i class="material-icons">chevron_left</i>
                            <p class="m-0">Baca sebelumnya</p>
                        </a>
                        <p style="color: #888;"><i><?= $prevArtikel['judul']; ?></i></p>
                    <?php } ?>
                </div>
                <div style="flex: 1;" class="d-flex flex-column align-items-end">
                    <?php if ($nextArtikel) { ?>
                        <a href="/article/<?= $nextArtikel['path']; ?>" class="d-flex gap-2 align-items-center" style="color: var(--hijau);">
                            <p class="m-0">Baca berikutnya</p>
                            <i class="material-icons">chevron_right</i>
                        </a>
                        <p style="color: #888;"><i><?= $nextArtikel['judul']; ?></i></p>
                    <?php } ?>
                </div>
            </div>
        </div>
        <div class="p-4 mx-4 hide-ke-show-block" style="background-color: white; box-shadow: 5px 5px 20px rgba(0,0,0,0.1); position: relative; margin-top: -15svh">
            <div class="d-flex justify-content-between mb-1 align-items-center">
                <div class="d-flex gap-1">
                    <?php foreach ($artikel['kategori'] as $k) { ?>
                        <h5 class="badge rounded-pill text-bg-secondary"><?= ucfirst($k); ?></h5>
                    <?php } ?>
                </div>
                <div class="d-flex gap-2 align-items-center">
                    <div class="d-flex gap-1 align-items-center">
                        <form action="/addlikearticle/<?= $artikel['id'] ?>" method="post">
                            <button type="submit" class="btn-sm" style="background: none; border: none;"><i class="material-icons text-secondary" style="font-size: 13px;">thumb_up</i></button>
                        </form>
                        <?php if ($artikel['suka'] > 0) { ?>
                            <p class="m-0" style="font-size: 13px;"><?= $artikel['suka']; ?></p>
                        <?php } ?>
                    </div>
                    <div class="d-flex gap-1 align-items-center">
                        <form action="/addsharearticle/<?= $artikel['id'] ?>" method="post">
                            <button type="submit" class="btn-sm" style="background: none; border: none;"><i class="material-icons text-secondary" style="font-size: 13px;">share</i></button>
                        </form>
                        <?php if ($artikel['bagikan'] > 0) { ?>
                            <p class="m-0" style="font-size: 13px;"><?= $artikel['bagikan']; ?></p>
                        <?php } ?>
                    </div>
                    <?php if (session()->get('role') == 1) { ?>
                        <a href="/editarticle/<?= $artikel['id'] ?>" class="btn btn-primary1">Edit</a>
                    <?php } ?>
                </div>
            </div>
            <h1 class="judul"><?= $artikel['judul'] ?></h1>
            <div class="d-flex justify-content-between">
                <p class="m-0 text-secondary">Ditulis oleh <?= $artikel['penulis']; ?></p>
                <p class="m-0 text-secondary"><?= $artikel['waktu']; ?></p>
            </div>
            <span class="garis my-3"></span>
            <?php foreach ($artikel['isi'] as $isi) {
                if ($isi['tag'] == 'h2') { ?>
                    <h2 class="<?= $isi['style']; ?>"><?= $isi['teks']; ?></h2>
                <?php } else if ($isi['tag'] == 'h4') { ?>
                    <h4 class="<?= $isi['style']; ?>"><?= $isi['teks']; ?></h4>
                <?php } else if ($isi['tag'] == 'p') { ?>
                    <p class="<?= $isi['style']; ?>"><?= $isi['teks']; ?></p>
                <?php } else if ($isi['tag'] == 'a') { ?>
                    <a href="<?= $isi['link']; ?>" class="<?= $isi['style']; ?>"><?= $isi['teks']; ?></a>
                <?php } else if ($isi['tag'] == 'img') { ?>
                    <a class="d-flex flex-column align-items-center <?= $isi['style']; ?>" href="<?= $isi['link']; ?>">
                        <img src="<?= $isi['src']; ?>" style="height: 500px; max-width: 100%" class="mb-1">
                        <p class="m-0 text-sm text-secondary"><?= $isi['sumber']; ?></p>
                    </a>
                <?php } else if ($isi['tag'] == 'space') { ?>
                    <div class="w-100" style="height: 1em"></div>
                <?php }
                ?>
            <?php } ?>
            <span class="garis my-3"></span>
            <div class="d-flex justify-content-between">
                <?php if ($prevArtikel) { ?>
                    <a href="/article/<?= $prevArtikel['path']; ?>" class="d-flex gap-2 align-items-center" style="color: var(--hijau);">
                        <i class="material-icons">chevron_left</i>
                        <p class="m-0" style="width: fit-content;">Baca<br>sebelumnya</p>
                    </a>
                <?php } else { ?>
                    <div></div>
                <?php } ?>
                <?php if ($nextArtikel) { ?>
                    <a href="/article/<?= $nextArtikel['path']; ?>" class="d-flex gap-2 align-items-center" style="color: var(--hijau);">
                        <p class="m-0" style="width: fit-content;">Baca<br>berikutnya</p>
                        <i class="material-icons">chevron_right</i>
                    </a>
                <?php } ?>
            </div>
        </div>
    </div>
    <div class="container mb-5">
        <h5 class="jdl-section mb-3">Komentar</h5>
        <form action="/addkomen/<?= $artikel['path']; ?>" method="post">
            <div class="mb-2">
                <label for="">Nama</label>
                <input type="text" class="form-control" name="nama" placeholder="Nama" required>
            </div>
            <div class="mb-2">
                <label for="">Komentar</label>
                <textarea class="form-control" name="isi" id="" placeholder="Tulis komentar ..." required></textarea>
            </div>
            <div class="d-flex justify-content-center">
                <button type="submit" class="btn btn-primary1">Kirim</button>
            </div>
        </form>
        <hr>
        <div class="container-komen">
            <?php foreach ($komen as $ind_k => $k) { ?>
                <div class="d-flex align-items-start">
                    <div style="flex: 1">
                        <p class="fw-bold mb-1"><?= $k['nama']; ?></p>
                        <p><?= $k['isi']; ?></p>
                    </div>
                    <?php if (session()->get('role') == 1) { ?>
                        <div style="width: fit-content;" class="d-flex gap-1">
                            <button class="btn btn-light" onclick="openEditKomen('<?= $ind_k; ?>','<?= $artikel['path']; ?>')"><i class="material-icons">edit</i></button>
                            <button class="btn btn-light" onclick="triggerToast('Komentar akan dihapus?', '/delkomen/<?= $ind_k; ?>/<?= $artikel['path']; ?>')"><i class="material-icons">delete_forever</i></button>
                        </div>
                    <?php } ?>
                </div>
                <hr>
            <?php } ?>
        </div>
    </div>
    <div class="container mb-5">
        <h5 class="jdl-section mb-3">Artikel Serupa</h5>
        <div class="d-flex flex-column gap-3" style="min-height: 100px;">
            <?php foreach ($artikelTerkait as $ind_a => $a) { ?>
                <div class="card-artikel-kecil" onclick="pergiKeArtikel(`<?= $a['path']; ?>`)">
                    <img class="rounded" src="<?= $a['header']; ?>" alt="<?= $a['judul']; ?>">
                    <div style="position: relative;" class="d-flex flex-column">
                        <p class="m-0 judul"><?= $a['judul']; ?></p>
                        <div class="container-isi">
                            <div class="overlay-isi"></div>
                            <p class="m-0 isi"><?= $a['isi'][0]['teks']; ?></p>
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
    <div class="container">
        <h5 class="jdl-section">Produk Terkait</h5>
        <!-- <h1 class="mb-1">Mencari produk terkait artikel diatas</h1> -->
        <div class="card-group1 no-scroll">
            <?php foreach ($produkTerkait as $p) { ?>
                <a class="card1" href="/product/<?= $p['path']; ?>">
                    <?php if ($p['diskon']) { ?>
                        <p class="diskon">-<?= number_format((float)$p['diskon'], 2, '.', ''); ?>%</p>
                    <?php } ?>
                    <div style="position: relative; width: 100%; aspect-ratio: 1 / 1;">
                        <img class="img-card1-wm" src="<?= base_url('img/WM Black 300.webp'); ?>" alt="Watermark Lunarea">
                        <img class="img-card1" src="data:image/webp;base64,<?= base64_encode($p['gambar']); ?>" alt="<?= $p['nama']; ?>">
                    </div>
                    <div>
                        <h5 class="mb-0"><?= $p['nama']; ?></h5>
                        <?php foreach (json_decode($p['varian'], true) as $v) { ?>
                            <p class="mb-0 varian"><?= $v ?></p>
                        <?php } ?>
                        <!-- <p class="mb-0 varian"><?= implode(" - ", json_decode($p['varian'], true)); ?></p> -->
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
                        <!-- <p>★☆☆ (<?= $p['rate']; ?>)</p> -->
                    </div>
                </a>
            <?php } ?>
        </div>
        <div class="mx-auto mt-2" style="width: fit-content;">
            <a href="/all" class="btn mx-auto btn-primary1" style="width: fit-content;">Lihat Semua Produk</a>
        </div>
    </div>
</div>
<script>
    function pergiKeArtikel(judulArtikel) {
        console.log(judulArtikel)
        window.location.href = '/article/' + judulArtikel
    }
</script>
<?php if (session()->get('role') == 1) { ?>
    <script>
        const editKomenElm = document.getElementById('edit-komen')
        const komen = JSON.parse(<?= json_encode($komenJson); ?>);
        const formEditElm = document.getElementById('form-edit');
        console.log(komen)

        function openEditKomen(indKomen, judulArtikel) {
            editKomenElm.classList.remove('d-none')
            editKomenElm.classList.add('d-flex')
            document.querySelector('#form-edit input[name="nama_edit"]').value = komen[indKomen].nama;
            document.querySelector('#form-edit input[name="isi_edit"]').value = komen[indKomen].isi;
            formEditElm.action = '/editkomen/' + indKomen + "/" + judulArtikel
        }

        function closeEditKomen() {
            editKomenElm.classList.add('d-none')
            editKomenElm.classList.remove('d-flex')
        }
    </script>
<?php } ?>
<?= $this->endSection(); ?>