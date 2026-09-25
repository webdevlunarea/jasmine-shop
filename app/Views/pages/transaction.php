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
$returSolutionLabel = function ($solution) {
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
$flashMsg = session()->getFlashdata('msg');
?>
<div class="konten account-shell">
    <div class="container">
        <?php $activeAccountMenu = 'transaction'; ?>
        <div class="account-layout">
            <?= $this->include('partials/account_nav'); ?>
            <main class="account-content-card">
                <div class="p-2">
                    <h3>Transaksi Pembayaran</h3>
                    <?php if ($flashMsg) { ?>
                        <div class="alert alert-info"><?= esc($flashMsg); ?></div>
                    <?php } ?>
                    <div class="accordion" id="accordionExample">
                        <?php if (count($transaksi) > 0) {
                            foreach ($transaksi as $index_transaksi => $item_transaksi) { ?>
                                <?php
                                $returAktif = $returMap[$item_transaksi['id_midtrans']] ?? null;
                                $returItems = $returAktif ? $decodeArray($returAktif['items'] ?? '[]') : [];
                                $returBukti = $returAktif ? $decodeArray($returAktif['bukti'] ?? '[]') : [];
                                $returLuna = $returAktif ? $decodeArray($returAktif['luna_response'] ?? '{}') : [];
                                ?>
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $index_transaksi; ?>" aria-expanded="false" aria-controls="collapse<?= $index_transaksi; ?>">
                                            <div class="d-flex justify-content-between" style="width: 100%;">
                                                <div>
                                                    <h5 class="mb-0">Rp
                                                        <?= number_format((int)json_decode($item_transaksi['data_mid'], true)['gross_amount'], 0, ",", "."); ?>
                                                    </h5>
                                                    <p class="mb-1" style="font-size: 12px;">ID Pesanan:
                                                        <?= $item_transaksi['id_midtrans']; ?></p>
                                                    <span class="badge rounded-pill <?php
                                                                                    switch ($item_transaksi['status']) {
                                                                                        case 'Menunggu Pembayaran':
                                                                                            echo "text-bg-primary";
                                                                                            break;
                                                                                        case 'Menunggu Pembayaran Rekening':
                                                                                            echo $item_transaksi['bukti_bayar'] ? 'text-bg-warning' : 'text-bg-primary';
                                                                                            break;
                                                                                        case 'Proses':
                                                                                            echo "text-bg-warning";
                                                                                            break;
                                                                                        case 'Dikirim':
                                                                                            echo "text-bg-info";
                                                                                            break;
                                                                                        case 'Selesai':
                                                                                            echo "text-bg-success";
                                                                                            break;
                                                                                        case 'Dibatalkan':
                                                                                            echo "text-bg-danger";
                                                                                            break;
                                                                                        case 'Gagal':
                                                                                            echo "text-bg-danger";
                                                                                            break;
                                                                                        default:
                                                                                            echo "text-bg-dark";
                                                                                            break;
                                                                                    }
                                                                                    ?>"><?= ucfirst($item_transaksi['status'] == 'Menunggu Pembayaran Rekening' ? ($item_transaksi['bukti_bayar'] ? 'Menunggu Konfirmasi' : 'Menunggu Pembayaran') : $item_transaksi['status']); ?></span>
                                                    <?php if ($returAktif) { ?>
                                                        <span class="badge rounded-pill <?= $returBadgeClass($returAktif['status'] ?? ''); ?>">
                                                            Retur: <?= esc($returAktif['status']); ?>
                                                        </span>
                                                    <?php } ?>
                                                </div>
                                                <div class="d-flex flex-column justify-content-end">
                                                    <p class="mb-0 text-secondary" style="font-size: 12px;">
                                                        <?= date("d/m/Y H:i:s", strtotime(json_decode($item_transaksi['data_mid'], true)['transaction_time'])); ?></p>
                                                    <?php if ($item_transaksi['status'] == "Menunggu Pembayaran" || $item_transaksi['status'] == "Menunggu Pembayaran Rekening") { ?>
                                                        <p class="mb-0 text-secondary" style="font-size: 12px;">Kadaluarsa pada <?php
                                                                                                                                $dataMid = json_decode($item_transaksi['data_mid'], true);
                                                                                                                                $d = strtotime($dataMid['transaction_time']);
                                                                                                                                if ($dataMid['payment_type'] == 'gopay' || $dataMid['payment_type'] == 'qris')
                                                                                                                                    $enddate = strtotime("+15 minutes", $d);
                                                                                                                                else
                                                                                                                                    $enddate = strtotime("+1 hour", $d);
                                                                                                                                echo date("d/m/Y H:i:s", $enddate);
                                                                                                                                ?>
                                                        </p>
                                                    <?php } ?>
                                                </div>
                                            </div>
                                        </button>
                                    </h2>
                                    <div id="collapse<?= $index_transaksi; ?>" class="accordion-collapse collapse" data-bs-parent="#accordionExample">
                                        <div class="accordion-body">
                                            <p class="fw-bold mb-0">Informasi Penerima</p>
                                            <p class="mb-0"><?= $item_transaksi['nama_pen']; ?></p>
                                            <p class="mb-0"><?= $item_transaksi['alamat_pen']; ?></p>
                                            <p><?= $item_transaksi['hp_pen']; ?></p>
                                            <p class="mb-0"><b>Items</b></p>
                                            <div class="w-100 mb-2">
                                                <?php foreach (json_decode($item_transaksi['items'], true) as $item) { ?>
                                                    <div class="w-100 d-flex">
                                                        <div style="flex: 2;">
                                                            <p class="mb-0"><?= $item['name']; ?></p>
                                                        </div>
                                                        <div style="flex: 1;" class="text-center">
                                                            <p class="mb-0"><?= $item['quantity']; ?></p>
                                                        </div>
                                                        <div style="flex: 1;" class="text-end">
                                                            <p class="mb-0">Rp <?= number_format($item['value'], 0, ",", "."); ?></p>
                                                        </div>
                                                    </div>
                                                <?php } ?>
                                            </div>
                                            <div class="w-100 d-flex justify-content-between mb-2">
                                                <div class="w-100">
                                                    <?php if ($item_transaksi['status'] == "Menunggu Pembayaran" || $item_transaksi['status'] == "Menunggu Pembayaran Rekening") { ?>
                                                        <p class="mb-0 fw-bold">Metode Pembayaran</p>
                                                        <p class="mb-0">
                                                            <?php
                                                            switch ($dataMid['payment_type']) {
                                                                case 'credit_card':
                                                                    echo "Credit Card<br>" . strtoupper($dataMid['bank']) . " " . ucfirst($dataMid['card_type']);
                                                                    break;
                                                                case 'echannel':
                                                                    switch ($dataMid['biller_code']) {
                                                                        case '70012':
                                                                            echo "Mandiri Bill<br>" . "Biller Code: " . $dataMid['biller_code'] . "<br>Bill Key: " . $dataMid['bill_key'];
                                                                            break;
                                                                        default:
                                                                            echo "EChannel<br>" . "Biller Code: " . $dataMid['biller_code'] . "<br>Bill Key: " . $dataMid['bill_key'];
                                                                            break;
                                                                    }
                                                                    break;
                                                                case 'bank_transfer':
                                                                    if (isset($dataMid['va_numbers']))
                                                                        echo strtoupper($dataMid['va_numbers'][0]['bank']) . " VA<br>" . $dataMid['va_numbers'][0]['va_number'];
                                                                    else if (isset($dataMid['permata_va_number']))
                                                                        echo "Bank Permata VA<br>" . $dataMid['permata_va_number'];
                                                                    else if (isset($dataMid['bca_va_number']))
                                                                        echo "BCA VA<br>" . $dataMid['bca_va_number'];
                                                                    break;
                                                                case 'rekening':
                                                                    echo strtoupper($dataMid['va_numbers'][0]['bank']) . " No. Rekening<br>" . $dataMid['va_numbers'][0]['va_number'] . '<br>a.n. Catur Bhakti Mandiri';
                                                                    break;
                                                                case 'gopay':
                                                                    echo 'Gopay';
                                                                    break;
                                                                case 'shopeepay':
                                                                    echo 'Shopeepay';
                                                                    break;
                                                                case 'qris':
                                                                    echo 'Qris';
                                                                    break;
                                                                    // case 'gopay':
                                                                    //     echo 'Qris<br><a href="/qris/' . $dataMid['order_id'] . '-' . $dataMid['gross_amount'] . '" style="color: var(--hijau); cursor:pointer;" class="link-offset-2 link-underline-opacity-25 link-underline-opacity-100-hover fw-bold">Lihar barcode</a>';
                                                                    //     break;
                                                                    // case 'qris':
                                                                    //     echo 'Qris<br><a href="/qris/' . $dataMid['order_id'] . '-' . $dataMid['gross_amount'] . '" style="color: var(--hijau); cursor:pointer;" class="link-offset-2 link-underline-opacity-25 link-underline-opacity-100-hover fw-bold">Lihar barcode</a>';
                                                                    //     break;
                                                                default:
                                                                    echo $dataMid['payment_type'];
                                                                    break;
                                                            }
                                                            ?></p>
                                                    <?php } else if ($item_transaksi['status'] == "Kadaluarsa" || $item_transaksi['status'] == "Ditolak" || $item_transaksi['status'] == "Gagal" || $item_transaksi['status'] == "Refund" || $item_transaksi['status'] == "Dibatalkan") { ?>

                                                    <?php } else { ?>
                                                        <p class="mb-0"><b>Nomor Resi : </b><?= $item_transaksi['resi']; ?>
                                                            <?php if ($item_transaksi['status'] != "Proses") { ?>
                                                                <a class="btn" onclick="copyresi('<?= $item_transaksi['resi'] ?>')"><i class="material-icons">content_copy</i></a>
                                                            <?php } ?>
                                                        </p>
                                                        <?php if ($item_transaksi['status'] != "Proses") { ?>
                                                            <!-- <a class="btn btn-primary1" href="/tracking/<?= $item_transaksi['kurir'] == 'dakota' ? "da" : "ro" ?>/<?= $item_transaksi['resi'] ?>">Tracking
                                                                Nomor Resi</a> -->
                                                            <a class="btn btn-primary1" href="https://indahonline.com/tracking/cek-resi" target="_blank">Tracking</a>
                                                        <?php } ?>
                                                    <?php } ?>
                                                </div>
                                                <div class="w-100 d-flex flex-column align-items-end gap-1">
                                                    <a href="/invoice/<?= $item_transaksi['id_midtrans']; ?>" class="btn btn-primary1">Invoice</a>
                                                    <?php if (in_array($item_transaksi['status'], ['Dikirim', 'Selesai'])) { ?>
                                                        <?php if ($returAktif) { ?>
                                                            <a href="/retur/order/<?= $item_transaksi['id_midtrans']; ?>" class="btn btn-outline-warning">
                                                                <?= (($returAktif['status'] ?? '') === 'Menunggu Sinkron Sistem') ? 'Kirim Ulang Retur' : 'Detail Retur'; ?>
                                                            </a>
                                                        <?php } else { ?>
                                                            <a href="/retur/order/<?= $item_transaksi['id_midtrans']; ?>" class="btn btn-outline-danger">Ajukan Retur</a>
                                                        <?php } ?>
                                                    <?php } ?>
                                                </div>
                                            </div>
                                            <?php if ($returAktif) { ?>
                                                <div class="border rounded p-3 mb-3" style="background:#fff8e8;">
                                                    <div class="d-flex justify-content-between align-items-start gap-2 flex-wrap">
                                                        <div>
                                                            <p class="fw-bold mb-1">Informasi Retur</p>
                                                            <span class="badge rounded-pill <?= $returBadgeClass($returAktif['status'] ?? ''); ?>"><?= esc($returAktif['status']); ?></span>
                                                            <?php if (!empty($returLuna['return_number'])) { ?>
                                                                <span class="badge rounded-pill text-bg-light border">No Retur: <?= esc($returLuna['return_number']); ?></span>
                                                            <?php } ?>
                                                        </div>
                                                        <small class="text-secondary text-end">
                                                            Diajukan: <?= !empty($returAktif['created_at']) ? date('d/m/Y H:i', strtotime($returAktif['created_at'])) : '-'; ?><br>
                                                            Update: <?= !empty($returAktif['updated_at']) ? date('d/m/Y H:i', strtotime($returAktif['updated_at'])) : '-'; ?>
                                                        </small>
                                                    </div>
                                                    <hr>
                                                    <p class="mb-1"><b>Solusi diminta:</b> <?= esc($returSolutionLabel($returAktif['solusi'] ?? '')); ?></p>
                                                    <p class="mb-2"><b>Alasan:</b> <?= esc($returAktif['alasan'] ?? '-'); ?></p>
                                                    <?php if (!empty($returLuna['note'])) { ?>
                                                        <p class="mb-2"><b>Catatan admin:</b> <?= esc($returLuna['note']); ?></p>
                                                    <?php } ?>
                                                    <?php if ($returItems) { ?>
                                                        <p class="fw-bold mb-1">Produk diajukan retur</p>
                                                        <ul class="mb-2">
                                                            <?php foreach ($returItems as $returItem) { ?>
                                                                <li><?= esc($returItem['name'] ?? '-'); ?> &times; <?= (int)($returItem['qty_requested'] ?? $returItem['quantity'] ?? 0); ?></li>
                                                            <?php } ?>
                                                        </ul>
                                                    <?php } ?>
                                                    <?php if ($returBukti) { ?>
                                                        <div class="d-flex gap-2 flex-wrap">
                                                            <?php foreach (array_slice($returBukti, 0, 5) as $foto) { ?>
                                                                <a href="<?= esc($foto); ?>" target="_blank" class="btn btn-sm btn-outline-secondary">Lihat bukti</a>
                                                            <?php } ?>
                                                        </div>
                                                    <?php } ?>
                                                </div>
                                            <?php } ?>
                                            <a href="/order/<?= $item_transaksi['id_midtrans']; ?>" style="color: var(--hijau)" class="fw-bold link-underline link-underline-opacity-100">Lihat halaman pesanan</a>
                                        </div>
                                    </div>
                                </div>
                            <?php }
                        } else {
                            if (session()->get('email') != 'tamu') { ?>
                                <p>Opss, belum ada transaksi</p>
                            <?php } else { ?>
                                <p>Riwayat transaksi dapat berfungsi ketika Anda login sebagai member</p>
                        <?php }
                        } ?>
                    </div>
                </div>
            </main>
        </div>
    </div>
</div>
<script>
    function copyresi(resi) {
        console.log(resi);
        navigator.clipboard.writeText(resi);
    }
</script>
<?= $this->endSection(); ?>
