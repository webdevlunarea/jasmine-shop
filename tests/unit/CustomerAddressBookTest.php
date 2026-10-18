<?php

use App\Services\CustomerAddressBook;
use CodeIgniter\Test\CIUnitTestCase;

final class CustomerAddressBookTest extends CIUnitTestCase
{
    private $connection;
    private CustomerAddressBook $book;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = \Config\Database::connect([
            'DBDriver' => 'SQLite3', 'database' => ':memory:', 'DBPrefix' => '', 'DBDebug' => true,
        ], false);
        $this->connection->query('CREATE TABLE customer_addresses (id INTEGER PRIMARY KEY AUTOINCREMENT, email_user VARCHAR(254) NOT NULL, label VARCHAR(60) NOT NULL, is_default INTEGER NOT NULL DEFAULT 0)');
        $this->book = new CustomerAddressBook($this->connection);
    }

    protected function tearDown(): void
    {
        $this->connection->close();
        parent::tearDown();
    }

    private function add(string $email, string $label, int $default = 0): int
    {
        $this->connection->table('customer_addresses')->insert([
            'email_user' => $email, 'label' => $label, 'is_default' => $default,
        ]);
        return (int) $this->connection->insertID();
    }

    private function rows(string $email): array
    {
        return $this->connection->table('customer_addresses')->where('email_user', $email)->orderBy('id')->get()->getResultArray();
    }

    public function testDeleteDefaultPromotesOldestRemainingOwnedAddress(): void
    {
        $oldest = $this->add('a@example.test', 'Kantor');
        $default = $this->add('a@example.test', 'Rumah', 1);
        $this->assertTrue($this->book->delete('a@example.test', $default));
        $rows = $this->rows('a@example.test');
        $this->assertCount(1, $rows);
        $this->assertSame($oldest, (int) $rows[0]['id']);
        $this->assertSame(1, (int) $rows[0]['is_default']);
    }

    public function testDeleteNonDefaultPreservesCurrentDefault(): void
    {
        $remove = $this->add('a@example.test', 'Kantor');
        $default = $this->add('a@example.test', 'Rumah', 1);
        $this->assertTrue($this->book->delete('a@example.test', $remove));
        $rows = $this->rows('a@example.test');
        $this->assertCount(1, $rows);
        $this->assertSame($default, (int) $rows[0]['id']);
        $this->assertSame(1, (int) $rows[0]['is_default']);
    }

    public function testDeleteOnlyDefaultLeavesAddressBookEmpty(): void
    {
        $id = $this->add('a@example.test', 'Rumah', 1);
        $this->assertTrue($this->book->delete('a@example.test', $id));
        $this->assertSame([], $this->rows('a@example.test'));
    }

    public function testCannotMutateAnotherCustomersAddress(): void
    {
        $id = $this->add('b@example.test', 'Pribadi', 1);
        $this->assertFalse($this->book->delete('a@example.test', $id));
        $this->assertFalse($this->book->setDefault('a@example.test', $id));
        $this->assertCount(1, $this->rows('b@example.test'));
        $this->assertSame(1, (int) $this->rows('b@example.test')[0]['is_default']);
    }

    public function testSetDefaultLeavesExactlyOneDefaultForCustomer(): void
    {
        $first = $this->add('a@example.test', 'Rumah', 1);
        $second = $this->add('a@example.test', 'Kantor');
        $this->add('b@example.test', 'Lain', 1);
        $this->assertTrue($this->book->setDefault('a@example.test', $second));
        $rows = $this->rows('a@example.test');
        $this->assertSame([0, 1], array_map(static fn(array $row): int => (int) $row['is_default'], $rows));
        $this->assertSame($second, (int) $rows[1]['id']);
        $this->assertSame(1, (int) $this->rows('b@example.test')[0]['is_default']);
        $this->assertNotSame($first, $second);
    }
}
