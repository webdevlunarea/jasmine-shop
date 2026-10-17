<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($title ?? 'Lunarea Furniture'); ?></title>
</head>
<body style="margin:0;padding:0;background-color:#f5f2ee;color:#2f2d20;font-family:Arial,Helvetica,sans-serif;-webkit-text-size-adjust:100%;">
    <div style="display:none;max-height:0;overflow:hidden;opacity:0;color:transparent;"><?= esc($preheader ?? 'Informasi terbaru dari Lunarea Furniture.'); ?></div>
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="width:100%;background-color:#f5f2ee;">
        <tr><td align="center" style="padding:32px 14px;">
            <table role="presentation" width="600" cellspacing="0" cellpadding="0" border="0" style="width:100%;max-width:600px;background-color:#fefeff;border:1px solid #e7dfd7;border-radius:14px;overflow:hidden;">
                <tr><td style="padding:25px 30px 22px;border-bottom:1px solid #eee8e2;">
                    <a href="https://lunareafurniture.com" style="color:#2f2d20;text-decoration:none;font-size:23px;font-weight:700;letter-spacing:3px;">LUNAREA</a>
                    <div style="padding-top:6px;color:#81776d;font-size:11px;letter-spacing:1.3px;">FURNITURE FOR YOUR HOME</div>
                </td></tr>
                <tr><td style="padding:30px;font-size:15px;line-height:1.75;color:#3f3b34;">
                    <?= $bodyHtml ?? ''; ?>
                </td></tr>
                <tr><td style="padding:20px 30px 24px;background-color:#f8f5f1;border-top:1px solid #eee8e2;">
                    <div style="color:#2f2d20;font-size:13px;font-weight:600;line-height:1.6;">Lunarea furniture, always your choice, always your furniture.</div>
                    <div style="padding-top:8px;color:#81776d;font-size:12px;line-height:1.7;">Email ini dikirim oleh Lunarea Furniture.<br><a href="https://lunareafurniture.com" style="color:#72583f;text-decoration:underline;">lunareafurniture.com</a></div>
                </td></tr>
            </table>
        </td></tr>
    </table>
</body>
</html>
