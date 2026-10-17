<?= $this->extend('layout/template'); ?>
<?= $this->section('content'); ?>
<?php if (!session()->get('submitEmail') && session()->get('role') != 1) { ?>
<div id="submit-email" class="d-none justify-content-center align-items-center article-email-modal" aria-hidden="true">
    <section class="article-email-dialog" role="dialog" aria-modal="true" aria-labelledby="article-email-title" tabindex="-1">
        <div class="article-email-dialog-topline">
            <span class="article-email-kicker">Jurnal Lunarea</span>
            <button type="button" class="article-email-close" aria-label="Tutup" onclick="closeSubmitEmail()"><span aria-hidden="true">&times;</span></button>
        </div>
        <h2 id="article-email-title">Inspirasi untuk rumah Anda</h2>
        <p class="article-email-description">Dapatkan artikel, ide penataan, dan kabar terbaru dari Lunarea melalui email.</p>
        <form action="/submitemail/<?= urlencode($artikel['judul']); ?>" method="post" data-confirm-submit data-confirm-message="Daftarkan email Anda untuk menerima inspirasi dan kabar terbaru Lunarea?">
            <label class="article-email-label" for="article-subscribe-email">Alamat email</label>
            <div class="article-email-field">
                <input id="article-subscribe-email" type="email" class="form-control" placeholder="nama@email.com" name="email" autocomplete="email" required>
            </div>
            <div class="article-email-consent">
                <input type="checkbox" required id="syaratsubmitemail">
                <label for="syaratsubmitemail">Saya menyetujui <a href="/kebijakan-privasi">kebijakan privasi</a> Lunarea.</label>
            </div>
            <button type="submit" class="btn btn-primary1 article-email-submit">Berlangganan</button>
        </form>
    </section>
</div>
<script>
    const submitEmailElm = document.getElementById('submit-email');
    const submitEmailDialog = submitEmailElm.querySelector('[role="dialog"]');
    const submitEmailCloseButton = submitEmailElm.querySelector('.article-email-close');
    let opened = false;
    let previousFocus = null;
    function openSubmitEmail() {
        if (opened) return;
        opened = true;
        previousFocus = document.activeElement;
        submitEmailElm.classList.remove('d-none');
        submitEmailElm.classList.add('d-flex');
        submitEmailElm.setAttribute('aria-hidden', 'false');
        document.documentElement.classList.add('article-modal-open');
        submitEmailCloseButton.focus();
    }

    function closeSubmitEmail() {
        submitEmailElm.classList.add('d-none');
        submitEmailElm.classList.remove('d-flex');
        submitEmailElm.setAttribute('aria-hidden', 'true');
        document.documentElement.classList.remove('article-modal-open');
        if (previousFocus && typeof previousFocus.focus === 'function') previousFocus.focus();
    }
    window.addEventListener('scroll', function () {
        if (opened) return;
        const scrollable = document.documentElement;
        const range = scrollable.scrollHeight - window.innerHeight;
        const progress = range > 0 ? (window.scrollY / range) * 100 : 0;
        if (progress >= 60) openSubmitEmail();
    }, { passive: true });
    submitEmailElm.addEventListener('click', function (event) {
        if (event.target === submitEmailElm) closeSubmitEmail();
    });
    document.addEventListener('keydown', function (event) {
        if (submitEmailElm.classList.contains('d-none')) return;
        if (event.key === 'Escape') closeSubmitEmail();
        if (event.key === 'Tab') {
            const focusable = submitEmailElm.querySelectorAll('a[href], button:not([disabled]), input:not([disabled])');
            const first = focusable[0];
            const last = focusable[focusable.length - 1];
            if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last.focus(); }
            else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first.focus(); }
        }
    });
</script>
<?php } ?>
<?php if (session()->get('role') == 1) { ?>
    <div id="edit-komen" class="d-none justify-content-center align-items-center article-email-modal">
        <div class="article-email-dialog">
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
<div class="konten artikel public-article-detail">
    <div class="container mb-5">
        <div class="article-cover-wrap image-skeleton"><img data-skeleton-image src="<?= esc($artikel['header'], 'attr'); ?>" alt="<?= esc($artikel['judul'], 'attr'); ?>" class="header article-cover"></div>
        <article class="p-5 mx-5 show-ke-hide article-content-card">
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
                        <button class="btn btn-danger d-flex" onclick="triggerToast('Artikel <?= $artikel['judul']; ?> akan dihapus?','/delarticle/<?= $artikel['id']; ?>')"><i class="material-icons">delete_forever</i></button>
                    <?php } ?>
                </div>
            </div>
            <h1 class="judul"><?= $artikel['judul'] ?></h1>
            <div class="d-flex justify-content-between">
                <p class="m-0 text-secondary">Ditulis oleh <?= $artikel['penulis']; ?></p>
                <p class="m-0 text-secondary">Terakhir diubah pada <?= $artikel['waktu']; ?></p>
            </div>
            <span class="garis my-3"></span>
            <div class="article-prose"><?= $artikel['isi']; ?></div>
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
        </article>
        <article class="p-4 mx-4 hide-ke-show-block article-content-card article-content-card--mobile">
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
            <div class="article-prose"><?= $artikel['isi']; ?></div>
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
        </article>
    </div>
    <div class="container mb-5 article-comments">
        <h5 class="jdl-section mb-3">Komentar</h5>
        <form action="/addkomen/<?= $artikel['path']; ?>" method="post" data-confirm-submit data-confirm-message="Kirim komentar ini pada artikel? Pastikan isinya sudah sesuai.">
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
    <div class="container mb-5 article-related">
        <h5 class="jdl-section mb-3">Artikel Serupa</h5>
        <?php if (!empty($artikelTerkait)) { ?>
            <div class="article-related-grid">
                <?php foreach ($artikelTerkait as $a) { ?>
                    <a class="article-related-card" href="<?= site_url('article/' . rawurlencode((string) ($a['path'] ?? ''))); ?>">
                        <span class="article-related-card__image image-skeleton">
                            <img data-skeleton-image loading="lazy" decoding="async" src="<?= esc($a['header'] ?? '', 'attr'); ?>" alt="<?= esc($a['judul'] ?? 'Artikel Lunarea', 'attr'); ?>">
                        </span>
                        <span class="article-related-card__body">
                            <span class="article-related-card__title"><?= esc($a['judul'] ?? ''); ?></span>
                            <span class="article-related-card__summary"><?= esc(trim(preg_replace('/\\s+/', ' ', strip_tags((string) ($a['isi'] ?? ''))))); ?></span>
                            <span class="article-related-card__link">Baca artikel <span aria-hidden="true">&rarr;</span></span>
                        </span>
                    </a>
                <?php } ?>
            </div>
        <?php } else { ?>
            <p class="article-related-empty">Belum ada artikel serupa untuk ditampilkan.</p>
        <?php } ?>
    </div>
    <div class="container">
        <h5 class="jdl-section">Produk Terkait</h5>
        <!-- <h1 class="mb-1">Mencari produk terkait artikel diatas</h1> -->
        <div class="card-group1 no-scroll">
            <?php foreach ($produkTerkait as $p) {
                $variants = json_decode($p['varian'] ?? '[]', true);
                $variants = is_array($variants) ? $variants : [];
            ?>
                <a class="card1 store-product-card" href="/product/<?= $p['path']; ?>">
                    <div class="product-card-media image-skeleton" style="position: relative; width: 100%; aspect-ratio: 1 / 1;">
                        <img class="img-card1-wm" src="<?= base_url('img/WM Black 300.webp'); ?>" alt="Watermark Lunarea">
                        <img data-skeleton-image class="img-card1" loading="lazy" decoding="async" src="<?= esc(\App\Services\PublicCatalogImage::productUrl($p), 'attr'); ?>" alt="<?= esc($p['nama'], 'attr'); ?>">
                    </div>
                    <div class="product-card-body">
                    <div class="product-card-heading">
                            <h5 class="mb-0" title="<?= esc($p['nama'], 'attr'); ?>"><?= esc($p['nama']); ?></h5>
                            <?= view('partials/product_card_variants', ['cardVariants' => $variants], ['saveData' => false]); ?>
                    </div>
                    <div class="product-card-pricing">
                            <?php if ($p['diskon']) { ?>
                                <div class="product-card-price-meta">
                                    <p class="mb-0 diskon-coret" style="text-decoration: line-through; color: grey; width:fit-content;">
                                        Rp
                                        <?= number_format($p['harga'], 0, ",", "."); ?></p>
                                    <p class="diskon-bwh">-<?= number_format((float)$p['diskon'], 2, '.', ''); ?>%</p>
                                </div>
                                <p class="mb-0 harga">Rp
                                    <?php
                                    $persen = (100 - $p['diskon']) / 100;
                                    $hasil = $persen * $p['harga'];
                                    echo number_format($hasil, 0, ",", ".");
                                    ?></p>
                            <?php } else { ?>
                                <p class="mb-0 harga">Rp <?= number_format($p['harga'], 0, ",", "."); ?></p>
                            <?php } ?>
                    </div>
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
