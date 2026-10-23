<?= $this->extend('layout/template'); ?>
<?= $this->section('content'); ?>
<main class="konten admin-module-page">
 <div class="container customer-directory-page">
  <?= view('partials/admin_page_heading', ['adminHeadingTitle'=>'Direktori pengguna', 'adminHeadingGroup'=>'Operasional', 'adminHeadingDescription'=>'Lihat informasi kontak akun pelanggan dan metode pendaftaran awal yang tercatat.'], ['saveData'=>false]); ?>
  <?php if (empty($ready)) { ?>
   <div class="alert alert-warning">Jalankan SQL <code>docs/sql/customer_directory_registration_metadata.sql</code> sebelum memakai direktori pengguna.</div>
  <?php } else { ?>
   <div class="alert alert-light border" role="note">Direktori ini hanya untuk kebutuhan layanan akun. Pengiriman promosi dan ekspor data tidak tersedia di halaman ini. Metode pendaftaran untuk akun lama yang tidak tercatat akan ditampilkan sebagai “Tidak diketahui”.</div>
   <section class="card border-0 shadow-sm rounded-4 p-3 p-md-4 mb-4" aria-label="Ringkasan pengguna aktif">
    <h2 class="h6 text-secondary mb-3">Pengguna aktif menurut metode pendaftaran awal</h2>
    <div class="row g-3">
     <?php foreach (['email'=>'Email', 'google'=>'Google', 'facebook'=>'Facebook', 'unknown'=>'Tidak diketahui'] as $key=>$label) { ?>
      <div class="col-6 col-lg-3"><div class="p-3 rounded-3 border h-100"><span class="small text-secondary d-block"><?= esc($label); ?></span><strong class="fs-4"><?= number_format((int)($methodCounts[$key] ?? 0), 0, ',', '.'); ?></strong></div></div>
     <?php } ?>
    </div>
   </section>
   <section class="card border-0 shadow-sm rounded-4 p-3 p-md-4">
    <form method="get" action="/customerdirectoryadmin" class="row g-3 align-items-end mb-4">
     <div class="col-12 col-lg-5"><label for="directory-q" class="form-label">Cari nama, email, atau nomor ponsel</label><input id="directory-q" name="q" type="search" maxlength="100" class="form-control" value="<?= esc($query, 'attr'); ?>" placeholder="Ketik untuk mencari" autocomplete="off"></div>
     <div class="col-6 col-lg-3"><label for="directory-method" class="form-label">Pendaftaran awal</label><select id="directory-method" name="method" class="form-select"><option value="all" <?= $method==='all'?'selected':''; ?>>Semua metode</option><option value="email" <?= $method==='email'?'selected':''; ?>>Email</option><option value="google" <?= $method==='google'?'selected':''; ?>>Google</option><option value="facebook" <?= $method==='facebook'?'selected':''; ?>>Facebook</option><option value="unknown" <?= $method==='unknown'?'selected':''; ?>>Tidak diketahui</option></select></div>
     <div class="col-6 col-lg-2"><label for="directory-status" class="form-label">Status akun</label><select id="directory-status" name="status" class="form-select"><option value="active" <?= $status==='active'?'selected':''; ?>>Aktif</option><option value="pending" <?= $status==='pending'?'selected':''; ?>>Belum aktif</option><option value="all" <?= $status==='all'?'selected':''; ?>>Semua</option></select></div>
     <div class="col-12 col-lg-2 d-flex gap-2"><button class="btn btn-primary1 flex-grow-1" type="submit">Terapkan</button><a class="btn btn-outline-secondary" href="/customerdirectoryadmin" aria-label="Hapus filter">Reset</a></div>
    </form>
    <div class="d-flex justify-content-between align-items-center gap-2 mb-3"><h2 class="h6 mb-0">Daftar pelanggan</h2><span class="small text-secondary"><?= number_format((int)$total, 0, ',', '.'); ?> hasil</span></div>
    <?php if ($customers) { ?>
     <div class="table-responsive"><table class="table align-middle"><thead><tr><th>Nama</th><th>Email</th><th>Nomor ponsel</th><th>Pendaftaran awal</th><th>Status</th><th>Terdaftar</th></tr></thead><tbody>
      <?php foreach ($customers as $customer) {
          $provider = in_array($customer['registration_method'] ?? '', ['email','google','facebook'], true) ? $customer['registration_method'] : 'unknown';
          $providerLabels = ['email'=>'Email','google'=>'Google','facebook'=>'Facebook','unknown'=>'Tidak diketahui'];
          $registered = null;
          if (!empty($customer['registered_at'])) { try { $registered = (new DateTimeImmutable($customer['registered_at'], new DateTimeZone('UTC')))->setTimezone(new DateTimeZone('Asia/Jakarta'))->format('d M Y, H:i') . ' WIB'; } catch (Throwable $error) { $registered = null; } }
      ?>
       <tr><td class="fw-semibold"><?= esc($customer['nama'] ?: 'Nama belum diisi'); ?></td><td><?= esc($customer['email']); ?></td><td><?= esc($customer['nohp'] ?: 'Belum tersedia'); ?></td><td><span class="badge text-bg-light"><?= esc($providerLabels[$provider]); ?></span></td><td><?= (string)$customer['active']==='1' ? '<span class="badge text-bg-success">Aktif</span>' : '<span class="badge text-bg-secondary">Belum aktif</span>'; ?></td><td><?= esc($registered ?? 'Tanggal belum tercatat'); ?></td></tr>
      <?php } ?>
     </tbody></table></div>
    <?php } else { ?><div class="text-center py-5"><p class="fw-semibold mb-1">Belum ada pengguna yang cocok</p><p class="text-secondary mb-0">Coba ubah kata kunci atau filter.</p></div><?php } ?>
    <?php if ($pages > 1) { ?><nav aria-label="Halaman pengguna" class="mt-3"><ul class="pagination justify-content-end mb-0">
      <?php for ($n=max(1,$page-2); $n<=min($pages,$page+2); $n++) { $params=['page'=>$n,'method'=>$method,'status'=>$status]; if ($query!=='') $params['q']=$query; ?>
       <li class="page-item <?= $n===$page?'active':''; ?>"><a class="page-link" href="/customerdirectoryadmin?<?= esc(http_build_query($params), 'attr'); ?>" <?= $n===$page?'aria-current="page"':''; ?>><?= $n; ?></a></li>
      <?php } ?>
     </ul></nav><?php } ?>
   </section>
  <?php } ?>
 </div>
</main>
<?= $this->endSection(); ?>
