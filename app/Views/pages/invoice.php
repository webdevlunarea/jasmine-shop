<?php $pdf = $pdf ?? false; $money = static fn($amount) => ($amount < 0 ? '− ' : '') . 'Rp ' . number_format(abs($amount), 0, ',', '.'); ?>
<!doctype html>
<html lang="id"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="robots" content="noindex, nofollow">
<title>Invoice <?= esc($invoice['orderId']); ?> | Lunarea Furniture</title>
<style><?= file_get_contents(FCPATH . 'css/buyer-invoice.css'); ?></style>
</head><body class="<?= $pdf ? 'invoice-pdf' : 'invoice-preview'; ?>">
<?php if (!$pdf) { ?>
<nav class="invoice-toolbar" aria-label="Tindakan invoice">
<a class="invoice-back" href="/transaction">← Kembali ke transaksi</a><div class="invoice-actions">
<button class="invoice-button invoice-button-secondary" type="button" id="invoice-print">Cetak</button>
<a class="invoice-button" href="/invoice/<?= rawurlencode($invoice['orderId']); ?>" download>Unduh PDF</a></div></nav>
<?php } ?>
<main class="invoice-paper" aria-labelledby="invoice-title">
<table class="invoice-heading" role="presentation"><tr>
<td class="invoice-brand"><span class="invoice-wordmark">Lunarea</span><span class="invoice-brand-detail">F U R N I T U R E</span></td>
<td class="invoice-heading-right"><h1 id="invoice-title">Invoice</h1><p><?= esc($invoice['number']); ?></p><span class="invoice-status <?= $invoice['paid'] ? 'invoice-status-paid' : ''; ?>"><?= esc($invoice['badge']); ?></span></td>
</tr></table>
<table class="invoice-parties" role="presentation"><tr>
<td><h2>Diterbitkan oleh</h2><p class="invoice-strong">Lunarea Furniture</p><p>Semarang, Jawa Tengah</p><p class="invoice-muted">lunareafurniture.com</p></td>
<td><h2>Ditujukan kepada</h2><p class="invoice-strong"><?= esc($invoice['recipient']); ?></p><p class="invoice-address"><?= nl2br(esc($invoice['address'])); ?></p></td>
</tr></table>
<table class="invoice-meta" role="presentation"><tr>
<td><h2>Nomor pesanan</h2><p><?= esc($invoice['orderId']); ?></p></td>
<td><h2>Tanggal pembelian</h2><p><?= esc($invoice['date']); ?></p></td>
<td><h2>Metode pembayaran</h2><p><?= esc($invoice['method']); ?></p></td>
</tr></table>
<table class="invoice-items"><caption>Rincian pesanan</caption>
<thead><tr><th scope="col" class="invoice-product">Produk</th><th scope="col" class="invoice-quantity">Jumlah</th><th scope="col" class="invoice-numeric">Harga satuan</th><th scope="col" class="invoice-numeric">Total</th></tr></thead><tbody>
<?php foreach ($invoice['items'] as $item) { ?>
<tr><td class="invoice-product"><?= esc($item['name']); ?></td><td class="invoice-quantity"><span class="invoice-mobile-label" aria-hidden="true">Jumlah</span><?= (int)$item['quantity']; ?></td><td class="invoice-numeric"><span class="invoice-mobile-label" aria-hidden="true">Harga satuan</span><?= $money($item['price']); ?></td><td class="invoice-numeric"><span class="invoice-mobile-label" aria-hidden="true">Total</span><?= $money($item['amount']); ?></td></tr>
<?php } ?>
<?php if (!$invoice['items']) { ?><tr><td colspan="4">Rincian produk belum tercatat.</td></tr><?php } ?>
</tbody></table>
<div class="invoice-settlement">
<table class="invoice-totals" aria-label="Ringkasan tagihan">
<?php foreach ($invoice['rows'] as $row) { ?><tr><th scope="row"><?= esc($row['label']); ?></th><td><?= $money($row['amount']); ?></td></tr><?php } ?>
<tr class="invoice-grand-total"><th scope="row">Total tagihan</th><td><?= $invoice['total'] === null ? 'Belum tercatat' : $money($invoice['total']); ?></td></tr></table>
<?php if ($invoice['hasAdjustment']) { ?><p class="invoice-adjustment">Penyesuaian mengikuti tagihan pesanan tersimpan dan dapat mencakup biaya layanan, proteksi, atau potongan lain.</p><?php } ?>
<?php if ($invoice['cashback'] > 0) { ?><p class="invoice-adjustment">Cashback pesanan: <?= $money($invoice['cashback']); ?>. Cashback bukan potongan total tagihan.</p><?php } ?>
</div>
<table class="invoice-delivery" role="presentation"><tr><td><h2>Pengiriman</h2><p><?= esc($invoice['courier']); ?></p></td><td><h2>Nomor resi</h2><p><?= esc($invoice['tracking']); ?></p></td></tr></table>
<footer class="invoice-note"><p class="invoice-strong">Terima kasih telah memilih Lunarea.</p><p>Dokumen ini diterbitkan secara elektronik. Simpan invoice sebagai rincian pesanan Anda.</p>
<?php if (!$invoice['paid']) { ?><p>Invoice ini bukan bukti pembayaran lunas. Status pesanan: <?= esc($invoice['status'] ?: 'Belum tersedia'); ?>.</p><?php } ?>
<p>Butuh bantuan? Hubungi layanan pelanggan melalui lunareafurniture.com/contact.</p></footer>
</main>
<?php if (!$pdf) { ?><script>document.getElementById('invoice-print').addEventListener('click', function () { window.print(); });</script><?php } ?>
</body></html>
