<?= $this->extend('layout/template'); ?>
<?= $this->section('content'); ?>
<div class="konten">
    <div class="container">
        <table class="table wishlist">
            <thead>
                <tr>
                    <th scope="col">Produk</th>
                    <th scope="col">Harga</th>
                    <th scope="col">Jumlah</th>
                    <th scope="col">Subtotal</th>
                    <th scope="col"></th>
                </tr>
            </thead>
            <tbody>
                <?php
                $subtotal = 0;
                if (!empty($keranjang)) { ?>
                    <?php foreach ($produk as $index => $p) { ?>
                        <tr>
                            <td>
                                <a href="/product/<?= $p['id']; ?>" class="btn">
                                    <img src="data:image/jpeg;base64,<?= base64_encode($p['gambar']); ?>" alt=""> <?= $p['nama']; ?>
                                </a>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-1" style="height:3em;">
                                    <?php if ($p['diskon']) { ?>
                                        <p class="mb-0 harga">Rp
                                            <?php
                                            $persen = (100 - $p['diskon']) / 100;
                                            $hasil = $persen * $p['harga'];
                                            echo number_format($hasil, 0, ",", ".");
                                            ?></p>
                                        <p class="mb-0" style="text-decoration: line-through; font-size: small; color: grey;">Rp <?= number_format($p['harga'], 0, ",", "."); ?></p>
                                    <?php } else {
                                        $hasil = $p['harga']; ?>
                                        <p class="mb-0 harga">Rp <?= number_format($p['harga'], 0, ",", "."); ?></p>
                                    <?php } ?>
                                </div>
                            </td>
                            <td>
                                <div class="input-group jumlah mt-2">
                                    <a class="input-group-text" href="/redcart/<?= $p['id']; ?>">-</a>
                                    <input disabled type="number" class="form-control text-center" value="<?= $jumlah[$index]; ?>">
                                    <a class="input-group-text" href="/addcart/<?= $p['id']; ?>">+</a>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-1" style="height:3em;">Rp
                                    <?php
                                    $subtotal += $hasil * $jumlah[$index];
                                    session()->set(['subtotal' => $subtotal]);
                                    echo number_format(($hasil * $jumlah[$index]), 0, ",", ".");
                                    ?>
                                </div>
                            </td>
                            <td><a href="/delcart/<?= $p['id']; ?>" class="btn btn-danger mt-2"><i class="material-icons">delete</i></a></td>
                        </tr>
                    <?php } ?>
                <?php } else { ?>
                    <tr>
                        <td>Keranjang kosong</td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
    <div class="container d-flex justify-content-between align-items-start">
        <form action="" class="d-flex gap-1">
            <input type="text" placeholder="Kode Kupon" class="form-control">
            <button type="submit" class="btn btn-danger">Terapkan</button>
        </form>
        <div class="cart-total">
            <h5>Total Keranjang</h5>
            <div class="d-flex justify-content-between border-bottom" style="gap: 10em;">
                <p class="my-2">Subtotal:</p>
                <p class="my-2"><b>Rp <?= number_format($subtotal, 0, ",", "."); ?></b></p>
            </div>
            <div class="d-flex justify-content-between border-bottom" style="gap: 10em;">
                <p class="my-2">Pengiriman:</p>
                <p class="my-2"><b>Rp 10.000</b></p>
            </div>
            <div class="d-flex justify-content-between" style="gap: 10em;">
                <p class="my-2">Total:</p>
                <p class="my-2"><b>Rp <?= number_format(($subtotal + 10000), 0, ",", "."); ?></b></p>
            </div>
            <a class="btn btn-danger <?= !empty($keranjang) ? "" : "disabled"; ?>" href="/checkout">Proses Checkout</a>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>