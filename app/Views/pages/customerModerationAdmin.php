<?= $this->extend('layout/template'); ?>
<?= $this->section('content'); ?>
<main class="konten admin-module-page">
 <div class="container customer-moderation">
  <?= view('partials/admin_page_heading', ['adminHeadingTitle'=>'Akun & email spam', 'adminHeadingGroup'=>'Pengaturan', 'adminHeadingDescription'=>'Kelola domain email sementara dan bersihkan pendaftaran kedaluwarsa. Akun yang terkait pesanan, retur, preorder, rating, poin, atau penukaran voucher akan dilewati.'], ['saveData'=>false]); ?>
  <?php if (!empty($msg)) { ?><div class="alert alert-info" role="status"><?= esc($msg); ?></div><?php } ?>
  <section class="card border-0 shadow-sm rounded-4 p-3 p-md-4 mb-4">
   <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-3"><div><h3 class="h5 fw-bold mb-1">Permintaan penghapusan akun</h3><p class="text-secondary mb-0">Mulai peninjauan, hapus data akun jika tidak ada catatan yang wajib disimpan, atau tutup setelah penanganan retensi selesai.</p></div><span class="badge text-bg-light"><?= count($deletionRequests); ?> permintaan</span></div>
   <?php if (empty($deletionRequestTableReady)) { ?><div class="alert alert-warning mb-0">Jalankan SQL <code>docs/sql/customer_account_deletion_requests.sql</code> di database produksi untuk mengaktifkan antrean.</div>
   <?php } elseif (!empty($deletionRequests)) { ?><div class="table-responsive"><table class="table align-middle"><thead><tr><th>Email akun</th><th>Waktu permintaan</th><th>Status</th><th>Tindakan</th></tr></thead><tbody><?php foreach ($deletionRequests as $request) { ?><tr><td class="fw-semibold"><?= esc($request['email_user']); ?></td><td><?= esc($request['requested_at']); ?></td><td><span class="badge text-bg-warning"><?= esc($request['status']); ?></span></td><td><div class="d-flex flex-wrap gap-2">
    <?php if ($request['status'] === 'pending') { ?><form method="post" action="/customeradmin/account-deletion/review" data-confirm-submit data-confirm-message="Mulai peninjauan permintaan penghapusan akun ini?"><?= csrf_field(); ?><input type="hidden" name="id" value="<?= (int)$request['id']; ?>"><button class="btn btn-sm btn-outline-primary" type="submit">Mulai tinjau</button></form><?php } ?>
    <?php if ($request['status'] === 'processing') { ?><form method="post" action="/customeradmin/account-deletion/delete" data-confirm-submit data-confirm-message="Akun hanya dihapus bila tidak ada pesanan, retur, poin, penukaran voucher, atau catatan lain yang wajib disimpan. Lanjutkan pemeriksaan dan penghapusan?"><?= csrf_field(); ?><input type="hidden" name="id" value="<?= (int)$request['id']; ?>"><button class="btn btn-sm btn-outline-danger" type="submit">Hapus data akun</button></form>
    <form method="post" action="/customeradmin/account-deletion/complete" data-confirm-submit data-confirm-message="Tutup permintaan setelah peninjauan dan kewajiban retensi selesai?"><?= csrf_field(); ?><input type="hidden" name="id" value="<?= (int)$request['id']; ?>"><button class="btn btn-sm btn-outline-success" type="submit">Tutup permintaan</button></form><?php } ?>
   </div></td></tr><?php } ?></tbody></table></div>
   <?php } else { ?><p class="text-secondary mb-0">Tidak ada permintaan yang menunggu.</p><?php } ?>
  </section>
  <section class="card border-0 shadow-sm rounded-4 p-3 p-md-4 mb-4">
   <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-3"><div><h3 class="h5 fw-bold mb-1">Pendaftaran belum diverifikasi</h3><p class="text-secondary mb-0">Hanya akun pelanggan dengan masa OTP yang sudah habis ditampilkan. Maksimal 300 akun per tampilan.</p></div><span class="badge text-bg-light"><?= count($users); ?> akun</span></div>
   <?php if ($users) { ?>
   <form method="post" action="/customeradmin/delete-expired-unverified" data-confirm-submit data-confirm-message="Hapus akun terpilih yang belum diverifikasi? Akun dengan riwayat transaksi akan dilewati.">
    <?= csrf_field(); ?>
    <div class="table-responsive"><table class="table align-middle"><thead><tr><th><input class="form-check-input" type="checkbox" aria-label="Pilih semua" data-select-all></th><th>Email</th><th>Masa verifikasi berakhir</th><th>Status data</th></tr></thead><tbody>
    <?php foreach ($users as $user) { ?><tr><td><input class="form-check-input" type="checkbox" name="emails[]" value="<?= esc($user['email'], 'attr'); ?>" <?= !empty($user['has_history']) ? 'disabled' : ''; ?>></td><td class="fw-semibold"><?= esc($user['email']); ?></td><td><?= esc(date('d M Y, H:i', (int) $user['waktu_otp'])); ?></td><td><?= !empty($user['has_history']) ? '<span class="badge text-bg-secondary">Riwayat tersimpan — dilindungi</span>' : '<span class="badge text-bg-warning">Tanpa riwayat terlindungi</span>'; ?></td></tr><?php } ?>
    </tbody></table></div>
    <button class="btn btn-outline-danger" type="submit"><i class="material-icons align-middle me-1" aria-hidden="true">delete_outline</i>Hapus akun terpilih</button>
   </form>
   <?php } else { ?><div class="text-center py-4 text-secondary">Tidak ada akun kedaluwarsa yang perlu dibersihkan.</div><?php } ?>
  </section>

  <section class="card border-0 shadow-sm rounded-4 p-3 p-md-4">
   <div class="mb-3"><h3 class="h5 fw-bold mb-1">Blokir domain email sementara</h3><p class="text-secondary mb-0">Pendaftaran baru dari domain yang tercatat akan ditolak. Domain induk turut mencakup subdomainnya.</p></div>
   <?php if (!$domainTableReady) { ?><div class="alert alert-warning mb-0">Daftar domain admin belum aktif. Jalankan migration <code>CreateCustomerBlockedEmailDomains</code> saat deployment untuk mengaktifkan pengelolaan ini.</div><?php } else { ?>
   <form method="post" action="/customeradmin/domain/add" class="row g-2 mb-3"><?= csrf_field(); ?><div class="col-12 col-md-8"><label class="form-label" for="blocked-domain">Domain email</label><input class="form-control" id="blocked-domain" name="domain" placeholder="contoh: mail-sementara.example" required maxlength="190" autocomplete="off"></div><div class="col-12 col-md-4 d-flex align-items-end"><button class="btn btn-primary1 w-100" type="submit">Tambahkan domain</button></div></form>
   <?php if ($domains) { ?><div class="list-group list-group-flush"><?php foreach ($domains as $domain) { ?><div class="list-group-item d-flex align-items-center justify-content-between gap-3 px-0"><span class="fw-semibold"><?= esc($domain['domain']); ?><?= empty($domain['is_active']) ? ' · Nonaktif' : ''; ?></span><form method="post" action="/customeradmin/domain/remove" data-confirm-submit data-confirm-message="Hapus domain ini dari daftar blokir?"><?= csrf_field(); ?><input type="hidden" name="id" value="<?= (int) $domain['id']; ?>"><button class="btn btn-sm btn-outline-secondary" type="submit">Hapus</button></form></div><?php } ?></div><?php } else { ?><p class="text-secondary mb-0">Belum ada domain tambahan dari admin.</p><?php } ?>
   <?php } ?>
  </section>
 </div>
</main>
<script>
document.querySelector('[data-select-all]')?.addEventListener('change', function () {
 document.querySelectorAll('input[name="emails[]"]:not(:disabled)').forEach((box) => { box.checked = this.checked; });
});
</script>
<?= $this->endSection(); ?>
