<?= $this->extend('layout/template'); ?>
<?= $this->section('content'); ?>
<div class="konten">
    <div class="container">
        <div class="baris-ke-kolom-reverse">
            <div style="width: 30%;" class="show-ke-hide">
                <ul class="list-group list-group-flush">
                    <?php if (session()->get('email') != 'tamu') { ?>
                        <li class="list-group-item"><a class="list" href="/account">Profilku</a></li>
                    <?php } ?>
                    <?php if (session()->get('role') == '0') { ?>
                        <li class="list-group-item"><b>Transaksi</b></li>
                        <li class="list-group-item"><a class="list" href="/point">Luna poin</a></li>
                        <li class="list-group-item"><a class="list" href="/voucher">Voucher</a></li>
                    <?php } ?>
                    <li class="list-group-item"><a class="btn btn-outline-danger" href="/keluar">Keluar</a></li>
                </ul>
            </div>
            <!-- <div class="hide-ke-show-flex w-100 justify-content-center border-top pt-3 mt-2">
                <a class="btn btn-outline-danger" style="width: fit-content;" href="/keluar">Keluar</a>
            </div> -->
            <div style="flex: 1;">
                <div class="p-2">
                    <h3>Transaksi Pembayaran</h3>
                    <div class="accordion" id="accordionExample">
                        <?php if (count($transaksi) > 0) {
                            foreach ($transaksi as $index_transaksi => $item_transaksi) { ?>
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
                                                                                    ?>"><?= ucfirst($item_transaksi['status']); ?></span>
                                                </div>
                                                <div class="d-flex flex-column justify-content-end">
                                                    <p class="mb-0 text-secondary" style="font-size: 12px;">
                                                        <?= date("d/m/Y H:i:s", strtotime(json_decode($item_transaksi['data_mid'], true)['transaction_time'])); ?></p>
                                                    <?php if ($item_transaksi['status'] == "Menunggu Pembayaran") { ?>
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
                                                    <?php if ($item_transaksi['status'] == "Menunggu Pembayaran") { ?>
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
                                                                    //     echo 'Qris<br><a href="/qris/' . $dataMid['order_id'] . '-' . $dataMid['gross_amount'] . '" style="color: #1db954; cursor:pointer;" class="link-offset-2 link-underline-opacity-25 link-underline-opacity-100-hover fw-bold">Lihar barcode</a>';
                                                                    //     break;
                                                                    // case 'qris':
                                                                    //     echo 'Qris<br><a href="/qris/' . $dataMid['order_id'] . '-' . $dataMid['gross_amount'] . '" style="color: #1db954; cursor:pointer;" class="link-offset-2 link-underline-opacity-25 link-underline-opacity-100-hover fw-bold">Lihar barcode</a>';
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
                                                <div class="w-100 d-flex flex-column align-items-end">
                                                    <a href="/invoice/<?= $item_transaksi['id_midtrans']; ?>" class="btn btn-primary1">Invoice</a>
                                                </div>
                                            </div>
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
            </div>
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