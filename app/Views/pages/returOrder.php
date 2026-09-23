<?= $this->extend('layout/template'); ?>
<?= $this->section('content'); ?>
<?php
$returBadgeClass = function ($status) {
    $status = strtolower((string)$status);
    if (strpos($status, 'setuju') !== false || strpos($status, 'approved') !== false || strpos($status, 'selesai') !== false || strpos($status, 'completed') !== false) return 'text-bg-success';
    if (strpos($status, 'tolak') !== false || strpos($status, 'reject') !== false || strpos($status, 'gagal') !== false) return 'text-bg-danger';
    if (strpos($status, 'sinkron') !== false) return 'text-bg-secondary';
    if (strpos($status, 'review') !== false || strpos($status, 'proses') !== false || strpos($status, 'pending') !== false) return 'text-bg-warning';
    return 'text-bg-info';
};
$solutionLabel = function ($solution) {
    $labels = [
        'review_admin' => 'Ikuti review admin',
        'refund' => 'Refund',
        'tukar_barang' => 'Tukar barang',
        'perbaikan' => 'Perbaikan',
        'barang_kurang_rusak' => 'Barang kurang/rusak',
    ];
    return $labels[$solution] ?? ucfirst(str_replace('_', ' ', (string)$solution));
};
$decodeArray = function ($json) {
    $decoded = json_decode((string)$json, true);
    return is_array($decoded) ? $decoded : [];
};
$canSubmitReturn = !$existingReturn || (($existingReturn['status'] ?? '') === 'Menunggu Sinkron Sistem');
?>
<div class="konten">
    <div class="container py-4">
        <div class="mb-3">
            <a href="/transaction" style="color: var(--hijau)" class="fw-bold">&larr; Kembali ke transaksi</a>
        </div>

        <div class="p-4 rounded shadow-sm bg-white">
            <h3 class="mb-1"><?= $canSubmitReturn ? 'Ajukan Retur' : 'Detail Retur'; ?></h3>
            <p class="text-secondary mb-3">Pesanan <b><?= esc($pemesanan['id_midtrans']); ?></b></p>

            <?php if (!empty($msg)) { ?>
                <div class="alert alert-info"><?= esc($msg); ?></div>
            <?php } ?>

            <?php if ($existingReturn) { ?>
                <?php
                $returItems = $decodeArray($existingReturn['items'] ?? '[]');
                $returBukti = $decodeArray($existingReturn['bukti'] ?? '[]');
                $returLuna = $decodeArray($existingReturn['luna_response'] ?? '{}');
                ?>
                <div class="alert alert-warning mb-3">
                    <div class="d-flex justify-content-between align-items-start gap-2 flex-wrap">
                        <div>
                            <b>Status pengajuan terakhir:</b>
                            <span class="badge rounded-pill <?= $returBadgeClass($existingReturn['status'] ?? ''); ?>"><?= esc($existingReturn['status']); ?></span>
                            <?php if (!empty($returLuna['return_number'])) { ?>
                                <span class="badge rounded-pill text-bg-light border">No Retur: <?= esc($returLuna['return_number']); ?></span>
                            <?php } ?>
                        </div>
                        <small class="text-secondary text-end">
                            Diajukan: <?= !empty($existingReturn['created_at']) ? date('d/m/Y H:i', strtotime($existingReturn['created_at'])) : '-'; ?><br>
                            Update: <?= !empty($existingReturn['updated_at']) ? date('d/m/Y H:i', strtotime($existingReturn['updated_at'])) : '-'; ?>
                        </small>
                    </div>
                </div>
                <?php if (($existingReturn['status'] ?? '') === 'Menunggu Sinkron Sistem') { ?>
                    <div class="alert alert-danger">
                        Pengajuan sebelumnya belum berhasil masuk ke sistem admin. Silakan cek data di bawah lalu kirim ulang.
                    </div>
                <?php } ?>

                <?php if (!$canSubmitReturn) { ?>
                <div class="border rounded p-3 mb-3">
                    <p class="mb-1"><b>Solusi diminta:</b> <?= esc($solutionLabel($existingReturn['solusi'] ?? '')); ?></p>
                    <p class="mb-2"><b>Alasan:</b> <?= esc($existingReturn['alasan'] ?? '-'); ?></p>
                    <?php if (!empty($returLuna['note'])) { ?>
                        <p class="mb-2"><b>Catatan admin:</b> <?= esc($returLuna['note']); ?></p>
                    <?php } ?>

                    <p class="fw-bold mb-1">Produk yang diajukan</p>
                    <div class="table-responsive mb-3">
                        <table class="table table-sm align-middle">
                            <thead>
                                <tr>
                                    <th>Produk</th>
                                    <th style="width: 120px;">Qty Retur</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($returItems as $returItem) { ?>
                                    <tr>
                                        <td><?= esc($returItem['name'] ?? '-'); ?></td>
                                        <td><?= (int)($returItem['qty_requested'] ?? $returItem['quantity'] ?? 0); ?></td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>

                    <?php if ($returBukti) { ?>
                        <p class="fw-bold mb-1">Bukti foto</p>
                        <div class="d-flex gap-2 flex-wrap">
                            <?php foreach (array_slice($returBukti, 0, 5) as $foto) { ?>
                                <a href="<?= esc($foto); ?>" target="_blank" class="btn btn-sm btn-outline-secondary">Lihat bukti</a>
                            <?php } ?>
                        </div>
                    <?php } ?>
                </div>

                <div class="alert alert-light border">
                    Tim admin akan memperbarui status retur melalui sistem. Setiap update juga akan dikirim melalui email/WhatsApp bila data kontak tersedia.
                </div>
                <?php } ?>
            <?php } ?>

            <?php if ($canSubmitReturn) { ?>
            <form action="/retur/order/<?= esc($pemesanan['id_midtrans']); ?>" method="post" enctype="multipart/form-data">
                <?= csrf_field(); ?>

                <p class="fw-bold mb-2">Pilih produk yang ingin diretur</p>
                <div class="table-responsive mb-3">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th style="width: 110px;">Dibeli</th>
                                <th style="width: 150px;">Qty Retur</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $index => $item) { ?>
                                <tr>
                                    <td>
                                        <b><?= esc($item['name'] ?? '-'); ?></b><br>
                                        <small class="text-secondary">Rp <?= number_format((int)($item['value'] ?? 0), 0, ',', '.'); ?></small>
                                    </td>
                                    <td><?= (int)($item['quantity'] ?? 0); ?></td>
                                    <td>
                                        <input type="number" min="0" max="<?= (int)($item['quantity'] ?? 0); ?>" value="0" name="qty[<?= $index; ?>]" class="form-control">
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Solusi yang diinginkan</label>
                    <select name="solusi" class="form-select">
                        <option value="review_admin">Ikuti review admin</option>
                        <option value="refund">Refund</option>
                        <option value="tukar_barang">Tukar barang</option>
                        <option value="perbaikan">Perbaikan</option>
                        <option value="barang_kurang_rusak">Barang kurang/rusak</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Alasan retur</label>
                    <textarea name="alasan" class="form-control" rows="5" minlength="5" required placeholder="Contoh: barang rusak di bagian pintu, warna tidak sesuai, atau ada bagian yang kurang."></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Foto bukti</label>
                    <input type="file" name="bukti[]" class="form-control" accept="image/png,image/jpeg,image/webp" multiple required>
                    <small class="text-secondary">Upload 1-5 foto, maksimal 3MB per foto.</small>
                </div>

                <div class="alert alert-light border">
                    Pengajuan ini akan masuk ke sistem admin Lunarea untuk direview. Stok/refund tidak berjalan otomatis sebelum admin menyetujui.
                </div>

                <button type="submit" class="btn btn-primary1 w-100">Kirim Pengajuan Retur</button>
            </form>
            <?php } ?>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>
