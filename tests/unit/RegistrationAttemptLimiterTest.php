<?php

use App\Services\Auth\RegistrationAttemptLimiter;
use CodeIgniter\Test\CIUnitTestCase;

final class RegistrationAttemptLimiterTest extends CIUnitTestCase
{
    public function testWebsiteAndApiUseTheSameHashedBucketsAndLimits(): void
    {
        $calls = [];
        $limiter = new RegistrationAttemptLimiter(static function (string $key, int $limit, int $seconds) use (&$calls): bool {
            $calls[] = [$key, $limit, $seconds];
            return true;
        });

        $this->assertTrue($limiter->allow('203.0.113.8', ' Person@Example.com '));
        $this->assertSame([
            ['lunarea-register-ip-' . hash('sha256', '203.0.113.8'), 5, 3600],
            ['lunarea-register-identity-' . hash('sha256', 'person@example.com'), 3, 3600],
        ], $calls);
    }

    public function testDeniedIpOrIdentityRejectsRegistration(): void
    {
        $limiter = new RegistrationAttemptLimiter(static fn(string $key): bool =>
            !str_starts_with($key, 'lunarea-register-ip-')
        );
        $this->assertFalse($limiter->allow('203.0.113.8', 'person@example.com'));

        $limiter = new RegistrationAttemptLimiter(static fn(string $key): bool =>
            !str_starts_with($key, 'lunarea-register-identity-')
        );
        $this->assertFalse($limiter->allow('203.0.113.8', 'person@example.com'));
    }
}
