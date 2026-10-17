<?php

namespace App\Controllers;

use App\Services\Auth\FacebookOAuth;
use App\Services\Auth\FacebookCustomers;

trait FacebookLoginTrait
{
    private bool $facebookLinkFlow = false;
    protected function facebookOAuth(): FacebookOAuth { return new FacebookOAuth(); }
    protected function facebookCustomer(array $profile): array { return (new FacebookCustomers())->resolve($profile); }
    protected function facebookLinkCustomer(array $profile, string $email): array { return (new FacebookCustomers())->link($profile, $email); }

    protected function facebookAccountLinkState(): string
    {
        if (!$this->facebookOAuth()->ready()) return 'disabled';
        try { return (new FacebookCustomers())->isLinked((string)session('email')) ? 'linked' : 'available'; }
        catch (\Throwable $e) { return 'unavailable'; }
    }

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
        if ($this->facebookLinkFlow && session('isLogin') && session('email') !== 'tamu') {
            return $this->facebookRedirect(site_url('account'));
        }
        return $this->facebookRedirect(site_url('login') . ($target ? '?redirect=' . rawurlencode($target) : ''));
    }

    public function facebookLink()
    {
        $this->facebookLinkFlow = true;
        session()->remove(['facebook_oauth', 'facebook_link_nonce']);
        if (strtoupper($this->request->getMethod()) !== 'POST' || !session('isLogin')
            || session('email') === 'tamu' || !is_string(session('email'))
            || (string)session('role') !== '0' || (string)session('active') !== '1') {
            return $this->facebookFailure('Masuk ke akun pelanggan aktif terlebih dahulu untuk menautkan Facebook.');
        }
        $oauth = $this->facebookOAuth();
        if (!$oauth->ready()) return $this->facebookFailure('Login Facebook belum dikonfigurasi.');
        session()->regenerate(true);
        $state = bin2hex(random_bytes(32));
        $nonce = bin2hex(random_bytes(32));
        session()->set('facebook_link_nonce', $nonce);
        session()->set('facebook_oauth', [
            'state' => $state, 'created' => time(), 'redirect' => '/account', 'mode' => 'link',
            'email' => session('email'), 'link_nonce' => $nonce,
        ]);
        return $this->facebookRedirect($oauth->authorizationUrl($state));
    }

    public function facebookLogin()
    {
        $target = FacebookOAuth::redirectTarget($this->request->getGet('redirect'));
        if (session('isLogin') && session('email') !== 'tamu') return $this->facebookRedirect($this->facebookApplicationUrl($target));
        $oauth = $this->facebookOAuth();
        session()->remove(['facebook_oauth', 'facebook_link_nonce']);
        if (!$oauth->ready()) return $this->facebookFailure('Login Facebook belum dikonfigurasi.', $target);
        $state = bin2hex(random_bytes(32));
        session()->set('facebook_oauth', ['state' => $state, 'created' => time(), 'redirect' => $target]);
        return $this->facebookRedirect($oauth->authorizationUrl($state));
    }

    public function facebookCallback()
    {
        $pending = session()->get('facebook_oauth');
        $nonce = session()->get('facebook_link_nonce');
        session()->remove(['facebook_oauth', 'facebook_link_nonce']);
        $this->facebookLinkFlow = is_array($pending) && ($pending['mode'] ?? '') === 'link';
        $target = FacebookOAuth::redirectTarget($pending['redirect'] ?? '');
        if (!$this->facebookLinkFlow && session('isLogin') && session('email') !== 'tamu') return $this->facebookRedirect(site_url('/'));
        if ($this->facebookLinkFlow && (!session('isLogin') || (string)session('role') !== '0'
            || (string)session('active') !== '1' || !is_string(session('email'))
            || !hash_equals((string)($pending['email'] ?? ''), session('email'))
            || !is_string($nonce) || $nonce === '' || !hash_equals((string)($pending['link_nonce'] ?? ''), $nonce))) {
            return $this->facebookFailure('Sesi akun berubah. Ulangi penautan Facebook dari Akun Saya.');
        }
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
            $user = $this->facebookLinkFlow
                ? $this->facebookLinkCustomer($profile, (string)session('email'))
                : $this->facebookCustomer($profile);
        } catch (\DomainException $e) {
            return $this->facebookFailure($e->getMessage(), $target);
        } catch (\App\Services\Auth\FacebookAccountException $e) {
            log_message('error', 'Facebook account storage failed: stage={stage}; db_code={db_code}. Run php spark facebook:check.', [
                'stage' => $e->stage(), 'db_code' => $e->databaseCode(),
            ]);
            return $this->facebookFailure('Login Facebook belum tersedia. Silakan coba lagi nanti.', $target);
        } catch (\Throwable $e) {
            log_message('error', 'Facebook account lookup failed. Run php spark facebook:check.');
            return $this->facebookFailure('Login Facebook belum tersedia. Silakan coba lagi nanti.', $target);
        }
        if ((string)($user['role'] ?? '') !== '0' || (string)($user['active'] ?? '') !== '1') {
            return $this->facebookFailure('Akun belum aktif untuk login Facebook.', $target);
        }
        if ($this->facebookLinkFlow) {
            session()->regenerate(true);
            session()->setFlashdata('msg', 'Facebook berhasil ditautkan. Login Facebook berikutnya memakai akun, transaksi, dan poin yang sama.');
            return $this->facebookRedirect(site_url('account'));
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
