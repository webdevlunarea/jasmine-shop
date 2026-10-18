<?php

use App\Services\Shipping\BinderByteTrackingProvider;
use PHPUnit\Framework\TestCase;

final class BinderByteTrackingProviderTest extends TestCase
{
    private $oldApiKey;
    private $oldBaseUrl;

    protected function setUp(): void
    {
        parent::setUp();
        $this->oldApiKey = getenv('BINDERBYTE_API_KEY');
        $this->oldBaseUrl = getenv('BINDERBYTE_BASE_URL');
        putenv('BINDERBYTE_API_KEY=unit-test-key');
        putenv('BINDERBYTE_BASE_URL=https://api.binderbyte.com');
    }

    protected function tearDown(): void
    {
        $this->oldApiKey === false ? putenv('BINDERBYTE_API_KEY') : putenv('BINDERBYTE_API_KEY=' . $this->oldApiKey);
        $this->oldBaseUrl === false ? putenv('BINDERBYTE_BASE_URL') : putenv('BINDERBYTE_BASE_URL=' . $this->oldBaseUrl);
        parent::tearDown();
    }

    public function testReturnsOnlyNormalizedTrackingFieldsAndSendsExpectedQuery(): void
    {
        $provider = new BinderByteTrackingProvider(static function (string $url, array $query): array {
            TestCase::assertSame('https://api.binderbyte.com/v1/track', $url);
            TestCase::assertSame(['api_key' => 'unit-test-key', 'courier' => 'jne', 'awb' => 'CM12345678'], $query);
            return ['status' => 200, 'body' => [
                'status' => 200,
                'data' => [
                    'summary' => ['awb' => 'CM12345678', 'status' => 'Dalam Pengiriman'],
                    'detail' => ['origin' => 'Semarang'],
                    'history' => [['desc' => 'Paket diterima kurir']],
                    'sensitive_provider_field' => 'not exposed',
                ],
            ]];
        });

        $result = $provider->track('JNE', 'cm-1234 5678');

        $this->assertTrue($result['success']);
        $this->assertSame('BinderByte', $result['provider']);
        $this->assertSame('Dalam Pengiriman', $result['summary']['status']);
        $this->assertSame('Semarang', $result['detail']['origin']);
        $this->assertCount(1, $result['history']);
        $this->assertArrayNotHasKey('raw', $result);
    }

    public function testProviderFailureUsesGenericCustomerMessage(): void
    {
        $provider = new BinderByteTrackingProvider(static fn(): array => [
            'status' => 400, 'body' => ['status' => 400, 'message' => 'internal provider diagnostic'],
        ]);

        $result = $provider->track('jne', 'CM12345678');

        $this->assertFalse($result['success']);
        $this->assertStringNotContainsString('internal provider diagnostic', $result['message']);
    }

    public function testRejectsUntrustedBaseUrlAndUnknownCourierBeforeNetworkCall(): void
    {
        putenv('BINDERBYTE_BASE_URL=http://127.0.0.1:8080');
        $called = false;
        $provider = new BinderByteTrackingProvider(static function () use (&$called): array {
            $called = true;
            return [];
        });

        $result = $provider->track('unknown', 'CM12345678');
        $this->assertFalse($result['success']);
        $this->assertFalse($called);

        $result = $provider->track('jne', 'CM12345678');
        $this->assertFalse($result['success']);
        $this->assertFalse($called);

        putenv('BINDERBYTE_BASE_URL=https://api.binderbyte.com.attacker.example');
        $result = $provider->track('jne', 'CM12345678');
        $this->assertFalse($result['success']);
        $this->assertFalse($called);
    }

    public function testCourierMappingAndAwbNormalization(): void
    {
        $this->assertSame('jnt', BinderByteTrackingProvider::resolveCourierCode('J&T Express'));
        $this->assertSame('CM123456789', BinderByteTrackingProvider::normalizeAwb(' cm-123 456 789 '));
        $this->assertSame('', BinderByteTrackingProvider::resolveCourierCode('Kurir Tidak Dikenal'));
    }
}
