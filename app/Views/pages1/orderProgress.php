<?= $this->extend("layout/template"); ?>
<?= $this->section("content"); ?>
<div class="konten">
    <div class="container">
        <div class="d-flex justify-content-center align-items-center gap-2 my-5">
            <div class="d-flex flex-column justify-content-center align-items-center">
                <div style="background-color: var(--hijau); padding: 1em; border-radius: 2em;"><i class="material-icons text-light">access_time</i></div>
                <p class="m-0 text-center fw-bold" style="line-height: 20px; color: var(--hijau)">Menunggu<br>Pembayaran</p>
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
                        <?php if ($dataMid['payment_type'] == 'bank_transfer' || $dataMid['payment_type'] == 'echannel') { ?>
                            <p class="m-0">Nomor Virtual Account</p>
                            <h5><?= strtoupper($bank); ?> <?= $va_number; ?></h5>
                        <?php } else if ($dataMid['payment_type'] == 'qris') { ?>
                            <p class="m-0">QR Code</p>
                            <img src="<?= $va_number; ?>" alt="" width="150px" height="150px">
                        <?php } else if ($dataMid['payment_type'] == 'gopay') { ?>
                            <div class="d-flex gap-1">
                                <a class="btn btn-primary1" href="<?= $va_number[0]['url']; ?>">QR Code</a>
                                <a class="btn btn-primary1" href="<?= $va_number[1]['url']; ?>">Buka Aplikasi</a>
                            </div>
                        <?php } else if ($dataMid['payment_type'] == 'shopeepay') { ?>
                            <div class="d-flex gap-1">
                                <a class="btn btn-primary1" href="<?= $va_number[0]['url']; ?>">Buka Aplikasi</a>
                            </div>
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
                    <h5 id="waktu" style="color: var(--hijau);"><?= $waktu; ?></h5>
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
        <div class="d-flex justify-content-center gap-1">
            <a class="btn btn-primary1" href="/order/<?= $dataMid['order_id']; ?>">
                <p class="m-0">Saya Sudah Bayar</p>
            </a>
            <button class="btn btn-danger" onclick="triggerToast('Anda yakin akan membatalkan pesanan <?= $dataMid['order_id']; ?>?','/cancelorder/<?= $dataMid['order_id']; ?>')">
                <p class="m-0">Batalkan Pesanan</p>
            </button>
        </div>
    </div>
</div>
<script>
    const expiryTimeElm = document.getElementById("waktu");
    const de = new Date('<?= $dataMid['expiry_time']; ?>');
    const expireTime = de.getTime();
    const dc = new Date();

    setInterval(() => {
        const currTime = new Date().getTime();
        let dselisih = expireTime - currTime;

        const hours = String(Math.floor(dselisih / (1000 * 60 * 60))).padStart(2, '0');
        dselisih %= (1000 * 60 * 60);
        const minutes = String(Math.floor(dselisih / (1000 * 60))).padStart(2, '0');
        dselisih %= (1000 * 60);
        const seconds = String(Math.floor(dselisih / 1000)).padStart(2, '0');

        expiryTimeElm.innerHTML = `${hours}: ${minutes}: ${seconds}`;
        if (Number(hours) < 0 && Number(minutes) < 0 && Number(seconds) < 0) {
            window.location.reload();
        }
    }, 1000);

    function copytext(teks) {
        navigator.clipboard.writeText(teks);
    }
</script>
<?= $this->endSection(); ?>