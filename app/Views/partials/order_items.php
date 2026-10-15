<table class="table table-borderless order-items-table">
    <caption class="visually-hidden">Rincian produk dan biaya pesanan</caption>
    <thead><tr><th scope="col">Produk / biaya</th><th scope="col">Jumlah</th><th scope="col">Harga satuan</th></tr></thead>
    <tbody>
        <?php foreach ($items as $orderItem) { ?>
        <tr><td><?= esc($orderItem['name']); ?></td><td>×<?= (int)$orderItem['quantity']; ?></td><td>Rp <?= number_format($orderItem['value'], 0, ',', '.'); ?></td></tr>
        <?php } ?>
    </tbody>
</table>
