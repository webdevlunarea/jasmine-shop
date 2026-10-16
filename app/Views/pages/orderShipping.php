<?= $this->extend("layout/template"); ?>
<?= $this->section("content"); ?>
<?php
$shipment = $shipment ?? null;
$isInternalDelivery = $shipment && (($shipment['shipping_method_code'] ?? '') === 'internal_delivery');
$shippingName = 'Belum dipilih';
if ($shipment) {
    $shippingName = $isInternalDelivery
        ? 'Jasa Kirim Toko'
        : trim(implode(' - ', array_filter([
            $shipment['courier_name'] ?? $shipment['courier_code'] ?? '',
            $shipment['service_name'] ?? $shipment['service_code'] ?? '',
        ])));
    $shippingName = $shippingName ?: 'Ekspedisi';
} elseif (($kurir ?? '') !== 'kosong') {
    $shippingName = (string)$kurir;
}
$trackingNumber = trim((string)($shipment['tracking_number'] ?? $pemesanan['resi'] ?? ''));
$shippingStatus = ($pemesanan['status'] ?? '') === 'Selesai' ? 'Selesai' : ($shipment['shipping_status'] ?? (($kurir ?? '') === 'kosong' ? 'Sedang diproses' : 'Dikirim'));
$shippingCost = $shipment ? (int)($shipment['shipping_cost'] ?? 0) : null;
if (($pemesanan['status'] ?? '') === 'Proses') $shippingStatus = \App\Services\Integrations\LunaFulfillment::label($pemesanan['data_mid'] ?? []);
?>
<?php
$orderStatus = (string)($pemesanan['status'] ?? 'Dikirim');
$orderComplete = $orderStatus === 'Selesai';
$orderStage = $orderComplete ? 4 : ($orderStatus === 'Proses' ? 2 : 3);
$orderTitle = $orderComplete ? 'Pesanan selesai' : ($orderStatus === 'Proses' ? 'Pesanan sedang diproses' : 'Pesanan dalam pengiriman');
$orderDescription = $orderComplete ? 'Terima kasih telah berbelanja di Lunarea. Detail pesanan Anda tetap tersedia di halaman ini.' : ($orderStatus === 'Proses' ? 'Pembayaran diterima. Tim kami sedang menyiapkan pesanan Anda.' : 'Pantau informasi pengiriman dan nomor resi pesanan Anda di bawah ini.');
?>
<main class="konten order-page-shell buyer-order-page" aria-labelledby="order-title">
    <div class="container">
        <header class="order-heading">
            <a class="order-back-link" href="<?= session('isLogin') && session('email') !== 'tamu' ? '/transaction' : '/all'; ?>"><span aria-hidden="true">←</span> <?= session('isLogin') && session('email') !== 'tamu' ? 'Riwayat pesanan' : 'Lihat produk'; ?></a>
            <p class="order-eyebrow">Pesanan Lunarea</p>
            <h1 id="order-title"><?= esc($orderTitle); ?></h1>
            <p class="order-intro"><?= esc($orderDescription); ?></p>
            <div class="order-reference"><span>Nomor pesanan</span><strong><?= esc($pemesanan['id_midtrans']); ?></strong></div>
        </header>
        <ol class="order-steps" aria-label="Progres pesanan">
            <?php foreach (['Pembayaran', 'Diproses', 'Dikirim', 'Selesai'] as $stepIndex => $stepLabel) { ?>
            <li class="<?= $stepIndex + 1 <= $orderStage ? 'is-reached' : ''; ?>" <?= $stepIndex + 1 === $orderStage ? 'aria-current="step"' : ''; ?>><span class="order-step-number" aria-hidden="true"><?= $stepIndex + 1; ?></span><span><?= esc($stepLabel); ?></span></li>
            <?php } ?>
        </ol>
        <p class="order-link-note">Simpan tautan halaman ini untuk memeriksa perkembangan pesanan Anda. Jangan bagikan tautan kepada orang lain.</p>
        <div class="baris-ke-kolom order-detail-grid">
            <div class="limapuluh-ke-seratus order-detail-section">
                <div class="d-flex justify-content-between mb-3 order-payment-summary">
                    <div class="flex-grow-1">
                        <p class="m-0">Metode Pembayaran</p>
                        <?php if ($dataMid['payment_type'] == 'bank_transfer' || $dataMid['payment_type'] == 'echannel' || $dataMid['payment_type'] == 'rekening') { ?>
                            <h5><?= strtoupper($bank); ?></h5>
                        <?php } else if ($dataMid['payment_type'] == 'qris') { ?>
                            <h5>Qris</h5>
                        <?php } else if ($dataMid['payment_type'] == 'gopay') { ?>
                            <h5>Gopay</h5>
                        <?php } else if ($dataMid['payment_type'] == 'shopeepay') { ?>
                            <h5>ShopeePay</h5>
                        <?php } else if ($dataMid['payment_type'] == 'credit_card') { ?>
                            <h5>Kartu Kredit</h5>
                        <?php } ?>
                    </div>
                    <div class="flex-grow-1">
                        <p class="m-0">Nominal</p>
                        <h5>Rp <?= number_format($dataMid['gross_amount'], 0, ",", "."); ?></h5>
                    </div>
                </div>
                <div class="mb-3">
                    <p class="m-0">Status</p>
                    <h5 style="color: var(--hijau);">Telah dibayar</h5>
                </div>
                <span class="garis mb-3"></span>
                <h2 class="order-section-title">Rincian pesanan</h2>
                <?= view('partials/order_items', ['items' => $items]); ?>
            </div>
            <div class="limapuluh-ke-seratus order-detail-section">
                <h2 class="order-section-title">Informasi pengiriman</h2>
                <div class="shipping-detail-panel flex-grow-1 mb-4">
                    <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                        <div>
                            <span class="shipping-pill"><svg class="order-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M3 6h11v12H3V6Zm11 5h4l3 4v3h-7M8 18a2 2 0 1 1-4 0 2 2 0 0 1 4 0Zm12 0a2 2 0 1 1-4 0 2 2 0 0 1 4 0Z"/></svg><?= esc($isInternalDelivery ? 'Internal' : 'Ekspedisi'); ?></span>
                            <h5 class="m-0 mt-2"><?= esc($shippingName); ?></h5>
                            <p class="m-0 text-secondary"><?= esc($shippingStatus); ?></p>
                            <?php if (!empty($shipment['etd'])) { ?>
                                <p class="m-0 text-secondary">Estimasi <?= esc($shipment['etd']); ?></p>
                            <?php } ?>
                        </div>
                        <div class="text-end">
                            <p class="m-0 text-secondary">Ongkir</p>
                            <h5 class="mb-0"><?= $shippingCost === null ? '-' : 'Rp ' . number_format($shippingCost, 0, ",", "."); ?></h5>
                        </div>
                    </div>

                    <?php if ($trackingNumber !== '' && strtolower($trackingNumber) !== 'kosong' && stripos($trackingNumber, 'menunggu') === false) { ?>
                        <div class="shipping-resi-row">
                            <div>
                                <p class="m-0 text-secondary">Nomor Resi</p>
                                <h6 class="mb-0"><?= esc($trackingNumber); ?></h6>
                            </div>
                            <a href="/lacak-pengiriman?keyword=<?= urlencode($pemesanan['id_midtrans']); ?>" class="btn btn-primary1">Lacak Pengiriman</a>
                        </div>
                    <?php } elseif ($isInternalDelivery) { ?>
                        <div class="shipping-resi-row">
                            <div>
                                <p class="m-0 text-secondary">Catatan</p>
                                <h6 class="mb-0">Dikirim langsung oleh tim Lunarea</h6>
                            </div>
                            <a href="/lacak-pengiriman?keyword=<?= urlencode($pemesanan['id_midtrans']); ?>" class="btn btn-outline-success">Lihat Status</a>
                        </div>
                    <?php } else { ?>
                        <div class="shipping-resi-row">
                            <div>
                                <p class="m-0 text-secondary">Status resi</p>
                                <h6 class="mb-0">Informasi resi akan tampil setelah pengiriman diperbarui.</h6>
                            </div>
                        </div>
                    <?php } ?>
                </div>
                <div>
                    <p class="m-0 fw-bold">Alamat</p>
                    <p class="m-0"><?= esc($pemesanan['alamat_pen']); ?></p>
                </div>
            </div>
        </div>
    </div>
</main>
<style>
    .shipping-detail-panel {
        border: 1px solid var(--hijaumuda2);
        border-radius: 22px;
        padding: 18px;
        background: linear-gradient(135deg, #fff 0%, var(--hijaumuda) 100%);
        box-shadow: 0 12px 28px rgba(36, 59, 107, .06);
    }

    .shipping-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border-radius: 999px;
        padding: 6px 10px;
        background: var(--hijau);
        color: #fff;
        font-weight: 800;
        font-size: .78rem;
    }

    .shipping-pill .material-icons {
        font-size: 16px;
    }

    .shipping-resi-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
        margin-top: 14px;
        padding-top: 14px;
        border-top: 1px dashed var(--hijaumuda2);
    }

    @media (max-width: 575.98px) {
        .shipping-detail-panel {
            padding: 14px;
            border-radius: 18px;
        }

        .shipping-resi-row .btn {
            width: 100%;
        }
    }
</style>
<?= $this->endSection(); ?>
