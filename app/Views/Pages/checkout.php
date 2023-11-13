<?= $this->extend('layout/template'); ?>
<?= $this->section('content'); ?>
<div class="konten">
    <div class="container">
        <h1>Detail Pembayaran</h1>
        <div class="row gap-5 mt-3">
            <div class="col">
                <div class="form-floating mb-1">
                    <input type="email" class="form-control" placeholder="Email" name="email" value="<?= $user['email']; ?>">
                    <label for="floatingInput">Email</label>
                </div>
                <div class="form-floating mb-1">
                    <input type="text" class="form-control" placeholder="Alamat" name="alamat" value="<?= $user['alamat']; ?>">
                    <label for="floatingPassword">Alamat</label>
                </div>
                <div class="form-floating mb-1">
                    <input type="text" class="form-control" placeholder="Phone" name="hp">
                    <label for="floatingInput">No. HP</label>
                </div>
            </div>
            <div class="col">
                <div>
                    <table class="table table-borderless">
                        <tbody>
                            <?php foreach ($produk as $index => $p) { ?>
                                <tr>
                                    <td><?= $p['nama']; ?></td>
                                    <td><?= $jumlah[$index]; ?></td>
                                    <td class="text-end">Rp 
                                        <?php
                                            if ($p['diskon']) {
                                                $persen = (100 - $p['diskon']) / 100;
                                                $hasil = $persen * $p['harga'];
                                                echo number_format($hasil, 0, ",", ".");
                                            } else {
                                                $hasil = $p['harga'];
                                                echo number_format($p['harga'], 0, ",", ".");
                                            }
                                        ?>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-between border-bottom border-top mt-3" style="gap: 10em;">
                    <p class="my-2">Subtotal:</p>
                    <p class="my-2"><b>Rp <?= number_format(session()->get('subtotal'), 0, ",", "."); ?></b></p>
                </div>
                <div class="d-flex justify-content-between border-bottom" style="gap: 10em;">
                    <p class="my-2">Pengiriman:</p>
                    <p class="my-2"><b>Rp 10.000</b></p>
                </div>
                <div class="d-flex justify-content-between" style="gap: 10em;">
                    <p class="my-2">Total:</p>
                    <p class="my-2"><b>Rp <?= number_format(session()->get('subtotal') + 10000, 0, ",", "."); ?></b></p>
                </div>
                <button class="btn btn-danger">Pesan</button>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>