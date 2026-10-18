<?php

use App\Commands\DispatchCustomerNotifications;
use App\Services\Notifications\FirebaseMessageSender;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Database;

final class DispatchCustomerNotificationsTest extends CIUnitTestCase
{
    private BaseConnection $connection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = Database::connect(['DBDriver' => 'SQLite3', 'database' => ':memory:', 'DBPrefix' => '', 'DBDebug' => true], false);
        $this->connection->query('CREATE TABLE customer_push_devices (
            id INTEGER PRIMARY KEY AUTOINCREMENT, email_user TEXT NOT NULL, device_token_hash TEXT NOT NULL,
            device_token TEXT NOT NULL, platform TEXT NOT NULL, device_name TEXT NULL,
            is_active INTEGER NOT NULL, last_seen_at TEXT NOT NULL, created_at TEXT NOT NULL, updated_at TEXT NOT NULL
        )');
        $this->connection->query('CREATE TABLE customer_push_notifications (
            id INTEGER PRIMARY KEY AUTOINCREMENT, email_user TEXT NOT NULL, notification_type TEXT NOT NULL,
            title TEXT NOT NULL, body TEXT NOT NULL, payload TEXT NULL, dedupe_key TEXT NOT NULL,
            status TEXT NOT NULL, attempts INTEGER NOT NULL, next_attempt_at TEXT NOT NULL,
            sent_at TEXT NULL, last_error TEXT NULL, created_at TEXT NOT NULL, updated_at TEXT NOT NULL
        )');
        $now = gmdate('Y-m-d H:i:s');
        $this->connection->table('customer_push_devices')->insert([
            'email_user' => 'buyer@example.test', 'device_token_hash' => str_repeat('a', 64),
            'device_token' => 'ciphertext', 'platform' => 'android', 'device_name' => null,
            'is_active' => 1, 'last_seen_at' => $now, 'created_at' => $now, 'updated_at' => $now,
        ]);
        $this->connection->table('customer_push_notifications')->insert([
            'email_user' => 'buyer@example.test', 'notification_type' => 'order_update',
            'title' => 'Pembaruan pesanan', 'body' => 'Pesanan diperbarui.', 'payload' => '{"order_id":"TEST-1"}',
            'dedupe_key' => str_repeat('b', 64), 'status' => 'pending', 'attempts' => 0,
            'next_attempt_at' => $now, 'sent_at' => null, 'last_error' => null, 'created_at' => $now, 'updated_at' => $now,
        ]);
    }

    protected function tearDown(): void
    {
        $this->connection->close();
        parent::tearDown();
    }

    private function dispatch(array $result, bool $throws = false): void
    {
        $connection = $this->connection;
        $command = new class($connection, $result, $throws) extends DispatchCustomerNotifications {
            protected function connection(): BaseConnection { return $this->testDb; }
            protected function decryptDeviceToken(string $ciphertext): string { return 'decrypted-test-token'; }
            public function __construct(private BaseConnection $testDb, public array $result, public bool $throws) {}
            protected function sender(): FirebaseMessageSender
            {
                $owner = $this;
                return new class($owner) implements FirebaseMessageSender {
                    public function __construct(private $owner) {}
                    public function send(string $deviceToken, array $notification, array $data = []): array
                    {
                        if ($this->owner->throws) throw new RuntimeException('synthetic transport outage');
                        return $this->owner->result;
                    }
                };
            }
        };
        $command->run([]);
    }

    public function testSuccessfulFcmDeliveryMarksOutboxEventSent(): void
    {
        $this->dispatch(['ok' => true, 'status' => 200, 'error_code' => null]);
        $notification = $this->connection->table('customer_push_notifications')->get()->getRowArray();
        $this->assertSame('sent', $notification['status']);
        $this->assertNotEmpty($notification['sent_at']);
        $this->assertSame(0, (int)$notification['attempts']);
    }

    public function testTransientFcmFailureSchedulesRetryWithoutLeakingProviderError(): void
    {
        $this->dispatch([], true);
        $notification = $this->connection->table('customer_push_notifications')->get()->getRowArray();
        $this->assertSame('pending', $notification['status']);
        $this->assertSame(1, (int)$notification['attempts']);
        $this->assertSame('FCM_DELIVERY_FAILED', $notification['last_error']);
        $this->assertGreaterThan(time(), strtotime($notification['next_attempt_at']));
    }

    public function testUnregisteredDeviceIsDisabledAndNotificationIsRetried(): void
    {
        $this->dispatch(['ok' => false, 'status' => 404, 'error_code' => 'UNREGISTERED']);
        $device = $this->connection->table('customer_push_devices')->get()->getRowArray();
        $notification = $this->connection->table('customer_push_notifications')->get()->getRowArray();
        $this->assertSame(0, (int)$device['is_active']);
        $this->assertSame('pending', $notification['status']);
        $this->assertSame('FCM_DELIVERY_FAILED', $notification['last_error']);
    }

    public function testNotificationWaitsForDeviceInsteadOfBeingLost(): void
    {
        $this->connection->table('customer_push_devices')->emptyTable();
        $this->dispatch(['ok' => true, 'status' => 200, 'error_code' => null]);
        $notification = $this->connection->table('customer_push_notifications')->get()->getRowArray();
        $this->assertSame('awaiting_device', $notification['status']);
        $this->assertNull($notification['sent_at']);
        $this->assertSame('AWAITING_DEVICE', $notification['last_error']);
    }
}
