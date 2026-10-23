<?php
$adminHeadingTitle = (string)($adminHeadingTitle ?? 'Administrasi');
$adminHeadingGroup = (string)($adminHeadingGroup ?? 'Lunarea admin');
$adminHeadingDescription = (string)($adminHeadingDescription ?? '');
$adminHeadingActions = is_array($adminHeadingActions ?? null) ? $adminHeadingActions : [];
$headingRole = \App\Services\Auth\AdminPermissions::normalizeRole(session('admin_role') ?: 'ADMIN');
if ($headingRole !== 'DEV') {
    $plainTitles = [
        'Program Afiliasi' => 'Program berbagi', 'Pemetaan MyLuna' => 'Cocokkan produk',
        'Direktori pengguna' => 'Daftar pelanggan', 'Suspend akun pelanggan' => 'Batasi akun pelanggan',
        'Akun & email spam' => 'Akun & pendaftaran', 'Gambar kategori' => 'Foto kategori',
        'Invoice' => 'Tagihan', 'Flash Sale' => 'Promo kilat', 'Tes notifikasi' => 'Coba notifikasi',
    ];
    $plainGroups = ['Promosi & konten' => 'Promosi', 'Operasional' => 'Toko', 'Katalog' => 'Produk', 'Pengaturan keamanan' => 'Keamanan'];
    $plainDescriptions = [
        'Kelola tautan afiliasi produk dan komisi Luna Poin untuk pesanan website maupun Android.' => 'Atur tautan berbagi dan hadiah Luna Poin dari pesanan di website atau aplikasi.',
        'Pastikan setiap produk dan varian toko terhubung ke produk yang tepat di MyLuna.' => 'Cocokkan produk toko dengan produk di sistem stok.',
        'Gunakan WebP atau AVIF, rekomendasi 300 × 300 px. Periksa preview sebelum menyimpan.' => 'Gunakan gambar persegi sekitar 300 × 300 piksel. Lihat hasilnya sebelum menyimpan.',
        'Kelola domain email sementara dan bersihkan pendaftaran kedaluwarsa. Akun yang terkait pesanan, retur, preorder, rating, poin, atau penukaran voucher akan dilewati.' => 'Atur pendaftaran mencurigakan dan hapus akun lama yang tidak memiliki aktivitas toko.',
        'Tangguhkan akses akun yang melanggar aturan. Setiap tindakan mencatat admin, waktu, dan alasan.' => 'Batasi sementara akun yang melanggar aturan. Catatan tindakan tersimpan untuk ditinjau.',
    ];
    $adminHeadingTitle = $plainTitles[$adminHeadingTitle] ?? $adminHeadingTitle;
    $adminHeadingGroup = $plainGroups[$adminHeadingGroup] ?? $adminHeadingGroup;
    $adminHeadingDescription = $plainDescriptions[$adminHeadingDescription] ?? $adminHeadingDescription;
}
?>
<header class="admin-module-heading">
    <div>
        <p class="admin-section-kicker"><?= esc($adminHeadingGroup); ?></p>
        <h2><?= esc($adminHeadingTitle); ?></h2>
        <?php if ($adminHeadingDescription !== '') { ?><p class="admin-module-description"><?= esc($adminHeadingDescription); ?></p><?php } ?>
    </div>
    <?php if ($adminHeadingActions) { ?>
    <div class="admin-module-actions">
        <?php foreach ($adminHeadingActions as $action) { ?>
        <a class="btn <?= !empty($action['primary']) ? 'btn-primary1' : 'orders-secondary'; ?>" href="<?= esc($action['href'], 'attr'); ?>" <?= !empty($action['external']) ? 'target="_blank" rel="noopener noreferrer"' : ''; ?>>
            <?php if (!empty($action['icon'])) { ?><i class="material-icons" aria-hidden="true"><?= esc($action['icon']); ?></i><?php } ?>
            <span><?= esc($action['label']); ?></span>
        </a>
        <?php } ?>
    </div>
    <?php } ?>
</header>
