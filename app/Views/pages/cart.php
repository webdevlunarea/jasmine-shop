<?= $this->extend('layout/template'); ?>
<?= $this->section('content'); ?>
<div class="konten">
    <div class="container baris-ke-kolom">
        <div style="flex: 1;">
            <?php
            $subtotal = 0;
            if (!empty($keranjang)) { ?>
                <?php foreach ($produk as $index => $p) { ?>
                    <div class="card-cart baris-ke-kolom justify-content-between">
                        <a href="/product/<?= $p['path']; ?>" class="d-flex gap-4 text-dark" style="height: 100%;">
                            <img src="data:image/webp;base64,<?= base64_encode($gambar[$index]); ?>" alt="<?= $p['nama']; ?>">
                            <div>
                                <p class="mb-0 <?= in_array($index, $indStokHabis) ? "text-danger" : ""; ?>"><?= $p['nama']; ?></p>
                                <p class="mb-0 <?= in_array($index, $indStokHabis) ? "text-danger" : ""; ?>">Varian : <?= $keranjang[$index]['varian'] ?></p>
                                <?php
                                if ($p['diskon']) {
                                    $persen = (100 - $p['diskon']) / 100;
                                    $hasil = round($persen * $p['harga']);
                                } else {
                                    $hasil = $p['harga'];
                                }
                                ?>
                                <?php if (in_array($index, $indStokHabis)) { ?>
                                    <p class="mb-0 text-danger"><b>Stok kurang</b></p>
                                <?php } else { ?>
                                    <?php if ($p['diskon']) { ?>
                                        <p class="mb-0" style="text-decoration: line-through; font-size: small; color: grey;">Rp
                                            <?= number_format($p['harga'], 0, ",", "."); ?></p>
                                        <p class="mb-0 harga" style="display: inline;">Rp <?= number_format($hasil, 0, ",", "."); ?></p>
                                    <?php } else { ?>
                                        <p class="mb-0 harga">Rp <?= number_format($hasil, 0, ",", "."); ?></p>
                                    <?php } ?>
                                <?php } ?>

                            </div>
                        </a>
                        <div>
                            <p class="border-bottom text-secondary text-end mb-0">Total</p>
                            <p class="fw-bold mb-2 text-end">Rp
                                <?php
                                $subtotal += $hasil * $jumlah[$index];
                                // session()->set(['subtotal' => $subtotal]);
                                echo number_format(($hasil * $jumlah[$index]), 0, ",", ".");
                                ?>
                            </p>
                            <div class="d-flex gap-3 justify-content-end">
                                <form action="/delcart/<?= $index; ?>" method="post">
                                    <button type="submit" class="btn btn-light"><i class="material-icons">delete</i></button>
                                </form>
                                <div class="input-group jumlah">
                                    <form action="/redcart/<?= $index; ?>" method="post">
                                        <button type="submit" class="input-group-text">-</button>
                                    </form>
                                    <input disabled type="number" class="form-control text-center <?= in_array($index, $indStokHabis) ? "text-danger" : ""; ?>" value="<?= $jumlah[$index]; ?>">
                                    <form action="/addcart/<?= $p['id']; ?>/<?= $keranjang[$index]['varian'] ?>/<?= $keranjang[$index]['index_gambar'] ?>" method="post">
                                        <button type="submit" class="input-group-text">+</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            <?php } else { ?>
                <div>
                    <p class="text-center">Oops, keranjangmu masih kosong!
                        Sepertinya belum nentuin produk favoritmu ya.</p>
                </div>
            <?php } ?>
        </div>
        <div class="cart-total">
            <h5>Total Keranjang</h5>
            <div class="d-flex justify-content-between border-bottom">
                <p class="my-2">Subtotal:</p>
                <p class="my-2"><b>Rp <?= number_format($subtotal, 0, ",", "."); ?></b></p>
            </div>
            <div class="d-flex justify-content-between border-bottom">
                <p class="my-2">Total Berat:</p>
                <p class="my-2"><b><?= $berat; ?> kg</b></p>
            </div>
            <a class="btn btn-primary1 mt-2 <?= !empty($keranjang) ? "" : "disabled"; ?><?= count($indStokHabis) > 0 ? "disabled" : ""; ?>" href="/checkout">Proses
                Checkout</a>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>