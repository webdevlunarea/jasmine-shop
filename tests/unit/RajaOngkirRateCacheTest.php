<?php

use App\Models\ShippingMethodModel;
use App\Services\Shipping\RajaOngkirRateProvider;
use CodeIgniter\Test\CIUnitTestCase;

final class RajaOngkirRateCacheTest extends CIUnitTestCase
{
    private bool $hadEnv = false;
    private $oldEnv = null;
    private bool $hadServer = false;
    private $oldServer = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->hadEnv = array_key_exists('RAJAONGKIR_API_KEY', $_ENV);
        $this->oldEnv = $_ENV['RAJAONGKIR_API_KEY'] ?? null;
        $this->hadServer = array_key_exists('RAJAONGKIR_API_KEY', $_SERVER);
        $this->oldServer = $_SERVER['RAJAONGKIR_API_KEY'] ?? null;
        $_ENV['RAJAONGKIR_API_KEY'] = $_SERVER['RAJAONGKIR_API_KEY'] = 'unit-test-key';
    }

    protected function tearDown(): void
    {
        if ($this->hadEnv) $_ENV['RAJAONGKIR_API_KEY'] = $this->oldEnv;
        else unset($_ENV['RAJAONGKIR_API_KEY']);
        if ($this->hadServer) $_SERVER['RAJAONGKIR_API_KEY'] = $this->oldServer;
        else unset($_SERVER['RAJAONGKIR_API_KEY']);
        parent::tearDown();
    }

    public function testIdenticalRateQueriesAreCachedButChangedWeightOrDestinationMisses(): void
    {
        $method = new class extends ShippingMethodModel {
            public function __construct() {}
            public function getByCode(string $code)
            {
                return ['id' => 3, 'code' => $code, 'is_active' => 1, 'settings' => '{"origin_id":"64987","couriers":["jne","tiki"]}'];
            }
        };
        $provider = new class($method) extends RajaOngkirRateProvider {
            public array $cache = [];
            public int $requests = 0;
            public function __construct(ShippingMethodModel $method) { parent::__construct($method); }
            protected function resolveOriginId($client, string $apiKey, array $settings): string { return (string)$settings['origin_id']; }
            protected function cacheGet(string $key) { return $this->cache[$key] ?? false; }
            protected function cacheSave(string $key, $value, int $ttl): void { $this->cache[$key] = $value; }
            protected function requestRatePayload($client, string $apiKey, string $originId, string $destinationId, int $weightGrams, string $courier): ?array
            {
                $this->requests++;
                return ['data' => [[
                    'code' => 'jne', 'name' => 'JNE', 'service' => 'REG',
                    'cost' => $weightGrams + (int)$destinationId, 'etd' => '2-3',
                ]]];
            }
        };
        $address = ['rajaongkir_destination_id' => '62240'];

        $first = $provider->getQuotes($address, 1500);
        $repeat = $provider->getQuotes($address, 1500);
        $this->assertSame(1, $provider->requests, 'An identical quote should reuse the provider response.');
        $provider->getQuotes($address, 2000);
        $provider->getQuotes(['rajaongkir_destination_id' => '64987'], 1500);

        $this->assertSame($first, $repeat);
        $this->assertSame(3, $provider->requests, 'Changing weight or destination must not reuse a mismatched quote.');
        $this->assertSame(63740, $first[0]['cost']);
    }
}
