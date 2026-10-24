<?php

namespace App\Services\Auth;

final class AdminStaffInvitationMailer
{
    public function send(string $recipient, string $url, string $role, string $expiresAt): bool
    {
        $email = null;
        try {
            $config = config(\Config\Email::class);
            $email = \Config\Services::email(null, false);
            $email->setFrom($config->fromEmail ?: $config->SMTPUser, $config->fromName ?: 'Lunarea Furniture');
            $email->setTo($recipient);
            $email->setSubject('Undangan akun staf Lunarea Furniture');
            $email->setMailType('html');
            $body = '<div style="font-size:15px;line-height:1.7;color:#3f3b34"><p>Anda diundang untuk membuat akun staf Lunarea Furniture.</p>'
                . '<p>Akses awal: <strong>' . esc($role) . '</strong>. Tautan berlaku hingga ' . esc($expiresAt) . ' UTC dan hanya dapat digunakan sekali.</p>'
                . '<p style="margin:24px 0"><a href="' . esc($url, 'attr') . '" style="display:inline-block;padding:12px 18px;border-radius:7px;background:#365b46;color:#fff;text-decoration:none;font-weight:600">Buat akun staf</a></p>'
                . '<p>Jika Anda tidak mengharapkan undangan ini, abaikan email ini.</p></div>';
            $email->setMessage(view('emails/layout', ['title' => 'Undangan akun staf', 'preheader' => 'Buat akun staf Lunarea dengan akses sesuai tugas.', 'bodyHtml' => $body], ['saveData' => false]));
            $email->setAltMessage("Anda diundang membuat akun staf Lunarea Furniture. Akses awal: {$role}. Tautan berlaku 48 jam dan sekali pakai:\n{$url}\nJika tidak mengharapkan undangan ini, abaikan email ini.");
            return (bool)$email->send(false);
        } catch (\Throwable $error) {
            log_message('error', 'Staff invitation email delivery failed: {type}', ['type' => get_class($error)]);
            return false;
        } finally {
            if ($email !== null) $email->clear(true);
        }
    }
}
