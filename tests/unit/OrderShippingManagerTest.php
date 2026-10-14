<?php

use App\Services\Shipping\OrderShippingManager;
use CodeIgniter\Test\CIUnitTestCase;

final class OrderShippingManagerTest extends CIUnitTestCase
{
    private $connection;
    private OrderShippingManager $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = \Config\Database::connect(['DBDriver' => 'SQLite3', 'database' => ':memory:', 'DBPrefix' => '', 'DBDebug' => true], false);
        $this->connection->query('CREATE TABLE pemesanan (id INTEGER PRIMARY KEY, id_midtrans TEXT, status TEXT, resi TEXT, kurir TEXT)');
        $this->connection->query('CREATE TABLE order_shipments (id INTEGER PRIMARY KEY, id_midtrans TEXT, shipping_method_code TEXT, shipping_status TEXT, tracking_number TEXT, shipping_cost INTEGER, updated_at TEXT)');
        $this->connection->table('pemesanan')->insert(['id_midtrans' => 'LTEST', 'status' => 'Proses', 'resi' => 'Menunggu pengiriman', 'kurir' => 'JNE']);
        $this->connection->table('order_shipments')->insert(['id_midtrans' => 'LTEST', 'shipping_method_code' => 'rajaongkir_domestic', 'shipping_status' => 'Menunggu pengiriman', 'shipping_cost' => 12345]);
        $this->manager = new OrderShippingManager($this->connection);
    }

    protected function tearDown(): void
    {
        $this->connection->close();
        parent::tearDown();
    }

    public function testExternalShipmentUpdatesBothRecordsAndKeepsCost(): void
    {
        $result = $this->manager->update('LTEST', ['kurir' => 'JNE', 'resi' => 'TEST123']);
        $this->assertSame('Dikirim', $result['order']['status']);
        $saved = $this->connection->table('order_shipments')->get()->getRowArray();
        $this->assertSame('TEST123', $saved['tracking_number']);
        $this->assertSame(12345, (int)$saved['shipping_cost']);
        $this->assertFalse($this->manager->update('LTEST', ['kurir' => 'JNE', 'resi' => 'TEST123'])['changed']);
    }

    public function testInternalDeliveryNeedsNoAwbAndCompletesBothRecords(): void
    {
        $this->connection->table('order_shipments')->update(['shipping_method_code' => 'internal_delivery', 'shipping_cost' => 0]);
        $result = $this->manager->update('LTEST', ['shipping_status' => 'Disiapkan']);
        $this->assertSame('Proses', $result['order']['status']);
        $result = $this->manager->update('LTEST', ['shipping_status' => 'Dalam Pengiriman', 'resi' => 'FORGED']);
        $this->assertSame('', $result['order']['resi']);
        $this->assertNull($result['shipment']['tracking_number']);
        $result = $this->manager->update('LTEST', [], true);
        $this->assertSame('Selesai', $result['order']['status']);
        $this->assertSame('Terkirim', $result['shipment']['shipping_status']);
    }

    public function testUnpaidOrderIsRejected(): void
    {
        $this->connection->table('pemesanan')->update(['status' => 'Menunggu Pembayaran']);
        $this->expectException(\DomainException::class);
        $this->manager->update('LTEST', ['kurir' => 'JNE', 'resi' => 'TEST123']);
    }

    public function testMissingAwbDoesNotMutateOrder(): void
    {
        try { $this->manager->update('LTEST', ['kurir' => 'JNE', 'resi' => '']); $this->fail('Invalid AWB accepted'); }
        catch (\DomainException $e) { $this->assertSame('Proses', $this->connection->table('pemesanan')->get()->getRowArray()['status']); }
    }

    public function testCannotCompleteBeforeDispatch(): void
    {
        $this->expectException(\DomainException::class);
        $this->manager->update('LTEST', [], true);
    }

    public function testCannotRegressCompletedOrders(): void
    {
        $this->manager->update('LTEST', ['kurir' => 'JNE', 'resi' => 'TEST123']);
        $this->manager->update('LTEST', [], true);
        $this->expectException(\DomainException::class);
        $this->manager->update('LTEST', ['kurir' => 'JNE', 'resi' => 'TEST123']);
    }

    public function testLegacyOrderWorksWithoutShipmentTable(): void
    {
        $this->connection->query('DROP TABLE order_shipments');
        $this->connection->resetDataCache();
        $result = $this->manager->update('LTEST', ['kurir' => 'TIKI', 'resi' => 'TEST999']);
        $this->assertSame('Dikirim', $result['order']['status']);
        $this->assertNull($result['shipment']);
    }

    public function testQuotedCourierCannotBeSilentlyChanged(): void
    {
        $this->expectException(\DomainException::class);
        $this->manager->update('LTEST', ['kurir' => 'TIKI', 'resi' => 'TEST999']);
    }
}
