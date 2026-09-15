<?= $this->extend('layout/template'); ?>
<?= $this->section('content'); ?>
<div class="konten">
    <div class="container py-4">
        <div class="mb-3">
            <a href="/transaction" style="color: var(--hijau)" class="fw-bold">&larr; Kembali ke transaksi</a>
        </div>

        <div class="p-4 rounded shadow-sm bg-white">
            <h3 class="mb-1">Ajukan Retur</h3>
            <p class="text-secondary mb-3">Pesanan <b><?= esc($pemesanan['id_midtrans']); ?></b></p>

            <?php if (!empty($msg)) { ?>
                <div class="alert alert-info"><?= esc($msg); ?></div>
            <?php } ?>

            <?php if ($existingReturn) { ?>
                <div class="alert alert-warning">
                    <b>Status pengajuan terakhir:</b> <?= esc($existingReturn['status']); ?><br>
                    <small>Diajukan pada <?= esc($existingReturn['created_at']); ?></small>
                </div>
            <?php } ?>

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
        </div>
    </div>
</div>
<?= $this->endSection(); ?>
