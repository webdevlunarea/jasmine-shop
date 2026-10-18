<?php

namespace App\Services\Auth;

final class CustomerVerificationMailer
{
    public function send(string $recipient, string $code): bool
    {
        $email = null;
        try {
            $config = config(\Config\Email::class);
            $email = \Config\Services::email(null, false);
            $from = $config->fromEmail ?: $config->SMTPUser;
            $email->setFrom($from, $config->fromName ?: 'Lunarea Furniture');
            $email->setReplyTo($from, 'Customer Care Lunarea Furniture');
            $email->setTo($recipient);
            $email->setSubject('Kode verifikasi akun Lunarea');
            $email->setMailType('html');
            $body = view('emails/customer_verification_content', ['code' => $code], ['saveData' => false]);
            $email->setMessage(view('emails/layout', [
                'title' => 'Verifikasi email Anda',
                'preheader' => 'Kode verifikasi akun Lunarea berlaku selama 10 menit.',
                'bodyHtml' => $body,
            ], ['saveData' => false]));
            $email->setAltMessage("Kode verifikasi akun Lunarea: {$code}\nBerlaku 10 menit dan hanya dapat digunakan sekali. Jangan bagikan kode ini kepada siapa pun.\nJika Anda tidak membuat akun, abaikan email ini.\n\nLunarea furniture, always your choice, always your furniture.\nhttps://lunareafurniture.com");
            if (!$email->send(false)) {
                log_message('error', 'Customer verification email transport rejected the message.');
                return false;
            }
            return true;
        } catch (\Throwable $e) {
            log_message('error', 'Customer verification email dispatch raised ' . get_class($e) . '.');
            return false;
        } finally {
            if ($email !== null) $email->clear(true);
        }
    }
}
