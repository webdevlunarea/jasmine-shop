<?php

use App\Filters\ApiCustomerFilter;
use CodeIgniter\Test\CIUnitTestCase;

final class ApiCustomerFilterTest extends CIUnitTestCase
{
    private function filter(?array $customer = null, bool $throws = false): ApiCustomerFilter
    {
        return new class($customer, $throws) extends ApiCustomerFilter {
            public function __construct(private ?array $customer, private bool $throws) {}
            protected function authenticateToken(string $token): ?array
            {
                if ($this->throws) throw new RuntimeException('test storage failure');
                return $this->customer;
            }
        };
    }

    public function testBearerAuthenticationOverwritesUntrustedIdentityHeader(): void
    {
        $request = service('request');
        $request->setHeader('Authorization', 'Bearer ' . str_repeat('a', 48));
        $request->setHeader('X-Lunarea-Authenticated-Customer', 'attacker@example.test');
        $this->assertNull($this->filter(['email' => 'buyer@example.test'])->before($request));
        $this->assertSame('buyer@example.test', $request->getHeaderLine('X-Lunarea-Authenticated-Customer'));
    }

    public function testMissingOrInvalidBearerTokenIsRejected(): void
    {
        $request = service('request');
        $request->setHeader('Authorization', 'Basic not-a-token');
        $response = $this->filter(['email' => 'buyer@example.test'])->before($request);
        $this->assertSame(401, $response->getStatusCode());
        $this->assertSame('UNAUTHENTICATED', json_decode($response->getBody(), true)['error']['code']);
    }

    public function testStorageFailureFailsClosedWithServiceUnavailable(): void
    {
        $request = service('request');
        $request->setHeader('Authorization', 'Bearer ' . str_repeat('b', 48));
        $response = $this->filter(null, true)->before($request);
        $this->assertSame(503, $response->getStatusCode());
        $this->assertSame('AUTH_UNAVAILABLE', json_decode($response->getBody(), true)['error']['code']);
    }
}
