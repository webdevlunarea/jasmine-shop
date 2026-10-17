<?php

namespace App\Controllers;

use App\Services\Auth\DisposableEmailPolicy;

class CustomerModerationAdmin extends BaseController
{
    public function index()
    {
        $db = db_connect();
        $expired = time();
        $users = $db->table('user')->select('email, waktu_otp')->where('role', '0')->where('active', '0')->where('waktu_otp <', $expired)->orderBy('waktu_otp', 'ASC')->limit(300)->get()->getResultArray();
        $protected = ['pemesanan' => ['email_cus'], 'retur_pengajuan' => ['email_cus'], 'preorder_barang' => ['email_customer'], 'rating' => ['email_cus'], 'point_history' => ['email_user'], 'voucher_redeem' => ['email_user']];
        foreach ($users as &$user) {
            $user['has_history'] = false;
            foreach ($protected as $table => $columns) {
                if (!$db->tableExists($table)) continue;
                $column = $columns[0];
                if (in_array($column, $db->getFieldNames($table), true) && $db->table($table)->where($column, $user['email'])->countAllResults() > 0) {
                    $user['has_history'] = true;
                    break;
                }
            }
        }
        unset($user);
        $domains = $db->tableExists('customer_blocked_email_domains')
            ? $db->table('customer_blocked_email_domains')->orderBy('domain')->get()->getResultArray() : [];
        return view('pages/customerModerationAdmin', [
            'title' => 'Akun & email spam', 'users' => $users, 'domains' => $domains,
            'domainTableReady' => $db->tableExists('customer_blocked_email_domains'),
            'msg' => session()->getFlashdata('msg'),
        ]);
    }

    public function saveDomain()
    {
        $domain = strtolower(trim((string) $this->request->getPost('domain')));
        $domain = preg_replace('/^\*@?/', '', $domain);
        if (!preg_match('/^(?=.{1,190}$)([a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z]{2,}$/', $domain)) {
            return $this->back('Masukkan domain email yang valid, misalnya mail.example.');
        }
        $db = db_connect();
        if (!$db->tableExists('customer_blocked_email_domains')) return $this->back('Jalankan migration domain email terlebih dahulu.');
        $db->table('customer_blocked_email_domains')->ignore(true)->insert(['domain' => $domain, 'is_active' => 1, 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')]);
        return $this->back('Domain email berhasil ditambahkan ke daftar blokir.');
    }

    public function removeDomain()
    {
        $id = (int) $this->request->getPost('id');
        $db = db_connect();
        if ($db->tableExists('customer_blocked_email_domains')) $db->table('customer_blocked_email_domains')->where('id', $id)->delete();
        return $this->back('Domain berhasil dihapus dari daftar blokir.');
    }

    public function deleteExpiredUnverified()
    {
        $emails = $this->request->getPost('emails');
        if (!is_array($emails) || !$emails) return $this->back('Pilih akun yang akan dihapus.');
        // The admin screen renders up to 300 eligible rows and its select-all
        // control submits that set; keep the server cap aligned with the UI.
        if (count($emails) > 300) return $this->back('Pilih maksimal 300 akun dalam satu proses.');
        $db = db_connect();
        $deleted = $skipped = 0;
        $protected = ['pemesanan' => 'email_cus', 'retur_pengajuan' => 'email_cus', 'preorder_barang' => 'email_customer', 'rating' => 'email_cus', 'point_history' => 'email_user', 'voucher_redeem' => 'email_user'];
        foreach (array_unique(array_map('strval', $emails)) as $email) {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $skipped++; continue; }
            $db->transBegin();
            try {
                $user = $db->table('user')->where(['email' => $email, 'role' => '0', 'active' => '0'])->where('waktu_otp <', time())->get()->getRowArray();
                if (!$user) { $db->transRollback(); $skipped++; continue; }
                $hasHistory = false;
                foreach ($protected as $table => $column) {
                    if ($db->tableExists($table) && in_array($column, $db->getFieldNames($table), true) && $db->table($table)->where($column, $email)->countAllResults() > 0) { $hasHistory = true; break; }
                }
                if ($hasHistory) { $db->transRollback(); $skipped++; continue; }
                // Hanya hapus data akun/aktivasi yang tidak memuat riwayat transaksi.
                foreach ([
                    'customer_usernames' => 'email_user', 'customer_password_reset_codes' => 'email_user',
                    'customer_password_sessions' => 'email_user', 'customer_addresses' => 'email_user',
                    'facebook_identities' => 'email_user', 'voucher_claimed' => 'email_user',
                    'gambar_user' => 'email_user', 'shipping_quotes' => 'email_user',
                ] as $table => $column) {
                    if ($db->tableExists($table) && in_array($column, $db->getFieldNames($table), true)) $db->table($table)->where($column, $email)->delete();
                }
                if ($db->tableExists('pembeli')) $db->table('pembeli')->where('email_user', $email)->delete();
                $db->table('user')->where('email', $email)->delete();
                if ($db->transStatus()) { $db->transCommit(); $deleted++; }
                else { $db->transRollback(); $skipped++; }
            } catch (\Throwable $e) {
                $db->transRollback();
                log_message('error', 'Penghapusan akun belum terverifikasi gagal. Email hash: {hash}', ['hash' => hash('sha256', strtolower($email))]);
                $skipped++;
            }
        }
        log_message('info', 'Pembersihan akun belum terverifikasi oleh admin. Terhapus: {deleted}; dilewati: {skipped}', ['deleted' => $deleted, 'skipped' => $skipped]);
        return $this->back("Selesai. {$deleted} akun dihapus; {$skipped} dilewati karena status berubah atau memiliki riwayat.");
    }

    private function back(string $message)
    {
        session()->setFlashdata('msg', $message);
        return redirect()->to('/customeradmin');
    }
}
