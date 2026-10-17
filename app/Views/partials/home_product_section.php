<?php if (!empty($sectionProducts)) { ?>
<section class="container home-product-section" aria-labelledby="<?= esc($sectionId, 'attr'); ?>">
    <div class="home-product-section-heading">
        <h2 class="jdl-section home-product-section-title" id="<?= esc($sectionId, 'attr'); ?>"><?= esc($sectionTitle); ?></h2>
        <a class="home-product-section-link" href="/all">Lihat semua <span aria-hidden="true">→</span></a>
    </div>
        <div class="card-group1 no-scroll">
            <?php foreach ($sectionProducts as $p) {
                $variants = json_decode($p['varian'] ?? '[]', true);
                $variants = is_array($variants) ? $variants : [];
            ?>
            <a class="card1 store-product-card" href="/product/<?= esc($p['path'], 'attr'); ?>">
                <div class="product-card-media image-skeleton" style="position: relative; width: 100%; aspect-ratio: 1 / 1;">
                    <img class="img-card1-wm" src="<?= base_url('img/WM Black 300.webp'); ?>" alt="Watermark Lunarea">
                    <img data-skeleton-image class="img-card1" loading="lazy" decoding="async" src="<?= esc(\App\Services\PublicCatalogImage::productUrl($p), 'attr'); ?>"
                        alt="<?= esc($p['nama'], 'attr'); ?>">
                    <?php
                    $terjualTampil = !empty($p['terjual_custom']) && $p['terjual_custom'] > 0 ? $p['terjual_custom'] : ($p['terjual'] ?? 0);
                    if ($terjualTampil > 0) {
                    ?>
                    <span class="terjual-badge">
                        <?= $terjualTampil >= 1000 ? number_format($terjualTampil/1000, 1, ",", ".") . 'rb+' : $terjualTampil; ?> Terjual
                    </span>
                    <?php } ?>
                </div>
                <div class="product-card-body">
                    <div class="product-card-heading">
                        <h5 class="mb-0" title="<?= esc($p['nama'], 'attr'); ?>"><?= esc($p['nama']); ?></h5>
                        <?= view('partials/product_card_variants', ['cardVariants' => $variants], ['saveData' => false]); ?>
                    </div>
                    <div class="product-card-pricing">
                        <?php if ($p['diskon']) { ?>
                        <div class="product-card-price-meta">
                            <p class="mb-0 diskon-coret"
                                style="text-decoration: line-through; color: grey; width:fit-content;">
                                Rp
                                <?= number_format($p['harga'], 0, ",", "."); ?></p>
                            <p class="diskon-bwh">-<?= (int)$p['diskon']; ?>%</p>
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
                    <!-- <p>★★★☆☆ (<?= $p['rate']; ?>)</p> -->
                </div>
            </a>
            <?php } ?>
        </div>
</section>
<?php } ?>
