<?= $this->extend('layout/template'); ?>
<?= $this->section('content'); ?>
<?php
$money = static fn($value): string => 'Rp ' . number_format((float)$value, 0, ',', '.');
$paymentLabels = ['bank_transfer'=>'Transfer bank / virtual account', 'rekening'=>'Transfer manual', 'credit_card'=>'Kartu kredit', 'echannel'=>'Mandiri Bill', 'gopay'=>'GoPay / QRIS', 'qris'=>'QRIS'];
$statusOptions = ['all'=>'Semua status', 'Menunggu-Pembayaran'=>'Menunggu pembayaran', 'Menunggu-Pembayaran-Rekening'=>'Transfer manual', 'Proses'=>'Proses', 'Dikirim'=>'Dikirim', 'Selesai'=>'Selesai', 'Kadaluarsa'=>'Kadaluarsa', 'Dibatalkan'=>'Dibatalkan', 'Ditolak'=>'Ditolak'];
$statusTone = static function (string $value): string {
    if ($value === 'Selesai') return 'success';
    if ($value === 'Dikirim') return 'info';
    if (in_array($value, ['Proses', 'Menunggu Pembayaran Rekening'], true)) return 'warning';
    if (in_array($value, ['Dibatalkan', 'Ditolak', 'Gagal'], true)) return 'danger';
    return 'neutral';
};
$transactionDate = static function ($value): string {
    if (!$value) return 'Waktu belum tersedia';
    try { return (new DateTimeImmutable((string)$value, new DateTimeZone('Asia/Jakarta')))->setTimezone(new DateTimeZone('Asia/Jakarta'))->format('d M Y, H:i') . ' WIB'; }
    catch (Throwable $error) { return 'Waktu perlu diperiksa'; }
};
$totalRows = (int)($totalFiltered ?? count($semuaTransaksiCus));
$currentPage = max(1, (int)$page);
$pageSize = max(1, (int)($perPage ?? 20));
$totalPages = max(1, (int)ceil($totalRows / $pageSize));
$currentPage = min($currentPage, $totalPages);
$rangeStart = $totalRows ? ($currentPage - 1) * $pageSize + 1 : 0;
$rangeEnd = $totalRows ? $rangeStart + count($transaksiCus) - 1 : 0;
?>
<main class="konten admin-orders-page" id="admin-orders-main" aria-labelledby="orders-heading">
    <div class="container">
        <header class="orders-page-heading">
            <div><p class="admin-section-kicker">Operasional toko</p><h2 id="orders-heading">Pesanan pelanggan</h2><p>Tinjau pembayaran, teruskan ke pusat, dan pantau pengiriman.</p></div>
            <a href="/shippingadmin" class="btn orders-secondary"><i class="material-icons" aria-hidden="true">local_shipping</i>Atur pengiriman</a>
        </header>
        <?php if ($orderFeedback = session()->getFlashdata('msg')) { ?>
            <div class="alert alert-info orders-feedback" role="status"><?= esc($orderFeedback); ?></div>
        <?php } ?>
        <div class="orders-overview" aria-label="Ringkasan daftar pesanan">
            <div><span>Semua pesanan</span><strong><?= number_format((int)($totalAllOrders ?? count($semuaTransaksiCus)), 0, ',', '.'); ?></strong></div>
            <div><span>Sesuai filter</span><strong><?= number_format($totalRows, 0, ',', '.'); ?></strong></div>
            <div><span>Ditampilkan</span><strong><?= $rangeStart; ?>–<?= $rangeEnd; ?><small> / <?= $totalRows; ?></small></strong></div>
        </div>
        <p class="orders-helper">Pesanan sandbox disembunyikan. Pesanan lama tanpa penanda tetap tersedia sebagai riwayat dan tidak dapat diproses.</p>
        <?php $actionCounts = $actionCounts ?? []; ?>
        <div class="orders-overview orders-action-overview" aria-label="Pesanan yang memerlukan perhatian">
            <div><span>Menunggu pembayaran</span><strong><?= number_format((int)($actionCounts['waiting_payment'] ?? 0), 0, ',', '.'); ?></strong></div>
            <div><span>Perlu tinjau transfer</span><strong><?= number_format((int)($actionCounts['review_transfer'] ?? 0), 0, ',', '.'); ?></strong></div>
            <div><span>Siap diproses</span><strong><?= number_format((int)($actionCounts['ready_to_process'] ?? 0), 0, ',', '.'); ?></strong></div>
        </div>
        <section class="orders-workspace" aria-label="Daftar dan filter pesanan">
            <div class="orders-toolbar">
                <div class="orders-search-field"><label for="orders-search">Cari di halaman ini</label><div class="orders-search-input"><i class="material-icons" aria-hidden="true">search</i><input id="orders-search" type="search" placeholder="Nama, email, atau nomor pesanan" autocomplete="off"></div></div>
                <div class="orders-status-field"><label for="orders-status">Status pesanan</label><select class="form-select" id="orders-status"><?php foreach ($statusOptions as $value=>$label) { ?><option value="<?= esc($value, 'attr'); ?>" <?= $status === $value ? 'selected' : ''; ?>><?= esc($label); ?></option><?php } ?></select></div>
            </div>
            <div id="btn-reload" class="orders-update-notice d-none" role="status"><i class="material-icons" aria-hidden="true">sync</i><span>Ada pembaruan pesanan.</span><a href="<?= esc('/listcustomer/' . $currentPage . '/' . $status, 'attr'); ?>">Perbarui daftar</a></div>
            <div class="orders-list-caption"><span>Daftar pesanan</span><span id="orders-search-feedback" aria-live="polite">Halaman <?= $currentPage; ?> dari <?= $totalPages; ?></span></div>
            <div class="container-list-customer">
                <?php if (!$transaksiCus) { ?>
                    <div class="orders-empty"><i class="material-icons" aria-hidden="true">inbox</i><h3>Belum ada pesanan pada filter ini</h3><p>Pilih status lain untuk melihat pesanan yang tersedia.</p><a class="btn orders-secondary" href="/listcustomer">Lihat semua pesanan</a></div>
                <?php } ?>
                <?php foreach ($transaksiCus as $t_ind=>$t) {
                    $id = (string)$t['id_midtrans'];
                    $urlId = rawurlencode($id);
                    $isLegacy = !empty($t['is_legacy']);
                    $isProduction = !empty($t['is_production']);
                    $canEdit = $isProduction && in_array($t['status'], ['Proses', 'Dikirim'], true);
                    $needsReview = $isProduction && $t['status'] === 'Menunggu Pembayaran Rekening' && !empty($t['bukti_bayar']);
                    $hasProof = !empty($t['bukti_bayar']);
                    $payment = $t['data_mid'] ?? [];
                    $statusLabel = $needsReview ? 'Perlu konfirmasi' : $t['status'];
                    $isInternal = ($t['shipment']['shipping_method_code'] ?? '') === 'internal_delivery';
                    $courier = $isInternal ? 'Jasa Kirim Toko' : trim((string)($t['shipment']['courier_name'] ?? $t['kurir'] ?? ''));
                    $awb = \App\Services\Integrations\LunaOrderSync::validTracking($t['resi'] ?? null) ? $t['resi'] : 'Belum tersedia';
                    $lunaStage = $payment['_app_luna_fulfillment']['stage'] ?? '';
                ?>
                <article class="list-customer <?= $needsReview ? 'need-confirm' : ''; ?>" data-order-index="<?= (int)$t_ind; ?>">
                    <div class="customer-order-summary">
                        <div class="orders-identity"><a class="orders-order-number" href="/order/<?= $urlId; ?>"><?= esc($id); ?><i class="material-icons" aria-hidden="true">north_east</i></a><strong class="nama"><?= esc($t['nama_pen']); ?></strong><span><?= esc($t['email_cus']); ?></span></div>
                        <div class="orders-state"><?php if ($isLegacy) { ?><span class="orders-status orders-status--neutral">Riwayat · tanpa penanda</span><?php } ?><span class="orders-status orders-status--<?= $statusTone($t['status']); ?>"><?= esc($statusLabel); ?></span><small><?= esc($courier && $courier !== 'kosong' ? $courier : 'Ekspedisi belum tersedia'); ?></small></div>
                        <div class="orders-order-total"><strong><?= esc($money($payment['gross_amount'] ?? 0)); ?></strong><small><?= esc($transactionDate($payment['transaction_time'] ?? null)); ?></small></div>
                        <button type="button" class="orders-detail-toggle" aria-expanded="false" aria-controls="order-detail-<?= (int)$t_ind; ?>" aria-label="Detail pesanan <?= esc($id, 'attr'); ?>" data-order-toggle="<?= (int)$t_ind; ?>"><span>Detail</span><i class="material-icons" aria-hidden="true">expand_more</i></button>
                    </div>
                    <div class="list-customer-detail" id="order-detail-<?= (int)$t_ind; ?>" hidden>
                        <div class="orders-detail-grid">
                            <section class="orders-detail-section" aria-labelledby="order-items-<?= (int)$t_ind; ?>">
                                <h3 id="order-items-<?= (int)$t_ind; ?>">Rincian pesanan</h3>
                                <div class="orders-items-table"><div class="orders-item-line orders-item-head"><span>Produk / varian</span><span>Jumlah</span><span>Harga satuan</span></div>
                                <?php $totalHargaBarang = 0; foreach ($t['items'] as $barang) { $totalHargaBarang += $barang['value'] * $barang['quantity']; ?>
                                    <div class="orders-item-line"><span><?= esc($barang['name']); ?></span><span><?= esc((string)$barang['quantity']); ?></span><span><?= esc($money($barang['value'])); ?></span></div>
                                <?php } ?></div>
                                <dl class="orders-totals"><div><dt>Subtotal produk</dt><dd><?= esc($money($totalHargaBarang)); ?></dd></div><div><dt><?= !empty($t['shipment']) ? 'Biaya / penyesuaian lainnya' : 'Biaya / potongan di luar rincian'; ?></dt><dd><?= esc($money((float)($payment['gross_amount'] ?? 0) - $totalHargaBarang - (int)($t['shipment']['shipping_cost'] ?? 0))); ?></dd></div>
                                <?php if (!empty($t['shipment'])) {
                                    $shippingPricing = json_decode((string)($t['shipment']['shipping_pricing'] ?? ''), true);
                                    if (is_array($shippingPricing) && (int)($shippingPricing['subsidy_amount'] ?? 0) > 0) { ?>
                                    <div class="orders-internal-line"><dt>Tarif ekspedisi asli · internal</dt><dd><?= esc($money($shippingPricing['original_cost'])); ?></dd></div><div class="orders-internal-line"><dt>Subsidi toko · internal</dt><dd><?= esc($money($shippingPricing['subsidy_amount'])); ?></dd></div>
                                <?php } ?><div><dt>Ongkir dibayar pembeli</dt><dd><?= esc($money($t['shipment']['shipping_cost'])); ?></dd></div><?php } ?>
                                <div class="orders-grand-total"><dt>Total pesanan</dt><dd><?= esc($money($payment['gross_amount'] ?? 0)); ?></dd></div></dl>
                                <div class="orders-document-actions"><a href="/invoice/<?= $urlId; ?>" class="btn orders-secondary"><i class="material-icons" aria-hidden="true">description</i>Invoice</a><a href="/pdf/<?= $urlId; ?>" class="btn orders-secondary"><i class="material-icons" aria-hidden="true">print</i>Form gudang</a><?php if ($hasProof) { ?><button type="button" class="btn orders-secondary" data-proof-index="<?= (int)$t_ind; ?>"><i class="material-icons" aria-hidden="true">receipt</i>Bukti bayar</button><?php } ?></div>
                                <?php if ($t['status'] === 'Menunggu Pembayaran') { ?>
                                <details class="orders-payment-details"><summary>Informasi pembayaran</summary><p><?= esc($paymentLabels[$payment['payment_type'] ?? ''] ?? ucfirst(str_replace('_', ' ', (string)($payment['payment_type'] ?? '-')))); ?></p>
                                <?php foreach (($payment['va_numbers'] ?? []) as $va) { ?><p><?= esc(strtoupper((string)($va['bank'] ?? ''))); ?> · <?= esc($va['va_number'] ?? ''); ?></p><?php } ?>
                                <?php foreach (['permata_va_number'=>'Permata VA', 'bca_va_number'=>'BCA VA', 'biller_code'=>'Biller code', 'bill_key'=>'Bill key', 'bank'=>'Bank', 'card_type'=>'Jenis kartu'] as $key=>$label) { if (!empty($payment[$key])) { ?><p><?= esc($label); ?>: <?= esc((string)$payment[$key]); ?></p><?php } } ?>
                                <?php if (in_array($payment['payment_type'] ?? '', ['qris', 'gopay'], true)) { ?><a href="/qris/<?= rawurlencode((string)($payment['order_id'] ?? $id)); ?>-<?= rawurlencode((string)($payment['gross_amount'] ?? 0)); ?>">Lihat barcode pembayaran</a><?php } ?></details>
                                <?php } ?>
                            </section>
                            <div class="orders-fulfillment-column">
                                <section class="orders-detail-section"><h3>Informasi penerima</h3><p class="orders-recipient-name"><?= esc($t['nama_pen']); ?></p><p><?= nl2br(esc($t['alamat_pen'])); ?></p><p><?= esc($t['hp_pen']); ?></p><?php if ($t['note'] !== '') { ?><div class="orders-customer-note"><span>Catatan pembeli</span><p><?= nl2br(esc($t['note'])); ?></p></div><?php } ?></section>
                                <section class="orders-detail-section"><div class="orders-section-heading"><h3>Pengiriman</h3><?php if ($canEdit) { ?><button type="button" class="btn orders-secondary" data-shipping-index="<?= (int)$t_ind; ?>">Atur resi</button><?php } ?></div><dl class="orders-shipping-data"><div><dt>Ekspedisi</dt><dd><?= esc($courier ?: 'Belum tersedia'); ?></dd></div><div><dt><?= $isInternal ? 'Resi toko' : 'Nomor resi'; ?></dt><dd class="resi"><?= esc($awb); ?></dd></div></dl>
                                <?php if ($isProduction && $t['status'] === 'Proses') { ?>
                                    <form class="order-sync-form" method="post" action="/syncorder/<?= $urlId; ?>"><?= csrf_field(); ?><div class="orders-process-status"><i class="material-icons" aria-hidden="true"><?= $lunaStage === 'processed' ? 'task_alt' : 'schedule'; ?></i><p><?= esc(\App\Services\Integrations\LunaFulfillment::label($payment)); ?></p></div><label class="orders-review-check"><input type="checkbox" name="review_confirmed" value="1" required><span>Produk, varian, alamat, dan ekspedisi sudah saya periksa.</span></label><button type="submit" class="btn btn-primary1 orders-sync-button"><?= $lunaStage ? 'Periksa status MyLuna' : 'Teruskan ke MyLuna'; ?><i class="material-icons" aria-hidden="true"><?= $lunaStage ? 'sync' : 'arrow_forward'; ?></i></button><p class="orders-helper">Resi diisi setelah proses pusat. Pengiriman ulang tidak membuat pesanan baru.</p></form>
                                <?php } ?>
                                <?php if ($isProduction && (in_array($t['status'], ['Dikirim', 'Selesai'], true) || ($t['shipment']['shipping_status'] ?? '') === 'Disiapkan')) { ?><button type="button" class="btn orders-secondary orders-resync" data-shipping-retry="<?= (int)$t_ind; ?>"><i class="material-icons" aria-hidden="true">sync</i>Sinkron ulang pengiriman</button><?php } ?>
                                <?php if ($isProduction && $t['status'] === 'Dikirim') { ?><button type="button" class="btn orders-secondary orders-complete" data-complete-index="<?= (int)$t_ind; ?>"><i class="material-icons" aria-hidden="true">check_circle</i>Konfirmasi pesanan diterima</button><?php } ?>
                                <?php if ($needsReview) { ?><button type="button" class="btn btn-primary1 orders-review-payment" data-proof-index="<?= (int)$t_ind; ?>">Tinjau bukti pembayaran</button><?php } ?>
                                </section>
                            </div>
                        </div>
                    </div>
                </article>
                <?php } ?>
            </div>
            <div class="orders-empty orders-search-empty" hidden><i class="material-icons" aria-hidden="true">search_off</i><h3>Tidak ditemukan di halaman ini</h3><p>Coba kata lain, halaman berikutnya, atau ubah filter status.</p><button class="btn orders-secondary" type="button" data-clear-order-search>Hapus pencarian</button></div>
            <?php if ($totalPages > 1) {
                $pages = [1, $totalPages]; for ($x=$currentPage-2; $x<=$currentPage+2; $x++) if ($x>=1 && $x<=$totalPages) $pages[]=$x;
                $pages=array_values(array_unique($pages)); sort($pages); $lastPrintedPage=0;
            ?>
            <nav class="customer-pagination-wrap" aria-label="Navigasi halaman pesanan"><span>Halaman <?= $currentPage; ?> dari <?= $totalPages; ?></span><ul class="pagination mb-0">
                <li class="page-item <?= $currentPage <= 1 ? 'disabled' : ''; ?>"><?php if ($currentPage > 1) { ?><a class="page-link" href="/listcustomer/<?= $currentPage-1; ?>/<?= esc($status, 'attr'); ?>" aria-label="Halaman sebelumnya"><i class="material-icons" aria-hidden="true">chevron_left</i></a><?php } else { ?><span class="page-link" aria-disabled="true"><i class="material-icons" aria-hidden="true">chevron_left</i></span><?php } ?></li>
                <?php foreach ($pages as $pageNumber) { if ($lastPrintedPage && $pageNumber>$lastPrintedPage+1) { ?><li class="page-item disabled"><span class="page-link">…</span></li><?php } ?><li class="page-item <?= $pageNumber===$currentPage ? 'active' : ''; ?>"><a class="page-link" href="/listcustomer/<?= $pageNumber; ?>/<?= esc($status, 'attr'); ?>" <?= $pageNumber===$currentPage ? 'aria-current="page"' : ''; ?> aria-label="Halaman <?= $pageNumber; ?>"><?= $pageNumber; ?></a></li><?php $lastPrintedPage=$pageNumber; } ?>
                <li class="page-item <?= $currentPage >= $totalPages ? 'disabled' : ''; ?>"><?php if ($currentPage<$totalPages) { ?><a class="page-link" href="/listcustomer/<?= $currentPage+1; ?>/<?= esc($status, 'attr'); ?>" aria-label="Halaman berikutnya"><i class="material-icons" aria-hidden="true">chevron_right</i></a><?php } else { ?><span class="page-link" aria-disabled="true"><i class="material-icons" aria-hidden="true">chevron_right</i></span><?php } ?></li>
            </ul></nav><?php } ?>
        </section>
    </div>
</main>
<div class="modal fade admin-order-modal" id="container-edit-resi" tabindex="-1" aria-labelledby="shipping-editor-title" aria-hidden="true"><div class="modal-dialog modal-dialog-centered modal-dialog-scrollable"><form class="modal-content" id="shipping-editor-form"><div class="modal-header"><div><p class="admin-section-kicker">Detail pengiriman</p><h2 class="modal-title" id="shipping-editor-title">Pengiriman pesanan</h2></div><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup dialog pengiriman"></button></div><div class="modal-body"><p class="orders-modal-order-id" id="shipping-editor-order"></p><div id="shipping-save-feedback" class="orders-modal-feedback" role="status" aria-live="polite"></div><div id="internal-shipping-fields" class="mb-3"><label class="form-label" for="internal-shipping-status">Tahap pengiriman</label><select id="internal-shipping-status" class="form-select"><option value="Disiapkan">Disiapkan</option><option value="Dalam Pengiriman">Dalam Pengiriman</option><option value="Gagal Kirim">Gagal Kirim</option><option value="Terkirim">Terkirim</option></select><p class="orders-helper">Pilih Dalam Pengiriman hanya ketika barang benar-benar dikirim.</p></div><div class="mb-3" id="external-shipping-courier"><label class="form-label" for="shipping-courier">Ekspedisi / layanan</label><input type="text" class="form-control" id="shipping-courier" name="kurir" maxlength="255"></div><div id="external-shipping-awb"><label class="form-label" id="awb-label" for="shipping-awb">Nomor resi (wajib)</label><input type="text" class="form-control" id="shipping-awb" name="resi" maxlength="120" required autocomplete="off"><p class="orders-helper">Gunakan resi aktual, termasuk nomor internal untuk Jasa Kirim Toko.</p></div></div><div class="modal-footer"><button type="button" class="btn orders-secondary" data-bs-dismiss="modal">Tutup</button><button type="submit" class="btn btn-primary1" id="shipping-save-button">Simpan pengiriman</button></div></form></div></div>
<div class="modal fade admin-order-modal" id="container-bukti-bayar" tabindex="-1" aria-labelledby="payment-proof-title" aria-hidden="true"><div class="modal-dialog modal-dialog-centered modal-dialog-scrollable"><div class="modal-content"><div class="modal-header"><div><p class="admin-section-kicker">Pemeriksaan pembayaran</p><h2 class="modal-title" id="payment-proof-title">Bukti pembayaran</h2></div><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup bukti pembayaran"></button></div><div class="modal-body"><p id="payment-proof-order" class="orders-modal-order-id"></p><img id="payment-proof-image" class="orders-proof-image" src="" alt="Bukti transfer pembeli"><p class="orders-helper">Cocokkan nominal dan mutasi rekening sebelum mengonfirmasi pembayaran.</p></div><div class="modal-footer"><button type="button" class="btn orders-secondary" data-bs-dismiss="modal">Tutup</button><form id="payment-proof-reject" method="post" hidden><?= csrf_field(); ?><button type="submit" class="btn orders-danger">Tolak pembayaran</button></form><form id="payment-proof-accept" method="post" hidden><?= csrf_field(); ?><button type="submit" class="btn btn-primary1">Konfirmasi pembayaran</button></form></div></div></div></div>
<script type="application/json" id="admin-orders-data"><?= json_encode(['orders'=>$transaksiCus, 'csrfName'=>csrf_token(), 'csrfHash'=>csrf_hash(), 'wsUrl'=>$wsUrl ?? ''], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE); ?></script>
<script defer src="/js/admin-orders.js?v=<?= filemtime(FCPATH . 'js/admin-orders.js'); ?>"></script>
<?= $this->endSection(); ?>
