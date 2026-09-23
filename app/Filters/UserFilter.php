<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class UserFilter implements FilterInterface
{
    private function currentTarget(RequestInterface $request): string
    {
        $uri = $request->getUri();
        $target = '/' . ltrim($uri->getPath(), '/');
        $query = $uri->getQuery();
        return $query ? $target . '?' . $query : $target;
    }

    public function before(RequestInterface $request, $arguments = null)
    {
        $target = $this->currentTarget($request);
        if (!session("isLogin")) {
            return redirect()->to(site_url('login') . '?redirect=' . rawurlencode($target));
        }
        if (session("email") === "tamu") {
            session()->remove(['email', 'role', 'alamat', 'wishlist', 'keranjang', 'isLogin', 'active', 'transaksi', 'nama', 'nohp', 'submitEmail', 'voucher', 'tgl_lahir', 'tier', 'poin', 'usepoin', 'foto', 'gantiKaca', 'redirect_after_verify']);
            session()->setFlashdata('msg', 'Checkout sekarang wajib memakai akun. Silakan masuk atau daftar terlebih dahulu.');
            return redirect()->to(site_url('login') . '?redirect=' . rawurlencode($target));
        }
        if (session("active") != "1") {
            session()->set('redirect_after_verify', $target);
            return redirect()->to(site_url('verify'));
        }
        if (session("role") != "0") {
            return redirect()->to(site_url('/'));
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do something here
    }
}
