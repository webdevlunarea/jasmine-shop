<?php

namespace App\Services\Notifications;

use CodeIgniter\Database\BaseConnection;
use RuntimeException;

/** Atomic database-wide campaign cooldown shared by email and FCM senders. */
final class MarketingCampaignRateLimiter
{
    public function claim(BaseConnection $db): void
    {
        if (!$db->tableExists('customer_marketing_campaign_guard')) {
            throw new RuntimeException('Marketing campaign guard schema is not deployed.');
        }
        $now = gmdate('Y-m-d H:i:s');
        $cutoff = gmdate('Y-m-d H:i:s', time() - 60);
        $select = $db->table('customer_marketing_campaign_guard')->where('id', 1)->getCompiledSelect();
        if ($db->DBDriver !== 'SQLite3') $select .= ' FOR UPDATE';
        $guard = $db->query($select)->getRowArray();
        if (!$guard) throw new RuntimeException('Marketing campaign guard row is missing.');
        if (!empty($guard['last_campaign_at']) && (string)$guard['last_campaign_at'] > $cutoff) {
            throw new RuntimeException('GLOBAL_MARKETING_COOLDOWN');
        }
        $today = gmdate('Y-m-d');
        $count = ($guard['campaign_date_utc'] ?? null) === $today ? (int)$guard['campaign_count'] : 0;
        if ($count >= 5) throw new RuntimeException('GLOBAL_MARKETING_DAILY_LIMIT');
        $saved = $db->table('customer_marketing_campaign_guard')->where('id', 1)->update([
            'last_campaign_at' => $now, 'campaign_date_utc' => $today,
            'campaign_count' => $count + 1, 'updated_at' => $now,
        ]);
        if (!$saved || $db->affectedRows() !== 1) throw new RuntimeException('Marketing campaign guard update failed.');
    }
}
