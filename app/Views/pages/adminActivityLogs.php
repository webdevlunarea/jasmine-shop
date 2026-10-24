<?= $this->extend('layout/template'); ?>
<?= $this->section('content'); ?>
<main class="konten admin-module-page">
 <div class="container">
  <?= view('partials/admin_page_heading', ['adminHeadingTitle'=>'Log aktivitas staf', 'adminHeadingGroup'=>'Workspace', 'adminHeadingDescription'=>'Lihat tindakan admin yang tercatat. Isi formulir dan alamat IP tidak disimpan. Catatan dihapus otomatis setelah enam bulan.'], ['saveData'=>false]); ?>
  <?php foreach ($errors as $error): ?><div class="alert alert-warning" role="status"><?= esc($error); ?></div><?php endforeach; ?>
  <?php if (!$ready): ?>
   <div class="alert alert-info" role="status">Tabel log aktivitas belum tersedia atau strukturnya belum sesuai. Periksa tabel <code>admin_activity_logs</code>.</div>
  <?php else: ?>
   <section class="card border-0 shadow-sm rounded-4 p-3 p-md-4 mb-4">
    <form method="get" action="/admin/activity-logs" class="row g-3 align-items-end">
     <div class="col-6 col-lg-2"><label class="form-label" for="log-from">Dari tanggal (WIB)</label><input class="form-control" type="date" id="log-from" name="from" value="<?= esc((string)$filters['from'], 'attr'); ?>"></div>
     <div class="col-6 col-lg-2"><label class="form-label" for="log-to">Sampai tanggal (WIB)</label><input class="form-control" type="date" id="log-to" name="to" value="<?= esc((string)$filters['to'], 'attr'); ?>"></div>
     <div class="col-12 col-md-6 col-lg-3"><label class="form-label" for="log-actor">Email staf</label><input class="form-control" type="search" id="log-actor" name="actor" maxlength="120" value="<?= esc($filters['actor'], 'attr'); ?>" placeholder="Cari staf"></div>
     <div class="col-6 col-md-3 col-lg-2"><label class="form-label" for="log-method">Tindakan</label><select class="form-select" id="log-method" name="method"><option value="">Semua</option><?php foreach (['POST', 'PUT', 'PATCH', 'DELETE'] as $method): ?><option value="<?= $method; ?>" <?= $filters['method'] === $method ? 'selected' : ''; ?>><?= $method; ?></option><?php endforeach; ?></select></div>
     <div class="col-6 col-md-3 col-lg-2"><label class="form-label" for="log-route">Bagian halaman</label><input class="form-control" type="search" id="log-route" name="route" maxlength="100" value="<?= esc($filters['route'], 'attr'); ?>" placeholder="Contoh: voucher"></div>
     <div class="col-12 col-lg-1 d-grid"><button class="btn btn-outline-dark" type="submit">Cari</button></div>
    </form>
   </section>
   <section class="card border-0 shadow-sm rounded-4 p-3 p-md-4">
    <div class="d-flex flex-wrap justify-content-between gap-2 align-items-center mb-3"><div><h2 class="h5 mb-1">Riwayat tindakan</h2><p class="small text-muted mb-0"><?= number_format($total, 0, ',', '.'); ?> catatan · Maksimal 50 per halaman</p></div><span class="badge text-bg-light">Halaman <?= $page; ?> / <?= $pages; ?></span></div>
    <?php if (!$logs): ?><p class="text-muted mb-0">Tidak ada catatan yang cocok dengan pencarian.</p><?php else: ?>
     <div class="table-responsive"><table class="table table-sm align-middle"><thead><tr><th>Waktu (WIB)</th><th>Staf</th><th>Peran</th><th>Tindakan</th><th>Halaman</th><th>Hasil</th></tr></thead><tbody>
      <?php foreach ($logs as $log): $logTime = (new DateTimeImmutable($log['created_at'], new DateTimeZone('UTC')))->setTimezone(new DateTimeZone('Asia/Jakarta'))->format('d M Y, H:i:s'); ?>
       <tr><td><?= esc($logTime); ?></td><td><?= esc($log['actor_email']); ?></td><td><?= esc($log['admin_role']); ?></td><td><span class="badge text-bg-light"><?= esc($log['http_method']); ?></span></td><td><code><?= esc($log['route']); ?></code></td><td><span class="badge <?= (int)$log['response_code'] < 400 ? 'text-bg-success' : 'text-bg-warning'; ?>"><?= (int)$log['response_code']; ?></span></td></tr>
      <?php endforeach; ?>
     </tbody></table></div>
     <?php if ($pages > 1): ?><nav class="mt-3" aria-label="Halaman log aktivitas"><ul class="pagination mb-0">
      <?php if ($page > 1): ?><li class="page-item"><a class="page-link" href="/admin/activity-logs?<?= esc($queryString . ($queryString !== '' ? '&' : '') . 'page=' . ($page - 1), 'attr'); ?>">Sebelumnya</a></li><?php endif; ?>
      <?php if ($page < $pages): ?><li class="page-item"><a class="page-link" href="/admin/activity-logs?<?= esc($queryString . ($queryString !== '' ? '&' : '') . 'page=' . ($page + 1), 'attr'); ?>">Berikutnya</a></li><?php endif; ?>
     </ul></nav><?php endif; ?>
    <?php endif; ?>
   </section>
  <?php endif; ?>
 </div>
</main>
<?= $this->endSection(); ?>
