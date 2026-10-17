<?php

namespace App\Services\Auth;

use CodeIgniter\Database\BaseConnection;

class FacebookCustomers
{
    private BaseConnection $db;

    public function __construct(?BaseConnection $db = null) { $this->db = $db ?? db_connect(); }

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
            return $user;
        }
        $email = $profile['email'] ?? '';
        if (!is_string($email) || strlen($email) > 254 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \DomainException('Email Facebook belum tersedia. Izinkan akses email atau daftar melalui email.');
        }
        $email = strtolower(trim($email));
        if ($this->db->table('user')->where('email', $email)->countAllResults() > 0) {
            // Facebook email is not proof of ownership of an existing local account.
            throw new \DomainException('Email sudah terdaftar. Silakan masuk menggunakan metode akun sebelumnya.');
        }
        $user = [
            'email' => $email, 'sandi' => password_hash(bin2hex(random_bytes(32)), PASSWORD_DEFAULT),
            'role' => '0', 'active' => '1', 'otp' => '0', 'waktu_otp' => '0', 'auth_provider' => 'facebook',
        ];
        $name = is_string($profile['name'] ?? null) ? trim($profile['name']) : '';
        $this->db->transBegin();
        try {
            $ok = $this->db->table('user')->insert($user)
                && $this->db->table('pembeli')->insert([
                    'email_user' => $email, 'nama' => mb_substr($name ?: 'Pelanggan Lunarea', 0, 100),
                    'nohp' => '', 'alamat' => '[]', 'wishlist' => '[]', 'keranjang' => '[]',
                    'transaksi' => '[]', 'poin' => '[]', 'tier' => json_encode(['label' => 'bronze', 'data' => []]),
                    'foto' => '/imguser/ZGVmYXVsdA==',
                ])
                && $this->db->table('facebook_identities')->insert(['facebook_id' => $id, 'email_user' => $email]);
            if (!$ok || !$this->db->transStatus()) throw new \RuntimeException('Facebook account creation failed');
            $this->db->transCommit();
        } catch (\Throwable $e) {
            $this->db->transRollback();
            throw new \RuntimeException('Facebook account creation failed');
        }
        return $user;
    }
}
