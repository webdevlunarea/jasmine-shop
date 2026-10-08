<?= $this->extend('layout/template'); ?>
<?= $this->section('content'); ?>
<?php
$subtotal = 0;
$itemCount = !empty($keranjang) ? count($keranjang) : 0;
$hasStockIssue = count($indStokHabis ?? []) > 0;
?>
<div class="konten cart-page-shell">
    <div class="container">
        <?php if (!empty($msg)) { ?>
            <div class="cart-alert mb-3">
                <i class="material-icons" aria-hidden="true">info</i>
                <span><?= esc($msg); ?></span>
            </div>
        <?php } ?>

        <div class="cart-page-heading">
            <div>
                <p class="cart-eyebrow mb-1">Keranjang Belanja</p>
                <h3 class="mb-1">Produk pilihan kamu</h3>
                <p class="text-secondary mb-0">Periksa varian, jumlah, dan stok sebelum lanjut ke checkout.</p>
            </div>
            <a href="/all" class="btn btn-outline-success cart-continue-btn">
                <i class="material-icons" aria-hidden="true">add_shopping_cart</i>
                Tambah Produk
            </a>
        </div>

        <div class="cart-layout">
            <section class="cart-items-panel">
                <?php if (!empty($keranjang)) { ?>
                    <?php foreach ($produk as $index => $p) { ?>
                        <?php
                        $isStockIssue = in_array($index, $indStokHabis ?? [], true);
                        $discount = (int)($p['diskon'] ?? 0);
                        $basePrice = (int)($p['harga'] ?? 0);
                        $finalPrice = $discount > 0 ? (int)round(((100 - $discount) / 100) * $basePrice) : $basePrice;
                        $lineTotal = $finalPrice * (int)$jumlah[$index];
                        $subtotal += $lineTotal;
                        ?>
                        <article class="card-cart <?= $isStockIssue ? 'is-warning' : ''; ?>">
                            <a href="/product/<?= esc($p['path'], 'url'); ?>" class="cart-product-link">
                                <span class="cart-product-img-wrap">
                                    <img src="data:image/webp;base64,<?= base64_encode($gambar[$index]); ?>" alt="<?= esc($p['nama']); ?>">
                                    <?php if ($discount > 0) { ?>
                                        <span class="cart-discount-badge">-<?= $discount; ?>%</span>
                                    <?php } ?>
                                </span>
                                <span class="cart-product-info">
                                    <strong class="cart-product-name <?= $isStockIssue ? 'text-danger' : ''; ?>"><?= esc($p['nama']); ?></strong>
                                    <span class="cart-variant <?= $isStockIssue ? 'text-danger' : ''; ?>">Varian: <?= esc($keranjang[$index]['varian']); ?></span>
                                    <?php if ($isStockIssue) { ?>
                                        <span class="cart-stock-warning"><i class="material-icons" aria-hidden="true">warning</i> Stok tidak mencukupi</span>
                                    <?php } else { ?>
                                        <span class="cart-price-row">
                                            <?php if ($discount > 0) { ?>
                                                <span class="cart-price-before">Rp <?= number_format($basePrice, 0, ',', '.'); ?></span>
                                            <?php } ?>
                                            <span class="cart-price-now">Rp <?= number_format($finalPrice, 0, ',', '.'); ?></span>
                                        </span>
                                    <?php } ?>
                                </span>
                            </a>

                            <div class="cart-item-actions">
                                <div class="cart-line-total">
                                    <span>Total</span>
                                    <strong>Rp <?= number_format($lineTotal, 0, ',', '.'); ?></strong>
                                </div>
                                <div class="cart-action-row">
                                    <form action="/delcart/<?= $index; ?>" method="post">
                                        <button type="submit" class="cart-delete-btn" aria-label="Hapus <?= esc($p['nama']); ?> dari keranjang">
                                            <i class="material-icons" aria-hidden="true">delete</i>
                                        </button>
                                    </form>
                                    <div class="cart-qty-control">
                                        <form action="/redcart/<?= $index; ?>" method="post">
                                            <button type="submit" aria-label="Kurangi jumlah">−</button>
                                        </form>
                                        <input disabled type="number" class="<?= $isStockIssue ? 'text-danger' : ''; ?>" value="<?= (int)$jumlah[$index]; ?>" aria-label="Jumlah produk">
                                        <form action="/addcart/<?= esc($p['id'], 'url'); ?>/<?= rawurlencode($keranjang[$index]['varian']); ?>/<?= (int)$keranjang[$index]['index_gambar']; ?>" method="post">
                                            <button type="submit" aria-label="Tambah jumlah">+</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </article>
                    <?php } ?>
                <?php } else { ?>
                    <div class="cart-empty-state">
                        <i class="material-icons" aria-hidden="true">shopping_cart</i>
                        <h5>Keranjangmu masih kosong</h5>
                        <p class="text-secondary mb-3">Yuk pilih furniture favorit dan simpan di keranjang sebelum checkout.</p>
                        <a href="/all" class="btn btn-primary1">Mulai Belanja</a>
                    </div>
                <?php } ?>
            </section>

            <aside class="cart-total">
                <div class="cart-summary-header">
                    <span class="cart-summary-icon"><i class="material-icons" aria-hidden="true">receipt_long</i></span>
                    <div>
                        <h5 class="mb-0">Ringkasan</h5>
                        <p class="text-secondary mb-0"><?= (int)$itemCount; ?> item di keranjang</p>
                    </div>
                </div>
                <div class="cart-summary-row">
                    <span>Subtotal</span>
                    <strong>Rp <?= number_format($subtotal, 0, ',', '.'); ?></strong>
                </div>
                <div class="cart-summary-row">
                    <span>Total berat</span>
                    <strong><?= number_format((float)$berat, 0, ',', '.'); ?> kg</strong>
                </div>
                <?php if ($hasStockIssue) { ?>
                    <div class="cart-summary-warning">
                        <i class="material-icons" aria-hidden="true">error_outline</i>
                        Ada item dengan stok kurang. Sesuaikan jumlah atau hapus item tersebut dulu.
                    </div>
                <?php } ?>
                <?php if ($adaPesananPending) { ?>
                    <a class="btn btn-outline-danger w-100 mt-3" href="/order/<?= esc($adaPesananPending['id_midtrans'], 'url'); ?>">Selesaikan pesanan aktif</a>
                <?php } else { ?>
                    <a class="btn btn-primary1 w-100 mt-3 <?= !empty($keranjang) && !$hasStockIssue ? '' : 'disabled'; ?>" href="/checkout">Lanjut Checkout</a>
                <?php } ?>
                <p class="cart-summary-note mb-0 mt-3">Ongkir dan voucher akan dihitung di halaman checkout.</p>
            </aside>
        </div>
    </div>
</div>
<script>
    window.localStorage.removeItem('notif-cart');
</script>
<?= $this->endSection(); ?>
