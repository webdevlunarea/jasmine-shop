<?= $this->extend("layout/template"); ?>
<?= $this->section("content"); ?>
<style>
    .tombol-copy {
        border: 1px solid gray;
        padding: 7px;
        border-radius: 10px;
        font-size: 13px;
        transition: 0.2s;
        cursor: pointer;
    }

    .tombol-copy.checked {
        font-weight: bold;
        color: var(--hijau);
        border: 1px solid var(--hijau);
        transition: 0.2s;
    }
</style>
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
                <div style="display:grid; grid-template-columns: repeat(2, 1fr); gap: 0.5em 2em;">
                    <div>
                        <?php if ($dataMid['payment_type'] == 'bank_transfer' || $dataMid['payment_type'] == 'echannel' || $dataMid['payment_type'] == 'rekening') { ?>
                            <p class="m-0"><?= $dataMid['payment_type'] == 'rekening' ? 'Nomor Rekening' : 'Nomor Virtual Account'; ?></p>
                            <div class="d-flex gap-2 align-items-center">
                                <h5 class="m-0"><?= strtoupper($bank); ?> <b><?= $va_number; ?></b></h5>
                                <i class="material-icons tombol-copy" onclick="copyVA(event)">content_copy</i>
                            </div>
                            <?php if ($dataMid['payment_type'] == 'rekening') { ?>
                                <p class="text-secondary">a.n. Catur Bhakti Mandiri</p>
                            <?php } ?>
                            <script>
                                function copyVA(e) {
                                    e.target.innerHTML = 'check';
                                    e.target.classList.add('checked');
                                    navigator.clipboard.writeText('<?= $va_number; ?>');
                                }
                            </script>
                        <?php } else if ($dataMid['payment_type'] == 'qris') { ?>
                            <p class="m-0">QR Code</p>
                            <img style="cursor: zoom-in;" onclick="openQR()" src="<?= $va_number; ?>" alt="" width="150px" height="150px">
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
                    <div>
                        <p class="m-0">Nominal</p>
                        <div class="d-flex gap-2 align-items-center">
                            <h5 class="m-0">Rp <?= number_format($dataMid['gross_amount'], 0, ",", "."); ?></h5>
                            <i class="material-icons tombol-copy" onclick="copyNominal(event)">content_copy</i>
                        </div>
                        <script>
                            function copyNominal(e) {
                                e.target.innerHTML = 'check';
                                e.target.classList.add('checked');
                                navigator.clipboard.writeText('<?= $dataMid['gross_amount']; ?>');
                            }
                        </script>
                    </div>
                    <div>
                        <p class="m-0">Waktu Kadaluarsa</p>
                        <h5><?= $waktuExpire; ?></h5>
                    </div>
                    <div>
                        <p class="m-0">Sisa Waktu Pembayaran</p>
                        <h5 id="waktu" style="color: var(--hijau);"><?= $waktu; ?></h5>
                    </div>
                </div>
                <?php if ($pemesanan['bukti_bayar']) { ?>
                    <hr>
                    <p class="m-0">*Sedang dalam proses pengecekan bukti pembayaran</p>
                <?php } else if ($dataMid['payment_type'] == 'rekening') { ?>
                    <hr>
                    <button class="btn btn-primary1" onclick="openUpload()">Upload bukti pembayaran</button>
                    <div id="modal-upload" class="d-none p-3 justify-content-center align-items-center" style="z-index: 12; background-color: rgba(0, 0, 0, 0.5); position:fixed; left: 0; top: 0; height: 100svh; width: 100vw;">
                        <div style="background-color: white;" class="px-4 py-3 rounded">
                            <div class="d-flex mb-3 gap-4 justify-content-between align-items-center">
                                <h5 class="m-0">Upload bukti pembayaran</h5>
                                <i style="cursor: pointer;" class="material-icons" onclick="closeUpload()">close</i>
                            </div>
                            <form action="/payorder/<?= $pemesanan['id_midtrans']; ?>" method="post" enctype="multipart/form-data">
                                <input id="bukti-bayar" type="file" name="buktiBayar" class="d-none" required>
                                <div id="preview-bayar" class="d-none" style="width: 100%; max-width: 500px; max-height: 300px; overflow: auto;">
                                    <img src="https://images.unsplash.com/photo-1733320662296-ff70b879480e?q=80&w=1065&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" alt="" style="display:block;" class="w-100">
                                </div>
                                <label for="bukti-bayar" class="btn btn-outline-dark w-100 mt-2" id="btn-pilih-foto">Pilih foto</label>
                                <button id="btn-submit-bayar" type="submit" class="mt-1 btn btn-primary1 d-block w-100" disabled>Upload</button>
                            </form>
                        </div>
                    </div>
                    <script>
                        const modalUploadElm = document.getElementById('modal-upload');
                        const buktiBayarElm = document.getElementById('bukti-bayar');
                        const previewBayarElm = document.getElementById('preview-bayar');
                        const btnSubmitBayarElm = document.getElementById('btn-submit-bayar');
                        const btnPilihFotoElm = document.getElementById('btn-pilih-foto');

                        function openUpload() {
                            modalUploadElm.classList.remove('d-none')
                            modalUploadElm.classList.add('d-flex')
                        }

                        function closeUpload() {
                            modalUploadElm.classList.add('d-none')
                            modalUploadElm.classList.remove('d-flex')
                        }

                        buktiBayarElm.addEventListener('change', (e) => {
                            console.log(e.target)
                            const file = e.target.files[0];
                            const blobFile = new Blob([file], {
                                type: file.type
                            });
                            var blobUrl = URL.createObjectURL(blobFile);
                            previewBayarElm.children[0].src = blobUrl;
                            previewBayarElm.classList.remove('d-none');
                            btnSubmitBayarElm.disabled = false
                            btnPilihFotoElm.innerHTML = 'Ganti foto'
                        })
                    </script>
                <?php } ?>
            </div>
            <div class="limapuluh-ke-seratus">
                <p class="fw-bold">Produk yang dibeli</p>
                <div>
                    <div class="d-flex mb-2">
                        <div style="flex: 3; color: gray;">
                            <p class="m-0">Nama</p>
                        </div>
                        <div style="flex: 1; color: gray;">
                            <p class="m-0 text-center">Jumlah</p>
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
                                <p class="m-0 text-center"><?= $i['quantity']; ?></p>
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
    const isRekening = <?= $dataMid['payment_type'] == 'rekening' ? 'true' : 'false'; ?>;

    setInterval(() => {
        const currTime = new Date().getTime();
        let dselisih = expireTime - currTime;

        const hours = String(Math.floor(dselisih / (1000 * 60 * 60))).padStart(2, '0');
        dselisih %= (1000 * 60 * 60);
        const minutes = String(Math.floor(dselisih / (1000 * 60))).padStart(2, '0');
        dselisih %= (1000 * 60);
        const seconds = String(Math.floor(dselisih / 1000)).padStart(2, '0');

        expiryTimeElm.innerHTML = `${hours}:${minutes}:${seconds}`;
        if (Number(hours) < 0 && Number(minutes) < 0 && Number(seconds) < 0) {
            if (isRekening) {
                expiryTimeElm.innerHTML = `00:00:00`;
            } else {
                // window.location.reload();
            }
        }
    }, 1000);

    function copytext(teks) {
        navigator.clipboard.writeText(teks);
    }

    const socket = new WebSocket('<?= $wsUrl; ?>');
    socket.onopen = () => {
        console.log(`Socket berhasil terkoneksi <?= $wsUrl; ?>`)
    }
    socket.onmessage = (event) => {
        const datanya = JSON.parse(event.data);
        console.log(datanya)
        if (datanya.jenis == 'order' && datanya.id_order == '<?= $pemesanan['id_midtrans']; ?>') {
            window.location.reload();
        }
    }
</script>
<?php if ($dataMid['payment_type'] == 'qris') { ?>
    <style>
        .gambar-qr {
            width: auto;
            height: 100%;
            background-color: white;
        }

        @media (orientation: portrait) {
            .gambar-qr {
                width: 100%;
                height: auto;
            }
        }
    </style>
    <div id="modal-qr" style="z-index: 100;position: fixed; top: 0; left: 0; width: 100vw; height: 100svh; background-color: rgba(0,0,0,0.3);" class="d-flex justify-content-center align-items-center p-5">
        <div class="gambar-qr p-2 rounded">
            <img src="<?= $va_number; ?>" alt="QRCode" class="w-100 h-100" style="object-fit: cover;">
        </div>
    </div>
    <script>
        const modalQrElm = document.getElementById('modal-qr');
        modalQrElm.addEventListener('click', () => {
            modalQrElm.classList.remove('d-flex');
            modalQrElm.classList.add('d-none');
        });
        modalQrElm.children[0].addEventListener('click', (e) => {
            e.stopPropagation();
        })

        function openQR() {
            modalQrElm.classList.add('d-flex');
            modalQrElm.classList.remove('d-none');
        }
    </script>
<?php } ?>
<?= $this->endSection(); ?>