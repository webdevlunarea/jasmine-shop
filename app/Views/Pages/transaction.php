<?= $this->extend('layout/template'); ?>
<?= $this->section('content'); ?>
<div class="konten">
    <div class="container">
        <div class="baris-ke-kolom-reverse">
            <div style="width: 30%;" class="show-ke-hide">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item"><b>Transaksi</b></li>
                    <li class="list-group-item"><a class="list" href="/account">Profilku</a></li>
                    <li class="list-group-item"><a class="list" href="/cart">Pesananku</a></li>
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
                    <!-- <div class="d-flex flex-column gap-2">
                        <div class="transaksi-card">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h5 class="mb-0">Rp 400.000</h5>
                                    <p class="mb-0" style="font-size: 12px;">ID Pesanan: 1231245167</p>
                                    <span class="badge rounded-pill text-bg-primary">Pending</span>
                                </div>
                                <div class="d-flex flex-column align-items-end justify-content-between">
                                    <p class="mb-0 text-secondary" style="font-size: 12px;">2024-01-12 09:54:52</p>
                                    <p class="mb-0 lihat-detail">Lihat detail</p>
                                </div>
                            </div>
                        </div>
                    </div> -->
                    <div class="accordion" id="accordionExample">
                        <?php foreach ($transaksi as $index_transaksi => $item_transaksi) { ?>
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $index_transaksi; ?>" aria-expanded="false" aria-controls="collapse<?= $index_transaksi; ?>">
                                        <div class="d-flex justify-content-between" style="width: 100%;">
                                            <div>
                                                <h5 class="mb-0">Rp <?= number_format((int)$item_transaksi['data']['gross_amount'], 0, ",", "."); ?></h5>
                                                <p class="mb-1" style="font-size: 12px;">ID Pesanan: <?= $item_transaksi['data']['order_id']; ?></p>
                                                <span class="badge rounded-pill text-bg-primary"><?= ucfirst($item_transaksi['data']['transaction_status']); ?></span>
                                            </div>
                                            <div class="d-flex flex-column justify-content-end">
                                                <p class="mb-0 text-secondary" style="font-size: 12px;"><?= $item_transaksi['data']['transaction_time']; ?></p>
                                            </div>
                                        </div>
                                    </button>
                                </h2>
                                <div id="collapse<?= $index_transaksi; ?>" class="accordion-collapse collapse" data-bs-parent="#accordionExample">
                                    <div class="accordion-body">
                                        <p><b>Items</b></p>
                                        <div>
                                            <table class="table table-borderless">
                                                <tbody>
                                                    <?php foreach ($item_transaksi['items'] as $item) { ?>
                                                        <tr>
                                                            <td><?= $item['name']; ?></td>
                                                            <td><?= $item['quantity'] ?></td>
                                                            <td class="text-end">Rp
                                                                <?= number_format($item['value'], 0, ",", "."); ?>
                                                            </td>
                                                        </tr>
                                                    <?php } ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                        <!-- <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse0" aria-expanded="false" aria-controls="collapse0">
                                    Accordion Item 00000
                                </button>
                            </h2>
                            <div id="collapse0" class="accordion-collapse collapse" data-bs-parent="#accordionExample">
                                <div class="accordion-body">
                                    <p>isi dari yang 00000</p>
                                </div>
                            </div>
                        </div> -->
                        <!-- <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                    Accordion Item #2
                                </button>
                            </h2>
                            <div id="collapseTwo" class="accordion-collapse collapse" data-bs-parent="#accordionExample">
                                <div class="accordion-body">
                                    <p>isi dari yang kedua</p>
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                    Accordion Item #3
                                </button>
                            </h2>
                            <div id="collapseThree" class="accordion-collapse collapse" data-bs-parent="#accordionExample">
                                <div class="accordion-body">
                                    <p>Ini isi dari yg ketiga</p>
                                </div>
                            </div>
                        </div> -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>