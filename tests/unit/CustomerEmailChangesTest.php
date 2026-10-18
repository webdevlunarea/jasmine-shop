<?php

use App\Services\Auth\CustomerEmailChanges;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Database;

final class CustomerEmailChangesTest extends CIUnitTestCase
{
    private BaseConnection $connection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = Database::connect([
            'DBDriver' => 'SQLite3', 'database' => ':memory:', 'DBPrefix' => '', 'DBDebug' => true,
        ], false);
        $this->connection->query('CREATE TABLE user (email TEXT PRIMARY KEY, sandi TEXT NOT NULL)');
        $this->connection->query('CREATE TABLE pembeli (email_user TEXT PRIMARY KEY, nama TEXT NOT NULL)');
        $this->connection->query('CREATE TABLE customer_email_changes (
            email_user TEXT PRIMARY KEY, pending_email TEXT UNIQUE, otp_hash TEXT, request_id TEXT,
            expires_at TEXT, requested_at TEXT, attempts INTEGER NOT NULL DEFAULT 0,
            changed_at TEXT, created_at TEXT, updated_at TEXT
        )');
        $this->connection->query('CREATE TABLE customer_password_sessions (email_user TEXT PRIMARY KEY, version TEXT NOT NULL)');
        $this->connection->query('CREATE TABLE customer_api_tokens (
            id INTEGER PRIMARY KEY AUTOINCREMENT, email_user TEXT NOT NULL, revoked_at TEXT NULL
        )');
        $this->connection->table('user')->insert(['email' => 'old@example.test', 'sandi' => password_hash('CurrentPass123', PASSWORD_DEFAULT)]);
        $this->connection->table('pembeli')->insert(['email_user' => 'old@example.test', 'nama' => 'Test Buyer']);
        $this->connection->table('customer_password_sessions')->insert(['email_user' => 'old@example.test', 'version' => 'old-version']);
        $this->connection->table('customer_api_tokens')->insert(['email_user' => 'old@example.test', 'revoked_at' => null]);
        $this->connection->table('customer_email_changes')->insert([
            'email_user' => 'old@example.test', 'pending_email' => 'new@example.test',
            'otp_hash' => password_hash('123456', PASSWORD_DEFAULT), 'request_id' => str_repeat('a', 36),
            'expires_at' => gmdate('Y-m-d H:i:s', time() + 600), 'requested_at' => gmdate('Y-m-d H:i:s'),
            'attempts' => 0, 'created_at' => gmdate('Y-m-d H:i:s'), 'updated_at' => gmdate('Y-m-d H:i:s'),
        ]);
    }

    protected function tearDown(): void
    {
        $this->connection->close();
        parent::tearDown();
    }

    private function service(): CustomerEmailChanges
    {
        return new class($this->connection) extends CustomerEmailChanges {
            public array $notices = [];
            public function __construct(BaseConnection $db) { parent::__construct($db); }
            protected function deliver(string $to, string $subject, string $body): bool
            {
                $this->notices[] = [$to, $subject];
                return true;
            }
        };
    }

    public function testConfirmAtomicallyMovesAccountAndRotatesPasswordSession(): void
    {
        $service = $this->service();
        $newEmail = $service->confirm('old@example.test', 'CurrentPass123', '123456');

        $this->assertSame('new@example.test', $newEmail);
        $this->assertNull($this->connection->table('user')->where('email', 'old@example.test')->get()->getRowArray());
        $this->assertNotNull($this->connection->table('user')->where('email', 'new@example.test')->get()->getRowArray());
        $this->assertNotNull($this->connection->table('pembeli')->where('email_user', 'new@example.test')->get()->getRowArray());
        $change = $this->connection->table('customer_email_changes')->where('email_user', 'new@example.test')->get()->getRowArray();
        $this->assertNull($change['pending_email']);
        $this->assertNull($change['otp_hash']);
        $session = $this->connection->table('customer_password_sessions')->where('email_user', 'new@example.test')->get()->getRowArray();
        $this->assertNotSame('old-version', $session['version']);
        $apiToken = $this->connection->table('customer_api_tokens')->where('email_user', 'old@example.test')->get()->getRowArray();
        $this->assertNotEmpty($apiToken['revoked_at']);
        $this->assertSame([['old@example.test', 'Pemberitahuan perubahan email Lunarea']], $service->notices);
    }

    public function testFailedAccountReferenceUpdateRollsBackIdentityChange(): void
    {
        $this->connection->query("CREATE TRIGGER reject_buyer_email BEFORE UPDATE OF email_user ON pembeli BEGIN SELECT RAISE(ABORT, 'synthetic failure'); END");
        $service = $this->service();

        try {
            $service->confirm('old@example.test', 'CurrentPass123', '123456');
            $this->fail('A failed customer-profile update must roll back the identity change.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Email change transaction failed', $exception->getMessage());
        }

        $this->assertNotNull($this->connection->table('user')->where('email', 'old@example.test')->get()->getRowArray());
        $this->assertNull($this->connection->table('user')->where('email', 'new@example.test')->get()->getRowArray());
        $this->assertNotNull($this->connection->table('customer_email_changes')->where('email_user', 'old@example.test')->get()->getRowArray());
        $this->assertNull($this->connection->table('customer_api_tokens')->where('email_user', 'old@example.test')->get()->getRowArray()['revoked_at']);
        $this->assertSame([], $service->notices);
    }

    public function testInvalidCodeIncrementsAttemptCounter(): void
    {
        try {
            $this->service()->confirm('old@example.test', 'CurrentPass123', '000000');
            $this->fail('An invalid verification code must be rejected.');
        } catch (DomainException $exception) {
            $this->assertSame('Kode verifikasi tidak sesuai.', $exception->getMessage());
        }

        $row = $this->connection->table('customer_email_changes')->where('email_user', 'old@example.test')->get()->getRowArray();
        $this->assertSame(1, (int)$row['attempts']);
    }
}
