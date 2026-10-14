<?php

use App\Models\CustomerAddressModel;
use CodeIgniter\Test\CIUnitTestCase;

final class CustomerAddressModelTest extends CIUnitTestCase
{
    private $connection;
    private $migration;
    private $addresses;

    protected function setUp(): void
    {
        parent::setUp();
        // Explicit, disposable database: never use the default/production connection.
        $this->connection = \Config\Database::connect([
            'DBDriver' => 'SQLite3', 'database' => ':memory:', 'DBPrefix' => '', 'DBDebug' => true,
        ], false);
        require_once APPPATH . 'Database/Migrations/2026-10-15-000001_CreateCustomerAddresses.php';
        $this->migration = new \App\Database\Migrations\CreateCustomerAddresses(\Config\Database::forge($this->connection));
        $this->migration->up();
        $this->addresses = new CustomerAddressModel($this->connection);
    }

    protected function tearDown(): void
    {
        $this->connection->close();
        parent::tearDown();
    }

    private function add(string $owner, string $label, bool $default = false): int
    {
        return (int)$this->addresses->insert([
            'email_user' => $owner, 'label' => $label,
            'recipient_name' => 'Penerima "Rumah"', 'recipient_phone' => '081234567890',
            'address_data' => json_encode(['add' => 'Jalan "Melati" No. 1']),
            'is_default' => $default ? 1 : 0,
        ]);
    }

    public function testCustomersOnlyReadTheirOwnAddresses(): void
    {
        $mine = $this->add('a@example.test', 'Rumah');
        $other = $this->add('b@example.test', 'Kantor');
        $this->assertCount(1, $this->addresses->forCustomer('a@example.test'));
        $this->assertNotNull($this->addresses->owned('a@example.test', $mine));
        $this->assertNull($this->addresses->owned('a@example.test', $other));
    }

    public function testDefaultSortAndRecipientRoundTrip(): void
    {
        $this->add('a@example.test', 'Kantor');
        $id = $this->add('a@example.test', 'Rumah', true);
        $rows = $this->addresses->forCustomer('a@example.test');
        $this->assertSame($id, (int)$rows[0]['id']);
        $this->assertSame('Penerima "Rumah"', $rows[0]['recipient_name']);
        $this->assertNotEmpty($rows[0]['created_at']);
    }

    public function testRollbackProtectsSavedAddresses(): void
    {
        $this->add('a@example.test', 'Rumah');
        $this->expectException(\RuntimeException::class);
        $this->migration->down();
    }

    public function testEmptyTableRollback(): void
    {
        $this->migration->down();
        $this->assertFalse($this->connection->tableExists('customer_addresses'));
    }
}
