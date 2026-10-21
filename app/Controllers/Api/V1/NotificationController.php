<?php

namespace App\Controllers\Api\V1;

use Config\Database;

class NotificationController extends ApiController
{
    private static function utcIso8601(?string $value): ?string
    {
        if ($value === null || trim($value) === '') return null;

        try {
            return (new \DateTimeImmutable($value, new \DateTimeZone('UTC')))
                ->setTimezone(new \DateTimeZone('UTC'))
                ->format('Y-m-d\\TH:i:s\\Z');
        } catch (\Throwable) {
            return null;
        }
    }

    public function index()
    {
        $db = Database::connect();
        if (!$db->tableExists('customer_push_notifications')) {
            return $this->failure('NOTIFICATIONS_UNAVAILABLE', 'Pemberitahuan belum tersedia.', 503);
        }

        $email = $this->authEmail();
        $query = $db->table('customer_push_notifications')
            ->select('id, notification_type, title, body, status, created_at, sent_at')
            ->where('email_user', $email)
            ->whereIn('status', ['sent', 'read', 'awaiting_device'])
            ->orderBy('created_at', 'DESC')
            ->limit(50)
            ->get();
        $rows = $query->getResultArray();
        $unread = $db->table('customer_push_notifications')
            ->where('email_user', $email)
            ->where('status', 'sent')
            ->countAllResults();

        return $this->success([
            'notifications' => array_map(static fn(array $row): array => [
                'id' => (int)$row['id'],
                'notification_type' => (string)$row['notification_type'],
                'title' => (string)$row['title'],
                'body' => (string)$row['body'],
                'status' => (string)$row['status'],
                // Stored in UTC; include the zone so Android can render it as WIB.
                'created_at' => self::utcIso8601((string)$row['created_at']),
                'sent_at' => self::utcIso8601($row['sent_at'] === null ? null : (string)$row['sent_at']),
            ], $rows),
            'unread_count' => (int)$unread,
        ]);
    }

    public function markRead()
    {
        $db = Database::connect();
        if (!$db->tableExists('customer_push_notifications')) {
            return $this->failure('NOTIFICATIONS_UNAVAILABLE', 'Pemberitahuan belum tersedia.', 503);
        }

        $payload = $this->payload();
        $ids = $payload['ids'] ?? [];
        if (!is_array($ids) || count($ids) > 50) {
            return $this->failure('VALIDATION_ERROR', 'Daftar pemberitahuan tidak valid.', 422);
        }
        $ids = array_values(array_unique(array_filter(array_map(static function ($id) {
            return filter_var($id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: null;
        }, $ids))));

        $builder = $db->table('customer_push_notifications')
            ->where('email_user', $this->authEmail())
            ->where('status', 'sent');
        if ($ids) $builder->whereIn('id', $ids);
        $builder->update(['status' => 'read', 'updated_at' => gmdate('Y-m-d H:i:s')]);

        return $this->success(['marked_read' => $db->affectedRows()]);
    }
}
