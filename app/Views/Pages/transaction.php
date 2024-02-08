<?= $this->extend('layout/template'); ?>
<?= $this->section('content'); ?>
<div class="konten">
    <div class="container">
        <div class="baris-ke-kolom-reverse">
            <div style="width: 30%;" class="show-ke-hide">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item"><b>Transaksi</b></li>
                    <li class="list-group-item"><a class="list" href="/account">Profilku</a></li>
                    <li class="list-group-item"><a class="list" href="/cart">Keranjang</a></li>
                    <li class="list-group-item"><a class="list" href="/wishlist">Wishlist</a></li>
                    <li class="list-group-item"><a class="btn btn-outline-danger" href="/keluar">Keluar</a></li>
                </ul>
            </div>
            <div class="hide-ke-show-flex w-100 justify-content-center border-top pt-3 mt-2">
                <a class="btn btn-outline-danger" style="width: fit-content;" href="/keluar">Keluar</a>
            </div>
            <div style="flex: 1;">
                <div class="p-2">
                    <h3>Transaksi Pembayaran</h3>
                    <div class="accordion" id="accordionExample">
                        <?php
                        if ($cekEror) { ?>
                            <p>Terdapat kesalahan, mohon halaman dimuat ulang</p>
                            <?php } else {
                            if (count($transaksi) > 0) {
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
                                                            <?= json_decode($item_transaksi['data_mid'], true)['transaction_time']; ?></p>
                                                        <?php if ($item_transaksi['status'] == "Menunggu Pembayaran") { ?>
                                                            <p class="mb-0 text-secondary" style="font-size: 12px;">Kadaluarsa pada <?php
                                                                                                                                    $d = strtotime(json_decode($item_transaksi['data_mid'], true)['transaction_time']);
                                                                                                                                    $enddate = strtotime("+1 hour", $d);
                                                                                                                                    echo date("Y-m-d H:i:s", $enddate);
                                                                                                                                    ?>
                                                            </p>
                                                        <?php } ?>
                                                    </div>
                                                </div>
                                            </button>
                                        </h2>
                                        <div id="collapse<?= $index_transaksi; ?>" class="accordion-collapse collapse" data-bs-parent="#accordionExample">
                                            <div class="accordion-body">
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
                                                <div class="w-100 d-flex justify-content-between">
                                                    <div class="w-100">
                                                        <?php if ($item_transaksi['status'] == "Menunggu Pembayaran") { ?>
                                                            <p class="mb-0">
                                                                <b><?= ucfirst(str_replace('_', ' ', json_decode($item_transaksi['data_mid'], true)['payment_type'])); ?></b>
                                                            </p>
                                                            <p class="mb-0">
                                                                <?= json_decode($item_transaksi['data_mid'], true)['payment_type'] == "bank_transfer" ? strtoupper(json_decode($item_transaksi['data_mid'], true)['va_numbers'][0]['bank']) . " " . json_decode($item_transaksi['data_mid'], true)['va_numbers'][0]['va_number'] : "" ?>
                                                            </p>
                                                            <p class="mb-0">
                                                                <?= json_decode($item_transaksi['data_mid'], true)['payment_type'] == "echannel" ? "Biller Code: " . json_decode($item_transaksi['data_mid'], true)['biller_code'] . "<br>Bill Key: " . json_decode($item_transaksi['data_mid'], true)['bill_key'] : "" ?>
                                                            </p>
                                                            <p class="mb-0">
                                                                <?= json_decode($item_transaksi['data_mid'], true)['payment_type'] == "cstore" ? "Kode Bayar: " . json_decode($item_transaksi['data_mid'], true)['payment_code'] : "" ?>
                                                            </p>
                                                        <?php } else if ($item_transaksi['status'] == "Kadaluarsa" || $item_transaksi['status'] == "Ditolak" || $item_transaksi['status'] == "Gagal" || $item_transaksi['status'] == "Refund" || $item_transaksi['status'] == "Dibatalkan") { ?>

                                                        <?php } else { ?>
                                                            <p class="mb-0"><b>Nomor Resi : </b><?= $item_transaksi['resi']; ?>
                                                                <?php if ($item_transaksi['status'] != "Proses") { ?>
                                                                    <a class="btn" onclick="copyresi('<?= $item_transaksi['resi'] ?>')"><i class="material-icons">content_copy</i></a>
                                                                <?php } ?>
                                                            </p>
                                                            <?php if ($item_transaksi['status'] != "Proses") { ?>
                                                                <a class="btn btn-primary1" href="/tracking/<?= $item_transaksi['kurir'] == 'dakota' ? "da" : "ro" ?>/<?= $item_transaksi['resi'] ?>">Tracking
                                                                    Nomor Resi</a>
                                                            <?php } ?>
                                                        <?php } ?>
                                                    </div>
                                                    <div class="w-100 d-flex flex-column align-items-end">
                                                        <a href="/invoice/<?= $item_transaksi['id_midtrans']; ?>" class="btn btn-primary1">Invoice</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php }
                            } else { ?>
                                <p>Opss, belum ada transaksi
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