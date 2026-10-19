<?php

namespace Tests\Unit;

use App\Services\Auth\GoogleCustomerAuthenticator;
use DomainException;
use PHPUnit\Framework\TestCase;

final class GoogleCustomerAuthenticatorTest extends TestCase
{
    public function testMalformedGoogleIdTokenIsRejectedBeforeAccountResolution(): void
    {
        $this->expectException(DomainException::class);
        (new GoogleCustomerAuthenticator())->authenticate('not-a-signed-google-id-token');
    }
}
