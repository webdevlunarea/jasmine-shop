<?= $this->extend("layout/template"); ?>
<?= $this->section("content"); ?>
<?php
$orderStatus = (string)($pemesanan['status'] ?? 'Kadaluarsa');
$orderComplete = $orderStatus === 'Selesai';
$orderStage = 0;
$orderTitle = $orderStatus === 'Dibatalkan' ? 'Pesanan dibatalkan' : 'Waktu pembayaran berakhir';
$orderDescription = $orderStatus === 'Dibatalkan' ? 'Pesanan ini sudah dibatalkan. Silakan pilih produk kembali jika Anda ingin membuat pesanan baru.' : 'Batas waktu pembayaran untuk pesanan ini telah berakhir. Silakan buat pesanan baru untuk melanjutkan belanja.';
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

        <p class="order-link-note">Simpan tautan halaman ini untuk memeriksa perkembangan pesanan Anda. Jangan bagikan tautan kepada orang lain.</p>
        <div class="baris-ke-kolom order-detail-grid mb-3">
            <div class="limapuluh-ke-seratus order-detail-section">
                <div class="d-flex justify-content-between mb-3 order-payment-summary">
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
                    <p class="m-0">Batas pembayaran</p>
                    <h5><?= $waktuExpire; ?></h5>
                </div>
                <span class="garis mb-3"></span>
                <div>
                    <p class="m-0">Status pesanan</p>
                    <h5 class="text-danger">Telah <?= esc($pemesanan['status']); ?></h5>
                </div>
            </div>
            <div class="limapuluh-ke-seratus order-detail-section">
                <h2 class="order-section-title">Rincian pesanan</h2>
                <?= view('partials/order_items', ['items' => $items]); ?>
            </div>
        </div>
        <div class="d-flex justify-content-center">
            <a class="btn btn-primary1" href="/all">Lihat Produk Kami</a>
        </div>
    </div>
</main>
<?= $this->endSection(); ?>