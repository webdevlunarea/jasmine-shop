<?php

namespace App\Controllers;

use App\Services\Auth\CustomerPasswordChange as PasswordChangeService;
use App\Services\Auth\PasswordSessionVersions;
use DomainException;

final class CustomerPassword extends BaseController
{
    public function change()
    {
        $email = session('email');
        if (!session('isLogin') || (string) session('role') !== '0'
            || (string) session('active') !== '1' || !is_string($email)
            || $email === 'tamu' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return redirect()->to('/login');
        }

        $throttler = service('throttler');
        $ipKey = hash('sha256', (string) $this->request->getIPAddress());
        $identityKey = hash('sha256', strtolower(trim($email)));
        if (!$throttler->check('web-password-change-ip-' . $ipKey, 5, 900)
            || !$throttler->check('web-password-change-account-' . $identityKey, 3, 900)) {
            session()->setFlashdata('customer_password_error', 'Terlalu banyak percobaan. Coba lagi dalam 15 menit.');
            return redirect()->to('/account#account-security');
        }

        $current = $this->request->getPost('current_password');
        $password = $this->request->getPost('password');
        $confirmation = $this->request->getPost('password_confirmation');
        if (!is_string($current) || !is_string($password) || !is_string($confirmation)) {
            session()->setFlashdata('customer_password_error', 'Data sandi belum valid. Periksa kembali isian Anda.');
            return redirect()->to('/account#account-security');
        }

        try {
            (new PasswordChangeService())->change($email, $current, $password, $confirmation);
            session()->set('password_session_version', (new PasswordSessionVersions())->current($email));
            session()->regenerate(true);
            session()->setFlashdata('customer_password_message', 'Sandi berhasil diperbarui.');
        } catch (DomainException $error) {
            session()->setFlashdata('customer_password_error', $error->getMessage());
        } catch (\Throwable $error) {
            log_message('error', 'Website customer password change failed: ' . get_class($error));
            session()->setFlashdata('customer_password_error', 'Sandi belum berhasil diperbarui. Coba lagi nanti.');
        }

        return redirect()->to('/account#account-security');
    }
}
