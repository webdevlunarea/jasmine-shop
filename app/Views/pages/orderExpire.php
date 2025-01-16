<?= $this->extend("layout/template"); ?>
<?= $this->section("content"); ?>
<div class="konten">
    <div class="container">
        <div class="d-flex justify-content-center align-items-center gap-2 my-5">
            <div class="d-flex flex-column justify-content-center align-items-center">
                <div style="padding: 1em; border-radius: 2em;" class="bg-danger"><i class="material-icons text-light">access_time</i></div>
                <p class="m-0 text-center fw-bold text-danger" style="line-height: 20px;"><?= ucfirst($pemesanan['status']); ?></p>
            </div>
            <div style="color: gray">--------</div>
            <div style="background-color: gray; padding: 1em; border-radius: 2em;"><i class="material-icons text-light">local_shipping</i></div>
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
        <div class="baris-ke-kolom mb-3">
            <div class="limapuluh-ke-seratus">
                <div class="d-flex justify-content-between mb-3">
                    <div class="flex-grow-1">
                        <p class="m-0 d-block">Metode Pembayaran</p>
                        <?php if ($dataMid['payment_type'] == 'bank_transfer' || $dataMid['payment_type'] == 'echannel' || $dataMid['payment_type'] == 'rekening') { ?>
                            <h5><?= strtoupper($bank); ?></h5>
                        <?php } else if ($dataMid['payment_type'] == 'qris') { ?>
                            <h5>Qris</h5>
                        <?php } else if ($dataMid['payment_type'] == 'gopay') { ?>
                            <h5>Gopay</h5>
                        <?php } else if ($dataMid['payment_type'] == 'shopeepay') { ?>
                            <h5>ShopeePay</h5>
                        <?php } ?>
                    </div>
                    <div class="flex-grow-1">
                        <p class="m-0">Nominal</p>
                        <h5>Rp <?= number_format($dataMid['gross_amount'], 0, ",", "."); ?></h5>
                    </div>
                </div>
                <div class="mb-3">
                    <p class="m-0">Waktu Kadaluarsa</p>
                    <h5><?= $waktuExpire; ?></h5>
                </div>
                <span class="garis mb-3"></span>
                <div>
                    <p class="m-0">Sisa Waktu Pembayaran</p>
                    <h5 class="text-danger">Telah <?= $pemesanan['status']; ?></h5>
                </div>
            </div>
            <div class="limapuluh-ke-seratus">
                <p class="fw-bold">Produk yang dibeli</p>
                <div>
                    <div class="d-flex mb-2">
                        <div style="flex: 3; color: gray;">
                            <p class="m-0">Nama</p>
                        </div>
                        <div style="flex: 1; color: gray;">
                            <p class="m-0">Jumlah</p>
                        </div>
                        <div style="flex: 2; color: gray;">
                            <p class="m-0">Harga</p>
                        </div>
                    </div>
                    <?php foreach ($items as $i) { ?>
                        <div class="d-flex">
                            <div style="flex: 3;">
                                <p class="m-0"><?= $i['name']; ?></p>
                            </div>
                            <div style="flex: 1;">
                                <p class="m-0"><?= $i['quantity']; ?></p>
                            </div>
                            <div style="flex: 2;">
                                <p class="m-0">Rp <?= number_format($i['value'], 0, ",", "."); ?></p>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
        <div>
            <p class="fw-bold text-center">Cara Pembayaran</p>
            <div class="accordion accordion-flush" id="accordionFlushExample">
                <?php foreach ($caraPembayaran as $ind_c => $c) { ?>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse<?= $ind_c ?>" aria-expanded="false" aria-controls="flush-collapse1">
                                <?= $c['nama']; ?>
                            </button>
                        </h2>
                        <div id="flush-collapse<?= $ind_c ?>" class="accordion-collapse collapse" data-bs-parent="#accordionFlushExample">
                            <div class="accordion-body">
                                <p class="mb-0"><?= $c['isi']; ?></p>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
        <div class="d-flex justify-content-center">
            <a class="btn btn-primary1" href="/all">Lihat Produk Kami</a>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>