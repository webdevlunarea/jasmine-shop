<?= $this->extend('layout/template'); ?>
<?= $this->section('content'); ?>
<div class="konten artikel">
    <div class="container">
        <?php
        // === NEW: deteksi halaman pertama ===
        // jika $pagination tidak ada (misal tidak pakai paginate manual), dianggap halaman 1
        $isFirstPage = !isset($pagination) || (int)($pagination['current'] ?? 1) <= 1;
        ?>

        <style>
        /* === Pagination Look & Feel (ringkas, rapi, nyatu tema) === */
        .lunarea-pager {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .5rem;
            padding: .6rem .9rem;
            border: 1px solid #e5e7eb;
            border-radius: 999px;
            background: linear-gradient(180deg, #ffffff, #f9fafb);
            box-shadow: 0 6px 16px rgba(0, 0, 0, .06);
            width: fit-content;
            margin-left: auto;
            margin-right: auto;
        }

        @media (max-width: 576px) {
            .lunarea-pager {
                border-radius: 14px;
                flex-wrap: wrap;
                gap: .4rem;
            }
        }

        .lunarea-pager .btn {
            border-radius: 999px;
            padding: .45rem .8rem;
            transition: transform .15s ease;
        }

        .lunarea-pager .btn:hover {
            transform: translateY(-1px);
        }

        .lunarea-pager .btn-light {
            background: #fff;
            border: 1px solid #e5e7eb;
        }

        .lunarea-pager .btn-light:hover {
            background: #f3f4f6;
        }

        .lunarea-pager .btn-primary1 {
            box-shadow: 0 4px 10px rgba(232, 74, 73, .25);
        }

        .lunarea-pager .btn.disabled,
        .lunarea-pager .btn:disabled {
            opacity: .5;
            pointer-events: none;
        }

        .lunarea-pager .ellipsis {
            pointer-events: none;
            min-width: 36px;
            text-align: center;
            color: #9ca3af;
        }

        .lunarea-pager .indicator {
            font-size: .9rem;
            color: #6b7280;
            margin-right: .25rem;
            white-space: nowrap;
        }
        </style>

        <form action="/actionsearcharticle" method="post" class="mb-2">
            <div class="d-flex gap-1 align-items-center w-100">
                <select onchange="changeKategori(event)" class="form-select" style="width: fit-content" name="kategori"
                    id="">
                    <option <?= isset($category) ? '' : 'selected'; ?> value="semua">Semua Kategori</option>
                    <option <?= isset($category) ? ($category == 'edukasi' ? 'selected' : '') : ''; ?> value="edukasi">
                        Edukasi</option>
                    <option <?= isset($category) ? ($category == 'tips-trik' ? 'selected' : '') : ''; ?>
                        value="tips-trik">Tips & Trik</option>
                    <option <?= isset($category) ? ($category == 'rekomendasi' ? 'selected' : '') : ''; ?>
                        value="rekomendasi">Rekomendasi</option>
                    <option <?= isset($category) ? ($category == 'plus-minus' ? 'selected' : '') : ''; ?>
                        value="plus-minus">Plus Minus</option>
                </select>
                <script>
                function changeKategori(e) {
                    console.log(e.target.value)
                    window.location.replace('/article/category/' + e.target.value)
                }
                </script>
                <div class="container-search-artikel">
                    <input type="text" placeholder="Cari artikel" class="form-control" name="cari"
                        value="<?= isset($find) ? $find : ''; ?>">
                    <button type="submit" class="btn btn-light"><i class="material-icons">search</i></button>
                </div>
            </div>
        </form>

        <?php if (!isset($find)) { ?>
        <div class="mb-4">
            <div class="p-5 header show-flex-ke-hide"
                style="position: relative; margin-bottom: -50svh; flex-direction: column; justify-content: end; width: 40%;">
                <h1 class="text-light mb-1" style="font-size: 50px; line-height: 52px">Welcome to Lunarea's Article</h1>
                <p class="text-light mb-3">Perbarui informasi & referensi Anda seputar furniture dengan desain ala
                    masyarakat urban</p>
                <div class="d-flex gap-2">
                    <a href="/all" class="btn btn-primary1">Pergi ke Toko</a>
                </div>
            </div>
            <div class="p-4 header hide-ke-show-flex"
                style="position: relative; margin-bottom: -30svh; flex-direction: column; justify-content: end; width: 80%;">
                <h2 class="text-light mb-1">Lunarea's Article</h2>
                <p class="text-light mb-3">Perbarui informasi & referensi Anda seputar furniture dengan desain ala
                    masyarakat urban</p>
                <div class="d-flex gap-2">
                    <a href="/all" class="btn btn-primary1">Pergi ke Toko</a>
                </div>
            </div>
            <img class="d-block rounded header"
                src="https://images.unsplash.com/photo-1613575831056-0acd5da8f085?q=80&w=2070&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D"
                alt="">
        </div>
        <?php } ?>

        <?php
        // Default: TIDAK skip item awal (grid rata 3 untuk halaman > 1)
        $indexAwal = -1;

        // HANYA HALAMAN PERTAMA: tampilkan "Artikel Baru" (item 0..4) lalu sisanya ke grid
        if ($isFirstPage && count($artikel) > 5) { ?>
        <div class="mb-4">
            <div class="d-flex justify-content-between mb-3 align-items-center">
                <h5 class="jdl-section">Artikel Baru</h5>
                <?php if (session()->get('role') == 1) { ?>
                <a href="/addarticle" class="btn btn-primary1">Buat Artikel Baru</a>
                <?php } ?>
            </div>
            <div class="show-flex-ke-hide gap-4">
                <div style="width: calc(100% / 3);">
                    <div class="card-artikel-besar" style="height: 320px;"
                        onclick="pergiKeArtikel(`<?= $artikel[0]['path']; ?>`)">
                        <img class="rounded" src="<?= $artikel[0]['header']; ?>" alt="<?= $artikel[0]['judul']; ?>">
                        <p class="m-0 judul"><?= $artikel[0]['judul']; ?></p>
                        <div class="container-isi">
                            <div class="overlay-isi"></div>
                            <p class="m-0 isi"><?= $artikel[0]['isi']; ?></p>
                        </div>
                        <a class="readmore">Baca selengkapnya</a>
                    </div>
                </div>
                <div style="width: calc(100% / 3);" class="d-flex flex-column gap-4">
                    <div class="card-artikel-kecil" style="height: 150px;"
                        onclick="pergiKeArtikel(`<?= $artikel[1]['path']; ?>`)">
                        <img class="rounded" src="<?= $artikel[1]['header']; ?>" alt="<?= $artikel[1]['judul']; ?>">
                        <div style="position: relative;" class="d-flex flex-column">
                            <p class="m-0 judul"><?= $artikel[1]['judul']; ?></p>
                            <div class="container-isi">
                                <div class="overlay-isi"></div>
                                <p class="m-0 isi"><?= $artikel[1]['isi']; ?></p>
                            </div>
                            <a class="readmore">Baca selengkapnya</a>
                        </div>
                    </div>
                    <div class="card-artikel-kecil" style="height: 150px;"
                        onclick="pergiKeArtikel(`<?= $artikel[2]['path']; ?>`)">
                        <img class="rounded" src="<?= $artikel[2]['header']; ?>" alt="<?= $artikel[2]['judul']; ?>">
                        <div style="position: relative;" class="d-flex flex-column">
                            <p class="m-0 judul"><?= $artikel[2]['judul']; ?></p>
                            <div class="container-isi">
                                <div class="overlay-isi"></div>
                                <p class="m-0 isi"><?= $artikel[2]['isi']; ?></p>
                            </div>
                            <a class="readmore">Baca selengkapnya</a>
                        </div>
                    </div>
                </div>
                <div style="width: calc(100% / 3);" class="d-flex flex-column gap-4">
                    <div class="card-artikel-kecil" style="height: 150px;"
                        onclick="pergiKeArtikel(`<?= $artikel[3]['path']; ?>`)">
                        <img class="rounded" src="<?= $artikel[3]['header']; ?>" alt="<?= $artikel[3]['judul']; ?>">
                        <div style="position: relative;" class="d-flex flex-column">
                            <p class="m-0 judul"><?= $artikel[3]['judul']; ?></p>
                            <div class="container-isi">
                                <div class="overlay-isi"></div>
                                <p class="m-0 isi"><?= $artikel[3]['isi']; ?></p>
                            </div>
                            <a class="readmore">Baca selengkapnya</a>
                        </div>
                    </div>
                    <div class="card-artikel-kecil" style="height: 150px;"
                        onclick="pergiKeArtikel(`<?= $artikel[4]['path']; ?>`)">
                        <img class="rounded" src="<?= $artikel[4]['header']; ?>" alt="<?= $artikel[4]['judul']; ?>">
                        <div style="position: relative;" class="d-flex flex-column">
                            <p class="m-0 judul"><?= $artikel[4]['judul']; ?></p>
                            <div class="container-isi">
                                <div class="overlay-isi"></div>
                                <p class="m-0 isi"><?= $artikel[4]['isi']; ?></p>
                            </div>
                            <a class="readmore">Baca selengkapnya</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
            // NEW: karena sudah tampilkan 0..4 di atas, sisanya mulai dari index 5 untuk grid
            $indexAwal = 5;
        }
        ?>

        <!-- GRID DESKTOP -->
        <div class="show-flex-ke-hide">
            <div style="display:grid; grid-template-columns: repeat(3, 1fr);" class="gap-4">
                <?php foreach ($artikel as $ind_a => $a) {
                    // NEW: jika halaman > 1 -> $indexAwal tetap -1 => semua item masuk grid (rata 3)
                    if ($ind_a > $indexAwal) { ?>
                <div class="card-artikel-besar" onclick="pergiKeArtikel(`<?= $a['path']; ?>`)">
                    <img class="rounded" src="<?= $a['header']; ?>" alt="<?= $a['judul']; ?>">
                    <p class="judul"><?= $a['judul']; ?></p>
                    <div class="container-isi">
                        <div class="overlay-isi"></div>
                        <p class="m-0 isi"><?= $a['isi']; ?></p>
                    </div>
                    <a class="readmore">Baca selengkapnya</a>
                </div>
                <?php }
                } ?>
            </div>
        </div>

        <!-- LIST MOBILE -->
        <div class="hide-ke-show-flex flex-column gap-2">
            <div class="d-flex flex-column gap-3" style="min-height: 100px;">
                <?php foreach ($artikel as $ind_a => $a) { ?>
                <div class="card-artikel-kecil" onclick="pergiKeArtikel(`<?= $a['path']; ?>`)">
                    <img class="rounded" src="<?= $a['header']; ?>" alt="<?= $a['judul']; ?>">
                    <div style="position: relative;" class="d-flex flex-column">
                        <p class="m-0 judul"><?= $a['judul']; ?></p>
                        <div class="container-isi">
                            <div class="overlay-isi"></div>
                            <p class="m-0 isi"><?= $a['isi']; ?></p>
                        </div>
                        <a class="readmore">Baca selengkapnya</a>
                    </div>
                </div>
                <?php } ?>
            </div>
        </div>

        <!-- PAGINATION (satu kali di paling bawah, berlaku untuk desktop & mobile) -->
        <?php if (isset($pagination) && $pagination['totalPages'] > 1): ?>
        <?php
                // label jumlah item
                $from = ($pagination['current'] - 1) * $pagination['perPage'] + 1;
                $to   = min($pagination['total'], $pagination['current'] * $pagination['perPage']);

                // generate pages dengan ellipsis: 1 … (c-1 c c+1) … last
                $makePages = function($current, $totalPages) {
                    $pages = [];
                    $push = function($n) use (&$pages){ $pages[] = $n; };
                    $push(1);
                    if ($current > 3) $push('...');
                    for ($i = $current - 1; $i <= $current + 1; $i++) {
                        if ($i > 1 && $i < $totalPages) $push($i);
                    }
                    if ($current < $totalPages - 2) $push('...');
                    if ($totalPages > 1) $push($totalPages);
                    // remove dupes
                    $unique = [];
                    foreach ($pages as $p) {
                        if ($unique && end($unique) === $p) continue;
                        $unique[] = $p;
                    }
                    return $unique;
                };
                $pages = $makePages($pagination['current'], $pagination['totalPages']);
            ?>
        <div class="lunarea-pager my-4">
            <a class="btn btn-light <?= $pagination['current'] <= 1 ? 'disabled' : '' ?>"
                href="<?= $pagination['current'] <= 1 ? 'javascript:void(0)' : $pagination['firstUrl']; ?>"
                aria-label="Halaman pertama" title="Halaman pertama">
                <i class="material-icons" style="font-size:18px;vertical-align:middle;">first_page</i>
            </a>
            <a class="btn btn-light <?= $pagination['current'] <= 1 ? 'disabled' : '' ?>"
                href="<?= $pagination['current'] <= 1 ? 'javascript:void(0)' : $pagination['prevUrl']; ?>"
                aria-label="Sebelumnya" title="Sebelumnya">
                <i class="material-icons" style="font-size:18px;vertical-align:middle;">chevron_left</i>
            </a>

            <?php foreach ($pages as $num): ?>
            <?php if ($num === '...'): ?>
            <span class="ellipsis">…</span>
            <?php else: ?>
            <a class="btn <?= $num == $pagination['current'] ? 'btn-primary1' : 'btn-light'; ?>"
                href="<?= $num == $pagination['current'] ? 'javascript:void(0)' : $pagination['numberUrl']($num); ?>"
                aria-current="<?= $num == $pagination['current'] ? 'page' : 'false' ?>"
                style="min-width:44px;text-align:center;">
                <?= $num ?>
            </a>
            <?php endif; ?>
            <?php endforeach; ?>

            <a class="btn btn-light <?= $pagination['current'] >= $pagination['totalPages'] ? 'disabled' : '' ?>"
                href="<?= $pagination['current'] >= $pagination['totalPages'] ? 'javascript:void(0)' : $pagination['nextUrl']; ?>"
                aria-label="Berikutnya" title="Berikutnya">
                <i class="material-icons" style="font-size:18px;vertical-align:middle;">chevron_right</i>
            </a>
            <a class="btn btn-light <?= $pagination['current'] >= $pagination['totalPages'] ? 'disabled' : '' ?>"
                href="<?= $pagination['current'] >= $pagination['totalPages'] ? 'javascript:void(0)' : $pagination['lastUrl']; ?>"
                aria-label="Halaman terakhir" title="Halaman terakhir">
                <i class="material-icons" style="font-size:18px;vertical-align:middle;">last_page</i>
            </a>
        </div>
        <?php endif; ?>

    </div>
</div>

<script>
function pergiKeArtikel(judulArtikel) {
    console.log(judulArtikel)
    window.location.href = '/article/' + judulArtikel
}
// UX: scroll ke awal konten saat klik pagination
document.addEventListener('click', (e) => {
    const a = e.target.closest('a');
    if (!a) return;
    const isPager = a.closest('.lunarea-pager');
    if (isPager && !a.classList.contains('disabled')) {
        const root = document.querySelector('.konten.artikel');
        if (root) window.scrollTo({
            top: root.offsetTop - 20,
            behavior: 'smooth'
        });
    }
});
</script>
<?= $this->endSection(); ?>