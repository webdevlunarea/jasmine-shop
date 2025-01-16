<?= $this->extend("layout/template"); ?>
<?= $this->section("content"); ?>
<div class="konten">
    <div class="container">
        <div class="d-flex justify-content-center align-items-center gap-2 my-5">
            <div style="background-color: var(--hijau); padding: 1em; border-radius: 2em;"><i class="material-icons text-light">access_time</i></div>
            <div style="color: var(--hijau)">--------</div>
            <div class="d-flex flex-column justify-content-center align-items-center">
                <div style="background-color: var(--hijau); padding: 1em; border-radius: 2em;"><i class="material-icons text-light">local_shipping</i></div>
                <p class="m-0 text-center fw-bold" style="line-height: 20px; color: var(--hijau)"><?= $kurir == 'kosong' ? 'Diproses' : 'Dikirim'; ?></p>
            </div>
            <div style="color: gray">--------</div>
            <div style="background-color: gray; padding: 1em; border-radius: 2em;"><i class="material-icons text-light">done</i></div>
        </div>
    </div>
    <div class="py-1 text-light w-100 text-center mb-5" style="background-color: var(--hijau);">ID
        Pesanan :
        <b><?= $pemesanan['id_midtrans']; ?></b>
    </div>
    <div class="container">
        <p class="my-auto text-secondary text-sm-start mb-4 limapuluh-ke-seratus">*Simpan URL halaman ini untuk melihat status pesanan. Atau dapat login sebagai member kami agar dapat melihat seluruh riwayat pesanan Anda.</p>
        <div class="baris-ke-kolom">
            <div class="limapuluh-ke-seratus">
                <div class="d-flex justify-content-between mb-3">
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
                <div class="flex-grow-1 mb-4">
                    <?php if ($kurir == 'kosong') { ?>
                        <div>
                            <p class="m-0">Barang masih kami proses untuk pengiriman</p>
                            <p class="m-0 text-secondary">*Jika pemesanan lebih dari jam 12:00 akan kami proses di esok harinya</p>
                        </div>
                    <?php } else { ?>
                        <div class="d-flex justify-content-between">
                            <div class="flex-grow-1">
                                <img src="/img/kurir/<?= strtolower(explode(" ", $kurir)[0]); ?>.webp" class="mb-1" style="height:40px"></img>
                                <h5 class="m-0"><?= explode("-", $kurir)[0]; ?></h5>
                                <p class="m-0"><?= explode("-", $kurir)[1]; ?></p>
                            </div>
                            <div class="flex-grow-1">
                                <p class="m-0">Resi</p>
                                <h5 class="mb-1"><?= $pemesanan['resi']; ?></h5>
                                <?php if (strtolower(explode(" ", $kurir)[0]) == 'indah') { ?>
                                    <a href="https://indahonline.com/tracking/cek-resi" class="btn btn-primary1">Tracking</a>
                                <?php } ?>
                            </div>
                        </div>
                    <?php } ?>
                </div>
                <div>
                    <p class="m-0 fw-bold">Alamat</p>
                    <p class="m-0"><?= $pemesanan['alamat_pen']; ?></h5>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>