<?php

namespace App\Controllers;

use App\Services\Auth\CustomerUsernames;

class CustomerUsername extends BaseController
{
    protected function usernames(): CustomerUsernames { return new CustomerUsernames(); }

    public function save()
    {
        if (strtoupper($this->request->getMethod()) !== 'POST' || !session('isLogin')
            || !is_string(session('email')) || session('email') === 'tamu'
            || (string)session('role') !== '0' || (string)session('active') !== '1') {
            return $this->response->setStatusCode(403);
        }
        try {
            $this->usernames()->save(session('email'), $this->request->getPost('username'));
            session()->setFlashdata('username_message', 'Username berhasil disimpan. Gunakan username atau email dengan sandi akun yang sama.');
        } catch (\DomainException $e) {
            session()->setFlashdata('username_error', $e->getMessage());
        } catch (\Throwable $e) {
            session()->setFlashdata('username_error', 'Pengaturan username belum tersedia. Hubungi admin untuk memeriksa tabel customer_usernames.');
        }
        return redirect()->to(site_url('account') . '#account-username');
    }
}
