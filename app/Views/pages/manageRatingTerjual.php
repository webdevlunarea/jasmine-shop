<?= $this->extend('layout/template'); ?>
<?= $this->section('content'); ?>
<div class="konten">
    <div class="container">
        <h5 class="jdl-section mb-3">Kelola Rating &amp; Terjual</h5>

        <?php if (!empty($msg)) { ?>
            <div class="alert alert-info py-2"><?= esc($msg); ?></div>
        <?php } ?>

        <ul class="nav nav-tabs mb-3" id="adminTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="terjual-tab" data-bs-toggle="tab" data-bs-target="#tab-terjual" type="button" role="tab">Kelola Terjual</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="rating-tab" data-bs-toggle="tab" data-bs-target="#tab-rating" type="button" role="tab">Kelola Rating &amp; Ulasan</button>
            </li>
        </ul>

        <div class="tab-content">
            <!-- ====================== TAB TERJUAL ====================== -->
            <div class="tab-pane fade show active" id="tab-terjual" role="tabpanel">
                <form method="get" action="/manageratingterjual" class="mb-3 d-flex gap-2">
                    <input type="text" name="cari" class="form-control" placeholder="Cari nama produk..." value="<?= esc($cari); ?>">
                    <button type="submit" class="btn btn-outline-dark">Cari</button>
                    <?php if ($cari !== '') { ?>
                        <a href="/manageratingterjual" class="btn btn-light">Reset</a>
                    <?php } ?>
                </form>

                <div class="table-responsive">
                    <table class="table table-bordered align-middle" style="font-size: 0.9rem;">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 60px;">Gambar</th>
                                <th>Nama Produk</th>
                                <th style="width: 130px;">Terjual (asli)</th>
                                <th style="width: 130px;">Terjual Custom</th>
                                <th style="width: 110px;">Rating</th>
                                <th style="width: 130px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($produk)) { ?>
                                <tr><td colspan="6" class="text-center text-muted py-4">Tidak ada produk.</td></tr>
                            <?php } ?>
                            <?php foreach ($produk as $p) { ?>
                                <?php $fid = 'form-terjual-' . $p['id']; ?>
                                <tr>
                                    <td>
                                        <form id="<?= $fid; ?>" action="/updateterjualadmin/<?= $p['id']; ?>" method="post" style="display:none;"></form>
                                        <?php if (!empty($p['gambar'])) { ?>
                                            <img src="data:image/webp;base64,<?= base64_encode($p['gambar']); ?>" style="width: 48px; height: 48px; object-fit: cover; border-radius: 6px;">
                                        <?php } ?>
                                    </td>
                                    <td>
                                        <div class="fw-bold"><?= esc($p['nama']); ?></div>
                                        <div class="text-muted" style="font-size: 0.75rem;">ID: <?= $p['id']; ?></div>
                                    </td>
                                    <td>
                                        <input type="number" form="<?= $fid; ?>" name="terjual" class="form-control form-control-sm" value="<?= (int)$p['terjual']; ?>" min="0">
                                    </td>
                                    <td>
                                        <input type="number" form="<?= $fid; ?>" name="terjual_custom" class="form-control form-control-sm" value="<?= (int)$p['terjual_custom']; ?>" min="0">
                                    </td>
                                    <td>
                                        <?php if ($p['rating_count'] > 0) { ?>
                                            <span style="color:#f5b301;">★</span>
                                            <strong><?= number_format($p['rating_avg'], 1, ",", "."); ?></strong>
                                            <span class="text-muted" style="font-size:0.75rem;">(<?= $p['rating_count']; ?>)</span>
                                        <?php } else { ?>
                                            <span class="text-muted">—</span>
                                        <?php } ?>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <button type="submit" form="<?= $fid; ?>" class="btn btn-sm btn-primary1" title="Simpan"><i class="material-icons" style="font-size: 16px;">save</i></button>
                                            <a href="/product/<?= $p['path']; ?>" target="_blank" class="btn btn-sm btn-light" title="Lihat"><i class="material-icons" style="font-size: 16px;">visibility</i></a>
                                        </div>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
                <p class="text-muted" style="font-size: 0.8rem;"><strong>Catatan:</strong> "Terjual Custom" akan diprioritaskan untuk ditampilkan di halaman produk. Jika 0, maka "Terjual (asli)" yang dipakai.</p>
            </div>

            <!-- ====================== TAB RATING ====================== -->
            <div class="tab-pane fade" id="tab-rating" role="tabpanel">
                <form method="get" action="/manageratingterjual" class="mb-3 d-flex gap-2 align-items-center">
                    <input type="hidden" name="tab" value="rating">
                    <label class="m-0">Filter produk:</label>
                    <select name="filter_barang" class="form-select" style="max-width: 320px;" onchange="this.form.submit()">
                        <option value="">— Semua produk —</option>
                        <?php foreach ($produkOpsi as $po) { ?>
                            <option value="<?= esc($po['id']); ?>" <?= (string)$filterBarang === (string)$po['id'] ? 'selected' : ''; ?>><?= esc($po['nama']); ?></option>
                        <?php } ?>
                    </select>
                    <?php if ($filterBarang) { ?>
                        <a href="/manageratingterjual#tab-rating" class="btn btn-light">Reset</a>
                    <?php } ?>
                </form>

                <?php if (empty($ratingList)) { ?>
                    <p class="text-center text-muted py-4">Tidak ada ulasan.</p>
                <?php } ?>
                <?php foreach ($ratingList as $r) { ?>
                    <div class="card mb-3" style="font-size: 0.9rem;">
                        <div class="card-body py-3">
                            <div class="d-flex justify-content-between align-items-start mb-2 flex-wrap gap-2">
                                <div>
                                    <span class="badge bg-light text-dark border"><?= esc($r['nama_barang']); ?></span>
                                    <?php if (!empty($r['path_barang'])) { ?>
                                        <a href="/product/<?= $r['path_barang']; ?>" target="_blank" style="font-size: 0.8rem;">lihat produk</a>
                                    <?php } ?>
                                </div>
                                <div class="text-muted" style="font-size: 0.75rem;">
                                    <?= date('d M Y H:i', strtotime($r['created_at'])); ?>
                                    <?php if ($r['updated_at'] && $r['updated_at'] != $r['created_at']) { ?>
                                        <span>(diedit <?= date('d M Y', strtotime($r['updated_at'])); ?>)</span>
                                    <?php } ?>
                                </div>
                            </div>
                            <form action="/editratingadmin/<?= $r['id']; ?>" method="post">
                                <div class="row g-2">
                                    <div class="col-md-4">
                                        <label class="form-label" style="font-size: 0.8rem;">Nama Pembeli</label>
                                        <input type="text" name="nama_pembeli" class="form-control form-control-sm" value="<?= esc($r['nama_pembeli']); ?>">
                                        <small class="text-muted">Tampil: <strong><?= esc($r['nama_sensor']); ?></strong></small><br>
                                        <small class="text-muted" style="font-size:0.7rem;"><?= esc($r['email_cus']); ?></small>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label" style="font-size: 0.8rem;">Rating</label>
                                        <select name="rating" class="form-select form-select-sm">
                                            <?php for ($s = 1; $s <= 5; $s++) { ?>
                                                <option value="<?= $s; ?>" <?= (int)$r['rating'] == $s ? 'selected' : ''; ?>><?= $s; ?> ★</option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" style="font-size: 0.8rem;">Komentar</label>
                                        <textarea name="komentar" class="form-control form-control-sm" rows="2" maxlength="1000"><?= esc($r['komentar']); ?></textarea>
                                    </div>
                                </div>
                                <div class="d-flex gap-2 mt-2">
                                    <button type="submit" class="btn btn-sm btn-primary1"><i class="material-icons" style="font-size: 14px;">save</i> Simpan</button>
                                </div>
                            </form>
                            <form action="/delrating/<?= $r['id']; ?>" method="post" onsubmit="return confirm('Hapus ulasan dari <?= esc($r['nama_pembeli']); ?>?');" class="mt-2">
                                <input type="hidden" name="from_admin" value="1">
                                <button type="submit" class="btn btn-sm btn-outline-danger"><i class="material-icons" style="font-size: 14px;">delete</i> Hapus ulasan</button>
                            </form>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
</div>

<script>
    // aktifkan tab sesuai hash url
    (function() {
        var hash = window.location.hash;
        if (hash === '#tab-rating') {
            var ratTab = document.getElementById('rating-tab');
            if (ratTab) new bootstrap.Tab(ratTab).show();
        } else if (hash === '#tab-terjual') {
            var terjTab = document.getElementById('terjual-tab');
            if (terjTab) new bootstrap.Tab(terjTab).show();
        }
        // sinkron hash saat ganti tab
        document.querySelectorAll('#adminTab button').forEach(function(btn) {
            btn.addEventListener('shown.bs.tab', function(e) {
                history.replaceState(null, '', e.target.getAttribute('data-bs-target'));
            });
        });
    })();
</script>
<?= $this->endSection(); ?>
