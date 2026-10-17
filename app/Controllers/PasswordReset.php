<?php

namespace App\Controllers;

use App\Services\Auth\PasswordResetService;
use App\Services\Auth\RecaptchaVerifier;

class PasswordReset extends BaseController
{
    protected function resets(): PasswordResetService { return new PasswordResetService(); }
    protected function captchaError(): ?string { return (new RecaptchaVerifier())->error($this->request->getPost('g-recaptcha-response')); }

    private function redirectTo(string $path)
    {
        return redirect()->to(site_url($path))->removeHeader('Cache-Control')
            ->setHeader('Cache-Control', 'no-store, private')->setHeader('Referrer-Policy', 'no-referrer');
    }
    private function pendingEmail(): ?string
    {
        $email = session('password_reset_pending_email');
        $created = session('password_reset_pending_at');
        if (!is_string($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)
            || !is_numeric($created) || (int)$created < time() - 600) {
            session()->remove(['password_reset_pending_email', 'password_reset_pending_at']);
            return null;
        }
        return $email;
    }
    public function requestForm()
    {
        $pending = $this->pendingEmail();
        if ($pending === null) {
            $previous = session('password_reset_email');
            if (is_string($previous) && filter_var($previous, FILTER_VALIDATE_EMAIL)) {
                $pending = $previous;
                session()->set(['password_reset_pending_email' => $pending, 'password_reset_pending_at' => time()]);
            }
        }
        return $this->page($pending !== null ? 'confirm' : 'request');
    }
    public function resetForm()
    {
        if (!is_string(session('password_reset_email')) || !filter_var(session('password_reset_email'), FILTER_VALIDATE_EMAIL)) return $this->redirectTo('lupa-sandi');
        return $this->page('reset');
    }
    private function page(string $step)
    {
        return $this->response->setHeader('Cache-Control', 'no-store, private')->setHeader('Referrer-Policy', 'no-referrer')
            ->setBody(view('pages/password_reset', [
                'title' => $step === 'reset' ? 'Reset sandi' : ($step === 'confirm' ? 'Konfirmasi email pemulihan' : 'Lupa sandi'), 'step' => $step,
                'email' => is_string(session('password_reset_email')) ? session('password_reset_email') : '',
                'maskedEmail' => $step === 'confirm' ? $this->maskEmail((string)session('password_reset_pending_email')) : '',
                'message' => session()->getFlashdata('reset_message'), 'error' => session()->getFlashdata('reset_error'),
            ]));
    }
    private function maskEmail(string $email): string
    {
        [$local, $domain] = array_pad(explode('@', $email, 2), 2, '');
        return strtoupper(mb_substr($local, 0, 1)) . '****@' . $domain;
    }
    public function requestCode()
    {
        if (strtoupper($this->request->getMethod()) !== 'POST') return $this->response->setStatusCode(403);
        if ($this->request->getPost('change_identity') === '1') {
            session()->remove(['password_reset_pending_email', 'password_reset_pending_at', 'password_reset_email']);
            return $this->redirectTo('lupa-sandi');
        }
        if ($error = $this->captchaError()) {
            session()->setFlashdata('reset_error', $error);
            return $this->redirectTo('lupa-sandi');
        }

        if ($this->request->getPost('confirm_email') === '1') {
            $email = $this->pendingEmail();
            if ($email === null) {
                session()->setFlashdata('reset_error', 'Sesi konfirmasi berakhir. Masukkan email atau username kembali.');
                return $this->redirectTo('lupa-sandi');
            }
            try { $this->resets()->request($email, $this->request->getIPAddress()); }
            catch (\Throwable $e) {
                log_message('error', 'Password reset request storage failed. Check reset schema privately.');
                session()->setFlashdata('reset_error', 'Layanan reset sandi sedang terganggu. Silakan coba lagi nanti.');
                return $this->redirectTo('lupa-sandi');
            }
            session()->set('password_reset_email', $email);
            session()->remove(['password_reset_pending_email', 'password_reset_pending_at']);
            session()->setFlashdata('reset_message', 'Jika akun terdaftar dan batas permintaan belum tercapai, kode dikirim ke email pemulihan. Periksa juga folder spam.');
            return $this->redirectTo('reset-sandi');
        }

        $identity = $this->request->getPost('identity');
        if (!is_string($identity) || strlen(trim($identity)) > 254 || trim($identity) === '') {
            session()->setFlashdata('reset_error', 'Masukkan email atau username akun Anda.');
            return $this->redirectTo('lupa-sandi');
        }
        $identity = trim($identity);
        if (strpos($identity, '@') !== false && !filter_var($identity, FILTER_VALIDATE_EMAIL)) {
            session()->setFlashdata('reset_error', 'Format email belum sesuai.');
            return $this->redirectTo('lupa-sandi');
        }
        try { $email = $this->resets()->resolveCustomerEmail($identity, $this->request->getIPAddress()); }
        catch (\Throwable $e) {
            log_message('error', 'Password reset identifier lookup failed.');
            session()->setFlashdata('reset_error', 'Layanan reset sandi sedang terganggu. Silakan coba lagi nanti.');
            return $this->redirectTo('lupa-sandi');
        }
        if ($email === null) {
            session()->setFlashdata('reset_message', 'Jika data cocok dengan akun pelanggan, instruksi pemulihan akan ditampilkan. Periksa kembali email atau username yang dimasukkan.');
            return $this->redirectTo('lupa-sandi');
        }
        session()->set(['password_reset_pending_email' => $email, 'password_reset_pending_at' => time()]);
        session()->remove('password_reset_email');
        return $this->redirectTo('lupa-sandi');
    }
    public function complete()
    {
        if (strtoupper($this->request->getMethod()) !== 'POST') return $this->response->setStatusCode(403);
        $email = session('password_reset_email');
        if (!is_string($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) return $this->redirectTo('lupa-sandi');
        try {
            $this->resets()->reset($email, $this->request->getPost('otp'), $this->request->getPost('sandi'),
                $this->request->getPost('konfirmasi_sandi'), $this->request->getIPAddress());
        } catch (\DomainException $e) {
            session()->setFlashdata('reset_error', $e->getMessage());
            return $this->redirectTo('reset-sandi');
        } catch (\Throwable $e) {
            log_message('error', 'Password reset completion storage failed.');
            session()->setFlashdata('reset_error', 'Reset sandi belum berhasil. Silakan coba lagi nanti.');
            return $this->redirectTo('reset-sandi');
        }
        // Clear both authenticated and reset state, rotate ID, then ask the owner to sign in normally.
        session()->remove(array_keys(session()->get()));
        session()->regenerate(true);
        session()->setFlashdata('msg', 'Sandi berhasil diperbarui. Silakan masuk dengan sandi baru.');
        return $this->redirectTo('login');
    }
}
