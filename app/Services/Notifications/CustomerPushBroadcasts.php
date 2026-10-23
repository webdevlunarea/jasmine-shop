<?php

namespace App\Services\Notifications;

use CodeIgniter\Database\BaseConnection;
use InvalidArgumentException;
use RuntimeException;

/** Queues admin broadcasts for Android customers who have active push devices. */
final class CustomerPushBroadcasts
{
    private const TYPES = [
        'voucher' => 'broadcast_voucher',
        'flash_sale' => 'broadcast_flash_sale',
        'event' => 'broadcast_event',
        'general' => 'broadcast_general',
    ];

    public function __construct(private BaseConnection $db)
    {
    }

    private function normalizedEmailJoin(string $left, string $right): string
    {
        $comparison = 'LOWER(TRIM(' . $left . ')) = LOWER(TRIM(' . $right . '))';
        if (stripos($this->db->DBDriver, 'mysql') !== false) {
            // Production columns may use different utf8mb4 collations (e.g. general_ci and 0900_ai_ci).
            $comparison = 'LOWER(TRIM(' . $left . ')) COLLATE utf8mb4_bin = LOWER(TRIM(' . $right . ')) COLLATE utf8mb4_bin';
        }
        return $comparison;
    }

    public function activeAndroidRecipientCount(): int
    {
        if (!$this->db->tableExists('customer_push_devices') || !$this->db->tableExists('user')) return 0;

        $row = $this->db->table('customer_push_devices d')
            ->select('COUNT(DISTINCT LOWER(TRIM(d.email_user))) AS total', false)
            ->join('user u', $this->normalizedEmailJoin('u.email', 'd.email_user'), 'inner', false)
            ->where(['d.is_active' => 1, 'u.role' => '0', 'u.active' => '1'])
            ->where("LOWER(TRIM(d.platform)) = 'android'", null, false)
            ->get()->getRowArray();

        return (int) ($row['total'] ?? 0);
    }

    public function queue(string $kind, string $title, string $body, ?string $imageUrl = null): int
    {
        $kind = trim($kind);
        $title = trim($title);
        $body = trim($body);
        if (!isset(self::TYPES[$kind]) || $title === '' || mb_strlen($title) > 100 || $body === '' || mb_strlen($body) > 240) {
            throw new InvalidArgumentException('Broadcast content is invalid.');
        }
        if (!$this->db->tableExists('customer_push_devices') || !$this->db->tableExists('customer_push_notifications')
            || !$this->db->tableExists('customer_marketing_campaign_guard')) {
            throw new RuntimeException('Push broadcast storage is unavailable.');
        }

        $audience = $this->db->table('customer_push_devices d')
            ->distinct()->select('d.email_user')
            ->join('user u', $this->normalizedEmailJoin('u.email', 'd.email_user'), 'inner', false)
            ->where(['d.is_active' => 1, 'u.role' => '0', 'u.active' => '1'])
            ->where("LOWER(TRIM(d.platform)) = 'android'", null, false)
            ->get()->getResultArray();
        $recipients = [];
        foreach ($audience as $row) {
            $email = strtolower(trim((string) ($row['email_user'] ?? '')));
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) $recipients[$email] = true;
        }
        if (!$recipients) return 0;

        $now = gmdate('Y-m-d H:i:s');
        $campaignId = bin2hex(random_bytes(16));
        $type = self::TYPES[$kind];
        $payloadData = [
            'type' => $type,
            'campaign_id' => $campaignId,
        ];
        if ($imageUrl !== null && $imageUrl !== '') {
            $parts = parse_url($imageUrl);
            $baseHost = strtolower((string) parse_url(base_url(), PHP_URL_HOST));
            if (!is_array($parts)
                || strtolower((string) ($parts['scheme'] ?? '')) !== 'https'
                || strtolower((string) ($parts['host'] ?? '')) !== $baseHost
                || !str_starts_with((string) ($parts['path'] ?? ''), '/uploads/push-broadcasts/')) {
                throw new InvalidArgumentException('Broadcast image URL is not an approved Lunarea HTTPS image.');
            }
            $payloadData['image_url'] = $imageUrl;
        }
        $payload = json_encode($payloadData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        $rows = [];
        foreach (array_keys($recipients) as $email) {
            $rows[] = [
                'email_user' => $email,
                'notification_type' => $type,
                'title' => $title,
                'body' => $body,
                'payload' => $payload,
                'dedupe_key' => 'broadcast:' . $campaignId . ':' . hash('sha256', $email),
                'status' => 'pending',
                'attempts' => 0,
                'next_attempt_at' => $now,
                'sent_at' => null,
                'last_error' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        $this->db->transBegin();
        try {
            (new MarketingCampaignRateLimiter())->claim($this->db);
            foreach (array_chunk($rows, 200) as $batch) {
                if ($this->db->table('customer_push_notifications')->insertBatch($batch) === false) {
                    throw new RuntimeException('Push broadcast queue insert failed.');
                }
            }
            if ($this->db->transStatus() === false) throw new RuntimeException('Push broadcast transaction failed.');
            $this->db->transCommit();
        } catch (\Throwable $error) {
            $this->db->transRollback();
            throw $error;
        }

        return count($rows);
    }
}
