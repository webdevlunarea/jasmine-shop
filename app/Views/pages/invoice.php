<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8" />
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport" />
    <title><?= $title; ?> | Lunarea Furniture</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4bw+/aepP/YC94hEpVNVgiZdgIC5+VKNBQNGCHeKRQN+PtmoHDEXuppvnDJzQIu9" crossorigin="anonymous" />
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
    <script src="https://unpkg.com/jspdf@latest/dist/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" />
    <!-- <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" /> -->
    <!-- icon google -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />

    <script src="https://kit.fontawesome.com/917733e7d4.js" crossorigin="anonymous"></script>

    <link rel="stylesheet" href="/css/style_pdf.css" />
</head>

<body>
    <div class="print">
        <?php if ($transaksi['status'] == 'Proses' || $transaksi['status'] == 'Dikirim' || $transaksi['status'] == 'Selesai') { ?>
            <div class="print-lunas">
                <p>L U N A S</p>
            </div>
        <?php } else if ($transaksi['status'] == 'Menunggu Pembayaran') {
        } else { ?>
            <div class="print-lunas">
                <p>G A G A L</p>
            </div>
        <?php } ?>
        <div class="d-flex justify-content-between mb-2">
            <img src="<?= base_url('img/Logo Lunarea Bg Terang ukuran kecil.webp'); ?>" style="width: 150em; height:fit-content;" />
            <div class="d-flex flex-column align-items-end">
                <p class="mb-0">INVOICE</p>
                <p class="mb-0"><?php
                                $d = strtotime($transaksi['data_mid']['transaction_time']);
                                echo date("dmY", $d)
                                ?>/CBM/<?= explode("-", $transaksi['id_midtrans'])[0]; ?></p>
            </div>
        </div>
        <div class="d-flex justify-content-between mb-2">
            <div class="w-100">
                <p class="fw-bold mb-0">DITERBITKAN ATAS NAMA</p>
                <p class="mb-0">Penjual : <b>Lunarea Furniture</b></p>
            </div>
            <div class="w-100">
                <p class="fw-bold mb-0">PENERIMA</p>
                <div class="w-100">
                    <div class="w-100 d-flex">
                        <div style="flex: 2.7">
                            <p class="mb-0">Pembeli</p>
                        </div>
                        <div style="flex: 0.4">
                            <p class="text-center mb-0">:</p>
                        </div>
                        <div style="flex: 5">
                            <p class="mb-0"><?= $transaksi['nama_pen']; ?></p>
                        </div>
                    </div>
                    <div class="w-100 d-flex">
                        <div style="flex: 2.7">
                            <p class="mb-0">Tanggal Pembelian</p>
                        </div>
                        <div style="flex: 0.4">
                            <p class="text-center mb-0">:</p>
                        </div>
                        <div style="flex: 5">
                            <p class="mb-0"><?php
                                            $d = strtotime($transaksi['data_mid']['transaction_time']);
                                            echo date("d", $d) . " " . $bulan[(int)date("m", $d) - 1] . " " . date("Y", $d)
                                            ?></p>
                        </div>
                    </div>
                    <div class="w-100 d-flex">
                        <div style="flex: 2.7">
                            <p class="mb-0">Alamat Pengiriman</p>
                        </div>
                        <div style="flex: 0.4">
                            <p class="text-center mb-0">:</p>
                        </div>
                        <div style="flex: 5">
                            <p class="mb-0">
                                <?= $transaksi['alamat_pen']; ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Container List Barang -->
        <div class="w-100 border-bottom border-dark pb-2">
            <div class="w-100 d-flex border-bottom border-top border-dark py-2">
                <div style="flex: 2.9">
                    <p class="mb-0 fw-bold">INFO PRODUK</p>
                </div>
                <div style="flex: 1">
                    <p class="text-center mb-0 fw-bold">KUANTITAS</p>
                </div>
                <div style="flex: 1">
                    <p class="text-center mb-0 fw-bold">HARGA SATUAN</p>
                </div>
                <div style="flex: 1">
                    <p class="text-center mb-0 fw-bold">JUMLAH</p>
                </div>
            </div>
            <?php
            $totalHarga = 0;
            foreach ($transaksi['items'] as $item) {
                $totalHarga += $item['value'] * $item['quantity'];
            ?>
                <div class="w-100 d-flex">
                    <div style="flex: 3">
                        <p class="mb-0"><?= $item['name']; ?></p>
                    </div>
                    <div style="flex: 1">
                        <p class="text-center mb-0"><?= $item['quantity']; ?></p>
                    </div>
                    <div style="flex: 1">
                        <p class="text-center mb-0">Rp <?= number_format($item['value'], 0, ",", "."); ?></p>
                    </div>
                    <div style="flex: 1">
                        <p class="text-end mb-0">Rp <?= number_format($item['value'] * $item['quantity'], 0, ",", "."); ?></p>
                    </div>
                </div>
            <?php } ?>
        </div>

        <!-- Container perhitungan -->
        <div class="w-100 d-flex mt-auto border-bottom">
            <div class="flex-grow-1 w-100"></div>
            <div class="flex-grow-1 w-100">
                <div class="w-100 py-2">
                    <div class="w-100 d-flex">
                        <div style="flex: 2">
                            <p class="mb-0 fw-bold">TOTAL HARGA</p>
                        </div>
                        <div style="flex: 1">
                            <p class="text-end mb-0 fw-bold">
                                Rp <?= number_format($totalHarga, 0, ",", "."); ?>
                            </p>
                        </div>
                    </div>
                    <?php if (((int)$transaksi['data_mid']['gross_amount'] - 5000 - $totalHarga) < 0) { ?>
                        <div class="w-100 d-flex">
                            <div style="flex: 2">
                                <p class="mb-0">Diskon</p>
                            </div>
                            <div style="flex: 1">
                                <p class="text-end mb-0">Rp <?php
                                                            $ongkir = (int)$transaksi['data_mid']['gross_amount'] - 5000 - $totalHarga;
                                                            echo number_format($ongkir, 0, ",", ".");
                                                            ?></p>
                            </div>
                        </div>
                    <?php } ?>
                    <div class="w-100 d-flex">
                        <div style="flex: 2">
                            <p class="mb-0">Biaya Admin</p>
                        </div>
                        <div style="flex: 1">
                            <p class="text-end mb-0">Rp 5.000</p>
                        </div>
                    </div>
                    <div class="w-100 d-flex">
                        <div style="flex: 2">
                            <p class="mb-0 fw-bold">TOTAL TAGIHAN</p>
                        </div>
                        <div style="flex: 1">
                            <p class="text-end mb-0 fw-bold">
                                Rp <?= number_format($transaksi['data_mid']['gross_amount'], 0, ",", "."); ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Container footer -->
        <div class="w-100 d-flex py-2">
            <div class="w-100">
                <p class="mb-0 text-black-50">Kurir :</p>
                <p class="mb-0 fw-bold"><?= $transaksi['kurir']; ?></p>
            </div>
            <div class="w-100">
                <p class="mb-0 text-black-50">Metode Pembayaran</p>
                <p class="mb-0">
                    <?php
                    switch ($transaksi['data_mid']['payment_type']) {
                        case 'credit_card':
                            echo "Credit Card<br>" . strtoupper($transaksi['data_mid']['bank']) . " " . ucfirst($transaksi['data_mid']['card_type']);
                            break;
                        case 'echannel':
                            switch ($transaksi['data_mid']['biller_code']) {
                                case '70012':
                                    echo "Mandiri Bill";
                                    break;
                                default:
                                    echo "EChannel";
                                    break;
                            }
                            break;
                        case 'bank_transfer':
                            if (isset($transaksi['data_mid']['va_numbers']))
                                echo strtoupper($transaksi['data_mid']['va_numbers'][0]['bank']) . " VA";
                            else if (isset($transaksi['data_mid']['permata_va_number']))
                                echo "Bank Permata VA";
                            else if (isset($transaksi['data_mid']['bca_va_number']))
                                echo "BCA VA";
                            break;
                        case 'gopay':
                            echo 'Qris';
                            break;
                        case 'qris':
                            echo 'Qris';
                            break;
                        default:
                            echo $transaksi['data_mid']['payment_type'];
                            break;
                    }
                    ?></p>
            </div>
        </div>
        <div class="w-100 d-flex py-2 justify-content-between align-items-end">
            <div style="width: 50%">
                <p class="mb-0">
                    Invoice ini sah dan diproses oleh sistem<br />
                    Silakan hubungi
                    <a style="color: var(--merahLogo)" class="link-offset-2 link-underline-opacity-0 link-underline-opacity-100-hover">Lunarea Furniture CS</a>
                    apabila kamu membutuhkan bantuan.
                </p>
            </div>
            <div>
                <p class="mb-0 text-black-50">
                    Terakhir diupdate: <?php
                                        $d = strtotime($transaksi['data_mid']['transaction_time']);
                                        echo date("d", $d) . " " . $bulan[(int)date("m", $d) - 1] . " " . date("Y", $d) . " " . date("H:i:s", $d)
                                        ?> WIB
                </p>
            </div>
        </div>
    </div>
</body>
<?php if ($transaksi['status'] == 'Proses' || $transaksi['status'] == 'Dikirim' || $transaksi['status'] == 'Selesai' || $transaksi['status'] == 'Menunggu Pembayaran') { ?>
    <script>
        window.print();
    </script>
<?php } ?>

</html>