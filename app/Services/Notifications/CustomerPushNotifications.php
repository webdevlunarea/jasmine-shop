<?php

namespace App\Services\Notifications;

use CodeIgniter\Database\BaseConnection;
use Config\Database;

/** Durable, deduplicated push-notification outbox for customer order events. */
final class CustomerPushNotifications
{
    private BaseConnection $db;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? Database::connect();
    }

    public function enqueue(string $email, string $type, string $title, string $body, array $data, string $dedupeKey): bool
    {
        if (!$this->db->tableExists('customer_push_notifications')) return false;
        $email = strtolower(trim($email));
        $type = trim($type);
        $title = trim($title);
        $body = trim($body);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $type === '' || $title === '' || $body === '' || trim($dedupeKey) === '') return false;
        $now = gmdate('Y-m-d H:i:s');
        $fingerprint = substr(hash('sha256', $dedupeKey), 0, 64);
        if ($this->db->table('customer_push_notifications')->where('dedupe_key', $fingerprint)->countAllResults() > 0) return true;
        $inserted = $this->db->table('customer_push_notifications')->ignore(true)->insert([
            'email_user' => $email, 'notification_type' => substr($type, 0, 50),
            'title' => substr($title, 0, 100), 'body' => substr($body, 0, 240),
            'payload' => json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
            'dedupe_key' => $fingerprint, 'status' => 'pending',
            'attempts' => 0, 'next_attempt_at' => $now, 'created_at' => $now, 'updated_at' => $now,
        ]);
        if ($inserted) return true;
        // Treat a competing idempotent insert as success; real persistence errors remain visible to the caller.
        if ($this->db->table('customer_push_notifications')->where('dedupe_key', $fingerprint)->countAllResults() > 0) return true;
        return false;
    }
}
