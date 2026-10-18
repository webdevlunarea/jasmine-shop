<?php

namespace Tests\Unit;

use App\Services\Payments\MidtransChargeClient;
use PHPUnit\Framework\TestCase;

final class MidtransChargeClientTest extends TestCase
{
    public function testChargeUsesSandboxAndVerifiedTlsAndValidatesMatchingOrder(): void
    {
        $captured = null;
        $client = new MidtransChargeClient(true, 'server-test-key', static function ($method, $url, $options) use (&$captured) {
            $captured = [$method, $url, $options];
            return ['http_status' => 201, 'body' => [
                'status_code' => '201', 'order_id' => 'L00000123', 'transaction_status' => 'pending',
            ]];
        });
        $response = $client->charge($this->payload());

        self::assertSame('201', $response['status_code']);
        self::assertSame('POST', $captured[0]);
        self::assertSame('https://api.sandbox.midtrans.com/v2/charge', $captured[1]);
        self::assertTrue($captured[2]['verify']);
        self::assertFalse($captured[2]['allow_redirects']);
        self::assertSame($this->payload(), $captured[2]['json']);
    }

    public function testProductionEnvironmentSelectsProductionEndpoint(): void
    {
        $url = '';
        (new MidtransChargeClient(false, 'production-key', static function ($method, $endpoint) use (&$url) {
            $url = $endpoint;
            return ['http_status' => 201, 'body' => ['status_code' => '201', 'order_id' => 'L00000123']];
        }))->charge($this->payload());

        self::assertSame('https://api.midtrans.com/v2/charge', $url);
    }

    /** @dataProvider invalidResponses */
    public function testChargeRejectsUntrustedOrMalformedResponses(array $response): void
    {
        $client = new MidtransChargeClient(false, 'server-key', static fn() => $response);
        $this->expectException(\RuntimeException::class);
        $client->charge($this->payload());
    }

    public static function invalidResponses(): array
    {
        return [
            'HTTP error' => [['http_status' => 500, 'body' => ['status_code' => '500', 'order_id' => 'L00000123']]],
            'mismatched order' => [['http_status' => 201, 'body' => ['status_code' => '201', 'order_id' => 'OTHER']]],
            'provider rejection' => [['http_status' => 400, 'body' => ['status_code' => '400', 'order_id' => 'L00000123']]],
            'invalid JSON' => [['http_status' => 201, 'body' => 'not-json']],
        ];
    }

    public function testChargeRejectsMissingKeyAndInvalidAmountBeforeTransport(): void
    {
        $called = false;
        $client = new MidtransChargeClient(false, 'DefaultValue', static function () use (&$called) {
            $called = true;
            return [];
        });
        try {
            $client->charge($this->payload());
            self::fail('Expected invalid configuration rejection');
        } catch (\RuntimeException $e) {
            self::assertFalse($called);
        }

        $client = new MidtransChargeClient(false, 'server-key', static fn() => []);
        $payload = $this->payload();
        $payload['transaction_details']['gross_amount'] = 0;
        $this->expectException(\RuntimeException::class);
        $client->charge($payload);
    }

    private function payload(): array
    {
        return ['transaction_details' => ['order_id' => 'L00000123', 'gross_amount' => 150000]];
    }
}
