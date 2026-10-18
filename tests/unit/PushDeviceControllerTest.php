<?php

use App\Controllers\Api\V1\PushDeviceController;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Database;

final class PushDeviceControllerTest extends CIUnitTestCase
{
    private $connection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = Database::connect(['DBDriver' => 'SQLite3', 'database' => ':memory:', 'DBPrefix' => '', 'DBDebug' => true], false);
        $this->connection->query('CREATE TABLE customer_push_devices (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            email_user VARCHAR(254) NOT NULL,
            device_token_hash CHAR(64) NOT NULL UNIQUE,
            device_token TEXT NOT NULL,
            platform VARCHAR(20) NOT NULL,
            device_name VARCHAR(100) NULL,
            is_active INTEGER NOT NULL DEFAULT 1,
            last_seen_at DATETIME NOT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL
        )');
    }

    protected function tearDown(): void
    {
        if ($this->connection) $this->connection->close();
        parent::tearDown();
    }

    private function invoke(string $method, string $email, array $payload)
    {
        $request = service('request');
        $request->setBody(json_encode($payload));
        $request->setHeader('Content-Type', 'application/json');
        $request->setHeader('X-Lunarea-Authenticated-Customer', $email);
        service('response')->setStatusCode(200);
        $controller = new class extends PushDeviceController {
            public function __construct()
            {
                $this->request = service('request');
                $this->response = service('response');
            }
            protected function encryptDeviceToken(string $token): string { return 'encrypted:' . $token; }
            protected function connection(): \CodeIgniter\Database\BaseConnection { return $this->testConnection; }
            public \CodeIgniter\Database\BaseConnection $testConnection;
        };
        $controller->testConnection = $this->connection;
        return $controller->{$method}();
    }

    public function testRegisterStoresOnlyEncryptedTokenAndReassignsExistingInstallation(): void
    {
        $token = str_repeat('fcm-token-', 8);
        $first = $this->invoke('register', 'first@example.test', ['device_token' => $token, 'platform' => 'android']);
        $this->assertSame(200, $first->getStatusCode());
        $row = $this->connection->table('customer_push_devices')->get()->getRowArray();
        $this->assertSame(hash('sha256', $token), $row['device_token_hash']);
        $this->assertSame('encrypted:' . $token, $row['device_token']);
        $this->assertSame('first@example.test', $row['email_user']);

        $this->invoke('register', 'second@example.test', ['device_token' => $token, 'platform' => 'ANDROID']);
        $this->assertSame(1, $this->connection->table('customer_push_devices')->countAllResults());
        $this->assertSame('second@example.test', $this->connection->table('customer_push_devices')->get()->getRow()->email_user);
    }

    public function testRegisterRejectsInvalidPlatformOrShortToken(): void
    {
        $response = $this->invoke('register', 'buyer@example.test', ['device_token' => 'short', 'platform' => 'ios']);
        $this->assertSame(422, $response->getStatusCode());
        $this->assertSame(0, $this->connection->table('customer_push_devices')->countAllResults());
    }

    public function testUnregisterIsOwnerScopedAndIdempotent(): void
    {
        $token = str_repeat('device-token-', 5);
        $this->invoke('register', 'buyer@example.test', ['device_token' => $token, 'platform' => 'android']);
        $this->invoke('unregister', 'other@example.test', ['device_token' => $token]);
        $this->assertSame(1, (int)$this->connection->table('customer_push_devices')->get()->getRow()->is_active);
        $this->invoke('unregister', 'buyer@example.test', ['device_token' => $token]);
        $this->assertSame(0, (int)$this->connection->table('customer_push_devices')->get()->getRow()->is_active);
    }
}
