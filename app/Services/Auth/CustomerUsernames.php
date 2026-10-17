<?php

namespace App\Services\Auth;

use CodeIgniter\Database\BaseConnection;

final class CustomerUsernames
{
    private BaseConnection $db;

    public function __construct(?BaseConnection $db = null) { $this->db = $db ?? db_connect(); }

    public function current(string $email): ?string
    {
        $row = $this->db->table('customer_usernames')->where('email_user', $email)->get()->getRowArray();
        return $row ? (string)$row['username'] : null;
    }

    public function save(string $email, $value): string
    {
        $username = UsernamePolicy::normalize($value);
        if ($username === null) throw new \DomainException('Username harus 3–30 karakter, diawali huruf, hanya berisi huruf, angka, atau garis bawah. Nama khusus seperti admin tidak tersedia.');
        $user = $this->db->table('user')->where('email', $email)->get()->getRowArray();
        if (!$user || (string)$user['role'] !== '0' || (string)$user['active'] !== '1') {
            throw new \DomainException('Masuk ke akun pelanggan aktif sebelum mengatur username.');
        }
        $taken = $this->db->table('customer_usernames')->where('username', $username)->get()->getRowArray();
        if ($taken && strcasecmp($taken['email_user'], $user['email']) !== 0) {
            throw new \DomainException('Username sudah dipakai. Silakan pilih username lain.');
        }
        $current = $this->current($user['email']);
        if ($current === $username) return $username;
        try {
            // Both the username PK and owner UNIQUE index are required. A pre-check alone is not sufficient.
            $builder = $this->db->table('customer_usernames');
            $ok = $current === null
                ? $builder->insert(['username' => $username, 'email_user' => $user['email']])
                : $builder->where('email_user', $user['email'])->update(['username' => $username]);
            if (!$ok) throw new \RuntimeException();
        } catch (\Throwable $e) {
            $code = (int)($this->db->error()['code'] ?? 0);
            if (in_array($code, [19, 1062], true)) {
                throw new \DomainException('Username sudah dipakai atau akun baru diperbarui. Muat ulang dan pilih username lain.');
            }
            throw new \RuntimeException('Username storage unavailable');
        }
        return $username;
    }
}
