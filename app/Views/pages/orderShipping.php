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
$shippingStatus = $shipment['shipping_status'] ?? (($kurir ?? '') === 'kosong' ? 'Sedang diproses' : 'Dikirim');
$shippingCost = $shipment ? (int)($shipment['shipping_cost'] ?? 0) : null;
?>
<div class="konten order-page-shell">
    <div class="container">
        <div class="d-flex justify-content-center align-items-center gap-2 my-5 order-steps">
            <div style="background-color: var(--hijau); padding: 1em; border-radius: 2em;"><i class="material-icons text-light">access_time</i></div>
            <div class="order-step-connector" style="color: var(--hijau)">--------</div>
            <div class="d-flex flex-column justify-content-center align-items-center">
                <div style="background-color: var(--hijau); padding: 1em; border-radius: 2em;"><i class="material-icons text-light">local_shipping</i></div>
                <p class="m-0 text-center fw-bold" style="line-height: 20px; color: var(--hijau)"><?= $kurir == 'kosong' ? 'Diproses' : 'Dikirim'; ?></p>
            </div>
            <div class="order-step-connector" style="color: gray">--------</div>
            <div style="background-color: gray; padding: 1em; border-radius: 2em;"><i class="material-icons text-light">done</i></div>
        </div>
    </div>
    <div class="py-1 text-light w-100 text-center mb-5 order-reference" style="background-color: var(--hijau);">ID
        Pesanan :
        <b><?= $pemesanan['id_midtrans']; ?></b>
    </div>
    <div class="container">
        <p class="my-auto text-secondary text-sm-start mb-4 limapuluh-ke-seratus">*Simpan URL halaman ini untuk melihat status pesanan. Atau dapat login sebagai member kami agar dapat melihat seluruh riwayat pesanan Anda.</p>
        <div class="baris-ke-kolom">
            <div class="limapuluh-ke-seratus">
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
                <p class="mb-2 fw-bold text-center">Produk yang Anda pesan</p>
                <div>
                    <table class="table table-borderless">
                        <tbody>
                            <?php foreach ($items as $i) { ?>
                                <tr>
                                    <td>
                                        <p class="mb-0"><?= $i['name']; ?></p>
                                    </td>
                                    <td>
                                        <p class="mb-0"><?= $i['quantity']; ?></p>
                                    </td>
                                    <td class="text-end">
                                        <p class="mb-0">Rp <?= number_format($i['value'], 0, ",", "."); ?></p>
                                    </td>
                                </tr>
                            <?php } ?>
                            <!-- <tr>
                                <td>
                                    <p class="mb-0">Lemari Hias LH 3310 (Sonoma)</p>
                                </td>
                                <td>
                                    <p class="mb-0">2</p>
                                </td>
                                <td class="text-end">
                                    <p class="mb-0">Rp 350.000</p>
                                </td>
                            </tr> -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="limapuluh-ke-seratus">
                <p class="fw-bold mb-2">Pengiriman</p>
                <div class="shipping-detail-panel flex-grow-1 mb-4">
                    <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                        <div>
                            <span class="shipping-pill"><i class="material-icons">local_shipping</i><?= esc($isInternalDelivery ? 'Internal' : 'Ekspedisi'); ?></span>
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
                                <h6 class="mb-0">Resi akan tampil setelah admin memperbarui pengiriman.</h6>
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
</div>
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
