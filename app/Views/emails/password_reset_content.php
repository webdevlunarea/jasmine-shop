<h1 style="margin:0 0 16px;color:#2f2d20;font-size:23px;line-height:1.35;font-weight:600;"><?= $mode === 'code' ? 'Reset sandi Anda' : 'Sandi berhasil diperbarui'; ?></h1>
<?php if ($mode === 'code') : ?>
    <p style="margin:0 0 22px;color:#625d55;">Gunakan kode verifikasi berikut untuk membuat sandi baru pada akun Lunarea Anda.</p>
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0"><tr><td align="center" style="padding:20px 12px;background:#f7f3ef;border:1px solid #e3d4c6;border-radius:9px;font-family:Consolas,Monaco,monospace;font-size:30px;line-height:1.5;letter-spacing:8px;font-weight:700;color:#2f2d20;"><?= esc($code); ?></td></tr></table>
    <p style="margin:14px 0 22px;text-align:center;color:#81776d;font-size:13px;">Berlaku <strong>10 menit</strong> dan hanya dapat digunakan sekali.</p>
    <p style="margin:0;color:#625d55;font-size:14px;">Jangan bagikan kode ini kepada siapa pun. Jika Anda tidak meminta reset sandi, abaikan email ini. Sandi Anda tetap sama sampai proses reset selesai.</p>
<?php else : ?>
    <p style="margin:0 0 14px;color:#625d55;">Sandi akun Anda telah diperbarui. Silakan masuk kembali menggunakan sandi baru.</p>
    <p style="margin:0;color:#625d55;font-size:14px;">Jika perubahan ini bukan dilakukan oleh Anda, gunakan menu Lupa sandi pada website resmi Lunarea dan hubungi layanan pelanggan kami.</p>
<?php endif; ?>
