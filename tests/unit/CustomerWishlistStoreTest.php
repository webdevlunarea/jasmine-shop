<?php

use App\Services\CustomerWishlistStore;
use CodeIgniter\Test\CIUnitTestCase;

final class CustomerWishlistStoreTest extends CIUnitTestCase
{
    private $connection;
    private CustomerWishlistStore $store;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = \Config\Database::connect([
            'DBDriver' => 'SQLite3', 'database' => ':memory:', 'DBPrefix' => '', 'DBDebug' => true,
        ], false);
        $this->connection->query('CREATE TABLE pembeli (email_user TEXT PRIMARY KEY, wishlist TEXT COLLATE NOCASE NULL)');
        $this->store = new CustomerWishlistStore($this->connection);
    }

    protected function tearDown(): void
    {
        $this->connection->close();
        parent::tearDown();
    }

    private function put(string $email, ?string $items): void
    {
        $this->connection->table('pembeli')->insert(['email_user' => $email, 'wishlist' => $items]);
    }

    private function current(string $email): ?string
    {
        $row = $this->connection->table('pembeli')->where('email_user', $email)->get()->getRowArray();
        return $row['wishlist'] === null ? null : (string)$row['wishlist'];
    }

    public function testCompareAndSwapWritesOnlyTheCurrentSnapshot(): void
    {
        $old = '[{"id":"A"}]';
        $this->put('buyer@example.test', $old);
        $this->assertTrue($this->store->saveIfUnchanged('buyer@example.test', $old, [['id' => 'A'], ['id' => 'B']]));
        $this->assertSame('[{"id":"A"},{"id":"B"}]', $this->current('buyer@example.test'));
    }

    public function testStaleOrCaseAlteredSnapshotCannotOverwriteWishlist(): void
    {
        $current = '[{"id":"a"}]';
        $this->put('buyer@example.test', $current);
        $this->assertFalse($this->store->saveIfUnchanged('buyer@example.test', '[{"id":"A"}]', []));
        $this->assertSame($current, $this->current('buyer@example.test'));
    }

    public function testNullLegacyWishlistCanBeInitializedAtomically(): void
    {
        $this->put('buyer@example.test', null);
        $this->assertTrue($this->store->saveIfUnchanged('buyer@example.test', null, [['id' => 'A']]));
        $this->assertSame('[{"id":"A"}]', $this->current('buyer@example.test'));
    }

    public function testSnapshotCannotBeAppliedToAnotherAccount(): void
    {
        $this->put('other@example.test', '[]');
        $this->assertFalse($this->store->saveIfUnchanged('buyer@example.test', '[]', [['id' => 'A']]));
        $this->assertSame('[]', $this->current('other@example.test'));
    }
}
