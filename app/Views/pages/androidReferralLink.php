<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <meta name="robots" content="noindex,nofollow">
  <title><?= esc($title); ?></title>
  <meta property="og:type" content="product">
  <meta property="og:site_name" content="Lunarea Furniture">
  <meta property="og:title" content="<?= esc($title, 'attr'); ?>">
  <meta property="og:description" content="<?= esc($productName !== '' ? 'Lihat foto, detail, dan harga ' . $productName . ' di Lunarea Furniture.' : 'Temukan furnitur pilihan untuk ruangmu di Lunarea Furniture.', 'attr'); ?>">
  <meta property="og:url" content="<?= esc($sharePageUrl, 'attr'); ?>">
  <meta property="og:image" content="<?= esc($productImageUrl, 'attr'); ?>">
  <meta property="og:image:alt" content="<?= esc($productName !== '' ? $productName : 'Lunarea Furniture', 'attr'); ?>">
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="<?= esc($title, 'attr'); ?>">
  <meta name="twitter:description" content="<?= esc($productName !== '' ? 'Lihat foto, detail, dan harga ' . $productName . ' di Lunarea Furniture.' : 'Temukan furnitur pilihan untuk ruangmu di Lunarea Furniture.', 'attr'); ?>">
  <meta name="twitter:image" content="<?= esc($productImageUrl, 'attr'); ?>">
  <style>
    *{box-sizing:border-box}body{margin:0;min-height:100vh;display:grid;place-items:center;padding:24px;background:#faf9f6;color:#262b27;font:16px/1.55 system-ui,-apple-system,"Segoe UI",sans-serif}.panel{width:min(100%,440px);padding:24px;background:#fff;border:1px solid #ebe8df;border-radius:22px}.preview{width:100%;aspect-ratio:1.7;object-fit:contain;background:#f5f3ee;border-radius:14px;margin-bottom:18px}.eyebrow{color:#028a0b;font-size:12px;font-weight:700;letter-spacing:.12em;text-transform:uppercase}h1{margin:10px 0;font-size:27px;line-height:1.2}.muted{color:#6b716d}.code{margin:22px 0;padding:18px;text-align:center;background:#f5f3ee;border-radius:14px;font-size:25px;font-weight:750;letter-spacing:.14em}.button{width:100%;min-height:48px;border:0;border-radius:12px;background:#03ac0e;color:#fff;font-weight:700;font-size:15px}.foot{margin-top:18px;padding-top:16px;border-top:1px solid #ebe8df;font-size:13px;color:#6b716d}
  </style>
</head>
<body><main class="panel">
  <div class="eyebrow">Lunarea · Afiliasi</div>
  <?php if ($productName !== '') { ?><img class="preview" src="<?= esc($productImageUrl, 'attr'); ?>" alt="<?= esc($productName, 'attr'); ?>"><?php } ?>
  <h1><?= $productName !== '' ? 'Temukan ' . esc($productName) : 'Belanja furnitur pilihan bersama Lunarea.'; ?></h1>
  <p class="muted">Tautan ini menyimpan kode afiliasi untuk pesanan website. Di aplikasi Android, kode akan ditautkan saat kamu masuk. Pengundang memperoleh komisi <?= esc(rtrim(rtrim(number_format((float)$commissionPercent, 2, ',', '.'), '0'), ',')); ?>% dalam Luna Poin setelah pesanan memenuhi syarat berstatus selesai.</p>
  <div id="referral-code" class="code"><?= esc($code); ?></div>
  <a class="button" href="<?= esc($productUrl, 'attr'); ?>" style="display:grid;place-items:center;text-decoration:none"><?= $productName !== '' ? 'Lihat produk' : 'Jelajahi produk Lunarea'; ?></a>
  <p class="foot">Komisi berupa Luna Poin (1 poin = Rp1), dihitung dari subtotal produk setelah diskon. Minimum subtotal produk: Rp<?= number_format((int)$minimumSubtotal, 0, ',', '.'); ?>. Transaksi sandbox, pembatalan, dan pesanan yang belum selesai tidak mendapat komisi.</p>
</main></body></html>
