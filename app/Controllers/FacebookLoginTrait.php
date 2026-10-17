<?php

namespace App\Controllers;

use App\Services\Auth\FacebookOAuth;
use App\Services\Auth\FacebookCustomers;

trait FacebookLoginTrait
{
    protected function facebookOAuth(): FacebookOAuth { return new FacebookOAuth(); }
    protected function facebookCustomer(array $profile): array { return (new FacebookCustomers())->resolve($profile); }

    private function facebookApplicationUrl(string $target): string
    {
        $baseURL = site_url('/');
        $path = FacebookOAuth::applicationPath($target, $baseURL);
        $basePath = rtrim((string)parse_url($baseURL, PHP_URL_PATH), '/');
        return site_url($basePath === '' ? $path : substr($path, strlen($basePath)));
    }

    private function facebookRedirect(string $url)
    {
        return redirect()->to($url)
            ->removeHeader('Cache-Control')
            ->setHeader('Cache-Control', 'no-store, private')
            ->setHeader('Pragma', 'no-cache')
            ->setHeader('Referrer-Policy', 'no-referrer');
    }

    private function facebookFailure(string $message, string $target = '')
    {
        session()->setFlashdata('msg', $message);
        return $this->facebookRedirect(site_url('login') . ($target ? '?redirect=' . rawurlencode($target) : ''));
    }

    public function facebookLogin()
    {
        $target = FacebookOAuth::redirectTarget($this->request->getGet('redirect'));
        if (session('isLogin') && session('email') !== 'tamu') return $this->facebookRedirect($this->facebookApplicationUrl($target));
        $oauth = $this->facebookOAuth();
        session()->remove('facebook_oauth');
        if (!$oauth->ready()) return $this->facebookFailure('Login Facebook belum dikonfigurasi.', $target);
        $state = bin2hex(random_bytes(32));
        session()->set('facebook_oauth', ['state' => $state, 'created' => time(), 'redirect' => $target]);
        return $this->facebookRedirect($oauth->authorizationUrl($state));
    }

    public function facebookCallback()
    {
        $pending = session()->get('facebook_oauth');
        session()->remove('facebook_oauth');
        $target = FacebookOAuth::redirectTarget($pending['redirect'] ?? '');
        if (session('isLogin') && session('email') !== 'tamu') return $this->facebookRedirect(site_url('/'));
        $state = $this->request->getGet('state');
        $code = $this->request->getGet('code');
        if (!is_array($pending) || !is_string($state) || $state === ''
            || !hash_equals((string)($pending['state'] ?? ''), $state)
            || time() - ($pending['created'] ?? 0) > 600 || ($pending['created'] ?? 0) > time()) {
            return $this->facebookFailure('Sesi login Facebook berakhir. Silakan coba lagi.', $target);
        }
        if ($this->request->getGet('error') !== null) return $this->facebookFailure('Login Facebook dibatalkan. Silakan coba lagi.', $target);
        $oauth = $this->facebookOAuth();
        if (!$oauth->ready() || !is_string($code) || $code === '' || strlen($code) > 4096) {
            return $this->facebookFailure('Login Facebook gagal. Silakan coba lagi.', $target);
        }
        try {
            $profile = $oauth->profile($code);
        } catch (\Throwable $e) {
            return $this->facebookFailure('Login Facebook gagal mengambil profil. Silakan coba lagi.', $target);
        }
        try {
            $user = $this->facebookCustomer($profile);
        } catch (\DomainException $e) {
            return $this->facebookFailure($e->getMessage(), $target);
        } catch (\Throwable $e) {
            return $this->facebookFailure('Login Facebook belum tersedia. Silakan coba lagi nanti.', $target);
        }
        if ((string)($user['role'] ?? '') !== '0' || (string)($user['active'] ?? '') !== '1') {
            return $this->facebookFailure('Akun belum aktif untuk login Facebook.', $target);
        }
        session()->regenerate(true);
        session()->remove(['voucher', 'submitEmail', 'redirect_after_verify', 'usepoin', 'gantiKaca']);
        if (!$this->loginUserSession($user)) return $this->facebookFailure('Data pembeli belum lengkap. Hubungi admin.', $target);
        $this->claimMissingAutoVouchersForEmail((string)$user['email']);
        $this->prepareLoginVoucherNotification((string)$user['email']);
        session()->setFlashdata('msg', 'Berhasil masuk dengan Facebook.');
        $destination = FacebookOAuth::applicationPath($target, site_url('/'));
        return $this->facebookRedirect(site_url('hapuslocalstorage/' . base64_encode($destination)));
    }
}
