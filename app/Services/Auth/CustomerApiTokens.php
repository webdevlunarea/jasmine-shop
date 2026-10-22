<?php

namespace App\Services\Auth;

use App\Models\UserModel;
use CodeIgniter\Database\BaseConnection;
use DateTimeImmutable;
use DateTimeZone;
use RuntimeException;

class CustomerApiTokens
{
    private BaseConnection $db;
    private UserModel $users;
    private PasswordSessionVersions $passwordVersions;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? \Config\Database::connect();
        $this->users = new UserModel($this->db);
        $this->passwordVersions = new PasswordSessionVersions($this->db);
    }

    public function issue(string $email, ?string $deviceName = null): array
    {
        $email = strtolower(trim($email));
        $this->requirePasswordVersionStore();
        if (!$this->eligibleCustomer($email)) throw new RuntimeException('Akun pelanggan tidak aktif.');
        $familyId = $this->uuid();
        // Both credentials form one session: never leave a usable access token
        // behind if creation of its refresh token fails.
        if (!$this->db->transBegin()) throw new RuntimeException('Sesi API tidak dapat dimulai.');
        try {
            $access = $this->createToken($email, $familyId, 'access', 900, $deviceName);
            $refresh = $this->createToken($email, $familyId, 'refresh', 2592000, $deviceName);
            if ($this->db->transStatus() === false) throw new RuntimeException('Sesi API tidak dapat disimpan.');
            if (!$this->db->transCommit()) throw new RuntimeException('Sesi API tidak berhasil disimpan.');
            return [
                'access_token' => $access['token'],
                'token_type' => 'Bearer',
                'expires_in' => 900,
                'refresh_token' => $refresh['token'],
                'refresh_expires_in' => 2592000,
            ];
        } catch (\Throwable $e) {
            if ((int)$this->db->transDepth > 0) $this->db->transRollback();
            throw $e;
        }
    }

    public function authenticate(string $plainToken): ?array
    {
        if (strlen($plainToken) < 32 || strlen($plainToken) > 128) return null;
        if (!$this->db->tableExists('customer_password_sessions')) return null;
        $hash = hash('sha256', $plainToken);
        $row = $this->db->table('customer_api_tokens')
            ->where(['token_hash' => $hash, 'token_type' => 'access', 'revoked_at' => null])
            ->where('expires_at >', $this->now())
            ->get()->getRowArray();
        if (!$row || !$this->eligibleCustomer((string)$row['email_user'])) return null;
        if (!$this->passwordVersions->matches((string)$row['email_user'], (string)($row['password_version'] ?? ''))) {
            $this->revokeFamily((string)$row['family_id']);
            return null;
        }
        if (empty($row['last_used_at']) || strtotime($row['last_used_at']) < time() - 300) {
            $this->db->table('customer_api_tokens')->where('id', $row['id'])->update(['last_used_at' => $this->now()]);
        }
        return $this->users->getUser((string)$row['email_user']);
    }

    public function rotate(string $plainRefresh): ?array
    {
        if (strlen($plainRefresh) < 32 || strlen($plainRefresh) > 128) return null;
        if (!$this->db->tableExists('customer_password_sessions')) return null;
        $hash = hash('sha256', $plainRefresh);
        if (!$this->db->transBegin()) throw new RuntimeException('Pembaruan sesi API tidak dapat dimulai.');
        try {
            $row = $this->db->table('customer_api_tokens')->where([
                'token_hash' => $hash,
                'token_type' => 'refresh',
            ])->get()->getRowArray();
            if (!$row) {
                $this->db->transRollback();
                return null;
            }
            if ($row['revoked_at'] !== null || $row['expires_at'] <= $this->now()) {
                // Refresh-token reuse revokes the entire family.
                $this->db->table('customer_api_tokens')->where('family_id', $row['family_id'])->where('revoked_at', null)->update(['revoked_at' => $this->now()]);
                if (!$this->db->transCommit()) throw new RuntimeException('Pencabutan sesi API gagal disimpan.');
                return null;
            }
            if (!$this->eligibleCustomer((string)$row['email_user'])) {
                $this->db->table('customer_api_tokens')->where('family_id', $row['family_id'])->where('revoked_at', null)->update(['revoked_at' => $this->now()]);
                if (!$this->db->transCommit()) throw new RuntimeException('Pencabutan sesi API gagal disimpan.');
                return null;
            }
            if (!$this->passwordVersions->matches((string)$row['email_user'], (string)($row['password_version'] ?? ''))) {
                $this->db->table('customer_api_tokens')->where('family_id', $row['family_id'])->where('revoked_at', null)->update(['revoked_at' => $this->now()]);
                if (!$this->db->transCommit()) throw new RuntimeException('Pencabutan sesi API gagal disimpan.');
                return null;
            }
            $claimed = $this->db->table('customer_api_tokens')->where([
                'id' => $row['id'], 'revoked_at' => null,
            ])->where('expires_at >', $this->now())->update(['revoked_at' => $this->now()]);
            if (!$claimed || $this->db->affectedRows() !== 1) {
                $this->db->transRollback();
                return null;
            }
            $deviceName = $row['device_name'] ?: null;
            $access = $this->createToken((string)$row['email_user'], (string)$row['family_id'], 'access', 900, $deviceName);
            $refresh = $this->createToken((string)$row['email_user'], (string)$row['family_id'], 'refresh', 2592000, $deviceName);
            $linked = $this->db->table('customer_api_tokens')->where('id', $row['id'])
                ->where('revoked_at IS NOT NULL')->update(['replaced_by_hash' => $refresh['hash']]);
            if (!$linked || $this->db->affectedRows() !== 1) throw new RuntimeException('Rotasi sesi API tidak dapat ditautkan.');
            if (!$this->db->transCommit()) throw new RuntimeException('Rotasi sesi API gagal disimpan.');
            return [
                'access_token' => $access['token'], 'token_type' => 'Bearer', 'expires_in' => 900,
                'refresh_token' => $refresh['token'], 'refresh_expires_in' => 2592000,
            ];
        } catch (\Throwable $e) {
            if ((int)$this->db->transDepth > 0) $this->db->transRollback();
            throw $e;
        }
    }

    public function revokeAccess(string $plainToken): void
    {
        $hash = hash('sha256', $plainToken);
        $this->db->table('customer_api_tokens')->where(['token_hash' => $hash, 'token_type' => 'access'])->update(['revoked_at' => $this->now()]);
    }

    public function revokeFamilyForAccess(string $plainToken): void
    {
        $row = $this->db->table('customer_api_tokens')->where(['token_hash' => hash('sha256', $plainToken), 'token_type' => 'access'])->get()->getRowArray();
        if ($row) $this->db->table('customer_api_tokens')->where('family_id', $row['family_id'])->where('revoked_at', null)->update(['revoked_at' => $this->now()]);
    }

    /** Revoke every other device session while keeping the calling session active. */
    public function revokeOtherSessionsForAccess(string $plainToken): int
    {
        if (strlen($plainToken) < 32 || strlen($plainToken) > 128) {
            throw new RuntimeException('Sesi aktif tidak valid.');
        }
        $current = $this->db->table('customer_api_tokens')
            ->where([
                'token_hash' => hash('sha256', $plainToken),
                'token_type' => 'access',
                'revoked_at' => null,
            ])
            ->where('expires_at >', $this->now())
            ->get()->getRowArray();
        if (!$current || !$this->passwordVersions->matches((string)$current['email_user'], (string)($current['password_version'] ?? ''))) {
            throw new RuntimeException('Sesi aktif tidak dapat diverifikasi.');
        }

        $email = (string)$current['email_user'];
        $currentFamily = (string)$current['family_id'];
        $this->requirePasswordVersionStore();
        if (!$this->db->transBegin()) throw new RuntimeException('Sesi lain belum dapat dikeluarkan.');
        try {
            // Website sessions are cookie-based rather than rows in customer_api_tokens.
            // Rotating this shared version invalidates them on their next request while
            // updating this app's family keeps the requesting device signed in.
            $newVersion = bin2hex(random_bytes(32));
            $versionRow = $this->db->table('customer_password_sessions')
                ->where('email_user', $email)->get()->getRowArray();
            if ($versionRow) {
                $ok = $this->db->table('customer_password_sessions')
                    ->where('email_user', $email)->update(['version' => $newVersion]);
            } else {
                $ok = $this->db->table('customer_password_sessions')->insert([
                    'email_user' => $email,
                    'version' => $newVersion,
                ]);
            }
            if (!$ok) throw new RuntimeException('Versi sesi tidak dapat diperbarui.');

            $this->db->table('customer_api_tokens')
                ->where(['email_user' => $email, 'family_id' => $currentFamily, 'revoked_at' => null])
                ->update(['password_version' => $newVersion]);
            if ($this->db->affectedRows() < 1) throw new RuntimeException('Sesi perangkat ini tidak dapat dipertahankan.');

            $otherFamilies = $this->db->table('customer_api_tokens')
                ->select('family_id')->distinct()
                ->where('email_user', $email)
                ->where('family_id !=', $currentFamily)
                ->where('revoked_at', null)
                ->get()->getResultArray();
            $familyIds = array_values(array_filter(array_unique(array_column($otherFamilies, 'family_id')), 'is_string'));
            if ($familyIds) {
                $this->db->table('customer_api_tokens')
                    ->where('email_user', $email)
                    ->whereIn('family_id', $familyIds)
                    ->where('revoked_at', null)
                    ->update(['revoked_at' => $this->now()]);
            }
            if ($this->db->transStatus() === false || !$this->db->transCommit()) {
                throw new RuntimeException('Pencabutan sesi lain tidak berhasil disimpan.');
            }
            return count($familyIds);
        } catch (\Throwable $error) {
            if ((int)$this->db->transDepth > 0) $this->db->transRollback();
            throw $error;
        }
    }

    /** Return safe metadata for active Android sessions owned by the bearer session. */
    public function activeSessionsForAccess(string $plainToken): array
    {
        $current = $this->activeAccessRow($plainToken);
        if (!$current) throw new RuntimeException('Sesi aktif tidak dapat diverifikasi.');

        $email = (string) $current['email_user'];
        $currentFamily = (string) $current['family_id'];
        $rows = $this->db->table('customer_api_tokens')
            ->select('family_id, device_name, created_at, expires_at, password_version')
            ->where(['email_user' => $email, 'token_type' => 'refresh', 'revoked_at' => null])
            ->where('expires_at >', $this->now())
            ->orderBy('created_at', 'DESC')
            ->limit(30)
            ->get()->getResultArray();

        $active = [];
        foreach ($rows as $row) {
            if (!$this->passwordVersions->matches($email, (string) ($row['password_version'] ?? ''))) continue;
            $family = (string) $row['family_id'];
            $active[$family] = [
                'session_id' => $family,
                'device_name' => trim((string) ($row['device_name'] ?? '')) ?: 'Perangkat Android',
                'created_at' => $this->utcIso((string) $row['created_at']) ?? '',
                'expires_at' => $this->utcIso((string) $row['expires_at']) ?? '',
                'is_current' => hash_equals($currentFamily, $family),
                'last_active_at' => null,
            ];
        }

        if (!isset($active[$currentFamily])) {
            $active[$currentFamily] = [
                'session_id' => $currentFamily,
                'device_name' => trim((string) ($current['device_name'] ?? '')) ?: 'Perangkat Android',
                'created_at' => $this->utcIso((string) $current['created_at']) ?? '',
                'expires_at' => $this->utcIso((string) $current['expires_at']) ?? '',
                'is_current' => true,
                'last_active_at' => $this->utcIso($current['last_used_at'] ?? null),
            ];
        }

        if ($active) {
            $activity = $this->db->table('customer_api_tokens')
                ->select('family_id, MAX(last_used_at) AS last_active_at')
                ->where('email_user', $email)
                ->where('token_type', 'access')
                ->whereIn('family_id', array_keys($active))
                ->groupBy('family_id')->get()->getResultArray();
            foreach ($activity as $row) {
                $family = (string) $row['family_id'];
                if (isset($active[$family])) $active[$family]['last_active_at'] = $this->utcIso($row['last_active_at'] ?? null);
            }
        }

        return array_values($active);
    }

    /** Revoke only a different active Android session belonging to this account. */
    public function revokeSessionForAccess(string $plainToken, string $sessionId): void
    {
        if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $sessionId)) {
            throw new \DomainException('Sesi perangkat tidak ditemukan. Muat ulang daftar sesi.');
        }
        $current = $this->activeAccessRow($plainToken);
        if (!$current) throw new RuntimeException('Sesi aktif tidak dapat diverifikasi.');
        if (hash_equals((string) $current['family_id'], $sessionId)) {
            throw new \DomainException('Perangkat yang sedang digunakan tidak dapat dikeluarkan dari sini.');
        }

        $email = (string) $current['email_user'];
        $target = $this->db->table('customer_api_tokens')->where([
            'email_user' => $email,
            'family_id' => $sessionId,
            'token_type' => 'refresh',
            'revoked_at' => null,
        ])->where('expires_at >', $this->now())->get()->getRowArray();
        if (!$target || !$this->passwordVersions->matches($email, (string) ($target['password_version'] ?? ''))) {
            throw new \DomainException('Sesi perangkat tidak aktif atau sudah dikeluarkan.');
        }

        if (!$this->db->transBegin()) throw new RuntimeException('Sesi perangkat belum dapat dikeluarkan.');
        try {
            $updated = $this->db->table('customer_api_tokens')
                ->where(['email_user' => $email, 'family_id' => $sessionId, 'revoked_at' => null])
                ->update(['revoked_at' => $this->now()]);
            if (!$updated || $this->db->affectedRows() < 1 || $this->db->transStatus() === false || !$this->db->transCommit()) {
                throw new RuntimeException('Pencabutan sesi perangkat tidak berhasil disimpan.');
            }
        } catch (\Throwable $error) {
            if ((int) $this->db->transDepth > 0) $this->db->transRollback();
            if ($error instanceof \DomainException) throw $error;
            throw new RuntimeException('Sesi perangkat belum dapat dikeluarkan.');
        }
    }

    private function activeAccessRow(string $plainToken): ?array
    {
        if (strlen($plainToken) < 32 || strlen($plainToken) > 128) return null;
        $row = $this->db->table('customer_api_tokens')->where([
            'token_hash' => hash('sha256', $plainToken),
            'token_type' => 'access',
            'revoked_at' => null,
        ])->where('expires_at >', $this->now())->get()->getRowArray();
        if (!$row || !$this->passwordVersions->matches(
            (string) $row['email_user'],
            (string) ($row['password_version'] ?? '')
        )) return null;
        return $row;
    }

    private function utcIso(?string $value): ?string
    {
        if ($value === null || trim($value) === '') return null;
        $timestamp = strtotime(trim($value) . ' UTC');
        return $timestamp === false ? null : gmdate('Y-m-d\TH:i:s\Z', $timestamp);
    }

    private function createToken(string $email, string $familyId, string $type, int $ttl, ?string $deviceName): array
    {
        $token = rtrim(strtr(base64_encode(random_bytes(48)), '+/', '-_'), '=');
        $hash = hash('sha256', $token);
        $created = $this->now();
        $expires = (new DateTimeImmutable($created, new DateTimeZone('UTC')))->modify('+' . $ttl . ' seconds')->format('Y-m-d H:i:s');
        if (!$this->db->table('customer_api_tokens')->insert([
            'email_user' => $email, 'family_id' => $familyId, 'token_hash' => $hash,
            'token_type' => $type, 'password_version' => $this->passwordVersions->current($email),
            'device_name' => $deviceName ? mb_substr(trim($deviceName), 0, 100) : null,
            'expires_at' => $expires, 'created_at' => $created,
        ])) throw new RuntimeException('Sesi API tidak dapat dibuat.');
        return ['token' => $token, 'hash' => $hash];
    }

    private function eligibleCustomer(string $email): bool
    {
        $user = $this->users->getUser($email);
        return $user && (string)($user['role'] ?? '') === '0' && (string)($user['active'] ?? '') === '1';
    }

    /** API sessions must be bound to the password-reset version store; fail closed if missing. */
    private function requirePasswordVersionStore(): void
    {
        if (!$this->db->tableExists('customer_password_sessions')) {
            throw new RuntimeException('Penyimpanan versi sesi password belum tersedia.');
        }
    }

    private function now(): string { return gmdate('Y-m-d H:i:s'); }

    private function revokeFamily(string $familyId): void
    {
        $this->db->table('customer_api_tokens')->where('family_id', $familyId)->where('revoked_at', null)->update(['revoked_at' => $this->now()]);
    }

    private function uuid(): string
    {
        $bytes = random_bytes(16);
        $bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40);
        $bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($bytes), 4));
    }
}
