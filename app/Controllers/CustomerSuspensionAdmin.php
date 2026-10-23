<?php

namespace App\Controllers;

use App\Services\Auth\CustomerAccountSuspensions;

class CustomerSuspensionAdmin extends BaseController
{
    public function index()
    {
        $db = db_connect();
        $ready = $db->tableExists('customer_account_suspensions') && $db->tableExists('customer_account_suspension_events')
            && $db->fieldExists('user_message', 'customer_account_suspensions') && $db->fieldExists('expires_at', 'customer_account_suspensions')
            && $db->fieldExists('user_message', 'customer_account_suspension_events') && $db->fieldExists('expires_at', 'customer_account_suspension_events');
        return view('pages/customerSuspensionAdmin', [
            'title' => 'Suspend akun pelanggan',
            'ready' => $ready,
            'suspended' => $ready ? $db->table('customer_account_suspensions')->groupStart()
                ->where('expires_at', null)->orWhere('expires_at >', gmdate('Y-m-d H:i:s'))->groupEnd()
                ->orderBy('suspended_at', 'DESC')->limit(200)->get()->getResultArray() : [],
            'events' => $ready ? $db->table('customer_account_suspension_events')->orderBy('created_at', 'DESC')->limit(100)->get()->getResultArray() : [],
            'msg' => session()->getFlashdata('msg'),
        ]);
    }

    public function suspend()
    {
        try {
            (new CustomerAccountSuspensions())->suspend(
                (string)$this->request->getPost('email'),
                (string)$this->request->getPost('reason'),
                (string)$this->request->getPost('user_message'),
                (string)$this->request->getPost('duration'),
                (string)session('email')
            );
            return $this->back('Penangguhan disimpan. Sesi dicabut; pesan dan masa berlaku tercatat.');
        } catch (\DomainException | \InvalidArgumentException $e) {
            return $this->back($e->getMessage());
        } catch (\Throwable $e) {
            log_message('error', 'Admin suspend customer failed: ' . get_class($e));
            return $this->back('Suspend belum dapat disimpan. Periksa tabel database dan coba lagi.');
        }
    }

    public function reinstate()
    {
        try {
            (new CustomerAccountSuspensions())->reinstate(
                (string)$this->request->getPost('email'),
                (string)session('email')
            );
            return $this->back('Penangguhan dicabut. Pengguna dapat masuk kembali; notifikasi perlu diaktifkan ulang dari perangkat.');
        } catch (\DomainException | \InvalidArgumentException $e) {
            return $this->back($e->getMessage());
        } catch (\Throwable $e) {
            log_message('error', 'Admin reinstate customer failed: ' . get_class($e));
            return $this->back('Penangguhan belum dapat dicabut. Periksa database dan coba lagi.');
        }
    }

    private function back(string $message)
    {
        session()->setFlashdata('msg', $message);
        return redirect()->to('/customersuspensionadmin');
    }
}
