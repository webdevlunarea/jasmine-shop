<?php

namespace App\Services\Auth;

use CodeIgniter\Database\BaseConnection;

class FacebookCustomers
{
    private BaseConnection $db;

    public function __construct(?BaseConnection $db = null) { $this->db = $db ?? db_connect(); }

    public function isLinked(string $email): bool
    {
        return $this->db->table('facebook_identities')->where('email_user', $email)->countAllResults() > 0;
    }

    /** Called only after authenticated, CSRF-protected linking intent and a fresh Facebook OAuth flow. */
    public function link(array $profile, string $email): array
    {
        $id = $profile['id'] ?? '';
        if (!is_string($id) || !preg_match('/^\d{1,64}$/D', $id)) throw new \DomainException('Profil Facebook belum valid.');
        $user = $this->db->table('user')->where('email', $email)->get()->getRowArray();
        if (!$user || (string)$user['role'] !== '0' || (string)$user['active'] !== '1'
            || !$this->db->table('pembeli')->where('email_user', $email)->countAllResults()) {
            throw new \DomainException('Masuk ke akun pelanggan aktif terlebih dahulu untuk menautkan Facebook.');
        }
        $identity = $this->db->table('facebook_identities')->where('facebook_id', $id)->get()->getRowArray();
        if ($identity) {
            if (strcasecmp($identity['email_user'], $email) !== 0) throw new \DomainException('Facebook ini sudah tertaut ke akun lain. Tidak ada akun yang digabungkan.');
            $suspensions = new CustomerAccountSuspensions($this->db);
            if ($suspensions->isSuspended((string)$identity['email_user'])) throw new \DomainException($suspensions->userMessage((string)$identity['email_user']));
            return $user;
        }
        $facebookEmail = $profile['email'] ?? null;
        if (!is_string($facebookEmail) || !filter_var($facebookEmail, FILTER_VALIDATE_EMAIL)
            || strcasecmp(trim($facebookEmail), $email) !== 0) {
            throw new \DomainException('Gunakan Facebook dengan email yang sama seperti akun ini dan izinkan akses email.');
        }
        if ($this->isLinked($email)) throw new \DomainException('Akun ini sudah tertaut ke Facebook lain. Tidak ada tautan yang diganti.');
        // The unique Facebook ID and email indexes arbitrate concurrent link requests.
        // Never update a mapping, password, activation, provider, or customer record here.
        try {
            if (!$this->db->table('facebook_identities')->insert(['facebook_id' => $id, 'email_user' => $user['email']])) {
                throw new \RuntimeException();
            }
        } catch (\Throwable $e) {
            throw new FacebookAccountException('identity_insert', (int)($this->db->error()['code'] ?? 0));
        }
        return $user;
    }

    public function resolve(array $profile): array
    {
        $id = $profile['id'] ?? '';
        if (!is_string($id) || !preg_match('/^\d{1,64}$/D', $id)) throw new \DomainException('Profil Facebook belum valid.');
        $identity = $this->db->table('facebook_identities')->where('facebook_id', $id)->get()->getRowArray();
        if ($identity) {
            $user = $this->db->table('user')->where('email', $identity['email_user'])->get()->getRowArray();
            if (!$user || (string)$user['role'] !== '0' || (string)$user['active'] !== '1') {
                throw new \DomainException('Akun belum aktif. Silakan masuk melalui email atau hubungi admin.');
            }
            $suspensions = new CustomerAccountSuspensions($this->db);
            if ($suspensions->isSuspended((string)$user['email'])) throw new \DomainException($suspensions->userMessage((string)$user['email']));
            return $user;
        }
        $email = $profile['email'] ?? '';
        if (!is_string($email) || strlen($email) > 254 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \DomainException('Email Facebook belum tersedia. Izinkan akses email atau daftar melalui email.');
        }
        $email = strtolower(trim($email));
        $suspensions = new CustomerAccountSuspensions($this->db);
        if ($suspensions->isSuspended($email)) throw new \DomainException($suspensions->userMessage($email));
        if ($this->db->table('user')->where('email', $email)->countAllResults() > 0) {
            // Facebook email is not proof of ownership of an existing local account.
            throw new \DomainException('Email sudah terdaftar. Masuk melalui email atau Google, lalu buka Akun Saya dan pilih Tautkan Facebook.');
        }
        $user = [
            'email' => $email, 'sandi' => password_hash(bin2hex(random_bytes(32)), PASSWORD_DEFAULT),
            'role' => '0', 'active' => '1', 'otp' => '0', 'waktu_otp' => '0', 'auth_provider' => 'facebook',
        ];
        $name = is_string($profile['name'] ?? null) ? trim($profile['name']) : '';
        if (!$this->db->transBegin()) throw new FacebookAccountException('transaction_start');
        $stage = 'user_insert';
        try {
            if (!$this->db->table('user')->insert($user)) throw new \RuntimeException();
            $stage = 'buyer_insert';
            $ok = $this->db->table('pembeli')->insert([
                    'email_user' => $email, 'nama' => mb_substr($name ?: 'Pelanggan Lunarea', 0, 100),
                    'nohp' => '', 'alamat' => '[]', 'wishlist' => '[]', 'keranjang' => '[]',
                    'transaksi' => '[]', 'poin' => '[]', 'tier' => json_encode(['label' => 'bronze', 'data' => []]),
                    'foto' => '/imguser/ZGVmYXVsdA==',
                ]);
            if (!$ok) throw new \RuntimeException();
            $stage = 'identity_insert';
            $ok = $this->db->table('facebook_identities')->insert(['facebook_id' => $id, 'email_user' => $email]);
            if (!$ok || !$this->db->transStatus()) throw new \RuntimeException('Facebook account creation failed');
            $stage = 'commit';
            if (!$this->db->transCommit()) throw new \RuntimeException();
        } catch (\Throwable $e) {
            $databaseCode = (int)($this->db->error()['code'] ?? 0);
            $this->db->transRollback();
            throw new FacebookAccountException($stage, $databaseCode);
        }
        return $user;
    }
}
