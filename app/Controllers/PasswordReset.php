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
    public function requestForm() { return $this->page('request'); }
    public function resetForm()
    {
        if (!is_string(session('password_reset_email')) || !filter_var(session('password_reset_email'), FILTER_VALIDATE_EMAIL)) return $this->redirectTo('lupa-sandi');
        return $this->page('reset');
    }
    private function page(string $step)
    {
        return $this->response->setHeader('Cache-Control', 'no-store, private')->setHeader('Referrer-Policy', 'no-referrer')
            ->setBody(view('pages/password_reset', [
                'title' => $step === 'request' ? 'Lupa sandi' : 'Reset sandi', 'step' => $step,
                'email' => is_string(session('password_reset_email')) ? session('password_reset_email') : '',
                'message' => session()->getFlashdata('reset_message'), 'error' => session()->getFlashdata('reset_error'),
            ]));
    }
    public function requestCode()
    {
        if (strtoupper($this->request->getMethod()) !== 'POST') return $this->response->setStatusCode(403);
        $email = $this->request->getPost('email');
        if (!is_string($email) || strlen($email) > 254 || !filter_var(trim($email), FILTER_VALIDATE_EMAIL)) {
            session()->setFlashdata('reset_error', 'Masukkan alamat email akun yang valid.');
            return $this->redirectTo('lupa-sandi');
        }
        if ($error = $this->captchaError()) {
            session()->setFlashdata('reset_error', $error);
            return $this->redirectTo('lupa-sandi');
        }
        $email = strtolower(trim($email));
        try { $this->resets()->request($email, $this->request->getIPAddress()); }
        catch (\Throwable $e) {
            log_message('error', 'Password reset request storage failed. Check reset schema privately.');
            session()->setFlashdata('reset_error', 'Layanan reset sandi sedang terganggu. Silakan coba lagi nanti.');
            return $this->redirectTo('lupa-sandi');
        }
        session()->set('password_reset_email', $email);
        session()->setFlashdata('reset_message', 'Jika email terdaftar sebagai akun pelanggan dan batas permintaan belum tercapai, kode dikirim ke email tersebut. Periksa juga folder spam.');
        return $this->redirectTo('reset-sandi');
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
