<?php

use App\Services\Notifications\CustomerPushBroadcasts;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Database;

final class CustomerPushBroadcastsTest extends CIUnitTestCase
{
    private BaseConnection $connection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = Database::connect([
            'DBDriver' => 'SQLite3', 'database' => ':memory:', 'DBPrefix' => '', 'DBDebug' => true,
        ], false);
        $this->connection->query('CREATE TABLE customer_push_devices (
            id INTEGER PRIMARY KEY AUTOINCREMENT, email_user TEXT NOT NULL, device_token_hash TEXT NOT NULL,
            device_token TEXT NOT NULL, platform TEXT NOT NULL, device_name TEXT NULL,
            is_active INTEGER NOT NULL, last_seen_at TEXT NOT NULL, created_at TEXT NOT NULL, updated_at TEXT NOT NULL
        )');
        $this->connection->query('CREATE TABLE customer_push_notifications (
            id INTEGER PRIMARY KEY AUTOINCREMENT, email_user TEXT NOT NULL, notification_type TEXT NOT NULL,
            title TEXT NOT NULL, body TEXT NOT NULL, payload TEXT NULL, dedupe_key TEXT NOT NULL UNIQUE,
            status TEXT NOT NULL, attempts INTEGER NOT NULL, next_attempt_at TEXT NOT NULL,
            sent_at TEXT NULL, last_error TEXT NULL, created_at TEXT NOT NULL, updated_at TEXT NOT NULL
        )');
        $this->connection->query('CREATE TABLE customer_marketing_preferences (
            email_user TEXT PRIMARY KEY, email_promotions INTEGER NOT NULL DEFAULT 0, push_promotions INTEGER NOT NULL DEFAULT 0,
            email_consent_at TEXT NULL, push_consent_at TEXT NULL, updated_at TEXT NOT NULL
        )');
        $this->connection->query('CREATE TABLE user (email TEXT PRIMARY KEY, role TEXT NOT NULL, active TEXT NOT NULL)');
        $this->connection->query('CREATE TABLE customer_marketing_campaign_guard (id INTEGER PRIMARY KEY, last_campaign_at TEXT NULL, campaign_date_utc TEXT NULL, campaign_count INTEGER NOT NULL DEFAULT 0, updated_at TEXT NOT NULL)');
        $this->connection->table('customer_marketing_campaign_guard')->insert(['id' => 1, 'last_campaign_at' => null, 'campaign_date_utc' => null, 'campaign_count' => 0, 'updated_at' => gmdate('Y-m-d H:i:s')]);
    }

    protected function tearDown(): void
    {
        $this->connection->close();
        parent::tearDown();
    }

    private function addDevice(string $email, string $platform = 'android', int $active = 1): void
    {
        $now = gmdate('Y-m-d H:i:s');
        $this->connection->table('customer_push_devices')->insert([
            'email_user' => $email, 'device_token_hash' => hash('sha256', $email . random_int(1, 1000000)),
            'device_token' => 'encrypted-test-token', 'platform' => $platform, 'device_name' => null,
            'is_active' => $active, 'last_seen_at' => $now, 'created_at' => $now, 'updated_at' => $now,
        ]);
    }

    private function optIn(string $email): void
    {
        $normalized = strtolower($email);
        $this->connection->table('user')->ignore(true)->insert(['email' => $normalized, 'role' => '0', 'active' => '1']);
        $this->connection->table('customer_marketing_preferences')->ignore(true)->insert([
            'email_user' => $normalized, 'email_promotions' => 0, 'push_promotions' => 1,
            'email_consent_at' => null, 'push_consent_at' => gmdate('Y-m-d H:i:s'), 'updated_at' => gmdate('Y-m-d H:i:s'),
        ]);
    }

    public function testBroadcastQueuesOnlyDistinctActiveAndroidAccounts(): void
    {
        $this->addDevice('buyer@example.test');
        $this->optIn('buyer@example.test');
        $this->addDevice('BUYER@example.test');
        $this->addDevice('inactive@example.test', 'android', 0);
        $this->addDevice('web@example.test', 'web', 1);
        $broadcasts = new CustomerPushBroadcasts($this->connection);

        $this->assertSame(1, $broadcasts->activeAndroidRecipientCount());
        $this->assertSame(1, $broadcasts->queue('voucher', 'Voucher Lunarea', 'Gunakan voucher hari ini.'));

        $row = $this->connection->table('customer_push_notifications')->get()->getRowArray();
        $this->assertSame('buyer@example.test', $row['email_user']);
        $this->assertSame('broadcast_voucher', $row['notification_type']);
        $this->assertSame('pending', $row['status']);
        $this->assertStringStartsWith('broadcast:', $row['dedupe_key']);
        $this->assertSame('broadcast_voucher', json_decode($row['payload'], true)['type']);
    }

    public function testInvalidBroadcastAndEmptyAudienceDoNotQueueAnything(): void
    {
        $broadcasts = new CustomerPushBroadcasts($this->connection);
        $this->assertSame(0, $broadcasts->queue('event', 'Acara', 'Info acara.'));

        $this->addDevice('buyer@example.test');
        $this->optIn('buyer@example.test');
        $this->expectException(InvalidArgumentException::class);
        $broadcasts->queue('unknown', 'Judul', 'Pesan');
    }

    public function testDatabaseGuardStopsRapidGlobalCampaigns(): void
    {
        $this->addDevice('buyer@example.test');
        $this->optIn('buyer@example.test');
        $broadcasts = new CustomerPushBroadcasts($this->connection);
        $this->assertSame(1, $broadcasts->queue('voucher', 'Voucher', 'Promo pertama.'));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('GLOBAL_MARKETING_COOLDOWN');
        $broadcasts->queue('event', 'Acara', 'Promo berikutnya.');
    }
}
