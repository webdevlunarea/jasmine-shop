<?= $this->extend('layout/template'); ?>
<?= $this->section('content'); ?>
<main class="konten admin-module-page">
 <div class="container customer-moderation">
  <?= view('partials/admin_page_heading', ['adminHeadingTitle'=>'Suspend akun pelanggan', 'adminHeadingGroup'=>'Operasional', 'adminHeadingDescription'=>'Tangguhkan akses akun yang melanggar aturan. Setiap tindakan mencatat admin, waktu, dan alasan.'], ['saveData'=>false]); ?>
  <?php if (!empty($msg)) { ?><div class="alert alert-info" role="status"><?= esc($msg); ?></div><?php } ?>
  <?php if (empty($ready)) { ?><div class="alert alert-warning">Siapkan tabel dan kolom suspend di database produksi menggunakan <code>docs/sql/customer_account_suspensions.sql</code> (instalasi baru) atau <code>docs/sql/customer_account_suspensions_upgrade.sql</code> (tabel versi lama).</div><?php } else { ?>
  <section class="card border-0 shadow-sm rounded-4 p-3 p-md-4 mb-4">
   <h2 class="h5 fw-bold">Tangguhkan akun</h2>
   <p class="text-secondary">Akun tetap tersimpan. Pengguna melihat pesan yang Anda tulis. Penangguhan berakhir otomatis sesuai durasi; sesi lama tetap dicabut.</p>
   <form method="post" action="/customersuspensionadmin/suspend" class="row g-3" onsubmit="return confirm('Tangguhkan akun ini dan cabut seluruh sesinya?');">
    <?= csrf_field(); ?>
    <div class="col-12 col-lg-5"><label for="suspend-email" class="form-label">Email akun pelanggan</label><input id="suspend-email" name="email" type="email" class="form-control" maxlength="254" autocomplete="off" required></div>
    <div class="col-12 col-lg-7"><label for="suspend-reason" class="form-label">Catatan internal / alasan</label><input id="suspend-reason" name="reason" class="form-control" minlength="10" maxlength="500" required placeholder="Contoh: dugaan penyalahgunaan poin; tiket #…"></div>
    <div class="col-12"><label for="suspend-message" class="form-label">Pesan untuk pengguna</label><textarea id="suspend-message" name="user_message" class="form-control" rows="2" minlength="5" maxlength="250" required placeholder="Contoh: Akses akun dibatasi sementara selama peninjauan. Hubungi layanan pelanggan jika membutuhkan bantuan."></textarea><div class="form-text">Pesan ini ditampilkan saat pengguna membuka aplikasi atau mencoba masuk.</div></div>
    <div class="col-12 col-md-5"><label for="suspend-duration" class="form-label">Lama penangguhan</label><select id="suspend-duration" name="duration" class="form-select" required><option value="24h">24 jam</option><option value="7d" selected>7 hari</option><option value="30d">30 hari</option><option value="permanent">Tanpa batas waktu</option></select></div>
    <div class="col-12"><button class="btn btn-danger" type="submit">Tangguhkan akun</button></div>
   </form>
  </section>
  <section class="card border-0 shadow-sm rounded-4 p-3 p-md-4 mb-4">
   <div class="d-flex justify-content-between gap-3 mb-3"><div><h2 class="h5 fw-bold mb-1">Akun yang sedang ditangguhkan</h2><p class="text-secondary mb-0">Maksimal 200 catatan terbaru.</p></div><span class="badge text-bg-light"><?= count($suspended); ?> akun</span></div>
   <?php if ($suspended) { ?><div class="table-responsive"><table class="table align-middle"><thead><tr><th>Email</th><th>Pesan pengguna</th><th>Alasan internal</th><th>Berakhir (UTC)</th><th>Admin</th><th></th></tr></thead><tbody><?php foreach ($suspended as $row) { ?><tr><td class="fw-semibold"><?= esc($row['email_user']); ?></td><td><?= esc($row['user_message']); ?></td><td><?= esc($row['reason']); ?></td><td><?= $row['expires_at'] ? esc($row['expires_at']) : 'Tanpa batas'; ?></td><td><?= esc($row['suspended_by']); ?></td><td><form method="post" action="/customersuspensionadmin/reinstate" onsubmit="return confirm('Cabut suspend dan izinkan akun ini masuk kembali?');"><?= csrf_field(); ?><input type="hidden" name="email" value="<?= esc($row['email_user'], 'attr'); ?>"><button type="submit" class="btn btn-sm btn-outline-success">Cabut suspend</button></form></td></tr><?php } ?></tbody></table></div><?php } else { ?><p class="text-secondary mb-0">Tidak ada akun yang sedang ditangguhkan.</p><?php } ?>
  </section>
  <section class="card border-0 shadow-sm rounded-4 p-3 p-md-4">
   <h2 class="h5 fw-bold">Riwayat tindakan</h2>
   <?php if ($events) { ?><div class="table-responsive"><table class="table table-sm align-middle"><thead><tr><th>Waktu (UTC)</th><th>Akun</th><th>Tindakan</th><th>Alasan</th><th>Pesan pengguna</th><th>Berakhir</th><th>Admin</th></tr></thead><tbody><?php foreach ($events as $event) { ?><tr><td><?= esc($event['created_at']); ?></td><td><?= esc($event['email_user']); ?></td><td><?= esc($event['action']); ?></td><td><?= esc($event['reason'] ?? '—'); ?></td><td><?= esc($event['user_message'] ?? '—'); ?></td><td><?= !empty($event['expires_at']) ? esc($event['expires_at']) : '—'; ?></td><td><?= esc($event['admin_email']); ?></td></tr><?php } ?></tbody></table></div><?php } else { ?><p class="text-secondary mb-0">Belum ada tindakan.</p><?php } ?>
  </section>
  <?php } ?>
 </div>
</main>
<?= $this->endSection(); ?>
