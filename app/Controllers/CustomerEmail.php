<?php

namespace App\Controllers;

use App\Services\Auth\CustomerEmailChanges;

class CustomerEmail extends BaseController
{
    private function service(): CustomerEmailChanges { return new CustomerEmailChanges(); }
    private function guard(): bool
    {
        return session('isLogin') && is_string(session('email')) && session('email') !== 'tamu' && (string)session('role') === '0' && (string)session('active') === '1';
    }
    public function requestChange()
    {
        if (!$this->guard()) return redirect()->to('/login');
        try {
            $this->service()->request((string)session('email'), (string)$this->request->getPost('current_password'), (string)$this->request->getPost('new_email'));
            session()->setFlashdata('email_change_message', 'Kode verifikasi dikirim ke email baru. Masukkan kode sebelum 10 menit.');
        } catch (\DomainException $e) { session()->setFlashdata('email_change_error', $e->getMessage()); }
        catch (\Throwable $e) { session()->setFlashdata('email_change_error', 'Permintaan belum dapat diproses. Coba lagi nanti atau hubungi layanan pelanggan.'); }
        return redirect()->to('/account#account-email');
    }
    public function confirmChange()
    {
        if (!$this->guard()) return redirect()->to('/login');
        try {
            $old = (string)session('email');
            $new = $this->service()->confirm($old, (string)$this->request->getPost('current_password'), (string)$this->request->getPost('verification_code'));
            session()->set('email', $new);
            session()->regenerate(true);
            session()->set('password_session_version', (new \App\Services\Auth\PasswordSessionVersions())->current($new));
            session()->setFlashdata('email_change_message', 'Email akun berhasil diperbarui. Notifikasi telah dikirim ke alamat email sebelumnya.');
        } catch (\DomainException $e) { session()->setFlashdata('email_change_error', $e->getMessage()); }
        catch (\Throwable $e) { session()->setFlashdata('email_change_error', 'Email belum berhasil diperbarui. Data akun tetap aman; coba lagi atau hubungi layanan pelanggan.'); }
        return redirect()->to('/account#account-email');
    }
}
