<?php

use App\Database\Migrations\CreateCustomerApiTokensAndDevices;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Database\BaseConnection;

require_once APPPATH . 'Database/Migrations/2026-10-18-000004_CreateCustomerApiTokensAndDevices.php';

final class CustomerApiStorageMigrationTest extends CIUnitTestCase
{
    private BaseConnection $connection;
    private CreateCustomerApiTokensAndDevices $migration;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = \Config\Database::connect(['DBDriver' => 'SQLite3', 'database' => ':memory:', 'DBPrefix' => '', 'DBDebug' => true], false);
        $this->connection->query('CREATE TABLE pemesanan (id INTEGER PRIMARY KEY, marker TEXT NOT NULL)');
        $this->connection->table('pemesanan')->insert(['id' => 1, 'marker' => 'legacy-order']);
        $this->migration = new CreateCustomerApiTokensAndDevices(\Config\Database::forge($this->connection));
    }

    protected function tearDown(): void { $this->connection->close(); parent::tearDown(); }

    public function testMigrationCreatesOnlyTheFourIsolatedApiTablesAndCanRollbackWhenEmpty(): void
    {
        $this->migration->up();
        foreach (['customer_api_tokens', 'customer_push_devices', 'customer_push_notifications', 'customer_checkout_requests'] as $table) {
            $this->assertTrue($this->connection->tableExists($table), $table);
        }
        $this->migration->down();
        foreach (['customer_api_tokens', 'customer_push_devices', 'customer_push_notifications', 'customer_checkout_requests'] as $table) {
            $this->assertFalse($this->connection->tableExists($table), $table);
        }
        $this->assertSame('legacy-order', $this->connection->table('pemesanan')->where('id', 1)->get()->getRow()->marker);
    }

    public function testRollbackRefusesToDropTablesContainingDeviceData(): void
    {
        $this->migration->up();
        $this->connection->table('customer_push_devices')->insert([
            'email_user' => 'buyer@example.test', 'device_token_hash' => str_repeat('a', 64), 'device_token' => 'ciphertext',
            'platform' => 'android', 'device_name' => null, 'is_active' => 1,
            'last_seen_at' => gmdate('Y-m-d H:i:s'), 'created_at' => gmdate('Y-m-d H:i:s'), 'updated_at' => gmdate('Y-m-d H:i:s'),
        ]);
        try { $this->migration->down(); $this->fail('Migration rollback should preserve registered device records'); }
        catch (RuntimeException $e) { $this->assertStringContainsString('berisi sesi atau perangkat', $e->getMessage()); }
        $this->assertTrue($this->connection->tableExists('customer_push_devices'));
        $this->assertSame(1, $this->connection->table('customer_push_devices')->countAllResults());
    }
}
