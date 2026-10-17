<!doctype html>
<html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?= $mode === 'code' ? 'Kode reset sandi' : 'Sandi diperbarui'; ?></title></head>
<body style="margin:0;padding:0;background:#f7f2ed;color:#2f2d20;font-family:Arial,Helvetica,sans-serif;">
<div style="display:none;max-height:0;overflow:hidden;opacity:0;"><?= $mode === 'code' ? 'Kode verifikasi reset sandi Anda berlaku selama 10 menit.' : 'Konfirmasi perubahan sandi akun Lunarea Anda.'; ?></div>
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f7f2ed;"><tr><td align="center" style="padding:32px 16px;">
<table role="presentation" width="560" cellspacing="0" cellpadding="0" style="width:100%;max-width:560px;background:#ffffff;border:1px solid #dfcbb9;border-radius:12px;">
<tr><td style="padding:32px 28px 24px;border-bottom:1px solid #eee8e1;"><span style="font-size:24px;font-weight:700;letter-spacing:3px;">LUNAREA</span><br><span style="font-size:11px;letter-spacing:2px;color:#757064;">FURNITURE FOR YOUR HOME</span></td></tr>
<tr><td style="padding:28px;">
<h1 style="margin:0 0 16px;font-size:24px;line-height:1.4;font-weight:600;"><?= $mode === 'code' ? 'Reset sandi Anda' : 'Sandi berhasil diperbarui'; ?></h1>
<?php if ($mode === 'code') : ?>
<p style="margin:0 0 24px;font-size:15px;line-height:1.8;color:#626262;">Gunakan kode verifikasi berikut untuk membuat sandi baru pada akun Lunarea Anda.</p>
<table role="presentation" width="100%" cellspacing="0" cellpadding="0"><tr><td align="center" style="padding:22px 12px;background:#f7f2ed;border:1px solid #dfcbb9;border-radius:8px;font-family:Consolas,Monaco,monospace;font-size:32px;line-height:1.5;letter-spacing:8px;font-weight:700;"><?= esc($code); ?></td></tr></table>
<p style="margin:16px 0 24px;text-align:center;font-size:13px;color:#757064;line-height:1.7;">Berlaku <strong>10 menit</strong> · Hanya sekali pakai</p>
<p style="margin:0;font-size:14px;line-height:1.8;color:#626262;">Jangan berikan kode ini kepada siapa pun, termasuk pihak yang mengaku sebagai Lunarea. Jika Anda tidak meminta reset sandi, abaikan email ini. Sandi Anda tetap sama sampai proses reset diselesaikan.</p>
<?php else : ?>
<p style="margin:0 0 16px;font-size:15px;line-height:1.8;color:#626262;">Sandi akun Anda telah diperbarui. Sesi login lama akan diakhiri pada permintaan berikutnya. Silakan masuk kembali menggunakan sandi baru.</p>
<p style="margin:0;font-size:14px;line-height:1.8;color:#626262;">Jika perubahan ini bukan dilakukan oleh Anda, gunakan menu Lupa sandi pada website resmi Lunarea dan hubungi layanan pelanggan kami.</p>
<?php endif; ?>
</td></tr>
<tr><td style="padding:22px 28px;border-top:1px solid #eee8e1;font-size:12px;line-height:1.8;color:#757064;">Email otomatis dari Lunarea Furniture.<br>Jangan membalas email ini dengan sandi atau kode verifikasi Anda.</td></tr>
</table>
</td></tr></table>
</body></html>
