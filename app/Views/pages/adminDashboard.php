<?= $this->extend('layout/template'); ?>
<?= $this->section('content'); ?>
<main class="konten admin-module-page admin-dashboard-page">
    <div class="admin-dashboard-wrap">
        <header class="admin-dashboard-welcome">
            <div>
                <p class="admin-dashboard-kicker">LUNAREA · WORKSPACE</p>
                <h2>Selamat datang<?= trim((string) session('nama')) !== '' ? ', ' . esc(trim((string) session('nama'))) : ''; ?></h2>
                <p>Ringkasan kerja Anda hari ini, sesuai akses <span><?= esc($roleLabel); ?></span>.</p>
            </div>
            <a class="admin-dashboard-store" href="/" aria-label="Buka toko Lunarea">
                <i class="material-icons" aria-hidden="true">open_in_new</i><span>Lihat toko</span>
            </a>
        </header>

        <?php if ($metrics): ?>
        <section class="admin-dashboard-metrics" aria-label="Ringkasan operasional">
            <?php
            $metricItems = [];
            if (array_key_exists('pendingOrders', $metrics)) $metricItems[] = ['Pesanan menunggu pembayaran', $metrics['pendingOrders'], 'schedule', '/listcustomer'];
            if (array_key_exists('processingOrders', $metrics)) $metricItems[] = ['Pesanan diproses', $metrics['processingOrders'], 'inventory', '/listcustomer'];
            if (array_key_exists('shippedOrders', $metrics)) $metricItems[] = ['Pesanan dikirim', $metrics['shippedOrders'], 'local_shipping', '/listcustomer'];
            if (array_key_exists('customers', $metrics)) $metricItems[] = ['Pelanggan aktif', $metrics['customers'], 'group', '/customerdirectoryadmin'];
            if (array_key_exists('products', $metrics)) $metricItems[] = ['Produk aktif', $metrics['products'], 'chair', '/listproduct'];
            if (array_key_exists('lowStock', $metrics)) $metricItems[] = ['Produk stok ≤ 5', $metrics['lowStock'], 'production_quantity_limits', '/stokadmin/all/1'];
            foreach ($metricItems as [$label, $value, $icon, $url]): ?>
                <a class="admin-dashboard-metric" href="<?= esc($url, 'attr'); ?>">
                    <span class="admin-dashboard-metric-icon"><i class="material-icons" aria-hidden="true"><?= esc($icon); ?></i></span>
                    <span class="admin-dashboard-metric-copy"><span><?= esc($label); ?></span><strong><?= $value === null ? '—' : number_format((int) $value, 0, ',', '.'); ?></strong></span>
                    <i class="material-icons admin-dashboard-arrow" aria-hidden="true">arrow_forward</i>
                </a>
            <?php endforeach; ?>
        </section>
        <?php endif; ?>

        <section class="admin-dashboard-section" aria-labelledby="admin-dashboard-shortcuts-title">
            <div class="admin-dashboard-section-heading">
                <div><p class="admin-dashboard-kicker">AKSES CEPAT</p><h3 id="admin-dashboard-shortcuts-title">Menu kerja</h3></div>
                <p>Pilih bagian yang ingin Anda kelola.</p>
            </div>
            <?php if ($shortcuts): ?>
                <div class="admin-dashboard-shortcuts">
                    <?php foreach ($shortcuts as $shortcut): ?>
                        <a class="admin-dashboard-shortcut" href="<?= esc($shortcut['url'], 'attr'); ?>">
                            <span class="admin-dashboard-shortcut-icon"><i class="material-icons" aria-hidden="true"><?= esc($shortcut['icon']); ?></i></span>
                            <span class="admin-dashboard-shortcut-copy"><strong><?= esc($shortcut['label']); ?></strong><small><?= esc($shortcut['description']); ?></small></span>
                            <i class="material-icons admin-dashboard-arrow" aria-hidden="true">arrow_forward</i>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="admin-dashboard-empty"><i class="material-icons" aria-hidden="true">admin_panel_settings</i><p>Akses menu belum ditetapkan. Hubungi administrator untuk mengatur hak akses Anda.</p></div>
            <?php endif; ?>
        </section>
        <p class="admin-dashboard-note"><i class="material-icons" aria-hidden="true">lock</i>Data ringkasan hanya menampilkan informasi yang sesuai dengan hak akses akun Anda.</p>
    </div>
</main>
<?= $this->endSection(); ?>
