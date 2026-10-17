<?= view('emails/layout', [
    'title' => $mode === 'code' ? 'Reset sandi Anda' : 'Sandi berhasil diperbarui',
    'preheader' => $mode === 'code' ? 'Kode verifikasi reset sandi Anda berlaku selama 10 menit.' : 'Informasi perubahan sandi akun Lunarea Anda.',
    'bodyHtml' => view('emails/password_reset_content', ['mode' => $mode, 'code' => $code ?? ''], ['saveData' => false]),
], ['saveData' => false]); ?>
