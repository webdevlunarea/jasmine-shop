<?php

namespace App\Services\Auth;

class PasswordResetMailer
{
    protected function emailClient() { return \Config\Services::email(null, false); }
    public function sendCode(string $recipient, string $code): bool { return $this->deliver($recipient, 'code', $code); }
    public function sendChanged(string $recipient): bool { return $this->deliver($recipient, 'changed', ''); }

    private function deliver(string $recipient, string $mode, string $code): bool
    {
        $email = null;
        try {
            $config = config(\Config\Email::class);
            $email = $this->emailClient();
            $email->setFrom($config->fromEmail ?: $config->SMTPUser, $config->fromName ?: 'Lunarea Furniture');
            $email->setTo($recipient);
            $email->setSubject($mode === 'code' ? 'Kode reset sandi Lunarea' : 'Sandi akun Lunarea berhasil diperbarui');
            $email->setMailType('html');
            $content = view('emails/password_reset_content', ['mode' => $mode, 'code' => $code], ['saveData' => false]);
            $email->setMessage(view('emails/layout', [
                'title' => $mode === 'code' ? 'Reset sandi Anda' : 'Sandi berhasil diperbarui',
                'preheader' => $mode === 'code' ? 'Kode verifikasi reset sandi Anda berlaku selama 10 menit.' : 'Informasi perubahan sandi akun Lunarea Anda.',
                'bodyHtml' => $content,
            ], ['saveData' => false]));
            $email->setAltMessage(($mode === 'code'
                ? "Kode reset sandi Lunarea: {$code}\nBerlaku 10 menit, hanya sekali pakai. Jangan bagikan kode ini kepada siapa pun.\nJika Anda tidak meminta reset sandi, abaikan email ini."
                : 'Sandi akun Lunarea berhasil diperbarui. Sesi lama akan diakhiri pada permintaan berikutnya. Jika bukan Anda yang mengubahnya, gunakan Lupa sandi di website resmi Lunarea dan hubungi layanan pelanggan.')
                . "\n\nLunarea furniture, always your choice, always your furniture.\nhttps://lunareafurniture.com");
            if (!$email->send(false)) throw new \RuntimeException();
            return true;
        } catch (\Throwable $e) {
            // SMTP debugger/exception messages can contain addresses, credentials and OTPs.
            log_message('error', 'Password reset email delivery failed. Check SMTP configuration privately.');
            return false;
        } finally {
            if ($email !== null) $email->clear(true);
        }
    }
}
