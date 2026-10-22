<?php

namespace App\Services\Auth;

use CodeIgniter\Database\BaseConnection;
use RuntimeException;

/** RFC 6238 authenticator-app MFA, with encrypted secrets and one-use login challenges. */
final class CustomerTotpMfa
{
    private BaseConnection $db;

    public function __construct(?BaseConnection $db = null) { $this->db = $db ?? db_connect(); }

    public function status(string $email): array
    {
        if (!$this->db->tableExists('customer_totp_mfa')) return ['available' => false, 'enabled' => false];
        $row = $this->db->table('customer_totp_mfa')->where('email_user', strtolower(trim($email)))->get()->getRowArray();
        return ['available' => true, 'enabled' => (bool)($row['enabled'] ?? false)];
    }

    /** Start enrollment; plaintext secret is returned once and never stored in plaintext. */
    public function beginSetup(string $email, string $password): array
    {
        $email = strtolower(trim($email));
        $this->requireSchema();
        $this->requirePassword($email, $password);
        $existing = $this->db->table('customer_totp_mfa')->where('email_user', $email)->get()->getRowArray();
        if ($existing && (int)$existing['enabled'] === 1) throw new \DomainException('Verifikasi dua langkah sudah aktif.');
        $secret = self::base32Encode(random_bytes(20));
        $now = gmdate('Y-m-d H:i:s');
        $record = [
            'email_user' => $email,
            'secret_cipher' => $existing['secret_cipher'] ?? null,
            'pending_secret_cipher' => $this->encrypt($secret),
            'enabled' => 0,
            'last_counter' => -1,
            'recovery_codes' => (string)($existing['recovery_codes'] ?? '[]'),
            'challenge_hash' => null,
            'challenge_expires_at' => null,
            'challenge_device_name' => null,
            'challenge_attempts' => 0,
            'updated_at' => $now,
        ];
        if ($existing) $this->db->table('customer_totp_mfa')->where('email_user', $email)->update($record);
        else $this->db->table('customer_totp_mfa')->insert($record + ['created_at' => $now]);
        if ($this->db->affectedRows() < 1) throw new RuntimeException('MFA enrollment could not be stored.');
        $label = rawurlencode('Lunarea Furniture:' . $email);
        $issuer = rawurlencode('Lunarea Furniture');
        return ['secret' => $secret, 'otpauth_uri' => "otpauth://totp/{$label}?secret={$secret}&issuer={$issuer}&algorithm=SHA1&digits=6&period=30"];
    }

    public function confirmSetup(string $email, string $code): array
    {
        $email = strtolower(trim($email));
        $this->requireSchema();
        $row = $this->db->table('customer_totp_mfa')->where('email_user', $email)->get()->getRowArray();
        if (!$row || empty($row['pending_secret_cipher'])) throw new \DomainException('Mulai penyiapan verifikasi dua langkah terlebih dahulu.');
        $secret = $this->decrypt((string)$row['pending_secret_cipher']);
        $counter = self::matchCounter($secret, $code, time());
        if ($counter === null) throw new \DomainException('Kode tidak sesuai atau sudah kedaluwarsa.');
        $recoveryCodes = [];
        $recoveryHashes = [];
        for ($i = 0; $i < 8; $i++) {
            $recoveryCodes[] = strtoupper(bin2hex(random_bytes(5)));
            $recoveryHashes[] = password_hash($recoveryCodes[$i], PASSWORD_DEFAULT);
        }
        $ok = $this->db->table('customer_totp_mfa')->where('email_user', $email)->update([
            'secret_cipher' => $row['pending_secret_cipher'],
            'pending_secret_cipher' => null,
            'enabled' => 1,
            'last_counter' => $counter,
            'recovery_codes' => json_encode($recoveryHashes, JSON_THROW_ON_ERROR),
            'updated_at' => gmdate('Y-m-d H:i:s'),
        ]);
        if (!$ok || $this->db->affectedRows() !== 1) throw new RuntimeException('MFA enrollment could not be completed.');
        return ['recovery_codes' => $recoveryCodes];
    }

    public function disable(string $email, string $password, string $code): void
    {
        $email = strtolower(trim($email));
        $this->requireSchema();
        $this->requirePassword($email, $password);
        $row = $this->db->table('customer_totp_mfa')->where(['email_user' => $email, 'enabled' => 1])->get()->getRowArray();
        if (!$row || empty($row['secret_cipher'])) throw new \DomainException('Verifikasi dua langkah belum aktif.');
        $secret = $this->decrypt((string)$row['secret_cipher']);
        $counter = self::matchCounter($secret, $code, time());
        if ($counter === null || $counter <= (int)$row['last_counter']) throw new \DomainException('Kode tidak sesuai atau sudah digunakan.');
        $ok = $this->db->table('customer_totp_mfa')->where('email_user', $email)->where('last_counter', $row['last_counter'])->update([
            'secret_cipher' => null, 'pending_secret_cipher' => null, 'enabled' => 0, 'recovery_codes' => '[]',
            'last_counter' => -1, 'challenge_hash' => null, 'challenge_expires_at' => null,
            'challenge_device_name' => null, 'challenge_attempts' => 0, 'updated_at' => gmdate('Y-m-d H:i:s'),
        ]);
        if (!$ok || $this->db->affectedRows() !== 1) throw new RuntimeException('MFA state update failed.');
    }

    /** Returns null when MFA is not enabled; otherwise a short-lived opaque challenge. */
    public function beginLogin(string $email, ?string $deviceName): ?array
    {
        if (!$this->db->tableExists('customer_totp_mfa')) return null;
        $email = strtolower(trim($email));
        $row = $this->db->table('customer_totp_mfa')->where(['email_user' => $email, 'enabled' => 1])->get()->getRowArray();
        if (!$row) return null;
        $challenge = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
        $ok = $this->db->table('customer_totp_mfa')->where('email_user', $email)->where('enabled', 1)->update([
            'challenge_hash' => hash('sha256', $challenge),
            'challenge_expires_at' => gmdate('Y-m-d H:i:s', time() + 300),
            'challenge_device_name' => mb_substr(trim((string)$deviceName), 0, 100),
            'challenge_attempts' => 0,
            'updated_at' => gmdate('Y-m-d H:i:s'),
        ]);
        if (!$ok || $this->db->affectedRows() !== 1) throw new RuntimeException('MFA challenge could not be created.');
        return ['mfa_required' => true, 'challenge_token' => $challenge, 'expires_in' => 300];
    }

    /** Consume challenge and TOTP counter atomically before the caller issues API tokens. */
    public function completeLogin(string $challenge, string $code): array
    {
        $this->requireSchema();
        if (strlen($challenge) < 32 || strlen($challenge) > 128) throw new \DomainException('Verifikasi dua langkah gagal. Masuk kembali.');
        if (!$this->db->transBegin()) throw new RuntimeException('MFA verification unavailable.');
        try {
            $query = $this->db->table('customer_totp_mfa')->where('challenge_hash', hash('sha256', $challenge))->where('enabled', 1);
            $sql = $query->getCompiledSelect();
            $row = $this->db->query($sql . ($this->db->DBDriver === 'SQLite3' ? '' : ' FOR UPDATE'))->getRowArray();
            if (!$row || empty($row['challenge_expires_at']) || strtotime($row['challenge_expires_at'] . ' UTC') < time()) {
                throw new \DomainException('Verifikasi dua langkah kedaluwarsa. Masuk kembali.');
            }
            if ((int)$row['challenge_attempts'] >= 5) throw new \DomainException('Batas percobaan tercapai. Masuk kembali.');
            $secret = $this->decrypt((string)$row['secret_cipher']);
            $counter = self::matchCounter($secret, $code, time());
            $recoveryHashes = json_decode((string)($row['recovery_codes'] ?? '[]'), true);
            if (!is_array($recoveryHashes)) $recoveryHashes = [];
            $recoveryIndex = null;
            if ($counter === null && preg_match('/^[A-F0-9]{10}$/D', strtoupper($code))) {
                foreach ($recoveryHashes as $index => $storedHash) {
                    if (is_string($storedHash) && password_verify(strtoupper($code), $storedHash)) { $recoveryIndex = $index; break; }
                }
            }
            $counterRejected = $counter !== null && $counter <= (int)$row['last_counter'];
            if (($counter === null && $recoveryIndex === null) || $counterRejected) {
                $this->db->table('customer_totp_mfa')->where('email_user', $row['email_user'])->update([
                    'challenge_attempts' => (int)$row['challenge_attempts'] + 1,
                    'updated_at' => gmdate('Y-m-d H:i:s'),
                ]);
                if (!$this->db->transCommit()) throw new RuntimeException('MFA attempt could not be stored.');
                throw new \DomainException('Kode verifikasi tidak sesuai atau sudah digunakan.');
            }
            if ($recoveryIndex !== null) unset($recoveryHashes[$recoveryIndex]);
            $updated = $this->db->table('customer_totp_mfa')->where('email_user', $row['email_user'])
                ->where('challenge_hash', hash('sha256', $challenge))->where('last_counter', $row['last_counter'])
                ->update([
                    'last_counter' => $counter ?? (int)$row['last_counter'],
                    'recovery_codes' => json_encode(array_values($recoveryHashes), JSON_THROW_ON_ERROR),
                    'challenge_hash' => null, 'challenge_expires_at' => null,
                    'challenge_attempts' => 0, 'updated_at' => gmdate('Y-m-d H:i:s'),
                ]);
            if (!$updated || $this->db->affectedRows() !== 1 || !$this->db->transCommit()) {
                throw new RuntimeException('MFA challenge could not be consumed.');
            }
            return ['email' => (string)$row['email_user'], 'device_name' => (string)($row['challenge_device_name'] ?? '')];
        } catch (\Throwable $error) {
            if ((int)$this->db->transDepth > 0) $this->db->transRollback();
            throw $error;
        }
    }

    public static function matchCounter(string $secret, string $code, int $timestamp): ?int
    {
        if (!preg_match('/^[0-9]{6}$/D', $code)) return null;
        $counter = intdiv($timestamp, 30);
        for ($candidate = $counter - 1; $candidate <= $counter + 1; $candidate++) {
            if ($candidate >= 0 && hash_equals(self::codeAt($secret, $candidate), $code)) return $candidate;
        }
        return null;
    }

    private static function codeAt(string $secret, int $counter): string
    {
        $key = self::base32Decode($secret);
        $digest = hash_hmac('sha1', pack('N2', intdiv($counter, 0x100000000), $counter & 0xffffffff), $key, true);
        $offset = ord($digest[19]) & 0x0f;
        $number = ((ord($digest[$offset]) & 0x7f) << 24) | ((ord($digest[$offset + 1]) & 0xff) << 16)
            | ((ord($digest[$offset + 2]) & 0xff) << 8) | (ord($digest[$offset + 3]) & 0xff);
        return str_pad((string)($number % 1000000), 6, '0', STR_PAD_LEFT);
    }

    private static function base32Encode(string $data): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567'; $buffer = 0; $bits = 0; $out = '';
        foreach (str_split($data) as $char) {
            $buffer = ($buffer << 8) | ord($char); $bits += 8;
            while ($bits >= 5) { $bits -= 5; $out .= $alphabet[($buffer >> $bits) & 31]; }
        }
        if ($bits > 0) $out .= $alphabet[($buffer << (5 - $bits)) & 31];
        return $out;
    }

    private static function base32Decode(string $text): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567'; $buffer = 0; $bits = 0; $out = '';
        foreach (str_split(strtoupper(rtrim($text, '='))) as $char) {
            $value = strpos($alphabet, $char);
            if ($value === false) throw new RuntimeException('Stored TOTP secret is invalid.');
            $buffer = ($buffer << 5) | $value; $bits += 5;
            if ($bits >= 8) { $bits -= 8; $out .= chr(($buffer >> $bits) & 255); }
        }
        return $out;
    }

    private function requirePassword(string $email, string $password): void
    {
        if ($password === '' || strlen($password) > 4096) throw new \DomainException('Kata sandi saat ini wajib diisi.');
        $user = $this->db->table('user')->where(['email' => $email, 'role' => '0', 'active' => '1'])->get()->getRowArray();
        if (!$user || !password_verify($password, (string)$user['sandi'])) throw new \DomainException('Kata sandi saat ini tidak sesuai.');
    }

    private function encrypt(string $plain): string { return base64_encode(service('encrypter')->encrypt($plain)); }
    private function decrypt(string $cipher): string
    {
        $decoded = base64_decode($cipher, true);
        if ($decoded === false) throw new RuntimeException('Stored TOTP secret is invalid.');
        return service('encrypter')->decrypt($decoded);
    }

    private function requireSchema(): void
    {
        if (!$this->db->tableExists('customer_totp_mfa')) throw new RuntimeException('MFA database schema is not deployed.');
    }
}
