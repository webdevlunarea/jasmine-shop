<?php

namespace App\Services\Auth;

/** Best-effort security notices; mail delivery never changes auth outcomes. */
final class CustomerSecurityMailer
{
    public function passwordChanged(string $recipient): void
    {
        $this->deliver(
            $recipient,
            'Pemberitahuan perubahan kata sandi Lunarea',
            'Kata sandi akun Lunarea Anda baru saja diubah. Semua sesi aplikasi telah diminta untuk berakhir. ' .
            'Jika Anda tidak melakukan perubahan ini, segera pulihkan kata sandi dan hubungi layanan pelanggan.'
        );
    }

    private function deliver(string $recipient, string $subject, string $message): void
    {
        $mail = null;
        try {
            $config = config(\Config\Email::class);
            $mail = \Config\Services::email(null, false);
            $mail->setFrom($config->fromEmail ?: $config->SMTPUser, $config->fromName ?: 'Lunarea Furniture');
            $mail->setTo($recipient);
            $mail->setSubject($subject);
            $mail->setMailType('html');
            $mail->setMessage(view('emails/layout', [
                'title' => $subject,
                'preheader' => 'Pemberitahuan keamanan akun Lunarea Furniture.',
                'bodyHtml' => '<div style="color:#3f3b34;font-size:15px;line-height:1.75"><p>' . esc($message) . '</p>' .
                    '<p style="margin:22px 0 0">Salam,<br><strong>Lunarea Furniture</strong></p></div>',
            ], ['saveData' => false]));
            $mail->setAltMessage($message . "\n\nLunarea Furniture\nhttps://lunareafurniture.com");
            if (!$mail->send(false)) throw new \RuntimeException('SMTP rejected the security notice');
        } catch (\Throwable $e) {
            log_message('error', 'Customer security notice delivery failed. Check SMTP configuration privately.');
        } finally {
            if ($mail !== null) $mail->clear(true);
        }
    }
}
