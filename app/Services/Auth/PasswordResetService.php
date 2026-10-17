<?php

namespace App\Services\Auth;

use CodeIgniter\Database\BaseConnection;

class PasswordResetService
{
    public const INVALID_CODE = 'Kode tidak valid, kedaluwarsa, atau sudah digunakan. Silakan minta kode baru.';
    private BaseConnection $db;
    private PasswordResetMailer $mailer;

    public function __construct(?BaseConnection $db = null, ?PasswordResetMailer $mailer = null)
    {
        $this->db = $db ?? db_connect(); $this->mailer = $mailer ?? new PasswordResetMailer();
    }
    protected function now(): int { return time(); }
    protected function newCode(): string { return str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT); }

    public static function passwordError($password, $confirmation): ?string
    {
        if (!is_string($password) || mb_strlen($password) < 8 || strlen($password) > 72 || strpos($password, "\0") !== false || !preg_match('/[0-9]/', $password)) {
            return 'Sandi minimal 8 karakter, wajib mengandung angka, dan maksimal 72 byte.';
        }
        return is_string($confirmation) && hash_equals($password, $confirmation) ? null : 'Konfirmasi sandi belum sama.';
    }

    private function allow(string $scope, string $value, int $limit, int $window): bool
    {
        $key = hash('sha256', $scope . '|' . $value); $now = $this->now();
        $row = $this->db->table('customer_password_reset_limits')->where('bucket', $key)->get()->getRowArray();
        if (!$row) {
            try {
                if ($this->db->table('customer_password_reset_limits')->insert(['bucket' => $key, 'window_start' => $now, 'hits' => 1])) return true;
            } catch (\Throwable $e) {
                if (!in_array((int)($this->db->error()['code'] ?? 0), [19, 1062], true)) throw new \RuntimeException('Reset limiter unavailable');
            }
        } elseif ((int)$row['window_start'] <= $now - $window) {
            $this->db->table('customer_password_reset_limits')->where('bucket', $key)->where('window_start', $row['window_start'])
                ->update(['window_start' => $now, 'hits' => 1]);
            if ($this->db->affectedRows() === 1) return true;
        }
        $this->db->table('customer_password_reset_limits')->where('bucket', $key)->where('window_start >', $now - $window)
            ->where('hits <', $limit)->set('hits', 'hits + 1', false)->update();
        return $this->db->affectedRows() === 1;
    }

    /** Same public response for known/unknown/limited/delivery-failed addresses. Never return the code. */
    public function request(string $email, string $ip): void
    {
        $email = strtolower(trim($email));
        if (strlen($email) > 254 || !filter_var($email, FILTER_VALIDATE_EMAIL)) return;
        if (!$this->allow('request-ip', $ip, 10, 900) || !$this->allow('request-email', $email, 3, 900)) return;
        $user = $this->db->table('user')->where('email', $email)->get()->getRowArray();
        if (!$user || (string)$user['role'] !== '0') return;
        $email = $user['email']; $now = $this->now();
        $old = $this->db->table('customer_password_reset_codes')->where('email_user', $email)->get()->getRowArray();
        if ($old && (int)$old['created_at'] > $now - 60) return;
        $code = $this->newCode(); $id = bin2hex(random_bytes(32));
        $record = ['email_user' => $email, 'request_id' => $id, 'otp_hash' => password_hash($code, PASSWORD_DEFAULT),
            'created_at' => $now, 'expires_at' => $now + 600, 'attempts' => 0, 'used' => 0];
        try {
            $builder = $this->db->table('customer_password_reset_codes');
            $ok = $old ? $builder->where('email_user', $email)->where('created_at <=', $now - 60)->update($record) : $builder->insert($record);
            if (!$ok || $this->db->affectedRows() !== 1) return;
        } catch (\Throwable $e) {
            if (in_array((int)($this->db->error()['code'] ?? 0), [19, 1062], true)) return;
            throw new \RuntimeException('Reset storage unavailable');
        }
        if (!$this->mailer->sendCode($email, $code)) {
            $this->db->table('customer_password_reset_codes')->where('email_user', $email)->where('request_id', $id)->update(['used' => 1]);
        }
    }

    public function reset(string $email, $code, $password, $confirmation, string $ip): void
    {
        if ($error = self::passwordError($password, $confirmation)) throw new \DomainException($error);
        if (!is_string($code) || !preg_match('/^[0-9]{6}$/D', $code)) throw new \DomainException(self::INVALID_CODE);
        if (!$this->allow('verify-ip', $ip, 30, 900)) throw new \DomainException('Terlalu banyak percobaan. Silakan tunggu 15 menit.');
        $row = $this->db->table('customer_password_reset_codes')->where('email_user', $email)->get()->getRowArray();
        $now = $this->now();
        if (!$row || (int)$row['used'] !== 0 || (int)$row['expires_at'] <= $now) throw new \DomainException(self::INVALID_CODE);
        $email = (string)$row['email_user'];
        // Atomic attempt reservation: parallel guesses share the same five-attempt budget.
        $this->db->table('customer_password_reset_codes')->where('email_user', $email)->where('request_id', $row['request_id'])
            ->where('used', 0)->where('expires_at >', $now)->where('attempts <', 5)->set('attempts', 'attempts + 1', false)->update();
        if ($this->db->affectedRows() !== 1 || !password_verify($code, $row['otp_hash'])) throw new \DomainException(self::INVALID_CODE);
        $hash = password_hash($password, PASSWORD_DEFAULT);
        if ($this->db->DBDriver === 'MySQLi') {
            foreach (['user', 'customer_password_reset_codes', 'customer_password_sessions'] as $table) {
                $engine = $this->db->query('SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?', [$this->db->prefixTable($table)])->getRowArray();
                if (!$engine || strcasecmp((string)$engine['ENGINE'], 'InnoDB') !== 0) throw new \RuntimeException('Transactional reset tables required');
            }
        }
        if (!$this->db->transBegin()) throw new \RuntimeException('Reset transaction unavailable');
        try {
            // Only one request may consume a valid code, including during resend/reset races.
            $this->db->table('customer_password_reset_codes')->where('email_user', $email)->where('request_id', $row['request_id'])
                ->where('used', 0)->where('expires_at >', $this->now())->where('attempts <=', 5)->update(['used' => 1]);
            if ($this->db->affectedRows() !== 1) throw new \DomainException(self::INVALID_CODE);
            $this->db->table('user')->where('email', $email)->where('role', '0')->update(['sandi' => $hash]);
            if ($this->db->affectedRows() !== 1) throw new \DomainException(self::INVALID_CODE);
            $versions = $this->db->table('customer_password_sessions');
            $existing = $versions->where('email_user', $email)->get()->getRowArray();
            $version = bin2hex(random_bytes(32));
            $ok = $existing
                ? $this->db->table('customer_password_sessions')->where('email_user', $email)->update(['version' => $version])
                : $this->db->table('customer_password_sessions')->insert(['email_user' => $email, 'version' => $version]);
            if (!$ok || !$this->db->transStatus() || !$this->db->transCommit()) throw new \RuntimeException('Reset update failed');
        } catch (\Throwable $e) {
            $this->db->transRollback();
            if ($e instanceof \DomainException) throw $e;
            throw new \RuntimeException('Reset update failed');
        }
        $this->mailer->sendChanged($email);
    }
}
