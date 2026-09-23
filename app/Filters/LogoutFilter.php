<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class LogoutFilter implements FilterInterface
{
    private function cleanRedirectTarget($target): string
    {
        $target = trim((string)$target);
        if ($target === '' || $target[0] !== '/' || strpos($target, '//') === 0 || preg_match('/^https?:\\/\\//i', $target)) {
            return '';
        }
        return $target;
    }

    public function before(RequestInterface $request, $arguments = null)
    {
        if (session("email") === "tamu") {
            session()->remove(['email', 'role', 'alamat', 'wishlist', 'keranjang', 'isLogin', 'active', 'transaksi', 'nama', 'nohp', 'submitEmail', 'voucher', 'tgl_lahir', 'tier', 'poin', 'usepoin', 'foto', 'gantiKaca', 'redirect_after_verify']);
            return;
        }

        if (session("isLogin")) {
            $redirect = $this->cleanRedirectTarget($request->getGet('redirect'));
            return redirect()->to(site_url($redirect ?: '/'));
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do something here
    }
}
