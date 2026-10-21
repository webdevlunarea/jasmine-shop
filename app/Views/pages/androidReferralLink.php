<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <meta name="robots" content="noindex,nofollow"><title>Afiliasi Lunarea Furniture</title>
  <style>
    *{box-sizing:border-box}body{margin:0;min-height:100vh;display:grid;place-items:center;padding:24px;background:#faf9f6;color:#262b27;font:16px/1.55 system-ui,-apple-system,"Segoe UI",sans-serif}.panel{width:min(100%,440px);padding:30px;background:#fff;border:1px solid #ebe8df;border-radius:22px}.eyebrow{color:#028a0b;font-size:12px;font-weight:700;letter-spacing:.12em;text-transform:uppercase}h1{margin:10px 0;font-size:27px;line-height:1.2}.muted{color:#6b716d}.code{margin:22px 0;padding:18px;text-align:center;background:#f5f3ee;border-radius:14px;font-size:25px;font-weight:750;letter-spacing:.14em}.button{width:100%;min-height:48px;border:0;border-radius:12px;background:#03ac0e;color:#fff;font-weight:700;font-size:15px}.foot{margin-top:18px;padding-top:16px;border-top:1px solid #ebe8df;font-size:13px;color:#6b716d}
  </style>
</head>
<body><main class="panel">
  <div class="eyebrow">Lunarea · Afiliasi</div>
  <h1>Belanja furnitur pilihan bersama Lunarea.</h1>
  <p class="muted">Tautan ini menyimpan kode afiliasi untuk pesanan website. Di aplikasi Android, kode akan ditautkan saat kamu masuk. Pengundang memperoleh komisi <?= esc(rtrim(rtrim(number_format((float)$commissionPercent, 2, ',', '.'), '0'), ',')); ?>% dalam Luna Poin setelah pesanan memenuhi syarat berstatus selesai.</p>
  <div id="referral-code" class="code"><?= esc($code); ?></div>
  <a class="button" href="<?= esc($browseUrl, 'attr'); ?>" style="display:grid;place-items:center;text-decoration:none">Jelajahi produk Lunarea</a>
  <p class="foot">Komisi berupa Luna Poin (1 poin = Rp1), dihitung dari subtotal produk setelah diskon. Minimum subtotal produk: Rp<?= number_format((int)$minimumSubtotal, 0, ',', '.'); ?>. Transaksi sandbox, pembatalan, dan pesanan yang belum selesai tidak mendapat komisi.</p>
</main></body></html>
