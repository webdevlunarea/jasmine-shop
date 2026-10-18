<?php

namespace App\Filters;

use App\Services\Auth\PasswordSessionVersions;
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
        try { $valid = $this->versions()->matches(session('email'), session('password_session_version')); }
        catch (\Throwable $e) { $valid = false; }
        if (!$valid) {
            session()->remove(array_keys(session()->get()));
            session()->regenerate(true);
            return redirect()->to(site_url('login'));
        }
    }
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) {}
}
