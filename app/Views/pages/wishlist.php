<?= $this->extend('layout/template'); ?>
<?= $this->section('content'); ?>
<div class="konten account-shell">
    <div class="container">
        <?php $activeAccountMenu = 'wishlist'; ?>
        <div class="account-layout">
            <?= $this->include('partials/account_nav'); ?>
            <main class="account-content-card">
        <div class="account-page-heading d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
            <h1 class="mb-0">Daftar Favorit <span class="account-heading-count">(<?= count($wishlist); ?>)</span></h1>
            <form action="/wishlisttocart" method="post" data-confirm-submit data-confirm-message="Tambahkan semua produk favorit yang tersedia ke keranjang?">
                        <button type="submit" class="btn btn-outline-dark" title="Tambahkan semua produk favorit ke keranjang" <?= count($wishlist) === 0 ? 'disabled' : ''; ?>>Tambah semua</button>
            </form>
        </div>
        <?php if (count($wishlist) > 0) { ?>
            <div class="card-group1 no-scroll">
                <?php foreach ($produk as $p) { ?>
                    <a class="card1 store-product-card" href="/product/<?= $p['path']; ?>">
                        <!-- <img src="data:image/webp;base64,<?= base64_encode($p['gambar']); ?>" alt=""> -->
                        <div class="product-card-media" style="position: relative; width: 100%; aspect-ratio: 1 / 1;">
                            <img class="img-card1-wm" src="<?= base_url('img/WM Black 300.webp'); ?>" alt="Watermark Lunarea">
                            <img class="img-card1" src="data:image/webp;base64,<?= base64_encode($p['wishlist_gambar'] ?? $p['gambar']); ?>" alt="<?= $p['nama']; ?>">
                            <?php if ((float) ($p['diskon'] ?? 0) > 0) { ?>
                            <span class="product-image-discount">-<?= (int) $p['diskon']; ?>%</span>
                            <?php } ?>
                        </div>
                        <div class="product-card-body">
                    <div class="product-card-heading">
                                <h5 class="mb-0" title="<?= esc($p['nama'], 'attr'); ?>"><?= esc($p['nama']); ?></h5>
                                <p class="mb-1 varian" title="<?= esc('Varian dipilih: ' . ($p['wishlist_varian'] ?? '-'), 'attr'); ?>"><b>Varian dipilih:</b> <?= esc($p['wishlist_varian'] ?? '-'); ?></p>
                    </div>
                    <div class="product-card-pricing">
                                <?php if ($p['diskon']) { ?>
                                    <div class="product-card-price-meta">
                                        <p class="mb-0 diskon-coret" style="text-decoration: line-through; color: grey; width:fit-content;">
                                            Rp
                                            <?= number_format($p['harga'], 0, ",", "."); ?></p>
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
                            <span class="d-block small text-secondary mt-2">Klik untuk lihat / ubah varian.</span>
                        </div>
                    </a>
                <?php } ?>
            </div>
        <?php } else { ?>
            <div class="account-empty-state"><h5>Daftar favorit Anda masih kosong.</h5><p class="text-secondary">Simpan produk yang Anda sukai agar mudah ditemukan kembali.</p><a href="/all" class="btn btn-primary1">Jelajahi Produk</a></div>
        <?php } ?>
            </main>
        </div>
    </div>
</div>
<script>
    window.localStorage.removeItem('notif-wishlist');
</script>
<?= $this->endSection(); ?>
