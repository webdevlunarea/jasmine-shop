<?= $this->extend('layout/template'); ?>
<?= $this->section('content'); ?>
<link rel="stylesheet" href="/css/luna-mapping-admin.css">
<div class="konten admin-module-page luna-mapping-page"><div class="container">
    <?= view('partials/admin_page_heading', ['adminHeadingTitle'=>'Pemetaan MyLuna', 'adminHeadingGroup'=>'Katalog', 'adminHeadingDescription'=>'Pastikan setiap produk dan varian toko terhubung ke produk yang tepat di MyLuna.'], ['saveData'=>false]); ?>
    <?php if ($msg) { ?><div class="alert alert-info" role="status"><?= esc($msg); ?></div><?php } ?>
    <div class="mapping-summary" aria-label="Ringkasan pemetaan">
        <div><span>Produk website</span><strong><?= (int)$total; ?></strong></div>
        <div><span>Referensi tersimpan</span><strong><?= (int)$mapped; ?></strong></div>
        <div><span>Belum terhubung</span><strong><?= (int)($total-$mapped); ?></strong></div>
    </div>
    <p class="mapping-notice">Stok produk dan varian Lunarea mengikuti MyLuna melalui sinkronisasi katalog. Gambar dan konten visual tetap milik Lunarea. Harga serta pesanan lama tidak berubah.
        <?php if (!$identityEnabled) { ?><strong>Penggunaan identitas baru di checkout masih nonaktif.</strong><?php } else { ?><strong>Identitas baru digunakan untuk pesanan berikutnya.</strong><?php } ?>
    </p>
    <div class="mapping-workspace">
        <section class="mapping-panel" aria-labelledby="mapping-list-title">
            <header><h3 id="mapping-list-title">Produk Lunarea</h3><p>Pilih satu produk untuk diperiksa.</p></header>
            <form method="get" action="/lunamappingadmin" class="mapping-filter">
                <label for="mapping-search">Cari nama atau ID produk</label>
                <div class="d-flex gap-2"><input id="mapping-search" class="form-control" type="search" name="q" value="<?= esc($q, 'attr'); ?>" maxlength="100"><button class="btn orders-secondary" type="submit">Cari</button></div>
                <label class="mapping-check"><input type="checkbox" name="pending" value="1" <?= $pending ? 'checked' : ''; ?>> Hanya belum terhubung</label>
            </form>
            <div class="mapping-product-list">
                <?php foreach ($products as $p) { $link = '/lunamappingadmin?' . http_build_query(['product'=>$p['id'], 'q'=>$q, 'pending'=>$pending?'1':'0', 'page'=>$page]) . '#mapping-editor'; ?>
                <a class="mapping-product <?= ($selected['id'] ?? '') === $p['id'] ? 'is-selected' : ''; ?>" href="<?= esc($link, 'attr'); ?>" <?= ($selected['id'] ?? '') === $p['id'] ? 'aria-current="true"' : ''; ?>>
                    <span><strong><?= esc($p['nama']); ?></strong><small><?= esc($p['id']); ?><?= empty($p['active']) ? ' · Nonaktif' : ''; ?></small></span>
                    <span class="mapping-status"><?= $p['mapping_error'] ? 'Perlu diperiksa' : ($p['identity'] ? 'Tersimpan' : 'Belum terhubung'); ?></span>
                </a>
                <?php } ?>
                <?php if (!$products) { ?><p class="mapping-empty">Tidak ada produk sesuai pencarian.</p><?php } ?>
            </div>
            <nav class="mapping-pagination" aria-label="Halaman daftar produk">
                <?php if ($page > 1) { ?><a class="btn orders-secondary" href="<?= esc('/lunamappingadmin?'.http_build_query(['page'=>$page-1,'q'=>$q,'pending'=>$pending?'1':'0']), 'attr'); ?>">Sebelumnya</a><?php } ?>
                <span><?= (int)$page; ?> / <?= (int)$pages; ?></span>
                <?php if ($page < $pages) { ?><a class="btn orders-secondary" href="<?= esc('/lunamappingadmin?'.http_build_query(['page'=>$page+1,'q'=>$q,'pending'=>$pending?'1':'0']), 'attr'); ?>">Berikutnya</a><?php } ?>
            </nav>
        </section>
        <section class="mapping-panel mapping-editor" id="mapping-editor" aria-labelledby="mapping-editor-title">
            <?php if (!$selected) { ?>
                <div class="mapping-empty"><i class="material-icons" aria-hidden="true">compare_arrows</i><h3 id="mapping-editor-title">Mulai dari satu produk</h3><p>Pilih produk di daftar, kemudian cocokkan produk dan setiap variannya. Nama yang mirip bukan bukti pasangan yang sama.</p></div>
            <?php } elseif ($selected['mapping_error']) { ?>
                <h3 id="mapping-editor-title"><?= esc($selected['nama']); ?></h3><div class="alert alert-warning"><?= esc($selected['mapping_error']); ?></div>
            <?php } else { ?>
                <header><span class="mapping-eyebrow">Produk yang diperiksa</span><h3 id="mapping-editor-title"><?= esc($selected['nama']); ?></h3><code><?= esc($selected['id']); ?></code></header>
                <a class="mapping-product-reference" href="/editproduct/<?= esc(rawurlencode($selected['id']), 'attr'); ?>" target="_blank" rel="noopener">Buka rincian produk website <span aria-hidden="true">↗</span></a>
                <div class="mapping-catalogue-toolbar"><p id="catalogue-status" role="status" aria-live="polite">Katalog belum dimuat.</p><button id="reload-catalogue" type="button" class="btn orders-secondary">Muat ulang katalog</button></div>
                <form action="/lunamappingadmin/save" method="post" id="mapping-form">
                    <?= csrf_field(); ?>
                    <input type="hidden" name="website_id" value="<?= esc($selected['id'], 'attr'); ?>">
                    <input type="hidden" name="revision" value="<?= esc($selected['revision'], 'attr'); ?>">
                    <input type="hidden" name="master_fingerprint" id="master-fingerprint" value="">
                    <fieldset id="mapping-fields" disabled>
                        <legend class="visually-hidden">Pilihan produk dan varian MyLuna</legend>
                        <label for="master-search">Cari produk MyLuna</label><input id="master-search" class="form-control" type="search" placeholder="Ketik nama, SKU, atau ID" autocomplete="off">
                        <label for="master-product">Produk MyLuna</label><select id="master-product" name="master_id" class="form-select" required><option value="">Pilih produk MyLuna</option></select>
                        <p class="mapping-help">Pilih berdasarkan model dan spesifikasi yang sama. Saat pasangan disimpan, stok terkini MyLuna diselaraskan ke Lunarea. Produk yang sudah terhubung ke ID website lain tidak ditawarkan.</p>
                        <div id="master-detail" class="mapping-master-detail" hidden></div>
                        <div id="variant-mapping" class="mapping-variants"></div>
                        <p id="mapping-validation" class="mapping-help" role="status" aria-live="polite"></p>
                        <label for="review-note">Catatan pemeriksaan</label><textarea id="review-note" name="review_note" class="form-control" rows="3" minlength="10" maxlength="500" required placeholder="Contoh: model MT 120 dan warna pada katalog sudah diperiksa."></textarea>
                        <label class="mapping-check mapping-confirm"><input type="checkbox" name="confirmed" value="1" required> Saya sudah memeriksa model, spesifikasi, dan seluruh pasangan varian.</label>
                        <button class="btn btn-primary1" id="save-mapping" type="submit" disabled>Simpan pasangan</button>
                    </fieldset>
                </form>
                <p class="mapping-help mt-3">Pasangan yang sudah tersimpan tidak dapat dialihkan ke ID lain melalui halaman ini. Jika salah, lakukan rekonsiliasi terpisah sebelum meneruskan pesanan.</p>
                <script type="application/json" id="mapping-website-data"><?= json_encode(['id'=>$selected['id'], 'variants'=>$selected['variants'], 'identity'=>$selected['identity']], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR); ?></script>
                <script src="/js/luna-mapping-admin.js" defer></script>
            <?php } ?>
        </section>
    </div>
</div></div>
<?= $this->endSection(); ?>
