<?= $this->extend("layout/template"); ?>
<?= $this->section("content"); ?>
<div class="konten">
    <div class="container">
        <div class="d-flex justify-content-center align-items-center gap-2 my-5">
            <div class="d-flex flex-column justify-content-center align-items-center">
                <div style="padding: 1em; border-radius: 2em;" class="bg-danger"><i class="material-icons text-light">access_time</i></div>
                <p class="m-0 text-center fw-bold text-danger" style="line-height: 20px;">Kadaluarsa</p>
            </div>
            <div style="color: gray">--------</div>
            <div style="background-color: gray; padding: 1em; border-radius: 2em;"><i class="material-icons text-light">local_shipping</i></div>
            <div style="color: gray">--------</div>
            <div style="background-color: gray; padding: 1em; border-radius: 2em;"><i class="material-icons text-light">done</i></div>
        </div>
        <p class="my-auto text-secondary text-sm-start mb-4 limapuluh-ke-seratus">*Simpan URL halaman ini untuk melihat status pesanan. Atau dapat login sebagai member kami agar dapat melihat seluruh riwayat pesanan Anda.</p>
        <div class="baris-ke-kolom">
            <div class="limapuluh-ke-seratus">
                <div class="d-flex justify-content-between mb-3">
                    <div class="flex-grow-1">
                        <?php if ($dataMid['payment_type'] == 'bank_transfer') { ?>
                            <p class="m-0">Nomor Virtual Account</p>
                            <h5><?= strtoupper($bank); ?> <?= $va_number; ?></h5>
                        <?php } else if ($dataMid['payment_type'] == 'qris') { ?>
                            <p class="m-0">QR Code</p>
                            <img src="<?= $va_number; ?>" alt="" width="150px" height="150px">
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
                <div class="mb-5">
                    <p class="m-0">Sisa Waktu Pembayaran</p>
                    <h5 class="text-danger">Sudah Kadaluarsa</h5>
                </div>
                <a class="btn btn-primary1" href="/all">Lihat Produk Kami</a>
            </div>
            <div class="limapuluh-ke-seratus">
                <p class="fw-bold">Cara Pembayaran</p>
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
        </div>
    </div>
</div>
<?= $this->endSection(); ?>