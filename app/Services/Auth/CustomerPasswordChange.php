<?php

namespace App\Services\Auth;

use CodeIgniter\Database\BaseConnection;
use DomainException;
use RuntimeException;

/** Changes a signed-in customer's password and invalidates every API session. */
class CustomerPasswordChange
{
    private BaseConnection $db;

    public function __construct(?BaseConnection $db = null) { $this->db = $db ?? db_connect(); }

    public function change(string $email, string $currentPassword, string $newPassword, string $confirmation): void
    {
        $email = strtolower(trim($email));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $currentPassword === '' || strlen($currentPassword) > 4096) {
            throw new DomainException('Sandi saat ini belum benar.');
        }
        if ($error = PasswordResetService::passwordError($newPassword, $confirmation)) throw new DomainException($error);
        foreach (['user', 'customer_password_sessions', 'customer_api_tokens'] as $table) {
            if (!$this->db->tableExists($table)) throw new RuntimeException('Password session storage unavailable');
        }
        $this->requireInnoDb(['user', 'customer_password_sessions', 'customer_api_tokens']);
        if (!$this->db->transBegin()) throw new RuntimeException('Password transaction unavailable');
        try {
            $builder = $this->db->table('user')->where('email', $email)->where('role', '0')->where('active', '1');
            $sql = $builder->getCompiledSelect();
            $user = $this->db->query($sql . ($this->db->DBDriver === 'SQLite3' ? '' : ' FOR UPDATE'))->getRowArray();
            if (!$user || !password_verify($currentPassword, (string)$user['sandi'])) {
                throw new DomainException('Sandi saat ini belum benar.');
            }
            if (password_verify($newPassword, (string)$user['sandi'])) {
                throw new DomainException('Gunakan sandi baru yang berbeda dari sandi saat ini.');
            }
            $updated = $this->db->table('user')->where('email', $email)->where('role', '0')->where('active', '1')
                ->update(['sandi' => password_hash($newPassword, PASSWORD_DEFAULT)]);
            if (!$updated || $this->db->affectedRows() !== 1) throw new RuntimeException('Password update failed');

            $version = bin2hex(random_bytes(32));
            $existing = $this->db->table('customer_password_sessions')->where('email_user', $email)->get()->getRowArray();
            $versionSaved = $existing
                ? $this->db->table('customer_password_sessions')->where('email_user', $email)->update(['version' => $version])
                : $this->db->table('customer_password_sessions')->insert(['email_user' => $email, 'version' => $version]);
            if (!$versionSaved) throw new RuntimeException('Password session invalidation failed');
            $revoked = $this->db->table('customer_api_tokens')->where('email_user', $email)->where('revoked_at', null)
                ->update(['revoked_at' => gmdate('Y-m-d H:i:s')]);
            if ($revoked === false || $this->db->transStatus() === false || !$this->db->transCommit()) {
                throw new RuntimeException('Password session invalidation failed');
            }
        } catch (\Throwable $e) {
            $this->db->transRollback();
            if ($e instanceof DomainException) throw $e;
            throw new RuntimeException('Password change failed');
        }

        // A successful password change is security-sensitive. Deliver the notice
        // after the transaction so a mail outage never rolls back the credential
        // update or leaves the customer uncertain about whether it succeeded.
        try {
            (new CustomerSecurityMailer())->passwordChanged($email);
        } catch (\Throwable $e) {
            log_message('error', 'Customer password-change security notice failed: ' . get_class($e));
        }
    }

    private function requireInnoDb(array $tables): void
    {
        if ($this->db->DBDriver !== 'MySQLi') return;
        foreach ($tables as $table) {
            $row = $this->db->query('SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?',
                [$this->db->prefixTable($table)])->getRowArray();
            if (!$row || strcasecmp((string)$row['ENGINE'], 'InnoDB') !== 0) throw new RuntimeException('Transactional password storage required');
        }
    }
}
