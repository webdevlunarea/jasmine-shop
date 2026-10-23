<?php

namespace App\Services\Auth;

use CodeIgniter\Database\BaseConnection;
use DomainException;
use RuntimeException;

/** Creates a pending customer account and activates it only after email OTP verification. */
class CustomerRegistrationService
{
    private BaseConnection $db;
    private $sendVerification;
    private $emailBlocked;

    public function __construct(?BaseConnection $db = null, ?callable $sendVerification = null, ?callable $emailBlocked = null)
    {
        $this->db = $db ?? db_connect();
        $mailer = new CustomerVerificationMailer();
        $this->sendVerification = $sendVerification ?? static fn(string $email, string $code): bool => $mailer->send($email, $code);
        $this->emailBlocked = $emailBlocked ?? static fn(string $email): bool => (new DisposableEmailPolicy())->isBlocked($email);
    }

    /** @return array{created:bool,email_sent:bool} */
    public function register(string $name, string $email, string $password, string $confirmation, string $phone): array
    {
        $name = trim($name);
        $email = strtolower(trim($email));
        $normalizedPhone = (new IndonesianMobileNumber())->normalize($phone);
        if ($name === '' || mb_strlen($name) > 120 || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 254 || $normalizedPhone === null) {
            throw new DomainException('Data pendaftaran belum valid. Periksa nama, email, dan nomor ponsel.');
        }
        if ($error = PasswordResetService::passwordError($password, $confirmation)) throw new DomainException($error);
        if (($this->emailBlocked)($email)) throw new DomainException('Gunakan alamat email permanen yang dapat Anda akses.');
        foreach (['user', 'pembeli'] as $table) if (!$this->db->tableExists($table)) throw new RuntimeException('Customer storage unavailable');
        $this->assertColumns([
            'user' => ['email', 'sandi', 'role', 'otp', 'active', 'waktu_otp', 'registration_method', 'registered_at'],
            'pembeli' => ['email_user', 'nama', 'nohp', 'alamat', 'wishlist', 'keranjang', 'transaksi', 'poin', 'tier', 'foto'],
        ]);
        $this->assertColumnLength('user', 'email', $email);
        $this->assertColumnLength('pembeli', 'email_user', $email);
        $this->assertColumnLength('pembeli', 'nama', $name);
        $this->assertColumnLength('pembeli', 'nohp', $normalizedPhone);
        $this->requireTransactionalTables(['user', 'pembeli']);

        $code = (string)random_int(100000, 999999);
        $expires = time() + 600;
        if (!$this->db->transBegin()) throw new RuntimeException('Customer transaction unavailable');
        try {
            if ($this->db->table('user')->where('email', $email)->countAllResults() > 0
                || $this->db->table('pembeli')->where('email_user', $email)->countAllResults() > 0) {
                throw new DomainException('Email sudah terdaftar. Silakan masuk atau pulihkan sandi akun.');
            }
            $inserted = $this->db->table('user')->insert([
                'email' => $email, 'sandi' => password_hash($password, PASSWORD_DEFAULT),
                'role' => '0', 'otp' => $code, 'active' => '0', 'waktu_otp' => (string)$expires,
                'auth_provider' => 'email', 'registration_method' => 'email', 'registered_at' => gmdate('Y-m-d H:i:s'),
            ]);
            if (!$inserted) throw new RuntimeException('Customer account persistence failed');
            $inserted = $this->db->table('pembeli')->insert([
                'nama' => $name, 'email_user' => $email, 'nohp' => $normalizedPhone,
                'alamat' => '[]', 'wishlist' => '[]', 'keranjang' => '[]', 'transaksi' => '[]', 'poin' => '[]',
                'tier' => json_encode(['label' => 'bronze', 'data' => []], JSON_THROW_ON_ERROR),
                'foto' => '/imguser/ZGVmYXVsdA==',
            ]);
            if (!$inserted) throw new RuntimeException('Customer profile persistence failed');
            $this->claimAutoVouchers($email);
            if ($this->db->transStatus() === false || !$this->db->transCommit()) throw new RuntimeException('Customer transaction failed');
        } catch (\Throwable $e) {
            $this->db->transRollback();
            if ($e instanceof DomainException) throw $e;
            if (in_array((int)($this->db->error()['code'] ?? 0), [1062, 19], true)) {
                throw new DomainException('Email sudah terdaftar. Silakan masuk atau pulihkan sandi akun.');
            }
            throw $e;
        }

        $sent = false;
        try { $sent = (bool)($this->sendVerification)($email, $code); }
        catch (\Throwable $e) { log_message('error', 'Customer verification email dispatch failed: ' . get_class($e)); }
        return ['created' => true, 'email_sent' => $sent];
    }

    public function verify(string $email, $code): bool
    {
        $email = strtolower(trim($email));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !is_string($code) || !preg_match('/^[0-9]{6}$/D', $code)) return false;
        if (!$this->db->tableExists('user')) throw new RuntimeException('Customer storage unavailable');
        $this->requireTransactionalTables(['user']);
        if (!$this->db->transBegin()) throw new RuntimeException('Customer transaction unavailable');
        try {
            $builder = $this->db->table('user')->where('email', $email)->where('role', '0');
            $sql = $builder->getCompiledSelect();
            $row = $this->db->query($sql . ($this->db->DBDriver === 'SQLite3' ? '' : ' FOR UPDATE'))->getRowArray();
            if (!$row || (string)$row['active'] !== '0' || !hash_equals((string)$row['otp'], $code) || (int)$row['waktu_otp'] < time()) {
                throw new DomainException('Kode verifikasi tidak valid atau sudah kedaluwarsa.');
            }
            $ok = $this->db->table('user')->where('email', $email)->where('active', '0')->where('otp', $code)
                ->where('waktu_otp >=', time())->update(['active' => '1', 'otp' => '0', 'waktu_otp' => '0']);
            if (!$ok || $this->db->affectedRows() !== 1 || $this->db->transStatus() === false || !$this->db->transCommit()) {
                throw new DomainException('Kode verifikasi tidak valid atau sudah kedaluwarsa.');
            }
            return true;
        } catch (\Throwable $e) {
            $this->db->transRollback();
            if ($e instanceof DomainException) throw $e;
            throw new RuntimeException('Customer verification failed');
        }
    }

    /** Resend is deliberately generic at the API boundary; this method only touches pending accounts. */
    public function resend(string $email): bool
    {
        $email = strtolower(trim($email));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !$this->db->tableExists('user')) return false;
        $row = $this->db->table('user')->where('email', $email)->where('role', '0')->where('active', '0')->get()->getRowArray();
        if (!$row || (int)$row['waktu_otp'] > time() + 540) return false;
        $code = (string)random_int(100000, 999999);
        $expires = time() + 600;
        $ok = $this->db->table('user')->where('email', $email)->where('active', '0')->where('waktu_otp', $row['waktu_otp'])
            ->update(['otp' => $code, 'waktu_otp' => (string)$expires]);
        if (!$ok || $this->db->affectedRows() !== 1) return false;
        try { return (bool)($this->sendVerification)($email, $code); }
        catch (\Throwable $e) { log_message('error', 'Customer verification resend failed: ' . get_class($e)); return false; }
    }

    /**
     * Applies the guarded, collision-resistant welcome-voucher flow to email
     * registration and Google account creation. The caller owns the transaction.
     */
    public function claimAutoVouchers(string $email): void
    {
        if (!$this->db->tableExists('voucher') || !$this->db->tableExists('voucher_claimed')) return;
        $this->assertColumns([
            'voucher' => ['id', 'active', 'auto_claimed', 'jadwal', 'durasi'],
            'voucher_claimed' => ['id', 'id_voucher', 'kadaluarsa', 'email_user', 'active'],
        ]);
        $this->assertColumnLength('voucher_claimed', 'email_user', $email);
        $this->requireTransactionalTables(['voucher', 'voucher_claimed']);
        $vouchers = $this->db->table('voucher')->where('active', 1)->where('auto_claimed', 1)->get()->getResultArray();
        $today = date('Y-m-d', time() + 7 * 3600);
        foreach ($vouchers as $voucher) {
            $schedule = explode('@', (string)($voucher['jadwal'] ?? ''));
            if (count($schedule) >= 2 && !empty($schedule[0]) && !empty($schedule[1])
                && (strtotime($today) < strtotime($schedule[0]) || strtotime($today) > strtotime($schedule[1]))) continue;
            $expiry = null;
            if (!empty($voucher['durasi'])) $expiry = date('Y-m-d', strtotime((string)$voucher['durasi'], strtotime($today)));
            $id = $this->newClaimId();
            $inserted = $this->db->table('voucher_claimed')->insert([
                'id' => $id, 'id_voucher' => (string)$voucher['id'], 'kadaluarsa' => $expiry,
                'email_user' => $email, 'active' => 1,
            ]);
            if (!$inserted) throw new RuntimeException('Customer welcome voucher persistence failed');
        }
    }

    private function newClaimId(): string
    {
        // Legacy schema uses a string key. Keep within VARCHAR(20), while avoiding
        // predictable same-second collisions across concurrent signups.
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $id = (string)(time() + 7 * 3600) . str_pad((string)random_int(0, 9999999999), 10, '0', STR_PAD_LEFT);
            if ($this->db->table('voucher_claimed')->where('id', $id)->countAllResults() === 0) return $id;
        }
        throw new RuntimeException('Customer voucher claim identifier unavailable');
    }

    private function requireTransactionalTables(array $tables): void
    {
        if ($this->db->DBDriver !== 'MySQLi') return;
        foreach ($tables as $table) {
            $row = $this->db->query(
                'SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?',
                [$this->db->prefixTable($table)]
            )->getRowArray();
            if (!$row || strcasecmp((string)$row['ENGINE'], 'InnoDB') !== 0) throw new RuntimeException('Transactional customer storage required');
        }
    }

    private function assertColumns(array $columnsByTable): void
    {
        foreach ($columnsByTable as $table => $columns) {
            foreach ($columns as $column) {
                if (!$this->db->fieldExists($column, $table)) throw new RuntimeException('Customer schema does not match the signup contract');
            }
        }
    }

    private function assertColumnLength(string $table, string $column, string $value): void
    {
        if ($this->db->DBDriver !== 'MySQLi') return;
        $row = $this->db->query(
            'SELECT CHARACTER_MAXIMUM_LENGTH AS max_length FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?',
            [$this->db->prefixTable($table), $column]
        )->getRowArray();
        $max = isset($row['max_length']) ? (int)$row['max_length'] : 0;
        if ($max > 0 && mb_strlen($value) > $max) throw new DomainException('Data pendaftaran melebihi batas yang didukung. Periksa panjang nama, email, atau nomor ponsel.');
    }
}
