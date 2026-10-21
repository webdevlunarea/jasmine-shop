<?php

namespace App\Services\Referrals;

use CodeIgniter\Database\BaseConnection;
use RuntimeException;

/** Server-authoritative Android-only referral rules, attribution, and rewards. */
final class AndroidReferralProgram
{
    private BaseConnection $db;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? \Config\Database::connect();
    }

    public static function defaults(): array
    {
        return ['enabled' => 0, 'referrer_reward_points' => 5000, 'minimum_subtotal' => 300000,
            'reward_trigger_status' => 'Selesai', 'point_expiry_days' => 365];
    }

    public function assertReady(): void
    {
        foreach (['android_referral_settings', 'android_referral_codes', 'android_referral_attributions', 'android_referral_rewards'] as $table) {
            if (!$this->db->tableExists($table)) throw new RuntimeException('Android referral schema is not installed');
        }
    }

    public function settings(): array
    {
        $this->assertReady();
        return array_merge(self::defaults(), $this->db->table('android_referral_settings')->where('id', 1)->get()->getRowArray() ?: []);
    }

    public function saveSettings(array $input): array
    {
        $this->assertReady();
        $enabled = in_array($input['enabled'] ?? null, ['1', 1, true, 'on'], true) ? 1 : 0;
        $points = filter_var($input['referrer_reward_points'] ?? null, FILTER_VALIDATE_INT);
        $minimum = filter_var($input['minimum_subtotal'] ?? null, FILTER_VALIDATE_INT);
        $expiry = filter_var($input['point_expiry_days'] ?? null, FILTER_VALIDATE_INT);
        $trigger = (string)($input['reward_trigger_status'] ?? '');
        if ($points === false || $points < 1 || $points > 1000000
            || $minimum === false || $minimum < 0 || $minimum > 1000000000
            || $expiry === false || $expiry < 30 || $expiry > 3650
            || $trigger !== 'Selesai') {
            throw new \DomainException('Pengaturan referral di luar batas yang diperbolehkan.');
        }
        $row = ['id' => 1, 'enabled' => $enabled, 'referrer_reward_points' => $points,
            'minimum_subtotal' => $minimum, 'reward_trigger_status' => $trigger,
            'point_expiry_days' => $expiry, 'updated_at' => $this->now()];
        $exists = $this->db->table('android_referral_settings')->where('id', 1)->countAllResults() > 0;
        if ($exists) $this->db->table('android_referral_settings')->where('id', 1)->update($row);
        else $this->db->table('android_referral_settings')->insert($row);
        if (!$this->db->transStatus()) throw new RuntimeException('Referral settings save failed');
        return $this->settings();
    }

    public function overview(string $email): array
    {
        $this->assertReady();
        $email = strtolower(trim($email));
        $settings = $this->settings();
        $row = $this->db->table('android_referral_codes')->where('email_user', $email)->get()->getRowArray();
        if (!$row) {
            $code = '';
            for ($i = 0; $i < 5; $i++) {
                $candidate = strtoupper(substr(bin2hex(random_bytes(8)), 0, 10));
                try {
                    $this->db->table('android_referral_codes')->insert(['email_user' => $email, 'referral_code' => $candidate, 'created_at' => $this->now()]);
                    $code = $candidate;
                    break;
                } catch (\Throwable $e) {
                    $row = $this->db->table('android_referral_codes')->where('email_user', $email)->get()->getRowArray();
                    if ($row) { $code = (string)$row['referral_code']; break; }
                }
            }
            if ($code === '') throw new RuntimeException('Referral code allocation failed');
        } else $code = (string)$row['referral_code'];

        $attribution = $this->db->table('android_referral_attributions a')
            ->select('a.referral_code, a.created_at, a.referrer_reward_points, a.minimum_subtotal, c.email_user AS referrer_email')
            ->join('android_referral_codes c', 'c.referral_code = a.referral_code')->where('a.invitee_email', $email)->get()->getRowArray();
        $counts = $this->db->table('android_referral_attributions a')
            ->select("COUNT(*) AS total, SUM(CASE WHEN r.state = 'awarded' THEN 1 ELSE 0 END) AS successful, SUM(CASE WHEN r.state = 'pending' THEN 1 ELSE 0 END) AS pending", false)
            ->join('android_referral_rewards r', 'r.invitee_email = a.invitee_email', 'left')
            ->where('a.referral_code', $code)->get()->getRowArray() ?: [];
        return ['enabled' => (int)$settings['enabled'] === 1, 'code' => $code,
            'share_url' => rtrim(base_url(), '/') . '/ref/' . rawurlencode($code),
            'reward_points' => (int)$settings['referrer_reward_points'], 'minimum_subtotal' => (int)$settings['minimum_subtotal'],
            'reward_trigger_status' => (string)$settings['reward_trigger_status'], 'point_expiry_days' => (int)$settings['point_expiry_days'],
            'attributed_by' => $attribution ? (string)$attribution['referrer_email'] : null,
            'stats' => ['invited' => (int)($counts['total'] ?? 0), 'successful' => (int)($counts['successful'] ?? 0), 'pending' => (int)($counts['pending'] ?? 0)]];
    }

    public function attribute(string $inviteeEmail, string $code): void
    {
        $this->assertReady();
        $inviteeEmail = strtolower(trim($inviteeEmail));
        $code = strtoupper(trim($code));
        $settings = $this->settings();
        if ((int)$settings['enabled'] !== 1) throw new \DomainException('Program ajak teman sedang tidak aktif.');
        if (!preg_match('/^[A-Z0-9]{6,12}$/', $code)) throw new \DomainException('Kode referral tidak valid.');
        $referrer = $this->db->table('android_referral_codes')->where('referral_code', $code)->get()->getRowArray();
        if (!$referrer) throw new \DomainException('Kode referral tidak ditemukan.');
        if (strtolower((string)$referrer['email_user']) === $inviteeEmail) throw new \DomainException('Kode referral sendiri tidak dapat digunakan.');
        if ($this->db->table('android_referral_attributions')->where('invitee_email', $inviteeEmail)->countAllResults() > 0) {
            throw new \DomainException('Akun ini sudah terhubung dengan kode referral.');
        }
        if ($this->db->table('pemesanan')->where('email_cus', $inviteeEmail)->countAllResults() > 0) {
            throw new \DomainException('Referral hanya dapat ditautkan sebelum pesanan pertama.');
        }
        $this->db->table('android_referral_attributions')->insert([
            'invitee_email' => $inviteeEmail, 'referral_code' => $code,
            'referrer_reward_points' => (int)$settings['referrer_reward_points'],
            'minimum_subtotal' => (int)$settings['minimum_subtotal'],
            'reward_trigger_status' => (string)$settings['reward_trigger_status'],
            'point_expiry_days' => (int)$settings['point_expiry_days'], 'created_at' => $this->now(),
        ]);
        if (!$this->db->transStatus()) throw new RuntimeException('Referral attribution save failed');
    }

    public function publicCode(string $code): bool
    {
        $this->assertReady();
        $code = strtoupper(trim($code));
        if (!preg_match('/^[A-Z0-9]{6,12}$/', $code)) return false;
        return (int)$this->settings()['enabled'] === 1
            && $this->db->table('android_referral_codes')->where('referral_code', $code)->countAllResults() === 1;
    }

    /** Must run inside the transaction which changes the Android order to its configured reward state. */
    public function rewardCompletedOrder(array $order): bool
    {
        foreach (['android_referral_settings', 'android_referral_codes', 'android_referral_attributions', 'android_referral_rewards'] as $table) {
            if (!$this->db->tableExists($table)) return false;
        }
        $email = strtolower(trim((string)($order['email_cus'] ?? '')));
        $orderId = trim((string)($order['id_midtrans'] ?? ''));
        $payment = json_decode((string)($order['data_mid'] ?? ''), true);
        if (!is_array($payment) || ($payment['_app_checkout_channel'] ?? '') !== 'android'
            || ($payment['_app_payment_environment'] ?? 'production') !== 'production'
            || $email === '' || $orderId === '') return false;
        $attribution = $this->db->table('android_referral_attributions')->where('invitee_email', $email)->get()->getRowArray();
        if (!$attribution || (string)$order['status'] !== (string)$attribution['reward_trigger_status']) return false;
        $existing = $this->db->table('android_referral_rewards')->where('order_id', $orderId)->get()->getRowArray();
        if ($existing) return false;
        $minimum = (int)$attribution['minimum_subtotal'];
        $subtotal = max(0, (int)($payment['_app_eligible_subtotal'] ?? 0));
        if ($subtotal < $minimum) return false;
        // A web or earlier Android order consumes the invitee's first-purchase eligibility.
        if ($this->db->table('pemesanan')->where('email_cus', $email)->where('id_midtrans !=', $orderId)->countAllResults() > 0) return false;
        $referrer = $this->db->table('android_referral_codes')->where('referral_code', (string)$attribution['referral_code'])->get()->getRowArray();
        if (!$referrer) return false;
        $reward = (int)$attribution['referrer_reward_points'];
        if ($reward < 1) return false;
        $buyerQuery = $this->db->table('pembeli')->where('email_user', (string)$referrer['email_user'])->getCompiledSelect();
        $buyer = $this->db->query($buyerQuery . ($this->db->DBDriver === 'SQLite3' ? '' : ' FOR UPDATE'))->getRowArray();
        if (!$buyer) throw new RuntimeException('Referral owner account is unavailable');
        $points = json_decode((string)($buyer['poin'] ?? '[]'), true);
        if (!is_array($points)) throw new RuntimeException('Referral owner points data is invalid');
        $today = new \DateTimeImmutable('now', new \DateTimeZone('Asia/Jakarta'));
        $expiry = $today->modify('+' . max(30, (int)$attribution['point_expiry_days']) . ' days')->format('Y-m-d');
        $points[] = ['kadaluarsa' => $expiry, 'nominal' => $reward, 'active' => true];
        $this->db->table('pembeli')->where('email_user', (string)$referrer['email_user'])->update(['poin' => json_encode(array_values($points), JSON_UNESCAPED_UNICODE)]);
        $this->db->table('point_history')->insert([
            'id' => time() + random_int(0, 999), 'label' => 'Bonus Ajak Teman', 'nominal' => $reward,
            'keterangan' => 'Referral Android untuk pesanan ' . $orderId, 'tanggal' => $today->format('Y-m-d'), 'email_user' => (string)$referrer['email_user'],
        ]);
        $this->db->table('android_referral_rewards')->insert([
            'order_id' => $orderId, 'referrer_email' => (string)$referrer['email_user'], 'invitee_email' => $email,
            'points' => $reward, 'eligible_subtotal' => $subtotal, 'state' => 'awarded', 'created_at' => $this->now(), 'awarded_at' => $this->now(),
        ]);
        if (!$this->db->transStatus()) throw new RuntimeException('Referral reward transaction failed');
        return true;
    }

    private function now(): string
    {
        return (new \DateTimeImmutable('now', new \DateTimeZone('Asia/Jakarta')))->format('Y-m-d H:i:s');
    }
}
