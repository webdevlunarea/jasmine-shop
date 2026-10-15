    <?php if (!empty($flashSaleProducts)) { ?>
    <section class="container my-4 flash-sale-section" aria-labelledby="flash-sale-title">
        <div class="flash-sale-shell">
            <div class="flash-sale-head">
                <div class="flash-sale-title-wrap">
                    <span class="flash-sale-kicker"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="m13 3-9 11h7l-1 7 10-12h-7l1-6Z"/></svg> <?= esc($flashSaleSettings['kicker'] ?? 'Promo kilat'); ?></span>
                    <h2 id="flash-sale-title" class="flash-sale-title"><?= esc($flashSaleSettings['title'] ?? 'Flash Sale Lunarea'); ?></h2>
                    <p><?= esc($flashSaleSettings['subtitle'] ?? 'Harga spesial untuk produk pilihan. Buruan sebelum waktu habis.'); ?></p>
                </div>
                <div class="flash-sale-action">
                    <div class="flash-countdown" data-flash-countdown data-end="<?= (int)($flashSaleEnd ?? strtotime('today 23:59:59')); ?>" role="timer" aria-label="Sisa waktu Flash Sale" aria-live="off">
                        <span class="flash-countdown-label">Berakhir dalam</span>
                        <div class="flash-countdown-time" aria-label="Hitung mundur flash sale">
                            <span data-unit="hours">00</span><b>:</b><span data-unit="minutes">00</span><b>:</b><span data-unit="seconds">00</span>
                        </div>
                    </div>
                    <a href="/all" class="flash-sale-see-all">Lihat semua <span aria-hidden="true">→</span></a>
                </div>
            </div>
            <div class="flash-sale-products-wrap">
                <button class="flash-sale-nav flash-sale-nav--prev" type="button" aria-label="Geser flash sale ke kiri" data-flash-prev>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="m15 6-6 6 6 6"/></svg>
                </button>
                <div class="flash-sale-products" data-flash-products>
                    <?php foreach ($flashSaleProducts as $p) {
                        $discount = max(0, (int)($p['diskon'] ?? 0));
                        $price = (float)($p['harga'] ?? 0);
                        $salePrice = round(((100 - $discount) / 100) * $price);
                        $stockRaw = (string)($p['stok'] ?? '0');
                        $stockParts = array_filter(array_map('trim', explode(',', $stockRaw)), static fn($v) => $v !== '');
                        $totalStock = 0;
                        foreach ($stockParts as $stockValue) {
                            $totalStock += max(0, (int)$stockValue);
                        }
                        $stockText = $totalStock <= 0 ? 'Stok habis' : ($totalStock <= 5 ? 'Stok terbatas' : 'Stok tersedia');
                    ?>
                    <a class="flash-sale-card" href="/product/<?= esc($p['path']); ?>" aria-label="<?= esc($p['nama']); ?> diskon <?= $discount; ?> persen">
                        <div class="flash-sale-image">
                            <span class="flash-sale-badge">-<?= $discount; ?>%</span>
                            <img class="flash-sale-watermark" src="<?= base_url('img/WM Black 300.webp'); ?>" alt="">
                            <img src="<?= esc(\App\Services\PublicCatalogImage::productUrl($p), 'attr'); ?>" alt="<?= esc($p['nama']); ?>" loading="lazy" decoding="async">
                        </div>
                        <div class="flash-sale-info">
                            <h3 title="<?= esc($p['nama'], 'attr'); ?>"><?= esc($p['nama']); ?></h3>
                            <div class="flash-sale-price">
                                <span>Rp <?= number_format($salePrice, 0, ",", "."); ?></span>
                                <del>Rp <?= number_format($price, 0, ",", "."); ?></del>
                            </div>
                            <div class="flash-sale-stock" aria-label="<?= esc($stockText); ?>">
                                <span><?= esc($stockText); ?></span>
                            </div>
                        </div>
                    </a>
                    <?php } ?>
                </div>
                <button class="flash-sale-nav flash-sale-nav--next" type="button" aria-label="Geser flash sale ke kanan" data-flash-next>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="m9 6 6 6-6 6"/></svg>
                </button>
            </div>
        </div>
    </section>
    <?php } ?>
