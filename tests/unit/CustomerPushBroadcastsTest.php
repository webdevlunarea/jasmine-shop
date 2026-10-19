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

    public function testBroadcastQueuesOnlyDistinctActiveAndroidAccounts(): void
    {
        $this->addDevice('buyer@example.test');
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
        $this->expectException(InvalidArgumentException::class);
        $broadcasts->queue('unknown', 'Judul', 'Pesan');
    }
}
