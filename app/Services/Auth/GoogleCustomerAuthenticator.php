<?php

namespace App\Services\Auth;

use App\Models\PembeliModel;
use App\Models\UserModel;
use App\Models\VoucherClaimedModel;
use App\Models\VoucherModel;
use CodeIgniter\Database\BaseConnection;
use DomainException;
use RuntimeException;

/** Verifies Google ID tokens and resolves them to customer-only Lunarea accounts. */
final class GoogleCustomerAuthenticator
{
    private BaseConnection $db;
    private UserModel $users;
    private PembeliModel $profiles;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? \Config\Database::connect();
        $this->users = new UserModel($this->db);
        $this->profiles = new PembeliModel($this->db);
    }

    /** @return array{email:string,name:string,phone:string} */
    public function authenticate(string $idToken): array
    {
        if ($idToken === '' || strlen($idToken) > 8192
            || !preg_match('/^[A-Za-z0-9_-]+\.[A-Za-z0-9_-]+\.[A-Za-z0-9_-]+$/', $idToken)) {
            throw new DomainException('Token Google tidak valid.');
        }
        $clientId = trim((string) env('GOOGLE_CLIENT_ID', ''));
        if ($clientId === '') throw new RuntimeException('Google client ID is not configured.');

        try {
            $client = new \Google\Client();
            $client->setClientId($clientId);
            $claims = $client->verifyIdToken($idToken);
        } catch (\Throwable $e) {
            // Never log the ID token or user claims. The exception class/code is
            // enough to distinguish Google key-fetch/network failures from app errors.
            log_message('error', sprintf(
                'Google ID token verification infrastructure failure (%s, code %s).',
                get_class($e),
                (string) $e->getCode()
            ));
            throw new DomainException('Token Google tidak dapat diverifikasi.');
        }

        $email = strtolower(trim((string) ($claims['email'] ?? '')));
        $googleId = trim((string) ($claims['sub'] ?? ''));
        $verified = filter_var($claims['email_verified'] ?? false, FILTER_VALIDATE_BOOLEAN);
        if (!$claims || $googleId === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)
            || strlen($email) > 254 || !$verified) {
            throw new DomainException('Akun Google belum memiliki email terverifikasi.');
        }

        $name = trim((string) ($claims['name'] ?? ''));
        $picture = trim((string) ($claims['picture'] ?? ''));
        if ($picture !== '' && (!filter_var($picture, FILTER_VALIDATE_URL)
            || !in_array(strtolower((string) parse_url($picture, PHP_URL_SCHEME)), ['https'], true))) {
            $picture = '';
        }

        $account = $this->resolveCustomer($email, $googleId, $name, $picture);
        return [
            'email' => (string) $account['email'],
            'name' => (string) ($account['name'] ?? ''),
            'phone' => (string) ($account['phone'] ?? ''),
        ];
    }

    private function resolveCustomer(string $email, string $googleId, string $name, string $picture): array
    {
        $stage = 'begin_transaction';
        try {
            if (!$this->db->transBegin()) throw new RuntimeException('Akun Google tidak dapat diproses.');
            // Prefer the immutable Google subject if it was already linked, even if
            // the user later changes their Google email address.
            $stage = 'find_linked_google_account';
            $byGoogle = $this->db->table('user')->where('google_id', $googleId)->get()->getRowArray();
            $stage = 'find_email_account';
            $user = $byGoogle ?: $this->users->getUser($email);

            if ($user && (string) ($user['role'] ?? '') !== '0') {
                throw new DomainException('Login Google hanya tersedia untuk akun pelanggan.');
            }
            if ($user && !empty($user['google_id']) && !hash_equals((string) $user['google_id'], $googleId)) {
                throw new DomainException('Email ini sudah terhubung ke akun Google lain.');
            }

            if (!$user) {
                $stage = 'create_customer_account';
                $emailOwner = $this->users->getUser($email);
                if ($emailOwner) throw new DomainException('Akun ini belum dapat ditautkan dengan Google.');
                $this->users->insert([
                    'email' => $email,
                    'sandi' => password_hash(bin2hex(random_bytes(32)), PASSWORD_DEFAULT),
                    'role' => '0', 'otp' => '0', 'active' => '1', 'waktu_otp' => '0',
                    'google_id' => $googleId, 'auth_provider' => 'google',
                ]);
                $user = $this->users->getUser($email);
                if (!$user) throw new RuntimeException('Akun Google tidak berhasil dibuat.');
                $stage = 'create_customer_profile';
                $this->profiles->insert([
                    'email_user' => $email,
                    'nama' => mb_substr($name !== '' ? $name : $email, 0, 120),
                    'nohp' => '', 'alamat' => json_encode([]), 'wishlist' => json_encode([]),
                    'keranjang' => json_encode([]), 'transaksi' => json_encode([]),
                    'poin' => json_encode([]), 'tier' => json_encode(['label' => 'bronze', 'data' => []]),
                    'foto' => $picture !== '' ? $picture : '/imguser/ZGVmYXVsdA==',
                ]);
                $stage = 'claim_automatic_vouchers';
                $this->claimAutomaticVouchers($email);
            } else {
                $accountEmail = strtolower((string) $user['email']);
                $update = [
                    'google_id' => $googleId,
                    'active' => '1',
                ];
                if (($user['auth_provider'] ?? 'email') === 'email') $update['auth_provider'] = 'google';
                $stage = 'link_google_account';
                if (!$this->db->table('user')->where('email', $accountEmail)->update($update)) {
                    throw new RuntimeException('Akun Google tidak berhasil diperbarui.');
                }
                $user = $this->users->getUser($accountEmail);
                $stage = 'load_customer_profile';
                $profile = $this->profiles->getPembeli($accountEmail);
                if (!$profile) {
                    $stage = 'create_customer_profile';
                    $this->profiles->insert([
                        'email_user' => $accountEmail,
                        'nama' => mb_substr($name !== '' ? $name : $accountEmail, 0, 120),
                        'nohp' => '', 'alamat' => json_encode([]), 'wishlist' => json_encode([]),
                        'keranjang' => json_encode([]), 'transaksi' => json_encode([]),
                        'poin' => json_encode([]), 'tier' => json_encode(['label' => 'bronze', 'data' => []]),
                        'foto' => $picture !== '' ? $picture : '/imguser/ZGVmYXVsdA==',
                    ]);
                    $profile = $this->profiles->getPembeli($accountEmail);
                } elseif ($picture !== '' && (($profile['foto'] ?? '') === '' || ($profile['foto'] ?? '') === '/imguser/ZGVmYXVsdA==')) {
                    $stage = 'update_customer_profile_photo';
                    $this->db->table('pembeli')->where('email_user', $accountEmail)->update(['foto' => $picture]);
                    $profile['foto'] = $picture;
                }
            }

            $stage = 'verify_customer_profile';
            if (!$user || (string) ($user['role'] ?? '') !== '0') {
                throw new RuntimeException('Akun pelanggan tidak tersedia.');
            }
            $accountEmail = strtolower((string) $user['email']);
            $profile = $this->profiles->getPembeli($accountEmail);
            if (!$profile || !$this->db->transStatus()) throw new RuntimeException('Profil akun tidak dapat disiapkan.');
            $stage = 'commit_account_transaction';
            if (!$this->db->transCommit()) throw new RuntimeException('Akun Google tidak berhasil disimpan.');
            return ['email' => $accountEmail, 'name' => $profile['nama'] ?? '', 'phone' => $profile['nohp'] ?? ''];
        } catch (\Throwable $e) {
            if ((int) $this->db->transDepth > 0) $this->db->transRollback();
            if (!$e instanceof DomainException) {
                log_message('error', sprintf(
                    'Google customer resolution failed at %s: %s (code %s).',
                    $stage,
                    get_class($e),
                    (string) $e->getCode()
                ));
            }
            throw $e;
        }
    }

    private function claimAutomaticVouchers(string $email): void
    {
        if (!$this->db->tableExists('voucher') || !$this->db->tableExists('voucher_claimed')) return;
        $voucherModel = new VoucherModel($this->db);
        $claimedModel = new VoucherClaimedModel($this->db);
        $now = strtotime('+7 Hours');
        $date = date('Y-m-d', $now);
        foreach ($voucherModel->where(['active' => true, 'auto_claimed' => true])->findAll() as $index => $voucher) {
            $expiry = !empty($voucher['durasi'])
                ? date('Y-m-d', strtotime((string) $voucher['durasi'], $now))
                : null;
            $id = (string) ($now + $index);
            while ($claimedModel->find($id)) $id = (string) ((int) $id + 1);
            if (!$claimedModel->insert([
                'id' => $id, 'id_voucher' => $voucher['id'], 'kadaluarsa' => $expiry,
                'email_user' => $email, 'active' => true,
            ])) throw new RuntimeException('Voucher akun baru tidak berhasil disiapkan.');
        }
    }
}
