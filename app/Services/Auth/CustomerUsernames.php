<?php

namespace App\Services\Auth;

use CodeIgniter\Database\BaseConnection;

final class CustomerUsernames
{
    private BaseConnection $db;

    public function __construct(?BaseConnection $db = null) { $this->db = $db ?? db_connect(); }

    private function formatIndonesianDate(\DateTimeImmutable $date): string
    {
        $months = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        return $date->format('j') . ' ' . $months[(int)$date->format('n')] . ' ' . $date->format('Y');
    }

    public function current(string $email): ?string
    {
        $row = $this->db->table('customer_usernames')->where('email_user', $email)->get()->getRowArray();
        return $row ? (string)$row['username'] : null;
    }

    public function settings(string $email): array
    {
        $row = $this->db->table('customer_usernames')->where('email_user', $email)->get()->getRowArray();
        $nextChangeAt = null;
        if ($row && !empty($row['changed_at'])) {
            $changedAt = new \DateTimeImmutable((string)$row['changed_at'], new \DateTimeZone('UTC'));
            $nextChangeAt = $changedAt->modify('+6 months');
        }
        $now = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
        $displayDate = $nextChangeAt
            ? $this->formatIndonesianDate($nextChangeAt->setTimezone(new \DateTimeZone('Asia/Jakarta')))
            : null;

        return [
            'username' => $row ? (string)$row['username'] : '',
            'can_change' => !$nextChangeAt || $now >= $nextChangeAt,
            'next_change_at' => $nextChangeAt ? $nextChangeAt->format('Y-m-d H:i:s') : null,
            'next_change_label' => $displayDate,
        ];
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
        $currentRow = $this->db->table('customer_usernames')->where('email_user', $user['email'])->get()->getRowArray();
        $current = $currentRow ? (string)$currentRow['username'] : null;
        if ($current === $username) return $username;

        $now = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
        if ($currentRow && !empty($currentRow['changed_at'])) {
            $nextChangeAt = (new \DateTimeImmutable((string)$currentRow['changed_at'], new \DateTimeZone('UTC')))->modify('+6 months');
            if ($now < $nextChangeAt) {
                $availableDate = $this->formatIndonesianDate($nextChangeAt->setTimezone(new \DateTimeZone('Asia/Jakarta')));
                throw new \DomainException("Username hanya dapat diubah setiap 6 bulan. Anda dapat mengubahnya kembali mulai {$availableDate}.");
            }
        }
        $changedAt = $now->format('Y-m-d H:i:s');
        try {
            // Both the username PK and owner UNIQUE index are required. A pre-check alone is not sufficient.
            $builder = $this->db->table('customer_usernames');
            $ok = $current === null
                ? $builder->insert(['username' => $username, 'email_user' => $user['email'], 'changed_at' => $changedAt])
                : $builder->where('email_user', $user['email'])->update(['username' => $username, 'changed_at' => $changedAt]);
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
