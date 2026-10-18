<?php

use App\Controllers\Api\V1\OrderController;
use App\Services\Shipping\BinderByteTrackingProvider;
use CodeIgniter\Test\CIUnitTestCase;

final class ApiOrderTrackingControllerTest extends CIUnitTestCase
{
    private $oldApiKey;

    protected function setUp(): void
    {
        parent::setUp();
        $this->oldApiKey = getenv('BINDERBYTE_API_KEY');
        putenv('BINDERBYTE_API_KEY=controller-test-key');
        service('request')->setHeader('X-Lunarea-Authenticated-Customer', 'buyer@example.test');
        service('request')->setMethod('GET');
    }

    protected function tearDown(): void
    {
        $this->oldApiKey === false ? putenv('BINDERBYTE_API_KEY') : putenv('BINDERBYTE_API_KEY=' . $this->oldApiKey);
        parent::tearDown();
    }

    private function controller(array $order, ?array $shipment, bool $allow, callable $transport, ?string $owner = 'buyer@example.test'): OrderController
    {
        return new class($order, $shipment, $allow, $transport, $owner) extends OrderController {
            public int $providerCalls = 0;
            private array $fixtureOrder;
            private ?array $fixtureShipment;
            private bool $allow;
            private $transport;
            private ?string $owner;

            public function __construct(array $order, ?array $shipment, bool $allow, callable $transport, ?string $owner)
            {
                $this->fixtureOrder = $order;
                $this->fixtureShipment = $shipment;
                $this->allow = $allow;
                $this->transport = $transport;
                $this->owner = $owner;
                $this->request = service('request');
                $this->response = service('response');
            }

            protected function findOwnedOrder(string $id, string $email): ?array
            {
                return $this->owner === $email && ($this->fixtureOrder['id_midtrans'] ?? null) === $id ? $this->fixtureOrder : null;
            }

            protected function findShipment(string $id): ?array { return $this->fixtureShipment; }
            protected function allowLiveTrackingRequest(string $email, string $id): bool { return $this->allow; }

            protected function binderByteProvider(): BinderByteTrackingProvider
            {
                $this->providerCalls++;
                return new BinderByteTrackingProvider($this->transport);
            }
        };
    }

    private function order(string $id = 'ORDER-123'): array
    {
        return ['id_midtrans' => $id, 'email_cus' => 'buyer@example.test', 'status' => 'Proses', 'resi' => 'CM12345678', 'kurir' => 'JNE'];
    }

    public function testOwnedExternalOrderReturnsStoredAndLiveTrackingSeparately(): void
    {
        $controller = $this->controller($this->order(), [
            'shipping_method_code' => 'rajaongkir_domestic', 'courier_code' => 'jne', 'courier_name' => 'JNE',
            'tracking_number' => 'CM12345678', 'shipping_status' => 'Dalam Pengiriman', 'updated_at' => '2026-10-18 10:00:00',
        ], true, static fn(string $url, array $query): array => [
            'status' => 200,
            'body' => ['status' => 200, 'data' => [
                'summary' => ['awb' => $query['awb'], 'status' => 'Paket tiba di hub'],
                'detail' => ['origin' => 'Semarang'], 'history' => [['desc' => 'Paket tiba di hub']],
            ]],
        ]);

        $response = $controller->tracking('ORDER-123');
        $body = json_decode($response->getBody(), true);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('Dalam Pengiriman', $body['data']['tracking']['status']);
        $this->assertSame('Proses', $body['data']['tracking']['order_status']);
        $this->assertSame('Paket tiba di hub', $body['data']['tracking']['live_tracking']['summary']['status']);
        $this->assertSame(1, $controller->providerCalls);
    }

    public function testInternalDeliveryUsesSavedStatusAndNeverCallsBinderByte(): void
    {
        $controller = $this->controller($this->order(), [
            'shipping_method_code' => 'internal_delivery', 'courier_name' => 'Jasa Kirim Toko',
            'tracking_number' => '', 'shipping_status' => 'Disiapkan', 'updated_at' => '2026-10-18 10:00:00',
        ], true, static fn(): array => []);

        $response = $controller->tracking('ORDER-123');
        $body = json_decode($response->getBody(), true);

        $this->assertSame('Disiapkan', $body['data']['tracking']['status']);
        $this->assertNull($body['data']['tracking']['live_tracking']);
        $this->assertSame(0, $controller->providerCalls);
    }

    public function testBinderByteFailurePreservesTheSavedShipmentSnapshot(): void
    {
        $controller = $this->controller($this->order(), [
            'shipping_method_code' => 'rajaongkir_domestic', 'courier_code' => 'jne', 'courier_name' => 'JNE',
            'tracking_number' => 'CM12345678', 'shipping_status' => 'Dalam Pengiriman',
            'updated_at' => '2026-10-18 10:00:00',
        ], true, static fn(): array => [
            'status' => 400, 'body' => ['status' => 400, 'message' => 'provider-private-detail'],
        ]);

        $response = $controller->tracking('ORDER-123');
        $body = json_decode($response->getBody(), true);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('Dalam Pengiriman', $body['data']['tracking']['status']);
        $this->assertFalse($body['data']['tracking']['live_tracking']['success']);
        $this->assertStringNotContainsString('provider-private-detail', $response->getBody());
    }

    public function testOwnerIsolationAndRateLimitAvoidProviderCall(): void
    {
        $transport = static fn(): array => [];
        $notOwner = $this->controller($this->order(), null, true, $transport, 'other@example.test');
        $response = $notOwner->tracking('ORDER-123');
        $this->assertSame(404, $response->getStatusCode());
        $this->assertSame(0, $notOwner->providerCalls);

        $limited = $this->controller($this->order(), null, false, $transport);
        $response = $limited->tracking('ORDER-123');
        $body = json_decode($response->getBody(), true);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertFalse($body['data']['tracking']['live_tracking']['success']);
        $this->assertSame(0, $limited->providerCalls);
    }
}
