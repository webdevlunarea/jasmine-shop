<?php

use App\Services\Shipping\ShippingService;
use App\Services\Shipping\ShippingSubsidyPolicy as Policy;
use App\Services\Shipping\RajaOngkirRateProvider;
use App\Services\Shipping\InternalDeliveryProvider;
use CodeIgniter\Test\CIUnitTestCase;

final class ShippingSubsidyQuoteTest extends CIUnitTestCase
{
    private $connection;
    private $external;
    private $internal;
    private array $cart = [['id' => 'PRODUCT', 'varian' => 'Putih', 'jumlah' => 1]];
    private array $address = ['provinsi_id' => 1, 'kabupaten_id' => 2, 'kecamatan_id' => 3, 'kelurahan_id' => 4, 'kodepos' => '50181'];

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = \Config\Database::connect(['DBDriver' => 'SQLite3', 'database' => ':memory:', 'DBPrefix' => '', 'DBDebug' => true], false);
        $this->connection->query('CREATE TABLE shipping_methods (id INTEGER PRIMARY KEY, code TEXT, name TEXT, type TEXT, provider TEXT, is_active INTEGER, settings TEXT, sort_order INTEGER, created_at TEXT, updated_at TEXT)');
        $this->connection->table('shipping_methods')->insert(['code' => 'rajaongkir_domestic', 'is_active' => 1, 'settings' => json_encode(['subsidy' => Policy::fromInput(['enabled' => true, 'percentage' => '10'])])]);
        require_once APPPATH . 'Database/Migrations/2026-10-14-000002_CreateShippingQuotesAndShipments.php';
        require_once APPPATH . 'Database/Migrations/2026-10-15-000002_AddShippingPricingSnapshot.php';
        $forge = \Config\Database::forge($this->connection);
        (new \App\Database\Migrations\CreateShippingQuotesAndShipments($forge))->up();
        (new \App\Database\Migrations\AddShippingPricingSnapshot($forge))->up();
        $this->external = $this->createMock(RajaOngkirRateProvider::class);
        $this->external->method('getQuotes')->willReturn([[
            'shipping_method_code' => 'rajaongkir_domestic', 'provider' => 'rajaongkir',
            'courier_code' => 'jne', 'courier_name' => 'JNE', 'service_code' => 'REG', 'cost' => 100000,
            'raw_response' => ['cost' => 100000, 'internal_provider_field' => 'private'],
        ]]);
        $this->internal = $this->createMock(InternalDeliveryProvider::class);
        $this->internal->method('getQuotes')->willReturn([]);
    }

    protected function tearDown(): void
    {
        $this->connection->close();
        parent::tearDown();
    }

    private function service(): ShippingService
    {
        return new class($this->connection, $this->external, $this->internal) extends ShippingService {
            public function __construct($db, $external, $internal) {
                parent::__construct($db); $this->rajaOngkirProvider = $external; $this->internalProvider = $internal;
            }
        };
    }

    public function testPublicQuoteOnlyReturnsFinalPriceAndServerKeepsAudit(): void
    {
        $service = $this->service();
        $quote = $service->getQuotes('buyer@test.invalid', $this->cart, $this->address, 1000)[0];
        $this->assertSame(90000, $quote['cost']);
        $this->assertArrayNotHasKey('raw_response', $quote);
        $this->assertArrayNotHasKey('shipping_pricing', $quote);
        $this->assertArrayNotHasKey('original_cost', $quote);
        $row = $service->validateQuoteToken($quote['token'], 'buyer@test.invalid', $this->cart, $this->address, 1000);
        $this->assertNotNull($row);
        $this->assertSame(10000, Policy::snapshot($row)['subsidy_amount']);
    }

    public function testPolicyChangesInvalidateUnpaidQuoteWithoutRepricingOldRow(): void
    {
        $quote = $this->service()->getQuotes('buyer@test.invalid', $this->cart, $this->address, 1000)[0];
        $this->connection->table('shipping_methods')->update(['settings' => json_encode(['subsidy' => Policy::fromInput(['enabled' => true, 'percentage' => '20'])])]);
        $this->assertNull($this->service()->validateQuoteToken($quote['token'], 'buyer@test.invalid', $this->cart, $this->address, 1000));
        $this->assertSame(90000, (int)$this->connection->table('shipping_quotes')->get()->getRowArray()['cost']);
    }

    public function testAnotherBuyerOrChangedAddressCannotUseQuote(): void
    {
        $service = $this->service(); $quote = $service->getQuotes('buyer@test.invalid', $this->cart, $this->address, 1000)[0];
        $this->assertNull($service->validateQuoteToken($quote['token'], 'other@test.invalid', $this->cart, $this->address, 1000));
        $address = $this->address; $address['kecamatan_id'] = 9;
        $this->assertNull($service->validateQuoteToken($quote['token'], 'buyer@test.invalid', $this->cart, $address, 1000));
    }

    public function testDisabledPolicyAndMissingSchemaKeepOriginalCost(): void
    {
        $oldQuote = $this->service()->getQuotes('buyer@test.invalid', $this->cart, $this->address, 1000)[0];
        $this->connection->query('ALTER TABLE order_shipments DROP COLUMN shipping_pricing');
        $this->connection->resetDataCache();
        $quote = $this->service()->getQuotes('buyer@test.invalid', $this->cart, $this->address, 1000)[0];
        $this->assertSame(100000, $quote['cost']);
        $this->assertNull($this->service()->validateQuoteToken($oldQuote['token'], 'buyer@test.invalid', $this->cart, $this->address, 1000));
    }

    public function testRollbackProtectsSavedOrderPricing(): void
    {
        $this->connection->table('order_shipments')->insert(['id_midtrans' => 'LTEST', 'shipping_method_code' => 'rajaongkir_domestic', 'provider' => 'rajaongkir', 'shipping_pricing' => '{"subsidy_amount":10000}']);
        $this->expectException(\RuntimeException::class);
        (new \App\Database\Migrations\AddShippingPricingSnapshot(\Config\Database::forge($this->connection)))->down();
    }

    public function testSettingsMergeDoesNotEraseSubsidyOrOrigin(): void
    {
        $id = (int)$this->connection->table('shipping_methods')->get()->getRowArray()['id'];
        \App\Services\Shipping\ShippingMethodSettingsStore::merge($id, ['origin_id' => '64987'], 1, $this->connection);
        $settings = json_decode($this->connection->table('shipping_methods')->get()->getRowArray()['settings'], true);
        $this->assertSame(1000, $settings['subsidy']['percentage_bps']);
        \App\Services\Shipping\ShippingMethodSettingsStore::merge($id, ['subsidy' => Policy::normalize([])], null, $this->connection);
        $settings = json_decode($this->connection->table('shipping_methods')->get()->getRowArray()['settings'], true);
        $this->assertSame('64987', $settings['origin_id']);
    }

    public function testExpiredQuoteCannotBeReplayed(): void
    {
        $quote = $this->service()->getQuotes('buyer@test.invalid', $this->cart, $this->address, 1000)[0];
        $this->connection->table('shipping_quotes')->update(['expires_at' => date('Y-m-d H:i:s', time() - 60)]);
        $this->assertNull($this->service()->validateQuoteToken($quote['token'], 'buyer@test.invalid', $this->cart, $this->address, 1000));
    }

    public function testFullySubsidizedExternalOptionIsLegitimateFreeDelivery(): void
    {
        $this->connection->table('shipping_methods')->update(['settings' => json_encode(['subsidy' => Policy::fromInput(['enabled' => true, 'percentage' => '100'])])]);
        $service = $this->service();
        $quote = $service->getQuotes('buyer@test.invalid', $this->cart, $this->address, 1000)[0];
        $this->assertSame(0, $quote['cost']);
        $this->assertNotNull($service->validateQuoteToken($quote['token'], 'buyer@test.invalid', $this->cart, $this->address, 1000));
    }
}
