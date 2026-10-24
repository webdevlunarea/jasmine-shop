<?= $this->extend('layout/template'); ?>
<?= $this->section('content'); ?>
<main class="konten admin-module-page">
 <div class="container">
  <?= view('partials/admin_page_heading', ['adminHeadingTitle'=>'Retur pelanggan', 'adminHeadingGroup'=>'Operasional', 'adminHeadingDescription'=>'Pantau pengajuan yang perlu ditinjau atau yang belum berhasil tersinkron. Perubahan status tetap dilakukan melalui alur sistem retur yang berlaku.'], ['saveData'=>false]); ?>
  <?php if ($error !== ''): ?><div class="alert alert-warning" role="status"><?= esc($error); ?></div><?php endif; ?>
  <?php if (!$ready): ?>
   <div class="alert alert-info" role="status">Tabel retur belum tersedia atau strukturnya belum sesuai.</div>
  <?php else: ?>
   <section class="card border-0 shadow-sm rounded-4 p-3 p-md-4 mb-4">
    <form method="get" action="/admin/returns" class="row g-3 align-items-end">
     <div class="col-12 col-md-6 col-lg-4"><label class="form-label" for="return-status">Status pengajuan</label><select class="form-select" id="return-status" name="status"><option value="">Semua yang perlu perhatian</option><option value="Menunggu Review Admin" <?= $status === 'Menunggu Review Admin' ? 'selected' : ''; ?>>Menunggu tinjauan</option><option value="Menunggu Sinkron Sistem" <?= $status === 'Menunggu Sinkron Sistem' ? 'selected' : ''; ?>>Belum tersinkron</option></select></div>
     <div class="col-12 col-md-auto d-grid"><button class="btn btn-outline-dark" type="submit">Tampilkan</button></div>
     <div class="col-12 col-md-auto ms-md-auto text-secondary small"><?= number_format($total, 0, ',', '.'); ?> pengajuan · Maks. 50 per halaman</div>
    </form>
   </section>
   <section class="card border-0 shadow-sm rounded-4 p-3 p-md-4">
    <?php if (!$returns): ?><p class="text-secondary mb-0">Tidak ada pengajuan retur yang perlu perhatian.</p><?php else: ?>
     <div class="table-responsive"><table class="table table-sm align-middle"><thead><tr><th>Diajukan</th><th>Pesanan</th><th>Pelanggan</th><th>Produk</th><th>Permintaan</th><th>Status</th></tr></thead><tbody>
      <?php foreach ($returns as $return): $allItems = json_decode((string)$return['items'], true); $allItems = is_array($allItems) ? $allItems : []; $items = array_slice($allItems, 0, 3); ?>
       <tr><td><?= esc((string)($return['created_at'] ?? '—')); ?></td><td><strong><?= esc((string)$return['id_midtrans']); ?></strong></td><td><?= esc((string)$return['email_cus']); ?></td><td><?php if (!$items): ?>Rincian tidak tersedia<?php else: ?><ul class="mb-0 ps-3"><?php foreach ($items as $item): ?><li><?= esc((string)($item['name'] ?? 'Produk')); ?> × <?= (int)($item['qty_requested'] ?? $item['quantity'] ?? 0); ?></li><?php endforeach; ?></ul><?php if (count($allItems) > 3): ?><small class="text-secondary">dan produk lainnya</small><?php endif; ?><?php endif; ?></td><td><strong><?= esc(ucfirst(str_replace('_', ' ', (string)$return['solusi']))); ?></strong><div class="small text-secondary"><?= esc((string)$return['alasan']); ?></div></td><td><span class="badge <?= $return['status'] === 'Menunggu Sinkron Sistem' ? 'text-bg-warning' : 'text-bg-info'; ?>"><?= esc((string)$return['status']); ?></span></td></tr>
      <?php endforeach; ?>
     </tbody></table></div>
     <?php if ($pages > 1): ?><nav class="mt-3" aria-label="Halaman pengajuan retur"><ul class="pagination mb-0">
      <?php if ($page > 1): ?><li class="page-item"><a class="page-link" href="/admin/returns?<?= esc(http_build_query(array_filter(['status'=>$status, 'page'=>$page-1], static fn($v) => $v !== '' && $v !== null)), 'attr'); ?>">Sebelumnya</a></li><?php endif; ?>
      <?php if ($page < $pages): ?><li class="page-item"><a class="page-link" href="/admin/returns?<?= esc(http_build_query(array_filter(['status'=>$status, 'page'=>$page+1], static fn($v) => $v !== '' && $v !== null)), 'attr'); ?>">Berikutnya</a></li><?php endif; ?>
     </ul></nav><?php endif; ?>
    <?php endif; ?>
   </section>
  <?php endif; ?>
 </div>
</main>
<?= $this->endSection(); ?>
