<?php

use App\Services\Notifications\CustomerPushNotifications;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Database;

final class CustomerPushNotificationsTest extends CIUnitTestCase
{
    private BaseConnection $connection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = Database::connect([
            'DBDriver' => 'SQLite3', 'database' => ':memory:', 'DBPrefix' => '', 'DBDebug' => true,
        ], false);
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

    public function testRepeatedOrderEventIsPersistedOnlyOnce(): void
    {
        $outbox = new CustomerPushNotifications($this->connection);
        $event = ['order_id' => 'L-TEST-1', 'status' => 'Proses'];

        $this->assertTrue($outbox->enqueue(' BUYER@EXAMPLE.TEST ', 'order_processing', 'Pesanan diproses', 'Pesanan diperbarui.', $event, 'processing:L-TEST-1'));
        $this->assertTrue($outbox->enqueue('buyer@example.test', 'order_processing', 'Pesanan diproses', 'Pesanan diperbarui.', $event, 'processing:L-TEST-1'));

        $this->assertSame(1, $this->connection->table('customer_push_notifications')->countAllResults());
        $row = $this->connection->table('customer_push_notifications')->get()->getRowArray();
        $this->assertSame('buyer@example.test', $row['email_user']);
        $this->assertSame(hash('sha256', 'processing:L-TEST-1'), $row['dedupe_key']);
        $this->assertSame($event, json_decode($row['payload'], true));
        $this->assertSame('pending', $row['status']);
    }

    public function testMissingOutboxStorageFailsClosed(): void
    {
        $this->connection->query('DROP TABLE customer_push_notifications');
        $outbox = new CustomerPushNotifications($this->connection);

        $this->assertFalse($outbox->enqueue('buyer@example.test', 'order_processing', 'Title', 'Body', [], 'event:1'));
    }

    public function testInvalidRecipientOrDedupeIdentityCannotEnterOutbox(): void
    {
        $outbox = new CustomerPushNotifications($this->connection);

        $this->assertFalse($outbox->enqueue('not-an-email', 'order_processing', 'Title', 'Body', [], 'event:1'));
        $this->assertFalse($outbox->enqueue('buyer@example.test', 'order_processing', 'Title', 'Body', [], '  '));
        $this->assertSame(0, $this->connection->table('customer_push_notifications')->countAllResults());
    }
}
