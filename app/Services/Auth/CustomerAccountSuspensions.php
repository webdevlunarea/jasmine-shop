<?php

namespace App\Services\Auth;

use CodeIgniter\Database\BaseConnection;
use RuntimeException;

class CustomerAccountSuspensions
{
    private BaseConnection $db;
    private ?bool $stateTableReady = null;
    public function __construct(?BaseConnection $db = null) { $this->db = $db ?? db_connect(); }

    public function isSuspended(string $email): bool
    {
        $email = strtolower(trim($email));
        if ($email === '') return false;
        if (!$this->stateTableReady()) return false;
        return $this->db->table('customer_account_suspensions')->where('email_user', $email)
            ->groupStart()->where('expires_at', null)->orWhere('expires_at >', gmdate('Y-m-d H:i:s'))->groupEnd()
            ->countAllResults() > 0;
    }

    public function userMessage(string $email): string
    {
        $email = strtolower(trim($email));
        if ($email === '' || !$this->stateTableReady()) return 'Akun Anda sedang dibatasi. Hubungi Lunarea melalui halaman bantuan.';
        $row = $this->db->table('customer_account_suspensions')->select('user_message')->where('email_user', $email)
            ->groupStart()->where('expires_at', null)->orWhere('expires_at >', gmdate('Y-m-d H:i:s'))->groupEnd()
            ->get()->getRowArray();
        return trim((string)($row['user_message'] ?? '')) ?: 'Akun Anda sedang dibatasi. Hubungi Lunarea melalui halaman bantuan.';
    }

    private function stateTableReady(): bool
    {
        if ($this->stateTableReady === null) $this->stateTableReady = $this->db->tableExists('customer_account_suspensions');
        return $this->stateTableReady;
    }

    public function suspend(string $email, string $reason, string $message, string $duration, string $adminEmail): void
    {
        $email = strtolower(trim($email));
        $reason = trim($reason);
        $message = trim($message);
        $adminEmail = strtolower(trim($adminEmail));
        $durations = ['24h' => 24, '7d' => 168, '30d' => 720, 'permanent' => null];
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($reason) < 10 || mb_strlen($reason) > 500
            || mb_strlen($message) < 5 || mb_strlen($message) > 250 || !array_key_exists($duration, $durations)
            || !filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Email, alasan, pesan untuk pengguna, atau durasi belum valid.');
        }
        $this->requireTables();
        if (!$this->db->transBegin()) throw new RuntimeException('Suspend tidak dapat dimulai.');
        try {
            $user = $this->db->query('SELECT email, role, active FROM user WHERE email = ? FOR UPDATE', [$email])->getRowArray();
            if (!$user || (string)$user['role'] !== '0' || (string)$user['active'] !== '1') {
                throw new \DomainException('Akun pelanggan aktif tidak ditemukan.');
            }
            $now = gmdate('Y-m-d H:i:s');
            $expiresAt = $durations[$duration] === null ? null : gmdate('Y-m-d H:i:s', time() + ($durations[$duration] * 3600));
            $existing = $this->db->table('customer_account_suspensions')->where('email_user', $email)->get()->getRowArray();
            $data = ['reason' => $reason, 'user_message' => $message, 'expires_at' => $expiresAt,
                'suspended_by' => $adminEmail, 'suspended_at' => $now, 'updated_at' => $now];
            if ($existing) $ok = $this->db->table('customer_account_suspensions')->where('email_user', $email)->update($data);
            else $ok = $this->db->table('customer_account_suspensions')->insert(['email_user' => $email] + $data + ['created_at' => $now]);
            if (!$ok) throw new RuntimeException('Status suspend tidak dapat disimpan.');
            if (!$this->db->table('customer_account_suspension_events')->insert([
                'email_user' => $email, 'action' => 'suspended', 'reason' => $reason,
                'user_message' => $message, 'expires_at' => $expiresAt,
                'admin_email' => $adminEmail, 'created_at' => $now,
            ])) throw new RuntimeException('Riwayat suspend tidak dapat disimpan.');

            $this->invalidateCustomerAccess($email, $now);
            if (!$this->db->transStatus() || !$this->db->transCommit()) throw new RuntimeException('Suspend tidak berhasil disimpan.');
        } catch (\Throwable $e) {
            if ((int)$this->db->transDepth > 0) $this->db->transRollback();
            throw $e;
        }
    }

    public function reinstate(string $email, string $adminEmail): void
    {
        $email = strtolower(trim($email));
        $adminEmail = strtolower(trim($adminEmail));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Email akun tidak valid.');
        }
        $this->requireTables();
        if (!$this->db->transBegin()) throw new RuntimeException('Pemulihan akun tidak dapat dimulai.');
        try {
            $row = $this->db->table('customer_account_suspensions')->where('email_user', $email)->get()->getRowArray();
            if (!$row) throw new \DomainException('Akun ini tidak sedang ditangguhkan.');
            $now = gmdate('Y-m-d H:i:s');
            if (!$this->db->table('customer_account_suspension_events')->insert([
                'email_user' => $email, 'action' => 'reinstated', 'reason' => null,
                'user_message' => null, 'expires_at' => null, 'admin_email' => $adminEmail, 'created_at' => $now,
            ])) throw new RuntimeException('Riwayat pemulihan tidak dapat disimpan.');
            if (!$this->db->table('customer_account_suspensions')->where('email_user', $email)->delete()) {
                throw new RuntimeException('Status suspend tidak dapat dihapus.');
            }
            if (!$this->db->transStatus() || !$this->db->transCommit()) throw new RuntimeException('Pemulihan akun tidak berhasil disimpan.');
        } catch (\Throwable $e) {
            if ((int)$this->db->transDepth > 0) $this->db->transRollback();
            throw $e;
        }
    }

    /** Remove expired active rows and preserve an auditable expiry event. Safe to run repeatedly. */
    public function expireDue(int $limit = 200): int
    {
        $this->requireTables();
        $now = gmdate('Y-m-d H:i:s');
        $rows = $this->db->table('customer_account_suspensions')->where('expires_at <=', $now)
            ->orderBy('expires_at', 'ASC')->limit(max(1, min(1000, $limit)))->get()->getResultArray();
        $expired = 0;
        foreach ($rows as $row) {
            if (!$this->db->transBegin()) throw new RuntimeException('Pembersihan suspend kedaluwarsa tidak dapat dimulai.');
            try {
                $current = $this->db->table('customer_account_suspensions')->where('email_user', $row['email_user'])
                    ->where('expires_at <=', $now)->get()->getRowArray();
                if (!$current) { $this->db->transCommit(); continue; }
                if (!$this->db->table('customer_account_suspension_events')->insert([
                    'email_user' => $current['email_user'], 'action' => 'expired', 'reason' => $current['reason'],
                    'user_message' => $current['user_message'], 'expires_at' => $current['expires_at'],
                    'admin_email' => 'system', 'created_at' => $now,
                ])) throw new RuntimeException('Riwayat masa suspend tidak dapat disimpan.');
                if (!$this->db->table('customer_account_suspensions')->where('email_user', $current['email_user'])
                    ->where('expires_at <=', $now)->delete()) throw new RuntimeException('Suspend kedaluwarsa tidak dapat dibersihkan.');
                if (!$this->db->transStatus() || !$this->db->transCommit()) throw new RuntimeException('Suspend kedaluwarsa tidak berhasil diproses.');
                $expired++;
            } catch (\Throwable $e) {
                if ((int)$this->db->transDepth > 0) $this->db->transRollback();
                throw $e;
            }
        }
        return $expired;
    }

    private function invalidateCustomerAccess(string $email, string $now): void
    {
        if ($this->db->tableExists('customer_password_sessions')) {
            $version = bin2hex(random_bytes(32));
            $row = $this->db->table('customer_password_sessions')->where('email_user', $email)->get()->getRowArray();
            if ($row) $ok = $this->db->table('customer_password_sessions')->where('email_user', $email)->update(['version' => $version]);
            else $ok = $this->db->table('customer_password_sessions')->insert(['email_user' => $email, 'version' => $version]);
            if (!$ok) throw new RuntimeException('Sesi website tidak dapat dicabut.');
        }
        if ($this->db->tableExists('customer_api_tokens')) {
            $this->db->table('customer_api_tokens')->where(['email_user' => $email, 'revoked_at' => null])->update(['revoked_at' => $now]);
        }
        if ($this->db->tableExists('customer_push_devices')) {
            $this->db->table('customer_push_devices')->where('email_user', $email)->update(['is_active' => 0, 'updated_at' => $now]);
        }
        if ($this->db->tableExists('customer_push_notifications')) {
            $this->db->table('customer_push_notifications')->where('email_user', $email)
                ->whereIn('status', ['pending', 'awaiting_device', 'processing'])
                ->update(['status' => 'cancelled', 'last_error' => 'ACCOUNT_SUSPENDED', 'updated_at' => $now]);
        }
    }

    private function requireTables(): void
    {
        foreach (['user', 'customer_account_suspensions', 'customer_account_suspension_events'] as $table) {
            if (!$this->db->tableExists($table)) throw new RuntimeException('Tabel suspend belum tersedia.');
        }
    }
}
