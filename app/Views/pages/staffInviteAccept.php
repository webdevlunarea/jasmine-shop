<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="referrer" content="no-referrer"><meta name="robots" content="noindex,nofollow">
    <title>Buat akun staf · Lunarea Furniture</title>
    <style>
        *{box-sizing:border-box}body{margin:0;min-height:100vh;display:grid;place-items:center;padding:24px;background:#faf9f6;color:#282e29;font:15px/1.6 "Plus Jakarta Sans",Arial,sans-serif}
        main{width:min(100%,480px);padding:30px;border:1px solid #e2ded5;border-radius:16px;background:#fff;box-shadow:0 12px 36px #282e2910}
        .brand{margin:0 0 7px;color:#365b46;font-size:11px;font-weight:700;letter-spacing:1.4px}h1{margin:0 0 7px;font-size:23px;letter-spacing:-.5px}p{margin:0 0 20px;color:#68736b;font-size:13px}
        label{display:block;margin:15px 0 6px;font-size:12px;font-weight:600}input{width:100%;min-height:45px;padding:10px 12px;border:1px solid #e2ded5;border-radius:8px;font:inherit;font-size:14px}input:focus{outline:3px solid #365b4630;border-color:#365b46}
        button{width:100%;min-height:46px;margin-top:20px;border:0;border-radius:8px;background:#365b46;color:#fff;font:inherit;font-size:13px;font-weight:600;cursor:pointer}button:hover{background:#294936}.error{margin:12px 0 0;color:#a3443b;font-size:12px}
        @media(max-width:520px){main{padding:23px 19px;border-radius:13px}h1{font-size:21px}}
    </style>
</head>
<body><main>
    <p class="brand">LUNAREA FURNITURE · AKUN STAF</p>
    <h1>Buat akun staf</h1>
    <p>Undangan untuk <strong><?= esc($invite['email']); ?></strong>. Akses awal: <strong><?= esc($invite['role_key']); ?></strong>. Atur sandi pribadi untuk menyelesaikan pendaftaran.</p>
    <form method="post" action="<?= site_url('staff/invite/accept'); ?>" autocomplete="on">
        <?= csrf_field(); ?><input type="hidden" name="token" value="<?= esc($token, 'attr'); ?>">
        <label for="staff-password">Sandi baru</label><input id="staff-password" name="password" type="password" minlength="8" maxlength="72" autocomplete="new-password" required>
        <label for="staff-password-confirm">Ulangi sandi</label><input id="staff-password-confirm" name="password_confirm" type="password" minlength="8" maxlength="72" autocomplete="new-password" required>
        <?php if (!empty($error)): ?><p class="error" role="alert"><?= esc($error); ?></p><?php endif; ?>
        <button type="submit">Aktifkan akun staf</button>
    </form>
</main></body></html>
