<?php

namespace App\Filters;

use App\Services\Auth\PasswordSessionVersions;
use App\Services\Auth\CustomerAccountSuspensions;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class PasswordSessionFilter implements FilterInterface
{
    protected function versions(): PasswordSessionVersions { return new PasswordSessionVersions(); }

    public function before(RequestInterface $request, $arguments = null)
    {
        // Native API requests authenticate with bearer tokens, never browser sessions.
        if (strpos(trim($request->getUri()->getPath(), '/'), 'api/') === 0) return;
        if (!$request instanceof IncomingRequest || !session('isLogin') || !is_string(session('email')) || session('email') === 'tamu') return;
        try {
            $suspended = (string)session('role') === '0' && (new CustomerAccountSuspensions())->isSuspended((string)session('email'));
            $valid = !$suspended && $this->versions()->matches(session('email'), session('password_session_version'));
        } catch (\Throwable $e) { $valid = false; $suspended = false; }
        if (!$valid) {
            $notice = null;
            if ($suspended) try { $notice = (new CustomerAccountSuspensions())->userMessage((string)session('email')); }
            catch (\Throwable $e) { $notice = 'Akun Anda sedang dibatasi. Hubungi Lunarea melalui halaman bantuan.'; }
            session()->remove(array_keys(session()->get()));
            session()->regenerate(true);
            if ($suspended) {
                session()->setFlashdata('msg', $notice);
                return redirect()->to(site_url('akun-dibatasi'));
            }
            return redirect()->to(site_url('login'));
        }
    }
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) {}
}
