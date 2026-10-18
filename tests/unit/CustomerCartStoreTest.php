<?php

use App\Services\CustomerCartStore;
use CodeIgniter\Test\CIUnitTestCase;

final class CustomerCartStoreTest extends CIUnitTestCase
{
    private $connection;
    private CustomerCartStore $store;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = \Config\Database::connect([
            'DBDriver' => 'SQLite3', 'database' => ':memory:', 'DBPrefix' => '', 'DBDebug' => true,
        ], false);
        $this->connection->query('CREATE TABLE pembeli (email_user TEXT PRIMARY KEY, keranjang TEXT COLLATE NOCASE NOT NULL)');
        $this->store = new CustomerCartStore($this->connection);
    }

    protected function tearDown(): void
    {
        $this->connection->close();
        parent::tearDown();
    }

    private function put(string $email, string $cart): void
    {
        $this->connection->table('pembeli')->insert(['email_user' => $email, 'keranjang' => $cart]);
    }

    private function current(string $email): string
    {
        return (string)$this->connection->table('pembeli')->where('email_user', $email)->get()->getRowArray()['keranjang'];
    }

    public function testCompareAndSwapPersistsCartWhenSnapshotMatches(): void
    {
        $old = '[{"id":"A","jumlah":1,"varian":"Putih"}]';
        $new = [['id' => 'A', 'jumlah' => 2, 'varian' => 'Putih']];
        $this->put('buyer@example.test', $old);
        $this->assertTrue($this->store->saveIfUnchanged('buyer@example.test', $old, $new));
        $this->assertSame('[{"id":"A","jumlah":2,"varian":"Putih"}]', $this->current('buyer@example.test'));
    }

    public function testStaleSnapshotCannotOverwriteConcurrentCartChange(): void
    {
        $stale = '[{"id":"A","jumlah":1}]';
        $current = '[{"id":"A","jumlah":3}]';
        $this->put('buyer@example.test', $current);
        $this->assertFalse($this->store->saveIfUnchanged('buyer@example.test', $stale, [['id' => 'A', 'jumlah' => 2]]));
        $this->assertSame($current, $this->current('buyer@example.test'));
    }

    public function testSnapshotIsScopedToAuthenticatedCustomer(): void
    {
        $old = '[]';
        $this->put('other@example.test', $old);
        $this->assertFalse($this->store->saveIfUnchanged('buyer@example.test', $old, [['id' => 'A']]));
        $this->assertSame($old, $this->current('other@example.test'));
    }

    public function testIdenticalCartIsOnlyAcceptedWhileSnapshotStillCurrent(): void
    {
        $old = '[{"id":"A","jumlah":1}]';
        $this->put('buyer@example.test', $old);
        $this->assertTrue($this->store->saveIfUnchanged('buyer@example.test', $old, [['id' => 'A', 'jumlah' => 1]]));
        $this->assertFalse($this->store->saveIfUnchanged('buyer@example.test', '[]', []));
    }

    public function testSnapshotComparisonIsCaseSensitiveEvenWithCaseInsensitiveColumnCollation(): void
    {
        $current = '[{"id":"A","varian":"putih"}]';
        $stale = '[{"id":"A","varian":"Putih"}]';
        $this->put('buyer@example.test', $current);
        $this->assertFalse($this->store->saveIfUnchanged('buyer@example.test', $stale, []));
        $this->assertSame($current, $this->current('buyer@example.test'));
    }
}
