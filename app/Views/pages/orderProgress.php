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
<?php
$orderStatus = (string)($pemesanan['status'] ?? 'Menunggu Pembayaran');
$orderComplete = $orderStatus === 'Selesai';
$orderStage = 1;
$orderTitle = !empty($pemesanan['bukti_bayar']) ? 'Pembayaran sedang diverifikasi' : 'Menunggu pembayaran';
$orderDescription = !empty($pemesanan['bukti_bayar']) ? 'Bukti pembayaran sudah diterima. Tim kami akan memeriksanya sebelum pesanan diproses.' : 'Selesaikan pembayaran sesuai rincian berikut agar pesanan dapat diproses.';
?>
<main class="konten order-page-shell buyer-order-page" aria-labelledby="order-title">
    <div class="container">
        <header class="order-heading">
            <a class="order-back-link" href="<?= session('isLogin') && session('email') !== 'tamu' ? '/transaction' : '/all'; ?>"><span aria-hidden="true">←</span> <?= session('isLogin') && session('email') !== 'tamu' ? 'Riwayat pesanan' : 'Lihat produk'; ?></a>
            <p class="order-eyebrow">Pesanan Lunarea</p>
            <h1 id="order-title"><?= esc($orderTitle); ?></h1>
            <p class="order-intro"><?= esc($orderDescription); ?></p>
            <div class="order-reference"><span>Nomor pesanan</span><strong><?= esc($pemesanan['id_midtrans']); ?></strong></div>
        </header>
        <ol class="order-steps" aria-label="Progres pesanan">
            <?php foreach (['Pembayaran', 'Diproses', 'Dikirim', 'Selesai'] as $stepIndex => $stepLabel) { ?>
            <li class="<?= $stepIndex + 1 <= $orderStage ? 'is-reached' : ''; ?>" <?= $stepIndex + 1 === $orderStage ? 'aria-current="step"' : ''; ?>><span class="order-step-number" aria-hidden="true"><?= $stepIndex + 1; ?></span><span><?= esc($stepLabel); ?></span></li>
            <?php } ?>
        </ol>
        <p class="order-link-note">Simpan tautan halaman ini untuk memeriksa perkembangan pesanan Anda. Jangan bagikan tautan kepada orang lain.</p>
        <div class="baris-ke-kolom order-detail-grid mb-3">
            <div class="limapuluh-ke-seratus order-detail-section">
                <div class="order-payment-summary" style="display:grid; grid-template-columns: repeat(2, 1fr); gap: 0.5em 2em;">
                    <div>
                        <?php if ($dataMid['payment_type'] == 'bank_transfer' || $dataMid['payment_type'] == 'echannel' || $dataMid['payment_type'] == 'rekening') { ?>
                            <p class="m-0"><?= $dataMid['payment_type'] == 'rekening' ? 'Nomor Rekening' : 'Nomor Virtual Account'; ?></p>
                            <div class="d-flex gap-2 align-items-center">
                                <h5 class="m-0"><?= strtoupper($bank); ?> <b><?= $va_number; ?></b></h5>
                                <button type="button" class="tombol-copy" data-order-copy="<?= esc($va_number, 'attr'); ?>" aria-label="Salin nomor pembayaran">Salin</button>
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
                            <button type="button" class="order-qr-trigger" onclick="openQR()" aria-label="Perbesar kode QR pembayaran"><img src="<?= esc($va_number, 'attr'); ?>" alt="Kode QR pembayaran" width="150" height="150"></button>
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
                            <button type="button" class="tombol-copy" data-order-copy="<?= esc((string)$dataMid['gross_amount'], 'attr'); ?>" aria-label="Salin nominal pembayaran">Salin</button>
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
                        <p class="m-0">Batas pembayaran</p>
                        <h5><?= $waktuExpire; ?></h5>
                    </div>
                    <div>
                        <p class="m-0">Sisa waktu pembayaran</p>
                        <h5 id="waktu" style="color: var(--hijau);"><?= $waktu; ?></h5>
                    </div>
                </div>
                <?php if ($pemesanan['bukti_bayar']) { ?>
                    <hr>
                    <p class="m-0">*Sedang dalam proses pengecekan bukti pembayaran</p>
                <?php } else if ($dataMid['payment_type'] == 'rekening') { ?>
                    <hr>
                    <button class="btn btn-primary1" onclick="openUpload()">Upload bukti pembayaran</button>
                    <div id="modal-upload" role="dialog" aria-modal="true" aria-labelledby="order-upload-title" class="d-none p-3 justify-content-center align-items-center" style="z-index: 12; background-color: rgba(0, 0, 0, 0.5); position:fixed; left: 0; top: 0; height: 100svh; width: 100vw;">
                        <div style="background-color: white;" class="px-4 py-3 rounded">
                            <div class="d-flex mb-3 gap-4 justify-content-between align-items-center">
                                <h2 id="order-upload-title" class="order-section-title m-0">Unggah bukti pembayaran</h2>
                                <button type="button" class="order-dialog-close" onclick="closeUpload()" aria-label="Tutup unggah bukti">×</button>
                            </div>
                            <form action="/payorder/<?= $pemesanan['id_midtrans']; ?>" method="post" enctype="multipart/form-data">
                                <?= csrf_field(); ?>
                                <input id="bukti-bayar" type="file" name="buktiBayar" class="order-file-input" accept="image/*" required>
                                <div id="preview-bayar" class="d-none" style="width: 100%; max-width: 500px; max-height: 300px; overflow: auto;">
                                    <img alt="Pratinjau bukti pembayaran" style="display:block;" class="w-100">
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
                            const file = e.target.files[0];
                            if (!file) {
                                previewBayarElm.classList.add('d-none');
                                btnSubmitBayarElm.disabled = true;
                                return;
                            }
                            if (previewBayarElm.children[0].src.startsWith('blob:')) URL.revokeObjectURL(previewBayarElm.children[0].src);
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
            <div class="limapuluh-ke-seratus order-detail-section">
                <h2 class="order-section-title">Rincian pesanan</h2>
                <?= view('partials/order_items', ['items' => $items]); ?>
            </div>
        </div>
        <?php if (is_array($caraPembayaran ?? null) && $caraPembayaran) { ?>
        <div>
            <h2 class="order-section-title">Cara pembayaran</h2>
            <div class="accordion accordion-flush" id="accordionFlushExample">
                <?php foreach ((is_array($caraPembayaran ?? null) ? $caraPembayaran : []) as $ind_c => $c) { ?>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse<?= $ind_c ?>" aria-expanded="false" aria-controls="flush-collapse<?= $ind_c ?>">
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
        <?php } ?>
        <div class="order-actions">
            <a class="btn btn-primary1" href="/order/<?= $dataMid['order_id']; ?>">
                <p class="m-0">Saya Sudah Bayar</p>
            </a>
            <button class="btn btn-danger" onclick="triggerToast('Anda yakin akan membatalkan pesanan <?= $dataMid['order_id']; ?>?','/cancelorder/<?= $dataMid['order_id']; ?>')">
                <p class="m-0">Batalkan Pesanan</p>
            </button>
        </div>
    </div>
</main>
<script>
    const expiryTimeElm = document.getElementById("waktu");
    const de = new Date('<?= $dataMid['expiry_time']; ?>');
    const expireTime = de.getTime();
    const dc = new Date();
    const isRekening = <?= $dataMid['payment_type'] == 'rekening' ? 'true' : 'false'; ?>;

    setInterval(() => {
        const currTime = new Date().getTime();
        let dselisih = Number.isFinite(expireTime) ? Math.max(0, expireTime - currTime) : 0;

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
    <div id="modal-qr" role="dialog" aria-modal="true" aria-label="Kode QR pembayaran" style="z-index: 100;position: fixed; top: 0; left: 0; width: 100vw; height: 100svh; background-color: rgba(0,0,0,0.3);" class="d-none justify-content-center align-items-center p-5">
        <div class="gambar-qr p-2 rounded">
            <button type="button" class="order-dialog-close" aria-label="Tutup kode QR" onclick="closeQR()">×</button>
            <img src="<?= $va_number; ?>" alt="QRCode" class="w-100 h-100" style="object-fit: contain;">
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

        function closeQR() {
            modalQrElm.classList.remove('d-flex');
            modalQrElm.classList.add('d-none');
        }
        function openQR() {
            modalQrElm.classList.add('d-flex');
            modalQrElm.classList.remove('d-none');
        }
    </script>
<?php } ?>
<?= $this->endSection(); ?>