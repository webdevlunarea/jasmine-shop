<?= $this->extend('layout/template'); ?>
<?= $this->section('content'); ?>
<style>
    .account-page {
        display: block;
        position: relative;
        z-index: 0;
        clear: both;
        min-height: auto;
        padding: 1rem 0 clamp(46px, 6vw, 86px);
        overflow: visible;
    }
    .account-page > .container {
        position: relative;
        z-index: 1;
    }
    .account-page ~ footer.footer-transparent,
    body:has(.account-page) footer.footer-transparent {
        clear: both;
        position: relative;
        z-index: 1;
        margin-top: 0 !important;
    }
    .account-hero { background: linear-gradient(135deg, rgba(36,56,47,.97), rgba(45,194,107,.86)); color: #fff; border-radius: 24px; padding: 22px; overflow: hidden; position: relative; }
    .account-hero:after { content: ""; position: absolute; right: -70px; top: -70px; width: 190px; height: 190px; border-radius: 50%; background: rgba(255,255,255,.14); }
    .account-avatar { width: 92px; height: 92px; object-fit: cover; border-radius: 50%; border: 4px solid rgba(255,255,255,.55); background: #fff; }
    .account-badge { display: inline-flex; align-items: center; gap: 6px; padding: 6px 10px; border-radius: 999px; background: rgba(255,255,255,.16); color: #fff; font-size: .82rem; font-weight: 700; }
    .account-card { background: #fff; border: 1px solid rgba(17,24,39,.08); border-radius: 20px; padding: 16px; box-shadow: 0 10px 28px rgba(17,24,39,.06); }
    .account-page .row > [class*="col-"] > .account-stat {
        height: 100%;
    }
    .account-card-soft { background: #f7fbf8; border-color: rgba(45,194,107,.18); }
    .account-stat { display: flex; align-items: center; gap: 12px; text-decoration: none; color: inherit; transition: .18s ease; }
    .account-stat:hover { transform: translateY(-2px); color: inherit; }
    .account-stat-icon { width: 44px; height: 44px; border-radius: 15px; display: grid; place-items: center; background: var(--hijau); color: #fff; flex: 0 0 44px; }
    .account-stat h6 { margin: 0; font-weight: 800; }
    .account-stat p { margin: 0; color: #6b7280; font-size: .86rem; }
    .account-menu { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; }
    .account-menu a { min-height: 92px; border: 1px solid rgba(17,24,39,.08); border-radius: 18px; text-decoration: none; color: #111827; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 8px; background: #fff; transition: .18s ease; text-align: center; padding: 10px; }
    .account-menu a:hover { transform: translateY(-2px); border-color: rgba(45,194,107,.45); color: var(--hijau); box-shadow: 0 10px 24px rgba(17,24,39,.08); }
    .account-menu .material-icons { font-size: 28px; color: var(--hijau); }
    .account-order { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 12px 0; border-bottom: 1px solid rgba(17,24,39,.08); }
    .account-order:last-child { border-bottom: 0; }
    .account-main-grid { align-items: flex-start; margin-bottom: clamp(42px, 7vw, 96px); }
    .account-main-grid > [class*="col-"] { min-width: 0; }
    .account-help-card { margin-bottom: 0 !important; }
    .status-pill { display: inline-flex; align-items: center; padding: 5px 10px; border-radius: 999px; font-size: .78rem; font-weight: 800; background: #eef7f1; color: #166534; }
    .status-pill.pending { background: #fff7ed; color: #c2410c; }
    .status-pill.danger { background: #fef2f2; color: #b91c1c; }
    .profile-progress { height: 10px; background: rgba(255,255,255,.28); border-radius: 999px; overflow: hidden; }
    .profile-progress > div { height: 100%; background: #fff; border-radius: 999px; }
    .icon-btn-change-pp { background-color: whitesmoke; color: var(--hijau); width: 46px; height: 46px; display: grid; place-items: center; position: absolute; right: calc(50% - 90px); bottom: 12px; border-radius: 100px; cursor: pointer; box-shadow: 0 8px 18px rgba(0,0,0,.12); }
    .icon-btn-change-pp:hover { background-color: var(--hijau); color: white; }
    .account-form-photo { position: relative; width: fit-content; margin: 0 auto; }
    .account-form-photo img { width: 160px; height: 160px; object-fit: cover; border-radius: 50%; border: 1px solid rgba(17,24,39,.08); }
    @media (max-width: 991.98px) { .account-menu { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
    @media (max-width: 575.98px) { .account-page { padding-top: .75rem; padding-bottom: 88px; } .account-page ~ footer.footer-transparent, body:has(.account-page) footer.footer-transparent { margin-top: 0 !important; } .account-hero { border-radius: 18px; padding: 18px; } .account-avatar { width: 76px; height: 76px; } .account-menu { grid-template-columns: repeat(2, minmax(0, 1fr)); } .account-order { align-items: flex-start; flex-direction: column; } .account-card { border-radius: 16px; } .account-main-grid { margin-bottom: 84px; } }
</style>
<?php
$email = session()->get('email');
$role = session()->get('role');
$formatRupiah = function ($nominal) {
    return 'Rp ' . number_format((float)$nominal, 0, ',', '.');
};
$orderTotal = function ($order) {
    $mid = json_decode($order['data_mid'] ?? '{}', true);
    if (isset($mid['gross_amount'])) return $mid['gross_amount'];
    if (isset($mid['transaction_details']['gross_amount'])) return $mid['transaction_details']['gross_amount'];
    $items = json_decode($order['items'] ?? '[]', true);
    $total = 0;
    if (is_array($items)) {
        foreach ($items as $item) $total += ((float)($item['price'] ?? 0) * (int)($item['quantity'] ?? 1));
    }
    return $total;
};
$statusClass = function ($status) {
    if (in_array($status, ['Menunggu Pembayaran', 'Menunggu Pembayaran Rekening'], true)) return 'pending';
    if (in_array($status, ['Kadaluarsa', 'Ditolak', 'Gagal', 'Dibatalkan'], true)) return 'danger';
    return '';
};
?>
<div class="konten account-page">
    <div class="container">
        <?php if ($role == '0') { $activeAccountMenu = 'account'; ?>
        <div class="account-layout">
            <?= $this->include('partials/account_nav'); ?>
            <main class="account-content-card">
        <?php } ?>

        <?php if ($msg) { ?>
            <div class="alert alert-success" role="alert"><?= esc($msg); ?></div>
        <?php } ?>

        <div class="account-hero mb-3">
            <div class="row align-items-center g-3 position-relative" style="z-index:1;">
                <div class="col-md-auto text-center text-md-start">
                    <img src="<?= esc($foto); ?>" alt="Foto profil <?= esc($nama ?: ($role == '1' ? 'Staf Lunarea' : 'Customer Lunarea')); ?>" class="account-avatar">
                </div>
                <div class="col">
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                        <span class="account-badge"><i class="material-icons" style="font-size:18px;"><?= $role == '1' ? 'admin_panel_settings' : 'verified_user'; ?></i><?= $role == '1' ? 'Akun staf' : (session()->get('active') ? 'Akun aktif' : 'Perlu aktivasi'); ?></span>
                        <span class="account-badge"><i class="material-icons" style="font-size:18px;"><?= $is_google_account ? 'account_circle' : 'mail_outline' ?></i><?= $is_google_account ? 'Masuk dengan Google' : 'Masuk dengan email' ?></span>
                    </div>
                    <h1 class="mb-1"><?= $role == '1' ? 'Profil staf' : 'Halo, ' . esc($nama ?: 'Customer Lunarea'); ?></h1>
                    <p class="mb-3" style="opacity:.9;"><?= esc($email); ?></p>
                    <?php if ($role == '1') { ?><p class="mb-0" style="opacity:.9;">Akun ini digunakan untuk mengelola operasional Lunarea.</p><?php } else { ?>
                        <div class="d-flex justify-content-between small fw-bold mb-1"><span>Kelengkapan profil</span><span><?= (int)$profileCompletion; ?>%</span></div>
                        <div class="profile-progress"><div style="width: <?= (int)$profileCompletion; ?>%;"></div></div>
                    <?php } ?>
                </div>
                <?php if ($role == '0') { ?><div class="col-md-auto d-grid gap-2">
                    <?php if (!empty($pendingPayment)) { ?>
                        <a href="/order/<?= esc($pendingPayment['id_midtrans']); ?>" class="btn btn-light fw-bold">Lanjutkan Pembayaran</a>
                    <?php } ?>
                    <a href="/all" class="btn btn-outline-light fw-bold">Belanja Lagi</a>
                </div><?php } ?>
            </div>
        </div>

        <?php if ($role == '0') { ?>
            <div class="row g-3 mb-3">
                <div class="col-6 col-lg-3"><a class="account-card account-stat" href="/transaction"><span class="account-stat-icon"><i class="material-icons">receipt_long</i></span><span><h6><?= (int)$activeOrders; ?></h6><p>Pesanan aktif</p></span></a></div>
                <div class="col-6 col-lg-3"><a class="account-card account-stat" href="/voucher"><span class="account-stat-icon"><i class="material-icons">local_offer</i></span><span><h6><?= (int)$voucherCount; ?></h6><p>Voucher tersedia</p></span></a></div>
                <div class="col-6 col-lg-3"><a class="account-card account-stat" href="/point"><span class="account-stat-icon"><i class="material-icons">stars</i></span><span><h6><?= number_format((int)$poinTotal, 0, ',', '.'); ?></h6><p>Luna poin</p></span></a></div>
                <div class="col-6 col-lg-3"><a class="account-card account-stat" href="/wishlist"><span class="account-stat-icon"><i class="material-icons">favorite_border</i></span><span><h6><?= (int)$wishlistCount; ?></h6><p>Daftar Favorit</p></span></a></div>
            </div>


        <?php } ?>

        <?php if ($role == '0') { ?>
            <?= view('partials/account_email', ['emailChangeSettings' => $emailChangeSettings ?? []], ['saveData' => false]); ?>
            <?= view('partials/account_username', ['usernameSettings' => $usernameSettings ?? ['available' => false, 'username' => '']], ['saveData' => false]); ?>
            <?= view('partials/account_facebook', ['facebookLinkState' => $facebookLinkState ?? 'disabled'], ['saveData' => false]); ?>
        <?php } ?>
        <div class="row g-3 account-main-grid">
            <div class="col-lg-7">
                <?php if ($role == '0') { ?>
                    <div class="account-card mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h5 class="fw-bold mb-0">Transaksi terbaru</h5>
                            <a href="/transaction" class="small fw-bold text-decoration-none">Lihat semua</a>
                        </div>
                        <?php if (!empty($latestOrders)) { ?>
                            <?php foreach ($latestOrders as $order) { ?>
                                <div class="account-order">
                                    <div>
                                        <div class="fw-bold">#<?= esc($order['id_midtrans']); ?></div>
                                        <div class="text-secondary small"><?= esc($formatRupiah($orderTotal($order))); ?> · <?= esc($order['kurir'] ?: 'Kurir belum dipilih'); ?></div>
                                    </div>
                                    <div class="text-start text-sm-end">
                                        <span class="status-pill <?= esc($statusClass($order['status'] ?? '')); ?>"><?= esc($order['status'] ?? '-'); ?></span>
                                        <div class="mt-2"><a href="/order/<?= esc($order['id_midtrans']); ?>" class="btn btn-sm btn-primary1">Detail</a></div>
                                    </div>
                                </div>
                            <?php } ?>
                        <?php } else { ?>
                            <div class="account-card-soft rounded-4 p-3 text-center">
                                <i class="material-icons text-secondary" style="font-size:40px;">shopping_bag</i>
                                <p class="mb-2 fw-bold">Belum ada transaksi</p>
                                <p class="text-secondary small mb-3">Mulai belanja dan semua status pesanan akan muncul di sini.</p>
                                <a href="/all" class="btn btn-primary1">Cari Produk</a>
                            </div>
                        <?php } ?>
                    </div>

                    <div class="account-card account-help-card">
                        <h5 class="fw-bold mb-2">Retur & bantuan pesanan</h5>
                        <?php if (!empty($latestReturn)) { ?>
                            <p class="mb-1"><b>Retur terakhir:</b> #<?= esc($latestReturn['id_midtrans']); ?></p>
                            <p class="mb-2 text-secondary small">Status: <?= esc($latestReturn['status'] ?? '-'); ?></p>
                            <a href="/retur/order/<?= esc($latestReturn['id_midtrans']); ?>" class="btn btn-sm btn-outline-success">Lihat retur</a>
                        <?php } else { ?>
                            <p class="text-secondary mb-2">Kalau ada kendala barang, pengajuan retur bisa dilakukan dari halaman transaksi.</p>
                            <a href="/transaction" class="btn btn-sm btn-outline-success">Buka transaksi</a>
                        <?php } ?>
                    </div>
                <?php } ?>
            </div>

            <div class="col-lg-5">
                <div class="account-card">
                    <h5 class="fw-bold mb-1"><?= $role == '1' ? 'Edit profil staf' : 'Edit profil'; ?></h5>
                    <p class="text-secondary small mb-3"><?= $role == '1' ? 'Perbarui informasi akun untuk identitas dan kebutuhan operasional.' : 'Profil yang lengkap membuat checkout, voucher, dan bantuan pesanan lebih mudah.'; ?></p>
                    <?php if (session()->get('active') || $role == '1') { ?>
                        <form class="row g-3" action="/account" method="post" enctype="multipart/form-data" data-confirm-submit data-confirm-message="Simpan perubahan profil yang Anda masukkan sekarang?">
                            <?= csrf_field(); ?>
                            <?php if ($role == '0' || !empty($canEditStaffPhoto)) { ?><div class="col-12 text-center">
                                <div class="account-form-photo">
                                    <label for="input-file" class="icon-btn-change-pp" aria-label="Ganti foto profil"><i class="material-icons">edit</i></label>
                                    <input class="d-none" name="foto" id="input-file" type="file" accept="image/*">
                                    <img src="<?= esc($foto); ?>" alt="Preview foto profil" id="prev-file">
                                </div>
                                <div class="text-secondary small mt-2">Gunakan foto JPG/PNG/WebP agar akun mudah dikenali.</div>
                            </div><?php } ?>
                            <?php if ($role == '1') { ?>
                                <div class="col-12">
                                    <label for="currentPassword" class="form-label">Sandi saat ini</label>
                                    <input name="current_sandi" type="password" class="form-control" id="currentPassword" autocomplete="current-password" placeholder="Wajib diisi jika mengganti sandi">
                                    <div class="form-text">Email akun staf dikunci dan tidak dapat diganti sendiri.</div>
                                </div>
                            <?php } ?>
                            <div class="col-12">
                                <label for="inputPassword4" class="form-label">Sandi baru <span class="text-secondary small">(opsional)</span></label>
                                <input name="sandi" type="password" class="form-control" id="inputPassword4" autocomplete="new-password" <?= $role == '1' ? 'minlength="12" maxlength="128"' : ''; ?> placeholder="<?= $role == '1' ? 'Minimal 12 karakter' : 'Isi kalau ingin mengganti sandi'; ?>">
                                <?php if ($role == '1') { ?><div class="form-text">Sandi lama wajib diverifikasi. Lima percobaan gagal dalam 15 menit akan mengunci perubahan sementara.</div><?php } ?>
                            </div>
                            <?php if ($role == '0' || !empty($canEditStaffProfile)) { ?>
                                <div class="col-12">
                                    <label class="form-label" for="profile-name"><?= $role == '1' ? 'Nama staf' : 'Nama lengkap'; ?></label>
                                    <input id="profile-name" name="nama" type="text" class="form-control" placeholder="Nama Lengkap" value="<?= esc($nama); ?>" required autocomplete="name">
                                </div>
                                <div class="col-12">
                                    <label class="form-label" for="profile-phone"><?= $role == '1' ? 'Nomor kontak' : 'Nomor handphone'; ?></label>
                                    <input id="profile-phone" name="nohp" type="tel" class="form-control" placeholder="No HP" value="<?= esc($nohp); ?>" required autocomplete="tel">
                                </div>
                                <?php if ($role == '0') { ?>
                                <div class="col-12">
                                    <label class="form-label" for="profile-birthday">Tanggal lahir</label>
                                    <input id="profile-birthday" name="tgl_lahir" type="date" class="form-control" value="<?= esc($tgl_lahir); ?>" required>
                                    <?php if ($kurang_dari && $batas_tgl_lahir) { ?>
                                        <p class="m-0 text-secondary small">*tanggal lahir dapat diubah kembali setelah tanggal <?= esc($batas_tgl_lahir); ?></p>
                                    <?php } ?>
                                </div>
                                <?php } ?>
                            <?php } elseif ($role == '1') { ?>
                                <div class="col-12"><p class="small text-secondary mb-0">Profil nama dan kontak belum tersedia untuk akun ini. Jalankan SQL <code>docs/sql/admin_staff_profile_fields.sql</code> agar data staf dapat disimpan. Sandi tetap dapat diperbarui.</p></div>
                            <?php } ?>
                            <div class="col-12 d-grid"><button type="submit" class="btn btn-primary1">Simpan Profil</button></div>
                        </form>
                    <?php } else { ?>
                        <p class="mb-2 text-secondary">Biodata dapat diubah ketika akun telah diaktifkan.</p>
                        <a href="/verify" class="btn btn-primary1">Aktivasi akun</a>
                    <?php } ?>
                </div>
                <?php if ($role == '0' && session()->get('active')) { ?>
                    <section class="account-card mt-3" aria-labelledby="marketing-preferences-title">
                        <h5 class="fw-bold mb-1" id="marketing-preferences-title">Preferensi promosi</h5>
                        <p class="text-secondary small mb-3">Email promosi dikirim ke email akun Anda. Nonaktifkan kapan saja jika tidak ingin menerimanya. Notifikasi Android mengikuti izin notifikasi perangkat.</p>
                        <?php if (!db_connect()->tableExists('customer_marketing_preferences')) { ?>
                            <p class="small text-secondary mb-0">Pengaturan promosi sedang disiapkan.</p>
                        <?php } else { ?>
                            <form action="/account/marketing-preferences" method="post">
                                <?= csrf_field(); ?>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" value="1" id="email-promotions" name="email_promotions" <?= !empty($marketingPreferences['email_promotions']) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="email-promotions">Saya menerima email voucher dan promo</label>
                                </div>
                                <button type="submit" class="btn btn-sm btn-primary1">Simpan preferensi</button>
                            </form>
                        <?php } ?>
                    </section>
                <?php } ?>
            </div>
        </div>
        <?php if ($role == '0') { ?>
            </main>
        </div>
        <?php } ?>

    </div>
</div>
<script>
    const inputFileElm = document.getElementById('input-file');
    const prevFileElm = document.getElementById('prev-file');
    if (inputFileElm && prevFileElm) {
        inputFileElm.addEventListener('change', (e) => {
            const file = e.target.files && e.target.files[0];
            if (!file) return;
            prevFileElm.src = URL.createObjectURL(file);
        });
    }
</script>
<?= $this->endSection(); ?>
