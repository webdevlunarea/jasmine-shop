<?php

namespace Tests\Unit;

use App\Services\Payments\InventoryOrderReservation;
use App\Services\Payments\ReservedStockRelease;
use Config\Database;
use DomainException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class InventoryOrderReservationTest extends TestCase
{
    private $db;

    protected function setUp(): void
    {
        parent::setUp();
        $this->db = Database::connect(['DBDriver' => 'SQLite3', 'database' => ':memory:', 'DBPrefix' => '', 'DBDebug' => true], false);
        $this->db->query('CREATE TABLE barang (id TEXT PRIMARY KEY, nama TEXT NOT NULL, active INTEGER NOT NULL, stok TEXT NOT NULL, varian TEXT NOT NULL)');
        $this->db->query('CREATE TABLE test_orders (id TEXT PRIMARY KEY)');
    }

    protected function tearDown(): void
    {
        $this->db->close();
        parent::tearDown();
    }

    public function testVariantStockAndOrderShellCommitTogether(): void
    {
        $this->product('P1', '["A","B"]', '3,5');
        $order = (new InventoryOrderReservation($this->db))->reserveAndPersist([
            ['product_id' => 'P1', 'variant' => 'B', 'quantity' => 2],
        ], function ($db) {
            $db->table('test_orders')->insert(['id' => 'O1']);
            return 'O1';
        });

        self::assertSame('O1', $order);
        self::assertSame('3,3', $this->stock('P1'));
        self::assertSame(1, $this->db->table('test_orders')->countAllResults());
    }

    public function testAggregateVariantStockAndReleaseRoundTrip(): void
    {
        $this->product('P2', '[{"nama":"A"},{"name":"B"}]', '4');
        (new InventoryOrderReservation($this->db))->reserveAndPersist([
            ['product_id' => 'P2', 'variant' => 'B', 'quantity' => 2],
        ], static fn() => 'O2');
        self::assertSame('2', $this->stock('P2'));

        ReservedStockRelease::release($this->db, [
            'items' => json_encode([['id' => 'P2', 'name' => 'Produk (B)', 'quantity' => 2]]),
        ]);
        self::assertSame('4', $this->stock('P2'));
    }

    public function testInsufficientStockAndPersistenceFailureRollBackReservation(): void
    {
        $this->product('P3', '["A"]', '1');
        $persistCalled = false;
        try {
            (new InventoryOrderReservation($this->db))->reserveAndPersist([
                ['product_id' => 'P3', 'variant' => 'A', 'quantity' => 2],
            ], static function () use (&$persistCalled) { $persistCalled = true; });
            self::fail('Out-of-stock reservation should fail.');
        } catch (DomainException $e) {
            self::assertSame('Stok salah satu produk berubah. Perbarui keranjang.', $e->getMessage());
        }
        self::assertFalse($persistCalled);
        self::assertSame('1', $this->stock('P3'));

        $this->product('P4', '["A"]', '3');
        try {
            (new InventoryOrderReservation($this->db))->reserveAndPersist([
                ['product_id' => 'P4', 'variant' => 'A', 'quantity' => 2],
            ], static function () { throw new RuntimeException('order persist error'); });
            self::fail('Persistence error should roll back stock.');
        } catch (RuntimeException $e) {
            self::assertSame('order persist error', $e->getMessage());
        }
        self::assertSame('3', $this->stock('P4'));
    }

    public function testSandboxOrderPersistsWithoutReservingProductionInventory(): void
    {
        $this->product('P5', '["A"]', '7');
        (new InventoryOrderReservation($this->db))->reserveAndPersist([
            ['product_id' => 'P5', 'variant' => 'A', 'quantity' => 2],
        ], function ($db) {
            $db->table('test_orders')->insert(['id' => 'SANDBOX1']);
            return 'SANDBOX1';
        }, null, false);
        self::assertSame('7', $this->stock('P5'));
        self::assertSame(1, $this->db->table('test_orders')->where('id', 'SANDBOX1')->countAllResults());
    }

    private function product(string $id, string $variants, string $stock): void
    {
        $this->db->table('barang')->insert(['id' => $id, 'nama' => 'Produk ' . $id, 'active' => 1, 'stok' => $stock, 'varian' => $variants]);
    }

    private function stock(string $id): string
    {
        return (string)$this->db->table('barang')->where('id', $id)->get()->getRowArray()['stok'];
    }
}
