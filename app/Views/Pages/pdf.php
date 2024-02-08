<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
    <title><?= $title; ?> | Jasmine Furniture</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4bw+/aepP/YC94hEpVNVgiZdgIC5+VKNBQNGCHeKRQN+PtmoHDEXuppvnDJzQIu9" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous">
    </script>
    <script src="https://unpkg.com/jspdf@latest/dist/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <!-- <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" /> -->
    <!-- icon google -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="SB-Mid-client-lDi-03j_XL3PVN0_">
    </script>

    <script src="https://kit.fontawesome.com/917733e7d4.js" crossorigin="anonymous"></script>

    <link rel="stylesheet" href="/css/style_gudang.css">
</head>

<body>
    <div class="print">
        <div class="p-5">
            <img src="../img/Logo Jasmine.png" width="150mm" />
            <h4 class="fw-bold text-center">FORMULIR PEMESANAN BARANG</h4>
            <div style="float: right" class="mb-3">
                <table>
                    <tbody>
                        <tr>
                            <td class="pe-1">Tanggal Order</td>
                            <td id="print-tanggal-order">: <?= explode("-", explode(" ", $transaksi['data_mid']['transaction_time'])[0])[2] . "/" . explode("-", explode(" ", $transaksi['data_mid']['transaction_time'])[0])[1] . "/" . explode("-", explode(" ", $transaksi['data_mid']['transaction_time'])[0])[0]; ?></td>
                        </tr>
                        <tr>
                            <td>No. Orderan</td>
                            <td id="print-no-order">: <?= $transaksi['id_midtrans']; ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div>
                <table class="table table-bordered border-dark">
                    <thead id="print-tabel-barang">
                        <tr>
                            <th>No</th>
                            <th>Nama Barang</th>
                            <th>Kode Barang</th>
                            <th>Qty</th>
                        </tr>
                        <?php
                        $hitungTotal = 0;
                        foreach ($transaksi['items'] as $ind_item => $item) {
                            $hitungTotal += $item['value'] * $item['quantity'];
                        ?>
                            <tr>
                                <td><?= $ind_item + 1; ?></td>
                                <td><?= $item['name']; ?></td>
                                <td><?= explode("- ", $item['name'])[1]; ?></td>
                                <td><?= $item['quantity']; ?></td>
                            </tr>
                        <?php } ?>
                    </thead>
                </table>
            </div>
            <div class="mb-3">
                <p class="fw-bold mb-0">Informasi Pemesanan</p>
                <table>
                    <tbody>
                        <tr>
                            <td class="pe-1">Nama Lengkap</td>
                            <td id="print-nama-pemesan">: <?= $transaksi['nama_cus']; ?></td>
                        </tr>
                        <tr>
                            <td>No. Hp</td>
                            <td id="print-no-pemesan">: <?= $transaksi['hp_cus']; ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="mb-3">
                <table class="table table-bordered border-dark">
                    <thead>
                        <tr>
                            <th colspan="2">Informasi Pembayaran</th>
                        </tr>
                        <tr>
                            <td><input type="radio" name="print-info-bayar" value="transfer" class="print-info-bayar" <?= ($transaksi['data_mid']['payment_type'] == 'bank_transfer' || $transaksi['data_mid']['payment_type'] == 'echannel') ? "checked" : ""; ?> /></td>
                            <td class="text-start">Transfer</td>
                        </tr>
                        <tr>
                            <td><input type="radio" name="print-info-bayar" value="kredit" class="print-info-bayar" <?= $transaksi['data_mid']['payment_type'] == 'credit_card' ? "checked" : ""; ?> /></td>
                            <td class="text-start">
                                Kartu Kredit
                                <p style="
                                            font-size: small;
                                            margin: 0;
                                        ">
                                    (Jika pemilik kartu ini tidak hadir,
                                    wajib melampirkan formular otorisasi
                                    yang telah diisi secara lengkap)
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <td><input type="radio" name="print-info-bayar" value="ewallet" class="print-info-bayar" <?= ($transaksi['data_mid']['payment_type'] == 'qris' || $transaksi['data_mid']['payment_type'] == 'gopay' || $transaksi['data_mid']['payment_type'] == 'shopeepay' || $transaksi['data_mid']['payment_type'] == 'cstore') ? "checked" : ""; ?> /></td>
                            <td class="text-start">Dompet Digital (Gopay, Dana, ShopeePay)</td>
                        </tr>
                    </thead>
                </table>
            </div>
            <div class="mb-3">
                <p class="fw-bold mb-0">Produk diatas akan dikirim ke</p>
                <table>
                    <tbody>
                        <tr>
                            <td>Nama</td>
                            <td id="print-nama-penerima">: <?= $transaksi['nama_pen']; ?></td>
                        </tr>
                        <tr>
                            <td class="pe-1">Alamat</td>
                            <td id="print-alamat-penerima">: <?= $transaksi['alamat_pen']['alamat']; ?></td>
                        </tr>
                        <tr>
                            <td>Kota</td>
                            <td id="print-kota">
                                : <?= $transaksi['alamat_pen']['kab']; ?> Kode Pos : <?= $transaksi['alamat_pen']['kodepos']; ?> Telp. <?= $transaksi['hp_pen']; ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="d-flex">
                <div class="border flex-grow-1 border-dark d-flex justify-content-center align-items-end" style="height: 150px">
                    <p class="mb-0">Staf</p>
                </div>
                <div class="border flex-grow-1 border-dark d-flex justify-content-center align-items-end" style="height: 150px">
                    <p class="mb-0">Pemesanan</p>
                </div>
            </div>
        </div>
    </div>
</body>
<script>
    console.log(JSON.parse('<?= $transaksiJson; ?>'))
    window.print()
</script>

</html>